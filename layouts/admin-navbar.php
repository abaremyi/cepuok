<?php
/**
 * Admin Navbar Layout
 * File: layouts/admin-navbar.php
 * 
 * @var object $currentUser Current user object from Auth
 */
?>
<header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-container navbar-bordered bg-white">
    <div class="navbar-nav-wrap">
        <!-- Logo -->
        <a class="navbar-brand" href="<?= url('admin/dashboard') ?>" aria-label="CEP UoK">
            <img class="navbar-brand-logo" src="<?= img_url('logo-only.png') ?>" alt="CEP UoK" style="height: 40px;" data-hs-theme-appearance="default">
            <img class="navbar-brand-logo" src="<?= img_url('logo-only.png') ?>" data-hs-theme-appearance="dark">
            <img class="navbar-brand-logo-mini" src="<?= img_url('logo-only.png') ?>" alt="Logo" data-hs-theme-appearance="default">
            <img class="navbar-brand-logo-mini" src="<?= img_url('logo-only.png') ?>" alt="Logo" data-hs-theme-appearance="dark">
        </a>
        
        <div class="navbar-nav-wrap-content-start">
            <!-- Navbar Vertical Toggle -->
            <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i class="bi-arrow-bar-left navbar-toggler-short-align"></i>
                <i class="bi-arrow-bar-right navbar-toggler-full-align"></i>
            </button>
        </div>
        
        <div class="navbar-nav-wrap-content-end">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <!-- Account Dropdown -->
                    <div class="dropdown">
                        <a class="navbar-dropdown-account-wrapper" href="javascript:;" id="accountNavbarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar avatar-sm avatar-circle">
                                <?php if (!empty($currentUser->photo)): ?>
                                    <img class="avatar-img" src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($currentUser->photo) ?>" alt="Profile">
                                <?php else: ?>
                                    <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                        <span class="avatar-initials">
                                            <?= strtoupper(substr($currentUser->firstname ?? 'U', 0, 1) . substr($currentUser->lastname ?? 'U', 0, 1)) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                            </div>
                        </a>
                        
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown-menu navbar-dropdown-menu-borderless navbar-dropdown-account" aria-labelledby="accountNavbarDropdown" style="width: 16rem;">
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-circle">
                                        <?php if (!empty($currentUser->photo)): ?>
                                            <img class="avatar-img" src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($currentUser->photo) ?>" alt="Profile">
                                        <?php else: ?>
                                            <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                                <span class="avatar-initials">
                                                    <?= strtoupper(substr($currentUser->firstname ?? 'U', 0, 1) . substr($currentUser->lastname ?? 'U', 0, 1)) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0"><?= htmlspecialchars($currentUser->firstname . ' ' . $currentUser->lastname) ?></h5>
                                        <p class="card-text text-body"><?= htmlspecialchars($currentUser->email) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="dropdown-divider"></div>
                            
                            <a class="dropdown-item" href="<?= BASE_URL ?>/admin/profile">
                                <i class="bi-person dropdown-item-icon"></i> Profile
                            </a>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/admin/settings">
                                <i class="bi-gear dropdown-item-icon"></i> Settings
                            </a>
                            
                            <div class="dropdown-divider"></div>
                            
                            <a class="dropdown-item" href="<?= BASE_URL ?>/logout">
                                <i class="bi-box-arrow-right dropdown-item-icon"></i> Sign out
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>