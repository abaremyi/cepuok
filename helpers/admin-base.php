<?php
/**
 * Admin Page Base Guard
 * File: helpers/admin-base.php
 *
 * ─────────────────────────────────────────────────────────────────
 * PURPOSE
 * ─────────────────────────────────────────────────────────────────
 * Every admin view does the exact same three things before rendering:
 *   1. Validate the JWT cookie (redirect to login if missing/expired)
 *   2. Check the user has the required permission(s)
 *   3. Expose $currentUser and $userPermissions to the view
 *   4. Always BASE url initialised and available as BASE_URL
 *
 * Instead of copy-pasting 40 lines of boilerplate into every page,
 * each admin view does ONE include:
 *
 *     <?php
 *     $requiredPermission = 'users.view';   // or [] for any logged-in user
 *     $pageTitle          = 'Users';
 *     require_once dirname(__DIR__, 4) . '/helpers/admin-base.php';
 *     ?>
 *     ... your HTML ...
 *
 * ─────────────────────────────────────────────────────────────────
 * CONTRACT
 * ─────────────────────────────────────────────────────────────────
 * Caller MUST define before including this file:
 *   $pageTitle (string)          - Used in <title> and page header
 *
 * Caller MAY define:
 *   $requiredPermission (string|array|null)
 *       - string  : single permission key required (e.g. 'users.view')
 *       - array   : ALL listed permissions required
 *       - null/'' : any authenticated user can access
 *
 * After this file, the view has access to:
 *   $currentUser      (stdClass)  - Decoded JWT payload
 *   $userPermissions  (array)     - Flat array of permission strings
 *   $isSuperAdmin     (bool)      - Whether user is super admin
 *   $pageTitle        (string)    - As passed in
 */

// ── Guard: this file must be called from a page with ROOT_PATH available ──────
if (!defined('ROOT_PATH')) {
    // Try to self-discover root (goes up until we find config/paths.php)
    $guessRoot = __DIR__;
    for ($i = 0; $i < 6; $i++) {
        if (file_exists($guessRoot . '/config/paths.php')) {
            require_once $guessRoot . '/config/paths.php';
            break;
        }
        $guessRoot = dirname($guessRoot);
    }
    if (!defined('ROOT_PATH')) {
        die('ROOT_PATH not defined. Include config/paths.php before admin-base.php.');
    }
}

require_once get_helper('AuthMiddleware');
require_once get_helper('PermissionHelper');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Normalise required permissions to an array ────────────────────────────────
$_adminBasePerms = [];
if (!empty($requiredPermission)) {
    $_adminBasePerms = is_array($requiredPermission)
        ? $requiredPermission
        : [$requiredPermission];
}

// ── Authenticate ──────────────────────────────────────────────────────────────
$_auth = new AuthMiddleware();

// No cookie at all → go to login immediately
$_token = $_COOKIE['auth_token'] ?? '';
if (!$_token) {
    header('Location: ' . url('membership'));
    exit;
}

// Validate + check permissions
try {
    $currentUser = $_auth->requireAuth($_adminBasePerms);
} catch (Exception $e) {
    // Invalid/expired token
    setcookie('auth_token', '', time() - 3600, '/');
    header('Location: ' . url('membership'));
    exit;
}

// ── Server-side session activity check ───────────────────────────────────────
$_sessionTimeout = 1800; // 30 min
if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > $_sessionTimeout
) {
    // Expired server-side too → full logout
    session_unset();
    session_destroy();
    setcookie('auth_token', '', time() - 3600, '/');
    header('Location: ' . url('membership'));
    exit;
}
$_SESSION['last_activity'] = time();

// ── Expose clean variables to the view ───────────────────────────────────────
$userPermissions = $currentUser->permissions ?? [];

error_log("admin-base.php - User is_super_admin from token: " . 
          (!empty($currentUser->is_super_admin) ? 'true' : 'false'));
error_log("admin-base.php - User role_name: " . ($currentUser->role_name ?? 'unknown'));

// Check if user is super admin - use multiple conditions
$isSuperAdmin = !empty($currentUser->is_super_admin) || 
                (isset($currentUser->role_name) && $currentUser->role_name === 'Super Admin') ||
                (isset($currentUser->is_super_admin) && $currentUser->is_super_admin === true);

error_log("admin-base.php - Computed isSuperAdmin: " . ($isSuperAdmin ? 'true' : 'false'));

// Derived display helpers (available in every view)
$userFullName = trim(
    htmlspecialchars(($currentUser->firstname ?? '') . ' ' . ($currentUser->lastname ?? ''))
);
if ($userFullName === '') {
    $userFullName = htmlspecialchars($currentUser->username ?? 'User');
}

$userInitials = strtoupper(
    substr($currentUser->firstname ?? '', 0, 1) .
    substr($currentUser->lastname  ?? '', 0, 1)
);
if ($userInitials === '') $userInitials = 'U';

$userPhoto = $currentUser->photo ?? '';

error_log("Current User ID: " . ($currentUser->user_id ?? 'N/A'));
error_log("Current User Session from JWT: " . ($currentUser->session_type ?? 'N/A'));

// Page title fallback
if (empty($pageTitle)) $pageTitle = 'Admin';

// currentPage: used by the sidebar to highlight the active link.
// The caller can set $currentPage manually; if not, we derive it from the URL.
if (empty($currentPage)) {
    $currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
}

unset($_adminBasePerms, $_token, $_auth, $_sessionTimeout);

// Make BASE_URL available in JavaScript
echo '<script>window.BASE_URL = "' . BASE_URL . '";</script>';
?>