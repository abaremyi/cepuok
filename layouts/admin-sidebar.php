<?php
/**
 * Admin Sidebar Layout - CEP Portal v2.1
 * File: layouts/admin-sidebar.php
 * Full portal navigation: Members, Families, Supporters, Finance, Departments, Choir, Projects, Reports
 */

$userPermissions = isset($userPermissions) && is_array($userPermissions) ? $userPermissions : [];
$isSuperAdmin = !empty($currentUser->is_super_admin);
$sessionType  = $currentUser->session_type ?? null; // 'day', 'weekend', 'both'

// Determine session badge
$sessionLabel = '';
$sessionBadgeClass = '';
if ($sessionType === 'day') {
    $sessionLabel = 'Day CEP';
    $sessionBadgeClass = 'bg-warning text-dark';
} elseif ($sessionType === 'weekend') {
    $sessionLabel = 'Weekend CEP';
    $sessionBadgeClass = 'bg-primary text-white';
} elseif ($sessionType === 'both') {
    $sessionLabel = 'All Sessions';
    $sessionBadgeClass = 'bg-success text-white';
}

// Helper: check if current page matches any of the given pages
function sidebarActive($currentPage, $pages) {
    if (is_array($pages)) return in_array($currentPage, $pages);
    return $currentPage === $pages;
}
?>

<aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered bg-white">
  <div class="navbar-vertical-container">
    <div class="navbar-vertical-footer-offset">

      <!-- Logo & Session Badge -->
      <a class="navbar-brand" href="<?= url('admin/dashboard') ?>">
        <img class="navbar-brand-logo" src="<?= img_url('logos/logo-long.png') ?>" alt="CEP UoK" data-hs-theme-appearance="default">
        <img class="navbar-brand-logo-mini" src="<?= img_url('logos/logo-long.png') ?>" alt="CEP UoK" data-hs-theme-appearance="default">
      </a>

      <!-- Collapse Toggle -->
      <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
          <i class="bi-arrow-bar-left navbar-toggler-short-align" data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>' data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
          <i class="bi-arrow-bar-right navbar-toggler-full-align" data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>' data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
      </button>

      <!-- Content -->
      <div class="navbar-vertical-content">

        <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">

          <!-- ===== DASHBOARD ===== -->
          <div class="nav-item">
            <a class="nav-link <?= sidebarActive($currentPage,'admin-dashboard.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/dashboard">
              <i class="bi-house-door nav-icon"></i>
              <span class="nav-link-title">Dashboard</span>
            </a>
          </div>

          <!-- Session Badge -->
          <?php if ($sessionLabel): ?>
            <div class="px-3 py-2 border-bottom mb-2">
              <span class="badge <?= $sessionBadgeClass ?> w-100 py-2" style="font-size:12px;">
                <i class="bi-layers me-1"></i> <?= $sessionLabel ?>
              </span>
            </div>
          <?php endif; ?>

          <div id="navbarVerticalMenuPagesMenu">

            <!-- ========== MEMBER MANAGEMENT ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['membership.view','membership.approve','membership.create'])): ?>
              <span class="dropdown-header mt-3">Member Management</span>
              <small class="bi-three-dots nav-subtitle-replacer"></small>

              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage,['membership-management.php','membership-applications.php','member-view.php','member-families.php']) ? '' : 'collapsed' ?>"
                   href="#membersMenu" data-bs-toggle="collapse" data-bs-target="#membersMenu"
                   aria-expanded="<?= sidebarActive($currentPage,['membership-management.php','membership-applications.php','member-view.php','member-families.php']) ? 'true' : 'false' ?>">
                  <i class="bi-person-badge nav-icon"></i>
                  <span class="nav-link-title">Members</span>
                  <?php
                    // Show pending count badge
                    try {
                      $db = Database::getConnection();
                      $pSql = $sessionType && $sessionType !== 'both' 
                              ? "SELECT COUNT(*) FROM members WHERE status='pending' AND cep_session='$sessionType'"
                              : "SELECT COUNT(*) FROM members WHERE status='pending'";
                      $pendingCount = $db->query($pSql)->fetchColumn();
                      if ($pendingCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-auto"><?= $pendingCount ?></span>
                  <?php endif;
                    } catch(Exception $e) {}
                  ?>
                </a>
                <div id="membersMenu" class="nav-collapse collapse <?= sidebarActive($currentPage,['membership-management.php','membership-applications.php','member-view.php','member-families.php']) ? 'show' : '' ?>">
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'membership.view')): ?>
                    <a class="nav-link <?= $currentPage=='membership-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/membership-management">
                      <i class="bi-people me-2"></i>All Members
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'membership.approve')): ?>
                    <a class="nav-link <?= $currentPage=='membership-applications.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/membership-applications">
                      <i class="bi-clock me-2"></i>Pending Applications
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'membership.create')): ?>
                    <a class="nav-link <?= $currentPage=='member-add.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/member-add">
                      <i class="bi-person-plus me-2"></i>Add Member
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'families.view')): ?>
                    <a class="nav-link <?= $currentPage=='member-families.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/member-families">
                      <i class="bi-diagram-3 me-2"></i>Spiritual Families
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== SUPPORTERS ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['supporters.view','supporters.create'])): ?>
              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage,['supporters-management.php','supporter-add.php']) ? '' : 'collapsed' ?>"
                   href="#supportersMenu" data-bs-toggle="collapse" data-bs-target="#supportersMenu"
                   aria-expanded="<?= sidebarActive($currentPage,['supporters-management.php','supporter-add.php']) ? 'true' : 'false' ?>">
                  <i class="bi-heart nav-icon"></i>
                  <span class="nav-link-title">Supporters</span>
                </a>
                <div id="supportersMenu" class="nav-collapse collapse <?= sidebarActive($currentPage,['supporters-management.php','supporter-add.php']) ? 'show' : '' ?>">
                  <a class="nav-link <?= $currentPage=='supporters-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/supporters-management">
                    <i class="bi-people me-2"></i>All Supporters
                  </a>
                  <a class="nav-link <?= $currentPage=='supporter-add.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/supporter-add">
                    <i class="bi-plus-circle me-2"></i>Add Supporter
                  </a>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== FINANCIAL MANAGEMENT ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['finance.view','finance.record_revenue','finance.manage_budget','finance.approve_funds'])): ?>
              <span class="dropdown-header mt-3">Financial Management</span>
              <small class="bi-three-dots nav-subtitle-replacer"></small>

              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage,['finance-overview.php','finance-revenue.php','finance-budget.php','finance-fund-requests.php','finance-disbursements.php']) ? '' : 'collapsed' ?>"
                   href="#financeMenu" data-bs-toggle="collapse" data-bs-target="#financeMenu"
                   aria-expanded="<?= sidebarActive($currentPage,['finance-overview.php','finance-revenue.php','finance-budget.php','finance-fund-requests.php','finance-disbursements.php']) ? 'true' : 'false' ?>">
                  <i class="bi-cash-stack nav-icon"></i>
                  <span class="nav-link-title">Finance</span>
                  <?php
                    // Count pending fund requests
                  ?>
                </a>
                <div id="financeMenu" class="nav-collapse collapse <?= sidebarActive($currentPage,['finance-overview.php','finance-revenue.php','finance-budget.php','finance-fund-requests.php','finance-disbursements.php']) ? 'show' : '' ?>">
                  <a class="nav-link <?= $currentPage=='finance-overview.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-overview">
                    <i class="bi-bar-chart me-2"></i>Overview
                  </a>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'finance.record_revenue')): ?>
                    <a class="nav-link <?= $currentPage=='finance-revenue.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-revenue">
                      <i class="bi-arrow-down-circle me-2"></i>Revenue / Offerings
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'finance.manage_budget')): ?>
                    <a class="nav-link <?= $currentPage=='finance-budget.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-budget">
                      <i class="bi-pie-chart me-2"></i>Budget Management
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'finance.fund_requests')): ?>
                    <a class="nav-link <?= $currentPage=='finance-fund-requests.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-fund-requests">
                      <i class="bi-send me-2"></i>Fund Requests
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'finance.disburse_funds')): ?>
                    <a class="nav-link <?= $currentPage=='finance-disbursements.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-disbursements">
                      <i class="bi-wallet2 me-2"></i>Disbursements
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'finance.reports')): ?>
                    <a class="nav-link <?= $currentPage=='finance-reports.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-reports">
                      <i class="bi-file-earmark-bar-graph me-2"></i>Financial Reports
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== DEPARTMENTS ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['departments.view','departments.manage_activities'])): ?>
              <span class="dropdown-header mt-3">Operations</span>
              <small class="bi-three-dots nav-subtitle-replacer"></small>

              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage,['departments-portal.php','dept-evangelism.php','dept-social.php','dept-media.php','dept-protocol.php']) ? '' : 'collapsed' ?>"
                   href="#deptMenu" data-bs-toggle="collapse" data-bs-target="#deptMenu"
                   aria-expanded="<?= sidebarActive($currentPage,['departments-portal.php','dept-evangelism.php','dept-social.php','dept-media.php','dept-protocol.php']) ? 'true' : 'false' ?>">
                  <i class="bi-diagram-2 nav-icon"></i>
                  <span class="nav-link-title">Departments</span>
                </a>
                <div id="deptMenu" class="nav-collapse collapse <?= sidebarActive($currentPage,['departments-portal.php','dept-evangelism.php','dept-social.php','dept-media.php','dept-protocol.php']) ? 'show' : '' ?>">
                  <a class="nav-link <?= $currentPage=='departments-portal.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/departments-portal">
                    <i class="bi-grid me-2"></i>All Departments
                  </a>
                  <a class="nav-link <?= $currentPage=='dept-evangelism.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/dept-evangelism">
                    <i class="bi-book me-2"></i>Evangelism & Prayers
                  </a>
                  <a class="nav-link <?= $currentPage=='dept-social.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/dept-social">
                    <i class="bi-people me-2"></i>Social Affairs
                  </a>
                  <a class="nav-link <?= $currentPage=='dept-protocol.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/dept-protocol">
                    <i class="bi-shield-check me-2"></i>Protocol
                  </a>
                  <a class="nav-link <?= $currentPage=='dept-media.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/dept-media">
                    <i class="bi-camera me-2"></i>Media & Communication
                  </a>
                  <a class="nav-link <?= $currentPage=='dept-worship.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/dept-worship">
                    <i class="bi-music-note me-2"></i>Worship Team
                  </a>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== CHOIR ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['choir.view','choir.manage_members'])): ?>
              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage,['choir-management.php','choir-members.php','choir-songs.php','choir-attendance.php','choir-projects.php']) ? '' : 'collapsed' ?>"
                   href="#choirMenu" data-bs-toggle="collapse" data-bs-target="#choirMenu"
                   aria-expanded="<?= sidebarActive($currentPage,['choir-management.php','choir-members.php','choir-songs.php','choir-attendance.php','choir-projects.php']) ? 'true' : 'false' ?>">
                  <i class="bi-music-note-beamed nav-icon"></i>
                  <span class="nav-link-title">Choir</span>
                </a>
                <div id="choirMenu" class="nav-collapse collapse <?= sidebarActive($currentPage,['choir-management.php','choir-members.php','choir-songs.php','choir-attendance.php','choir-projects.php']) ? 'show' : '' ?>">
                  <a class="nav-link <?= $currentPage=='choir-members.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/choir-members">
                    <i class="bi-people me-2"></i>Choir Members
                  </a>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'choir.manage_songs')): ?>
                    <a class="nav-link <?= $currentPage=='choir-songs.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/choir-songs">
                      <i class="bi-vinyl me-2"></i>Repertoire / Songs
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'choir.manage_attendance')): ?>
                    <a class="nav-link <?= $currentPage=='choir-attendance.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/choir-attendance">
                      <i class="bi-check2-square me-2"></i>Attendance
                    </a>
                  <?php endif; ?>
                  <a class="nav-link <?= $currentPage=='choir-projects.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/choir-projects">
                    <i class="bi-collection-play me-2"></i>Choir Projects
                  </a>
                  <a class="nav-link <?= $currentPage=='choir-supporters.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/choir-supporters">
                    <i class="bi-heart me-2"></i>Choir Supporters
                  </a>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== PROJECT MANAGEMENT ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['projects.view','projects.create'])): ?>
              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage,['projects-management.php','project-add.php','project-tasks.php']) ? '' : 'collapsed' ?>"
                   href="#projectMenu" data-bs-toggle="collapse" data-bs-target="#projectMenu"
                   aria-expanded="<?= sidebarActive($currentPage,['projects-management.php','project-add.php','project-tasks.php']) ? 'true' : 'false' ?>">
                  <i class="bi-kanban nav-icon"></i>
                  <span class="nav-link-title">Projects</span>
                </a>
                <div id="projectMenu" class="nav-collapse collapse <?= sidebarActive($currentPage,['projects-management.php','project-add.php','project-tasks.php']) ? 'show' : '' ?>">
                  <a class="nav-link <?= $currentPage=='projects-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/projects-management">
                    <i class="bi-list-task me-2"></i>All Projects
                  </a>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'projects.create')): ?>
                    <a class="nav-link <?= $currentPage=='project-add.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/project-add">
                      <i class="bi-plus-circle me-2"></i>New Project
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== REPORTS ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['reports.view','handover.view'])): ?>
              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage,['reports-overview.php','reports-members.php','reports-finance.php','handover.php']) ? '' : 'collapsed' ?>"
                   href="#reportsMenu" data-bs-toggle="collapse" data-bs-target="#reportsMenu"
                   aria-expanded="<?= sidebarActive($currentPage,['reports-overview.php','reports-members.php','reports-finance.php','handover.php']) ? 'true' : 'false' ?>">
                  <i class="bi-file-earmark-bar-graph nav-icon"></i>
                  <span class="nav-link-title">Reports</span>
                </a>
                <div id="reportsMenu" class="nav-collapse collapse <?= sidebarActive($currentPage,['reports-overview.php','reports-members.php','reports-finance.php','handover.php']) ? 'show' : '' ?>">
                  <a class="nav-link <?= $currentPage=='reports-overview.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/reports-overview">
                    <i class="bi-graph-up me-2"></i>Overview
                  </a>
                  <a class="nav-link <?= $currentPage=='reports-members.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/reports-members">
                    <i class="bi-people me-2"></i>Member Reports
                  </a>
                  <a class="nav-link <?= $currentPage=='reports-finance.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/reports-finance">
                    <i class="bi-cash me-2"></i>Financial Reports
                  </a>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'handover.view')): ?>
                    <a class="nav-link <?= $currentPage=='handover.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/handover">
                      <i class="bi-box-arrow-right me-2"></i>Handover Documents
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== WEBSITE MANAGEMENT ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['news.view','gallery.view','leadership.view'])): ?>
              <span class="dropdown-header mt-3">Website</span>
              <small class="bi-three-dots nav-subtitle-replacer"></small>

              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage,['news-events-management.php','gallery-management.php','video-gallery-management.php','testimonials-management.php','leadership-management.php']) ? '' : 'collapsed' ?>"
                   href="#websiteMenu" data-bs-toggle="collapse" data-bs-target="#websiteMenu"
                   aria-expanded="<?= sidebarActive($currentPage,['news-events-management.php','gallery-management.php','video-gallery-management.php','testimonials-management.php','leadership-management.php']) ? 'true' : 'false' ?>">
                  <i class="bi-globe nav-icon"></i>
                  <span class="nav-link-title">Website Content</span>
                </a>
                <div id="websiteMenu" class="nav-collapse collapse <?= sidebarActive($currentPage,['news-events-management.php','gallery-management.php','video-gallery-management.php','testimonials-management.php','leadership-management.php']) ? 'show' : '' ?>">
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'news.view')): ?>
                    <a class="nav-link <?= $currentPage=='news-events-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/news-events-management">
                      <i class="bi-newspaper me-2"></i>News & Events
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'gallery.view')): ?>
                    <a class="nav-link <?= $currentPage=='gallery-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/gallery-management">
                      <i class="bi-images me-2"></i>Gallery
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'videos.view')): ?>
                    <a class="nav-link <?= $currentPage=='video-gallery-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/video-gallery-management">
                      <i class="bi-camera-video me-2"></i>Videos
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'testimonials.view')): ?>
                    <a class="nav-link <?= $currentPage=='testimonials-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/testimonials-management">
                      <i class="bi-chat-quote me-2"></i>Testimonials
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'leadership.view')): ?>
                    <a class="nav-link <?= $currentPage=='leadership-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/leadership-management">
                      <i class="bi-stars me-2"></i>Leadership
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions,'messages.view')): ?>
                    <a class="nav-link <?= $currentPage=='messages-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/messages-management">
                      <i class="bi-chat-dots me-2"></i>Messages
                      <span class="badge bg-primary rounded-pill ms-auto" id="unreadMessagesCount" style="display:none;">0</span>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== ADMIN / SETTINGS ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions,['users.view','roles.view','settings.view','sessions.view'])): ?>
              <span class="dropdown-header mt-3">Administration</span>
              <small class="bi-three-dots nav-subtitle-replacer"></small>

              <?php if ($isSuperAdmin || hasAnyPermission($userPermissions,['users.view','users.create'])): ?>
                <div class="nav-item">
                  <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage,['users-management.php','users-add-user.php','roles-permissions-management.php']) ? '' : 'collapsed' ?>"
                     href="#usersMenu" data-bs-toggle="collapse" data-bs-target="#usersMenu"
                     aria-expanded="<?= sidebarActive($currentPage,['users-management.php','users-add-user.php','roles-permissions-management.php']) ? 'true' : 'false' ?>">
                    <i class="bi-people nav-icon"></i>
                    <span class="nav-link-title">Users & Roles</span>
                  </a>
                  <div id="usersMenu" class="nav-collapse collapse <?= sidebarActive($currentPage,['users-management.php','users-add-user.php','roles-permissions-management.php']) ? 'show' : '' ?>">
                    <a class="nav-link <?= $currentPage=='users-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/users-management">
                      <i class="bi-people me-2"></i>All Users
                    </a>
                    <a class="nav-link <?= $currentPage=='users-add-user.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/users-add-user">
                      <i class="bi-person-plus me-2"></i>Add User
                    </a>
                    <?php if ($isSuperAdmin || hasPermission($userPermissions,'roles.view')): ?>
                      <a class="nav-link <?= $currentPage=='roles-permissions-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/roles-permissions-management">
                        <i class="bi-shield me-2"></i>Roles & Permissions
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- Settings & Session Management (Super Admin only) -->
              <?php if ($isSuperAdmin): ?>
                <div class="nav-item">
                  <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage,['settings.php','session-management.php','audit-logs.php']) ? '' : 'collapsed' ?>"
                     href="#settingsMenu" data-bs-toggle="collapse" data-bs-target="#settingsMenu"
                     aria-expanded="<?= sidebarActive($currentPage,['settings.php','session-management.php','audit-logs.php']) ? 'true' : 'false' ?>">
                    <i class="bi-gear nav-icon"></i>
                    <span class="nav-link-title">Settings</span>
                  </a>
                  <div id="settingsMenu" class="nav-collapse collapse <?= sidebarActive($currentPage,['settings.php','session-management.php','audit-logs.php']) ? 'show' : '' ?>">
                    <a class="nav-link <?= $currentPage=='settings.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/settings">
                      <i class="bi-sliders me-2"></i>General Settings
                    </a>
                    <a class="nav-link <?= $currentPage=='session-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/session-management">
                      <i class="bi-layers me-2"></i>Session Management
                    </a>
                    <a class="nav-link <?= $currentPage=='audit-logs.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/audit-logs">
                      <i class="bi-journal-text me-2"></i>Audit Logs
                    </a>
                  </div>
                </div>
              <?php endif; ?>
            <?php endif; ?>

          </div><!-- #navbarVerticalMenuPagesMenu -->
        </div>
      </div><!-- .navbar-vertical-content -->

      <!-- Footer -->
      <div class="navbar-vertical-footer" style="background-color: white;">
        <ul class="navbar-vertical-footer-list">
          <!-- User info mini -->
          <li class="navbar-vertical-footer-list-item">
            <div class="d-flex align-items-center gap-2 px-2 py-1">
              <?php
                $photo = $currentUser->photo ?? null;
                $initials = strtoupper(substr($currentUser->firstname??'',0,1) . substr($currentUser->lastname??'',0,1));
              ?>
              <?php if ($photo): ?>
                <img src="<?= ROOT_PATH ?>/<?= htmlspecialchars($photo) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
              <?php else: ?>
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width:32px;height:32px;font-size:12px;"><?= $initials ?></div>
              <?php endif; ?>
              <div class="flex-grow-1 overflow-hidden" style="min-width:0;">
                <div class="text-truncate fw-semibold" style="font-size:12px;"><?= htmlspecialchars(($currentUser->firstname??'') . ' ' . ($currentUser->lastname??'')) ?></div>
                <div class="text-muted text-truncate" style="font-size:11px;"><?= htmlspecialchars($currentUser->role_name ?? '') ?></div>
              </div>
            </div>
          </li>
          <li class="navbar-vertical-footer-list-item">
            <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle" id="logoutBtn" data-bs-toggle="tooltip" title="Sign Out">
              <i class="bi-box-arrow-right"></i>
            </button>
            <a href="<?= BASE_URL ?>/admin/profile" class="btn btn-ghost-secondary btn-icon rounded-circle" data-bs-toggle="tooltip" title="Profile">
              <i class="bi-person-circle"></i>
            </a>
          </li>
        </ul>
      </div>

    </div><!-- .navbar-vertical-footer-offset -->
  </div><!-- .navbar-vertical-container -->
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // --- Logout Logic ---
  const logoutBtn = document.getElementById('logoutBtn');
  
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
      // Note: Swal (SweetAlert2) is already a JavaScript library, 
      // so the internal logic remains exactly the same!
      Swal.fire({
        title: 'Sign Out?',
        text: 'Are you sure you want to sign out of the portal?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d96d20',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, sign out',
        cancelButtonText: 'Cancel'
      }).then(result => {
        if (result.isConfirmed) {
          // Standard Vanilla JS cookie deletion
          document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT; SameSite=Strict';
          window.location.href = '<?= BASE_URL ?>/membership';
        }
      });
    });
  }

  // --- Load Unread Messages ---
  // We define it inside or outside, but call it here
  loadUnreadMessages();
  setInterval(loadUnreadMessages, 60000);
});

// Using async/await for the background check
async function loadUnreadMessages() {
  const badge = document.getElementById('unreadMessagesCount');
  if (!badge) return;

  try {
    const response = await fetch('<?= BASE_URL ?>/api/contact?action=getUnreadCount');
    
    // Check if the response is actually a 200 OK
    if (!response.ok) return; 

    const res = await response.json();

    if (res.success && res.count > 0) {
      badge.textContent = res.count;
      badge.style.display = 'inline-block';
    } else {
      badge.style.display = 'none';
    }
  } catch (error) {
    // Silent fail as requested in your original code
    console.debug('Failed to fetch unread count');
  }
}
</script>