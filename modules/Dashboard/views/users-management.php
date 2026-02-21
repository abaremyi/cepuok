<?php
/**
 * Users Management Page
 * File: modules/Dashboard/views/users-management.php
 */

// Include required files
require_once ROOT_PATH . '/helpers/AuthMiddleware.php';
require_once ROOT_PATH . '/helpers/PermissionHelper.php';
require_once ROOT_PATH . '/helpers/DateHelper.php';
require_once ROOT_PATH . '/modules/Authentication/controllers/UserController.php';

$auth = new AuthMiddleware();
$currentUser = $auth->requireAuth(['users.view']);

// Get user permissions
$userPermissions = $currentUser->permissions ?? [];
$pageTitle = 'Users Management';
$currentPage = 'users-management.php';

// Initialize controller
$userController = new UserController();

// Get filter parameters
$filters = [];
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (isset($_GET['role_id']) && !empty($_GET['role_id'])) {
    $filters['role_id'] = $_GET['role_id'];
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

// Get users
$users = $userController->index($filters);
$stats = $userController->getStats();
$roles = $userController->getRoles();
$availableMembers = $userController->getAvailableMembers();

include LAYOUTS_PATH . '/admin-header.php';
?>

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
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="bi-people me-2"></i>
                        Users Management
                    </h1>
                    
                    <!-- Stats -->
                    <div class="mt-2">
                        <span class="badge bg-soft-secondary text-secondary me-2">
                            <i class="bi-people me-1"></i> Total: <?= $stats['total_users'] ?? 0 ?>
                        </span>
                        <span class="badge bg-soft-success text-success me-2">
                            <i class="bi-check-circle me-1"></i> Active: <?= $stats['active_users'] ?? 0 ?>
                        </span>
                        <span class="badge bg-soft-warning text-warning me-2">
                            <i class="bi-clock me-1"></i> Pending: <?= $stats['pending_users'] ?? 0 ?>
                        </span>
                        <span class="badge bg-soft-info text-info">
                            <i class="bi-graph-up me-1"></i> New (30d): <?= $stats['new_users_30_days'] ?? 0 ?>
                        </span>
                    </div>
                </div>
                
                <div class="col-sm-auto">
                    <?php if (hasPermission($userPermissions, 'users.create')): ?>
                        <a href="<?= BASE_URL ?>/admin/users-add-user" class="btn btn-primary">
                            <i class="bi-plus me-1"></i> Add New User
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Filters Card -->
        <div class="card mb-3 mb-lg-5">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Name or email..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    
                    <div class="col-sm-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?= (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="inactive" <?= (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            <option value="suspended" <?= (isset($_GET['status']) && $_GET['status'] == 'suspended') ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>
                    
                    <div class="col-sm-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role_id">
                            <option value="">All Roles</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= (isset($_GET['role_id']) && $_GET['role_id'] == $role['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['name']) ?>
                                    <?= $role['is_super_admin'] ? ' (Super Admin)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-sm-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-white w-100">
                            <i class="bi-filter me-1"></i> Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- End Filters Card -->

        <!-- Users Table Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-header-title">All Users</h4>
                
                <div>
                    <span class="text-muted me-2" id="selectedCount">0 selected</span>
                    <?php if (hasPermission($userPermissions, 'users.delete')): ?>
                        <button class="btn btn-white btn-sm" id="bulkDeleteBtn" disabled onclick="bulkDelete()">
                            <i class="bi-trash text-danger"></i> Delete Selected
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="table-check">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="checkAll">
                                    <label class="form-check-label" for="checkAll"></label>
                                </div>
                            </th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Member #</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <img class="avatar avatar-xl mb-3" src="<?= img_url('icons/empty.svg') ?>" alt="No users">
                                    <h5 class="mb-2">No users found</h5>
                                    <p class="text-muted">Try adjusting your filters or create a new user.</p>
                                    <?php if (hasPermission($userPermissions, 'users.create')): ?>
                                        <a href="<?= BASE_URL ?>/admin/users-add-user" class="btn btn-primary">
                                            <i class="bi-plus me-1"></i> Add New User
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="table-check">
                                        <?php if (!$user['is_super_admin']): ?>
                                            <div class="form-check">
                                                <input class="form-check-input user-checkbox" type="checkbox" 
                                                       value="<?= $user['id'] ?>" id="userCheck<?= $user['id'] ?>">
                                                <label class="form-check-label" for="userCheck<?= $user['id'] ?>"></label>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a class="d-flex align-items-center" href="<?= BASE_URL ?>/admin/users-view?id=<?= $user['id'] ?>">
                                            <div class="flex-shrink-0">
                                                <?php if (!empty($user['photo'])): ?>
                                                    <div class="avatar avatar-sm avatar-circle">
                                                        <img class="avatar-img" src="<?= BASE_URL ?>/uploads/<?= $user['photo'] ?>" 
                                                             alt="<?= htmlspecialchars($user['firstname']) ?>">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                                        <span class="avatar-initials">
                                                            <?= strtoupper(substr($user['firstname'] ?? 'U', 0, 1) . substr($user['lastname'] ?? 'U', 0, 1)) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="text-inherit mb-0">
                                                    <?= htmlspecialchars($user['full_name'] ?? $user['firstname'] . ' ' . $user['lastname']) ?>
                                                    <?php if ($user['is_super_admin']): ?>
                                                        <span class="badge bg-soft-primary text-primary ms-1">
                                                            <i class="bi-shield-check"></i> Super Admin
                                                        </span>
                                                    <?php endif; ?>
                                                </h5>
                                                <span class="text-muted small"><?= htmlspecialchars($user['email']) ?></span>
                                                <?php if (!empty($user['phone'])): ?>
                                                    <br><span class="text-muted small"><?= htmlspecialchars($user['phone']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-dark text-dark">
                                            <?= htmlspecialchars($user['role_name'] ?? 'No Role') ?>
                                        </span>
                                        <?php if ($user['active_sessions'] > 0): ?>
                                            <span class="badge bg-soft-success text-success ms-1" 
                                                  title="<?= $user['active_sessions'] ?> active session(s)">
                                                <i class="bi-circle-fill" style="font-size: 6px;"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'active' => 'bg-soft-success text-success',
                                            'pending' => 'bg-soft-warning text-warning',
                                            'inactive' => 'bg-soft-secondary text-secondary',
                                            'suspended' => 'bg-soft-danger text-danger'
                                        ];
                                        $statusIcons = [
                                            'active' => 'bi-check-circle',
                                            'pending' => 'bi-clock',
                                            'inactive' => 'bi-pause-circle',
                                            'suspended' => 'bi-exclamation-circle'
                                        ];
                                        $class = $statusClasses[$user['status']] ?? 'bg-soft-secondary text-secondary';
                                        $icon = $statusIcons[$user['status']] ?? 'bi-question-circle';
                                        ?>
                                        <span class="badge <?= $class ?>">
                                            <i class="<?= $icon ?> me-1"></i>
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['last_login']): ?>
                                            <span title="<?= date('Y-m-d H:i:s', strtotime($user['last_login'])) ?>">
                                                <?= time_elapsed_string($user['last_login']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['membership_number']): ?>
                                            <span class="badge bg-soft-info text-info">
                                                <?= htmlspecialchars($user['membership_number']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <?php if (hasPermission($userPermissions, 'users.edit')): ?>
                                                <a class="btn btn-white btn-sm" 
                                                   href="<?= BASE_URL ?>/admin/users-add-user?id=<?= $user['id'] ?>" 
                                                   data-bs-toggle="tooltip" title="Edit User">
                                                    <i class="bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (hasPermission($userPermissions, 'users.change_role')): ?>
                                                <button class="btn btn-white btn-sm" 
                                                        onclick="changeRole(<?= $user['id'] ?>, '<?= $user['role_name'] ?>')"
                                                        data-bs-toggle="tooltip" title="Change Role">
                                                    <i class="bi-shield"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if (!$user['is_super_admin'] && hasPermission($userPermissions, 'users.edit')): ?>
                                                <?php if ($user['status'] === 'active'): ?>
                                                    <button class="btn btn-white btn-sm text-warning" 
                                                            onclick="deactivateUser(<?= $user['id'] ?>)"
                                                            data-bs-toggle="tooltip" title="Deactivate">
                                                        <i class="bi-pause-circle"></i>
                                                    </button>
                                                <?php elseif ($user['status'] === 'inactive' || $user['status'] === 'suspended'): ?>
                                                    <button class="btn btn-white btn-sm text-success" 
                                                            onclick="activateUser(<?= $user['id'] ?>)"
                                                            data-bs-toggle="tooltip" title="Activate">
                                                        <i class="bi-play-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php if (!$user['is_super_admin'] && hasPermission($userPermissions, 'users.delete')): ?>
                                                <button class="btn btn-white btn-sm text-danger" 
                                                        onclick="deleteUser(<?= $user['id'] ?>)"
                                                        data-bs-toggle="tooltip" title="Delete">
                                                    <i class="bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($users)): ?>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-sm text-muted">
                            Showing <?= count($users) ?> of <?= $stats['total_users'] ?? 0 ?> users
                        </div>
                        <div class="col-sm-auto">
                            <!-- Pagination would go here -->
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!-- End Users Table Card -->
    </div>

    <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- Change Role Modal -->
<div class="modal fade" id="changeRoleModal" tabindex="-1" aria-labelledby="changeRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeRoleModalLabel">Change User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changeRoleForm">
                <input type="hidden" id="changeRoleUserId" name="user_id">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Select New Role</label>
                        <select class="form-select" id="newRoleId" name="role_id" required>
                            <option value="">Choose a role...</option>
                            <?php foreach ($roles as $role): ?>
                                <?php if (!$role['is_super_admin'] || ($role['is_super_admin'] && $currentUser->is_super_admin)): ?>
                                    <option value="<?= $role['id'] ?>">
                                        <?= htmlspecialchars($role['name']) ?>
                                        <?= $role['is_super_admin'] ? '(Super Admin)' : '' ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Check all functionality
    $('#checkAll').change(function() {
        $('.user-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkDeleteButton();
    });
    
    $('.user-checkbox').change(function() {
        updateBulkDeleteButton();
        $('#checkAll').prop('checked', $('.user-checkbox:checked').length === $('.user-checkbox').length);
    });
    
    function updateBulkDeleteButton() {
        var selected = $('.user-checkbox:checked').length;
        $('#selectedCount').text(selected + ' selected');
        $('#bulkDeleteBtn').prop('disabled', selected === 0);
    }
});

// Change Role
function changeRole(userId, currentRole) {
    $('#changeRoleUserId').val(userId);
    $('#changeRoleModal').modal('show');
}

$('#changeRoleForm').submit(function(e) {
    e.preventDefault();
    var userId = $('#changeRoleUserId').val();
    var roleId = $('#newRoleId').val();
    
    Swal.fire({
        title: 'Change Role',
        text: 'Are you sure you want to change this user\'s role?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#377dff',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= BASE_URL ?>/api/users?action=change-role',
                type: 'POST',
                data: {
                    user_id: userId,
                    role_id: roleId
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', 'Role updated successfully', 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to update role', 'error');
                }
            });
        }
    });
});

