<?php

require_once __DIR__ . '/../config/config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {
    private $secret;

    public function __construct() {
        $this->secret = JWT_SECRET_KEY;
    }

    public function generateToken($payload) {
        // Make sure is_super_admin is a boolean and explicitly set
        if (isset($payload['is_super_admin'])) {
            $payload['is_super_admin'] = (bool)$payload['is_super_admin'];
        }
        
        // Log for debugging
        error_log("JWTHandler: Generating token with is_super_admin: " . 
                  (isset($payload['is_super_admin']) && $payload['is_super_admin'] ? 'true' : 'false'));
        
        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            
            // Ensure is_super_admin is properly cast when decoding
            if (isset($decoded->is_super_admin)) {
                $decoded->is_super_admin = (bool)$decoded->is_super_admin;
            }
            
            return $decoded;
        } catch (Exception $e) {
            error_log("JWT Validation error: " . $e->getMessage());
            return false;
        }
    }
}