<?php
/**
 * Membership API v2.1
 * File: modules/Membership/api/membershipApi.php
 * Handles: registration, member CRUD, family assignment, session management, handover
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Membership/controllers/MembershipController.php";
require_once $root_path . "/helpers/AuthMiddleware.php";

$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

function getInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    return $input ?: $_POST;
}

try {
    $mc   = new MembershipController();
    $auth = new AuthMiddleware();

    // ==================== PUBLIC ENDPOINTS ====================
    $publicActions = ['register','checkEmail','getMembershipTypes','getTalents','getFaculties'];

    if (in_array($action, $publicActions)) {
        switch ($action) {
            case 'register':
                $input = getInput();
                if (!$input) $input = $_POST;
                echo json_encode($mc->register($input));
                break;
            case 'checkEmail':
                $email = $_GET['email'] ?? $_POST['email'] ?? '';
                echo json_encode($mc->checkEmail($email));
                break;
            case 'getMembershipTypes':
                echo json_encode($mc->getMembershipTypes());
                break;
            case 'getTalents':
                echo json_encode($mc->getTalents());
                break;
            case 'getFaculties':
                echo json_encode($mc->getFaculties());
                break;
        }
        exit;
    }

    // ==================== PROTECTED ENDPOINTS ====================
    // Authenticate for all remaining actions
    $currentUser = $auth->requireAuth(['membership.view']);

    switch ($action) {

        // ---- Get single member ----
        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); break; }
            echo json_encode($mc->getMember($id));
            break;

        // ---- List members with filters ----
        case 'list':
            $filters = [];
            $filterKeys = ['search','status','cep_session','faculty','family_id','gender','membership_type_id','year_joined'];
            foreach ($filterKeys as $k) {
                if (!empty($_GET[$k])) $filters[$k] = $_GET[$k];
            }
            // Handle 'unassigned' family filter
            if (isset($_GET['family_id']) && $_GET['family_id'] === 'unassigned') {
                $filters['family_unassigned'] = true;
                unset($filters['family_id']);
            }
            $page    = max(1,(int)($_GET['page']??1));
            $perPage = max(1, min(100,(int)($_GET['per_page']??20)));
            echo json_encode($mc->getAllMembers($filters, $page, $perPage));
            break;

        // ---- Pending members ----
        case 'pending':
            $session = $_GET['session'] ?? null;
            echo json_encode($mc->getPendingMembers($session));
            break;

        // ---- Approve member ----
        case 'approve':
            $auth->requireAuth(['membership.approve']);
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); break; }
            echo json_encode($mc->approveMember($id, $currentUser->user_id));
            break;

        // ---- Reject member ----
        case 'reject':
            $auth->requireAuth(['membership.approve']);
            $id = (int)($_GET['id'] ?? 0);
            $input = getInput();
            $reason = $input['reason'] ?? '';
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); break; }
            echo json_encode($mc->rejectMember($id, $currentUser->user_id, $reason));
            break;

        // ---- Update member ----
        case 'update':
            $auth->requireAuth(['membership.edit']);
            $id = (int)($_GET['id'] ?? 0);
            $input = getInput();
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); break; }
            echo json_encode($mc->updateMember($id, $input));
            break;

        // ---- Assign family ----
        case 'assignFamily':
            $auth->requireAuth(['membership.edit']);
            $id = (int)($_GET['id'] ?? 0);
            $input = getInput();
            $familyId = $input['family_id'] ?? null;
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); break; }
            echo json_encode($mc->assignFamily($id, $familyId));
            break;

        // ---- Delete member ----
        case 'delete':
            $auth->requireAuth(['membership.delete']);
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); break; }
            echo json_encode($mc->deleteMember($id));
            break;

        // ---- Bulk approve ----
        case 'bulkApprove':
            $auth->requireAuth(['membership.approve']);
            $input = getInput();
            $ids = array_filter(array_map('intval', $input['ids'] ?? []));
            if (empty($ids)) { echo json_encode(['success'=>false,'message'=>'No IDs']); break; }
            $results = [];
            foreach ($ids as $id) {
                try { $mc->approveMember($id, $currentUser->user_id); $results[] = $id; }
                catch(Exception $e) { /* skip failed */ }
            }
            echo json_encode(['success'=>true,'message'=>count($results).' member(s) approved','approved'=>$results]);
            break;

        // ---- Bulk delete ----
        case 'bulkDelete':
            $auth->requireAuth(['membership.delete']);
            $input = getInput();
            $ids = array_filter(array_map('intval', $input['ids'] ?? []));
            if (empty($ids)) { echo json_encode(['success'=>false,'message'=>'No IDs']); break; }
            $count = 0;
            foreach ($ids as $id) {
                try { $mc->deleteMember($id); $count++; }
                catch(Exception $e) { /* skip */ }
            }
            echo json_encode(['success'=>true,'message'=>"$count member(s) deleted"]);
            break;

        // ---- Statistics ----
        case 'statistics':
            $session = $_GET['session'] ?? null;
            echo json_encode($mc->getStatistics($session));
            break;

        // ---- Families ----
        case 'families':
        case 'getFamilies':
            $session = $_GET['session'] ?? null;
            echo json_encode($mc->getFamilies($session));
            break;

        // ---- Toggle session portal access (super admin) ----
        case 'toggleSession':
            if (!$currentUser->is_super_admin) {
                echo json_encode(['success'=>false,'message'=>'Super admin access required']); break;
            }
            $input = getInput();
            $sessionType = $input['session_type'] ?? '';
            $enabled     = (bool)($input['enabled'] ?? false);
            $reason      = $input['reason'] ?? '';
            if (!in_array($sessionType, ['day','weekend'])) {
                echo json_encode(['success'=>false,'message'=>'Invalid session type']); break;
            }
            $model = new MembershipModel();
            $result = $model->toggleSessionPortal($sessionType, $enabled, $currentUser->user_id, $reason);
            echo json_encode(['success'=>$result, 'message'=>$result ? 'Session updated' : 'Failed to update session']);
            break;

        // ---- Save/create session settings (super admin) ----
        case 'saveSession':
            if (!$currentUser->is_super_admin) {
                echo json_encode(['success'=>false,'message'=>'Super admin access required']); break;
            }
            $input = getInput();
            $db = Database::getConnection();
            $sessionType = $input['session_type'] ?? '';
            $id = (int)($input['id'] ?? 0);

            if (!in_array($sessionType, ['day','weekend'])) {
                echo json_encode(['success'=>false,'message'=>'Invalid session type']); break;
            }

            if ($id > 0) {
                // Update existing
                $sql = "UPDATE cep_sessions SET session_label=:label, academic_year=:ay, 
                        committee_year_id=:cy, handover_date=:hd, updated_at=NOW()
                        WHERE id=:id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':label' => $input['session_label'],
                    ':ay'    => $input['academic_year'],
                    ':cy'    => $input['committee_year_id'] ?: null,
                    ':hd'    => $input['handover_date'] ?: null,
                    ':id'    => $id,
                ]);
            } else {
                // Create new (deactivate old)
                $db->prepare("UPDATE cep_sessions SET is_current=0 WHERE session_type=:t")->execute([':t' => $sessionType]);
                $sql = "INSERT INTO cep_sessions (session_type, session_label, academic_year, committee_year_id, handover_date, portal_enabled, is_current)
                        VALUES (:type, :label, :ay, :cy, :hd, 1, 1)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':type'  => $sessionType,
                    ':label' => $input['session_label'],
                    ':ay'    => $input['academic_year'],
                    ':cy'    => $input['committee_year_id'] ?: null,
                    ':hd'    => $input['handover_date'] ?: null,
                ]);
            }
            echo json_encode(['success'=>true,'message'=>'Session saved successfully']);
            break;

        // ---- Submit committee handover ----
        case 'submitHandover':
            if (!$currentUser->is_super_admin) {
                echo json_encode(['success'=>false,'message'=>'Super admin access required']); break;
            }
            $input = getInput();
            $db = Database::getConnection();
            $sql = "INSERT INTO committee_handovers 
                    (cep_session, outgoing_year_id, incoming_year_id, handover_date, handover_summary,
                     financial_balance, pending_issues, recommendations, conducted_by, status)
                    VALUES (:cs, :oy, :iy, :hd, :hs, :fb, :pi, :rec, :cb, 'completed')";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':cs'  => $input['cep_session'],
                ':oy'  => (int)$input['outgoing_year_id'],
                ':iy'  => !empty($input['incoming_year_id']) ? (int)$input['incoming_year_id'] : null,
                ':hd'  => $input['handover_date'],
                ':hs'  => $input['handover_summary'],
                ':fb'  => !empty($input['financial_balance']) ? (float)$input['financial_balance'] : 0,
                ':pi'  => $input['pending_issues'] ?? null,
                ':rec' => $input['recommendations'] ?? null,
                ':cb'  => $currentUser->user_id,
            ]);
            // Update outgoing year to not current
            if (!empty($input['incoming_year_id'])) {
                $db->prepare("UPDATE leadership_years SET is_current=0 WHERE id=:id")->execute([':id'=>(int)$input['outgoing_year_id']]);
                $db->prepare("UPDATE leadership_years SET is_current=1 WHERE id=:id")->execute([':id'=>(int)$input['incoming_year_id']]);
            }
            echo json_encode(['success'=>true,'message'=>'Handover completed successfully']);
            break;

        // ---- Export members (CSV) ----
        case 'export':
            $auth->requireAuth(['membership.export']);
            // Build query
            $sessionFilter = $_GET['cep_session'] ?? null;
            $statusFilter  = $_GET['status'] ?? null;
            $searchFilter  = $_GET['search'] ?? null;
            $where = ['1=1'];
            $params = [];
            if ($sessionFilter) { $where[] = "m.cep_session=:s"; $params[':s'] = $sessionFilter; }
            if ($statusFilter)  { $where[] = "m.status=:st"; $params[':st'] = $statusFilter; }
            if ($searchFilter)  { $where[] = "(m.firstname LIKE :q OR m.lastname LIKE :q OR m.email LIKE :q)"; $params[':q'] = "%$searchFilter%"; }
            
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT m.membership_number, m.firstname, m.lastname, m.email, m.phone, m.gender, m.cep_session, m.faculty, m.program, m.academic_year, m.church_name, m.is_born_again, m.is_baptized, m.status, m.year_joined_cep, cf.family_name, m.created_at FROM members m LEFT JOIN cep_families cf ON m.family_id=cf.id WHERE " . implode(' AND ',$where) . " ORDER BY m.cep_session, m.firstname");
            $stmt->execute($params);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Output CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="cep_members_' . date('Y-m-d') . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['#', 'First Name', 'Last Name', 'Email', 'Phone', 'Gender', 'Session', 'Faculty', 'Program', 'Year', 'Church', 'Born Again', 'Baptized', 'Status', 'Year Joined', 'Family', 'Registered']);
            $i = 1;
            foreach ($members as $m) {
                fputcsv($out, [$i++, $m['firstname'], $m['lastname'], $m['email'], $m['phone'], $m['gender'], strtoupper($m['cep_session']), $m['faculty'], $m['program'], $m['academic_year'], $m['church_name'], $m['is_born_again'], $m['is_baptized'], $m['status'], $m['year_joined_cep'], $m['family_name'], $m['created_at']]);
            }
            fclose($out);
            exit;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action: ' . $action]);
            break;
    }

} catch (Exception $e) {
    error_log("Membership API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error occurred']);
}