<?php
/**
 * Projects API
 * File: modules/Projects/api/projectsApi.php
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$root = dirname(__DIR__, 4);
require_once "$root/config/paths.php";
require_once "$root/config/database.php";
require_once "$root/helpers/AuthMiddleware.php";
require_once __DIR__ . '/../controllers/ProjectsController.php';

$auth   = new AuthMiddleware();
$action = $_GET['action'] ?? '';
$ctrl   = new ProjectsController();

try {
    $cu    = $auth->requireAuth(['projects.view']);
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $sessF = ($cu->isSuperAdmin ?? false) ? ($_GET['session'] ?? null) : ($cu->session_type ?? null);

    switch ($action) {
        case 'list':
            $filters = array_filter([
                'session'  => $sessF,
                'status'   => $_GET['status']   ?? null,
                'category' => $_GET['category'] ?? null,
                'search'   => $_GET['search']   ?? null,
            ]);
            $r = $ctrl->list($filters, (int)($_GET['page']??1), (int)($_GET['per_page']??20));
            echo json_encode(['success'=>true,'data'=>$r['data'],'total'=>$r['total'],'pages'=>$r['pages']]);
            break;

        case 'get':
            $p = $ctrl->get((int)$_GET['id']);
            echo json_encode(['success'=>!empty($p),'data'=>$p]);
            break;

        case 'stats':
            echo json_encode(['success'=>true,'data'=>$ctrl->getStats($sessF)]);
            break;

        case 'create':
            $auth->requireAuth(['projects.create']);
            $input['created_by'] = $cu->id;
            if ($sessF && empty($input['cep_session'])) $input['cep_session'] = $sessF;
            echo json_encode($ctrl->create($input));
            break;

        case 'update':
            $auth->requireAuth(['projects.edit']);
            echo json_encode($ctrl->update((int)($input['id']??0), $input));
            break;

        case 'delete':
            $auth->requireAuth(['projects.edit']);
            echo json_encode($ctrl->delete((int)($input['id']??0)));
            break;

        case 'add_task':
            $auth->requireAuth(['projects.manage_tasks']);
            $pid = (int)($input['project_id'] ?? 0);
            echo json_encode($ctrl->addTask($pid, $input));
            break;

        case 'task_status':
            $auth->requireAuth(['projects.manage_tasks']);
            echo json_encode($ctrl->updateTaskStatus((int)($input['task_id']??0), $input['status']??'todo'));
            break;

        case 'add_update':
            echo json_encode($ctrl->addUpdate(
                (int)($input['project_id']??0),
                $input['update_text'] ?? '',
                $input['progress'] ?? null,
                $cu->id
            ));
            break;

        default:
            throw new Exception("Invalid action: $action");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}