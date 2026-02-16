<?php
/**
 * Membership Model
 * File: modules/Membership/models/MembershipModel.php
 * Handles all database operations for membership management
 */

class MembershipModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Create new member
     * @param array $data Member data
     * @return int|bool Member ID or false
     */
    public function createMember($data)
    {
        try {
            $query = "INSERT INTO members (
                membership_type_id, firstname, lastname, email, phone, gender, 
                date_of_birth, address, year_joined_cep, church_id, other_church_name,
                is_born_again, is_baptized, profile_photo, bio, status
            ) VALUES (
                :membership_type_id, :firstname, :lastname, :email, :phone, :gender,
                :date_of_birth, :address, :year_joined_cep, :church_id, :other_church_name,
                :is_born_again, :is_baptized, :profile_photo, :bio, :status
            )";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':membership_type_id' => $data['membership_type_id'],
                ':firstname' => $data['firstname'],
                ':lastname' => $data['lastname'],
                ':email' => $data['email'],
                ':phone' => $data['phone'],
                ':gender' => $data['gender'],
                ':date_of_birth' => $data['date_of_birth'] ?? null,
                ':address' => $data['address'] ?? null,
                ':year_joined_cep' => $data['year_joined_cep'],
                ':church_id' => $data['church_id'],
                ':other_church_name' => $data['other_church_name'] ?? null,
                ':is_born_again' => $data['is_born_again'] ?? 'Prefer not to say',
                ':is_baptized' => $data['is_baptized'] ?? 'Prefer not to say',
                ':profile_photo' => $data['profile_photo'] ?? null,
                ':bio' => $data['bio'] ?? null,
                ':status' => $data['status'] ?? 'pending'
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Membership Model Error (createMember): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add member talents
     * @param int $memberId Member ID
     * @param array $talentIds Array of talent IDs
     * @return bool Success status
     */
    public function addMemberTalents($memberId, $talentIds)
    {
        try {
            if (empty($talentIds)) {
                return true;
            }

            $query = "INSERT INTO member_talents (member_id, talent_id) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);

            foreach ($talentIds as $talentId) {
                $stmt->execute([$memberId, $talentId]);
            }

            return true;
        } catch (PDOException $e) {
            error_log("Membership Model Error (addMemberTalents): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists
     * @param string $email Email to check
     * @param int $excludeId Member ID to exclude (for updates)
     * @return bool True if exists
     */
    public function emailExists($email, $excludeId = 0)
    {
        try {
            $query = "SELECT COUNT(*) FROM members WHERE email = :email";
            
            if ($excludeId > 0) {
                $query .= " AND id != :exclude_id";
            }

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            
            if ($excludeId > 0) {
                $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Membership Model Error (emailExists): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if phone exists
     * @param string $phone Phone to check
     * @param int $excludeId Member ID to exclude (for updates)
     * @return bool True if exists
     */
    public function phoneExists($phone, $excludeId = 0)
    {
        try {
            $query = "SELECT COUNT(*) FROM members WHERE phone = :phone";
            
            if ($excludeId > 0) {
                $query .= " AND id != :exclude_id";
            }

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
            
            if ($excludeId > 0) {
                $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Membership Model Error (phoneExists): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get member by ID
     * @param int $id Member ID
     * @return array|null Member data
     */
    public function getMemberById($id)
    {
        try {
            $query = "SELECT m.*, 
                             mt.type_name as membership_type_name,
                             c.church_name,
                             CONCAT(u.firstname, ' ', u.lastname) as approved_by_name
                      FROM members m
                      LEFT JOIN membership_types mt ON m.membership_type_id = mt.id
                      LEFT JOIN churches c ON m.church_id = c.id
                      LEFT JOIN users u ON m.approved_by = u.id
                      WHERE m.id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($member) {
                // Get member talents
                $member['talents'] = $this->getMemberTalents($id);
            }

            return $member;
        } catch (PDOException $e) {
            error_log("Membership Model Error (getMemberById): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get member by email
     * @param string $email Email address
     * @return array|null Member data
     */
    public function getMemberByEmail($email)
    {
        try {
            $query = "SELECT m.*, 
                             mt.type_name as membership_type_name,
                             c.church_name
                      FROM members m
                      LEFT JOIN membership_types mt ON m.membership_type_id = mt.id
                      LEFT JOIN churches c ON m.church_id = c.id
                      WHERE m.email = :email";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($member) {
                $member['talents'] = $this->getMemberTalents($member['id']);
            }

            return $member;
        } catch (PDOException $e) {
            error_log("Membership Model Error (getMemberByEmail): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get member talents
     * @param int $memberId Member ID
     * @return array Array of talent IDs and names
     */
    public function getMemberTalents($memberId)
    {
        try {
            $query = "SELECT mt.talent_id, t.talent_name, t.category, mt.proficiency_level, mt.notes
                      FROM member_talents mt
                      JOIN talents_gifts t ON mt.talent_id = t.id
                      WHERE mt.member_id = :member_id";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':member_id' => $memberId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Membership Model Error (getMemberTalents): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all members with filters and pagination
     * @param array $filters Filter criteria
     * @param int $limit Number of records
     * @param int $offset Starting position
     * @return array Array of members
     */
    public function getAllMembers($filters = [], $limit = 20, $offset = 0)
    {
        try {
            $query = "SELECT m.*, 
                             mt.type_name as membership_type_name,
                             c.church_name,
                             CONCAT(u.firstname, ' ', u.lastname) as approved_by_name
                      FROM members m
                      LEFT JOIN membership_types mt ON m.membership_type_id = mt.id
                      LEFT JOIN churches c ON m.church_id = c.id
                      LEFT JOIN users u ON m.approved_by = u.id
                      WHERE 1=1";

            $params = [];

            // Apply filters
            if (!empty($filters['search'])) {
                $query .= " AND (m.firstname LIKE :search OR m.lastname LIKE :search 
                            OR m.email LIKE :search OR m.phone LIKE :search 
                            OR m.membership_number LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if (!empty($filters['membership_type_id'])) {
                $query .= " AND m.membership_type_id = :membership_type_id";
                $params[':membership_type_id'] = $filters['membership_type_id'];
            }

            if (!empty($filters['status'])) {
                $query .= " AND m.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['year_joined'])) {
                $query .= " AND m.year_joined_cep = :year_joined";
                $params[':year_joined'] = $filters['year_joined'];
            }

            if (!empty($filters['church_id'])) {
                $query .= " AND m.church_id = :church_id";
                $params[':church_id'] = $filters['church_id'];
            }

            if (!empty($filters['gender'])) {
                $query .= " AND m.gender = :gender";
                $params[':gender'] = $filters['gender'];
            }

            $query .= " ORDER BY m.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Membership Model Error (getAllMembers): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total members with filters
     * @param array $filters Filter criteria
     * @return int Total count
     */
    public function countMembers($filters = [])
    {
        try {
            $query = "SELECT COUNT(*) FROM members m WHERE 1=1";
            $params = [];

            // Apply same filters as getAllMembers
            if (!empty($filters['search'])) {
                $query .= " AND (m.firstname LIKE :search OR m.lastname LIKE :search 
                            OR m.email LIKE :search OR m.phone LIKE :search 
                            OR m.membership_number LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if (!empty($filters['membership_type_id'])) {
                $query .= " AND m.membership_type_id = :membership_type_id";
                $params[':membership_type_id'] = $filters['membership_type_id'];
            }

            if (!empty($filters['status'])) {
                $query .= " AND m.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['year_joined'])) {
                $query .= " AND m.year_joined_cep = :year_joined";
                $params[':year_joined'] = $filters['year_joined'];
            }

            if (!empty($filters['church_id'])) {
                $query .= " AND m.church_id = :church_id";
                $params[':church_id'] = $filters['church_id'];
            }

            if (!empty($filters['gender'])) {
                $query .= " AND m.gender = :gender";
                $params[':gender'] = $filters['gender'];
            }

            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Membership Model Error (countMembers): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update member
     * @param int $id Member ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function updateMember($id, $data)
    {
        try {
            $fields = [];
            $params = [':id' => $id];

            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            $query = "UPDATE members SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Membership Model Error (updateMember): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve member
     * @param int $id Member ID
     * @param int $approvedBy User ID who approved
     * @return bool Success status
     */
    public function approveMember($id, $approvedBy)
    {
        try {
            $query = "UPDATE members 
                      SET status = 'active', 
                          approved_by = :approved_by, 
                          approved_at = NOW() 
                      WHERE id = :id";

            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':id' => $id,
                ':approved_by' => $approvedBy
            ]);
        } catch (PDOException $e) {
            error_log("Membership Model Error (approveMember): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete member
     * @param int $id Member ID
     * @return bool Success status
     */
    public function deleteMember($id)
    {
        try {
            $query = "DELETE FROM members WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Membership Model Error (deleteMember): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all membership types
     * @return array Array of membership types
     */
    public function getMembershipTypes()
    {
        try {
            $query = "SELECT * FROM membership_types WHERE is_active = 1 ORDER BY id";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Membership Model Error (getMembershipTypes): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all churches
     * @return array Array of churches
     */
    public function getChurches()
    {
        try {
            $query = "SELECT * FROM churches WHERE is_active = 1 ORDER BY church_name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Membership Model Error (getChurches): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all talents/gifts
     * @return array Array of talents grouped by category
     */
    public function getTalents()
    {
        try {
            $query = "SELECT * FROM talents_gifts WHERE is_active = 1 ORDER BY category, talent_name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $talents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by category
            $grouped = [];
            foreach ($talents as $talent) {
                $category = $talent['category'];
                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }
                $grouped[$category][] = $talent;
            }

            return $grouped;
        } catch (PDOException $e) {
            error_log("Membership Model Error (getTalents): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get membership statistics
     * @return array Statistics data
     */
    public function getStatistics()
    {
        try {
            $stats = [];

            // Total members by status
            $query = "SELECT status, COUNT(*) as count FROM members GROUP BY status";
            $stmt = $this->db->query($query);
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Total members by type
            $query = "SELECT mt.type_name, COUNT(m.id) as count 
                      FROM membership_types mt 
                      LEFT JOIN members m ON mt.id = m.membership_type_id 
                      GROUP BY mt.id";
            $stmt = $this->db->query($query);
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Total members by gender
            $query = "SELECT gender, COUNT(*) as count FROM members GROUP BY gender";
            $stmt = $this->db->query($query);
            $stats['by_gender'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Total members by year
            $query = "SELECT year_joined_cep, COUNT(*) as count 
                      FROM members 
                      GROUP BY year_joined_cep 
                      ORDER BY year_joined_cep DESC";
            $stmt = $this->db->query($query);
            $stats['by_year'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            return $stats;
        } catch (PDOException $e) {
            error_log("Membership Model Error (getStatistics): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log member activity
     * @param int $memberId Member ID
     * @param string $activityType Activity type
     * @param string $description Activity description
     * @return bool Success status
     */
    public function logActivity($memberId, $activityType, $description = null)
    {
        try {
            $query = "INSERT INTO member_activities (member_id, activity_type, activity_description, ip_address, user_agent) 
                      VALUES (:member_id, :activity_type, :description, :ip_address, :user_agent)";

            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':member_id' => $memberId,
                ':activity_type' => $activityType,
                ':description' => $description,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Membership Model Error (logActivity): " . $e->getMessage());
            return false;
        }
    }
}