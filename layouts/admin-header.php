<?php
/**
 * Admin Header Layout
 * File: layouts/admin-header.php
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $pageTitle ?? 'Admin Dashboard' ?> | CEP UoK</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= IMG_URL ?>/favicon.ico">
    
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/dashboard-assets/css/vendor.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/dashboard-assets/css/theme.min.css">
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/dashboard-assets/css/admin.css">
</head>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset"></body>