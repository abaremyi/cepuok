<?php
/**
 * Video API Endpoint - CEP UOK WEBSITE
 * File: modules/Videos/api/videoApi.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Videos/controllers/VideoController.php";

$action = isset($_GET['action']) ? $_GET['action'] : '';
if (empty($action) && isset($_POST['action'])) {
    $action = $_POST['action'];
}

try {
    $videoController = new VideoController();

    switch ($action) {
        case 'get_videos':
        case '':
            $params = [
                'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 12,
                'offset' => isset($_GET['offset']) ? (int)$_GET['offset'] : 0,
                'status' => 'active'
            ];
            $result = $videoController->getVideos($params);
            echo json_encode($result);
            break;

        case 'increment_views':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $result = $videoController->incrementViews($id);
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action.',
                'available_actions' => ['get_videos', 'increment_views']
            ]);
            break;
    }
} catch (Exception $e) {
    error_log("Video API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>