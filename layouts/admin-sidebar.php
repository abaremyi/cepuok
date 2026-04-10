<?php
/**
 * Admin Sidebar Layout - CEP Portal 
 * File: layouts/admin-sidebar.php
 */

$userPermissions = isset($userPermissions) && is_array($userPermissions) ? $userPermissions : [];
$isSuperAdmin = !empty($currentUser->is_super_admin);
$sessionType  = $currentUser->session_type ?? null;

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

function sidebarActive($currentPage, $pages) {
    if (is_array($pages)) return in_array($currentPage, $pages);
    return $currentPage === $pages;
}

// Safe DB count helper — returns 0 on any error
function sidebarCount($sql) {
    try {
        $db = Database::getConnection();
        return (int) $db->query($sql)->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}
?>

<aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered bg-white">
  <div class="navbar-vertical-container">
    <div class="navbar-vertical-footer-offset">

      <!-- Logo -->
      <a class="navbar-brand" href="<?= url('admin/dashboard') ?>">
        <img class="navbar-brand-logo" src="<?= img_url('logos/logo-long.png') ?>" alt="CEP UoK" data-hs-theme-appearance="default">
        <img class="navbar-brand-logo-mini" src="<?= img_url('logos/logo-long.png') ?>" alt="CEP UoK" data-hs-theme-appearance="default">
      </a>

      <!-- Collapse Toggle -->
      <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
        <i class="bi-arrow-bar-left navbar-toggler-short-align"
           data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
           data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
        <i class="bi-arrow-bar-right navbar-toggler-full-align"
           data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
           data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
      </button>

      <div class="navbar-vertical-content">
        <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">

          <!-- ===== DASHBOARD ===== -->
          <div class="nav-item">
            <a class="nav-link <?= sidebarActive($currentPage, 'admin-dashboard.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/dashboard">
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
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['membership.view','membership.approve','membership.create','families.view'])): ?>
              <span class="dropdown-header mt-3">Member Management</span>
              <small class="bi-three-dots nav-subtitle-replacer"></small>

              <?php
                $sessFilter = ($sessionType && $sessionType !== 'both') ? "AND cep_session='$sessionType'" : '';
                $pendingMembers = sidebarCount("SELECT COUNT(*) FROM members WHERE status='pending' $sessFilter");
              ?>
              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage, ['membership-management.php','membership-applications.php','member-view.php','member-add.php','member-families.php','family-detail.php','members-statistics.php']) ? '' : 'collapsed' ?>"
                   href="#membersMenu" data-bs-toggle="collapse" data-bs-target="#membersMenu"
                   aria-expanded="<?= sidebarActive($currentPage, ['membership-management.php','membership-applications.php','member-view.php','member-add.php','member-families.php','family-detail.php','members-statistics.php']) ? 'true' : 'false' ?>">
                  <i class="bi-person-badge nav-icon"></i>
                  <span class="nav-link-title">Members</span>
                  <?php if ($pendingMembers > 0): ?>
                    <span class="badge bg-danger rounded-pill ms-auto"><?= $pendingMembers ?></span>
                  <?php endif; ?>
                </a>
                <div id="membersMenu" class="nav-collapse collapse <?= sidebarActive($currentPage, ['membership-management.php','membership-applications.php','member-view.php','member-add.php','member-families.php','family-detail.php','members-statistics.php']) ? 'show' : '' ?>">
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'membership.view')): ?>
                    <a class="nav-link <?= $currentPage == 'members-statistics.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/members-statistics">
                      <i class="bi-bar-chart-line me-2"></i>Statistics
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'membership.view')): ?>
                    <a class="nav-link <?= $currentPage == 'membership-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/membership-management">
                      <i class="bi-people me-2"></i>All Members
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'membership.approve')): ?>
                    <a class="nav-link <?= $currentPage == 'membership-applications.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/membership-applications">
                      <i class="bi-clock me-2"></i>Pending Applications
                      <?php if ($pendingMembers > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-auto"><?= $pendingMembers ?></span>
                      <?php endif; ?>
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'membership.create')): ?>
                    <a class="nav-link <?= $currentPage == 'member-add.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/member-add">
                      <i class="bi-person-plus me-2"></i>Add Member
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'families.view')): ?>
                    <a class="nav-link <?= in_array($currentPage, ['member-families.php','family-detail.php']) ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/member-families">
                      <i class="bi-diagram-3 me-2"></i>Spiritual Families
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== SUPPORTERS ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['supporters.view','supporters.create','supporters.contributions'])): ?>
              <div class="nav-item">
                <!-- Supporters: adding is done via modal inside supporters-management, so no submenu needed -->
                <a class="nav-link <?= in_array($currentPage, ['supporters-management.php','supporter-detail.php']) ? 'active' : '' ?>"
                   href="<?= BASE_URL ?>/admin/supporters-management">
                  <i class="bi-heart nav-icon"></i>
                  <span class="nav-link-title">Supporters</span>
                </a>
              </div>
            <?php endif; ?>

            <!-- ========== FINANCIAL MANAGEMENT ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['finance.view','finance.record_revenue','finance.manage_budget','finance.fund_requests','finance.approve_funds','finance.disburse_funds','finance.reports'])): ?>
              <span class="dropdown-header mt-3">Financial Management</span>
              <small class="bi-three-dots nav-subtitle-replacer"></small>

              <?php
                $sessFilter2 = ($sessionType && $sessionType !== 'both') ? "AND cep_session='$sessionType'" : '';
                $pendingRequests = sidebarCount("SELECT COUNT(*) FROM fund_requests WHERE stage='to_president' $sessFilter2");
              ?>
              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage, ['finance-dashboard.php','finance-revenue.php','finance-budget.php','finance-budget-indicators.php','finance-fund-requests.php','finance-disbursements.php','finance-reports.php']) ? '' : 'collapsed' ?>"
                  href="#financeMenu" data-bs-toggle="collapse" data-bs-target="#financeMenu"
                  aria-expanded="<?= sidebarActive($currentPage, ['finance-dashboard.php','finance-revenue.php','finance-budget.php','finance-budget-indicators.php','finance-fund-requests.php','finance-disbursements.php','finance-reports.php']) ? 'true' : 'false' ?>">
                  <i class="bi-cash-stack nav-icon"></i>
                  <span class="nav-link-title">Finance</span>
                  <?php if ($pendingRequests > 0): ?>
                    <span class="badge bg-warning text-dark rounded-pill ms-auto"><?= $pendingRequests ?></span>
                  <?php endif; ?>
                </a>
                <div id="financeMenu" class="nav-collapse collapse <?= sidebarActive($currentPage, ['finance-dashboard.php','finance-revenue.php','finance-budget.php','finance-budget-indicators.php','finance-fund-requests.php','finance-disbursements.php','finance-reports.php']) ? 'show' : '' ?>">
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'finance.view')): ?>
                    <a class="nav-link <?= $currentPage == 'finance-dashboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-dashboard">
                      <i class="bi-bar-chart me-2"></i>Overview
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'finance.record_revenue')): ?>
                    <a class="nav-link <?= $currentPage == 'finance-revenue.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-revenue">
                      <i class="bi-arrow-down-circle me-2"></i>Revenue / Offerings
                    </a>
                  <?php endif; ?>
                  
                  <!-- NEW: Budget Indicators Link -->
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'finance.manage_budget')): ?>
                    <a class="nav-link <?= $currentPage == 'finance-budget-indicators.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-budget-indicators">
                      <i class="bi-pie-chart-fill me-2"></i>Budget Indicators
                    </a>
                  <?php endif; ?>
                  
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'finance.manage_budget')): ?>
                    <a class="nav-link <?= $currentPage == 'finance-budget.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-budget">
                      <i class="bi-calendar3-range me-2"></i>Quarterly Budgets
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'finance.fund_requests')): ?>
                    <a class="nav-link <?= $currentPage == 'finance-fund-requests.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-fund-requests">
                      <i class="bi-send me-2"></i>Fund Requests
                      <?php if ($pendingRequests > 0): ?>
                        <span class="badge bg-warning text-dark rounded-pill ms-auto"><?= $pendingRequests ?></span>
                      <?php endif; ?>
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'finance.disburse_funds')): ?>
                    <a class="nav-link <?= $currentPage == 'finance-disbursements.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-disbursements">
                      <i class="bi-wallet2 me-2"></i>Disbursements
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'finance.reports')): ?>
                    <a class="nav-link <?= $currentPage == 'finance-reports.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-reports">
                      <i class="bi-file-earmark-bar-graph me-2"></i>Financial Reports
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin): ?>
                    <a class="nav-link <?= $currentPage == 'finance-maintenance.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/finance-maintenance">
                        <i class="bi-tools me-2"></i>Maintenance
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
                <!-- All dept sub-pages (dept-evangelism etc.) had no routes → collapsed into one link -->
                <a class="nav-link <?= $currentPage == 'departments-management.php' ? 'active' : '' ?>"
                   href="<?= BASE_URL ?>/admin/departments-management">
                  <i class="bi-diagram-2 nav-icon"></i>
                  <span class="nav-link-title">Departments</span>
                </a>
              </div>
            <?php endif; ?>

            <!-- ========== CHOIR ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['choir.view','choir.manage_members','choir.manage_songs','choir.manage_attendance'])): ?>
              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage, ['choir-members.php','choir-songs.php','choir-attendance.php','choir-projects.php']) ? '' : 'collapsed' ?>"
                   href="#choirMenu" data-bs-toggle="collapse" data-bs-target="#choirMenu"
                   aria-expanded="<?= sidebarActive($currentPage, ['choir-members.php','choir-songs.php','choir-attendance.php','choir-projects.php']) ? 'true' : 'false' ?>">
                  <i class="bi-music-note-beamed nav-icon"></i>
                  <span class="nav-link-title">Choir</span>
                </a>
                <div id="choirMenu" class="nav-collapse collapse <?= sidebarActive($currentPage, ['choir-members.php','choir-songs.php','choir-attendance.php','choir-projects.php']) ? 'show' : '' ?>">
                  <a class="nav-link <?= $currentPage == 'choir-members.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/choir-members">
                    <i class="bi-people me-2"></i>Choir Members
                  </a>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'choir.manage_songs')): ?>
                    <a class="nav-link <?= $currentPage == 'choir-songs.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/choir-songs">
                      <i class="bi-vinyl me-2"></i>Repertoire / Songs
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'choir.manage_attendance')): ?>
                    <a class="nav-link <?= $currentPage == 'choir-attendance.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/choir-attendance">
                      <i class="bi-check2-square me-2"></i>Attendance
                    </a>
                  <?php endif; ?>
                  <a class="nav-link <?= $currentPage == 'choir-projects.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/choir-projects">
                    <i class="bi-collection-play me-2"></i>Choir Projects
                  </a>
                  <!-- REMOVED: /admin/choir-supporters had no route -->
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== PROJECT MANAGEMENT ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['projects.view','projects.create'])): ?>
              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage, ['projects-management.php','project-add.php']) ? '' : 'collapsed' ?>"
                   href="#projectMenu" data-bs-toggle="collapse" data-bs-target="#projectMenu"
                   aria-expanded="<?= sidebarActive($currentPage, ['projects-management.php','project-add.php']) ? 'true' : 'false' ?>">
                  <i class="bi-kanban nav-icon"></i>
                  <span class="nav-link-title">Projects</span>
                </a>
                <div id="projectMenu" class="nav-collapse collapse <?= sidebarActive($currentPage, ['projects-management.php','project-add.php']) ? 'show' : '' ?>">
                  <a class="nav-link <?= $currentPage == 'projects-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/projects-management">
                    <i class="bi-list-task me-2"></i>All Projects
                  </a>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'projects.create')): ?>
                    <!-- Add project is via modal on projects-management page -->
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/projects-management">
                      <i class="bi-plus-circle me-2"></i>New Project
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== REPORTS ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['reports.view','reports.export'])): ?>
              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage, ['reports-overview.php','reports-members.php','reports-members-custom.php','reports-finance.php']) ? '' : 'collapsed' ?>"
                   href="#reportsMenu" data-bs-toggle="collapse" data-bs-target="#reportsMenu"
                   aria-expanded="<?= sidebarActive($currentPage, ['reports-overview.php','reports-members.php','reports-members-custom.php','reports-finance.php']) ? 'true' : 'false' ?>">
                  <i class="bi-file-earmark-bar-graph nav-icon"></i>
                  <span class="nav-link-title">Reports</span>
                </a>
                <div id="reportsMenu" class="nav-collapse collapse <?= sidebarActive($currentPage, ['reports-overview.php','reports-members.php','reports-members-custom.php','reports-finance.php']) ? 'show' : '' ?>">
                  <a class="nav-link <?= $currentPage == 'reports-overview.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/reports-overview">
                    <i class="bi-graph-up me-2"></i>Overview
                  </a>
                  <a class="nav-link <?= $currentPage == 'reports-members.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/reports-members">
                    <i class="bi-people me-2"></i>Member Reports
                  </a>
                  <a class="nav-link <?= $currentPage == 'reports-members-custom.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/reports-members-custom">
                    <i class="bi-file-person me-2"></i>Custom Member Report
                    <!-- <span class="badge bg-soft-primary text-primary ms-auto" style="font-size:9px;">NEW</span> -->
                  </a>
                  <a class="nav-link <?= $currentPage == 'reports-finance.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/reports-finance">
                    <i class="bi-cash me-2"></i>Financial Reports
                  </a>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== WEBSITE MANAGEMENT ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['news.view','gallery.view','videos.view','testimonials.view','leadership.view','messages.view'])): ?>
              <span class="dropdown-header mt-3">Website</span>
              <small class="bi-three-dots nav-subtitle-replacer"></small>

              <div class="nav-item">
                <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage, ['news-events-management.php','gallery-management.php','video-gallery-management.php','testimonials-management.php','leadership-management.php','messages-management.php']) ? '' : 'collapsed' ?>"
                   href="#websiteMenu" data-bs-toggle="collapse" data-bs-target="#websiteMenu"
                   aria-expanded="<?= sidebarActive($currentPage, ['news-events-management.php','gallery-management.php','video-gallery-management.php','testimonials-management.php','leadership-management.php','messages-management.php']) ? 'true' : 'false' ?>">
                  <i class="bi-globe nav-icon"></i>
                  <span class="nav-link-title">Website Content</span>
                </a>
                <div id="websiteMenu" class="nav-collapse collapse <?= sidebarActive($currentPage, ['news-events-management.php','gallery-management.php','video-gallery-management.php','testimonials-management.php','leadership-management.php','messages-management.php']) ? 'show' : '' ?>">
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'news.view')): ?>
                    <a class="nav-link <?= $currentPage == 'news-events-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/news-events-management">
                      <i class="bi-newspaper me-2"></i>News & Events
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'gallery.view')): ?>
                    <a class="nav-link <?= $currentPage == 'gallery-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/gallery-management">
                      <i class="bi-images me-2"></i>Gallery
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'videos.view')): ?>
                    <a class="nav-link <?= $currentPage == 'video-gallery-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/video-gallery-management">
                      <i class="bi-camera-video me-2"></i>Videos
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'testimonials.view')): ?>
                    <a class="nav-link <?= $currentPage == 'testimonials-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/testimonials-management">
                      <i class="bi-chat-quote me-2"></i>Testimonials
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'leadership.view')): ?>
                    <a class="nav-link <?= $currentPage == 'leadership-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/leadership-management">
                      <i class="bi-stars me-2"></i>Leadership
                    </a>
                  <?php endif; ?>
                  <?php if ($isSuperAdmin || hasPermission($userPermissions, 'messages.view')): ?>
                    <a class="nav-link <?= $currentPage == 'messages-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/messages-management">
                      <i class="bi-chat-dots me-2"></i>Messages
                      <span class="badge bg-primary rounded-pill ms-auto" id="unreadMessagesCount" style="display:none;">0</span>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- ========== ADMINISTRATION ========== -->
            <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['users.view','roles.view','settings.view'])): ?>
              <span class="dropdown-header mt-3">Administration</span>
              <small class="bi-three-dots nav-subtitle-replacer"></small>

              <?php if ($isSuperAdmin || hasAnyPermission($userPermissions, ['users.view','users.create'])): ?>
                <div class="nav-item">
                  <a class="nav-link dropdown-toggle <?= sidebarActive($currentPage, ['users-management.php','users-add-user.php','roles-permissions-management.php']) ? '' : 'collapsed' ?>"
                     href="#usersMenu" data-bs-toggle="collapse" data-bs-target="#usersMenu"
                     aria-expanded="<?= sidebarActive($currentPage, ['users-management.php','users-add-user.php','roles-permissions-management.php']) ? 'true' : 'false' ?>">
                    <i class="bi-people nav-icon"></i>
                    <span class="nav-link-title">Users & Roles</span>
                  </a>
                  <div id="usersMenu" class="nav-collapse collapse <?= sidebarActive($currentPage, ['users-management.php','users-add-user.php','roles-permissions-management.php']) ? 'show' : '' ?>">
                    <a class="nav-link <?= $currentPage == 'users-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/users-management">
                      <i class="bi-people me-2"></i>All Users
                    </a>
                    <a class="nav-link <?= $currentPage == 'users-add-user.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/users-add-user">
                      <i class="bi-person-plus me-2"></i>Add User
                    </a>
                    <?php if ($isSuperAdmin || hasPermission($userPermissions, 'roles.view')): ?>
                      <a class="nav-link <?= $currentPage == 'roles-permissions-management.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/roles-permissions-management">
                        <i class="bi-shield me-2"></i>Roles & Permissions
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- Credentials Wallet — Super Admin only; /admin/credentials-wallet is the only valid route -->
              <?php if ($isSuperAdmin): ?>
                <div class="nav-item">
                  <a class="nav-link <?= $currentPage == 'credentials-wallet.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/credentials-wallet">
                    <i class="bi-key nav-icon"></i>
                    <span class="nav-link-title">Credentials Wallet</span>
                  </a>
                </div>
              <?php endif; ?>

              <!-- Settings — Super Admin only; /admin/settings is the only valid route -->
              <?php if ($isSuperAdmin): ?>
                <div class="nav-item">
                  <a class="nav-link <?= $currentPage == 'settings.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/settings">
                    <i class="bi-gear nav-icon"></i>
                    <span class="nav-link-title">Settings</span>
                  </a>
                </div>
              <?php endif; ?>
            <?php endif; ?>
            <br> <span style="margin-bottom:20px;"></span>

          </div><!-- #navbarVerticalMenuPagesMenu -->
        </div>
      </div><!-- .navbar-vertical-content -->

      <!-- Footer -->
      <div class="navbar-vertical-footer" style="background-color: white;">
          <ul class="navbar-vertical-footer-list">
              <li class="navbar-vertical-footer-list-item">
                  <div class="d-flex align-items-center gap-2 px-2 py-1">
                      <?php
                      $photo    = $currentUser->photo ?? null;
                      $initials = strtoupper(substr($currentUser->firstname ?? '', 0, 1) . substr($currentUser->lastname ?? '', 0, 1));
                      ?>
                      <?php if ($photo): ?>
                          <img src="<?= ROOT_PATH ?>/<?= htmlspecialchars($photo) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                      <?php else: ?>
                          <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width:32px;height:32px;font-size:12px;"><?= $initials ?></div>
                      <?php endif; ?>
                      <div class="flex-grow-1 overflow-hidden" style="min-width:0;">
                          <div class="text-truncate fw-semibold" style="font-size:12px;"><?= htmlspecialchars(($currentUser->firstname ?? '') . ' ' . ($currentUser->lastname ?? '')) ?></div>
                          <div class="text-muted text-truncate" style="font-size:11px;"><?= htmlspecialchars($currentUser->role_name ?? '') ?></div>
                      </div>
                  </div>
              </li>
              <li class="navbar-vertical-footer-list-item">
                  <div class="d-flex gap-1">
                      <a href="<?= BASE_URL ?>/admin/profile" class="btn btn-ghost-secondary btn-icon rounded-circle" data-bs-toggle="tooltip" title="My Profile">
                          <i class="bi-person-circle"></i>
                      </a>
                      <a href="<?= BASE_URL ?>/admin/profile-settings" class="btn btn-ghost-secondary btn-icon rounded-circle" data-bs-toggle="tooltip" title="Settings">
                          <i class="bi-gear"></i>
                      </a>
                      <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle" id="logoutBtn" data-bs-toggle="tooltip" title="Sign Out">
                          <i class="bi-box-arrow-right"></i>
                      </button>
                  </div>
              </li>
          </ul>
      </div>
      <!-- Footer -->

    </div><!-- .navbar-vertical-footer-offset -->
  </div><!-- .navbar-vertical-container -->
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {

  // --- Logout ---
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function () {
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
          document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT; SameSite=Strict';
          window.location.href = '<?= BASE_URL ?>/membership';
        }
      });
    });
  }

  // --- Unread Messages Badge ---
  loadUnreadMessages();
  setInterval(loadUnreadMessages, 60000);
});

async function loadUnreadMessages() {
  const badge = document.getElementById('unreadMessagesCount');
  if (!badge) return;
  try {
    const response = await fetch('<?= BASE_URL ?>/api/contact?action=getUnreadCount');
    if (!response.ok) return;
    const res = await response.json();
    if (res.success && res.count > 0) {
      badge.textContent = res.count;
      badge.style.display = 'inline-block';
    } else {
      badge.style.display = 'none';
    }
  } catch (e) {
    console.debug('Failed to fetch unread count');
  }
}
</script>