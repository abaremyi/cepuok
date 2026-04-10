<?php
/**
 * Membership API v2.1
 * File: modules/Membership/api/membershipApi.php
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
                $result = $mc->register($input);
    
                // If registration successful, generate membership number
                if ($result['success']) {
                    $memberId = $result['member_id'];
                    
                    // Get the member's session
                    $db = Database::getConnection();
                    $stmt = $db->prepare("SELECT cep_session FROM members WHERE id = ?");
                    $stmt->execute([$memberId]);
                    $session = $stmt->fetchColumn();
                    
                    // Generate membership number
                    $sessionPrefix = strtoupper(substr($session, 0, 1)); // D or W
                    $year = date('Y');
                    $num = str_pad($memberId, 4, '0', STR_PAD_LEFT);
                    $membershipNumber = "CEP-{$sessionPrefix}-{$year}-{$num}";
                    
                    // Update member with membership number
                    $updateStmt = $db->prepare("UPDATE members SET membership_number = ? WHERE id = ?");
                    $updateStmt->execute([$membershipNumber, $memberId]);
                    
                    $result['membership_number'] = $membershipNumber;
                }
                
                echo json_encode($result);
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
            $input = getInput();
            $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); break; }
            echo json_encode($mc->approveMember($id, $currentUser->user_id));
            break;

        // ---- Reject member ----
        case 'reject':
            $auth->requireAuth(['membership.approve']);
            $input = getInput();
            $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
            $reason = $input['reason'] ?? '';
            
            error_log("Reject API called - ID: $id, Reason: $reason");

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

        // ---- Applications list (for membership-applications.php) ----
        case 'applications':
            $auth->requireAuth(['membership.approve']);
            $session = $_GET['session'] ?? null;
            $result = $mc->getApplications($session);
            // The controller already returns ['success'=>true, 'data'=>...]
            echo json_encode($result);  // Don't wrap again
            break;
            
        // ---- Mark reviewing ----
        case 'reviewing':
            $auth->requireAuth(['membership.approve']);
            $id = (int)($_GET['id'] ?? getInput()['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); break; }
            $result = $mc->markReviewing($id, $currentUser->user_id);
            echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Marked for review' : 'Failed']);
            break;

        // ---- Bulk mark reviewing ----
        case 'bulkReviewing':
            $auth->requireAuth(['membership.approve']);
            $input = getInput();
            $ids   = array_filter(array_map('intval', $input['ids'] ?? []));
            if (empty($ids)) { echo json_encode(['success'=>false,'message'=>'No IDs']); break; }
            $count = 0;
            foreach ($ids as $id) {
                try { $mc->markReviewing($id, $currentUser->user_id); $count++; }
                catch(Exception $e) { /* skip */ }
            }
            echo json_encode(['success'=>true,'message'=>"$count application(s) marked for review"]);
            break;

        // ---- Bulk reject ----
        case 'bulkReject':
            $auth->requireAuth(['membership.approve']);
            $input  = getInput();
            $ids    = array_filter(array_map('intval', $input['ids'] ?? []));
            $reason = $input['reason'] ?? 'Rejected in bulk action';
            if (empty($ids)) { echo json_encode(['success'=>false,'message'=>'No IDs']); break; }
            $count = 0;
            foreach ($ids as $id) {
                try { $mc->rejectMember($id, $currentUser->user_id, $reason); $count++; }
                catch(Exception $e) { /* skip */ }
            }
            echo json_encode(['success'=>true,'message'=>"$count application(s) rejected"]);
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

        // ---- Update profile photo ----
        case 'updatePhoto':
            $auth->requireAuth(['membership.edit']);
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); break; }
            if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success'=>false,'message'=>'No photo uploaded or upload error']); break;
            }
            require_once $root_path . '/helpers/UploadHelper.php';
            $uploader = new UploadHelper();
            $upload = $uploader->uploadFile($_FILES['photo'], 'members');
            if (!$upload['success']) {
                echo json_encode(['success'=>false,'message'=>$upload['message']]); break;
            }
            $mc->updateMember($id, ['profile_photo' => $upload['filepath']]);
            echo json_encode(['success'=>true,'message'=>'Photo updated','path'=>$upload['filepath']]);
            break;

        // ---- Admin create member ----
        case 'adminCreate':
            // Check if user has membership.create permission OR is super admin
            $hasPermission = $currentUser->is_super_admin || 
                             (is_array($currentUser->permissions) && 
                              in_array('membership.create', $currentUser->permissions));
            
            if (!$hasPermission) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'You need membership.create permission to add members'
                ]);
                break;
            }
            
            $input = getInput();
            $input['created_by'] = $currentUser->user_id;
            echo json_encode($mc->adminCreate($input));
            break;
            
        // ---- Export applications CSV ----
        case 'exportApplications':
            $auth->requireAuth(['membership.export']);
            $session = $_GET['session'] ?? null;
            $stage   = $_GET['stage']   ?? null;
            $data    = $mc->getApplications($session);
            if ($stage) {
                $data = array_filter($data, function($a) use ($stage) { return $a['status'] === $stage; });
            }
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="cep_applications_' . date('Y-m-d') . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['#','First Name','Last Name','Email','Phone','Gender','Session','Faculty','Year Joined','Born Again','Baptized','Status','Applied']);
            $i = 1;
            foreach ($data as $a) {
                fputcsv($out, [$i++, $a['firstname'], $a['lastname'], $a['email'], $a['phone'],
                    $a['gender'], strtoupper($a['cep_session']), $a['faculty'],
                    $a['year_joined'], $a['born_again'], $a['baptized'], $a['status'], $a['applied']]);
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