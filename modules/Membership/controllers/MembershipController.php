<?php
/**
 * Membership Controller
 * File: modules/Membership/controllers/MembershipController.php
 */

require_once ROOT_PATH . '/modules/Membership/models/MembershipModel.php';
require_once ROOT_PATH . '/helpers/UploadHelper.php';

class MembershipController {
    private $model;
    private $uploadHelper;

    public function __construct() {
        $this->model = new MembershipModel();
        $this->uploadHelper = new UploadHelper();
    }

    // ============================================================
    // PUBLIC ENDPOINTS
    // ============================================================

    public function register($data) {
        try {
            // --- Validate required fields ---
            $required = ['membership_type_id','firstname','lastname','email','phone','gender','year_joined_cep','cep_session'];
            $missing = [];
            foreach ($required as $f) {
                if (empty($data[$f])) $missing[] = $f;
            }
            if ($missing) {
                return ['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)];
            }

            // --- Email format ---
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email address format'];
            }

            // --- Email uniqueness ---
            if ($this->model->emailExists($data['email'])) {
                return ['success' => false, 'message' => 'This email is already registered'];
            }

            // --- Phone format ---
            if (!preg_match('/^\+?[0-9]{10,15}$/', $data['phone'])) {
                return ['success' => false, 'message' => 'Invalid phone number format'];
            }

            // --- Phone uniqueness ---
            if ($this->model->phoneExists($data['phone'])) {
                return ['success' => false, 'message' => 'This phone number is already registered'];
            }

            // --- Session validation ---
            if (!in_array($data['cep_session'], ['day', 'weekend'])) {
                return ['success' => false, 'message' => 'Invalid CEP session selected'];
            }

            // --- Year validation ---
            $year = (int)$data['year_joined_cep'];
            if ($year < 2016 || $year > (int)date('Y')) {
                return ['success' => false, 'message' => 'Year joined must be between 2016 and ' . date('Y')];
            }

            // --- Membership type validation ---
            $types = $this->model->getMembershipTypes();
            $typeIds = array_column($types, 'id');
            if (!in_array($data['membership_type_id'], $typeIds)) {
                return ['success' => false, 'message' => 'Invalid membership type'];
            }

            // --- Sanitize talents ---
            if (!empty($data['talents'])) {
                $data['talents'] = array_filter(array_map('intval', (array)$data['talents']));
            }

            // --- Create member ---
            $result = $this->model->register($data);

            // --- Handle photo upload ---
            if ($result['success'] && !empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $upload = $this->uploadHelper->uploadFile($_FILES['profile_photo'], 'members');
                if ($upload['success']) {
                    $this->model->updateProfilePhoto($result['member_id'], $upload['filepath']);
                }
            }

            return [
                'success'   => true,
                'message'   => 'Registration successful! Your application is under review.',
                'member_id' => $result['member_id'],
            ];

        } catch (Exception $e) {
            error_log("MembershipController::register - " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    public function checkEmail($email) {
        return ['exists' => $this->model->emailExists($email)];
    }

    public function getMembershipTypes() {
        return ['success' => true, 'data' => $this->model->getMembershipTypes()];
    }

    public function getTalents() {
        return ['success' => true, 'data' => $this->model->getTalents()];
    }

    public function getFaculties() {
        $faculties = [];
        foreach (MembershipModel::$FACULTIES as $name => $code) {
            $faculties[] = ['name' => $name, 'code' => $code];
        }
        return ['success' => true, 'data' => $faculties];
    }

    // ============================================================
    // PROTECTED ADMIN ENDPOINTS
    // ============================================================

    public function getMember($id) {
        $member = $this->model->getMemberById($id);
        if (!$member) return ['success' => false, 'message' => 'Member not found'];
        return ['success' => true, 'data' => $member];
    }

    public function getAllMembers($filters = [], $page = 1, $perPage = 20) {
        $result = $this->model->getAllMembers($filters, $page, $perPage);
        return ['success' => true, 'data' => $result['data'],
                'meta' => ['total' => $result['total'], 'page' => $result['page'],
                           'per_page' => $result['per_page'], 'total_pages' => $result['total_pages']]];
    }

    public function getPendingMembers($sessionFilter = null) {
        return ['success' => true, 'data' => $this->model->getPendingMembers($sessionFilter)];
    }

    public function updateMember($id, $data) {
        try {
            $result = $this->model->updateMember($id, $data);
            return ['success' => $result, 'message' => $result ? 'Member updated' : 'No changes'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function approveMember($id, $approvedBy) {
        try {
            $this->model->approveMember($id, $approvedBy);
            return ['success' => true, 'message' => 'Member approved successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function rejectMember($id, $reviewedBy, $reason = '') {
        try {
            $this->model->rejectMember($id, $reviewedBy, $reason);
            return ['success' => true, 'message' => 'Member application rejected'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteMember($id) {
        try {
            $this->model->deleteMember($id);
            return ['success' => true, 'message' => 'Member deleted'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function assignFamily($memberId, $familyId) {
        try {
            $this->model->assignFamily($memberId, $familyId);
            return ['success' => true, 'message' => 'Family assigned successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getStatistics($sessionFilter = null) {
        return ['success' => true, 'data' => $this->model->getStatistics($sessionFilter)];
    }

    public function getFamilies($sessionFilter = null) {
        return ['success' => true, 'data' => $this->model->getFamilies($sessionFilter)];
    }
}