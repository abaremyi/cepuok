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
            <img class="navbar-brand-logo" src="<?= img_url('logos/logo-long.png') ?>" alt="CEP UoK" data-hs-theme-appearance="default">
        </a>
        
        <div class="navbar-nav-wrap-content-start">
            <!-- Navbar Vertical Toggle -->
            <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i class="bi-arrow-bar-left navbar-toggler-short-align" data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>' data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i class="bi-arrow-bar-right navbar-toggler-full-align" data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>' data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
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
                            
                            <a class="dropdown-item" href="javascript:void(0);" id="navbarLogoutBtn">
                                <i class="bi-box-arrow-right dropdown-item-icon"></i> Sign out
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navbar logout with SweetAlert confirmation
    const navbarLogoutBtn = document.getElementById('navbarLogoutBtn');
    if (navbarLogoutBtn) {
        navbarLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Sign Out?',
                text: 'Are you sure you want to sign out of the portal?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d96d20',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, sign out',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= BASE_URL ?>/logout';
                }
            });
        });
    }
});
</script>