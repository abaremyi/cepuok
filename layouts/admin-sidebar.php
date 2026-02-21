<?php
/**
 * Admin Sidebar Layout
 * File: layouts/admin-sidebar.php
 * 
 * @var object $currentUser Current user object from Auth
 * @var array $userPermissions User permissions array
 */

// Ensure $userPermissions is an array
$userPermissions = isset($userPermissions) && is_array($userPermissions) ? $userPermissions : [];

// Current page for active state
// $currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside
  class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered bg-white">
  <div class="navbar-vertical-container">
    <div class="navbar-vertical-footer-offset">
      <!-- Logo -->

      <a class="navbar-brand" href="<?= url('admin/dashboard') ?>" aria-label="CEP UoK">
        <img class="navbar-brand-logo" src="<?= img_url('logo-only.png') ?>" alt="Logo"
          data-hs-theme-appearance="default" style="height: 100%;">
        <img class="navbar-brand-logo" src="<?= img_url('logo-only.png') ?>" data-hs-theme-appearance="dark"
          style="height: 100%;">
        <img class="navbar-brand-logo-mini" src="<?= img_url('logo-only.png') ?>" alt="Logo"
          data-hs-theme-appearance="default" style="height: 100%;">
        <img class="navbar-brand-logo-mini" src="<?= img_url('logo-only.png') ?>" alt="Logo"
          data-hs-theme-appearance="dark" style="height: 100%;">
      </a>

      <!-- End Logo -->

      <!-- Navbar Vertical Toggle -->
      <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
        <i class="bi-arrow-bar-left navbar-toggler-short-align"
          data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
          data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
        <i class="bi-arrow-bar-right navbar-toggler-full-align"
          data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
          data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
      </button>

      <!-- End Navbar Vertical Toggle -->

      <!-- Content -->
      <div class="navbar-vertical-content">
        <ul id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">

          <!-- Dashboard -->
          <div class="nav-item">
            <a class="nav-link <?= $currentPage == 'admin-dashboard.php' ? 'active' : '' ?>"
              href="<?= BASE_URL ?>/admin/dashboard">
              <i class="bi-house-door nav-icon"></i>
              <span class="nav-link-title">Dashboard</span>
            </a>
          </div>

          <span class="dropdown-header mt-4">Website Management</span>
          <small class="bi-three-dots nav-subtitle-replacer"></small>

          <!-- Collapse -->
          <div class="navbar-nav nav-compact">

          </div>

          <div id="navbarVerticalMenuPagesMenu">

            <?php if (hasAnyPermission($userPermissions, ['dashboard.admin_access']) || !empty($currentUser->is_super_admin)): ?>

              <!-- User Management -->
              <?php if (hasAnyPermission($userPermissions, ['users.view', 'users.create', 'users.edit', 'users.delete'])): ?>
                <div class="nav-item">
                  <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['users-management.php', 'users-add-user.php', 'roles-permissions-management.php']) ? '' : 'collapsed' ?>"
                    href="#usersMenu" role="button" data-bs-toggle="collapse" data-bs-target="#usersMenu"
                    aria-expanded="<?= in_array($currentPage, ['users-management.php', 'users-add-user.php', 'roles-permissions-management.php']) ? 'true' : 'false' ?>">
                    <i class="bi-people nav-icon"></i>
                    <span class="nav-link-title">User Management</span>
                  </a>
                  <div id="usersMenu"
                    class="nav-collapse collapse <?= in_array($currentPage, ['users-management.php', 'users-add-user.php', 'roles-permissions-management.php']) ? 'show' : '' ?>"
                    data-bs-parent="#navbarVerticalMenuPagesMenu">
                    <?php if (hasPermission($userPermissions, 'users.view')): ?>
                      <a class="nav-link <?= $currentPage == 'users-management.php' ? 'active' : '' ?>"
                          href="<?= BASE_URL ?>/admin/users-management">All Users</a>
                    <?php endif; ?>
                    <?php if (hasPermission($userPermissions, 'users.create')): ?>
                      <a class="nav-link <?= $currentPage == 'users-add-user.php' ? 'active' : '' ?>"
                          href="<?= BASE_URL ?>/admin/users-add-user">Add New User</a>
                    <?php endif; ?>
                    <?php if (hasPermission($userPermissions, 'roles.view')): ?>
                      <a class="nav-link <?= $currentPage == 'roles-permissions-management.php' ? 'active' : '' ?>"
                          href="<?= BASE_URL ?>/admin/roles-permissions-management">Roles & Permissions</a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- Membership Management -->
              <?php if (hasAnyPermission($userPermissions, ['membership.view', 'membership.approve'])): ?>
                <div class="nav-item">
                  <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['membership-management.php', 'membership-applications.php']) ? '' : 'collapsed' ?>"
                    href="#membershipMenu" role="button" data-bs-toggle="collapse" data-bs-target="#membershipMenu"
                    aria-expanded="<?= in_array($currentPage, ['membership-management.php', 'membership-applications.php']) ? 'true' : 'false' ?>">
                    <i class="bi-person-badge nav-icon"></i>
                    <span class="nav-link-title">Membership</span>
                  </a>
                  <div id="membershipMenu"
                    class="nav-collapse collapse <?= in_array($currentPage, ['membership-management.php', 'membership-applications.php']) ? 'show' : '' ?>"
                    data-bs-parent="#navbarVerticalMenu">
                    <?php if (hasPermission($userPermissions, 'membership.view')): ?>
                      <a class="nav-link <?= $currentPage == 'membership-management.php' ? 'active' : '' ?>"
                          href="<?= BASE_URL ?>/admin/membership-management">All Members</a>
                    <?php endif; ?>
                    <?php if (hasPermission($userPermissions, 'membership.approve')): ?>
                      <a class="nav-link <?= $currentPage == 'membership-applications.php' ? 'active' : '' ?>"
                          href="<?= BASE_URL ?>/admin/membership-applications">Pending Applications</a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- Content Management -->
              <?php if (hasAnyPermission($userPermissions, ['news.view', 'gallery.view', 'videos.view'])): ?>
                <div class="nav-item">
                  <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['news-events-management.php', 'gallery-management.php', 'video-gallery-management.php', 'testimonials-management.php']) ? '' : 'collapsed' ?>"
                    href="#contentMenu" role="button" data-bs-toggle="collapse" data-bs-target="#contentMenu"
                    aria-expanded="<?= in_array($currentPage, ['news-events-management.php', 'gallery-management.php', 'video-gallery-management.php', 'testimonials-management.php']) ? 'true' : 'false' ?>">
                    <i class="bi-file-text nav-icon"></i>
                    <span class="nav-link-title">Content</span>
                  </a>
                  <div id="contentMenu"
                    class="nav-collapse collapse <?= in_array($currentPage, ['news-events-management.php', 'gallery-management.php', 'video-gallery-management.php', 'testimonials-management.php']) ? 'show' : '' ?>"
                    data-bs-parent="#navbarVerticalMenu">
                    <?php if (hasPermission($userPermissions, 'news.view')): ?>
                      <a class="nav-link <?= $currentPage == 'news-events-management.php' ? 'active' : '' ?>"
                          href="<?= BASE_URL ?>/admin/news-events-management">News & Events</a>
                    <?php endif; ?>
                    <?php if (hasPermission($userPermissions, 'gallery.view')): ?>
                      <a class="nav-link <?= $currentPage == 'gallery-management.php' ? 'active' : '' ?>"
                          href="<?= BASE_URL ?>/admin/gallery-management">Photo Gallery</a>
                    <?php endif; ?>
                    <?php if (hasPermission($userPermissions, 'videos.view')): ?>
                      <a class="nav-link <?= $currentPage == 'video-gallery-management.php' ? 'active' : '' ?>"
                          href="<?= BASE_URL ?>/admin/video-gallery-management">Video Gallery</a>
                    <?php endif; ?>
                    <?php if (hasPermission($userPermissions, 'testimonials.view')): ?>
                      <a class="nav-link <?= $currentPage == 'testimonials-management.php' ? 'active' : '' ?>"
                          href="<?= BASE_URL ?>/admin/testimonials-management">Testimonials</a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- Leadership Management -->
              <?php if (hasAnyPermission($userPermissions, ['leadership.view', 'leadership.edit'])): ?>
                <div class="nav-item">
                  <a class="nav-link <?= $currentPage == 'leadership-management.php' ? 'active' : '' ?>"
                    href="<?= BASE_URL ?>/admin/leadership-management">
                    <i class="bi-stars nav-icon"></i>
                    <span class="nav-link-title">Leadership</span>
                  </a>
                </div>
              <?php endif; ?>

              <!-- Programs Management -->
              <?php if (hasPermission($userPermissions, 'programs.view')): ?>
                <div class="nav-item">
                  <a class="nav-link <?= $currentPage == 'programs-management.php' ? 'active' : '' ?>"
                    href="<?= BASE_URL ?>/admin/programs-management">
                    <i class="bi-calendar-event nav-icon"></i>
                    <span class="nav-link-title">Programs</span>
                  </a>
                </div>
              <?php endif; ?>

              <!-- Departments Management -->
              <?php if (hasPermission($userPermissions, 'departments.view')): ?>
                <div class="nav-item">
                  <a class="nav-link <?= $currentPage == 'departments-management.php' ? 'active' : '' ?>"
                    href="<?= BASE_URL ?>/admin/departments-management">
                    <i class="bi-diagram-3 nav-icon"></i>
                    <span class="nav-link-title">Departments</span>
                  </a>
                </div>
              <?php endif; ?>

              <!-- Messages -->
              <?php if (hasPermission($userPermissions, 'messages.view')): ?>
                <div class="nav-item">
                  <a class="nav-link <?= $currentPage == 'messages-management.php' ? 'active' : '' ?>"
                    href="<?= BASE_URL ?>/admin/messages-management">
                    <i class="bi-chat-dots nav-icon"></i>
                    <span class="nav-link-title">Messages</span>
                    <span class="badge bg-primary rounded-pill ms-auto" id="unreadMessagesCount">0</span>
                  </a>
                </div>
              <?php endif; ?>

              <!-- Settings -->
              <?php if (hasPermission($userPermissions, 'settings.view') || !empty($currentUser->is_super_admin)): ?>
                <div class="nav-item">
                  <a class="nav-link <?= $currentPage == 'settings.php' ? 'active' : '' ?>"
                    href="<?= BASE_URL ?>/admin/settings">
                    <i class="bi-gear nav-icon"></i>
                    <span class="nav-link-title">Settings</span>
                  </a>
                </div>
              <?php endif; ?>

            <?php endif; ?>
          </div>

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
              <a class="nav-link mt-2 <?= $currentPage == 'session-members.php' ? 'active' : '' ?>"
                href="<?= BASE_URL ?>/admin/session/members">
                <i class="bi-people-fill nav-icon"></i>
                <span class="nav-link-title">My Session Members</span>
              </a>
              <a class="nav-link <?= $currentPage == 'session-activities.php' ? 'active' : '' ?>"
                href="<?= BASE_URL ?>/admin/session/activities">
                <i class="bi-activity nav-icon"></i>
                <span class="nav-link-title">Session Activities</span>
              </a>
              <a class="nav-link <?= $currentPage == 'session-reports.php' ? 'active' : '' ?>"
                href="<?= BASE_URL ?>/admin/session/reports">
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
            <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle" id="logoutBtn" title="Logout">
              <i class="bi-box-arrow-right"></i>
            </button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</aside>

<script>
  document.getElementById('logoutBtn')?.addEventListener('click', function () {
    if (confirm('Are you sure you want to logout?')) {
      // Clear auth cookie
      document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT; SameSite=Strict';
      window.location.href = '<?= BASE_URL ?>/logout';
    }
  });

  // Load unread messages count
  function loadUnreadMessages() {
    $.ajax({
      url: '<?= BASE_URL ?>/api/messages?action=getUnreadCount',
      type: 'GET',
      success: function (response) {
        if (response.success && response.count > 0) {
          $('#unreadMessagesCount').text(response.count).show();
        } else {
          $('#unreadMessagesCount').hide();
        }
      }
    });
  }

  $(document).ready(function () {
    loadUnreadMessages();
    setInterval(loadUnreadMessages, 60000); // Refresh every minute
  });
</script>