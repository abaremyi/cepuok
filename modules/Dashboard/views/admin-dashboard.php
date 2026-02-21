<?php
/**
 * Admin Dashboard
 * File: modules/Dashboard/views/admin-dashboard.php
 */

// Include required files
require_once ROOT_PATH . '/helpers/AuthMiddleware.php';
require_once ROOT_PATH . '/helpers/PermissionHelper.php';

$auth = new AuthMiddleware();
$currentUser = $auth->requireAuth(['dashboard.view']);

// Get user permissions
$userPermissions = $currentUser->permissions ?? [];
$pageTitle = 'Dashboard';
$currentPage = 'admin-dashboard.php';

if (!$currentUser) {
    header('Location: ' . url('logout'));
    exit;
}

?>

<?php include LAYOUTS_PATH . '/admin-header.php'; ?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">

  <script src="<?= admin_js_url('hs.theme-appearance.js') ?>"></script>
  <script src="<?= ROOT_PATH ?>/dashboard-assets/vendor/hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js"></script>
  
  <?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
  <?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<main id="content" role="main" class="main">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">Dashboard</h1>
                </div>
                <div class="col-auto">
                    <span class="text-muted">Welcome back, <?= htmlspecialchars($currentUser->firstname ?? 'User') ?>!</span>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row" id="statsContainer">
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle">Total Users</h6>
                        <div class="row align-items-center gx-2 mb-1">
                            <div class="col-6">
                                <h2 class="card-title text-inherit" id="totalUsers">0</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle">Total Members</h6>
                        <div class="row align-items-center gx-2 mb-1">
                            <div class="col-6">
                                <h2 class="card-title text-inherit" id="totalMembers">0</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle">Pending Members</h6>
                        <div class="row align-items-center gx-2 mb-1">
                            <div class="col-6">
                                <h2 class="card-title text-inherit" id="pendingMembers">0</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle">Today's Visitors</h6>
                        <div class="row align-items-center gx-2 mb-1">
                            <div class="col-6">
                                <h2 class="card-title text-inherit" id="todayVisitors">0</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users Table -->
        <div class="card mb-3 mb-lg-5">
            <div class="card-header">
                <h4 class="card-header-title">Recent Users</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody id="recentUsersTable">
                        <tr>
                            <td colspan="5" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

<script>
$(document).ready(function() {
    // Load dashboard stats
    loadDashboardStats();
    loadRecentUsers();
    
    function loadDashboardStats() {
        $.ajax({
            url: '<?= BASE_URL ?>/api/dashboard?action=getStats',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#totalUsers').text(response.data.total_users || 0);
                    $('#totalMembers').text(response.data.total_members || 0);
                    $('#pendingMembers').text(response.data.pending_members || 0);
                    $('#todayVisitors').text(response.data.today_visitors || 0);
                }
            },
            error: function() {
                $('#totalUsers, #totalMembers, #pendingMembers, #todayVisitors').text('Error');
            }
        });
    }
    
    function loadRecentUsers() {
        $.ajax({
            url: '<?= BASE_URL ?>/api/dashboard?action=getRecentUsers',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(user) {
                        const initials = (user.firstname ? user.firstname.charAt(0) : '') + 
                                       (user.lastname ? user.lastname.charAt(0) : '');
                        html += `
                            <tr>
                                <td>
                                    <a class="d-flex align-items-center" href="<?= BASE_URL ?>/admin/users-management?id=${user.id}">
                                        <div class="flex-shrink-0">
                                            ${user.photo ? 
                                                `<div class="avatar avatar-sm avatar-circle">
                                                    <img class="avatar-img" src="<?= BASE_URL ?>/uploads/${user.photo}" alt="${user.firstname}">
                                                </div>` :
                                                `<div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                                    <span class="avatar-initials">${initials || 'U'}</span>
                                                </div>`
                                            }
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="text-inherit mb-0">${user.firstname || ''} ${user.lastname || ''}</h5>
                                        </div>
                                    </a>
                                </td>
                                <td>${user.email || 'N/A'}</td>
                                <td>${user.role_name || 'N/A'}</td>
                                <td>
                                    <span class="badge ${user.status === 'active' ? 'bg-success' : user.status === 'pending' ? 'bg-warning' : 'bg-secondary'}">
                                        ${user.status || 'unknown'}
                                    </span>
                                </td>
                                <td>${user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}</td>
                            </tr>
                        `;
                    });
                    $('#recentUsersTable').html(html);
                } else {
                    $('#recentUsersTable').html('<tr><td colspan="5" class="text-center">No users found</td></tr>');
                }
            },
            error: function() {
                $('#recentUsersTable').html('<tr><td colspan="5" class="text-center">Error loading users</td></tr>');
            }
        });
    }
});
</script>
</body>
</html>