<?php
/**
 * Jobs API
 * Handles job listings, search, applications via AJAX
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = Database::getInstance();

switch ($action) {
    case 'list':
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 12), 100);
        $search = sanitize($_GET['search'] ?? '');
        $type = sanitize($_GET['type'] ?? '');
        $remote = sanitize($_GET['remote'] ?? '');
        $experienceLevel = sanitize($_GET['experience_level'] ?? '');
        $location = sanitize($_GET['location'] ?? '');
        $companyId = (int)($_GET['company_id'] ?? 0);
        
        $where = "j.status = 'active'";
        $params = [];
        
        if ($search) {
            $where .= " AND (j.title LIKE :search OR j.description LIKE :search)";
            $params['search'] = "%$search%";
        }
        if ($type) {
            $where .= " AND j.type = :type";
            $params['type'] = $type;
        }
        if ($remote) {
            $where .= " AND j.remote = :remote";
            $params['remote'] = $remote;
        }
        if ($experienceLevel) {
            $where .= " AND j.experience_level = :experience_level";
            $params['experience_level'] = $experienceLevel;
        }
        if ($location) {
            $where .= " AND j.location LIKE :location";
            $params['location'] = "%$location%";
        }
        if ($companyId) {
            $where .= " AND j.company_id = :company_id";
            $params['company_id'] = $companyId;
        }
        
        // Count total
        $total = $db->fetch("SELECT COUNT(*) as count FROM jobs j WHERE $where", $params)['count'];
        $pagination = paginate($total, $limit, $page);
        
        // Get jobs
        $sql = "SELECT j.*, c.name as company_name, c.logo as company_logo, c.industry as company_industry
                FROM jobs j
                LEFT JOIN companies c ON j.company_id = c.id
                WHERE $where
                ORDER BY j.created_at DESC
                LIMIT {$pagination['offset']}, $limit";
        
        $jobs = $db->fetchAll($sql, $params);
        
        // Parse JSON fields
        foreach ($jobs as &$job) {
            $job['skills'] = json_decode($job['skills'] ?? '[]', true);
            $job['requirements'] = json_decode($job['requirements'] ?? '[]', true);
            $job['benefits'] = json_decode($job['benefits'] ?? '[]', true);
        }
        
        jsonResponse([
            'success' => true,
            'jobs' => $jobs,
            'pagination' => $pagination
        ]);
        break;
        
    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Job ID is required'], 400);
        }
        
        $sql = "SELECT j.*, c.name as company_name, c.logo as company_logo, 
                       c.industry as company_industry, c.description as company_description,
                       c.headquarters, c.size as company_size, c.website as company_website
                FROM jobs j
                LEFT JOIN companies c ON j.company_id = c.id
                WHERE j.id = :id";
        
        $job = $db->fetch($sql, ['id' => $id]);
        
        if (!$job) {
            jsonResponse(['success' => false, 'message' => 'Job not found'], 404);
        }
        
        // Increment views
        $db->query("UPDATE jobs SET views = views + 1 WHERE id = :id", ['id' => $id]);
        
        // Parse JSON fields
        $job['skills'] = json_decode($job['skills'] ?? '[]', true);
        $job['requirements'] = json_decode($job['requirements'] ?? '[]', true);
        $job['responsibilities'] = json_decode($job['responsibilities'] ?? '[]', true);
        $job['benefits'] = json_decode($job['benefits'] ?? '[]', true);
        
        jsonResponse(['success' => true, 'job' => $job]);
        break;
        
    case 'recommendations':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        
        $user = getCurrentUser();
        $limit = min((int)($_GET['limit'] ?? 10), 20);
        
        // Get active jobs
        $sql = "SELECT j.*, c.name as company_name, c.logo as company_logo, c.industry as company_industry
                FROM jobs j
                LEFT JOIN companies c ON j.company_id = c.id
                WHERE j.status = 'active'
                ORDER BY j.created_at DESC
                LIMIT 100";
        
        $jobs = $db->fetchAll($sql);
        
        // Calculate match scores
        $recommendations = [];
        foreach ($jobs as $job) {
            $job['skills'] = json_decode($job['skills'] ?? '[]', true);
            $score = calculateMatchScore($user, $job);
            if ($score > 20) {
                $job['match_score'] = $score;
                $recommendations[] = $job;
            }
        }
        
        // Sort by match score
        usort($recommendations, function($a, $b) {
            return $b['match_score'] - $a['match_score'];
        });
        
        $recommendations = array_slice($recommendations, 0, $limit);
        
        jsonResponse(['success' => true, 'recommendations' => $recommendations]);
        break;
        
    case 'apply':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        
        $jobId = (int)($_POST['job_id'] ?? 0);
        $coverLetter = sanitize($_POST['cover_letter'] ?? '');
        $source = sanitize($_POST['source'] ?? 'manual');
        
        if (!$jobId) {
            jsonResponse(['success' => false, 'message' => 'Job ID is required'], 400);
        }
        
        // Check if job exists and is active
        $job = $db->fetch("SELECT * FROM jobs WHERE id = :id AND status = 'active'", ['id' => $jobId]);
        if (!$job) {
            jsonResponse(['success' => false, 'message' => 'Job not found or no longer active'], 404);
        }
        
        // Check if already applied
        $existing = $db->fetch(
            "SELECT id FROM applications WHERE user_id = :user_id AND job_id = :job_id",
            ['user_id' => $_SESSION['user_id'], 'job_id' => $jobId]
        );
        
        if ($existing) {
            jsonResponse(['success' => false, 'message' => 'You have already applied for this job'], 400);
        }
        
        // Calculate match score
        $user = getCurrentUser();
        $job['skills'] = json_decode($job['skills'] ?? '[]', true);
        $matchScore = calculateMatchScore($user, $job);
        
        // Create application
        $applicationId = $db->insert('applications', [
            'user_id' => $_SESSION['user_id'],
            'job_id' => $jobId,
            'cover_letter' => $coverLetter,
            'cv_path' => $user['cv_path'],
            'match_score' => $matchScore,
            'source' => $source,
            'status' => 'pending'
        ]);
        
        // Increment applications count
        $db->query("UPDATE jobs SET applications_count = applications_count + 1 WHERE id = :id", ['id' => $jobId]);
        
        logActivity($_SESSION['user_id'], 'job_application', 'application', $applicationId, ['job_id' => $jobId]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Application submitted successfully',
            'application_id' => $applicationId,
            'match_score' => $matchScore
        ], 201);
        break;
        
    case 'my_applications':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        
        $sql = "SELECT a.*, j.title as job_title, j.location, j.type as job_type,
                       c.name as company_name, c.logo as company_logo
                FROM applications a
                JOIN jobs j ON a.job_id = j.id
                LEFT JOIN companies c ON j.company_id = c.id
                WHERE a.user_id = :user_id
                ORDER BY a.created_at DESC";
        
        $applications = $db->fetchAll($sql, ['user_id' => $_SESSION['user_id']]);
        
        jsonResponse(['success' => true, 'applications' => $applications]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
