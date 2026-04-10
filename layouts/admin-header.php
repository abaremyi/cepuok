<!DOCTYPE html>
<html lang="en">
  <?php
/**
 * Admin Header Layout CEPUOK
 * File: layouts/admin-header.php
 */
?>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Admin Dashboard' ?> | CEP UoK</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>">

  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

  <!-- CSS Implementing Plugins -->
  <link rel="stylesheet" href="<?= admin_css_url('vendor.min.css') ?>">

  <!-- CSS Front Template -->
  <link rel="stylesheet" href="<?= admin_css_url('theme-dark.min.css') ?>" data-hs-appearance="dark" as="style">
  <link rel="stylesheet" href="<?= admin_css_url('theme.min.css') ?>" data-hs-appearance="default" as="style">

  
 
</head>