<?php
/**
 * Role Management API Endpoint
 * File: modules/Authentication/api/roleApi.php
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
require_once $root_path . "/modules/Authentication/controllers/RoleController.php";

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Log the request for debugging
error_log("Role API called with action: " . $action);

// Authenticate user for all actions
$auth = new AuthMiddleware();

try {
    $roleController = new RoleController();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Handle GET requests
            switch ($action) {
                case 'list':
                    // Require view permission
                    $currentUser = $auth->requireAuth(['roles.view']);
                    
                    $roles = $roleController->index();
                    echo json_encode(['success' => true, 'data' => $roles]);
                    break;
                    
                case 'get':
                    // Require view permission
                    $currentUser = $auth->requireAuth(['roles.view']);
                    
                    if (!isset($_GET['id'])) {
                        throw new Exception('Role ID required');
                    }
                    
                    $role = $roleController->show($_GET['id']);
                    echo json_encode(['success' => true, 'data' => $role]);
                    break;
                    
                case 'permissions':
                    // Require view permission
                    $currentUser = $auth->requireAuth(['roles.view']);
                    
                    $permissions = $roleController->getPermissions();
                    echo json_encode(['success' => true, 'data' => $permissions]);
                    break;
                    
                case 'role-permissions':
                    // Require view permission
                    $currentUser = $auth->requireAuth(['roles.view']);
                    
                    if (!isset($_GET['role_id'])) {
                        throw new Exception('Role ID required');
                    }
                    
                    $permissions = $roleController->getRolePermissions($_GET['role_id']);
                    echo json_encode(['success' => true, 'data' => $permissions]);
                    break;
                    
                case 'stats':
                    // Require view permission
                    $currentUser = $auth->requireAuth(['roles.view']);
                    
                    $stats = $roleController->getStats();
                    echo json_encode(['success' => true, 'data' => $stats]);
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
                    $currentUser = $auth->requireAuth(['roles.create']);
                    
                    $result = $roleController->store($_POST);
                    echo json_encode($result);
                    break;
                    
                case 'update':
                    // Require edit permission
                    $currentUser = $auth->requireAuth(['roles.edit']);
                    
                    if (!isset($_GET['id'])) {
                        throw new Exception('Role ID required');
                    }
                    
                    $result = $roleController->update($_GET['id'], $_POST);
                    echo json_encode($result);
                    break;
                    
                case 'update-permissions':
                    // Require assign_permissions permission
                    $currentUser = $auth->requireAuth(['roles.assign_permissions']);
                    
                    if (!isset($_GET['role_id'])) {
                        throw new Exception('Role ID required');
                    }
                    
                    $permissionIds = $_POST['permissions'] ?? [];
                    $result = $roleController->updateRolePermissions($_GET['role_id'], $permissionIds);
                    echo json_encode($result);
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
                    $currentUser = $auth->requireAuth(['roles.delete']);
                    
                    $input = json_decode(file_get_contents('php://input'), true);
                    $roleId = $_GET['id'] ?? $input['id'] ?? null;
                    
                    if (!$roleId) {
                        throw new Exception('Role ID required');
                    }
                    
                    $result = $roleController->destroy($roleId);
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