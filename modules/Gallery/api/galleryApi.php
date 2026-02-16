<?php
/**
 * Gallery API Endpoint
 * File: modules/Gallery/api/galleryApi.php
 * Handles all gallery API requests
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Calculate the root path - go up 4 levels from this file's location
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Gallery/controllers/GalleryController.php";

// Get action from GET or POST
$action = isset($_GET['action']) ? $_GET['action'] : '';
if (empty($action) && isset($_POST['action'])) {
    $action = $_POST['action'];
}

// Log the request for debugging
error_log("Gallery API called with action: " . $action);

try {
    // Create controller instance
    $galleryController = new GalleryController();

    switch ($action) {
        case 'get_year_counts':
            // Get count of images per year
            $result = $galleryController->getYearCounts();
            echo json_encode($result);
            break;

        case 'get_images':
            // Get images by year with pagination
            $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $result = $galleryController->getImagesByYear($year, $limit, $offset);
            echo json_encode($result);
            break;

        case 'get_gallery':
        case '': // Default action
            // Get images with optional filters
            $params = [
                'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 50,
                'offset' => isset($_GET['offset']) ? (int)$_GET['offset'] : 0,
                'category' => isset($_GET['category']) ? $_GET['category'] : null,
                'year' => isset($_GET['year']) ? $_GET['year'] : null,
                'status' => isset($_GET['status']) ? $_GET['status'] : 'active'
            ];

            $result = $galleryController->getGalleryImages($params);
            echo json_encode($result);
            break;

        case 'get_categories':
        case 'getCategories':
            // Get all categories
            $result = $galleryController->getCategories();
            echo json_encode($result);
            break;

        case 'get_image':
        case 'getImage':
        case 'get_image_by_id':
            // Get single image by ID
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $result = $galleryController->getImageById($id);
            echo json_encode($result);
            break;

        case 'get_navigation':
        case 'getNavigation':
            // Get navigation IDs for next/previous
            $currentId = isset($_GET['current_id']) ? (int)$_GET['currentId'] : 0;
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $year = isset($_GET['year']) ? $_GET['year'] : null;
            $result = $galleryController->getNavigationIds($currentId, $category, $year);
            echo json_encode($result);
            break;

        case 'get_by_category':
        case 'getByCategory':
            // Get images by category
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            
            $result = $galleryController->getImagesByCategory($category, $limit);
            echo json_encode($result);
            break;

        case 'get_category_counts':
        case 'getCategoryCounts':
            // Get count of images per category
            $result = $galleryController->getCategoryCounts();
            echo json_encode($result);
            break;

        case 'get_featured':
        case 'getFeatured':
            // Get featured/random images
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
            $result = $galleryController->getFeaturedImages($limit);
            echo json_encode($result);
            break;

        case 'create_image':
        case 'createImage':
            // Create new image (requires authentication)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'image_url' => $_POST['image_url'] ?? '',
                'thumbnail_url' => $_POST['thumbnail_url'] ?? $_POST['image_url'] ?? '',
                'category' => $_POST['category'] ?? 'general',
                'year' => isset($_POST['year']) ? (int)$_POST['year'] : date('Y'),
                'display_order' => $_POST['display_order'] ?? 0,
                'status' => $_POST['status'] ?? 'active'
            ];
            
            $result = $galleryController->createImage($data);
            echo json_encode($result);
            break;

        case 'update_image':
        case 'updateImage':
            // Update image (requires authentication)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $data = [];
            
            if (isset($_POST['title'])) $data['title'] = $_POST['title'];
            if (isset($_POST['description'])) $data['description'] = $_POST['description'];
            if (isset($_POST['image_url'])) $data['image_url'] = $_POST['image_url'];
            if (isset($_POST['thumbnail_url'])) $data['thumbnail_url'] = $_POST['thumbnail_url'];
            if (isset($_POST['category'])) $data['category'] = $_POST['category'];
            if (isset($_POST['year'])) $data['year'] = (int)$_POST['year'];
            if (isset($_POST['display_order'])) $data['display_order'] = $_POST['display_order'];
            if (isset($_POST['status'])) $data['status'] = $_POST['status'];
            
            $result = $galleryController->updateImage($id, $data);
            echo json_encode($result);
            break;

        case 'delete_image':
        case 'deleteImage':
            // Delete image (requires authentication)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $result = $galleryController->deleteImage($id);
            echo json_encode($result);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action.',
                'available_actions' => [
                    'get_gallery', 'get_categories', 'get_year_counts', 'get_images',
                    'get_image_by_id', 'get_by_category', 'get_category_counts', 
                    'get_featured', 'create_image', 'update_image', 'delete_image'
                ]
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Gallery API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>