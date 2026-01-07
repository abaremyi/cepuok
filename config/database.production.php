<?php
/**
 * Production Database Configuration for Railway
 * This file will be used in production only
 */

class Database {
    private static $connection = null;

    public static function getInstance() {
        return self::getConnection();
    }

    public static function getConnection() {
        if (self::$connection === null) {
            // Railway provides these environment variables automatically
            $host = getenv('MYSQLHOST') ?: 'localhost';
            $port = getenv('MYSQLPORT') ?: '3306';
            $db   = getenv('MYSQLDATABASE') ?: 'mount_carmel_db';
            $user = getenv('MYSQLUSER') ?: 'root';
            $pass = getenv('MYSQLPASSWORD') ?: '';
            
            // Alternative: If using MySQL_URL format
            $mysql_url = getenv('MYSQL_URL');
            if ($mysql_url) {
                $url = parse_url($mysql_url);
                $host = $url['host'];
                $port = $url['port'];
                $db   = ltrim($url['path'], '/');
                $user = $url['user'];
                $pass = $url['pass'];
            }

            try {
                self::$connection = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                self::$connection->exec("SET NAMES utf8mb4");
                
            } catch (PDOException $e) {
                error_log("Production Database Connection Error: " . $e->getMessage());
                // Don't display detailed errors in production
                die("Database connection failed. Please try again later.");
            }
        }
        return self::$connection;
    }
}