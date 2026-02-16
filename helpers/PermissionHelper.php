<?php
/**
 * Permission Helper Functions
 * File: helpers/PermissionHelper.php
 */

if (!function_exists('hasPermission')) {
    /**
     * Check if user has a specific permission
     * @param array $permissions User permissions array
     * @param string $permission Permission to check (e.g., 'users.view')
     * @return bool
     */
    function hasPermission($permissions, $permission) {
        if (empty($permissions) || !is_array($permissions)) {
            return false;
        }
        return in_array($permission, $permissions);
    }
}

if (!function_exists('hasAnyPermission')) {
    /**
     * Check if user has any of the given permissions
     * @param array $permissions User permissions array
     * @param array $permissionsToCheck Array of permissions to check
     * @return bool
     */
    function hasAnyPermission($permissions, $permissionsToCheck) {
        if (empty($permissions) || !is_array($permissions) || empty($permissionsToCheck)) {
            return false;
        }
        
        foreach ($permissionsToCheck as $perm) {
            if (in_array($perm, $permissions)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('hasAllPermissions')) {
    /**
     * Check if user has all of the given permissions
     * @param array $permissions User permissions array
     * @param array $permissionsToCheck Array of permissions to check
     * @return bool
     */
    function hasAllPermissions($permissions, $permissionsToCheck) {
        if (empty($permissions) || !is_array($permissions) || empty($permissionsToCheck)) {
            return false;
        }
        
        foreach ($permissionsToCheck as $perm) {
            if (!in_array($perm, $permissions)) {
                return false;
            }
        }
        return true;
    }
}