<?php
/**
 * Role Model
 * File: modules/Authentication/models/RoleModel.php
 */

require_once ROOT_PATH . '/config/database.php';

class RoleModel {
    private $db;
    private $rolesTable = 'roles';
    private $permissionsTable = 'permissions';
    private $rolePermissionsTable = 'role_permissions';

    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            $this->db = Database::getInstance();
        }
    }

    /**
     * Get all roles with user and permission counts
     */
    public function getAllRoles() {
        try {
            $sql = "SELECT r.*, 
                           COUNT(DISTINCT rp.permission_id) as permission_count,
                           COUNT(DISTINCT u.id) as user_count
                    FROM {$this->rolesTable} r
                    LEFT JOIN {$this->rolePermissionsTable} rp ON r.id = rp.role_id
                    LEFT JOIN users u ON r.id = u.role_id
                    GROUP BY r.id
                    ORDER BY 
                        CASE 
                            WHEN r.is_super_admin = 1 THEN 0 
                            ELSE 1 
                        END,
                        r.name ASC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("RoleModel::getAllRoles - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get role by ID
     */
    public function getRoleById($id) {
        try {
            $sql = "SELECT * FROM {$this->rolesTable} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("RoleModel::getRoleById - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new role
     */
    public function createRole($data) {
        try {
            // Check if role name exists
            if ($this->roleNameExists($data['name'])) {
                throw new Exception("Role name already exists");
            }

            $sql = "INSERT INTO {$this->rolesTable} (name, description, is_super_admin) 
                    VALUES (:name, :description, :is_super_admin)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':is_super_admin' => $data['is_super_admin'] ?? 0
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("RoleModel::createRole - Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update role
     */
    public function updateRole($id, $data) {
        try {
            // Check if role is super admin
            $role = $this->getRoleById($id);
            if ($role && $role['is_super_admin']) {
                throw new Exception("Cannot modify super admin role");
            }

            // Check if role name exists (excluding current)
            if ($this->roleNameExists($data['name'], $id)) {
                throw new Exception("Role name already exists");
            }

            $sql = "UPDATE {$this->rolesTable} 
                    SET name = :name, description = :description 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("RoleModel::updateRole - Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete role
     */
    public function deleteRole($id) {
        try {
            // Check if role is super admin
            $role = $this->getRoleById($id);
            if ($role && $role['is_super_admin']) {
                throw new Exception("Cannot delete super admin role");
            }

            // Check if role has users
            $sql = "SELECT COUNT(*) as count FROM users WHERE role_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                throw new Exception("Cannot delete role with assigned users");
            }

            // Delete role permissions first
            $sql = "DELETE FROM {$this->rolePermissionsTable} WHERE role_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            // Delete role
            $sql = "DELETE FROM {$this->rolesTable} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            error_log("RoleModel::deleteRole - Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if role name exists
     */
    public function roleNameExists($name, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->rolesTable} WHERE name = :name";
            $params = [':name' => $name];
            
            if ($excludeId) {
                $sql .= " AND id != :id";
                $params[':id'] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log("RoleModel::roleNameExists - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all permissions grouped by module
     */
    public function getAllPermissions() {
        try {
            $sql = "SELECT * FROM {$this->permissionsTable} ORDER BY module ASC, action ASC";
            $stmt = $this->db->query($sql);
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
        } catch (Exception $e) {
            error_log("RoleModel::getAllPermissions - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get role permissions
     */
    public function getRolePermissions($roleId) {
        try {
            $sql = "SELECT permission_id FROM {$this->rolePermissionsTable} WHERE role_id = :role_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':role_id' => $roleId]);
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_column($result, 'permission_id');
        } catch (Exception $e) {
            error_log("RoleModel::getRolePermissions - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions($roleId, $permissionIds) {
        try {
            $this->db->beginTransaction();

            // Delete existing permissions
            $sql = "DELETE FROM {$this->rolePermissionsTable} WHERE role_id = :role_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':role_id' => $roleId]);

            // Insert new permissions
            if (!empty($permissionIds)) {
                $sql = "INSERT INTO {$this->rolePermissionsTable} (role_id, permission_id) 
                        VALUES (:role_id, :permission_id)";
                $stmt = $this->db->prepare($sql);
                
                foreach ($permissionIds as $permissionId) {
                    $stmt->execute([
                        ':role_id' => $roleId,
                        ':permission_id' => $permissionId
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("RoleModel::updateRolePermissions - Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get role statistics
     */
    public function getRoleStats() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_roles,
                        SUM(CASE WHEN is_super_admin = 1 THEN 1 ELSE 0 END) as super_admin_roles,
                        SUM(CASE WHEN is_super_admin = 0 THEN 1 ELSE 0 END) as regular_roles,
                        (SELECT COUNT(*) FROM role_permissions) as total_assignments,
                        (SELECT COUNT(*) FROM permissions) as total_permissions
                    FROM {$this->rolesTable}";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("RoleModel::getRoleStats - Error: " . $e->getMessage());
            return [];
        }
    }
}

?>