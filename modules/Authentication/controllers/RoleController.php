<?php
/**
 * Role Controller CEPUOK
 * Handles role management for admin panel
 * File: modules/Authentication/controllers/RoleController.php
 */

require_once __DIR__ . '/../models/RoleModel.php';

class RoleController {
    private $roleModel;

    public function __construct() {
        $this->roleModel = new RoleModel();
    }

    /**
     * Get all roles
     */
    public function index() {
        try {
            return $this->roleModel->getAllRoles();
        } catch (Exception $e) {
            error_log("RoleController::index - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single role
     */
    public function show($id) {
        try {
            return $this->roleModel->getRoleById($id);
        } catch (Exception $e) {
            error_log("RoleController::show - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create role
     */
    public function store($data) {
        try {
            // Validate required fields
            if (empty($data['name'])) {
                throw new Exception("Role name is required");
            }

            return [
                'success' => true,
                'message' => 'Role created successfully',
                'role_id' => $this->roleModel->createRole($data)
            ];

        } catch (Exception $e) {
            error_log("RoleController::store - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update role
     */
    public function update($id, $data) {
        try {
            // Validate required fields
            if (empty($data['name'])) {
                throw new Exception("Role name is required");
            }

            $result = $this->roleModel->updateRole($id, $data);

            return [
                'success' => $result,
                'message' => $result ? 'Role updated successfully' : 'No changes made'
            ];

        } catch (Exception $e) {
            error_log("RoleController::update - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete role
     */
    public function destroy($id) {
        try {
            $result = $this->roleModel->deleteRole($id);

            return [
                'success' => $result,
                'message' => $result ? 'Role deleted successfully' : 'Failed to delete role'
            ];

        } catch (Exception $e) {
            error_log("RoleController::destroy - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all permissions grouped by module
     */
    public function getPermissions() {
        try {
            return $this->roleModel->getAllPermissions();
        } catch (Exception $e) {
            error_log("RoleController::getPermissions - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get role permissions
     */
    public function getRolePermissions($roleId) {
        try {
            return $this->roleModel->getRolePermissions($roleId);
        } catch (Exception $e) {
            error_log("RoleController::getRolePermissions - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions($roleId, $permissionIds) {
        try {
            if (!is_array($permissionIds)) {
                $permissionIds = [];
            }

            $result = $this->roleModel->updateRolePermissions($roleId, $permissionIds);

            return [
                'success' => $result,
                'message' => $result ? 'Permissions updated successfully' : 'Failed to update permissions'
            ];

        } catch (Exception $e) {
            error_log("RoleController::updateRolePermissions - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get role statistics
     */
    public function getStats() {
        try {
            return $this->roleModel->getRoleStats();
        } catch (Exception $e) {
            error_log("RoleController::getStats - Error: " . $e->getMessage());
            return [];
        }
    }
}

?>