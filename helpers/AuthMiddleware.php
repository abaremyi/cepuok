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

        if ($decoded) {
            $sessionType = $this->getUserSessionType($decoded->user_id);
            $decoded->session_type = $sessionType;
        }
        
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

    /**
     * Get user session type
     * @param int $userId User ID
     * @return string|null Session type (day/weekend/both)
    */
    private function getUserSessionType($userId) {
        try {
            require_once ROOT_PATH . '/config/database.php';
            $db = Database::getConnection();
            
            // Check if user is a leader in current year
            $query = "SELECT lm.session_type 
                    FROM leadership_members lm
                    JOIN leadership_years ly ON lm.year_id = ly.id
                    JOIN members m ON lm.full_name = CONCAT(m.firstname, ' ', m.lastname)
                    WHERE m.user_id = :user_id AND ly.is_current = 1 AND lm.status = 'active'
                    LIMIT 1";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['session_type'] : null;
        } catch (Exception $e) {
            error_log("Error getting user session type: " . $e->getMessage());
            return null;
        }
    }
}
?>