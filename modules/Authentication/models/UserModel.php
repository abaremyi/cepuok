<?php
/**
 * User Model - Updated with all user management functions
 * File: modules/Authentication/models/UserModel.php
 */

class UserModel
{
    private $db;
    private $table = 'users';
    private $rolesTable = 'roles';
    private $membersTable = 'members';

    public function __construct($db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            $this->db = Database::getInstance();
        }
    }

    /**
     * Check if email/phone exists
     */
    public function userExists($email, $phone)
    {
        try {
            $query = "SELECT COUNT(*) FROM users WHERE email = :email OR phone = :phone";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;

        } catch (PDOException $e) {
            error_log("User Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists (for validation)
     */
    public function emailExists($email, $excludeId = null)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
            $params = [':email' => $email];
            
            if ($excludeId) {
                $sql .= " AND id != :id";
                $params[':id'] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;

        } catch (Exception $e) {
            error_log("UserModel::emailExists - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = :username";
            $params = [':username' => $username];
            
            if ($excludeId) {
                $sql .= " AND id != :id";
                $params[':id'] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;

        } catch (Exception $e) {
            error_log("UserModel::usernameExists - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user by email or phone for login
     */
    public function getUserByEmailOrPhone($identifier)
    {
        try {
            $query = "SELECT u.*, r.name as role_name, r.is_super_admin,
                             CONCAT(u.firstname, ' ', u.lastname) as full_name
                      FROM users u
                      JOIN roles r ON u.role_id = r.id
                      WHERE u.email = :email_id OR u.phone = :phone_id OR u.username = :username_id 
                      LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':email_id' => $identifier,
                ':phone_id' => $identifier,
                ':username_id' => $identifier,
            ]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Fetch permissions
            if ($user) {
                $user['permissions'] = $this->getUserPermissions($user['role_id']);
            }

            return $user;

        } catch (PDOException $e) {
            error_log("User Model Error in getUserByEmailOrPhone: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user permissions as comma-separated string
     */
    private function getUserPermissions($roleId)
    {
        try {
            $query = "SELECT CONCAT(p.module, '.', p.action) as permission
                      FROM role_permissions rp
                      JOIN permissions p ON rp.permission_id = p.id
                      WHERE rp.role_id = :role_id";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':role_id' => $roleId]);
            $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            
            return implode(',', $permissions);

        } catch (PDOException $e) {
            error_log("User Model Error in getUserPermissions: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Get user by ID with role info
     */
    public function getUserById($id)
    {
        try {
            $sql = "SELECT u.*, r.name as role_name, r.is_super_admin,
                           CONCAT(u.firstname, ' ', u.lastname) as full_name,
                           m.membership_number,
                           (SELECT COUNT(*) FROM user_sessions WHERE user_id = u.id AND expires_at > NOW()) as active_sessions
                    FROM {$this->table} u
                    LEFT JOIN roles r ON u.role_id = r.id
                    LEFT JOIN members m ON u.member_id = m.id
                    WHERE u.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("UserModel::getUserById - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all users with filters (for admin)
     */
    public function getAllUsers($filters = [])
    {
        try {
            $sql = "SELECT u.*, r.name as role_name, r.is_super_admin,
                           CONCAT(u.firstname, ' ', u.lastname) as full_name,
                           m.membership_number,
                           CONCAT(creator.firstname, ' ', creator.lastname) as created_by_name,
                           (SELECT COUNT(*) FROM user_sessions WHERE user_id = u.id AND expires_at > NOW()) as active_sessions
                    FROM {$this->table} u
                    LEFT JOIN roles r ON u.role_id = r.id
                    LEFT JOIN members m ON u.member_id = m.id
                    LEFT JOIN users creator ON u.created_by = creator.id
                    WHERE 1=1";
            
            $params = [];

            // Apply filters
            if (!empty($filters['status'])) {
                $sql .= " AND u.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['role_id'])) {
                $sql .= " AND u.role_id = :role_id";
                $params[':role_id'] = $filters['role_id'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (u.firstname LIKE :search OR u.lastname LIKE :search OR u.email LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            $sql .= " ORDER BY u.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("UserModel::getAllUsers - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new user
     */
    public function createUser($data)
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO {$this->table} (
                        role_id, member_id, firstname, lastname, username, email,
                        phone, password, photo, bio, email_verified, status,
                        is_adepr_member, can_manage_website, created_by
                    ) VALUES (
                        :role_id, :member_id, :firstname, :lastname, :username, :email,
                        :phone, :password, :photo, :bio, :email_verified, :status,
                        :is_adepr_member, :can_manage_website, :created_by
                    )";

            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':role_id' => $data['role_id'] ?? 5,
                ':member_id' => $data['member_id'] ?? null,
                ':firstname' => $data['firstname'],
                ':lastname' => $data['lastname'],
                ':username' => $data['username'] ?? null,
                ':email' => $data['email'],
                ':phone' => $data['phone'] ?? null,
                ':password' => $data['password'] ?? null,
                ':photo' => $data['photo'] ?? null,
                ':bio' => $data['bio'] ?? null,
                ':email_verified' => $data['email_verified'] ?? 0,
                ':status' => $data['status'] ?? 'pending',
                ':is_adepr_member' => $data['is_adepr_member'] ?? 0,
                ':can_manage_website' => $data['can_manage_website'] ?? 0,
                ':created_by' => $data['created_by'] ?? null
            ];

            $stmt->execute($params);
            $userId = $this->db->lastInsertId();

            $this->db->commit();
            return $userId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("UserModel::createUser - Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update user
     */
    public function updateUser($id, $data)
    {
        try {
            $this->db->beginTransaction();

            $updateFields = [];
            $params = [':id' => $id];

            $allowedFields = [
                'role_id', 'member_id', 'firstname', 'lastname', 'username',
                'email', 'phone', 'photo', 'bio', 'email_verified',
                'status', 'is_adepr_member', 'can_manage_website'
            ];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateFields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            // Handle password separately
            if (!empty($data['password'])) {
                $updateFields[] = "password = :password";
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (empty($updateFields)) {
                return false;
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("UserModel::updateUser - Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        try {
            // Check if user is super admin
            $user = $this->getUserById($id);
            if ($user && $user['is_super_admin']) {
                throw new Exception("Cannot delete super admin user");
            }

            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);

        } catch (Exception $e) {
            error_log("UserModel::deleteUser - Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update user status
     */
    public function updateStatus($id, $status)
    {
        try {
            // Check if user is super admin
            if ($status !== 'active') {
                $user = $this->getUserById($id);
                if ($user && $user['is_super_admin']) {
                    throw new Exception("Cannot deactivate super admin user");
                }
            }

            $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);

        } catch (Exception $e) {
            error_log("UserModel::updateStatus - Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStats()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_users,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_users,
                        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_users,
                        SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_users,
                        SUM(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as active_last_7_days,
                        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_users_30_days
                    FROM {$this->table}";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("UserModel::getUserStats - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all roles for dropdown
     */
    public function getAllRoles()
    {
        try {
            $sql = "SELECT id, name, description, is_super_admin 
                    FROM {$this->rolesTable} 
                    ORDER BY 
                        CASE 
                            WHEN is_super_admin = 1 THEN 0 
                            ELSE 1 
                        END,
                        name ASC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("UserModel::getAllRoles - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get members for dropdown (to link user accounts)
     */
    public function getAvailableMembers()
    {
        try {
            $sql = "SELECT m.id, CONCAT(m.firstname, ' ', m.lastname) as full_name, 
                           m.membership_number, m.email
                    FROM members m
                    LEFT JOIN users u ON m.id = u.member_id
                    WHERE u.id IS NULL
                    ORDER BY m.firstname, m.lastname";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("UserModel::getAvailableMembers - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Store reset token (OTP) - from your existing code
     */
    public function storeResetToken($userId, $otp)
    {
        try {
            $query = "UPDATE users 
                     SET reset_token = :otp, 
                         reset_expiry = DATE_ADD(NOW(), INTERVAL 5 MINUTE) 
                     WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':otp', $otp, PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("User Model Error in storeResetToken: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify reset token (OTP) - from your existing code
     */
    public function verifyResetToken($email, $otp)
    {
        try {
            $query = "SELECT id FROM users 
                     WHERE email = :email AND reset_token = :otp 
                     AND reset_expiry > NOW()";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':otp', $otp, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : false;

        } catch (PDOException $e) {
            error_log("User Model Error in verifyResetToken: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear reset token - from your existing code
     */
    public function clearResetToken($userId)
    {
        try {
            $query = "UPDATE users SET reset_token = NULL, reset_expiry = NULL WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("User Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last login - from your existing code
     */
    public function updateLastLogin($userId)
    {
        try {
            $query = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("User Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Change password - from your existing code
     */
    public function changePassword($id, $hashedPassword)
    {
        try {
            $query = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("User Model Error: " . $e->getMessage());
            return false;
        }
    }
}

?>