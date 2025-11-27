<?php
/**
 * Messages API
 * Handles in-app messaging between users and admins
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require authentication
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = Database::getInstance();
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'send_message':
        $recipientId = (int)($_POST['recipient_id'] ?? 0);
        $subject = sanitize($_POST['subject'] ?? 'No Subject');
        $body = sanitize($_POST['body'] ?? '');
        $parentId = (int)($_POST['parent_id'] ?? 0) ?: null;
        $priority = sanitize($_POST['priority'] ?? 'normal');
        
        if (!$recipientId || !$body) {
            jsonResponse(['success' => false, 'message' => 'Recipient and message body are required'], 400);
        }
        
        // Verify recipient exists
        $recipient = getUserById($recipientId);
        if (!$recipient) {
            jsonResponse(['success' => false, 'message' => 'Recipient not found'], 404);
        }
        
        // Create message
        $messageId = $db->insert('messages', [
            'sender_id' => $userId,
            'recipient_id' => $recipientId,
            'subject' => $subject,
            'body' => $body,
            'parent_id' => $parentId,
            'priority' => $priority
        ]);
        
        // Create notification for recipient
        $sender = getCurrentUser();
        createNotification(
            $recipientId,
            'message',
            'New Message from ' . $sender['first_name'] . ' ' . $sender['last_name'],
            substr(strip_tags($body), 0, 100),
            'pages/messages.php?with=' . $userId,
            $priority
        );
        
        logActivity($userId, 'send_message', 'message', $messageId, [
            'recipient_id' => $recipientId,
            'subject' => $subject
        ]);
        
        jsonResponse([
            'success' => true, 
            'message' => 'Message sent successfully',
            'message_id' => $messageId
        ]);
        break;
        
    case 'get_messages':
        $otherUserId = (int)($_GET['with'] ?? 0);
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 50), 100);
        
        if (!$otherUserId) {
            jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
        }
        
        $offset = ($page - 1) * $limit;
        
        $messages = $db->fetchAll(
            "SELECT m.*, 
                    s.first_name as sender_first_name, s.last_name as sender_last_name, s.role as sender_role
             FROM messages m
             JOIN users s ON m.sender_id = s.id
             WHERE (m.sender_id = :user_id AND m.recipient_id = :other_id)
                OR (m.sender_id = :other_id AND m.recipient_id = :user_id)
             ORDER BY m.created_at DESC
             LIMIT $offset, $limit",
            ['user_id' => $userId, 'other_id' => $otherUserId]
        );
        
        // Mark as read
        $db->update('messages', 
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            'recipient_id = :user_id AND sender_id = :other_id AND is_read = 0',
            ['user_id' => $userId, 'other_id' => $otherUserId]
        );
        
        jsonResponse(['success' => true, 'messages' => array_reverse($messages)]);
        break;
        
    case 'get_conversations':
        $conversations = $db->fetchAll(
            "SELECT 
                CASE WHEN m.sender_id = :user_id THEN m.recipient_id ELSE m.sender_id END as other_user_id,
                MAX(m.created_at) as last_message_time,
                SUM(CASE WHEN m.recipient_id = :user_id AND m.is_read = 0 THEN 1 ELSE 0 END) as unread_count
             FROM messages m
             WHERE m.sender_id = :user_id OR m.recipient_id = :user_id
             GROUP BY other_user_id
             ORDER BY last_message_time DESC",
            ['user_id' => $userId]
        );
        
        // Get user details and last message for each conversation
        foreach ($conversations as &$conv) {
            $otherUser = getUserById($conv['other_user_id']);
            $conv['other_user'] = [
                'id' => $otherUser['id'],
                'first_name' => $otherUser['first_name'],
                'last_name' => $otherUser['last_name'],
                'role' => $otherUser['role']
            ];
            
            $lastMessage = $db->fetch(
                "SELECT subject, body, sender_id, created_at FROM messages 
                 WHERE (sender_id = :user_id AND recipient_id = :other_id) 
                    OR (sender_id = :other_id AND recipient_id = :user_id)
                 ORDER BY created_at DESC LIMIT 1",
                ['user_id' => $userId, 'other_id' => $conv['other_user_id']]
            );
            $conv['last_message'] = $lastMessage;
        }
        
        jsonResponse(['success' => true, 'conversations' => $conversations]);
        break;
        
    case 'mark_read':
        $messageId = (int)($_POST['message_id'] ?? 0);
        
        if ($messageId) {
            $db->update('messages', 
                ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
                'id = :id AND recipient_id = :user_id',
                ['id' => $messageId, 'user_id' => $userId]
            );
        } else {
            // Mark all as read
            $db->update('messages', 
                ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
                'recipient_id = :user_id AND is_read = 0',
                ['user_id' => $userId]
            );
        }
        
        jsonResponse(['success' => true, 'message' => 'Marked as read']);
        break;
        
    case 'get_unread_count':
        $count = $db->count('messages', 'recipient_id = :id AND is_read = 0', ['id' => $userId]);
        jsonResponse(['success' => true, 'count' => $count]);
        break;
        
    case 'delete_message':
        $messageId = (int)($_POST['message_id'] ?? 0);
        
        if (!$messageId) {
            jsonResponse(['success' => false, 'message' => 'Message ID required'], 400);
        }
        
        // Check ownership
        $message = $db->fetch(
            "SELECT * FROM messages WHERE id = :id AND (sender_id = :user_id OR recipient_id = :user_id)",
            ['id' => $messageId, 'user_id' => $userId]
        );
        
        if (!$message) {
            jsonResponse(['success' => false, 'message' => 'Message not found'], 404);
        }
        
        // Archive instead of delete
        if ($message['sender_id'] == $userId) {
            $db->update('messages', ['is_archived_sender' => 1], 'id = :id', ['id' => $messageId]);
        } else {
            $db->update('messages', ['is_archived_recipient' => 1], 'id = :id', ['id' => $messageId]);
        }
        
        jsonResponse(['success' => true, 'message' => 'Message archived']);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
