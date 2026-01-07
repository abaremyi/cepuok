<?php
/**
 * Facilities API Endpoint: modules/Facilities/api/facilitiesApi.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Facilities/controllers/FacilitiesController.php";

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$pageType = isset($_GET['page_type']) ? $_GET['page_type'] : '';
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

error_log("Facilities API called with action: " . $action);

try {
    $facilitiesController = new FacilitiesController();

    switch ($action) {
        case 'get_page_facilities':
            // Get facilities for specific page
            if (empty($pageType)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Page type is required.'
                ]);
                break;
            }
            
            $result = $facilitiesController->getFacilitiesByPage($pageType);
            echo json_encode($result);
            break;

        case 'get_facility_by_slug':
            // Get single facility by slug
            if (empty($slug)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Facility slug is required.'
                ]);
                break;
            }
            
            $result = $facilitiesController->getFacilityBySlug($slug);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available actions: get_page_facilities, get_facility_by_slug'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Facilities API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>