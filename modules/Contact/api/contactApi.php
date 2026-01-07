<?php
/**
 * Contact API Endpoint
 * File: modules/Contact/api/contactApi.php
 * Handles contact form submissions
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Contact/controllers/ContactController.php";
require_once $root_path . "/modules/Contact/models/ContactModel.php";

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

error_log("Contact API called with action: " . $action);

try {
    $contactController = new ContactController();

    switch ($action) {
        case 'submit_contact':
        case 'contact':
        case '':
            // Handle contact form submission
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'message' => $_POST['message'] ?? '',
                'person_type' => $_POST['person_type'] ?? 'guest',
                'inquiry_type' => $_POST['inquiry_type'] ?? 'general'
            ];

            error_log("Contact form data - Name: {$data['name']}, Person Type: {$data['person_type']}, Email: {$data['email']}, Phone: {$data['phone']}, Inquiry: {$data['inquiry_type']}");

            $result = $contactController->handleContactForm($data);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available actions: submit_contact, contact'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Contact API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred: ' . $e->getMessage()
    ]);
}
?>