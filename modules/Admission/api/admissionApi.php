<?php
/**
 * Admission API Endpoint
 * File: modules/Admission/api/admissionApi.php
 * Handles all admission API requests
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Admission/controllers/AdmissionController.php";
require_once $root_path . "/modules/Admission/models/AdmissionModel.php";

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

error_log("Admission API called with action: " . $action);

try {
    $admissionController = new AdmissionController();

    switch ($action) {
        case 'get_all_sections':
        case 'getAllSections':
        case '':
            // Get all admission sections
            $result = $admissionController->getAllSections();
            echo json_encode($result);
            break;

        case 'get_section_by_id':
        case 'getSectionById':
            // Get single section by ID
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $result = $admissionController->getSectionById($id);
            echo json_encode($result);
            break;

        case 'get_section_by_slug':
        case 'getSectionBySlug':
            // Get section by slug/title
            $slug = isset($_GET['slug']) ? $_GET['slug'] : '';
            $result = $admissionController->getSectionBySlug($slug);
            echo json_encode($result);
            break;

        case 'track_download':
            // Track document download
            $document_id = isset($_POST['document_id']) ? (int)$_POST['document_id'] : 0;
            
            if ($document_id) {
                try {
                    $stmt = $pdo->prepare("UPDATE admission_documents SET downloads = downloads + 1 WHERE id = ?");
                    $stmt->execute([$document_id]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Download tracked'
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to track download'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid document ID'
                ]);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available actions: get_all_sections, get_section_by_id, get_section_by_slug'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Admission API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>