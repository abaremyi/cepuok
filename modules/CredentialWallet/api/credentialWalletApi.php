<?php
/**
 * Credential Wallet API
 * File: modules/CredentialWallet/api/credentialWalletApi.php
 * Route: /api/wallet
 *
 * All endpoints require super-admin or wallet.view permission.
 * Password reveal / copy requires wallet.view.
 * Create / Update require wallet.create / wallet.edit.
 * Delete requires wallet.delete.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$root = dirname(__DIR__, 3);
require_once "$root/config/paths.php";
require_once "$root/config/config.php";
require_once "$root/config/database.php";
require_once "$root/helpers/AuthMiddleware.php";
require_once __DIR__ . '/../controllers/CredentialWalletController.php';

$auth   = new AuthMiddleware();
$action = $_GET['action'] ?? '';
$ctrl   = new CredentialWalletController();

try {
    // All wallet endpoints require at minimum: super admin OR wallet.view
    $cu          = $auth->requireAuth(['wallet.view']);
    $isSuperAdmin = $cu->isSuperAdmin ?? false;

    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    switch ($action) {

        // ── List / filter ────────────────────────────────────────
        case 'list':
            $filters = array_filter([
                'category'  => $_GET['category']  ?? null,
                'search'    => $_GET['search']    ?? null,
                'is_active' => isset($_GET['is_active']) ? (int)$_GET['is_active'] : null,
            ], fn($v) => $v !== null);
            echo json_encode($ctrl->list($filters));
            break;

        // ── Single record (with password decrypted) ──────────────
        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID is required');
            echo json_encode($ctrl->get($id, $cu->id));
            break;

        // ── Stats dashboard ──────────────────────────────────────
        case 'stats':
            echo json_encode($ctrl->stats());
            break;

        // ── Category list ────────────────────────────────────────
        case 'categories':
            echo json_encode($ctrl->categories());
            break;

        // ── Reveal / copy password (POST, audit-logged) ──────────
        case 'reveal':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('POST required');
            $id = (int)($input['id'] ?? 0);
            if (!$id) throw new Exception('ID is required');
            echo json_encode($ctrl->revealPassword($id, $cu->id));
            break;

        // ── Audit log for one credential ─────────────────────────
        case 'audit':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID is required');
            echo json_encode($ctrl->auditLog($id));
            break;

        // ── Create ───────────────────────────────────────────────
        case 'create':
            $auth->requireAuth(['wallet.create']);
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('POST required');
            echo json_encode($ctrl->create($input, $cu->id));
            break;

        // ── Update ───────────────────────────────────────────────
        case 'update':
            $auth->requireAuth(['wallet.edit']);
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('POST required');
            $id = (int)($input['id'] ?? 0);
            if (!$id) throw new Exception('ID is required');
            echo json_encode($ctrl->update($id, $input, $cu->id));
            break;

        // ── Toggle active/inactive ────────────────────────────────
        case 'toggle':
            $auth->requireAuth(['wallet.edit']);
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('POST required');
            $id = (int)($input['id'] ?? 0);
            if (!$id) throw new Exception('ID is required');
            echo json_encode($ctrl->toggleStatus($id, $cu->id));
            break;

        // ── Delete ───────────────────────────────────────────────
        case 'delete':
            $auth->requireAuth(['wallet.delete']);
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('POST required');
            $id = (int)($input['id'] ?? 0);
            if (!$id) throw new Exception('ID is required');
            echo json_encode($ctrl->delete($id, $cu->id));
            break;

        default:
            throw new Exception("Unknown action: $action");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}