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
        // Super admin always has all permissions
        global $currentUser;
        if (isset($currentUser) && !empty($currentUser->is_super_admin)) {
            return true;
        }
        
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
        // Super admin always has all permissions
        global $currentUser;
        if (isset($currentUser) && !empty($currentUser->is_super_admin)) {
            return true;
        }
        
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
        // Super admin always has all permissions
        global $currentUser;
        if (isset($currentUser) && !empty($currentUser->is_super_admin)) {
            return true;
        }
        
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

if (!function_exists('formatPermissions')) {
    /**
     * Format permissions array for display
     * @param array $permissions Permissions array
     * @return array Formatted permissions grouped by module
     */
    function formatPermissions($permissions) {
        $formatted = [];
        foreach ($permissions as $perm) {
            $parts = explode('.', $perm);
            $module = $parts[0] ?? 'general';
            $action = $parts[1] ?? $perm;
            
            if (!isset($formatted[$module])) {
                $formatted[$module] = [];
            }
            $formatted[$module][] = $action;
        }
        return $formatted;
    }
}

