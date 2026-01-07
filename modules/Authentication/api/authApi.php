<?php
/**
 * Auth API Endpoint
 * File: modules/Authentication/api/authApi.php
 * Handles all authentication API requests
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Calculate the root path - go up 4 levels from this file's location
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Authentication/controllers/AuthController.php";
require_once $root_path . "/modules/Authentication/models/UserModel.php";

// Get action from query parameter or request body
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Log the request for debugging
error_log("Auth API called with action: " . $action);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $authController = new AuthController();

    switch ($action) {
        case 'login':
            // Get JSON input or form data
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $identifier = $input['identifier'] ?? '';
            $password = $input['password'] ?? '';
            
            if (empty($identifier) || empty($password)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Email/Phone and password are required'
                ]);
                exit;
            }
            
            $result = $authController->login($identifier, $password);
            echo json_encode($result);
            break;

        case 'register':
            // Get input data
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            // Validate required fields
            $required = ['firstname', 'lastname', 'email', 'phone', 'password'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    echo json_encode([
                        'success' => false, 
                        'message' => "Missing required field: $field"
                    ]);
                    exit;
                }
            }
            
            // Default role for registration (Student)
            $input['role_id'] = $input['role_id'] ?? 5;
            $input['status'] = 'pending';
            $input['created_by'] = 1; // Default to super admin
            
            $result = $authController->register($input, $input['created_by']);
            echo json_encode($result);
            break;

        case 'checkEmail':
            // Check if email exists
            $email = $_POST['email'] ?? '';
            if (empty($email)) {
                echo json_encode(['exists' => false]);
                exit;
            }
            
            $userModel = new UserModel(Database::getInstance());
            $exists = $userModel->userExists($email, ''); // Check only email
            echo json_encode(['exists' => $exists]);
            break;

        case 'forgot-password':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $email = $input['email'] ?? '';
            
            if (empty($email)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Email is required'
                ]);
                exit;
            }
            
            $result = $authController->forgotPassword($email);
            echo json_encode($result);
            break;

        case 'reset-password':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $email = $input['email'] ?? '';
            $otp = $input['otp'] ?? '';
            $password = $input['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Email and password are required'
                ]);
                exit;
            }
            
            if (empty($otp)) {
                // Allow OTP-less reset for demo
                echo json_encode([
                    'success' => false, 
                    'message' => 'OTP is required for security'
                ]);
                exit;
            }
            
            $result = $authController->resetPassword($email, $otp, $password);
            echo json_encode($result);
            break;

        case 'verify-otp':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $email = $input['email'] ?? '';
            $otp = $input['otp'] ?? '';
            
            if (empty($email) || empty($otp)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Email and OTP are required'
                ]);
                exit;
            }
            
            $result = $authController->verifyOtp($email, $otp);
            echo json_encode($result);
            break;

        case 'validate':
            $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            } else {
                // Try to get from cookie
                $token = $_COOKIE['auth_token'] ?? $token;
            }
            
            $decoded = $authController->validateToken($token);
            
            if ($decoded) {
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'user_id' => $decoded->user_id,
                        'username' => $decoded->username,
                        'firstname' => $decoded->firstname,
                        'lastname' => $decoded->lastname,
                        'email' => $decoded->email,
                        'role_id' => $decoded->role_id,
                        'role_name' => $decoded->role_name,
                        'is_super_admin' => $decoded->is_super_admin,
                        'permissions' => $decoded->permissions,
                        'photo' => $decoded->photo
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Invalid token'
                ]);
            }
            break;

        case 'logout':
            // Clear session
            session_destroy();
            
            // Clear cookies
            setcookie('auth_token', '', time() - 3600, '/');
            
            $result = $authController->logout();
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid action',
                'available_actions' => [
                    'login', 'register', 'checkEmail', 'forgot-password', 
                    'reset-password', 'verify-otp', 'validate', 'logout'
                ]
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Auth API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>