<?php
/**
 * Programs API Endpoint
 * File: modules/Programs/api/programsApi.php
 * Handles all programs API requests
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Programs/controllers/ProgramsController.php";
require_once $root_path . "/modules/Programs/models/ProgramsModel.php";

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

error_log("Programs API called with action: " . $action);

try {
    $programsController = new ProgramsController();

    switch ($action) {
        case 'get_all_programs':
        case 'getAllPrograms':
        case '':
            // Get all programs
            $result = $programsController->getAllPrograms();
            echo json_encode($result);
            break;

        case 'get_program_by_id':
        case 'getProgramById':
            // Get single program by ID
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $result = $programsController->getProgramById($id);
            echo json_encode($result);
            break;

        case 'get_program_by_title':
        case 'getProgramByTitle':
            // Get program by title/slug
            $title = isset($_GET['title']) ? $_GET['title'] : '';
            $result = $programsController->getProgramByTitle($title);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available actions: get_all_programs, get_program_by_id, get_program_by_title'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Programs API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>