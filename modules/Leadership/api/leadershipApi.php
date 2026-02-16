<?php
/**
 * Leadership API Endpoint
 * File: modules/Leadership/api/leadershipApi.php
 * Handles all leadership API requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Leadership/models/LeadershipModel.php";

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    $db = Database::getInstance();
    $leadershipModel = new LeadershipModel($db);

    switch ($action) {
        case 'get_all_years':
            $years = $leadershipModel->getAllYears();
            echo json_encode([
                'success' => true,
                'data' => $years,
                'total' => count($years)
            ]);
            break;

        case 'get_current_year':
            $currentYear = $leadershipModel->getCurrentYear();
            if ($currentYear) {
                $data = $leadershipModel->getCompleteYearData($currentYear['id']);
                echo json_encode([
                    'success' => true,
                    'data' => $data
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No current year set'
                ]);
            }
            break;

        case 'get_year_data':
            $yearId = isset($_GET['year_id']) ? (int)$_GET['year_id'] : 0;
            
            if ($yearId <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid year ID'
                ]);
                break;
            }
            
            $data = $leadershipModel->getCompleteYearData($yearId);
            
            if ($data) {
                echo json_encode([
                    'success' => true,
                    'data' => $data
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Year not found'
                ]);
            }
            break;

        case 'get_positions':
            $positions = $leadershipModel->getAllPositions();
            echo json_encode([
                'success' => true,
                'data' => $positions
            ]);
            break;

        case 'get_history_timeline':
            $timeline = $leadershipModel->getHistoryTimeline();
            echo json_encode([
                'success' => true,
                'data' => $timeline,
                'total' => count($timeline)
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Available: get_all_years, get_current_year, get_year_data, get_positions, get_history_timeline'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Leadership API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}