// Activate User
function activateUser(userId) {
    Swal.fire({
        title: 'Activate User',
        text: 'Are you sure you want to activate this user?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#00c9a7',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, activate!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= BASE_URL ?>/api/users?action=update-status',
                type: 'POST',
                data: {
                    user_id: userId,
                    status: 'active'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', 'User activated successfully', 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to activate user', 'error');
                }
            });
        }
    });
}

// Deactivate User
function deactivateUser(userId) {
    Swal.fire({
        title: 'Deactivate User',
        text: 'Are you sure you want to deactivate this user?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, deactivate!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= BASE_URL ?>/api/users?action=update-status',
                type: 'POST',
                data: {
                    user_id: userId,
                    status: 'inactive'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', 'User deactivated successfully', 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to deactivate user', 'error');
                }
            });
        }
    });
}

// Delete User
function deleteUser(userId) {
    Swal.fire({
        title: 'Delete User?',
        text: "This action cannot be undone!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= BASE_URL ?>/api/users?action=delete',
                type: 'DELETE',
                data: { user_id: userId },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'User has been deleted.', 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to delete user', 'error');
                }
            });
        }
    });
}

// Bulk Delete
function bulkDelete() {
    var selected = [];
    $('.user-checkbox:checked').each(function() {
        selected.push($(this).val());
    });
    
    if (selected.length === 0) return;
    
    Swal.fire({
        title: 'Delete Selected Users?',
        text: 'You are about to delete ' + selected.length + ' user(s). This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete them!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= BASE_URL ?>/api/users?action=bulk-delete',
                type: 'POST',
                data: { user_ids: selected },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'Selected users have been deleted.', 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to delete users', 'error');
                }
            });
        }
    });
}

// Helper function for time ago
function time_elapsed_string($datetime) {
    // PHP function - keep as is
    <?php
    if (!function_exists('time_elapsed_string')) {
        function time_elapsed_string($datetime, $full = false) {
            $now = new DateTime;
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);
            
            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;
            
            $string = array(
                'y' => 'year',
                'm' => 'month',
                'w' => 'week',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            );
            
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }
            
            if (!$full) $string = array_slice($string, 0, 1);
            return $string ? implode(', ', $string) . ' ago' : 'just now';
        }
    }
    ?>
}
</script>

</body>
</html>