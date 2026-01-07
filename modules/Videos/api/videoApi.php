<?php
/**
 * Video Gallery API Endpoint
 * File: modules/Videos/api/videoApi.php
 * Handles all video gallery API requests
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Calculate the root path
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Videos/controllers/VideoController.php";
require_once $root_path . "/modules/Videos/models/VideoModel.php";

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

error_log("Video API called with action: " . $action);

try {
    $videoController = new VideoController();

    switch ($action) {
        case 'get_videos':
        case 'getVideos':
        case '':
            // Get all videos with optional filters
            $params = [
                'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 50,
                'offset' => isset($_GET['offset']) ? (int)$_GET['offset'] : 0,
                'category' => isset($_GET['category']) ? $_GET['category'] : null,
                'status' => 'active'
            ];

            $result = $videoController->getVideos($params);
            echo json_encode($result);
            break;

        case 'get_video_by_id':
        case 'getVideoById':
            // Get single video by ID
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $result = $videoController->getVideoById($id);
            echo json_encode($result);
            break;

        case 'get_by_category':
        case 'getByCategory':
            // Get videos by category
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            
            $result = $videoController->getVideosByCategory($category, $limit);
            echo json_encode($result);
            break;

        case 'get_stats':
        case 'getStats':
            // Get video statistics
            $result = $videoController->getVideoStats();
            echo json_encode($result);
            break;

        case 'increment_views':
        case 'incrementViews':
            // Increment video views
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $result = $videoController->incrementViews($id);
            echo json_encode($result);
            break;

        case 'get_featured':
        case 'getFeatured':
            // Get featured/popular videos
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
            $result = $videoController->getFeaturedVideos($limit);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available actions: get_videos, get_video_by_id, get_by_category, get_stats, increment_views, get_featured'
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