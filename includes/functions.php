<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

require_once __DIR__ . '/config.php';

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Format currency
 */
function formatSalary($min, $max, $currency = 'USD') {
    if (!$min && !$max) {
        return 'Competitive';
    }
    
    $formatter = function($amount) use ($currency) {
        return '$' . number_format($amount);
    };
    
    if ($min && $max) {
        return $formatter($min) . ' - ' . $formatter($max);
    }
    return $min ? 'From ' . $formatter($min) : 'Up to ' . $formatter($max);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Time ago format
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Generate random string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get experience level label
 */
function getExperienceLevelLabel($level) {
    $labels = [
        'entry' => 'Entry Level',
        'junior' => 'Junior',
        'mid' => 'Mid Level',
        'senior' => 'Senior',
        'lead' => 'Lead',
        'executive' => 'Executive'
    ];
    return $labels[$level] ?? $level;
}

/**
 * Get job type label
 */
function getJobTypeLabel($type) {
    return ucwords(str_replace('-', ' ', $type));
}

/**
 * Get remote label
 */
function getRemoteLabel($remote) {
    $labels = [
        'on-site' => '🏢 On-site',
        'remote' => '🏠 Remote',
        'hybrid' => '🔄 Hybrid'
    ];
    return $labels[$remote] ?? $remote;
}

/**
 * Get company size label
 */
function getCompanySizeLabel($size) {
    return $size . ' employees';
}

/**
 * Get application status badge class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'bg-warning',
        'reviewed' => 'bg-info',
        'shortlisted' => 'bg-primary',
        'interview' => 'bg-primary',
        'offered' => 'bg-success',
        'hired' => 'bg-success',
        'rejected' => 'bg-danger'
    ];
    return $classes[$status] ?? 'bg-secondary';
}

/**
 * Validate file upload for CV
 */
function validateCVUpload($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'File upload failed'];
    }
    
    if ($file['size'] > MAX_CV_SIZE) {
        return ['valid' => false, 'message' => 'File size exceeds maximum limit (5MB)'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_CV_TYPES)) {
        return ['valid' => false, 'message' => 'Invalid file type. Allowed: PDF, DOC, DOCX'];
    }
    
    return ['valid' => true];
}

/**
 * Upload CV file
 */
function uploadCV($file, $userId) {
    $validation = validateCVUpload($file);
    if (!$validation['valid']) {
        return $validation;
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'cv_' . $userId . '_' . time() . '.' . $ext;
    $destination = UPLOAD_PATH . 'cv/' . $filename;
    
    if (!is_dir(UPLOAD_PATH . 'cv/')) {
        mkdir(UPLOAD_PATH . 'cv/', 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['valid' => true, 'path' => 'assets/uploads/cv/' . $filename];
    }
    
    return ['valid' => false, 'message' => 'Failed to save file'];
}

/**
 * Calculate AI match score between user and job
 */
function calculateMatchScore($user, $job) {
    $score = 0;
    $maxScore = 100;
    
    // Skills matching (40% weight)
    $userSkills = is_array($user['skills']) ? $user['skills'] : json_decode($user['skills'] ?? '[]', true);
    $jobSkills = is_array($job['skills']) ? $job['skills'] : json_decode($job['skills'] ?? '[]', true);
    
    if (!empty($jobSkills) && !empty($userSkills)) {
        $matchedSkills = 0;
        foreach ($userSkills as $userSkill) {
            foreach ($jobSkills as $jobSkill) {
                if (stripos($userSkill, $jobSkill) !== false || stripos($jobSkill, $userSkill) !== false) {
                    $matchedSkills++;
                    break;
                }
            }
        }
        $score += ($matchedSkills / count($jobSkills)) * 40;
    }
    
    // Experience level matching (25% weight)
    $userExperience = is_array($user['experience']) ? $user['experience'] : json_decode($user['experience'] ?? '[]', true);
    $totalYears = calculateTotalExperience($userExperience);
    
    $levelYears = [
        'entry' => 0, 'junior' => 1, 'mid' => 3,
        'senior' => 5, 'lead' => 8, 'executive' => 10
    ];
    
    $requiredYears = $levelYears[$job['experience_level']] ?? 0;
    if ($totalYears >= $requiredYears) {
        $score += 25;
    } elseif ($totalYears >= $requiredYears - 1) {
        $score += 17.5;
    } elseif ($totalYears >= $requiredYears - 2) {
        $score += 10;
    }
    
    // Interests matching (20% weight)
    $userInterests = is_array($user['interests']) ? $user['interests'] : json_decode($user['interests'] ?? '[]', true);
    if (!empty($userInterests)) {
        foreach ($userInterests as $interest) {
            if (stripos($job['department'] ?? '', $interest) !== false || 
                stripos($job['title'] ?? '', $interest) !== false) {
                $score += 20;
                break;
            }
        }
    }
    
    // Location matching (15% weight)
    if ($job['remote'] === 'remote') {
        $score += 15;
    } elseif (!empty($user['city']) && !empty($job['location'])) {
        if (stripos($job['location'], $user['city']) !== false) {
            $score += 15;
        } elseif ($job['remote'] === 'hybrid') {
            $score += 7.5;
        }
    }
    
    return round(min($score, $maxScore), 2);
}

/**
 * Calculate total years of experience
 */
function calculateTotalExperience($experience) {
    if (empty($experience)) return 0;
    
    $totalMonths = 0;
    $now = new DateTime();
    
    foreach ($experience as $exp) {
        if (empty($exp['startDate'])) continue;
        
        $start = new DateTime($exp['startDate']);
        $end = !empty($exp['current']) ? $now : (isset($exp['endDate']) ? new DateTime($exp['endDate']) : $now);
        
        $diff = $start->diff($end);
        $totalMonths += ($diff->y * 12) + $diff->m;
    }
    
    return floor($totalMonths / 12);
}

/**
 * Pagination helper
 */
function paginate($total, $perPage, $currentPage) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => ($currentPage - 1) * $perPage,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Log activity
 */
function logActivity($userId, $action, $entityType = null, $entityId = null, $details = null) {
    $db = Database::getInstance();
    $db->insert('activity_log', [
        'user_id' => $userId,
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'details' => $details ? json_encode($details) : null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}
