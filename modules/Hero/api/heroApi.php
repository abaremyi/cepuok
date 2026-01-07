<?php
/**
 * Hero Slider API Endpoint
 * File: modules/Hero/api/heroApi.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Hero/controllers/HeroController.php";
require_once $root_path . "/modules/Hero/models/HeroModel.php";

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    $heroController = new HeroController();

    switch ($action) {
        case 'get_sliders':
        case 'getSliders':
        case '':
            $params = [
                'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 10
            ];

            $result = $heroController->getSliders($params);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available actions: get_sliders'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Hero API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>