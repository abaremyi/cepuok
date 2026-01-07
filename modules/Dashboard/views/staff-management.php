<?php
// modules/Dashboard/views/staff-management.php
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
    
    if ($action === 'add_staff') {
        try {
            $staff_code = $_POST['staff_code'];
            $full_name = $_POST['full_name'];
            $position = $_POST['position'];
            $staff_type = $_POST['staff_type'];
            $department_id = $_POST['department_id'] ?: NULL;
            $qualifications = $_POST['qualifications'];
            $short_bio = $_POST['short_bio'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $join_date = $_POST['join_date'];
            $years_experience = $_POST['years_experience'];
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            // Check if staff code already exists
            $stmt = $pdo->prepare("SELECT id FROM school_staff WHERE staff_code = ?");
            $stmt->execute([$staff_code]);
            if ($stmt->fetch()) {
                throw new Exception('Staff code already exists. Please use a unique code.');
            }
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM school_staff WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email already exists. Please use a different email.');
            }
            
            $stmt = $pdo->prepare("INSERT INTO school_staff (staff_code, full_name, position, staff_type, department_id, qualifications, short_bio, email, phone, join_date, years_experience, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$staff_code, $full_name, $position, $staff_type, $department_id, $qualifications, $short_bio, $email, $phone, $join_date, $years_experience, $display_order, $status]);
            
            $_SESSION['success_message'] = 'Staff added successfully!';
            header("Location: " . url('admin/staff'));
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Failed to add staff: ' . $e->getMessage();
            header("Location: " . url('admin/staff'));
            exit;
        }
    }
    
    if ($action === 'update_staff') {
        try {
            $id = $_POST['id'];
            $staff_code = $_POST['staff_code'];
            $full_name = $_POST['full_name'];
            $position = $_POST['position'];
            $staff_type = $_POST['staff_type'];
            $department_id = $_POST['department_id'] ?: NULL;
            $qualifications = $_POST['qualifications'];
            $short_bio = $_POST['short_bio'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $join_date = $_POST['join_date'];
            $years_experience = $_POST['years_experience'];
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            // Check if staff code already exists (excluding current)
            $stmt = $pdo->prepare("SELECT id FROM school_staff WHERE staff_code = ? AND id != ?");
            $stmt->execute([$staff_code, $id]);
            if ($stmt->fetch()) {
                throw new Exception('Staff code already exists. Please use a unique code.');
            }
            
            // Check if email already exists (excluding current)
            $stmt = $pdo->prepare("SELECT id FROM school_staff WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                throw new Exception('Email already exists. Please use a different email.');
            }
            
            $stmt = $pdo->prepare("UPDATE school_staff SET staff_code = ?, full_name = ?, position = ?, staff_type = ?, department_id = ?, qualifications = ?, short_bio = ?, email = ?, phone = ?, join_date = ?, years_experience = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$staff_code, $full_name, $position, $staff_type, $department_id, $qualifications, $short_bio, $email, $phone, $join_date, $years_experience, $display_order, $status, $id]);
            
            $_SESSION['success_message'] = 'Staff updated successfully!';
            header("Location: " . url('admin/staff'));
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Failed to update staff: ' . $e->getMessage();
            header("Location: " . url('admin/staff'));
            exit;
        }
    }
    
    if ($action === 'delete_staff') {
        try {
            $id = $_POST['id'];
            
            $stmt = $pdo->prepare("DELETE FROM school_staff WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success_message'] = 'Staff deleted successfully!';
            header("Location: " . url('admin/staff'));
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Failed to delete staff: ' . $e->getMessage();
            header("Location: " . url('admin/staff'));
            exit;
        }
    }
    
    if ($action === 'upload_staff_image') {
        try {
            $id = $_POST['id'];
            $upload_dir = $root_path . '/public/uploads/staff/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            if (isset($_FILES['staff_image']) && $_FILES['staff_image']['error'] === UPLOAD_ERR_OK) {
                $file_name = time() . '_' . basename($_FILES['staff_image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Check file type
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array($imageFileType, $allowed_types)) {
                    throw new Exception('Only JPG, JPEG, PNG & GIF files are allowed.');
                }
                
                // Check file size (5MB max)
                if ($_FILES['staff_image']['size'] > 5000000) {
                    throw new Exception('File is too large. Maximum size is 5MB.');
                }
                
                if (move_uploaded_file($_FILES['staff_image']['tmp_name'], $target_file)) {
                    $image_url = '/uploads/staff/' . $file_name;
                    
                    $stmt = $pdo->prepare("UPDATE school_staff SET image_url = ? WHERE id = ?");
                    $stmt->execute([$image_url, $id]);
                    
                    $_SESSION['success_message'] = 'Staff image uploaded successfully!';
                } else {
                    throw new Exception('Failed to upload image.');
                }
            }
            
            header("Location: " . url('admin/staff'));
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Failed to upload image: ' . $e->getMessage();
            header("Location: " . url('admin/staff'));
            exit;
        }
    }
}

// Fetch all staff
$stmt = $pdo->query("
    SELECT s.*, d.department_name 
    FROM school_staff s 
    LEFT JOIN departments d ON s.department_id = d.id 
    ORDER BY s.display_order, s.full_name
");
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all departments for dropdown
$deptStmt = $pdo->query("SELECT id, department_name FROM departments WHERE status = 'active' ORDER BY department_name");
$departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch statistics
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_staff,
        SUM(CASE WHEN staff_type = 'teaching' THEN 1 ELSE 0 END) as total_teachers,
        SUM(CASE WHEN staff_type = 'non_teaching' THEN 1 ELSE 0 END) as total_non_teaching,
        SUM(CASE WHEN staff_type = 'leadership' THEN 1 ELSE 0 END) as total_leadership,
        AVG(years_experience) as avg_experience
    FROM school_staff 
    WHERE status = 'active'
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Check for success/error messages
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
    <title>Staff Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .staff-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .staff-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .staff-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }
        .staff-info {
            padding: 15px;
        }
        .staff-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .staff-position {
            font-size: 0.9rem;
            color: #667eea;
            margin-bottom: 5px;
        }
        .staff-department {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .staff-bio {
            font-size: 0.85rem;
            color: #4b5563;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
        }
        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
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
                        <h2 class="mb-0">Staff Management</h2>
                        <p class="text-muted mb-0">Manage school staff members</p>
                    </div>
                    <div>
                        <a href="<?= url('admin/departments') ?>" class="btn btn-outline-primary me-2">
                            <i class="fas fa-sitemap me-2"></i> Departments
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                            <i class="fas fa-plus me-2"></i> Add New Staff
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <i class="fas fa-users stats-icon"></i>
                    <div class="stats-number"><?= $stats['total_staff'] ?? 0 ?></div>
                    <div class="stats-label">Total Staff</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-chalkboard-teacher stats-icon"></i>
                    <div class="stats-number"><?= $stats['total_teachers'] ?? 0 ?></div>
                    <div class="stats-label">Teachers</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-user-tie stats-icon"></i>
                    <div class="stats-number"><?= $stats['total_leadership'] ?? 0 ?></div>
                    <div class="stats-label">Leadership</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-chart-line stats-icon"></i>
                    <div class="stats-number"><?= round($stats['avg_experience'] ?? 0, 1) ?> yrs</div>
                    <div class="stats-label">Avg Experience</div>
                </div>
            </div>
        </div>

        <!-- Staff Grid -->
        <div class="row">
            <?php if (empty($staff)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h4>No staff members added yet</h4>
                            <p class="text-muted mb-4">Add your first staff member to get started</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                                <i class="fas fa-plus me-2"></i> Add First Staff
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($staff as $member): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="staff-card">
                            <div class="position-relative">
                                <img src="<?= !empty($member['image_url']) ? img_url($member['image_url']) : 'https://ui-avatars.com/api/?name=' . urlencode($member['full_name']) . '&background=667eea&color=fff&size=200' ?>" 
                                     alt="<?= htmlspecialchars($member['full_name']) ?>" 
                                     class="staff-image">
                                <div class="position-absolute top-0 end-0 p-2">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item" type="button" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editStaffModal"
                                                        data-staff-id="<?= $member['id'] ?>"
                                                        data-staff-code="<?= htmlspecialchars($member['staff_code']) ?>"
                                                        data-full-name="<?= htmlspecialchars($member['full_name']) ?>"
                                                        data-position="<?= htmlspecialchars($member['position']) ?>"
                                                        data-staff-type="<?= $member['staff_type'] ?>"
                                                        data-department-id="<?= $member['department_id'] ?>"
                                                        data-qualifications="<?= htmlspecialchars($member['qualifications']) ?>"
                                                        data-short-bio="<?= htmlspecialchars($member['short_bio']) ?>"
                                                        data-email="<?= htmlspecialchars($member['email']) ?>"
                                                        data-phone="<?= htmlspecialchars($member['phone']) ?>"
                                                        data-join-date="<?= $member['join_date'] ?>"
                                                        data-years-experience="<?= $member['years_experience'] ?>"
                                                        data-display-order="<?= $member['display_order'] ?>"
                                                        data-status="<?= $member['status'] ?>">
                                                    <i class="fas fa-edit me-2"></i> Edit
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" type="button" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadImageModal"
                                                        data-staff-id="<?= $member['id'] ?>"
                                                        data-staff-name="<?= htmlspecialchars($member['full_name']) ?>">
                                                    <i class="fas fa-camera me-2"></i> Upload Image
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" type="button" 
                                                        onclick="confirmDelete(<?= $member['id'] ?>)">
                                                    <i class="fas fa-trash me-2"></i> Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="staff-info">
                                <h6 class="staff-name"><?= htmlspecialchars($member['full_name']) ?></h6>
                                <div class="staff-position"><?= htmlspecialchars($member['position']) ?></div>
                                <?php if ($member['department_name']): ?>
                                    <div class="staff-department">
                                        <i class="fas fa-building me-1"></i>
                                        <?= htmlspecialchars($member['department_name']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="staff-bio">
                                    <?= htmlspecialchars(substr($member['short_bio'], 0, 100)) . (strlen($member['short_bio']) > 100 ? '...' : '') ?>
                                </div>
                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-envelope me-1"></i>
                                        <?= htmlspecialchars($member['email']) ?>
                                    </small>
                                    <span class="badge bg-<?= $member['status'] === 'active' ? 'success' : ($member['status'] === 'on_leave' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($member['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addStaffForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_staff">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Staff Code *</label>
                                <input type="text" class="form-control" name="staff_code" required 
                                       pattern="[A-Z]{2}[0-9]{4}"
                                       title="Format: XX1234 (Two letters followed by four numbers)">
                                <small class="text-muted">Format: XX1234 (e.g., ST0012)</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position *</label>
                                <input type="text" class="form-control" name="position" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Staff Type *</label>
                                <select class="form-select" name="staff_type" required>
                                    <option value="teaching">Teaching</option>
                                    <option value="non_teaching">Non-Teaching</option>
                                    <option value="leadership">Leadership</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-select" name="department_id">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Join Date</label>
                                <input type="date" class="form-control" name="join_date">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Years of Experience</label>
                                <input type="number" class="form-control" name="years_experience" min="0" max="50" value="0">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0" min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Qualifications</label>
                            <textarea class="form-control" name="qualifications" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Short Biography *</label>
                            <textarea class="form-control" name="short_bio" rows="3" required maxlength="500"></textarea>
                            <small class="text-muted">Maximum 500 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="on_leave">On Leave</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div class="modal fade" id="editStaffModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editStaffForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_staff">
                        <input type="hidden" name="id" id="editStaffId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Staff Code *</label>
                                <input type="text" class="form-control" name="staff_code" id="editStaffCode" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" id="editFullName" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position *</label>
                                <input type="text" class="form-control" name="position" id="editPosition" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Staff Type *</label>
                                <select class="form-select" name="staff_type" id="editStaffType" required>
                                    <option value="teaching">Teaching</option>
                                    <option value="non_teaching">Non-Teaching</option>
                                    <option value="leadership">Leadership</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-select" name="department_id" id="editDepartmentId">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" id="editEmail" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" id="editPhone">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Join Date</label>
                                <input type="date" class="form-control" name="join_date" id="editJoinDate">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Years of Experience</label>
                                <input type="number" class="form-control" name="years_experience" id="editYearsExperience" min="0" max="50">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editDisplayOrder" min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Qualifications</label>
                            <textarea class="form-control" name="qualifications" id="editQualifications" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Short Biography *</label>
                            <textarea class="form-control" name="short_bio" id="editShortBio" rows="3" required maxlength="500"></textarea>
                            <small class="text-muted">Maximum 500 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="editStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="on_leave">On Leave</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Image Modal -->
    <div class="modal fade" id="uploadImageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Staff Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="uploadImageForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="upload_staff_image">
                        <input type="hidden" name="id" id="uploadStaffId">
                        
                        <div class="mb-3">
                            <label class="form-label">Select Image</label>
                            <input type="file" class="form-control" name="staff_image" accept="image/*" required>
                            <small class="text-muted">Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF</small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Recommended size: 400x500 pixels (Portrait)
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_staff">
        <input type="hidden" name="id" id="deleteStaffId">
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
            $('#editStaffModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#editStaffId').val(button.data('staff-id'));
                modal.find('#editStaffCode').val(button.data('staff-code'));
                modal.find('#editFullName').val(button.data('full-name'));
                modal.find('#editPosition').val(button.data('position'));
                modal.find('#editStaffType').val(button.data('staff-type'));
                modal.find('#editDepartmentId').val(button.data('department-id'));
                modal.find('#editQualifications').val(button.data('qualifications'));
                modal.find('#editShortBio').val(button.data('short-bio'));
                modal.find('#editEmail').val(button.data('email'));
                modal.find('#editPhone').val(button.data('phone'));
                modal.find('#editJoinDate').val(button.data('join-date'));
                modal.find('#editYearsExperience').val(button.data('years-experience'));
                modal.find('#editDisplayOrder').val(button.data('display-order'));
                modal.find('#editStatus').val(button.data('status'));
            });
            
            // Upload image modal
            $('#uploadImageModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#uploadStaffId').val(button.data('staff-id'));
                modal.find('.modal-title').text('Upload Image for ' + button.data('staff-name'));
            });
            
            // Form validation
            $('#addStaffForm, #editStaffForm').submit(function(e) {
                const formName = $(this).attr('id') === 'addStaffForm' ? 'Add' : 'Update';
                
                // Validate staff code format
                const staffCode = $(this).find('input[name="staff_code"]').val().trim();
                const staffCodeRegex = /^[A-Z]{2}[0-9]{4}$/;
                
                if (!staffCodeRegex.test(staffCode)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Staff Code',
                        text: 'Staff code must be in format: XX1234 (Two capital letters followed by four numbers)',
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
                
                // Validate email
                const email = $(this).find('input[name="email"]').val().trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Email',
                        text: 'Please enter a valid email address',
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
                
                // Validate short bio length
                const shortBio = $(this).find('textarea[name="short_bio"]').val().trim();
                if (shortBio.length > 500) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Bio Too Long',
                        text: 'Short biography must not exceed 500 characters',
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
                
                return true;
            });
            
            // Image upload validation
            $('#uploadImageForm').submit(function(e) {
                const fileInput = $(this).find('input[name="staff_image"]')[0];
                
                if (fileInput.files.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'No File Selected',
                        text: 'Please select an image file to upload',
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
                
                const file = fileInput.files[0];
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Only JPG, JPEG, PNG, and GIF files are allowed',
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
                
                if (file.size > maxSize) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'Maximum file size is 5MB',
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
                
                return true;
            });
        });
        
        function confirmDelete(staffId) {
            Swal.fire({
                title: 'Delete Staff Member?',
                text: 'This action cannot be undone. The staff member will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#deleteStaffId').val(staffId);
                    $('#deleteForm').submit();
                }
            });
        }
    </script>
</body>
</html>