<?php
require_once '../../../helpers/JWTHandler.php';
require_once '../../../config/database.php';

// Authentication check
if (!isset($_COOKIE['jwtToken'])) {
    header('Location: ../../Authentication/views/login.php');
    exit;
}

$jwtToken = $_COOKIE['jwtToken'];
$jwtHandler = new JWTHandler();
$decodedToken = $jwtHandler->validateToken($jwtToken);

if ($decodedToken === false) {
    header('Location: ../../Authentication/views/login.php');
    exit;
}

// Fetch all subscribers
$db = Database::getInstance();
$subscribers = $db->query("SELECT email, is_active, subscribed_at, unsubscribed_at FROM newsletter_subscribers ORDER BY subscribed_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=subscribers_' . date('Y-m-d') . '.csv');

// Create output file pointer
$output = fopen('php://output', 'w');

// Output column headings
fputcsv($output, ['Email', 'Status', 'Subscribed Date', 'Unsubscribed Date']);

// Output data
foreach ($subscribers as $subscriber) {
    $status = $subscriber['is_active'] ? 'Active' : 'Inactive';
    $subscribedDate = date('Y-m-d H:i:s', strtotime($subscriber['subscribed_at']));
    $unsubscribedDate = $subscriber['unsubscribed_at'] ? date('Y-m-d H:i:s', strtotime($subscriber['unsubscribed_at'])) : '';
    
    fputcsv($output, [
        $subscriber['email'],
        $status,
        $subscribedDate,
        $unsubscribedDate
    ]);
}

fclose($output);
exit;