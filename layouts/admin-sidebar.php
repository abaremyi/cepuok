<?php
/**
 * Admin Sidebar Layout
 * File: layouts/admin-sidebar.php
 * 
 * @var object $currentUser Current user object from Auth
 * @var array $userPermissions User permissions array
 */
?>
<aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered bg-white">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset">
            <div class="navbar-vertical-content">
                <ul id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">
                    
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/dashboard">
                            <i class="bi-house-door nav-icon"></i>
                            <span class="nav-link-title">Dashboard</span>
                        </a>
                    </li>
                    
                    <?php if (in_array('dashboard.admin_access', $userPermissions) || $currentUser->is_super_admin): ?>
                    
                    <!-- User Management -->
                    <?php if (hasAnyPermission($userPermissions, ['users.view', 'users.create', 'users.edit', 'users.delete'])): ?>
                    <li class="nav-item">
                        <a class="nav-link dropdown-toggle" href="#usersMenu" role="button" data-bs-toggle="collapse" data-bs-target="#usersMenu" aria-expanded="false">
                            <i class="bi-people nav-icon"></i>
                            <span class="nav-link-title">User Management</span>
                        </a>
                        <ul id="usersMenu" class="nav-collapse collapse <?= in_array(basename($_SERVER['PHP_SELF']), ['users-management.php', 'roles-permissions-management.php']) ? 'show' : '' ?>" data-bs-parent="#navbarVerticalMenu">
                            <?php if (hasPermission($userPermissions, 'users.view')): ?>
                            <li><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/users-management">All Users</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission($userPermissions, 'users.create')): ?>
                            <li><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users-add-user.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/users-add-user">Add New User</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission($userPermissions, 'roles.view')): ?>
                            <li><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'roles-permissions-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/roles-permissions-management">Roles & Permissions</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Membership Management -->
                    <?php if (hasAnyPermission($userPermissions, ['membership.view', 'membership.approve'])): ?>
                    <li class="nav-item">
                        <a class="nav-link dropdown-toggle" href="#membershipMenu" role="button" data-bs-toggle="collapse" data-bs-target="#membershipMenu" aria-expanded="false">
                            <i class="bi-person-badge nav-icon"></i>
                            <span class="nav-link-title">Membership</span>
                        </a>
                        <ul id="membershipMenu" class="nav-collapse collapse <?= in_array(basename($_SERVER['PHP_SELF']), ['membership-management.php', 'membership-applications.php']) ? 'show' : '' ?>" data-bs-parent="#navbarVerticalMenu">
                            <?php if (hasPermission($userPermissions, 'membership.view')): ?>
                            <li><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'membership-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/membership-management">All Members</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission($userPermissions, 'membership.approve')): ?>
                            <li><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'membership-applications.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/membership-applications">Pending Applications</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Content Management -->
                    <?php if (hasAnyPermission($userPermissions, ['news.view', 'gallery.view', 'videos.view'])): ?>
                    <li class="nav-item">
                        <a class="nav-link dropdown-toggle" href="#contentMenu" role="button" data-bs-toggle="collapse" data-bs-target="#contentMenu" aria-expanded="false">
                            <i class="bi-file-text nav-icon"></i>
                            <span class="nav-link-title">Content</span>
                        </a>
                        <ul id="contentMenu" class="nav-collapse collapse <?= in_array(basename($_SERVER['PHP_SELF']), ['news-events-management.php', 'gallery-management.php', 'video-gallery-management.php', 'testimonials-management.php']) ? 'show' : '' ?>" data-bs-parent="#navbarVerticalMenu">
                            <?php if (hasPermission($userPermissions, 'news.view')): ?>
                            <li><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'news-events-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/news-events-management">News & Events</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission($userPermissions, 'gallery.view')): ?>
                            <li><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'gallery-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/gallery-management">Photo Gallery</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission($userPermissions, 'videos.view')): ?>
                            <li><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'video-gallery-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/video-gallery-management">Video Gallery</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission($userPermissions, 'testimonials.view')): ?>
                            <li><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'testimonials-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/testimonials-management">Testimonials</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Leadership Management -->
                    <?php if (hasPermission($userPermissions, 'leadership.view') || hasPermission($userPermissions, 'leadership.edit')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'leadership-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/leadership-management">
                            <i class="bi-stars nav-icon"></i>
                            <span class="nav-link-title">Leadership</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Programs Management -->
                    <?php if (hasPermission($userPermissions, 'programs.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'programs-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/programs-management">
                            <i class="bi-calendar-event nav-icon"></i>
                            <span class="nav-link-title">Programs</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Departments Management -->
                    <?php if (hasPermission($userPermissions, 'departments.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'departments-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/departments-management">
                            <i class="bi-diagram-3 nav-icon"></i>
                            <span class="nav-link-title">Departments</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Messages -->
                    <?php if (hasPermission($userPermissions, 'messages.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'messages-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/messages-management">
                            <i class="bi-chat-dots nav-icon"></i>
                            <span class="nav-link-title">Messages</span>
                            <span class="badge bg-primary rounded-pill ms-auto" id="unreadMessagesCount">0</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Settings -->
                    <?php if (hasPermission($userPermissions, 'settings.view') || $currentUser->is_super_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/settings">
                            <i class="bi-gear nav-icon"></i>
                            <span class="nav-link-title">Settings</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php endif; ?>
                    
                    <!-- Session-based sections (Day/Weekend) -->
                    <?php if (!empty($currentUser->session_type) && $currentUser->session_type != 'both'): ?>
                    <li class="nav-item mt-4">
                        <hr class="my-2">
                        <span class="dropdown-header text-uppercase">
                            <i class="bi-calendar-range me-1"></i> 
                            <?= ucfirst($currentUser->session_type) ?> Session
                        </span>
                        <small class="bi-three-dots nav-subtitle-replacer"></small>
                        
                        <!-- Day/Weekend specific menu items -->
                        <a class="nav-link mt-2" href="<?= BASE_URL ?>/admin/session/members">
                            <i class="bi-people-fill nav-icon"></i>
                            <span class="nav-link-title">My Session Members</span>
                        </a>
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/session/activities">
                            <i class="bi-activity nav-icon"></i>
                            <span class="nav-link-title">Session Activities</span>
                        </a>
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/session/reports">
                            <i class="bi-bar-chart nav-icon"></i>
                            <span class="nav-link-title">Session Reports</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="navbar-vertical-footer">
                <ul class="navbar-vertical-footer-list">
                    <li class="navbar-vertical-footer-list-item">
                        <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle" id="logoutBtn">
                            <i class="bi-box-arrow-right"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</aside>

<script>
document.getElementById('logoutBtn')?.addEventListener('click', function() {
    if (confirm('Are you sure you want to logout?')) {
        document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT';
        window.location.href = '<?= BASE_URL ?>/login';
    }
});
</script>