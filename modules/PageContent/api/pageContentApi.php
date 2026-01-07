<?php
/**
 * Page Content API Endpoint
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$root_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";

$action = $_GET['action'] ?? '';

try {
    $pdo = Database::getConnection();

    switch ($action) {
        case 'get_page_content':
            $page_name = $_GET['page'] ?? 'home';
            
            $stmt = $pdo->prepare("SELECT * FROM page_content WHERE page_name = ? AND status = 'active'");
            $stmt->execute([$page_name]);
            $content = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert to associative array by section_name
            $content_array = [];
            foreach ($content as $item) {
                $content_array[$item['section_name']] = $item;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $content_array
            ]);
            break;

        case 'get_educational_programs':
            $stmt = $pdo->query("SELECT * FROM educational_programs WHERE status = 'active' ORDER BY display_order");
            $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $programs
            ]);
            break;

        case 'get_why_choose':
            $stmt = $pdo->query("SELECT * FROM why_choose_items WHERE status = 'active' ORDER BY display_order");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $items
            ]);
            break;

        case 'get_quick_stats':
            $stmt = $pdo->query("SELECT * FROM quick_stats WHERE status = 'active' ORDER BY display_order");
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Page Content API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>