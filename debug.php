<?php
// debug_query.php
require_once 'config/database.php';
require_once 'modules/Authentication/models/UserModel.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debugging SQL Query</h2>";

try {
    $db = Database::getInstance();
    $userModel = new UserModel($db);
    
    // Test 1: Direct SQL test of the query
    echo "<h3>Test 1: Running the exact SQL query</h3>";
    
    $identifier = 'info@mountcarmel.ac.rw';
    $query = "SELECT u.*, r.name as role_name, r.is_super_admin,
                     GROUP_CONCAT(CONCAT(p.module, '.', p.action)) as permissions
              FROM users u
              LEFT JOIN roles r ON u.role_id = r.id
              LEFT JOIN role_permissions rp ON r.id = rp.role_id
              LEFT JOIN permissions p ON rp.permission_id = p.id
              WHERE u.email = :identifier OR u.phone = :identifier OR u.username = :identifier
              GROUP BY u.id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Query returned results:</p>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Query returned NO results</p>";
        
        // Let's test step by step
        echo "<h4>Step-by-step debugging:</h4>";
        
        // Check if user exists
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Direct user query: " . ($user ? "✓ Found user" : "✗ User not found") . "</p>";
        if ($user) {
            echo "<pre>";
            print_r($user);
            echo "</pre>";
        }
        
        // Check roles table
        $stmt = $db->query("SELECT * FROM roles WHERE id = " . ($user['role_id'] ?? 0));
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Role query: " . ($role ? "✓ Found role" : "✗ Role not found") . "</p>";
        
        // Check role_permissions
        $stmt = $db->query("SELECT COUNT(*) FROM role_permissions WHERE role_id = " . ($user['role_id'] ?? 0));
        $permCount = $stmt->fetchColumn();
        echo "<p>Role permissions count: $permCount</p>";
    }
    
    // Test 2: Simplified query
    echo "<h3>Test 2: Simplified query (without GROUP_CONCAT)</h3>";
    
    $simpleQuery = "SELECT u.*, r.name as role_name, r.is_super_admin
                    FROM users u
                    LEFT JOIN roles r ON u.role_id = r.id
                    WHERE u.email = ? OR u.phone = ?
                    LIMIT 1";
    
    $stmt = $db->prepare($simpleQuery);
    $stmt->execute([$identifier, $identifier]);
    $simpleResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($simpleResult) {
        echo "<p style='color: green;'>✓ Simplified query works!</p>";
        echo "<pre>";
        print_r($simpleResult);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Even simplified query fails</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}  