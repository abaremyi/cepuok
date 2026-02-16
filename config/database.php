<?php
/**
 * Database Connection Class - Updated for both local and production
 */

class Database {
    private static $connection = null;

    public static function getInstance() {
        return self::getConnection();
    }

    public static function getConnection() {
        if (self::$connection === null) {
            // Check if we're in production (Railway)
            $isProduction = (getenv('RAILWAY_ENVIRONMENT') === 'production') || 
                           (getenv('MYSQLHOST') !== false);
            
            if ($isProduction) {
                // Use production configuration
                require_once __DIR__ . '/database.production.php';
                self::$connection = \Database::getConnection();
                return self::$connection;
            } else {
                // Use local development configuration
                $host = 'localhost';  
                $port = '3308';  // Your XAMPP port
                $db = 'cep_uokdb';  
                $user = 'root'; 
                $pass = ''; 

                try {
                    self::$connection = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
                    self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                    self::$connection->exec("SET NAMES utf8mb4");
                    
                } catch (PDOException $e) {
                    error_log("Local Database Connection Error: " . $e->getMessage());
                    die("Local database connection failed. Please check your XAMPP configuration.");
                }
            }
        }
        return self::$connection;
    }
    
    public static function closeConnection() {
        self::$connection = null;
    }
}