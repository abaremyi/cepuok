<?php
/**
 * Membership Model
 * File: modules/Membership/models/MembershipModel.php
 * Handles all member data operations with session awareness
 */

require_once ROOT_PATH . '/config/database.php';

class MembershipModel {
    private $db;

    // Faculty options as defined in system documentation
    public static $FACULTIES = [
        'Information Technology' => 'IT',
        'Law' => 'Law',
        'Finance' => 'Finance',
        'Accounting' => 'Accounting',
        'Procurement' => 'Procurement',
        'Education' => 'Education',
        'Economics' => 'Economics',
        'Graduate School' => 'Graduate',
    ];

    // Academic years
    public static $ACADEMIC_YEARS = ['Year 1', 'Year 2', 'Year 3', 'Year 4', 'Year 5', 'Graduate'];

    public function __construct($db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    // ============================================================
    // REGISTRATION & PUBLIC METHODS
    // ============================================================

    /**
     * Register a new member
     */
    public function register($data) {
        try {
            $this->db->beginTransaction();

            // Generate unique membership number stub (full number assigned on approval)
            $membershipNumber = null;

            $sql = "INSERT INTO members (
                membership_type_id, firstname, lastname, email, phone, gender,
                date_of_birth, address, year_joined_cep, cep_session, faculty,
                program, academic_year, church_name,
                is_born_again, is_baptized, bio, status, created_at
            ) VALUES (
                :membership_type_id, :firstname, :lastname, :email, :phone, :gender,
                :date_of_birth, :address, :year_joined_cep, :cep_session, :faculty,
                :program, :academic_year, :church_name,
                :is_born_again, :is_baptized, :bio, 'pending', NOW()
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':membership_type_id' => $data['membership_type_id'] ?? 1,
                ':firstname'          => trim($data['firstname']),
                ':lastname'           => trim($data['lastname']),
                ':email'              => strtolower(trim($data['email'])),
                ':phone'              => trim($data['phone']),
                ':gender'             => $data['gender'],
                ':date_of_birth'      => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                ':address'            => $data['address'] ?? null,
                ':year_joined_cep'    => $data['year_joined_cep'],
                ':cep_session'        => $data['cep_session'] ?? 'day',
                ':faculty'            => $data['faculty'] ?? null,
                ':program'            => !empty($data['program']) ? trim($data['program']) : null,
                ':academic_year'      => $data['academic_year'] ?? null,
                ':church_name'        => !empty($data['church_name']) ? trim($data['church_name']) : null,
                ':is_born_again'      => $data['is_born_again'] ?? 'Prefer not to say',
                ':is_baptized'        => $data['is_baptized'] ?? 'Prefer not to say',
                ':bio'                => !empty($data['bio']) ? trim($data['bio']) : null,
            ]);

            $memberId = $this->db->lastInsertId();

            // Insert membership application record
            $appSql = "INSERT INTO membership_applications (member_id, application_type, status, submission_date)
                       VALUES (:member_id, 'new', 'submitted', NOW())";
            $appStmt = $this->db->prepare($appSql);
            $appStmt->execute([':member_id' => $memberId]);

            // Insert talents if provided
            if (!empty($data['talents']) && is_array($data['talents'])) {
                $talentSql = "INSERT IGNORE INTO member_talents (member_id, talent_id) VALUES (:member_id, :talent_id)";
                $talentStmt = $this->db->prepare($talentSql);
                foreach ($data['talents'] as $talentId) {
                    if (is_numeric($talentId)) {
                        $talentStmt->execute([':member_id' => $memberId, ':talent_id' => (int)$talentId]);
                    }
                }
            }

            // Log activity
            $logSql = "INSERT INTO member_activities (member_id, activity_type, activity_description, ip_address, user_agent)
                       VALUES (:member_id, 'registration', 'Member registered', :ip, :ua)";
            $logStmt = $this->db->prepare($logSql);
            $logStmt->execute([
                ':member_id' => $memberId,
                ':ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua'        => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            $this->db->commit();
            return ['success' => true, 'member_id' => $memberId];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("MembershipModel::register - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload profile photo after registration
     */
    public function updateProfilePhoto($memberId, $photoPath) {
        $sql = "UPDATE members SET profile_photo = :photo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':photo' => $photoPath, ':id' => $memberId]);
    }

    /**
     * Check email availability
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM members WHERE email = :email";
        $params = [':email' => strtolower(trim($email))];
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check phone availability
     */
    public function phoneExists($phone, $excludeId = null) {
        $sql = "SELECT id FROM members WHERE phone = :phone";
        $params = [':phone' => $phone];
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get membership types (active only)
     */
    public function getMembershipTypes() {
        $sql = "SELECT id, type_name, description FROM membership_types WHERE is_active = 1 ORDER BY id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get talents grouped by category
     */
    public function getTalents() {
        $sql = "SELECT id, talent_name, category FROM talents_gifts WHERE is_active = 1 ORDER BY category, talent_name";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['category']][] = $row;
        }
        return $grouped;
    }

    // ============================================================
    // ADMIN / PORTAL METHODS
    // ============================================================

    /**
     * Get single member by ID with full details
     */
    public function getMemberById($id) {
        $sql = "SELECT m.*, 
                       mt.type_name AS membership_type_name,
                       cf.family_name, cf.family_code, cf.color_code AS family_color,
                       u.email AS user_email, u.status AS user_status,
                       r.name AS user_role,
                       approver.firstname AS approved_by_firstname,
                       approver.lastname AS approved_by_lastname
                FROM members m
                LEFT JOIN membership_types mt ON m.membership_type_id = mt.id
                LEFT JOIN cep_families cf ON m.family_id = cf.id
                LEFT JOIN users u ON m.user_id = u.id
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN users approver ON m.approved_by = approver.id
                WHERE m.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($member) {
            // Attach talents
            $talentSql = "SELECT tg.id, tg.talent_name, tg.category, mt.proficiency_level
                          FROM member_talents mt
                          JOIN talents_gifts tg ON mt.talent_id = tg.id
                          WHERE mt.member_id = :mid";
            $tStmt = $this->db->prepare($talentSql);
            $tStmt->execute([':mid' => $id]);
            $member['talents'] = $tStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $member;
    }

    /**
     * Get all members with filters, pagination, and session awareness
     */
    public function getAllMembers($filters = [], $page = 1, $perPage = 20) {
        $where = ['1=1'];
        $params = [];

        // Session filter (critical for portal separation)
        if (!empty($filters['cep_session'])) {
            $where[] = "m.cep_session = :cep_session";
            $params[':cep_session'] = $filters['cep_session'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(m.firstname LIKE :search OR m.lastname LIKE :search OR m.email LIKE :search 
                         OR m.phone LIKE :search OR m.membership_number LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[] = "m.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['membership_type_id'])) {
            $where[] = "m.membership_type_id = :mtid";
            $params[':mtid'] = (int)$filters['membership_type_id'];
        }

        if (!empty($filters['faculty'])) {
            $where[] = "m.faculty = :faculty";
            $params[':faculty'] = $filters['faculty'];
        }

        if (!empty($filters['family_id'])) {
            $where[] = "m.family_id = :family_id";
            $params[':family_id'] = (int)$filters['family_id'];
        }

        if (!empty($filters['gender'])) {
            $where[] = "m.gender = :gender";
            $params[':gender'] = $filters['gender'];
        }

        if (!empty($filters['year_joined'])) {
            $where[] = "m.year_joined_cep = :year_joined";
            $params[':year_joined'] = (int)$filters['year_joined'];
        }

        $whereStr = implode(' AND ', $where);

        // Count total
        $countSql = "SELECT COUNT(*) FROM members m WHERE $whereStr";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Pagination
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT m.id, m.membership_number, m.firstname, m.lastname, m.email, m.phone,
                       m.gender, m.cep_session, m.faculty, m.program, m.academic_year,
                       m.church_name, m.status, m.year_joined_cep, m.profile_photo,
                       m.is_born_again, m.is_baptized, m.created_at, m.approved_at,
                       mt.type_name AS membership_type_name,
                       cf.family_name, cf.color_code AS family_color
                FROM members m
                LEFT JOIN membership_types mt ON m.membership_type_id = mt.id
                LEFT JOIN cep_families cf ON m.family_id = cf.id
                WHERE $whereStr
                ORDER BY m.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'        => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    /**
     * Get pending applications
     */
    public function getPendingMembers($sessionFilter = null) {
        $where = "m.status = 'pending'";
        $params = [];
        if ($sessionFilter) {
            $where .= " AND m.cep_session = :session";
            $params[':session'] = $sessionFilter;
        }

        $sql = "SELECT m.id, m.firstname, m.lastname, m.email, m.phone, m.gender,
                       m.cep_session, m.faculty, m.program, m.church_name,
                       m.is_born_again, m.is_baptized, m.year_joined_cep, m.created_at,
                       mt.type_name AS membership_type_name,
                       ma.submission_date
                FROM members m
                LEFT JOIN membership_types mt ON m.membership_type_id = mt.id
                LEFT JOIN membership_applications ma ON ma.member_id = m.id AND ma.status = 'submitted'
                WHERE $where
                ORDER BY m.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Approve member
     */
    public function approveMember($memberId, $approvedBy) {
        try {
            $this->db->beginTransaction();

            // Update member status
            $sql = "UPDATE members SET status = 'active', approved_by = :approved_by, 
                    approved_at = NOW(), updated_at = NOW()
                    WHERE id = :id AND status = 'pending'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':approved_by' => $approvedBy, ':id' => $memberId]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Member not found or already approved");
            }

            // Update application status
            $appSql = "UPDATE membership_applications SET status = 'approved', 
                       review_date = NOW(), reviewed_by = :reviewer
                       WHERE member_id = :member_id AND status = 'submitted'";
            $appStmt = $this->db->prepare($appSql);
            $appStmt->execute([':reviewer' => $approvedBy, ':member_id' => $memberId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Reject member
     */
    public function rejectMember($memberId, $reviewedBy, $reason = '') {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE members SET status = 'inactive', updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $memberId]);

            $appSql = "UPDATE membership_applications SET status = 'rejected',
                       review_date = NOW(), reviewed_by = :reviewer, rejection_reason = :reason
                       WHERE member_id = :member_id";
            $appStmt = $this->db->prepare($appSql);
            $appStmt->execute([':reviewer' => $reviewedBy, ':reason' => $reason, ':member_id' => $memberId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update member data
     */
    public function updateMember($id, $data) {
        $allowed = ['firstname','lastname','phone','gender','date_of_birth','address',
                    'year_joined_cep','cep_session','faculty','program','academic_year',
                    'church_name','is_born_again','is_baptized','bio','status',
                    'membership_type_id','family_id','profile_photo'];
        
        $sets = [];
        $params = [':id' => $id];
        
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($sets)) return false;

        $sets[] = "updated_at = NOW()";
        $sql = "UPDATE members SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Assign member to family
     */
    public function assignFamily($memberId, $familyId) {
        $sql = "UPDATE members SET family_id = :family_id, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':family_id' => $familyId ?: null, ':id' => $memberId]);
    }

    /**
     * Delete member (admin only)
     */
    public function deleteMember($id) {
        $sql = "DELETE FROM members WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get statistics - session-aware
     */
    public function getStatistics($sessionFilter = null) {
        $sessionWhere = $sessionFilter ? "AND cep_session = '$sessionFilter'" : "";

        $sql = "SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
            SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended,
            SUM(CASE WHEN gender = 'Male' $sessionWhere THEN 1 ELSE 0 END) as male,
            SUM(CASE WHEN gender = 'Female' $sessionWhere THEN 1 ELSE 0 END) as female,
            SUM(CASE WHEN cep_session = 'day' THEN 1 ELSE 0 END) as day_session,
            SUM(CASE WHEN cep_session = 'weekend' THEN 1 ELSE 0 END) as weekend_session,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_30_days
            FROM members WHERE 1=1 $sessionWhere";

        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get faculty distribution
     */
    public function getFacultyStats($sessionFilter = null) {
        $where = $sessionFilter ? "WHERE cep_session = :session" : "WHERE 1=1";
        $params = $sessionFilter ? [':session' => $sessionFilter] : [];
        $sql = "SELECT faculty, COUNT(*) as count FROM members $where AND faculty IS NOT NULL
                GROUP BY faculty ORDER BY count DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================================
    // FAMILIES
    // ============================================================

    /**
     * Get all families with member counts
     */
    public function getFamilies($sessionFilter = null) {
        $where = $sessionFilter ? "WHERE cf.cep_session = :session OR cf.cep_session = 'both'" : "WHERE 1=1";
        $params = $sessionFilter ? [':session' => $sessionFilter] : [];

        $sql = "SELECT cf.*,
                       COUNT(m.id) as member_count,
                       pu.firstname AS parent_firstname, pu.lastname AS parent_lastname,
                       cu.firstname AS co_parent_firstname, cu.lastname AS co_parent_lastname
                FROM cep_families cf
                LEFT JOIN members m ON m.family_id = cf.id AND m.status = 'active'
                LEFT JOIN users pu ON cf.parent_user_id = pu.id
                LEFT JOIN users cu ON cf.co_parent_user_id = cu.id
                $where
                GROUP BY cf.id ORDER BY cf.cep_session, cf.family_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get family with members
     */
    public function getFamilyWithMembers($familyId) {
        $family = $this->db->prepare("SELECT * FROM cep_families WHERE id = :id");
        $family->execute([':id' => $familyId]);
        $familyData = $family->fetch(PDO::FETCH_ASSOC);

        if ($familyData) {
            $memberSql = "SELECT id, firstname, lastname, email, phone, gender, faculty, profile_photo, status
                          FROM members WHERE family_id = :fid ORDER BY firstname";
            $mStmt = $this->db->prepare($memberSql);
            $mStmt->execute([':fid' => $familyId]);
            $familyData['members'] = $mStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $familyData;
    }

    // ============================================================
    // SESSION MANAGEMENT
    // ============================================================

    /**
     * Get current portal sessions
     */
    public function getPortalSessions() {
        $sql = "SELECT cs.*, ly.year_label AS committee_year_label,
                       lu.firstname AS locked_by_name, lu.lastname AS locked_by_lastname
                FROM cep_sessions cs
                LEFT JOIN leadership_years ly ON cs.committee_year_id = ly.id
                LEFT JOIN users lu ON cs.locked_by = lu.id
                WHERE cs.is_current = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Toggle portal access for a session
     */
    public function toggleSessionPortal($sessionType, $enabled, $userId, $reason = '') {
        $sql = "UPDATE cep_sessions SET portal_enabled = :enabled, 
                portal_locked_reason = :reason,
                locked_by = :user_id, locked_at = :locked_at,
                updated_at = NOW()
                WHERE session_type = :session AND is_current = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':enabled'    => $enabled ? 1 : 0,
            ':reason'     => $enabled ? null : $reason,
            ':user_id'    => $enabled ? null : $userId,
            ':locked_at'  => $enabled ? null : date('Y-m-d H:i:s'),
            ':session'    => $sessionType,
        ]);
    }

    /**
     * Check if portal session is accessible
     */
    public function isSessionAccessible($sessionType) {
        $sql = "SELECT portal_enabled, portal_locked_reason FROM cep_sessions 
                WHERE session_type = :session AND is_current = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':session' => $sessionType]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?? ['portal_enabled' => 1, 'portal_locked_reason' => null];
    }
}