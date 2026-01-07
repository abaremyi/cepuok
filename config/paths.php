<?php
// config/paths.php - Path Helper

// Define absolute paths
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('LAYOUTS_PATH', ROOT_PATH . '/layouts');
define('DB_PATH', ROOT_PATH . '/config');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('IMG_PATH', ROOT_PATH . '/img');
define('CSS_PATH', ROOT_PATH . '/css');
define('JS_PATH', ROOT_PATH . '/js');

// Determine environment - more robust detection
$isProduction = (getenv('RAILWAY_ENVIRONMENT') === 'production') || 
                (getenv('RAILWAY_STATIC_URL') !== false) ||
                (getenv('MYSQLHOST') !== false);

// Define URL paths
if ($isProduction) {
    $protocol = 'https';
    $host = getenv('RAILWAY_STATIC_URL') ?: (getenv('RAILWAY_PUBLIC_DOMAIN') ?: $_SERVER['HTTP_HOST']);
    $base_url = $protocol . "://" . $host;
} else {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_dir = str_replace('/index.php', '', $script_name);
    $base_url = $protocol . "://" . $host . $base_dir;
}

// Ensure no double slashes
$base_url = rtrim($base_url, '/');

define('BASE_URL', $base_url);
define('IMG_URL', BASE_URL . '/img');
define('CSS_URL', BASE_URL . '/css');
define('JS_URL', BASE_URL . '/js');
define('AUTH_CSS_URL', CSS_URL . '/auth-style.css');
define('AUTH_JS_URL', JS_URL . '/auth.js');
define('VENDOR_URL', BASE_URL . '/vendor');

// Helper functions
function get_layout($layout_name) {
    return LAYOUTS_PATH . '/' . $layout_name . '.php';
}

function get_db($db_conn_name) {
    return DB_PATH . '/' . $db_conn_name . '.php';
}

function img_url($image_name) {
    return IMG_URL . '/' . $image_name;
}

function css_url($css_name) {
    return CSS_URL . '/' . $css_name;
}

function js_url($js_name) {
    return JS_URL . '/' . $js_name;
}

function url($path = '') {
    if (empty($path)) {
        return BASE_URL;
    }
    return BASE_URL . '/' . ltrim($path, '/');
}

function auth_css_url() {
    return AUTH_CSS_URL;
}

function auth_js_url() {
    return AUTH_JS_URL;
}