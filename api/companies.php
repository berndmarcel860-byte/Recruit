<?php
/**
 * Companies API
 * Handles company listings via AJAX
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
        $industry = sanitize($_GET['industry'] ?? '');
        
        $where = "is_active = 1";
        $params = [];
        
        if ($search) {
            $where .= " AND (name LIKE :search OR description LIKE :search)";
            $params['search'] = "%$search%";
        }
        if ($industry) {
            $where .= " AND industry = :industry";
            $params['industry'] = $industry;
        }
        
        // Count total
        $total = $db->fetch("SELECT COUNT(*) as count FROM companies WHERE $where", $params)['count'];
        $pagination = paginate($total, $limit, $page);
        
        // Get companies
        $sql = "SELECT * FROM companies WHERE $where ORDER BY name ASC LIMIT {$pagination['offset']}, $limit";
        $companies = $db->fetchAll($sql, $params);
        
        // Get job counts for each company
        foreach ($companies as &$company) {
            $jobCount = $db->fetch(
                "SELECT COUNT(*) as count FROM jobs WHERE company_id = :id AND status = 'active'",
                ['id' => $company['id']]
            );
            $company['job_count'] = $jobCount['count'];
        }
        
        jsonResponse([
            'success' => true,
            'companies' => $companies,
            'pagination' => $pagination
        ]);
        break;
        
    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Company ID is required'], 400);
        }
        
        $company = $db->fetch("SELECT * FROM companies WHERE id = :id AND is_active = 1", ['id' => $id]);
        
        if (!$company) {
            jsonResponse(['success' => false, 'message' => 'Company not found'], 404);
        }
        
        // Get company jobs
        $jobs = $db->fetchAll(
            "SELECT * FROM jobs WHERE company_id = :id AND status = 'active' ORDER BY created_at DESC",
            ['id' => $id]
        );
        
        foreach ($jobs as &$job) {
            $job['skills'] = json_decode($job['skills'] ?? '[]', true);
            $job['benefits'] = json_decode($job['benefits'] ?? '[]', true);
        }
        
        $company['jobs'] = $jobs;
        
        jsonResponse(['success' => true, 'company' => $company]);
        break;
        
    case 'industries':
        $industries = $db->fetchAll(
            "SELECT DISTINCT industry FROM companies WHERE is_active = 1 AND industry IS NOT NULL ORDER BY industry"
        );
        jsonResponse(['success' => true, 'industries' => array_column($industries, 'industry')]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
