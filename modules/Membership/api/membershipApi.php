<?php
/**
 * Membership API Endpoint
 * File: modules/Membership/api/membershipApi.php
 * Handles all membership API requests
 */

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

// Calculate the root path - go up 4 levels from this file's location
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Membership/controllers/MembershipController.php";
require_once $root_path . "/helpers/AuthMiddleware.php";

// Get action from query parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Log the request for debugging
error_log("Membership API called with action: " . $action);

try {
    $membershipController = new MembershipController();
    $authMiddleware = new AuthMiddleware();

    switch ($action) {
        case 'register':
            // Public endpoint - anyone can register
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $result = $membershipController->register($input);
            echo json_encode($result);
            break;

        case 'checkEmail':
            // Public endpoint - check if email exists
            $email = $_GET['email'] ?? $_POST['email'] ?? '';
            
            if (empty($email)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email is required'
                ]);
                exit;
            }
            
            $result = $membershipController->checkEmail($email);
            echo json_encode($result);
            break;

        case 'getMembershipTypes':
            // Public endpoint - get membership types
            $result = $membershipController->getMembershipTypes();
            echo json_encode($result);
            break;

        case 'getChurches':
            // Public endpoint - get churches
            $result = $membershipController->getChurches();
            echo json_encode($result);
            break;

        case 'getTalents':
            // Public endpoint - get talents
            $result = $membershipController->getTalents();
            echo json_encode($result);
            break;

        case 'get':
            // Requires authentication - get member by ID
            $user = $authMiddleware->requireAuth(['membership.view']);
            
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Valid member ID is required'
                ]);
                exit;
            }
            
            $result = $membershipController->getMember($id);
            echo json_encode($result);
            break;

        case 'list':
            // Requires authentication - get all members with filters
            $user = $authMiddleware->requireAuth(['membership.view']);
            
            $filters = [];
            
            if (isset($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }
            if (isset($_GET['membership_type_id'])) {
                $filters['membership_type_id'] = (int)$_GET['membership_type_id'];
            }
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['year_joined'])) {
                $filters['year_joined'] = (int)$_GET['year_joined'];
            }
            if (isset($_GET['church_id'])) {
                $filters['church_id'] = (int)$_GET['church_id'];
            }
            if (isset($_GET['gender'])) {
                $filters['gender'] = $_GET['gender'];
            }
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
            
            $result = $membershipController->getAllMembers($filters, $page, $perPage);
            echo json_encode($result);
            break;

        case 'update':
            // Requires authentication - update member
            $user = $authMiddleware->requireAuth(['membership.edit']);
            
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Valid member ID is required'
                ]);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $result = $membershipController->updateMember($id, $input);
            echo json_encode($result);
            break;

        case 'approve':
            // Requires authentication - approve member
            $user = $authMiddleware->requireAuth(['membership.approve']);
            
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Valid member ID is required'
                ]);
                exit;
            }
            
            $result = $membershipController->approveMember($id, $user->user_id);
            echo json_encode($result);
            break;

        case 'delete':
            // Requires authentication - delete member
            $user = $authMiddleware->requireAuth(['membership.delete']);
            
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Valid member ID is required'
                ]);
                exit;
            }
            
            $result = $membershipController->deleteMember($id);
            echo json_encode($result);
            break;

        case 'statistics':
            // Requires authentication - get statistics
            $user = $authMiddleware->requireAuth(['membership.view']);
            
            $result = $membershipController->getStatistics();
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action',
                'available_actions' => [
                    'Public: register, checkEmail, getMembershipTypes, getChurches, getTalents',
                    'Protected: get, list, update, approve, delete, statistics'
                ]
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Membership API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'error' => $e->getMessage()
    ]);
}