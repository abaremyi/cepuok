<?php
/**
 * Auth API Endpoint CEPUOK
 * File: modules/Authentication/api/authApi.php
 *
 * FIX: The login case now calls setcookie() so the JWT lands in the browser
 *      before the JS redirect happens.  Without this the cookie was never set
 *      and admin-dashboard.php saw no token → redirect loop back to membership.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Dynamic CORS: reflect the request origin instead of a wildcard so cookies work
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
} else {
    header('Access-Control-Allow-Origin: *');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . '/config/paths.php';
require_once $root_path . '/config/database.php';
require_once $root_path . '/modules/Authentication/controllers/AuthController.php';
require_once $root_path . '/modules/Authentication/models/UserModel.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Helper: resolve cookie domain/path safely ───────────────────────────────
function cookieOptions(int $expires): array {
    return [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

try {
    $authController = new AuthController();

    switch ($action) {

        // ── LOGIN ─────────────────────────────────────────────────────────────
        case 'login':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            $identifier = trim($input['identifier'] ?? '');
            $password   = $input['password'] ?? '';

            if (!$identifier || !$password) {
                echo json_encode(['success' => false, 'message' => 'Email/Phone and password are required']);
                exit;
            }

            $result = $authController->login($identifier, $password);

            // ★ THE KEY FIX ★
            // Set the auth cookie server-side so every subsequent PHP page load
            // finds $_COOKIE['auth_token'] already populated.
            if ($result['success'] && !empty($result['token'])) {
                $jwtTtl   = (int)($_ENV['JWT_EXPIRATION_TIME'] ?? 3600);
                $expires  = time() + $jwtTtl;
                setcookie('auth_token', $result['token'], cookieOptions($expires));

                // Also persist the last-activity timestamp in the PHP session
                $_SESSION['last_activity'] = time();
                $_SESSION['user_id']       = $result['user']['id'] ?? null;
            }

            echo json_encode($result);
            break;

        // ── REGISTER ──────────────────────────────────────────────────────────
        case 'register':
            $input    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $required = ['firstname', 'lastname', 'email', 'phone', 'password'];

            foreach ($required as $field) {
                if (empty($input[$field])) {
                    echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
                    exit;
                }
            }

            $input['role_id']    = $input['role_id']    ?? 5;
            $input['status']     = 'pending';
            $input['created_by'] = 1;

            echo json_encode($authController->register($input, $input['created_by']));
            break;

        // ── CHECK EMAIL ───────────────────────────────────────────────────────
        case 'checkEmail':
            $email     = $_POST['email'] ?? '';
            $userModel = new UserModel(Database::getInstance());
            echo json_encode(['exists' => $email ? $userModel->userExists($email, '') : false]);
            break;

        // ── FORGOT PASSWORD ───────────────────────────────────────────────────
        case 'forgot-password':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $email = trim($input['email'] ?? '');

            if (!$email) {
                echo json_encode(['success' => false, 'message' => 'Email is required']);
                exit;
            }
            echo json_encode($authController->forgotPassword($email));
            break;

        // ── RESET PASSWORD ────────────────────────────────────────────────────
        case 'reset-password':
            $input    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $email    = $input['email']    ?? '';
            $otp      = $input['otp']      ?? '';
            $password = $input['password'] ?? '';

            if (!$email || !$password || !$otp) {
                echo json_encode(['success' => false, 'message' => 'Email, OTP and new password are required']);
                exit;
            }
            echo json_encode($authController->resetPassword($email, $otp, $password));
            break;

        // ── VERIFY OTP ────────────────────────────────────────────────────────
        case 'verify-otp':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $email = $input['email'] ?? '';
            $otp   = $input['otp']   ?? '';

            if (!$email || !$otp) {
                echo json_encode(['success' => false, 'message' => 'Email and OTP are required']);
                exit;
            }
            echo json_encode($authController->verifyOtp($email, $otp));
            break;

        // ── VALIDATE TOKEN ────────────────────────────────────────────────────
        case 'validate':
            $token = '';
            if (isset($_SERVER['HTTP_AUTHORIZATION']) && str_starts_with($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ')) {
                $token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
            }
            $token   = $token ?: ($_COOKIE['auth_token'] ?? '');
            $decoded = $authController->validateToken($token);

            if ($decoded) {
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'user_id'       => $decoded->user_id,
                        'username'      => $decoded->username,
                        'firstname'     => $decoded->firstname,
                        'lastname'      => $decoded->lastname,
                        'email'         => $decoded->email,
                        'role_id'       => $decoded->role_id,
                        'role_name'     => $decoded->role_name,
                        'is_super_admin'=> $decoded->is_super_admin,
                        'permissions'   => $decoded->permissions,
                        'photo'         => $decoded->photo,
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid token']);
            }
            break;

        // ── EXTEND SESSION ────────────────────────────────────────────────────
        case 'extend-session':
        case 'heartbeat':
            // Re-issue the token with a fresh expiry so the cookie stays alive
            $token   = $_COOKIE['auth_token'] ?? '';
            $decoded = $authController->validateToken($token);

            if ($decoded) {
                $jwtTtl  = (int)($_ENV['JWT_EXPIRATION_TIME'] ?? 3600);
                $expires = time() + $jwtTtl;
                $payload = (array)$decoded;
                $payload['iat'] = time();
                $payload['exp'] = $expires;

                require_once $root_path . '/helpers/JWTHandler.php';
                $newToken = (new JWTHandler())->generateToken($payload);

                setcookie('auth_token', $newToken, cookieOptions($expires));
                $_SESSION['last_activity'] = time();

                echo json_encode(['success' => true, 'message' => 'Session extended']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Token invalid or expired']);
            }
            break;

        // ── LOGOUT ────────────────────────────────────────────────────────────
        case 'logout':
            session_unset();
            session_destroy();
            setcookie('auth_token', '', cookieOptions(time() - 3600));
            echo json_encode(['success' => true, 'message' => 'Logged out']);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action',
                'available_actions' => [
                    'login', 'register', 'checkEmail', 'forgot-password',
                    'reset-password', 'verify-otp', 'validate',
                    'extend-session', 'heartbeat', 'logout',
                ],
            ]);
    }

} catch (Exception $e) {
    error_log('Auth API Exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error'   => $e->getMessage(),
    ]);
}