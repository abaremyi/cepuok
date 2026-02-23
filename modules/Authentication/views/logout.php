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
setcookie('auth_token', '', time() - 3600, '/', '', true, true);
setcookie('refresh_token', '', time() - 3600, '/', '', true, true);

// Clear all cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-3600, '/');
        setcookie($name, '', time()-3600, '/', '', true, true);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - CEP UoK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 90%;
        }
        .logout-icon {
            font-size: 64px;
            color: #764ba2;
            margin-bottom: 20px;
        }
        h2 {
            color: #333;
            margin: 0 0 10px;
            font-size: 24px;
        }
        p {
            color: #666;
            margin: 0 0 20px;
            line-height: 1.6;
        }
        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #764ba2;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .redirect-message {
            color: #888;
            font-size: 14px;
        }
    </style>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        
        // Clear all storage
        localStorage.clear();
        sessionStorage.clear();
        
        // Clear any remaining cookies via JavaScript
        document.cookie.split(";").forEach(function(c) { 
            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
        });
        
        // Redirect to membership page
        setTimeout(function() {
            window.location.href = BASE_URL + '/membership';
        }, 2000);
    </script>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Successfully Logged Out</h2>
        <p>You have been securely logged out of your account.</p>
        <div class="loader"></div>
        <p class="redirect-message">Redirecting to login page...</p>
        <p><a href="<?= BASE_URL ?>/membership" style="color: #764ba2; text-decoration: none;">Click here if not redirected</a></p>
    </div>
</body>
</html>