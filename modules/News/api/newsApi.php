<?php
/**
 * News API Endpoint
 * File: modules/News/api/newsApi.php
 * Handles all news API requests
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Calculate the root path - go up 4 levels from this file's location
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/News/controllers/NewsController.php";
require_once $root_path . "/modules/News/models/NewsModel.php";

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Log the request for debugging
error_log("News API called with action: " . $action);

try {
    $newsController = new NewsController();

    switch ($action) {
        case 'get_news':
        case 'getNews':
        case '':
            // Get news items with optional filters
            $params = [
                'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 4,
                'offset' => isset($_GET['offset']) ? (int)$_GET['offset'] : 0,
                'category' => isset($_GET['category']) ? $_GET['category'] : null,
                'upcoming' => isset($_GET['upcoming']) ? filter_var($_GET['upcoming'], FILTER_VALIDATE_BOOLEAN) : false
            ];

            $result = $newsController->getNewsItems($params);
            echo json_encode($result);
            break;

        case 'get_news_by_id':
        case 'getNewsById':
            // Get single news item by ID
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $result = $newsController->getNewsById($id);
            echo json_encode($result);
            break;

        case 'get_related':
        case 'getRelated':
            // Get related news items
            $currentId = isset($_GET['current_id']) ? (int)$_GET['current_id'] : 0;
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 3;
            
            $result = $newsController->getRelatedNews($currentId, $category, $limit);
            echo json_encode($result);
            break;

        case 'get_latest':
        case 'getLatest':
            // Get latest news items
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $result = $newsController->getLatestNews($limit);
            echo json_encode($result);
            break;

        case 'get_featured':
        case 'getFeatured':
            // Get Featured news items
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $result = $newsController->getFeaturedNews($limit);
            echo json_encode($result);
            break;

        case 'get_upcoming':
        case 'getUpcoming':
            // Get upcoming events
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
            $result = $newsController->getUpcomingEvents($limit);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available actions: get_news, get_news_by_id, get_related, get_latest, get_upcoming'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("News API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>