<?php
/**
 * Cron Job: Suspend expired budget drafts
 * File: cron/suspend-expired-budgets.php
 * 
 * Run this daily via cron:
 * 0 0 * * * php cron/suspend-expired-budgets.php
 */

require_once dirname(__DIR__) . '/config/paths.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/modules/Finance/models/FinanceModel.php';

try {
    $db = Database::getInstance();
    
    // Suspend budgets that have been in draft for more than 14 days
    $stmt = $db->prepare("
        UPDATE budget_quarters 
        SET status = 'suspended' 
        WHERE status = 'draft' 
        AND draft_created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)
    ");
    $stmt->execute();
    
    $count = $stmt->rowCount();
    
    // Log the result
    error_log("[CRON] Suspended {$count} expired budget drafts");
    
} catch (Exception $e) {
    error_log("[CRON ERROR] " . $e->getMessage());
}
?>