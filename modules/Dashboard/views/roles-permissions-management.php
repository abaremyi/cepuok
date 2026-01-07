<?php
// modules/Dashboard/views/roles-permissions-management.php
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . '/helpers/JWTHandler.php';

// Get token from cookie
$token = $_COOKIE['auth_token'] ?? '';
$jwtHandler = new JWTHandler();
$decoded = $token ? $jwtHandler->validateToken($token) : null;

if (!$decoded) {
    header("Location: " . url('login'));
    exit;
}

// Check permission - only super admin can manage roles
if (!$decoded->is_super_admin && !in_array('roles.view', $decoded->permissions)) {
    header("Location: " . url('admin'));
    exit;
}

// Include database connection and models
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Authentication/models/RoleModel.php";

$pdo = Database::getConnection();
$roleModel = new RoleModel($pdo);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_role') {
        try {
            $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
            $is_super_admin = isset($_POST['is_super_admin']) ? 1 : 0;
            
            // Check if role exists
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                throw new Exception('Role name already exists');
            }
            
            $roleId = $roleModel->createRole([
                'name' => $name,
                'description' => $description,
                'is_super_admin' => $is_super_admin
            ]);
            
            if ($roleId) {
                session_start();
                $_SESSION['success_message'] = 'Role added successfully!';
            } else {
                throw new Exception('Failed to create role');
            }
            
            header("Location: " . url('admin/roles-permissions'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to add role: ' . $e->getMessage();
            header("Location: " . url('admin/roles-permissions'));
            exit;
        }
    }
    
    if ($action === 'update_role') {
        try {
            $id = intval($_POST['id']);
            $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
            $is_super_admin = isset($_POST['is_super_admin']) ? 1 : 0;
            
            // Check if role name exists for other roles
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ? AND id != ?");
            $stmt->execute([$name, $id]);
            if ($stmt->fetch()) {
                throw new Exception('Role name already exists');
            }
            
            $success = $roleModel->updateRole($id, [
                'name' => $name,
                'description' => $description,
                'is_super_admin' => $is_super_admin
            ]);
            
            if ($success) {
                session_start();
                $_SESSION['success_message'] = 'Role updated successfully!';
            } else {
                throw new Exception('Failed to update role');
            }
            
            header("Location: " . url('admin/roles-permissions'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update role: ' . $e->getMessage();
            header("Location: " . url('admin/roles-permissions'));
            exit;
        }
    }
    
    if ($action === 'delete_role') {
        try {
            $id = intval($_POST['id']);
            
            if ($id == 1) {
                throw new Exception('Cannot delete super admin role');
            }
            
            $success = $roleModel->deleteRole($id);
            
            if ($success) {
                session_start();
                $_SESSION['success_message'] = 'Role deleted successfully!';
            } else {
                throw new Exception('Failed to delete role');
            }
            
            header("Location: " . url('admin/roles-permissions'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to delete role: ' . $e->getMessage();
            header("Location: " . url('admin/roles-permissions'));
            exit;
        }
    }
    
    if ($action === 'update_permissions') {
        try {
            $role_id = intval($_POST['role_id']);
            $permissions = $_POST['permissions'] ?? [];
            
            $success = $roleModel->updateRolePermissions($role_id, $permissions);
            
            if ($success) {
                session_start();
                $_SESSION['success_message'] = 'Permissions updated successfully!';
            } else {
                throw new Exception('Failed to update permissions');
            }
            
            header("Location: " . url('admin/roles-permissions'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update permissions: ' . $e->getMessage();
            header("Location: " . url('admin/roles-permissions'));
            exit;
        }
    }
}

// Get all roles
$roles = $roleModel->getAllRoles();

// Get all permissions grouped by module
$permissionsGrouped = $roleModel->getAllPermissionsGrouped();

// Check for success/error messages from session
session_start();
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Clear messages from session
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles & Permissions Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .role-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
            background: white;
        }
        .role-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .role-card.super-admin {
            border-left: 4px solid #f59e0b;
            background: linear-gradient(135deg, #fffbf0, #fff9e6);
        }
        .role-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .permission-module {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .permission-module-header {
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .permission-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 5px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .permission-item:hover {
            background: #f0f9ff;
        }
        .permission-checkbox {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            cursor: pointer;
        }
        .permission-label {
            flex-grow: 1;
        }
        .permission-action {
            color: #6b7280;
            font-size: 0.9rem;
            background: #e5e7eb;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .user-count-badge {
            background: #10b981;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .select-all-permissions {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Include admin sidebar -->
    <?php include_once 'components/admin-sidebar.php'; ?>
    
    <!-- Include admin navbar -->
    <?php include_once 'components/admin-navbar.php'; ?>

    <!-- Page Content -->
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Roles & Permissions Management</h2>
                        <p class="text-muted mb-0">Manage system roles and assign permissions</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                        <i class="fas fa-plus me-2"></i> Add New Role
                    </button>
                </div>
            </div>
        </div>

        <!-- Roles Grid -->
        <div class="row">
            <?php if (empty($roles)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
                            <h4>No roles found</h4>
                            <p class="text-muted">Click the "Add New Role" button to add your first role.</p>
                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                <i class="fas fa-plus me-2"></i> Add First Role
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($roles as $role): ?>
                    <?php
                    $userCount = $roleModel->getUserCountByRole($role['id']);
                    $rolePermissions = $roleModel->getRolePermissions($role['id']);
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="role-card <?= $role['is_super_admin'] ? 'super-admin' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="role-icon" style="background: <?= $role['is_super_admin'] ? 'linear-gradient(135deg, #f59e0b, #fbbf24)' : 'linear-gradient(135deg, #667eea, #764ba2)' ?>; color: white;">
                                        <i class="fas <?= $role['is_super_admin'] ? 'fa-crown' : 'fa-user-tag' ?>"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-0"><?= htmlspecialchars($role['name']) ?></h5>
                                        <span class="user-count-badge"><?= $userCount ?> user(s)</span>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#permissionsModal"
                                                    onclick="loadPermissions(<?= $role['id'] ?>, '<?= htmlspecialchars(addslashes($role['name'])) ?>', <?= json_encode($rolePermissions) ?>)">
                                                <i class="fas fa-shield-alt me-2 text-primary"></i> Manage Permissions
                                            </button>
                                        </li>
                                        <?php if ($role['id'] != 1 && $decoded->is_super_admin): ?>
                                        <li>
                                            <button class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editRoleModal"
                                                    data-role-id="<?= $role['id'] ?>"
                                                    data-name="<?= htmlspecialchars($role['name']) ?>"
                                                    data-description="<?= htmlspecialchars($role['description']) ?>"
                                                    data-is-super-admin="<?= $role['is_super_admin'] ?>">
                                                <i class="fas fa-edit me-2 text-warning"></i> Edit Role
                                            </button>
                                        </li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <?php if ($role['id'] != 1 && $userCount == 0 && $decoded->is_super_admin): ?>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="confirmDeleteRole(<?= $role['id'] ?>, '<?= htmlspecialchars(addslashes($role['name'])) ?>')">
                                                <i class="fas fa-trash me-2"></i> Delete Role
                                            </button>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <p class="text-muted mb-3"><?= htmlspecialchars($role['description']) ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?= count($rolePermissions) ?> permission(s) assigned
                                </small>
                                <?php if ($role['is_super_admin']): ?>
                                    <span class="badge bg-warning">Super Admin</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addRoleForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_role">
                        
                        <div class="mb-3">
                            <label class="form-label">Role Name *</label>
                            <input type="text" class="form-control" name="name" required maxlength="50" placeholder="e.g., Moderator, Content Manager">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" maxlength="255" placeholder="Describe the role's purpose and responsibilities"></textarea>
                        </div>
                        
                        <?php if ($decoded->is_super_admin): ?>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_super_admin" id="addIsSuperAdmin" value="1">
                            <label class="form-check-label" for="addIsSuperAdmin">
                                Super Admin Role (Full system access)
                            </label>
                            <small class="text-muted d-block">Warning: Super admin roles have all permissions automatically</small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editRoleForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="id" id="editRoleId">
                        
                        <div class="mb-3">
                            <label class="form-label">Role Name *</label>
                            <input type="text" class="form-control" name="name" id="editRoleName" required maxlength="50">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editRoleDescription" rows="3" maxlength="255"></textarea>
                        </div>
                        
                        <?php if ($decoded->is_super_admin): ?>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_super_admin" id="editIsSuperAdmin" value="1">
                            <label class="form-check-label" for="editIsSuperAdmin">
                                Super Admin Role (Full system access)
                            </label>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Permissions Modal -->
    <div class="modal fade" id="permissionsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-shield-alt me-2"></i>Manage Permissions: <span id="permissionsRoleName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="permissionsForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_permissions">
                        <input type="hidden" name="role_id" id="permissionsRoleId">
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Super admin roles automatically have all permissions. For regular roles, select the specific permissions needed.
                        </div>
                        
                        <div class="select-all-permissions">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="selectAllPermissions">
                                <label class="form-check-label fw-bold" for="selectAllPermissions">
                                    Select All Permissions
                                </label>
                            </div>
                        </div>
                        
                        <div id="permissionsContent">
                            <?php foreach ($permissionsGrouped as $module => $permissions): ?>
                                <div class="permission-module">
                                    <div class="permission-module-header">
                                        <i class="fas fa-folder me-2 text-primary"></i>
                                        <?= ucfirst(str_replace('_', ' ', $module)) ?>
                                    </div>
                                    <?php foreach ($permissions as $permission): ?>
                                        <div class="permission-item">
                                            <input type="checkbox" 
                                                   class="permission-checkbox" 
                                                   name="permissions[]" 
                                                   value="<?= $permission['id'] ?>"
                                                   id="perm_<?= $permission['id'] ?>"
                                                   data-module="<?= $module ?>">
                                            <label class="permission-label" for="perm_<?= $permission['id'] ?>">
                                                <?= htmlspecialchars($permission['name']) ?>
                                                <?php if ($permission['description']): ?>
                                                    <small class="text-muted d-block"><?= htmlspecialchars($permission['description']) ?></small>
                                                <?php endif; ?>
                                            </label>
                                            <span class="permission-action">
                                                <?= $permission['action'] ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Permissions</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Forms for actions -->
    <form id="deleteRoleForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_role">
        <input type="hidden" name="id" id="deleteRoleId">
    </form>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include_once 'components/admin-scripts.php'; ?>
    
    <script>
        $(document).ready(function() {
            <?php if ($success_message): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?= addslashes($success_message) ?>',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?= addslashes($error_message) ?>',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true
                });
            <?php endif; ?>
            
            // Edit modal data
            $('#editRoleModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#editRoleId').val(button.data('role-id'));
                modal.find('#editRoleName').val(button.data('name'));
                modal.find('#editRoleDescription').val(button.data('description'));
                modal.find('#editIsSuperAdmin').prop('checked', button.data('is-super-admin') == 1);
            });
            
            // Form validation
            $('#addRoleForm, #editRoleForm').submit(function(e) {
                const name = $(this).find('input[name="name"]').val().trim();
                if (!name) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Role Name Required',
                        text: 'Please enter a role name'
                    });
                    return false;
                }
                
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            });
            
            // Select all permissions
            $('#selectAllPermissions').change(function() {
                const isChecked = $(this).prop('checked');
                $('.permission-checkbox').prop('checked', isChecked);
            });
            
            // Module-wise selection
            $('.permission-checkbox').change(function() {
                const allChecked = $('.permission-checkbox:not(:checked)').length === 0;
                $('#selectAllPermissions').prop('checked', allChecked);
            });
        });
        
        function loadPermissions(roleId, roleName, rolePermissions) {
            $('#permissionsRoleId').val(roleId);
            $('#permissionsRoleName').text(roleName);
            
            // Uncheck all permissions first
            $('.permission-checkbox').prop('checked', false);
            
            // Check permissions assigned to this role
            if (rolePermissions && rolePermissions.length > 0) {
                rolePermissions.forEach(function(permId) {
                    $(`#perm_${permId}`).prop('checked', true);
                });
            }
            
            // Update select all checkbox
            const allChecked = $('.permission-checkbox:not(:checked)').length === 0;
            $('#selectAllPermissions').prop('checked', allChecked);
            
            // Show modal
            $('#permissionsModal').modal('show');
        }
        
        function confirmDeleteRole(roleId, roleName) {
            Swal.fire({
                title: 'Delete Role?',
                html: `Are you sure you want to delete the role <strong>${roleName}</strong>?<br>
                      <small class="text-danger">This action cannot be undone. Make sure no users are assigned to this role.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#deleteRoleId').val(roleId);
                    $('#deleteRoleForm').submit();
                }
            });
        }
        
        // Permissions form submission
        $('#permissionsForm').submit(function(e) {
            const roleId = $('#permissionsRoleId').val();
            const selectedCount = $('.permission-checkbox:checked').length;
            
            Swal.fire({
                title: 'Updating Permissions...',
                html: `Updating ${selectedCount} permission(s) for this role.<br>
                      <small>Please wait while we save the changes.</small>`,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        });
    </script>
</body>
</html>