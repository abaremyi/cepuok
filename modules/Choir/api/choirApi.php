<?php
/**
 * Choir API
 * File: modules/Choir/api/choirApi.php
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$root = dirname(__DIR__, 4);
require_once "$root/config/paths.php";
require_once "$root/config/database.php";
require_once "$root/helpers/AuthMiddleware.php";
require_once __DIR__ . '/../controllers/ChoirController.php';

$auth   = new AuthMiddleware();
$action = $_GET['action'] ?? '';
$ctrl   = new ChoirController();

try {
    $cu    = $auth->requireAuth(['choir.view']);
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $sessF = ($cu->isSuperAdmin ?? false) ? ($_GET['session'] ?? null) : ($cu->session_type ?? null);

    switch ($action) {
        // ── Members ─────────────────────────────────────────────
        case 'members':
            $filters = array_filter([
                'session'    => $sessF,
                'voice_part' => $_GET['voice_part'] ?? null,
                'status'     => $_GET['status']     ?? null,
                'search'     => $_GET['search']     ?? null,
            ]);
            $r = $ctrl->listMembers($filters, (int)($_GET['page']??1), (int)($_GET['per_page']??30));
            echo json_encode(['success'=>true,'data'=>$r['data'],'total'=>$r['total'],'pages'=>$r['pages']]);
            break;

        case 'member_stats':
            echo json_encode(['success'=>true, 'data'=>$ctrl->getStats()]);
            break;

        case 'add_member':
            $auth->requireAuth(['choir.manage_members']);
            echo json_encode($ctrl->addMember($input));
            break;

        case 'update_member':
            $auth->requireAuth(['choir.manage_members']);
            $id = (int)($input['id'] ?? 0);
            echo json_encode($ctrl->updateMember($id, $input));
            break;

        case 'remove_member':
            $auth->requireAuth(['choir.manage_members']);
            echo json_encode($ctrl->removeMember((int)($input['id']??0)));
            break;

        // ── Songs ────────────────────────────────────────────────
        case 'songs':
            $filters = array_filter([
                'category' => $_GET['category'] ?? null,
                'status'   => $_GET['status']   ?? null,
                'language' => $_GET['language'] ?? null,
                'search'   => $_GET['search']   ?? null,
            ]);
            $r = $ctrl->listSongs($filters, (int)($_GET['page']??1), (int)($_GET['per_page']??20));
            echo json_encode(['success'=>true,'data'=>$r['data'],'total'=>$r['total'],'pages'=>$r['pages']]);
            break;

        case 'song_stats':
            echo json_encode(['success'=>true, 'data'=>$ctrl->getSongStats()]);
            break;

        case 'add_song':
            $auth->requireAuth(['choir.manage_songs']);
            echo json_encode($ctrl->addSong($input));
            break;

        case 'update_song':
            $auth->requireAuth(['choir.manage_songs']);
            echo json_encode($ctrl->updateSong((int)($input['id']??0), $input));
            break;

        case 'delete_song':
            $auth->requireAuth(['choir.manage_songs']);
            echo json_encode($ctrl->deleteSong((int)($input['id']??0)));
            break;

        // ── Rehearsals & Attendance ──────────────────────────────
        case 'rehearsals':
            $filters = array_filter(['session' => $sessF]);
            $r = $ctrl->listRehearsals($filters, (int)($_GET['page']??1), (int)($_GET['per_page']??20));
            echo json_encode(['success'=>true,'data'=>$r['data'],'total'=>$r['total'],'pages'=>$r['pages']]);
            break;

        case 'create_rehearsal':
            $auth->requireAuth(['choir.manage_attendance']);
            $input['conductor_id'] = $cu->id;
            echo json_encode($ctrl->createRehearsal($input));
            break;

        case 'attendance':
            $rid  = (int)($_GET['rehearsal_id'] ?? 0);
            echo json_encode(['success'=>true, 'data'=>$ctrl->getRehearsalAttendance($rid)]);
            break;

        case 'save_attendance':
            $auth->requireAuth(['choir.manage_attendance']);
            $rid  = (int)($input['rehearsal_id'] ?? 0);
            $rows = $input['attendance'] ?? [];
            echo json_encode($ctrl->saveAttendance($rid, $rows));
            break;

        case 'member_rate':
            $rate = $ctrl->getMemberRate((int)($_GET['id']??0));
            echo json_encode(['success'=>true, 'rate'=>$rate]);
            break;

        default:
            throw new Exception("Invalid action: $action");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}