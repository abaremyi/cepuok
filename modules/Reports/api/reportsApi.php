<?php
/**
 * Reports API
 * File: modules/Reports/api/reportsApi.php
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$root = dirname(__DIR__, 4);
require_once "$root/config/paths.php";
require_once "$root/config/database.php";
require_once "$root/helpers/AuthMiddleware.php";
require_once __DIR__ . '/../controllers/ReportsController.php';

$auth   = new AuthMiddleware();
$action = $_GET['action'] ?? '';
$ctrl   = new ReportsController();

try {
    $cu    = $auth->requireAuth(['reports.view']);
    $sessF = ($cu->isSuperAdmin ?? false) ? ($_GET['session'] ?? null) : ($cu->session_type ?? null);

    switch ($action) {
        case 'member_overview':
            echo json_encode(['success'=>true,'data'=>$ctrl->getMemberOverview($sessF)]);
            break;

        case 'member_list':
            $filters = array_filter([
                'session'   => $sessF,
                'status'    => $_GET['status']    ?? null,
                'faculty'   => $_GET['faculty']   ?? null,
                'family_id' => $_GET['family_id'] ?? null,
                'gender'    => $_GET['gender']    ?? null,
            ]);
            $r = $ctrl->listMembers($filters, (int)($_GET['page']??1), (int)($_GET['per_page']??50));
            echo json_encode(['success'=>true,'data'=>$r['data'],'total'=>$r['total'],'pages'=>$r['pages']]);
            break;

        case 'member_export':
            $auth->requireAuth(['reports.export']);
            $filters = array_filter(['session'=>$sessF,'status'=>$_GET['status']??null,'faculty'=>$_GET['faculty']??null]);
            $ctrl->exportMembersCSV($filters);
            break;

        case 'finance_overview':
            echo json_encode(['success'=>true,'data'=>$ctrl->getFinanceOverview($sessF, $_GET['year']??null)]);
            break;

        default:
            throw new Exception("Invalid action: $action");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}