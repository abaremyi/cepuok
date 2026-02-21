<?php
session_start();

// Clear all session data
$_SESSION = [];

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear cookies
setcookie('auth_token', '', time() - 3600, '/');
setcookie('refresh_token', '', time() - 3600, '/');

// Clear localStorage via JavaScript and redirect
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Mount Carmel School</title>
    <script>
        // Clear localStorage
        localStorage.removeItem('auth_token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('user');
        
        // Redirect to login page after 1 second
        setTimeout(function() {
            window.location.href = "<?= url('membership')?>";
        }, 1000);
    </script>
</head>
<body>
    <div style="text-align: center; margin-top: 50px;">
        <h2>Logging out...</h2>
        <p>You are being redirected to the login page.</p>
    </div>
</body>
</html>