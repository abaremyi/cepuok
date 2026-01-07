<?php
// modules/Dashboard/views/users-management.php
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

// Check permission
if (!$decoded->is_super_admin && !in_array('users.view', $decoded->permissions)) {
    header("Location: " . url('admin'));
    exit;
}

// Include database connection and models
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Authentication/models/UserModel.php";
require_once $root_path . "/modules/Authentication/models/RoleModel.php";

$pdo = Database::getConnection();
$userModel = new UserModel($pdo);
$roleModel = new RoleModel($pdo);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_user') {
        try {
            $firstname = htmlspecialchars($_POST['firstname'], ENT_QUOTES, 'UTF-8');
            $lastname = htmlspecialchars($_POST['lastname'], ENT_QUOTES, 'UTF-8');
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
            $role_id = intval($_POST['role_id']);
            $status = $_POST['status'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Generate username from email
            $username = explode('@', $email)[0];
            
            // Check if user exists
            if ($userModel->userExists($email, $phone)) {
                throw new Exception('Email or phone already registered');
            }
            
            $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, phone, username, password, role_id, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$firstname, $lastname, $email, $phone, $username, $password, $role_id, $status, $decoded->user_id]);
            
            session_start();
            $_SESSION['success_message'] = 'User added successfully!';
            
            header("Location: " . url('admin/users-management'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to add user: ' . $e->getMessage();
            header("Location: " . url('admin/users-management'));
            exit;
        }
    }
    
    if ($action === 'update_user') {
        try {
            $id = intval($_POST['id']);
            $firstname = htmlspecialchars($_POST['firstname'], ENT_QUOTES, 'UTF-8');
            $lastname = htmlspecialchars($_POST['lastname'], ENT_QUOTES, 'UTF-8');
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
            $role_id = intval($_POST['role_id']);
            $status = $_POST['status'];
            
            // Check if email/phone exists for other users
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR phone = ?) AND id != ?");
            $stmt->execute([$email, $phone, $id]);
            if ($stmt->fetch()) {
                throw new Exception('Email or phone already registered to another user');
            }
            
            $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, phone = ?, role_id = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$firstname, $lastname, $email, $phone, $role_id, $status, $id]);
            
            session_start();
            $_SESSION['success_message'] = 'User updated successfully!';
            
            header("Location: " . url('admin/users-management'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update user: ' . $e->getMessage();
            header("Location: " . url('admin/users-management'));
            exit;
        }
    }
    
    if ($action === 'delete_user') {
        try {
            $id = intval($_POST['id']);
            
            // Prevent deleting super admin and self
            if ($id == 1) {
                throw new Exception('Cannot delete super admin account');
            }
            
            if ($id == $decoded->user_id) {
                throw new Exception('Cannot delete your own account');
            }
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role_id != 1");
            $stmt->execute([$id]);
            
            session_start();
            $_SESSION['success_message'] = 'User deleted successfully!';
            
            header("Location: " . url('admin/users-management'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to delete user: ' . $e->getMessage();
            header("Location: " . url('admin/users-management'));
            exit;
        }
    }
    
    if ($action === 'change_password') {
        try {
            $id = intval($_POST['id']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$password, $id]);
            
            session_start();
            $_SESSION['success_message'] = 'Password changed successfully!';
            
            header("Location: " . url('admin/users-management'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to change password: ' . $e->getMessage();
            header("Location: " . url('admin/users-management'));
            exit;
        }
    }
    
    if ($action === 'bulk_action') {
        try {
            $action_type = $_POST['bulk_action_type'] ?? '';
            $user_ids = $_POST['user_ids'] ?? [];
            
            if (empty($user_ids) || empty($action_type)) {
                throw new Exception('No users selected or no action specified');
            }
            
            $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
            
            switch ($action_type) {
                case 'activate':
                    $stmt = $pdo->prepare("UPDATE users SET status = 'active', updated_at = NOW() WHERE id IN ($placeholders)");
                    break;
                case 'deactivate':
                    $stmt = $pdo->prepare("UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id IN ($placeholders)");
                    break;
                case 'delete':
                    // Prevent deleting super admin
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders) AND role_id != 1 AND id != 1");
                    break;
                default:
                    throw new Exception('Invalid bulk action');
            }
            
            $stmt->execute($user_ids);
            
            session_start();
            $_SESSION['success_message'] = 'Bulk action completed successfully!';
            
            header("Location: " . url('admin/users-management'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to perform bulk action: ' . $e->getMessage();
            header("Location: " . url('admin/users-management'));
            exit;
        }
    }
}

// Get search parameters
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query with filters
$query = "SELECT u.*, r.name as role_name, r.is_super_admin,
                 CONCAT(creator.firstname, ' ', creator.lastname) as created_by_name
          FROM users u
          JOIN roles r ON u.role_id = r.id
          LEFT JOIN users creator ON u.created_by = creator.id
          WHERE 1=1";

$params = [];
$types = '';

if ($search) {
    $query .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}

if ($role_filter) {
    $query .= " AND u.role_id = ?";
    $params[] = $role_filter;
    $types .= 'i';
}

if ($status_filter) {
    $query .= " AND u.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$query .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $pdo->prepare($query);
if ($params) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total users for pagination
$countQuery = "SELECT COUNT(*) as total FROM users u WHERE 1=1";
$countParams = [];
$countTypes = '';

if ($search) {
    $countQuery .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $countTypes .= 'ssss';
}

if ($role_filter) {
    $countQuery .= " AND u.role_id = ?";
    $countParams[] = $role_filter;
    $countTypes .= 'i';
}

if ($status_filter) {
    $countQuery .= " AND u.status = ?";
    $countParams[] = $status_filter;
    $countTypes .= 's';
}

$countStmt = $pdo->prepare($countQuery);
if ($countParams) {
    $countStmt->execute($countParams);
} else {
    $countStmt->execute();
}
$totalUsers = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalUsers / $limit);

// Get all roles for filter
$roles = $roleModel->getAllRoles();

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
    <title>Users Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .user-avatar-lg {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .badge-role {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .last-login {
            font-size: 0.8rem;
            color: #6b7280;
        }
        .bulk-action-bar {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .action-dropdown .dropdown-menu {
            min-width: 200px;
        }
        .select-all-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .user-row.selected {
            background-color: rgba(102, 126, 234, 0.05);
        }
        .pagination-custom .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: #667eea;
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
                        <h2 class="mb-0">Users Management</h2>
                        <p class="text-muted mb-0">Manage system users, roles, and permissions</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter me-2"></i> Filter
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-user-plus me-2"></i> Add New User
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Action Bar -->
        <div class="row mb-3" id="bulkActionBar" style="display: none;">
            <div class="col-12">
                <div class="bulk-action-bar">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fas fa-users me-2 text-primary"></i>
                            <span id="selectedCount">0</span> user(s) selected
                        </div>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" style="width: auto;" id="bulkActionSelect">
                                <option value="">Select Action</option>
                                <option value="activate">Activate Selected</option>
                                <option value="deactivate">Deactivate Selected</option>
                                <?php if ($decoded->is_super_admin): ?>
                                <option value="delete">Delete Selected</option>
                                <?php endif; ?>
                            </select>
                            <button class="btn btn-sm btn-primary" onclick="performBulkAction()">
                                <i class="fas fa-play me-1"></i> Apply
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                                <i class="fas fa-times me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h4>No users found</h4>
                                <p class="text-muted">Try adjusting your filters or add a new user.</p>
                                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fas fa-user-plus me-2"></i> Add First User
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" class="select-all-checkbox" id="selectAll">
                                            </th>
                                            <th>User</th>
                                            <th>Contact</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Last Login</th>
                                            <th>Created</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr class="user-row" data-id="<?= $user['id'] ?>">
                                                <td>
                                                    <input type="checkbox" class="user-checkbox" value="<?= $user['id'] ?>"
                                                           <?= $user['id'] == 1 || $user['id'] == $decoded->user_id ? 'disabled' : '' ?>>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= img_url($user['photo']) ?>" 
                                                             class="user-avatar me-3"
                                                             alt="<?= htmlspecialchars($user['firstname']) ?>"
                                                             onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['firstname'] . '+' . $user['lastname']) ?>&background=667eea&color=fff'">
                                                        <div>
                                                            <h6 class="mb-0"><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></h6>
                                                            <small class="text-muted"><?= htmlspecialchars($user['username']) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div><i class="fas fa-envelope me-2 text-muted"></i> <?= htmlspecialchars($user['email']) ?></div>
                                                        <div class="mt-1"><i class="fas fa-phone me-2 text-muted"></i> <?= htmlspecialchars($user['phone']) ?></div>
                                                    </div>
                                                </td>
                                                <td style="min-width: 130px; text-align: center;">
                                                    <span class="badge-role"><?= htmlspecialchars($user['role_name']) ?></span>
                                                    <?php if ($user['is_super_admin']): ?>
                                                        <span class="badge bg-warning ms-1">Super Admin</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge status-badge status-<?= $user['status'] ?>">
                                                        <?= ucfirst($user['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="last-login">
                                                        <?= $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= date('M d, Y', strtotime($user['created_at'])) ?><br>
                                                        <span class="text-primary">by <?= htmlspecialchars($user['created_by_name'] ?? 'System') ?></span>
                                                    </small>
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown action-dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <button class="dropdown-item" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#viewUserModal"
                                                                        onclick="viewUser(<?= $user['id'] ?>)">
                                                                    <i class="fas fa-eye me-2 text-info"></i> View Details
                                                                </button>
                                                            </li>
                                                            <?php if ($user['id'] != 1 && ($decoded->is_super_admin || in_array('users.edit', $decoded->permissions))): ?>
                                                            <li>
                                                                <button class="dropdown-item" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#editUserModal"
                                                                        onclick="editUser(
                                                                            <?= $user['id'] ?>,
                                                                            '<?= htmlspecialchars(addslashes($user['firstname'])) ?>',
                                                                            '<?= htmlspecialchars(addslashes($user['lastname'])) ?>',
                                                                            '<?= htmlspecialchars(addslashes($user['email'])) ?>',
                                                                            '<?= htmlspecialchars(addslashes($user['phone'])) ?>',
                                                                            <?= $user['role_id'] ?>,
                                                                            '<?= $user['status'] ?>'
                                                                        )">
                                                                    <i class="fas fa-edit me-2 text-primary"></i> Edit User
                                                                </button>
                                                            </li>
                                                            <?php endif; ?>
                                                            <?php if ($user['id'] != 1 && ($decoded->is_super_admin || in_array('users.edit', $decoded->permissions))): ?>
                                                            <li>
                                                                <button class="dropdown-item" onclick="changeUserPassword(<?= $user['id'] ?>)">
                                                                    <i class="fas fa-key me-2 text-warning"></i> Change Password
                                                                </button>
                                                            </li>
                                                            <?php endif; ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <?php if ($user['id'] != 1 && $user['id'] != $decoded->user_id && ($decoded->is_super_admin || in_array('users.delete', $decoded->permissions))): ?>
                                                            <li>
                                                                <button class="dropdown-item text-danger" onclick="confirmDeleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['firstname'] . ' ' . $user['lastname'])) ?>')">
                                                                    <i class="fas fa-trash me-2"></i> Delete User
                                                                </button>
                                                            </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination pagination-custom justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= url('admin/users-management') ?>?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role_filter=<?= $role_filter ?>&status_filter=<?= $status_filter ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <?php if ($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="<?= url('admin/users-management') ?>?page=<?= $i ?>&search=<?= urlencode($search) ?>&role_filter=<?= $role_filter ?>&status_filter=<?= $status_filter ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php elseif (abs($i - $page) == 3): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= url('admin/users-management') ?>?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role_filter=<?= $role_filter ?>&status_filter=<?= $status_filter ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                            
                            <div class="text-muted text-center mt-3">
                                Showing <?= count($users) ?> of <?= $totalUsers ?> users
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-filter me-2"></i>Filter Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="GET" action="<?= url('admin/users-management') ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Search by name, email, or phone...">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select class="form-select" name="role_filter">
                                    <option value="">All Roles</option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= $role['id'] ?>" <?= $role_filter == $role['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status_filter">
                                    <option value="">All Status</option>
                                    <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="suspended" <?= $status_filter == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= url('admin/users-management') ?>" class="btn btn-secondary">Clear All</a>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addUserForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="firstname" required maxlength="100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="lastname" required maxlength="100">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="email" required maxlength="100">
                            <small class="text-muted">This will also be used as username</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" name="phone" required maxlength="20">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" required minlength="6">
                                <small class="text-muted">Min 6 characters</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" name="confirm_password" required minlength="6">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role *</label>
                                <select class="form-select" name="role_id" required>
                                    <?php foreach ($roles as $role): ?>
                                        <?php if (!$role['is_super_admin'] || $decoded->is_super_admin): ?>
                                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editUserForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="id" id="editUserId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="firstname" id="editFirstname" required maxlength="100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="lastname" id="editLastname" required maxlength="100">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="email" id="editEmail" required maxlength="100">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" name="phone" id="editPhone" required maxlength="20">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role *</label>
                                <select class="form-select" name="role_id" id="editRoleId" required>
                                    <?php foreach ($roles as $role): ?>
                                        <?php if (!$role['is_super_admin'] || $decoded->is_super_admin): ?>
                                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" id="editStatus" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="pending">Pending</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user me-2"></i>User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="userDetailsContent">
                    <!-- Content will be loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading user details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Forms for actions -->
    <form id="deleteUserForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="id" id="deleteUserId">
    </form>
    
    <form id="bulkActionForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="bulk_action">
        <input type="hidden" name="bulk_action_type" id="bulkActionType">
        <input type="hidden" name="user_ids" id="bulkUserIds">
    </form>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            
            // Form validation
            $('#addUserForm').submit(function(e) {
                const password = $(this).find('input[name="password"]').val();
                const confirmPassword = $(this).find('input[name="confirm_password"]').val();
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Mismatch',
                        text: 'Passwords do not match!'
                    });
                    return false;
                }
                
                Swal.fire({
                    title: 'Adding User...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            });
            
            // Bulk selection
            $('#selectAll').change(function() {
                $('.user-checkbox:not(:disabled)').prop('checked', this.checked);
                updateBulkActionBar();
            });
            
            $('.user-checkbox').change(function() {
                updateBulkActionBar();
                $('#selectAll').prop('checked', 
                    $('.user-checkbox:not(:disabled)').length === $('.user-checkbox:not(:disabled):checked').length
                );
            });
        });
        
        function updateBulkActionBar() {
            const selectedCount = $('.user-checkbox:checked').length;
            if (selectedCount > 0) {
                $('#bulkActionBar').show();
                $('#selectedCount').text(selectedCount);
            } else {
                $('#bulkActionBar').hide();
            }
        }
        
        function clearSelection() {
            $('.user-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            updateBulkActionBar();
        }
        
        function performBulkAction() {
            const action = $('#bulkActionSelect').val();
            if (!action) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Select Action',
                    text: 'Please select an action to perform'
                });
                return;
            }
            
            const selectedIds = [];
            $('.user-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            Swal.fire({
                title: 'Confirm Bulk Action',
                text: `Are you sure you want to ${action} ${selectedIds.length} user(s)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, proceed'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#bulkActionType').val(action);
                    $('#bulkUserIds').val(JSON.stringify(selectedIds));
                    $('#bulkActionForm').submit();
                }
            });
        }
        
        function viewUser(userId) {
            $.ajax({
                url: '<?= url('api/dashboard') ?>?action=get_user_details&id=' + userId,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#userDetailsContent').html(response.html);
                    } else {
                        $('#userDetailsContent').html(`
                            <div class="text-center py-5">
                                <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                                <h4>Error Loading Details</h4>
                                <p class="text-muted">${response.message}</p>
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#userDetailsContent').html(`
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                            <h4>Connection Error</h4>
                            <p class="text-muted">Failed to load user details. Please try again.</p>
                        </div>
                    `);
                }
            });
        }
        
        function editUser(userId, firstname, lastname, email, phone, roleId, status) {
            // Decode HTML entities
            firstname = $('<textarea/>').html(firstname).text();
            lastname = $('<textarea/>').html(lastname).text();
            email = $('<textarea/>').html(email).text();
            phone = $('<textarea/>').html(phone).text();
            
            // Set form values
            $('#editUserId').val(userId);
            $('#editFirstname').val(firstname);
            $('#editLastname').val(lastname);
            $('#editEmail').val(email);
            $('#editPhone').val(phone);
            $('#editRoleId').val(roleId);
            $('#editStatus').val(status);
            
            // Show modal
            $('#editUserModal').modal('show');
        }
        
        function changeUserPassword(userId) {
            Swal.fire({
                title: 'Change Password',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" id="newUserPassword" class="form-control" placeholder="Enter new password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" id="confirmUserPassword" class="form-control" placeholder="Confirm new password" required>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Change Password',
                preConfirm: () => {
                    const newPass = document.getElementById('newUserPassword').value;
                    const confirm = document.getElementById('confirmUserPassword').value;
                    
                    if (!newPass || !confirm) {
                        Swal.showValidationMessage('Please fill all fields');
                        return false;
                    }
                    
                    if (newPass !== confirm) {
                        Swal.showValidationMessage('Passwords do not match');
                        return false;
                    }
                    
                    if (newPass.length < 6) {
                        Swal.showValidationMessage('Password must be at least 6 characters');
                        return false;
                    }
                    
                    return { newPass };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= url('admin/users-management') ?>',
                        method: 'POST',
                        data: {
                            action: 'change_password',
                            id: userId,
                            password: result.value.newPass
                        },
                        success: function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Password changed successfully',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to change password'
                            });
                        }
                    });
                }
            });
        }
        
        function confirmDeleteUser(userId, userName) {
            // Decode HTML entities
            userName = $('<textarea/>').html(userName).text();
            
            Swal.fire({
                title: 'Delete User?',
                html: `Are you sure you want to delete <strong>${userName}</strong>?<br><small class="text-danger">This action cannot be undone.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#deleteUserId').val(userId);
                    $('#deleteUserForm').submit();
                }
            });
        }
    </script>
</body>
</html>