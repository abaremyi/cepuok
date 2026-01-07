<?php

require_once __DIR__ . '/../config/config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {
    private $secret;

    public function __construct() {
        $this->secret = JWT_SECRET_KEY;  // Use the environment variable (constant)
    }

    public function generateToken($payload) {
        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
}

