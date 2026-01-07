<?php
// modules/Dashboard/views/quick-stats-management.php
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
if (!$decoded->is_super_admin && !in_array('website.manage_content', $decoded->permissions)) {
    header("Location: " . url('admin'));
    exit;
}

// Include database connection
require_once $root_path . "/config/database.php";
$pdo = Database::getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_stat') {
        try {
            $stat_name = $_POST['stat_name'];
            $stat_value = $_POST['stat_value'];
            $stat_label = $_POST['stat_label'];
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            // Validate inputs
            $stat_name = preg_replace('/[^a-zA-Z0-9_]/', '', $stat_name); // Only alphanumeric and underscore
            $stat_label = htmlspecialchars($stat_label, ENT_QUOTES, 'UTF-8');
            
            // Check if stat name already exists
            $stmt = $pdo->prepare("SELECT id FROM quick_stats WHERE stat_name = ?");
            $stmt->execute([$stat_name]);
            if ($stmt->fetch()) {
                throw new Exception('Stat name already exists. Please use a unique name.');
            }
            
            $stmt = $pdo->prepare("INSERT INTO quick_stats (stat_name, stat_value, stat_label, display_order, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$stat_name, $stat_value, $stat_label, $display_order, $status]);
            
            session_start();
            $_SESSION['success_message'] = 'Stat added successfully!';
            $_SESSION['success_action'] = 'add';
            
            header("Location: " . url('admin/quick-stats'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to add stat: ' . $e->getMessage();
            header("Location: " . url('admin/quick-stats'));
            exit;
        }
    }
    
    if ($action === 'update_stat') {
        try {
            $id = $_POST['id'];
            $stat_name = $_POST['stat_name'];
            $stat_value = $_POST['stat_value'];
            $stat_label = $_POST['stat_label'];
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            // Validate inputs
            $stat_name = preg_replace('/[^a-zA-Z0-9_]/', '', $stat_name);
            $stat_label = htmlspecialchars($stat_label, ENT_QUOTES, 'UTF-8');
            
            // Check if stat name already exists (excluding current)
            $stmt = $pdo->prepare("SELECT id FROM quick_stats WHERE stat_name = ? AND id != ?");
            $stmt->execute([$stat_name, $id]);
            if ($stmt->fetch()) {
                throw new Exception('Stat name already exists. Please use a unique name.');
            }
            
            $stmt = $pdo->prepare("UPDATE quick_stats SET stat_name = ?, stat_value = ?, stat_label = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$stat_name, $stat_value, $stat_label, $display_order, $status, $id]);
            
            session_start();
            $_SESSION['success_message'] = 'Stat updated successfully!';
            $_SESSION['success_action'] = 'update';
            
            header("Location: " . url('admin/quick-stats'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update stat: ' . $e->getMessage();
            header("Location: " . url('admin/quick-stats'));
            exit;
        }
    }
    
    if ($action === 'delete_stat') {
        try {
            $id = $_POST['id'];
            
            $stmt = $pdo->prepare("DELETE FROM quick_stats WHERE id = ?");
            $stmt->execute([$id]);
            
            session_start();
            $_SESSION['success_message'] = 'Stat deleted successfully!';
            $_SESSION['success_action'] = 'delete';
            
            header("Location: " . url('admin/quick-stats'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to delete stat: ' . $e->getMessage();
            header("Location: " . url('admin/quick-stats'));
            exit;
        }
    }
    
    if ($action === 'bulk_update') {
        try {
            $stats = $_POST['stats'] ?? [];
            
            foreach ($stats as $id => $stat_data) {
                $stmt = $pdo->prepare("UPDATE quick_stats SET stat_value = ?, display_order = ?, status = ? WHERE id = ?");
                $stmt->execute([$stat_data['value'], $stat_data['order'], $stat_data['status'], $id]);
            }
            
            session_start();
            $_SESSION['success_message'] = 'Stats updated successfully!';
            $_SESSION['success_action'] = 'bulk_update';
            
            header("Location: " . url('admin/quick-stats'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update stats: ' . $e->getMessage();
            header("Location: " . url('admin/quick-stats'));
            exit;
        }
    }
}

// Fetch all stats
$stmt = $pdo->query("SELECT * FROM quick_stats ORDER BY display_order, created_at");
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for success/error messages from session
session_start();
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
$success_action = $_SESSION['success_action'] ?? null;

// Clear messages from session
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
unset($_SESSION['success_action']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Stats Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .stat-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .stat-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .stat-name {
            color: #9ca3af;
            font-size: 0.8rem;
            font-family: monospace;
        }
        .order-input {
            width: 70px;
        }
        .bulk-actions {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid #667eea;
        }
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #9ca3af;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .edit-btn-stat {
            padding: 2px 8px;
            font-size: 0.8rem;
        }
        .value-input {
            max-width: 120px;
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
                        <h2 class="mb-0">Quick Stats Management</h2>
                        <p class="text-muted mb-0">Manage statistics displayed on the homepage</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStatModal">
                        <i class="fas fa-plus me-2"></i> Add New Stat
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bulk-actions">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Bulk Actions</h6>
                                <p class="mb-0 small text-muted">Edit multiple stats quickly using the form below</p>
                            </div>
                            <button class="btn btn-sm btn-success" id="saveAllBtn">
                                <i class="fas fa-save me-2"></i> Save All Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <?php if (empty($stats)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body empty-state">
                            <i class="fas fa-chart-bar"></i>
                            <h4>No statistics added yet</h4>
                            <p class="text-muted mb-4">Add your first statistic to display on the homepage</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStatModal">
                                <i class="fas fa-plus me-2"></i> Add First Statistic
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" id="bulkForm">
                <input type="hidden" name="action" value="bulk_update">
                <div class="row">
                    <?php foreach ($stats as $stat): ?>
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card stat-card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon me-2">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div>
                                            <span class="stat-name"><?= htmlspecialchars($stat['stat_name']) ?></span>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item" type="button"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editStatModal"
                                                        data-stat-id="<?= $stat['id'] ?>"
                                                        data-stat-name="<?= htmlspecialchars($stat['stat_name']) ?>"
                                                        data-stat-value="<?= htmlspecialchars($stat['stat_value']) ?>"
                                                        data-stat-label="<?= htmlspecialchars($stat['stat_label']) ?>"
                                                        data-display-order="<?= $stat['display_order'] ?>"
                                                        data-status="<?= $stat['status'] ?>">
                                                    <i class="fas fa-edit me-2 text-primary"></i> Edit Details
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" type="button" onclick="confirmDelete(<?= $stat['id'] ?>)">
                                                    <i class="fas fa-trash me-2"></i> Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Stat Value</label>
                                        <div class="input-group">
                                            <input type="text" 
                                                   class="form-control form-control-sm value-input" 
                                                   name="stats[<?= $stat['id'] ?>][value]" 
                                                   value="<?= htmlspecialchars($stat['stat_value']) ?>"
                                                   required>
                                            <?php if (strpos($stat['stat_value'], '%') !== false): ?>
                                                <span class="input-group-text">%</span>
                                            <?php elseif (is_numeric($stat['stat_value'])): ?>
                                                <span class="input-group-text">
                                                    <i class="fas fa-hashtag"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Display Label</label>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-tag me-2 text-muted"></i>
                                            <span class="stat-label"><?= htmlspecialchars($stat['stat_label']) ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label small text-muted mb-1">Order</label>
                                            <input type="number" 
                                                   class="form-control form-control-sm order-input" 
                                                   name="stats[<?= $stat['id'] ?>][order]" 
                                                   value="<?= $stat['display_order'] ?>"
                                                   min="0"
                                                   max="999">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted mb-1">Status</label>
                                            <select class="form-select form-select-sm" name="stats[<?= $stat['id'] ?>][status]">
                                                <option value="active" <?= $stat['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= $stat['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Add Stat Modal -->
    <div class="modal fade" id="addStatModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Statistic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addStatForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_stat">
                        
                        <div class="mb-3">
                            <label class="form-label">Statistic Name *</label>
                            <input type="text" class="form-control" name="stat_name" required 
                                   pattern="[a-zA-Z0-9_]+" 
                                   title="Only letters, numbers, and underscores allowed">
                            <small class="text-muted">Internal name (letters, numbers, underscores only)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Statistic Value *</label>
                            <input type="text" class="form-control" name="stat_value" required>
                            <small class="text-muted">Example: 10 (for years), 100% (for percentage), 3 (for count)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Display Label *</label>
                            <input type="text" class="form-control" name="stat_label" required maxlength="100">
                            <small class="text-muted">This will be displayed below the value on the website</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0" min="0" max="999">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Statistic</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Stat Modal -->
    <div class="modal fade" id="editStatModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Statistic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editStatForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_stat">
                        <input type="hidden" name="id" id="editStatId">
                        
                        <div class="mb-3">
                            <label class="form-label">Statistic Name *</label>
                            <input type="text" class="form-control" name="stat_name" id="editStatName" required 
                                   pattern="[a-zA-Z0-9_]+"
                                   title="Only letters, numbers, and underscores allowed">
                            <small class="text-muted">Internal name (cannot be changed easily once set)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Statistic Value *</label>
                            <input type="text" class="form-control" name="stat_value" id="editStatValue" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Display Label *</label>
                            <input type="text" class="form-control" name="stat_label" id="editStatLabel" required maxlength="100">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editStatDisplayOrder" min="0" max="999">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="editStatStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Statistic</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_stat">
        <input type="hidden" name="id" id="deleteStatId">
    </form>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include_once 'components/admin-scripts.php'; ?>
    
    <script>
        // Show success/error messages with SweetAlert
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
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
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
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            <?php endif; ?>
            
            // Edit modal data
            $('#editStatModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#editStatId').val(button.data('stat-id'));
                modal.find('#editStatName').val(button.data('stat-name'));
                modal.find('#editStatValue').val(button.data('stat-value'));
                modal.find('#editStatLabel').val(button.data('stat-label'));
                modal.find('#editStatDisplayOrder').val(button.data('display-order'));
                modal.find('#editStatStatus').val(button.data('status'));
            });
            
            // Save all changes
            $('#saveAllBtn').click(function() {
                // Validate all inputs
                let isValid = true;
                $('.value-input').each(function() {
                    if (!$(this).val().trim()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Error',
                        text: 'Please fill in all statistic values',
                        confirmButtonColor: '#667eea'
                    });
                    return;
                }
                
                Swal.fire({
                    title: 'Save All Changes?',
                    text: 'This will update all statistics with the values you entered.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, save all',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Saving...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                        
                        $('#bulkForm').submit();
                    }
                });
            });
            
            // Form submission with SweetAlert confirmation
            $('#addStatForm, #editStatForm').submit(function(e) {
                const formName = $(this).attr('id') === 'addStatForm' ? 'Add' : 'Update';
                
                // Validate required fields
                const statName = $(this).find('input[name="stat_name"]').val().trim();
                const statValue = $(this).find('input[name="stat_value"]').val().trim();
                const statLabel = $(this).find('input[name="stat_label"]').val().trim();
                
                if (!statName || !statValue || !statLabel) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please fill in all required fields',
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
                
                // Validate stat name format
                const statNameRegex = /^[a-zA-Z0-9_]+$/;
                if (!statNameRegex.test(statName)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Format',
                        text: 'Statistic name can only contain letters, numbers, and underscores',
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
                
                // Show confirmation
                e.preventDefault();
                Swal.fire({
                    title: `${formName} Statistic?`,
                    text: `Are you sure you want to ${formName.toLowerCase()} this statistic?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Yes, ${formName.toLowerCase()} it`,
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Processing...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                        
                        $(this).off('submit').submit();
                    }
                });
                
                return false;
            });
        });
        
        // Confirm delete
        function confirmDelete(statId) {
            Swal.fire({
                title: 'Delete Statistic?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    
                    $('#deleteStatId').val(statId);
                    $('#deleteForm').submit();
                }
            });
        }
        
        // Toggle sidebar on mobile
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
        
        // Auto-add percentage sign if value ends with %
        $(document).on('blur', '.value-input', function() {
            const value = $(this).val().trim();
            if (value && value.endsWith('%')) {
                // Remove any extra percentage signs and add one
                const cleanValue = value.replace(/%+$/, '');
                $(this).val(cleanValue + '%');
            }
        });
    </script>
</body>
</html>