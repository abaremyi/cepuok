<?php
require_once __DIR__ . '/JWTHandler.php';

class AuthMiddleware {
    private $jwtHandler;
    
    public function __construct() {
        $this->jwtHandler = new JWTHandler();
    }
    
    public function authenticate($requiredPermissions = [], $requireSuperAdmin = false) {
        // Get token from cookie or Authorization header
        $token = $_COOKIE['auth_token'] ?? '';
        
        if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            if (strpos($authHeader, 'Bearer ') === 0) {
                $token = substr($authHeader, 7);
            }
        }
        
        if (!$token) {
            return ['authenticated' => false, 'message' => 'No token provided'];
        }
        
        // Validate token
        $decoded = $this->jwtHandler->validateToken($token);
        
        if (!$decoded) {
            return ['authenticated' => false, 'message' => 'Invalid token'];
        }
        
        // Check if user is active
        if (isset($decoded->account_status) && $decoded->account_status !== 'active') {
            return ['authenticated' => false, 'message' => 'Account is ' . $decoded->account_status];
        }
        
        // Check if super admin is required
        if ($requireSuperAdmin && !$decoded->is_super_admin) {
            return ['authenticated' => false, 'message' => 'Super admin access required'];
        }
        
        // Check permissions
        if (!empty($requiredPermissions)) {
            $userPermissions = $decoded->permissions ?? [];
            $missingPermissions = array_diff($requiredPermissions, $userPermissions);
            
            if (!empty($missingPermissions)) {
                return [
                    'authenticated' => false,
                    'message' => 'Insufficient permissions',
                    'missing' => $missingPermissions
                ];
            }
        }
        
        return [
            'authenticated' => true,
            'user' => $decoded
        ];
    }
    
    public function requireAuth($requiredPermissions = [], $requireSuperAdmin = false) {
        $auth = $this->authenticate($requiredPermissions, $requireSuperAdmin);
        
        if (!$auth['authenticated']) {
            if (php_sapi_name() === 'cli' || !isset($_SERVER['REQUEST_METHOD'])) {
                // CLI or non-HTTP context
                throw new Exception('Authentication required: ' . $auth['message']);
            }
            
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => $auth['message']
            ]);
            exit;
        }
        
        return $auth['user'];
    }
    
    public function optionalAuth() {
        $token = $_COOKIE['auth_token'] ?? '';
        
        if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            if (strpos($authHeader, 'Bearer ') === 0) {
                $token = substr($authHeader, 7);
            }
        }
        
        if ($token) {
            $decoded = $this->jwtHandler->validateToken($token);
            return $decoded ?: null;
        }
        
        return null;
    }
}
?>