<?php
// modules/Dashboard/views/components/admin-sidebar.php

// Get token from cookie
$token = $_COOKIE['auth_token'] ?? '';
$jwtHandler = new JWTHandler();
$decoded = $token ? $jwtHandler->validateToken($token) : null;
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">
        <div class="d-flex align-items-center justify-content-center mb-3">
            <div style="width: 50px; height: 50px; background: white; border-radius: 2px; 
                      display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                <img src="<?= img_url('logo-only.png') ?>" alt="MCS Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h4 class="mb-0">MOUNT CARMEL</h4>
        </div>
        <small class="text-white-50">A Private Christian School</small>
    </div>
    
    <div class="sidebar-content">
        <ul class="nav flex-column mt-4">
            <li class="nav-item">
                <a href="<?= url('admin/dashboard') ?>" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            
            <?php if ($decoded->is_super_admin || in_array('users.view', $decoded->permissions)): ?>
            <li class="nav-item">
                <a href="<?= url('admin/users-management') ?>" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users-management.php' ? 'active' : '' ?>">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <?php endif; ?>

            <?php if ($decoded->is_super_admin || in_array('roles.view', $decoded->permissions)): ?>
            <li class="nav-item">
                <a href="<?= url('admin/roles-permissions') ?>" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'roles-permissions-management.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-tag me-2"></i> Roles & Permissions
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Leadership section (replacing Staff) -->
            <?php if ($decoded->is_super_admin || in_array('leadership.view', $decoded->permissions)): ?>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-user-tie me-2"></i> Leadership
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="<?= url('admin/leadership') ?>" class="dropdown-item">
                            <i class="fas fa-users-cog me-2"></i> Leadership Management
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- Website Management -->
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-globe me-2"></i> Website
                </a>
                <ul class="dropdown-menu">
                    <?php if ($decoded->is_super_admin || in_array('website.manage_sliders', $decoded->permissions)): ?>
                    <li>
                        <a href="<?= url('admin/hero-sliders') ?>" class="dropdown-item">
                            <i class="fas fa-images me-2"></i> Hero Sliders
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($decoded->is_super_admin || in_array('website.manage_news', $decoded->permissions)): ?>
                    <li>
                        <a href="<?= url('admin/news-events') ?>" class="dropdown-item">
                            <i class="fas fa-newspaper me-2"></i> News & Events
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($decoded->is_super_admin || in_array('website.manage_testimonials', $decoded->permissions)): ?>
                    <li>
                        <a href="<?= url('admin/testimonials') ?>" class="dropdown-item">
                            <i class="fas fa-quote-left me-2"></i> Testimonials
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($decoded->is_super_admin || in_array('website.manage_content', $decoded->permissions)): ?>
                    <li>
                        <a href="<?= url('admin/page-content') ?>" class="dropdown-item">
                            <i class="fas fa-file-alt me-2"></i> Page Content
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?= url('admin/why-choose') ?>" class="dropdown-item">
                            <i class="fas fa-star me-2"></i> Why Choose Us
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('admin/quick-stats') ?>" class="dropdown-item">
                            <i class="fas fa-chart-bar me-2"></i> Quick Stats
                        </a>
                    </li>
                </ul>
            </li>
            
            <?php if ($decoded->is_super_admin || in_array('programs.view', $decoded->permissions)): ?>
            <li class="nav-item">
                <a href="<?= url('admin/educational-programs') ?>" class="nav-link">
                    <i class="fas fa-book-open me-2"></i> Programs
                </a>
            </li>
            <?php endif; ?>

            <?php if ($decoded->is_super_admin || in_array('programs.view', $decoded->permissions)): ?>
            <li class="nav-item">
                <a href="<?= url('admin/admission') ?>" class="nav-link">
                    <i class="fas fa-user-graduate me-2"></i> Admission
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Gallery Dropdown -->
            <?php if ($decoded->is_super_admin || in_array('website.manage_gallery', $decoded->permissions)): ?>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-images me-2"></i> Gallery
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="<?= url('admin/gallery') ?>" class="dropdown-item">
                            <i class="fas fa-camera me-2"></i> Photo Gallery
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('admin/video-gallery') ?>" class="dropdown-item">
                            <i class="fas fa-video me-2"></i> Video Gallery
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link <?= (basename($_SERVER['REQUEST_URI']) == 'facilities-management.php' || basename($_SERVER['REQUEST_URI']) == 'facilities') ? 'active' : '' ?>" 
                   href="<?= url('admin/facilities') ?>">
                    <i class="fas fa-building me-2"></i>
                    <span>Facilities Management</span>
                </a>
            </li>
            
            
        </ul>
    </div>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar-small">
                <?php if (!empty($decoded->avatar)): ?>
                    <img src="<?= img_url($decoded->avatar) ?>" alt="<?= htmlspecialchars($decoded->full_name) ?>">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?= htmlspecialchars($decoded->full_name ?? 'Admin') ?></div>
                <div class="user-role"><?= htmlspecialchars($decoded->role_name ?? 'Administrator') ?></div>
            </div>
        </div>
    </div>
</div>