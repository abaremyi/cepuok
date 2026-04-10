<?php
/**
 * Supporters API
 * File: modules/Supporters/api/supportersApi.php
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$root = dirname(__DIR__, 3);
require_once "$root/config/paths.php";
require_once "$root/config/database.php";
require_once "$root/helpers/AuthMiddleware.php";
require_once __DIR__ . '/../controllers/SupportersController.php';

$auth = new AuthMiddleware();
$action = $_GET['action'] ?? '';
$ctrl = new SupportersController();

function handlePhotoUpload($file, $subfolder, $rootPath)
{
    $targetDir = $rootPath . "/uploads/{$subfolder}/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetFile = $targetDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'filepath' => "{$subfolder}/{$filename}"];
    }
    return ['success' => false, 'message' => 'Failed to upload file'];
}

try {
    $cu = $auth->requireAuth(['supporters.view']);
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    switch ($action) {
        case 'list':
            $filters = array_filter([
                'type' => $_GET['type'] ?? null,
                'tier' => $_GET['tier'] ?? null,
                'session' => $_GET['session'] ?? null,
                'status' => $_GET['status'] ?? null,
                'search' => $_GET['search'] ?? null,
            ]);
            $r = $ctrl->list($filters, (int) ($_GET['page'] ?? 1), (int) ($_GET['per_page'] ?? 20));
            echo json_encode(['success' => true, 'data' => $r['data'], 'total' => $r['total'], 'pages' => $r['pages']]);
            break;

        case 'get':
            $s = $ctrl->get((int) $_GET['id']);
            echo json_encode(['success' => !empty($s), 'data' => $s]);
            break;

        case 'stats':
            echo json_encode(['success' => true, 'data' => $ctrl->getStats()]);
            break;

        case 'create':
            $auth->requireAuth(['supporters.create']);
            // Handle both JSON and form data
            if (!empty($_FILES)) {
                $input = $_POST;
                // Handle photo upload
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = handlePhotoUpload($_FILES['photo'], 'supporters', $root);
                    if ($uploadResult['success']) {
                        $input['photo'] = $uploadResult['filepath'];
                    }
                }
            } else {
                $input = json_decode(file_get_contents('php://input'), true) ?: [];
            }
            echo json_encode($ctrl->create($input));
            break;

        case 'update':
            $auth->requireAuth(['supporters.edit']);
            $id = (int) ($_GET['id'] ?? $input['id'] ?? 0);
            if (!$id)
                throw new Exception("ID required");

            // Handle both JSON and form data
            if (!empty($_FILES)) {
                $input = $_POST;
                // Handle photo upload
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = handlePhotoUpload($_FILES['photo'], 'supporters', $root);
                    if ($uploadResult['success']) {
                        $input['photo'] = $uploadResult['filepath'];
                    }
                }
            } else {
                $input = json_decode(file_get_contents('php://input'), true) ?: [];
            }

            echo json_encode($ctrl->update($id, $input));
            break;

        case 'delete':
            $auth->requireAuth(['supporters.delete']);
            echo json_encode($ctrl->delete((int) ($input['id'] ?? 0)));
            break;

        case 'add_contribution':
            $auth->requireAuth(['supporters.contributions']);
            $sid = (int) ($input['supporter_id'] ?? 0);
            $input['recorded_by'] = $cu->user_id;
            echo json_encode($ctrl->addContribution($sid, $input));
            break;

        case 'delete_contribution':
            $auth->requireAuth(['supporters.contributions']);
            $contributionId = (int) ($input['contribution_id'] ?? 0);
            $supporterId = (int) ($input['supporter_id'] ?? 0);

            if (!$contributionId || !$supporterId) {
                throw new Exception("Contribution ID and Supporter ID required");
            }

            echo json_encode($ctrl->deleteContribution($supporterId, $contributionId));
            break;

        default:
            throw new Exception("Invalid action: $action");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}