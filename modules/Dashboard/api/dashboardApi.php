<?php
/**
 * Dashboard API Endpoint
 * File: modules/Dashboard/api/dashboardApi.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include required files
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/helpers/AuthMiddleware.php';

// Authenticate request
$auth = new AuthMiddleware();
$user = $auth->requireAuth(['dashboard.view']);

// Get action from query parameter
$action = $_GET['action'] ?? '';

try {
    $db = Database::getConnection();
    
    switch ($action) {
        case 'getStats':
            // Get total users
            $stmt = $db->query("SELECT COUNT(*) as total FROM users");
            $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get total members
            $stmt = $db->query("SELECT COUNT(*) as total FROM members");
            $totalMembers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get pending members
            $stmt = $db->query("SELECT COUNT(*) as total FROM members WHERE status = 'pending'");
            $pendingMembers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get today's visitors (users who logged in today)
            $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE DATE(last_login) = CURDATE()");
            $todayVisitors = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total_users' => (int)$totalUsers,
                    'total_members' => (int)$totalMembers,
                    'pending_members' => (int)$pendingMembers,
                    'today_visitors' => (int)$todayVisitors
                ]
            ]);
            break;
            
        case 'getRecentUsers':
            // Get recent users with their roles
            $query = "SELECT u.*, r.name as role_name 
                      FROM users u
                      LEFT JOIN roles r ON u.role_id = r.id
                      ORDER BY u.created_at DESC
                      LIMIT 10";
            $stmt = $db->query($query);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $users
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred'
    ]);
}