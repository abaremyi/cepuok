<?php
// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';


// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); // Path to the root directory
$dotenv->load();

// You can now access environment variables like this:
define('JWT_SECRET_KEY', $_ENV['JWT_SECRET_KEY']);
