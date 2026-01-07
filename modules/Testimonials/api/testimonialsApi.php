<?php
/**
 * Testimonials API Endpoint
 * File: modules/Testimonials/api/testimonialsApi.php
 * Handles all testimonials API requests
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
require_once $root_path . "/modules/Testimonials/controllers/TestimonialController.php";
require_once $root_path . "/modules/Testimonials/models/TestimonialModel.php";

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Log the request for debugging
error_log("Testimonials API called with action: " . $action);

try {
    $testimonialController = new TestimonialController();

    switch ($action) {
        case 'get_testimonials':
        case 'getTestimonials':
        case '':
            // Get testimonials with optional filters
            $params = [
                'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 10
            ];

            $result = $testimonialController->getTestimonials($params);
            echo json_encode($result);
            break;

        case 'get_testimonial':
        case 'getTestimonial':
            // Get single testimonial by ID
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $result = $testimonialController->getTestimonialById($id);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available actions: get_testimonials, get_testimonial'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Testimonials API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>