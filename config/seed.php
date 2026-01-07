<?php
require_once __DIR__ . '/database.php';

class DatabaseSeeder {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    public function run() {
        $this->seedRoles();
        $this->seedPermissions();
        $this->seedSuperAdmin();
        $this->assignPermissionsToSuperAdmin();
        
        echo "Database seeded successfully!\n";
    }
    
    private function seedRoles() {
        $roles = [
            ['name' => 'Super Admin', 'description' => 'Has full system access', 'is_super_admin' => 1],
            ['name' => 'Administrator', 'description' => 'School administration staff', 'is_super_admin' => 0],
            ['name' => 'Teacher', 'description' => 'Teaching staff', 'is_super_admin' => 0],
            ['name' => 'Parent', 'description' => 'Student parents', 'is_super_admin' => 0],
            ['name' => 'Student', 'description' => 'School students', 'is_super_admin' => 0]
        ];
        
        foreach ($roles as $role) {
            $stmt = $this->db->prepare("
                INSERT INTO roles (name, description, is_super_admin, created_at, updated_at) 
                VALUES (:name, :description, :is_super_admin, NOW(), NOW())
                ON DUPLICATE KEY UPDATE description = :description, updated_at = NOW()
            ");
            $stmt->execute($role);
        }
        
        echo "Roles seeded.\n";
    }
    
    private function seedPermissions() {
        $permissions = [
            // Dashboard
            ['name' => 'view_dashboard', 'description' => 'View dashboard', 'module' => 'dashboard', 'action' => 'view'],
            
            // User Management
            ['name' => 'view_users', 'description' => 'View users list', 'module' => 'users', 'action' => 'view'],
            ['name' => 'create_users', 'description' => 'Create new users', 'module' => 'users', 'action' => 'create'],
            ['name' => 'edit_users', 'description' => 'Edit users', 'module' => 'users', 'action' => 'edit'],
            ['name' => 'delete_users', 'description' => 'Delete users', 'module' => 'users', 'action' => 'delete'],
            
            // Role Management
            ['name' => 'view_roles', 'description' => 'View roles', 'module' => 'roles', 'action' => 'view'],
            ['name' => 'create_roles', 'description' => 'Create roles', 'module' => 'roles', 'action' => 'create'],
            ['name' => 'edit_roles', 'description' => 'Edit roles', 'module' => 'roles', 'action' => 'edit'],
            ['name' => 'delete_roles', 'description' => 'Delete roles', 'module' => 'roles', 'action' => 'delete'],
            ['name' => 'assign_permissions', 'description' => 'Assign permissions to roles', 'module' => 'roles', 'action' => 'assign_permissions'],
            
            // Student Management
            ['name' => 'view_students', 'description' => 'View students', 'module' => 'students', 'action' => 'view'],
            ['name' => 'create_students', 'description' => 'Add students', 'module' => 'students', 'action' => 'create'],
            ['name' => 'edit_students', 'description' => 'Edit student info', 'module' => 'students', 'action' => 'edit'],
            ['name' => 'delete_students', 'description' => 'Remove students', 'module' => 'students', 'action' => 'delete'],
            
            // Staff Management
            ['name' => 'view_staff', 'description' => 'View staff', 'module' => 'staff', 'action' => 'view'],
            ['name' => 'create_staff', 'description' => 'Add staff', 'module' => 'staff', 'action' => 'create'],
            ['name' => 'edit_staff', 'description' => 'Edit staff info', 'module' => 'staff', 'action' => 'edit'],
            ['name' => 'delete_staff', 'description' => 'Remove staff', 'module' => 'staff', 'action' => 'delete'],
            
            // Academic Management
            ['name' => 'manage_classes', 'description' => 'Manage classes', 'module' => 'academics', 'action' => 'manage_classes'],
            ['name' => 'manage_subjects', 'description' => 'Manage subjects', 'module' => 'academics', 'action' => 'manage_subjects'],
            ['name' => 'manage_timetable', 'description' => 'Manage timetable', 'module' => 'academics', 'action' => 'manage_timetable'],
            
            // Financial Management
            ['name' => 'view_finances', 'description' => 'View finances', 'module' => 'finance', 'action' => 'view'],
            ['name' => 'manage_fees', 'description' => 'Manage fee structure', 'module' => 'finance', 'action' => 'manage_fees'],
            ['name' => 'process_payments', 'description' => 'Process payments', 'module' => 'finance', 'action' => 'process_payments'],
            
            // Website Content Management
            ['name' => 'manage_news', 'description' => 'Manage news & events', 'module' => 'website', 'action' => 'manage_news'],
            ['name' => 'manage_gallery', 'description' => 'Manage gallery', 'module' => 'website', 'action' => 'manage_gallery'],
            ['name' => 'manage_testimonials', 'description' => 'Manage testimonials', 'module' => 'website', 'action' => 'manage_testimonials'],
            ['name' => 'manage_sliders', 'description' => 'Manage hero sliders', 'module' => 'website', 'action' => 'manage_sliders']
        ];
        
        foreach ($permissions as $permission) {
            $stmt = $this->db->prepare("
                INSERT INTO permissions (name, description, module, action, created_at) 
                VALUES (:name, :description, :module, :action, NOW())
                ON DUPLICATE KEY UPDATE description = :description
            ");
            $stmt->execute($permission);
        }
        
        echo "Permissions seeded.\n";
    }
    
    private function seedSuperAdmin() {
        $password = password_hash('MountCarmel@2025', PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (firstname, lastname, email, phone, username, password, role_id, status, created_at, updated_at) 
            VALUES ('BAHATI', 'Gerchom', 'info@mountcarmel.ac.rw', '0787254817', 'superadmin', :password, 1, 'active', NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                firstname = VALUES(firstname),
                lastname = VALUES(lastname),
                phone = VALUES(phone),
                password = VALUES(password),
                updated_at = NOW()
        ");
        
        $stmt->execute(['password' => $password]);
        
        echo "Super admin user created.\n";
        echo "Default credentials:\n";
        echo "Email: info@mountcarmel.ac.rw\n";
        echo "Phone: 0787254817\n";
        echo "Username: superadmin\n";
        echo "Password: MountCarmel@2025\n";
        echo "Please change the password after first login!\n";
    }
    
    private function assignPermissionsToSuperAdmin() {
        // Get all permission IDs
        $stmt = $this->db->query("SELECT id FROM permissions");
        $permissionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Assign all permissions to super admin role (role_id = 1)
        foreach ($permissionIds as $permissionId) {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at) 
                VALUES (1, :permission_id, NOW())
            ");
            $stmt->execute(['permission_id' => $permissionId]);
        }
        
        echo "Permissions assigned to Super Admin role.\n";
    }
}

// Run seeder if executed directly
if (php_sapi_name() === 'cli') {
    $seeder = new DatabaseSeeder();
    $seeder->run();
}
?>