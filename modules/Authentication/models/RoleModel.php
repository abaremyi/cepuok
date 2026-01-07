<?php
/**
 * Role Model
 * File: modules/Authentication/models/RoleModel.php
 * Handles all database operations for roles and permissions
 */

class RoleModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get all roles
     * @return array Array of roles
     */
    public function getAllRoles()
    {
        try {
            $query = "SELECT * FROM roles ORDER BY id";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Role Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get role by ID
     * @param int $id Role ID
     * @return array|null Role data or null
     */
    public function getRoleById($id)
    {
        try {
            $query = "SELECT * FROM roles WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Role Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new role
     * @param array $data Role data
     * @return int|bool Role ID or false
     */
    public function createRole($data)
    {
        try {
            $query = "INSERT INTO roles (name, description, is_super_admin) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['is_super_admin'] ?? 0
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Role Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update role
     * @param int $id Role ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function updateRole($id, $data)
    {
        try {
            $query = "UPDATE roles SET name = ?, description = ?, is_super_admin = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['is_super_admin'] ?? 0,
                $id
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Role Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete role
     * @param int $id Role ID
     * @return bool Success status
     */
    public function deleteRole($id)
    {
        try {
            // Check if role has users
            $checkQuery = "SELECT COUNT(*) FROM users WHERE role_id = ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute([$id]);
            
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("Cannot delete role that has users assigned");
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Delete role permissions first
            $deletePermissionsQuery = "DELETE FROM role_permissions WHERE role_id = ?";
            $deletePermissionsStmt = $this->db->prepare($deletePermissionsQuery);
            $deletePermissionsStmt->execute([$id]);
            
            // Delete role (cannot delete super admin role)
            $deleteQuery = "DELETE FROM roles WHERE id = ? AND is_super_admin = 0";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $result = $deleteStmt->execute([$id]);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Role Model Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get role permissions
     * @param int $roleId Role ID
     * @return array Array of permission IDs
     */
    public function getRolePermissions($roleId)
    {
        try {
            $query = "SELECT permission_id FROM role_permissions WHERE role_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$roleId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            error_log("Role Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update role permissions
     * @param int $roleId Role ID
     * @param array $permissionIds Permission IDs
     * @return bool Success status
     */
    public function updateRolePermissions($roleId, $permissionIds)
    {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Delete existing permissions
            $deleteQuery = "DELETE FROM role_permissions WHERE role_id = ?";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->execute([$roleId]);
            
            // Insert new permissions
            if (!empty($permissionIds)) {
                $insertQuery = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                $insertStmt = $this->db->prepare($insertQuery);
                
                foreach ($permissionIds as $permissionId) {
                    $insertStmt->execute([$roleId, $permissionId]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Role Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all permissions grouped by module
     * @return array Array of permissions grouped by module
     */
    public function getAllPermissionsGrouped()
    {
        try {
            $query = "SELECT * FROM permissions ORDER BY module, id";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group by module
            $grouped = [];
            foreach ($permissions as $permission) {
                $module = $permission['module'];
                if (!isset($grouped[$module])) {
                    $grouped[$module] = [];
                }
                $grouped[$module][] = $permission;
            }
            
            return $grouped;
        } catch (PDOException $e) {
            error_log("Role Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all permissions
     * @return array Array of all permissions
     */
    public function getAllPermissions()
    {
        try {
            $query = "SELECT * FROM permissions ORDER BY module, action";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Role Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user count by role
     * @param int $roleId Role ID
     * @return int User count
     */
    public function getUserCountByRole($roleId)
    {
        try {
            $query = "SELECT COUNT(*) FROM users WHERE role_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$roleId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Role Model Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if role name exists
     * @param string $name Role name
     * @param int $excludeId Role ID to exclude (for updates)
     * @return bool True if exists
     */
    public function roleNameExists($name, $excludeId = 0)
    {
        try {
            if ($excludeId > 0) {
                $query = "SELECT COUNT(*) FROM roles WHERE name = ? AND id != ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$name, $excludeId]);
            } else {
                $query = "SELECT COUNT(*) FROM roles WHERE name = ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$name]);
            }
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Role Model Error: " . $e->getMessage());
            return false;
        }
    }
}
?>