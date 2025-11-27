<?php
/**
 * Messages Page
 * In-app messaging system between users and admins
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();

$user = getCurrentUser();
$db = Database::getInstance();

// Get conversation with specific user if ID provided
$conversationWith = isset($_GET['with']) ? (int)$_GET['with'] : null;
$messageId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Mark message as read if viewing specific message
if ($messageId) {
    $db->update('messages', ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')], 
        'id = :id AND recipient_id = :user_id', 
        ['id' => $messageId, 'user_id' => $user['id']]
    );
}

// Get conversations (grouped by other user)
$conversations = $db->fetchAll(
    "SELECT 
        CASE WHEN m.sender_id = :user_id THEN m.recipient_id ELSE m.sender_id END as other_user_id,
        MAX(m.created_at) as last_message_time,
        SUM(CASE WHEN m.recipient_id = :user_id AND m.is_read = 0 THEN 1 ELSE 0 END) as unread_count
     FROM messages m
     WHERE m.sender_id = :user_id OR m.recipient_id = :user_id
     GROUP BY other_user_id
     ORDER BY last_message_time DESC",
    ['user_id' => $user['id']]
);

// Get user details for each conversation
foreach ($conversations as &$conv) {
    $otherUser = getUserById($conv['other_user_id']);
    $conv['other_user'] = $otherUser;
    
    // Get last message
    $lastMessage = $db->fetch(
        "SELECT * FROM messages 
         WHERE (sender_id = :user_id AND recipient_id = :other_id) 
            OR (sender_id = :other_id AND recipient_id = :user_id)
         ORDER BY created_at DESC LIMIT 1",
        ['user_id' => $user['id'], 'other_id' => $conv['other_user_id']]
    );
    $conv['last_message'] = $lastMessage;
}

// Get messages for selected conversation
$selectedMessages = [];
if ($conversationWith) {
    $selectedMessages = $db->fetchAll(
        "SELECT m.*, 
                s.first_name as sender_first_name, s.last_name as sender_last_name, s.role as sender_role
         FROM messages m
         JOIN users s ON m.sender_id = s.id
         WHERE (m.sender_id = :user_id AND m.recipient_id = :other_id)
            OR (m.sender_id = :other_id AND m.recipient_id = :user_id)
         ORDER BY m.created_at ASC",
        ['user_id' => $user['id'], 'other_id' => $conversationWith]
    );
    
    // Mark all as read
    $db->update('messages', ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')], 
        'recipient_id = :user_id AND sender_id = :other_id AND is_read = 0', 
        ['user_id' => $user['id'], 'other_id' => $conversationWith]
    );
    
    $otherUserDetails = getUserById($conversationWith);
}

// Get admins/moderators for new message
$admins = $db->fetchAll("SELECT id, first_name, last_name, role FROM users WHERE role IN ('admin', 'moderator') AND is_active = 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .messages-container {
            height: calc(100vh - 200px);
            min-height: 500px;
        }
        .conversation-list {
            max-height: 100%;
            overflow-y: auto;
        }
        .conversation-item {
            cursor: pointer;
            transition: all 0.2s;
        }
        .conversation-item:hover {
            background-color: #f8f9fa;
        }
        .conversation-item.active {
            background-color: #e7f1ff;
            border-left: 3px solid #0d6efd;
        }
        .message-thread {
            max-height: calc(100% - 150px);
            overflow-y: auto;
            padding: 1rem;
        }
        .message-bubble {
            max-width: 75%;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            margin-bottom: 0.5rem;
        }
        .message-bubble.sent {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 0.25rem;
        }
        .message-bubble.received {
            background-color: #f0f2f5;
            border-bottom-left-radius: 0.25rem;
        }
        .message-compose {
            border-top: 1px solid #dee2e6;
            padding: 1rem;
            background: white;
        }
        .unread-badge {
            width: 10px;
            height: 10px;
            background: #0d6efd;
            border-radius: 50%;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row g-4">
            <!-- Sidebar - Conversations List -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Messages</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                            <i class="bi bi-plus-lg me-1"></i>New
                        </button>
                    </div>
                    <div class="card-body p-0 conversation-list">
                        <?php if (empty($conversations)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-chat-dots text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No messages yet</p>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                                Start a conversation
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($conversations as $conv): ?>
                            <?php if ($conv['other_user']): ?>
                            <a href="?with=<?= $conv['other_user_id'] ?>" 
                               class="list-group-item list-group-item-action conversation-item <?= $conversationWith == $conv['other_user_id'] ? 'active' : '' ?>">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-<?= $conv['other_user']['role'] === 'admin' ? 'danger' : ($conv['other_user']['role'] === 'moderator' ? 'warning' : 'primary') ?> text-white me-3" style="width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <?= strtoupper(substr($conv['other_user']['first_name'], 0, 1) . substr($conv['other_user']['last_name'], 0, 1)) ?>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong><?= htmlspecialchars($conv['other_user']['first_name'] . ' ' . $conv['other_user']['last_name']) ?></strong>
                                            <?php if ($conv['unread_count'] > 0): ?>
                                            <span class="badge bg-primary rounded-pill"><?= $conv['unread_count'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted text-truncate d-block">
                                            <?= htmlspecialchars(substr($conv['last_message']['body'] ?? '', 0, 40)) ?>...
                                        </small>
                                        <small class="text-muted">
                                            <?= timeAgo($conv['last_message_time']) ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Main - Message Thread -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm messages-container">
                    <?php if ($conversationWith && isset($otherUserDetails)): ?>
                    <!-- Conversation Header -->
                    <div class="card-header bg-transparent d-flex align-items-center py-3">
                        <div class="avatar-circle bg-<?= $otherUserDetails['role'] === 'admin' ? 'danger' : ($otherUserDetails['role'] === 'moderator' ? 'warning' : 'primary') ?> text-white me-3" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <?= strtoupper(substr($otherUserDetails['first_name'], 0, 1) . substr($otherUserDetails['last_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="mb-0"><?= htmlspecialchars($otherUserDetails['first_name'] . ' ' . $otherUserDetails['last_name']) ?></h6>
                            <small class="text-muted">
                                <span class="badge bg-<?= $otherUserDetails['role'] === 'admin' ? 'danger' : ($otherUserDetails['role'] === 'moderator' ? 'warning' : 'primary') ?> badge-sm">
                                    <?= ucfirst($otherUserDetails['role']) ?>
                                </span>
                            </small>
                        </div>
                    </div>
                    
                    <!-- Messages -->
                    <div class="message-thread" id="message-thread">
                        <?php foreach ($selectedMessages as $msg): ?>
                        <div class="d-flex <?= $msg['sender_id'] == $user['id'] ? 'justify-content-end' : 'justify-content-start' ?>">
                            <div class="message-bubble <?= $msg['sender_id'] == $user['id'] ? 'sent' : 'received' ?>">
                                <div class="fw-medium small mb-1">
                                    <?= $msg['sender_id'] == $user['id'] ? 'You' : htmlspecialchars($msg['sender_first_name']) ?>
                                </div>
                                <div><?= nl2br(htmlspecialchars($msg['body'])) ?></div>
                                <div class="small mt-1 <?= $msg['sender_id'] == $user['id'] ? 'text-white-50' : 'text-muted' ?>">
                                    <?= formatDate($msg['created_at'], 'M d, g:i A') ?>
                                    <?php if ($msg['sender_id'] == $user['id'] && $msg['is_read']): ?>
                                    <i class="bi bi-check2-all ms-1"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Compose -->
                    <div class="message-compose">
                        <form id="reply-form" onsubmit="sendReply(event)">
                            <div class="input-group">
                                <input type="text" class="form-control" id="reply-message" placeholder="Type your message..." required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <?php else: ?>
                    <!-- No conversation selected -->
                    <div class="card-body d-flex flex-column align-items-center justify-content-center h-100">
                        <i class="bi bi-chat-square-text text-muted" style="font-size: 5rem;"></i>
                        <h5 class="mt-4 text-muted">Select a conversation</h5>
                        <p class="text-muted">Choose from your existing conversations or start a new one</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                            <i class="bi bi-plus-lg me-2"></i>New Message
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- New Message Modal -->
    <div class="modal fade" id="newMessageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-envelope me-2"></i>New Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="new-message-form">
                        <div class="mb-3">
                            <label for="recipient" class="form-label">To <span class="text-danger">*</span></label>
                            <select class="form-select" id="recipient" required>
                                <option value="">Select recipient</option>
                                <?php foreach ($admins as $admin): ?>
                                <option value="<?= $admin['id'] ?>">
                                    <?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?>
                                    (<?= ucfirst($admin['role']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" required placeholder="What is this about?">
                        </div>
                        <div class="mb-3">
                            <label for="message-body" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message-body" rows="5" required placeholder="Type your message here..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="priority" id="priority-low" value="low">
                                <label class="btn btn-outline-secondary" for="priority-low">Low</label>
                                
                                <input type="radio" class="btn-check" name="priority" id="priority-normal" value="normal" checked>
                                <label class="btn btn-outline-primary" for="priority-normal">Normal</label>
                                
                                <input type="radio" class="btn-check" name="priority" id="priority-high" value="high">
                                <label class="btn btn-outline-warning" for="priority-high">High</label>
                                
                                <input type="radio" class="btn-check" name="priority" id="priority-urgent" value="urgent">
                                <label class="btn btn-outline-danger" for="priority-urgent">Urgent</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="sendNewMessage()">
                        <i class="bi bi-send me-2"></i>Send Message
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        // Scroll to bottom of messages
        const thread = document.getElementById('message-thread');
        if (thread) {
            thread.scrollTop = thread.scrollHeight;
        }
        
        async function sendReply(event) {
            event.preventDefault();
            
            const message = document.getElementById('reply-message').value.trim();
            if (!message) return;
            
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('recipient_id', <?= $conversationWith ?? 0 ?>);
            formData.append('body', message);
            
            const result = await apiRequest(API_BASE + '/api/messages.php', 'POST', formData);
            
            if (result.success) {
                location.reload();
            }
        }
        
        async function sendNewMessage() {
            const recipient = document.getElementById('recipient').value;
            const subject = document.getElementById('subject').value.trim();
            const body = document.getElementById('message-body').value.trim();
            const priority = document.querySelector('input[name="priority"]:checked').value;
            
            if (!recipient || !subject || !body) {
                showToast('Please fill in all required fields', 'danger');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('recipient_id', recipient);
            formData.append('subject', subject);
            formData.append('body', body);
            formData.append('priority', priority);
            
            const result = await apiRequest(API_BASE + '/api/messages.php', 'POST', formData);
            
            if (result.success) {
                window.location.href = '?with=' + recipient;
            }
        }
    </script>
</body>
</html>
