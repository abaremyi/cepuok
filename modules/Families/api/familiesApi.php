<?php
/**
 * Families API
 * File: modules/Families/api/familiesApi.php
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$root = dirname(__DIR__, 4);
require_once "$root/config/paths.php";
require_once "$root/config/database.php";
require_once "$root/helpers/AuthMiddleware.php";
require_once __DIR__ . '/../controllers/FamiliesController.php';

$auth   = new AuthMiddleware();
$action = $_GET['action'] ?? '';
$ctrl   = new FamiliesController();

try {
    $cu     = $auth->requireAuth(['families.view']);
    $isSA   = $cu->isSuperAdmin ?? false;
    $sessF  = $isSA ? ($_GET['session'] ?? null) : ($cu->session_type ?? null);
    $input  = json_decode(file_get_contents('php://input'), true) ?: [];

    switch ($action) {
        case 'list':
            echo json_encode(['success'=>true, 'data'=>$ctrl->listFamilies($sessF)]);
            break;

        case 'get':
            $f = $ctrl->getFamily((int)$_GET['id']);
            echo json_encode(['success'=>!empty($f), 'data'=>$f]);
            break;

        case 'members':
            $fid  = (int)($_GET['family_id'] ?? 0);
            $data = $ctrl->getFamilyMembers($fid, ['status'=>$_GET['status']??null,'search'=>$_GET['search']??null]);
            echo json_encode(['success'=>true, 'data'=>$data]);
            break;

        case 'unassigned':
            $data = $ctrl->getUnassigned($sessF, $_GET['search']??'');
            echo json_encode(['success'=>true, 'data'=>$data]);
            break;

        case 'stats':
            echo json_encode(['success'=>true, 'data'=>$ctrl->getStats()]);
            break;

        case 'create':
            $auth->requireAuth(['families.create']);
            echo json_encode($ctrl->create($input));
            break;

        case 'update':
            $auth->requireAuth(['families.edit']);
            echo json_encode($ctrl->update((int)($input['id']??0), $input));
            break;

        case 'delete':
            $auth->requireAuth(['families.delete']);
            echo json_encode($ctrl->delete((int)($input['id']??0)));
            break;

        case 'assign':
            $auth->requireAuth(['families.assign']);
            $fid = (int)($input['family_id'] ?? 0);
            $ids = $input['member_ids'] ?? [];
            echo json_encode($ctrl->assignMembers($fid, $ids));
            break;

        case 'remove_member':
            $auth->requireAuth(['families.assign']);
            echo json_encode($ctrl->removeMember((int)($input['member_id']??0)));
            break;

        default:
            throw new Exception("Invalid action: $action");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}