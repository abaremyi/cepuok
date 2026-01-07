<?php
/**
 * User Model
 * File: modules/Authentication/models/UserModel.php
 * Handles all database operations for users
 */

class UserModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Check if email/phone exists
     * @param string $email User email
     * @param string $phone User phone
     * @return bool True if exists
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
     * Get user by email or phone for login
     * @param string $identifier Email, phone, or username
     * @return array|null User data or null
     */
    public function getUserByEmailOrPhone($identifier)
    {
        try {
            $query = "SELECT u.*, r.name as role_name, r.is_super_admin
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.email = :email_id OR u.phone = :phone_id OR u.username = :username_id 
                    LIMIT 1";

            $stmt = $this->db->prepare($query);

            // Bind the identifier value to all three unique placeholders
            $stmt->execute([
                ':email_id' => $identifier,
                ':phone_id' => $identifier,
                ':username_id' => $identifier,
            ]);
            
            $user = $stmt->fetch();
            
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

    private function getUserPermissions($roleId)
    {
        try {
            $query = "SELECT CONCAT(p.module, '.', p.action) as permission
                      FROM role_permissions rp
                      JOIN permissions p ON rp.permission_id = p.id
                      WHERE rp.role_id = :role_id";

            $stmt = $this->db->prepare($query);
            $stmt->execute(array(':role_id' => $roleId)); 

            // Use fetchAll to get ALL permission strings for this role
            $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 
            
            return implode(',', $permissions);

        } catch (PDOException $e) {
            error_log("User Model Error in getUserPermissions: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Create new user
     * @param array $data User data
     * @return int|bool User ID or false
     */
    public function createUser($data)
    {
        try {
            $query = "INSERT INTO users (firstname, lastname, email, phone, username, password, role_id, status, created_by) 
                      VALUES (:firstname, :lastname, :email, :phone, :username, :password, :role_id, :status, :created_by)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':firstname', $data['firstname'], PDO::PARAM_STR);
            $stmt->bindParam(':lastname', $data['lastname'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
            $stmt->bindParam(':username', $data['username'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $data['password'], PDO::PARAM_STR);
            $stmt->bindParam(':role_id', $data['role_id'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
            $stmt->bindParam(':created_by', $data['created_by'], PDO::PARAM_INT);

            return $stmt->execute() ? $this->db->lastInsertId() : false;

        } catch (PDOException $e) {
            error_log("User Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user last login
     * @param int $userId User ID
     * @return bool Success status
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
     * Get user by ID
     * @param int $id User ID
     * @return array|null User data or null
     */
    public function getUserById($id)
    {
        try {
            $query = "SELECT u.*, r.name as role_name, r.is_super_admin
                    FROM users u
                    LEFT JOIN roles r ON u.role_id = r.id
                    WHERE u.id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("User Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user
     * @param int $id User ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function updateUser($id, $data)
    {
        try {
            $fields = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
            }

            $query = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("User Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Change password
     * @param int $id User ID
     * @param string $hashedPassword Hashed password
     * @return bool Success status
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

    /**
     * Store reset token (OTP)
     * @param int $userId User ID
     * @param string $otp OTP code
     * @return bool Success status
     */
    public function storeResetToken($userId, $otp)
    {
        try {
            // Use MySQL DATE_ADD to ensure correct timezone handling
            $query = "UPDATE users 
                     SET reset_token = :otp, 
                         reset_expiry = DATE_ADD(NOW(), INTERVAL 5 MINUTE) 
                     WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':otp', $otp, PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            // Debug: Check if the update was successful
            if ($result) {
                error_log("OTP stored successfully for user ID: {$userId}, OTP: {$otp}");
                
                // Verify the stored data - Fixed: use backticks for reserved word and alias
                $verifyQuery = "SELECT reset_token, reset_expiry, NOW() as `server_time` FROM users WHERE id = :id";
                $verifyStmt = $this->db->prepare($verifyQuery);
                $verifyStmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $verifyStmt->execute();
                $stored = $verifyStmt->fetch(PDO::FETCH_ASSOC);
                error_log("Verified stored OTP: " . print_r($stored, true));
            } else {
                error_log("Failed to store OTP for user ID: {$userId}");
            }
            
            return $result;

        } catch (PDOException $e) {
            error_log("User Model Error in storeResetToken: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify reset token (OTP)
     * @param string $email User email
     * @param string $otp OTP code
     * @return int|bool User ID or false
     */
    public function verifyResetToken($email, $otp)
    {
        try {
            // First, let's check what's in the database
            $debugQuery = "SELECT id, email, reset_token, reset_expiry, NOW() as current_time 
                          FROM users WHERE email = :email";
            $debugStmt = $this->db->prepare($debugQuery);
            $debugStmt->bindParam(':email', $email, PDO::PARAM_STR);
            $debugStmt->execute();
            $debugData = $debugStmt->fetch(PDO::FETCH_ASSOC);
            error_log("Debug - User data for email {$email}: " . print_r($debugData, true));
            error_log("Debug - Comparing OTP: Input='{$otp}' vs Stored='{$debugData['reset_token']}'");
            
            // Trim both values to remove any whitespace
            $otp = trim($otp);
            
            $query = "SELECT id, reset_token, reset_expiry FROM users 
                     WHERE email = :email AND reset_token = :otp 
                     AND reset_expiry > NOW()";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':otp', $otp, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("OTP verification successful for email: {$email}");
                return $result['id'];
            } else {
                error_log("OTP verification failed for email: {$email}, OTP: {$otp}");
                
                // Additional checks
                $checkQuery = "SELECT id, reset_token, reset_expiry, 
                              CASE WHEN reset_expiry > NOW() THEN 'valid' ELSE 'expired' END as status
                              FROM users WHERE email = :email";
                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
                $checkStmt->execute();
                $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
                error_log("Additional check: " . print_r($checkResult, true));
                
                return false;
            }

        } catch (PDOException $e) {
            error_log("User Model Error in verifyResetToken: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear reset token after use
     * @param int $userId User ID
     * @return bool Success status
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
     * Get all users with pagination (admin only)
     * @param int $limit Number of users
     * @param int $offset Starting position
     * @param string $search Search term
     * @return array Array of users
     */
    public function getAllUsers($limit = 20, $offset = 0, $search = '')
    {
        try {
            $query = "SELECT u.*, r.name as role_name, 
                             CONCAT(creator.firstname, ' ', creator.lastname) as created_by_name
                      FROM users u
                      LEFT JOIN roles r ON u.role_id = r.id
                      LEFT JOIN users creator ON u.created_by = creator.id
                      WHERE u.firstname LIKE :search OR u.lastname LIKE :search OR u.email LIKE :search
                      ORDER BY u.created_at DESC
                      LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($query);
            $searchTerm = "%$search%";
            $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("User Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete user (admin only)
     * @param int $id User ID
     * @return bool Success status
     */
    public function deleteUser($id)
    {
        try {
            $query = "DELETE FROM users WHERE id = :id AND role_id != 1"; // Prevent deleting super admin
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("User Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count total users
     * @param string $search Search term
     * @return int Total count
     */
    public function countUsers($search = '')
    {
        try {
            $query = "SELECT COUNT(*) FROM users 
                      WHERE firstname LIKE :search OR lastname LIKE :search OR email LIKE :search";

            $stmt = $this->db->prepare($query);
            $searchTerm = "%$search%";
            $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchColumn();

        } catch (PDOException $e) {
            error_log("User Model Error: " . $e->getMessage());
            return 0;
        }
    }
}
?>