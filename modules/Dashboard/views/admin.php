<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get root path
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Admin Dashboard</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .spinner {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #764ba2;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="spinner"></div>
    
    <script>
        const BASE_URL = '<?php echo url(); ?>';
        const API_URL = BASE_URL + '/api/auth';
        
        async function checkAuthAndRedirect() {
            const token = localStorage.getItem('auth_token');
            
            if (!token) {
                // No token found, redirect to login
                window.location.href = BASE_URL + '/login';
                return;
            }
            
            try {
                // Validate token
                const response = await fetch(`${API_URL}?action=validate`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Set cookie for PHP access
                    document.cookie = `auth_token=${token}; path=/; max-age=${24*60*60}`;
                    
                    // Check role and redirect
                    if (result.user.is_super_admin || result.user.role_id === 1) {
                        window.location.href = BASE_URL + '/admin/dashboard';
                    } else if (result.user.role_id === 2) {
                        window.location.href = BASE_URL + '/admin/dashboard';
                    } else {
                        window.location.href = BASE_URL + '/admin/dashboard';
                    }
                } else {
                    // Invalid token
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    window.location.href = BASE_URL + '/login';
                }
            } catch (error) {
                console.error('Auth check failed:', error);
                window.location.href = BASE_URL + '/login';
            }
        }
        
        // Run on page load
        checkAuthAndRedirect();
    </script>
</body>
</html>