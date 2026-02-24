<?php
/**
 * Finance API
 * File: modules/Finance/api/financeApi.php
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$root = dirname(__DIR__, 4);
require_once "$root/config/paths.php";
require_once "$root/config/database.php";
require_once "$root/helpers/AuthMiddleware.php";
require_once __DIR__ . '/../controllers/FinanceController.php';

$auth   = new AuthMiddleware();
$action = $_GET['action'] ?? '';
$ctrl   = new FinanceController();

try {
    $cu = $auth->requireAuth(['finance.view']);
    $isSuperAdmin = $cu->isSuperAdmin ?? false;
    $sessionType  = $cu->session_type ?? null;

    // For non-super-admin: enforce session filter
    $sessionFilter = $isSuperAdmin ? ($_GET['session'] ?? null) : $sessionType;

    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    switch ($action) {
        // ── Dashboard ──────────────────────────────────────────
        case 'dashboard':
            echo json_encode(['success'=>true, 'data'=>$ctrl->getDashboard($sessionFilter)]);
            break;

        // ── Revenue ────────────────────────────────────────────
        case 'revenue_list':
            $filters = array_filter([
                'session' => $_GET['session'] ?? $sessionFilter,
                'type'    => $_GET['type']    ?? null,
                'month'   => $_GET['month']   ?? null,
            ]);
            $result = $ctrl->listRevenue($filters, (int)($_GET['page']??1), (int)($_GET['per_page']??20));
            echo json_encode(['success'=>true, 'data'=>$result['data'], 'total'=>$result['total'], 'pages'=>$result['pages']]);
            break;

        case 'revenue_record':
            $auth->requireAuth(['finance.record_revenue']);
            $data = $_SERVER['REQUEST_METHOD'] === 'POST'
                ? ($input ?: array_merge($_POST, ['recorded_by' => $cu->id]))
                : [];
            $data['recorded_by'] = $cu->id;
            if ($sessionFilter && empty($data['cep_session'])) $data['cep_session'] = $sessionFilter;
            echo json_encode($ctrl->recordRevenue($data));
            break;

        case 'daily_total':
            $session = $_GET['session'] ?? $sessionFilter;
            $total   = $ctrl->getDailyTotal($session, $_GET['date'] ?? null);
            echo json_encode(['success'=>true, 'total'=>$total]);
            break;

        case 'revenue_export':
            $auth->requireAuth(['finance.reports']);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="revenue_' . date('Ymd') . '.csv"');
            $filters = array_filter(['session' => $sessionFilter, 'type' => $_GET['type']??null, 'month' => $_GET['month']??null]);
            $result  = $ctrl->listRevenue($filters, 1, 100000);
            $out = fopen('php://output','w');
            fputcsv($out, ['Date','Session','Type','Amount','Reference','Description','Recorded By']);
            foreach ($result['data'] as $r) {
                fputcsv($out, [$r['revenue_date'],$r['cep_session'],$r['revenue_type'],$r['amount'],$r['reference_no']??'',$r['description']??'',$r['recorded_by_name']??'']);
            }
            fclose($out); exit;

        // ── Budgets ────────────────────────────────────────────
        case 'budget_list':
            $filters = array_filter(['session' => $sessionFilter, 'status' => $_GET['status'] ?? null]);
            echo json_encode(['success'=>true, 'data'=>$ctrl->listBudgets($filters)]);
            break;

        case 'budget_create':
            $auth->requireAuth(['finance.manage_budget']);
            $data  = $input;
            $lines = $data['lines'] ?? [];
            unset($data['lines']);
            $data['created_by'] = $cu->id;
            echo json_encode($ctrl->createBudget($data, $lines));
            break;

        case 'budget_get':
            $id = (int)($_GET['id'] ?? 0);
            echo json_encode(['success'=>true, 'data'=>$ctrl->getBudget($id)]);
            break;

        case 'budget_approve':
            $auth->requireAuth(['finance.manage_budget']);
            $id = (int)($input['id'] ?? 0);
            echo json_encode($ctrl->approveBudget($id, $cu->id));
            break;

        // ── Fund Requests ───────────────────────────────────────
        case 'fund_requests':
            $filters = array_filter([
                'session' => $sessionFilter,
                'stage'   => $_GET['stage']  ?? null,
                'search'  => $_GET['search'] ?? null,
            ]);
            $result = $ctrl->listFundRequests($filters, (int)($_GET['page']??1), (int)($_GET['per_page']??20));
            echo json_encode(['success'=>true, 'data'=>$result['data'], 'total'=>$result['total'], 'pages'=>$result['pages']]);
            break;

        case 'fund_pipeline':
            echo json_encode(['success'=>true, 'data'=>$ctrl->getPipeline($sessionFilter)]);
            break;

        case 'fund_submit':
            $auth->requireAuth(['finance.fund_requests']);
            $input['requested_by'] = $cu->id;
            if ($sessionFilter) $input['cep_session'] = $sessionFilter;
            echo json_encode($ctrl->submitFundRequest($input));
            break;

        case 'fund_advance':
            $allowed = ['mark_review','approve','reject','disburse'];
            $action2 = $input['action'] ?? '';
            if (!in_array($action2, $allowed)) throw new Exception("Invalid action");
            $permMap = ['approve'=>'finance.approve_funds','disburse'=>'finance.disburse_funds'];
            if (isset($permMap[$action2])) $auth->requireAuth([$permMap[$action2]]);
            echo json_encode($ctrl->advanceRequest((int)($input['id']??0), $action2, $cu->id, $input));
            break;

        // ── Disbursements ───────────────────────────────────────
        case 'disbursements':
            $filters = array_filter(['session' => $sessionFilter, 'method' => $_GET['method'] ?? null]);
            $result  = $ctrl->listDisbursements($filters, (int)($_GET['page']??1), (int)($_GET['per_page']??20));
            echo json_encode(['success'=>true, 'data'=>$result['data'], 'total'=>$result['total'], 'pages'=>$result['pages']]);
            break;

        default:
            throw new Exception("Invalid action: $action");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}