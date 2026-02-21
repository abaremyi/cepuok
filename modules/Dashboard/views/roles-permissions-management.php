<?php
/**
 * Roles & Permissions Management Page
 * File: modules/Dashboard/views/roles-permissions-management.php
 */

// Include required files
require_once ROOT_PATH . '/helpers/AuthMiddleware.php';
require_once ROOT_PATH . '/helpers/PermissionHelper.php';
require_once ROOT_PATH . '/helpers/DateHelper.php';
require_once ROOT_PATH . '/modules/Authentication/controllers/RoleController.php';

$auth = new AuthMiddleware();
$currentUser = $auth->requireAuth(['roles.view', 'roles.assign_permissions']);

// Get user permissions
$userPermissions = $currentUser->permissions ?? [];
$pageTitle = 'Roles & Permissions';
$currentPage = 'roles-permissions-management.php';

// Initialize controller
$roleController = new RoleController();

// Get data
$roles = $roleController->index();
$permissions = $roleController->getPermissions();
$stats = $roleController->getStats();

// Check if viewing specific role permissions
$viewingRole = null;
$rolePermissions = [];
if (isset($_GET['role_id'])) {
    $viewingRole = $roleController->show($_GET['role_id']);
    if ($viewingRole) {
        $rolePermissions = $roleController->getRolePermissions($_GET['role_id']);
    }
}

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
                    <?php if ($viewingRole): ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-no-gutter">
                                <li class="breadcrumb-item">
                                    <a class="breadcrumb-link" href="<?= BASE_URL ?>/admin/roles-permissions-management">
                                        Roles & Permissions
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Role: <?= htmlspecialchars($viewingRole['name']) ?>
                                </li>
                            </ol>
                        </nav>
                    <?php endif; ?>
                    
                    <h1 class="page-header-title">
                        <i class="bi-shield-check me-2"></i>
                        <?= $viewingRole ? 'Role: ' . htmlspecialchars($viewingRole['name']) : 'Roles & Permissions' ?>
                    </h1>
                    
                    <!-- Stats -->
                    <?php if (!$viewingRole): ?>
                        <div class="mt-2">
                            <span class="badge bg-soft-secondary text-secondary me-2">
                                <i class="bi-shield me-1"></i> Total Roles: <?= $stats['total_roles'] ?? 0 ?>
                            </span>
                            <span class="badge bg-soft-primary text-primary me-2">
                                <i class="bi-key me-1"></i> Permissions: <?= $stats['total_permissions'] ?? 0 ?>
                            </span>
                            <span class="badge bg-soft-info text-info">
                                <i class="bi-link me-1"></i> Assignments: <?= $stats['total_assignments'] ?? 0 ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!$viewingRole): ?>
                    <div class="col-sm-auto">
                        <?php if (hasPermission($userPermissions, 'roles.create')): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                                <i class="bi-plus me-1"></i> New Role
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="col-sm-auto">
                        <a href="<?= BASE_URL ?>/admin/roles-permissions-management" class="btn btn-dark">
                            <i class="bi-arrow-left me-1"></i> Back to Roles
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- End Page Header -->

        <?php if (!$viewingRole): ?>
            <!-- Roles List -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-header-title">Roles</h4>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Role</th>
                                <th>Users</th>
                                <th>Permissions</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <h5 class="mb-0">
                                                <?= htmlspecialchars($role['name']) ?>
                                                <?php if ($role['is_super_admin']): ?>
                                                    <span class="badge bg-soft-primary text-primary ms-1">
                                                        <i class="bi-shield-check"></i> Super Admin
                                                    </span>
                                                <?php endif; ?>
                                            </h5>
                                            <?php if ($role['description']): ?>
                                                <small class="text-muted"><?= htmlspecialchars($role['description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-secondary text-secondary">
                                            <?= $role['user_count'] ?? 0 ?> users
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-info text-info">
                                            <?= $role['permission_count'] ?? 0 ?> permissions
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-success text-success">
                                            <span class="legend-indicator bg-success"></span>Active
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a class="btn btn-white btn-sm" 
                                               href="<?= BASE_URL ?>/admin/roles-permissions-management?role_id=<?= $role['id'] ?>" 
                                               data-bs-toggle="tooltip" title="Manage Permissions">
                                                <i class="bi-shield"></i> Permissions
                                            </a>
                                            
                                            <?php if (!$role['is_super_admin'] && hasPermission($userPermissions, 'roles.edit')): ?>
                                                <button class="btn btn-white btn-sm" 
                                                        onclick="editRole(<?= $role['id'] ?>, '<?= htmlspecialchars($role['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($role['description'] ?? '', ENT_QUOTES) ?>')" 
                                                        data-bs-toggle="tooltip" title="Edit Role">
                                                    <i class="bi-pencil"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if (!$role['is_super_admin'] && hasPermission($userPermissions, 'roles.delete')): ?>
                                                <button class="btn btn-white btn-sm text-danger" 
                                                        onclick="deleteRole(<?= $role['id'] ?>)" 
                                                        data-bs-toggle="tooltip" title="Delete Role">
                                                    <i class="bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- End Roles List -->
            
        <?php else: ?>
            <!-- Permissions Management -->
            <form id="permissionsForm" method="POST">
                <input type="hidden" name="role_id" value="<?= $viewingRole['id'] ?>">
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-header-title mb-0">
                            Manage Permissions for <span class="text-primary"><?= htmlspecialchars($viewingRole['name']) ?></span>
                        </h4>
                        <div>
                            <button type="button" class="btn btn-sm btn-ghost-secondary me-2" onclick="toggleAllPermissions()">
                                <i class="bi-check-all me-1"></i> Toggle All
                            </button>
                            <button type="button" class="btn btn-sm btn-ghost-secondary" onclick="expandAllModules()">
                                <i class="bi-arrows-expand me-1"></i> Expand All
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <?php foreach ($permissions as $module => $modulePermissions): ?>
                            <div class="mb-4 pb-4 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="bi-folder me-2 text-primary"></i>
                                        <?= ucfirst(str_replace('_', ' ', $module)) ?>
                                        <span class="badge bg-soft-secondary text-secondary ms-2">
                                            <?= count($modulePermissions) ?> permissions
                                        </span>
                                    </h5>
                                    <button type="button" class="btn btn-xs btn-ghost-secondary" 
                                            onclick="toggleModulePermissions('<?= $module ?>')">
                                        <i class="bi-check2-square me-1"></i> Select All
                                    </button>
                                </div>

                                <div class="row g-3">
                                    <?php foreach ($modulePermissions as $permission): ?>
                                        <div class="col-sm-6 col-md-4 col-lg-3">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox module-<?= $module ?>" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="<?= $permission['id'] ?>" 
                                                       id="perm_<?= $permission['id'] ?>"
                                                       <?= in_array($permission['id'], $rolePermissions) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="perm_<?= $permission['id'] ?>">
                                                    <strong><?= ucfirst($permission['action']) ?></strong>
                                                    <?php if ($permission['description']): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($permission['description']) ?></small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted" id="selectedCount">0 permissions selected</span>
                        </div>
                        <div>
                            <a href="<?= BASE_URL ?>/admin/roles-permissions-management" class="btn btn-white me-2">
                                <i class="bi-x me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi-check-circle me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <!-- End Permissions Management -->
        <?php endif; ?>
    </div>

    <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRoleModalLabel">Create New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createRoleForm">
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="roleName" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" name="name" required 
                               placeholder="e.g., Editor, Manager, Moderator">
                    </div>

                    <div class="mb-4">
                        <label for="roleDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="roleDescription" name="description" rows="3" 
                                  placeholder="Brief description of this role's responsibilities..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi-check-circle me-1"></i> Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRoleForm">
                <input type="hidden" id="editRoleId" name="id">
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="editRoleName" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editRoleName" name="name" required>
                    </div>

                    <div class="mb-4">
                        <label for="editRoleDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editRoleDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi-check-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    <?php if ($viewingRole): ?>
    // Update selected count on load and change
    updateSelectedCount();
    $('.permission-checkbox').change(updateSelectedCount);
    <?php endif; ?>
});

