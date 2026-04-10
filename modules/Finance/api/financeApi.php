<?php
/**
 * Finance API — v2
 * File: modules/Finance/api/financeApi.php
 * Route: /api/finance
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$root = dirname(__DIR__, 3);
require_once "$root/config/paths.php";
require_once "$root/config/config.php";
require_once "$root/config/database.php";
require_once "$root/helpers/AuthMiddleware.php";
require_once "$root/helpers/PermissionHelper.php";
require_once __DIR__ . '/../controllers/FinanceController.php';

$auth   = new AuthMiddleware();
$action = $_GET['action'] ?? '';
$ctrl   = new FinanceController();

try {
    $cu           = $auth->requireAuth(['finance.view']);
    $isSuperAdmin = (bool)($cu->isSuperAdmin ?? false);
    $isPresident  = (bool)($cu->role_name === 'President' || $isSuperAdmin);
    $sessionType  = $cu->session_type ?? null;
    $userId       = (int)($cu->id ?? $cu->user_id ?? 0);
    $sessionFilter = $isSuperAdmin ? ($_GET['session'] ?? null) : $sessionType;
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    switch ($action) {

        // ── Dashboard ────────────────────────────────────────
        case 'dashboard':
            echo json_encode(['success'=>true,'data'=>$ctrl->getDashboard($sessionFilter)]);
            break;

        // ── Revenue ──────────────────────────────────────────
        case 'revenue_list':
            $f = ['session'=>$sessionFilter,'type'=>$_GET['type']??null,'month'=>$_GET['month']??null];
            $r = $ctrl->listRevenue(array_filter($f), (int)($_GET['page']??1), (int)($_GET['per_page']??20));
            echo json_encode(['success'=>true]+$r);
            break;

        case 'revenue_record':
            $auth->requireAuth(['finance.record_revenue']);
            $data = $input;
            $data['recorded_by'] = $userId;
            if ($sessionFilter && empty($data['cep_session'])) $data['cep_session'] = $sessionFilter;
            echo json_encode($ctrl->recordRevenue($data));
            break;

        case 'revenue_get':
            $r = $ctrl->getRevenueById((int)($_GET['id']??0));
            echo json_encode(['success'=>(bool)$r,'data'=>$r]);
            break;

        case 'revenue_update':
            $auth->requireAuth(['finance.record_revenue']);
            echo json_encode($ctrl->updateRevenue($input));
            break;

        case 'revenue_delete':
            $auth->requireAuth(['finance.record_revenue']);
            echo json_encode($ctrl->deleteRevenue((int)($input['id']??0)));
            break;

        case 'daily_total':
            echo json_encode(['success'=>true,'total'=>$ctrl->getDailyTotal($sessionFilter,$_GET['date']??null)]);
            break;

        case 'revenue_export':
            $auth->requireAuth(['finance.reports']);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="revenue_'.date('Ymd').'.csv"');
            $r = $ctrl->listRevenue(['session'=>$sessionFilter,'type'=>$_GET['type']??null,'month'=>$_GET['month']??null],1,100000);
            $out = fopen('php://output','w');
            fputcsv($out,['Date','Session','Type','Amount','Reference','Description','Recorded By']);
            foreach ($r['data'] as $row) fputcsv($out,[$row['revenue_date'],$row['cep_session'],$row['revenue_type'],$row['amount'],$row['reference_no']??'',$row['description']??'',$row['recorded_by_name']??'']);
            fclose($out); exit;

        // ── Budget Indicators ─────────────────────────────────
        case 'indicator_get':
            $sess = $_GET['session'] ?? $sessionFilter;
            $year = $_GET['year']    ?? date('Y');
            $ind  = $ctrl->getIndicator($sess, $year);
            echo json_encode(['success'=>true,'data'=>$ind]);
            break;

        case 'indicator_get_by_id':
            $ind = $ctrl->getIndicatorById((int)($_GET['id']??0));
            echo json_encode(['success'=>(bool)$ind,'data'=>$ind]);
            break;

        case 'indicator_create':
            $auth->requireAuth(['finance.manage_indicators']);
            $pools = $input['pools'] ?? [];
            unset($input['pools']);
            echo json_encode($ctrl->createIndicator($input, $pools, $userId));
            break;

        case 'indicator_update':
            // President can edit if before lock_date, super admin always
            $pools = $input['pools'] ?? [];
            $id    = (int)($input['id'] ?? 0);
            unset($input['pools'], $input['id']);
            echo json_encode($ctrl->updateIndicator($id, $input, $pools, $userId));
            break;

        case 'indicator_confirm':
            // Only president or super admin
            if (!$isPresident) throw new Exception('Only the President can confirm budget indicators');
            echo json_encode($ctrl->confirmIndicator((int)($input['id']??0), $userId));
            break;

        case 'indicator_delete':
            // Debug logging
            error_log("indicator_delete called - User ID: $userId");
            error_log("isSuperAdmin value: " . ($isSuperAdmin ? 'true' : 'false'));
            error_log("isPresident value: " . ($isPresident ? 'true' : 'false'));
            error_log("User role: " . ($cu->role_name ?? 'unknown'));
            
            // Check multiple conditions for super admin
            $isUserSuperAdmin = $isSuperAdmin || 
                                (isset($cu->role_name) && $cu->role_name === 'Super Admin') ||
                                (isset($cu->is_super_admin) && $cu->is_super_admin === true);
            
            error_log("Computed isUserSuperAdmin: " . ($isUserSuperAdmin ? 'true' : 'false'));
            
            // Allow if user is super admin (by any definition)
            if ($isUserSuperAdmin) {
                error_log("User is Super Admin - allowing deletion");
                echo json_encode($ctrl->deleteIndicator((int)($input['id']??0), $userId, true));
                break;
            }
            
            // Check if user is President
            if (!$isPresident) {
                error_log("User is not President - denying deletion");
                throw new Exception('Only Super Admin or President can delete budget indicators');
            }
            
            // President can delete (model will check for approved quarters)
            error_log("User is President - allowing deletion with checks");
            echo json_encode($ctrl->deleteIndicator((int)($input['id']??0), $userId, false));
            break;
            
        case 'run_maintenance':
            if (!$isSuperAdmin) throw new Exception('Only Super Admin can run maintenance');
            
            $task = $input['task'] ?? 'all';
            $results = [];
            
            try {
                $db = Database::getInstance();
                
                if ($task === 'all' || $task === 'indicators') {
                    $stmt = $db->prepare("
                        UPDATE budget_indicators 
                        SET status = 'locked' 
                        WHERE status IN ('draft', 'confirmed') 
                        AND lock_date IS NOT NULL 
                        AND lock_date < CURDATE()
                    ");
                    $stmt->execute();
                    $results['indicators_locked'] = $stmt->rowCount();
                    
                    // Log the action
                    error_log("[MAINTENANCE] Locked {$results['indicators_locked']} expired indicators by user ID: $userId");
                }
                
                if ($task === 'all' || $task === 'budgets') {
                    $stmt = $db->prepare("
                        UPDATE budget_quarters 
                        SET status = 'suspended' 
                        WHERE status = 'draft' 
                        AND draft_created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)
                    ");
                    $stmt->execute();
                    $results['budgets_suspended'] = $stmt->rowCount();
                    
                    // Log the action
                    error_log("[MAINTENANCE] Suspended {$results['budgets_suspended']} expired budgets by user ID: $userId");
                }
                
                // Log to user activity
                $logStmt = $db->prepare("
                    INSERT INTO user_activity_log (user_id, action, module, description, ip_address, created_at)
                    VALUES (?, 'maintenance', 'finance', ?, ?, NOW())
                ");
                $logStmt->execute([
                    $userId,
                    "Ran maintenance task: $task - Results: " . json_encode($results),
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]);
                
                echo json_encode(['success' => true, 'results' => $results]);
                
            } catch (Exception $e) {
                error_log("[MAINTENANCE ERROR] " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        // ── Budget Quarters ───────────────────────────────────
        case 'quarters_list':
            $indId = (int)($_GET['indicator_id']??0);
            if (!$indId) throw new Exception('indicator_id required');
            echo json_encode(['success'=>true,'data'=>$ctrl->getQuartersByIndicator($indId)]);
            break;

        case 'quarter_get':
            $q = $ctrl->getQuarterById((int)($_GET['id']??0));
            echo json_encode(['success'=>(bool)$q,'data'=>$q]);
            break;

        case 'quarter_active':
            $q = $ctrl->getActiveQuarter($sessionFilter);
            echo json_encode(['success'=>true,'data'=>$q]);
            break;

        case 'quarter_create':
            $auth->requireAuth(['finance.manage_budget']);
            $acts = $input['activities'] ?? [];
            unset($input['activities']);
            if ($sessionFilter && empty($input['cep_session'])) $input['cep_session'] = $sessionFilter;
            echo json_encode($ctrl->createQuarter($input, $acts, $userId));
            break;

        case 'quarter_update':
            $auth->requireAuth(['finance.manage_budget']);
            $id   = (int)($input['id']??0);
            $acts = $input['activities'] ?? [];
            unset($input['activities'],$input['id']);
            echo json_encode($ctrl->updateQuarter($id, $input, $acts));
            break;

        case 'quarter_approve':
            if (!$isPresident) throw new Exception('Only the President can approve quarterly budgets');
            echo json_encode($ctrl->approveQuarter((int)($input['id']??0), $userId));
            break;

        case 'quarter_reactivate':
            if (!$isSuperAdmin) throw new Exception('Only Super Admin can reactivate a suspended budget');
            echo json_encode($ctrl->reactivateQuarter((int)($input['id']??0)));
            break;

        case 'quarter_delete':
            if (!$isSuperAdmin) throw new Exception('Only Super Admin can delete quarterly budgets');
            echo json_encode($ctrl->deleteQuarter((int)($input['id']??0)));
            break;

        // ── Fund Requests ─────────────────────────────────────
        case 'fund_requests':
            // Visibility scoping
            $f = ['session'=>$sessionFilter,'stage'=>$_GET['stage']??null,'search'=>$_GET['search']??null];
            // Non-president/non-admin: show own drafts + all submitted ones
            $isFinance = hasPermission($cu->permissions??[],'finance.disburse_funds');
            echo json_encode(['success'=>true]+$ctrl->listFundRequests(array_filter($f),(int)($_GET['page']??1),(int)($_GET['per_page']??20)));
            break;

        case 'my_requests':
            // Sender's own inbox
            $f = ['session'=>$sessionFilter,'requested_by'=>$userId,'stage'=>$_GET['stage']??null];
            echo json_encode(['success'=>true]+$ctrl->listFundRequests(array_filter($f),(int)($_GET['page']??1),20));
            break;

        case 'fund_request_get':
            $req = $ctrl->getFundRequestById((int)($_GET['id']??0));
            echo json_encode(['success'=>(bool)$req,'data'=>$req]);
            break;

        case 'fund_request_create':
            $auth->requireAuth(['finance.fund_requests']);
            if ($sessionFilter && empty($input['cep_session'])) $input['cep_session'] = $sessionFilter;
            echo json_encode($ctrl->createFundRequest($input, $userId));
            break;

        case 'fund_request_update':
            $auth->requireAuth(['finance.fund_requests']);
            echo json_encode($ctrl->updateFundRequest((int)($input['id']??0), $input, $userId));
            break;

        case 'fund_request_submit':
            $auth->requireAuth(['finance.fund_requests']);
            echo json_encode($ctrl->submitFundRequest((int)($input['id']??0), $userId));
            break;

        case 'fund_request_delete':
            echo json_encode($ctrl->deleteFundRequest((int)($input['id']??0), $userId, $isSuperAdmin));
            break;

        case 'fund_request_comment':
            if (empty($input['id'])||empty($input['comment'])) throw new Exception('ID and comment required');
            echo json_encode($ctrl->addComment((int)$input['id'], $userId, trim($input['comment'])));
            break;

        case 'fund_president_action':
            if (!$isPresident) throw new Exception('Only the President can approve/reject fund requests');
            $id     = (int)($input['id']??0);
            $act2   = $input['action'] ?? '';
            echo json_encode($ctrl->presidentAction($id, $act2, $userId, $input));
            break;

        case 'fund_disburse':
            $auth->requireAuth(['finance.disburse_funds']);
            echo json_encode($ctrl->disburse((int)($input['id']??0), $userId, $input));
            break;

        case 'fund_pipeline':
            echo json_encode(['success'=>true,'data'=>$ctrl->getPipeline($sessionFilter)]);
            break;

        // ── Disbursements ─────────────────────────────────────
        case 'disbursements':
            $f = ['session'=>$sessionFilter,'method'=>$_GET['method']??null,'month'=>$_GET['month']??null];
            $r = $ctrl->listDisbursements(array_filter($f),(int)($_GET['page']??1),(int)($_GET['per_page']??20));
            echo json_encode(['success'=>true]+$r);
            break;

        // ── Reports ───────────────────────────────────────────
        case 'report_overview':
            $auth->requireAuth(['finance.reports']);
            $s = $_GET['session'] ?? $sessionFilter;
            $y = $_GET['year']    ?? date('Y');
            echo json_encode(['success'=>true,'data'=>$ctrl->getFinanceOverview($s,$y)]);
            break;

        case 'report_distribution':
            $auth->requireAuth(['finance.reports']);
            $d = $ctrl->getBudgetDistributionReport($_GET['session']??$sessionFilter,$_GET['year']??date('Y'),$_GET['quarter_id']??null);
            echo json_encode(['success'=>true,'data'=>$d]);
            break;

        case 'report_disbursements':
            $auth->requireAuth(['finance.reports']);
            $f = ['pool_id'=>$_GET['pool_id']??null,'method'=>$_GET['method']??null];
            $d = $ctrl->getDisbursementReport($_GET['session']??$sessionFilter,$_GET['year']??date('Y'),array_filter($f));
            echo json_encode(['success'=>true,'data'=>$d]);
            break;

        case 'report_fund_requests':
            $auth->requireAuth(['finance.reports']);
            $f = ['stage'=>$_GET['stage']??null,'pool_id'=>$_GET['pool_id']??null];
            $d = $ctrl->getFundRequestReport($_GET['session']??$sessionFilter,$_GET['year']??date('Y'),array_filter($f));
            echo json_encode(['success'=>true,'data'=>$d]);
            break;

        // Legacy compatibility
        case 'budget_list':
            $f = ['session'=>$sessionFilter,'status'=>$_GET['status']??null];
            echo json_encode(['success'=>true,'data'=>[],'message'=>'Use indicator+quarters system']);
            break;

        default:
            throw new Exception("Unknown action: $action");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}