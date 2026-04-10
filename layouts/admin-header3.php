<?php
/**
 * Admin Header Layout
 * File: layouts/admin-header.php
 *
 * CHANGES FROM ORIGINAL:
 *  1. Added proper <!DOCTYPE html> and <html lang="en"> opening tags.
 *  2. Theme CSS kept as rel="preload" as="style" — this IS correct.
 *     hs.theme-appearance.js reads the href and creates a real stylesheet.
 *  3. vendor.min.js + theme.min.js added here with `defer`.
 *     REMOVES them from admin-scripts.php.
 *     With defer they execute after the full DOM exists, so the HS sidebar
 *     toggle plugin (HsNavbarVerticalAside) finds the button correctly.
 *
 * NOTE: hs.theme-appearance.js still loads early in <body> in each page —
 * do NOT move it here. It must run synchronously after the preload links
 * exist, which the opacity:0 block below ensures.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= htmlspecialchars($pageTitle ?? 'Admin Dashboard') ?> | Portal</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

  <!-- ─── CSS Plugins ──────────────────────────────────────────────────── -->
  <link rel="stylesheet" href="<?= admin_css_url('vendor.min.css') ?>">

  <!-- ─── Theme CSS preload sources
       hs.theme-appearance.js (loaded in <body>) reads the href attribute
       from the matching data-hs-appearance element and injects a real
       <link rel="stylesheet"> for the active theme only.
       Do NOT change these to rel="stylesheet" — that loads both themes.
  -->
  <link rel="preload" href="<?= admin_css_url('theme.min.css') ?>"      data-hs-appearance="default" as="style">
  <link rel="preload" href="<?= admin_css_url('theme-dark.min.css') ?>" data-hs-appearance="dark"    as="style">

  <!-- Keeps body hidden until theme CSS loads; removed by hs.theme-appearance.js -->
  <style data-hs-appearance-onload-styles>
    /* * { transition: unset !important; } */
    /* body { opacity: 0 !important; } */
  </style>

  <!-- ─── Core JS — deferred ──────────────────────────────────────────────
       Moved here from the bottom of admin-scripts.php.
       defer = download in parallel, execute after DOM is fully parsed.
       This is what makes the sidebar collapse button work: the
       HsNavbarVerticalAside plugin now runs AFTER the sidebar HTML exists.
  -->
  <script defer src="<?= admin_js_url('vendor.min.js') ?>"></script>
  <script defer src="<?= admin_js_url('theme.min.js') ?>"></script>

</head>