// Create Role
$('#createRoleForm').submit(function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    $.ajax({
        url: '<?= BASE_URL ?>/api/roles?action=create',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error!', 'Failed to create role', 'error');
        }
    });
});

// Edit Role
function editRole(id, name, description) {
    $('#editRoleId').val(id);
    $('#editRoleName').val(name);
    $('#editRoleDescription').val(description);
    $('#editRoleModal').modal('show');
}

$('#editRoleForm').submit(function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = $('#editRoleId').val();

    $.ajax({
        url: `<?= BASE_URL ?>/api/roles?action=update&id=${id}`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error!', 'Failed to update role', 'error');
        }
    });
});

// Delete Role
function deleteRole(id) {
    Swal.fire({
        title: 'Delete Role?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `<?= BASE_URL ?>/api/roles?action=delete&id=${id}`,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to delete role', 'error');
                }
            });
        }
    });
}

<?php if ($viewingRole): ?>
// Permissions Management
$('#permissionsForm').submit(function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const roleId = '<?= $viewingRole['id'] ?>';

    $.ajax({
        url: `<?= BASE_URL ?>/api/roles?action=update-permissions&role_id=${roleId}`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error!', 'Failed to update permissions', 'error');
        }
    });
});

// Toggle all permissions
function toggleAllPermissions() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    updateSelectedCount();
}

// Toggle module permissions
function toggleModulePermissions(module) {
    const checkboxes = document.querySelectorAll('.module-' + module);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    updateSelectedCount();
}

// Expand all modules (if using accordion)
function expandAllModules() {
    // Implementation depends on your UI
}

// Update selected count
function updateSelectedCount() {
    const checked = document.querySelectorAll('.permission-checkbox:checked').length;
    $('#selectedCount').text(checked + ' permissions selected');
}
<?php endif; ?>
</script>

</body>
</html>