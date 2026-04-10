<?php
require_once 'config/paths.php';
require_once 'config/database.php';

$db = Database::getConnection();

// Find all users with matching email in members table where member_id is NULL
$sql = "SELECT u.id as user_id, u.email, m.id as member_id 
        FROM users u
        JOIN members m ON u.email = m.email
        WHERE u.member_id IS NULL OR u.member_id != m.id";

$stmt = $db->query($sql);
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($matches) . " users to link\n";

foreach ($matches as $match) {
    // Update users table
    $updateUser = "UPDATE users SET member_id = :member_id WHERE id = :user_id";
    $stmt = $db->prepare($updateUser);
    $stmt->execute([
        ':member_id' => $match['member_id'],
        ':user_id' => $match['user_id']
    ]);
    
    // Update members table
    $updateMember = "UPDATE members SET user_id = :user_id WHERE id = :member_id";
    $stmt = $db->prepare($updateMember);
    $stmt->execute([
        ':user_id' => $match['user_id'],
        ':member_id' => $match['member_id']
    ]);
    
    echo "Linked user ID {$match['user_id']} with member ID {$match['member_id']}\n";
}

echo "Done!\n";