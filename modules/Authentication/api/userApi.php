<?php
/**
 * User Management API Endpoint
 * File: modules/Authentication/api/userApi.php
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Calculate the root path
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/helpers/AuthMiddleware.php";
require_once $root_path . "/modules/Authentication/controllers/UserController.php";

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Log the request for debugging
error_log("User API called with action: " . $action);

// Authenticate user for all actions
$auth = new AuthMiddleware();

try {
    // Public actions (if any) - none for user management
    $userController = new UserController();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Handle GET requests
            switch ($action) {
                case 'list':
                    // Require view permission
                    $currentUser = $auth->requireAuth(['users.view']);
                    
                    $filters = [];
                    if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
                    if (isset($_GET['role_id'])) $filters['role_id'] = $_GET['role_id'];
                    if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
                    
                    $users = $userController->index($filters);
                    echo json_encode(['success' => true, 'data' => $users]);
                    break;
                    
                case 'get':
                    // Require view permission
                    $currentUser = $auth->requireAuth(['users.view']);
                    
                    if (!isset($_GET['id'])) {
                        throw new Exception('User ID required');
                    }
                    
                    $user = $userController->show($_GET['id']);
                    if ($user) {
                        unset($user['password']); // Remove sensitive data
                    }
                    echo json_encode(['success' => true, 'data' => $user]);
                    break;
                    
                case 'stats':
                    // Require view permission
                    $currentUser = $auth->requireAuth(['users.view']);
                    
                    $stats = $userController->getStats();
                    echo json_encode(['success' => true, 'data' => $stats]);
                    break;
                    
                case 'roles':
                    $currentUser = $auth->requireAuth(['users.view']);
                    $roles = $userController->getRoles();
                    echo json_encode(['success' => true, 'data' => $roles]);
                    break;
                    
                case 'available-members':
                    $currentUser = $auth->requireAuth(['users.create', 'users.edit']);
                    $members = $userController->getAvailableMembers();
                    echo json_encode(['success' => true, 'data' => $members]);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        case 'POST':
            // Handle POST requests
            switch ($action) {
                case 'create':
                    // Require create permission
                    $currentUser = $auth->requireAuth(['users.create']);
                    
                    $result = $userController->store($_POST, $_FILES);
                    echo json_encode($result);
                    break;
                    
                case 'update':
                    // Require edit permission
                    $currentUser = $auth->requireAuth(['users.edit']);
                    
                    if (!isset($_GET['id'])) {
                        throw new Exception('User ID required');
                    }
                    
                    $result = $userController->update($_GET['id'], $_POST, $_FILES);
                    echo json_encode($result);
                    break;
                    
                case 'update-status':
                    // Require edit permission
                    $currentUser = $auth->requireAuth(['users.edit']);
                    
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!$input) {
                        $input = $_POST;
                    }
                    
                    if (!isset($input['user_id']) || !isset($input['status'])) {
                        throw new Exception('User ID and status required');
                    }
                    
                    $result = $userController->updateStatus($input['user_id'], $input['status']);
                    echo json_encode($result);
                    break;
                    
                case 'change-role':
                    // Require change_role permission
                    $currentUser = $auth->requireAuth(['users.change_role']);
                    
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!$input) {
                        $input = $_POST;
                    }
                    
                    if (!isset($input['user_id']) || !isset($input['role_id'])) {
                        throw new Exception('User ID and role ID required');
                    }
                    
                    $result = $userController->update($input['user_id'], ['role_id' => $input['role_id']]);
                    echo json_encode($result);
                    break;
                    
                case 'bulk-delete':
                    // Require delete permission
                    $currentUser = $auth->requireAuth(['users.delete']);
                    
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!$input) {
                        $input = $_POST;
                    }
                    
                    if (!isset($input['user_ids']) || !is_array($input['user_ids'])) {
                        throw new Exception('User IDs required');
                    }
                    
                    $results = [];
                    foreach ($input['user_ids'] as $userId) {
                        $results[] = $userController->destroy($userId);
                    }
                    
                    echo json_encode(['success' => true, 'message' => 'Users deleted successfully']);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        case 'DELETE':
            // Handle DELETE requests
            switch ($action) {
                case 'delete':
                    // Require delete permission
                    $currentUser = $auth->requireAuth(['users.delete']);
                    
                    $input = json_decode(file_get_contents('php://input'), true);
                    $userId = $_GET['id'] ?? $input['user_id'] ?? null;
                    
                    if (!$userId) {
                        throw new Exception('User ID required');
                    }
                    
                    $result = $userController->destroy($userId);
                    echo json_encode($result);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}