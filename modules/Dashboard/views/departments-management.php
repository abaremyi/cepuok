<?php
// modules/Dashboard/views/departments-management.php
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
if (!$decoded->is_super_admin && !in_array('staff.view', $decoded->permissions)) {
    header("Location: " . url('admin'));
    exit;
}

// Include database connection
require_once $root_path . "/config/database.php";
$pdo = Database::getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_department') {
        try {
            $department_name = $_POST['department_name'];
            $department_icon = $_POST['department_icon'];
            $description = $_POST['description'];
            $staff_count = $_POST['staff_count'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $head_of_department = $_POST['head_of_department'];
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            $stmt = $pdo->prepare("INSERT INTO departments (department_name, department_icon, description, staff_count, email, phone, head_of_department, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$department_name, $department_icon, $description, $staff_count, $email, $phone, $head_of_department, $display_order, $status]);
            
            $_SESSION['success_message'] = 'Department added successfully!';
            header("Location: " . url('admin/departments'));
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Failed to add department: ' . $e->getMessage();
            header("Location: " . url('admin/departments'));
            exit;
        }
    }
    
    if ($action === 'update_department') {
        try {
            $id = $_POST['id'];
            $department_name = $_POST['department_name'];
            $department_icon = $_POST['department_icon'];
            $description = $_POST['description'];
            $staff_count = $_POST['staff_count'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $head_of_department = $_POST['head_of_department'];
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            $stmt = $pdo->prepare("UPDATE departments SET department_name = ?, department_icon = ?, description = ?, staff_count = ?, email = ?, phone = ?, head_of_department = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$department_name, $department_icon, $description, $staff_count, $email, $phone, $head_of_department, $display_order, $status, $id]);
            
            $_SESSION['success_message'] = 'Department updated successfully!';
            header("Location: " . url('admin/departments'));
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Failed to update department: ' . $e->getMessage();
            header("Location: " . url('admin/departments'));
            exit;
        }
    }
    
    if ($action === 'delete_department') {
        try {
            $id = $_POST['id'];
            
            // Check if department has staff
            $stmt = $pdo->prepare("SELECT COUNT(*) as staff_count FROM school_staff WHERE department_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['staff_count'] > 0) {
                throw new Exception('Cannot delete department. It has staff members assigned. Please reassign or delete staff first.');
            }
            
            $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success_message'] = 'Department deleted successfully!';
            header("Location: " . url('admin/departments'));
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Failed to delete department: ' . $e->getMessage();
            header("Location: " . url('admin/departments'));
            exit;
        }
    }
}

// Fetch all departments
$stmt = $pdo->query("SELECT * FROM departments ORDER BY display_order, department_name");
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total staff count per department
$staffCountStmt = $pdo->query("
    SELECT department_id, COUNT(*) as count 
    FROM school_staff 
    WHERE status = 'active' 
    GROUP BY department_id
");
$staffCounts = [];
while ($row = $staffCountStmt->fetch(PDO::FETCH_ASSOC)) {
    $staffCounts[$row['department_id']] = $row['count'];
}

// Check for messages
session_start();
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Clear messages
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
</head>
<body>
    <?php include_once 'components/admin-sidebar.php'; ?>
    <?php include_once 'components/admin-navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Departments Management</h2>
                        <p class="text-muted mb-0">Manage school departments and staff allocation</p>
                    </div>
                    <div>
                        <a href="<?= url('admin/staff') ?>" class="btn btn-outline-primary me-2">
                            <i class="fas fa-users me-2"></i> Staff Management
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                            <i class="fas fa-plus me-2"></i> Add Department
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($departments)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                        <h4>No departments added yet</h4>
                        <p class="text-muted mb-4">Add your first department to organize staff</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                            <i class="fas fa-plus me-2"></i> Add First Department
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Department</th>
                                    <th>Staff Count</th>
                                    <th>Head of Department</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td><?= $dept['display_order'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="<?= $dept['department_icon'] ?> fa-lg me-3 text-primary"></i>
                                                <div>
                                                    <strong><?= htmlspecialchars($dept['department_name']) ?></strong>
                                                    <div class="text-muted small"><?= htmlspecialchars(substr($dept['description'], 0, 60)) ?>...</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill">
                                                <?= $staffCounts[$dept['id']] ?? 0 ?> / <?= $dept['staff_count'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($dept['head_of_department'] ?? 'Not assigned') ?></td>
                                        <td>
                                            <?php if ($dept['email']): ?>
                                                <div class="small"><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($dept['email']) ?></div>
                                            <?php endif; ?>
                                            <?php if ($dept['phone']): ?>
                                                <div class="small"><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($dept['phone']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $dept['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($dept['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editDepartmentModal"
                                                        data-dept-id="<?= $dept['id'] ?>"
                                                        data-dept-name="<?= htmlspecialchars($dept['department_name']) ?>"
                                                        data-dept-icon="<?= htmlspecialchars($dept['department_icon']) ?>"
                                                        data-description="<?= htmlspecialchars($dept['description']) ?>"
                                                        data-staff-count="<?= $dept['staff_count'] ?>"
                                                        data-email="<?= htmlspecialchars($dept['email']) ?>"
                                                        data-phone="<?= htmlspecialchars($dept['phone']) ?>"
                                                        data-hod="<?= htmlspecialchars($dept['head_of_department']) ?>"
                                                        data-display-order="<?= $dept['display_order'] ?>"
                                                        data-status="<?= $dept['status'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="confirmDelete(<?= $dept['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addDepartmentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_department">
                        
                        <div class="mb-3">
                            <label class="form-label">Department Name *</label>
                            <input type="text" class="form-control" name="department_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Icon Class *</label>
                            <input type="text" class="form-control" name="department_icon" 
                                   value="fas fa-building" required>
                            <small class="text-muted">Font Awesome icon class (e.g., fas fa-users, fas fa-book)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Staff Count</label>
                                <input type="number" class="form-control" name="staff_count" value="0" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0" min="0">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Head of Department</label>
                            <input type="text" class="form-control" name="head_of_department">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editDepartmentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_department">
                        <input type="hidden" name="id" id="editDeptId">
                        
                        <div class="mb-3">
                            <label class="form-label">Department Name *</label>
                            <input type="text" class="form-control" name="department_name" id="editDeptName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Icon Class *</label>
                            <input type="text" class="form-control" name="department_icon" id="editDeptIcon" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDeptDesc" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Staff Count</label>
                                <input type="number" class="form-control" name="staff_count" id="editStaffCount" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editDisplayOrderDept" min="0">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="editDeptEmail">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" id="editDeptPhone">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Head of Department</label>
                            <input type="text" class="form-control" name="head_of_department" id="editDeptHOD">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="editDeptStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_department">
        <input type="hidden" name="id" id="deleteDeptId">
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
                    timer: 3000
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
                    timer: 5000
                });
            <?php endif; ?>
            
            // Edit modal data
            $('#editDepartmentModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#editDeptId').val(button.data('dept-id'));
                modal.find('#editDeptName').val(button.data('dept-name'));
                modal.find('#editDeptIcon').val(button.data('dept-icon'));
                modal.find('#editDeptDesc').val(button.data('description'));
                modal.find('#editStaffCount').val(button.data('staff-count'));
                modal.find('#editDeptEmail').val(button.data('email'));
                modal.find('#editDeptPhone').val(button.data('phone'));
                modal.find('#editDeptHOD').val(button.data('hod'));
                modal.find('#editDisplayOrderDept').val(button.data('display-order'));
                modal.find('#editDeptStatus').val(button.data('status'));
            });
        });
        
        function confirmDelete(deptId) {
            Swal.fire({
                title: 'Delete Department?',
                text: 'This will permanently remove the department. Make sure no staff members are assigned to it.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#deleteDeptId').val(deptId);
                    $('#deleteForm').submit();
                }
            });
        }
    </script>
</body>
</html>