<?php
// modules/Dashboard/views/why-choose-management.php
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
    
    if ($action === 'add_item') {
        try {
            $icon_class = $_POST['icon_class'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            // Validate inputs
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
            
            $stmt = $pdo->prepare("INSERT INTO why_choose_items (icon_class, title, description, display_order, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$icon_class, $title, $description, $display_order, $status]);
            
            session_start();
            $_SESSION['success_message'] = 'Item added successfully!';
            $_SESSION['success_action'] = 'add';
            
            header("Location: " . url('admin/why-choose'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to add item: ' . $e->getMessage();
            header("Location: " . url('admin/why-choose'));
            exit;
        }
    }
    
    if ($action === 'update_item') {
        try {
            $id = $_POST['id'];
            $icon_class = $_POST['icon_class'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            // Validate inputs
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
            
            $stmt = $pdo->prepare("UPDATE why_choose_items SET icon_class = ?, title = ?, description = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$icon_class, $title, $description, $display_order, $status, $id]);
            
            session_start();
            $_SESSION['success_message'] = 'Item updated successfully!';
            $_SESSION['success_action'] = 'update';
            
            header("Location: " . url('admin/why-choose'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update item: ' . $e->getMessage();
            header("Location: " . url('admin/why-choose'));
            exit;
        }
    }
    
    if ($action === 'delete_item') {
        try {
            $id = $_POST['id'];
            
            $stmt = $pdo->prepare("DELETE FROM why_choose_items WHERE id = ?");
            $stmt->execute([$id]);
            
            session_start();
            $_SESSION['success_message'] = 'Item deleted successfully!';
            $_SESSION['success_action'] = 'delete';
            
            header("Location: " . url('admin/why-choose'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to delete item: ' . $e->getMessage();
            header("Location: " . url('admin/why-choose'));
            exit;
        }
    }
    
    if ($action === 'update_order') {
        try {
            $orders = $_POST['order'] ?? [];
            
            foreach ($orders as $id => $order) {
                $stmt = $pdo->prepare("UPDATE why_choose_items SET display_order = ? WHERE id = ?");
                $stmt->execute([$order, $id]);
            }
            
            session_start();
            $_SESSION['success_message'] = 'Display order updated successfully!';
            $_SESSION['success_action'] = 'order_update';
            
            header("Location: " . url('admin/why-choose'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update order: ' . $e->getMessage();
            header("Location: " . url('admin/why-choose'));
            exit;
        }
    }
}

// Fetch all items
$stmt = $pdo->query("SELECT * FROM why_choose_items ORDER BY display_order, created_at DESC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// FontAwesome icons for selection
$fontawesome_icons = [
    'fas fa-user-graduate' => 'Graduate',
    'fas fa-laptop' => 'Laptop',
    'fas fa-heartbeat' => 'Heartbeat',
    'fas fa-shield-alt' => 'Shield',
    'fas fa-globe' => 'Globe',
    'fas fa-trophy' => 'Trophy',
    'fas fa-chalkboard-teacher' => 'Teacher',
    'fas fa-book-open' => 'Book',
    'fas fa-flask' => 'Flask',
    'fas fa-music' => 'Music',
    'fas fa-paint-brush' => 'Paint Brush',
    'fas fa-futbol' => 'Sports',
    'fas fa-users' => 'Users',
    'fas fa-hands-helping' => 'Helping Hands',
    'fas fa-award' => 'Award',
    'fas fa-star' => 'Star',
    'fas fa-check-circle' => 'Check Circle',
    'fas fa-lightbulb' => 'Lightbulb',
    'fas fa-graduation-cap' => 'Graduation Cap',
    'fas fa-child' => 'Child',
    'fas fa-baby' => 'Baby'
];

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
    <title>Why Choose Us Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .item-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.3s;
            height: 100%;
        }
        .item-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .item-card.ui-sortable-helper {
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transform: rotate(2deg);
        }
        .item-card.ui-sortable-placeholder {
            border: 2px dashed #667eea;
            background: rgba(102, 126, 234, 0.1);
            visibility: visible !important;
        }
        .item-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin: 0 auto 15px;
        }
        .item-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
            max-height: 80px;
            overflow: hidden;
        }
        .sortable-handle {
            cursor: move;
            color: #9ca3af;
            font-size: 1.2rem;
        }
        .sortable-handle:hover {
            color: #667eea;
        }
        .icon-preview-lg {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 10px;
        }
        .order-badge {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
        }
        .drag-instructions {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid #667eea;
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
                        <h2 class="mb-0">Why Choose Us Management</h2>
                        <p class="text-muted mb-0">Manage the "Why Choose Mount Carmel School" section items</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="fas fa-plus me-2"></i> Add New Item
                    </button>
                </div>
            </div>
        </div>

        <!-- Drag Instructions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card drag-instructions">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-arrows-alt me-3 text-primary fs-4"></i>
                            <div>
                                <h6 class="mb-1">Drag & Drop Sorting</h6>
                                <p class="mb-0 small text-muted">Drag items by the handle (<i class="fas fa-arrows-alt"></i>) to reorder them. The order will be saved automatically.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Grid -->
        <div class="row" id="sortableItems">
            <?php if (empty($items)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <h4>No items added yet</h4>
                            <p class="text-muted">Click the "Add New Item" button to add your first "Why Choose Us" item.</p>
                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                <i class="fas fa-plus me-2"></i> Add First Item
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($items as $index => $item): ?>
                    <div class="col-md-6 col-lg-4 mb-4" data-id="<?= $item['id'] ?>">
                        <div class="card item-card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="sortable-handle me-2">
                                        <i class="fas fa-arrows-alt"></i>
                                    </span>
                                    <div class="order-badge">
                                        <?= $item['display_order'] + 1 ?>
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
                                                    data-bs-target="#editItemModal"
                                                    data-item-id="<?= $item['id'] ?>"
                                                    data-icon-class="<?= $item['icon_class'] ?>"
                                                    data-title="<?= htmlspecialchars($item['title']) ?>"
                                                    data-description="<?= htmlspecialchars($item['description']) ?>"
                                                    data-display-order="<?= $item['display_order'] ?>"
                                                    data-status="<?= $item['status'] ?>">
                                                <i class="fas fa-edit me-2 text-primary"></i> Edit
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="confirmDelete(<?= $item['id'] ?>)">
                                                <i class="fas fa-trash me-2"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <div class="item-icon">
                                    <i class="<?= $item['icon_class'] ?>"></i>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                                <p class="card-text item-description"><?= htmlspecialchars($item['description']) ?></p>
                                <div class="mt-3">
                                    <span class="badge <?= $item['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($item['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addItemForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_item">
                        
                        <div class="mb-3 text-center">
                            <div class="icon-preview-lg">
                                <i id="addIconPreview" class="fas fa-star"></i>
                            </div>
                            <label class="form-label">Icon</label>
                            <select class="form-select" name="icon_class" id="addIconClass" required>
                                <option value="">Select an icon</option>
                                <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                    <option value="<?= $icon ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" required maxlength="255">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="4" required maxlength="500"></textarea>
                            <small class="text-muted">Max 500 characters</small>
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
                        <button type="submit" class="btn btn-primary">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editItemForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_item">
                        <input type="hidden" name="id" id="editItemId">
                        
                        <div class="mb-3 text-center">
                            <div class="icon-preview-lg">
                                <i id="editIconPreview" class="fas fa-star"></i>
                            </div>
                            <label class="form-label">Icon</label>
                            <select class="form-select" name="icon_class" id="editIconClass" required>
                                <option value="">Select an icon</option>
                                <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                    <option value="<?= $icon ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" id="editTitle" required maxlength="255">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="4" required maxlength="500"></textarea>
                            <small class="text-muted">Max 500 characters</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editItemDisplayOrder" min="0" max="999">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="editItemStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_item">
        <input type="hidden" name="id" id="deleteItemId">
    </form>

    <!-- Update Order Form -->
    <form id="updateOrderForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_order">
        <input type="hidden" name="order" id="orderData">
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
            
            // Initialize sortable
            $("#sortableItems").sortable({
                handle: ".sortable-handle",
                placeholder: "ui-sortable-placeholder",
                update: function(event, ui) {
                    updateDisplayOrder();
                }
            });
            $("#sortableItems").disableSelection();
            
            // Icon preview for add modal
            $('#addIconClass').change(function() {
                const selectedIcon = $(this).val();
                $('#addIconPreview').attr('class', selectedIcon);
            });
            
            // Icon preview for edit modal
            $('#editIconClass').change(function() {
                const selectedIcon = $(this).val();
                $('#editIconPreview').attr('class', selectedIcon);
            });
            
            // Edit modal data
            $('#editItemModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#editItemId').val(button.data('item-id'));
                modal.find('#editIconClass').val(button.data('icon-class'));
                modal.find('#editTitle').val(button.data('title'));
                modal.find('#editDescription').val(button.data('description'));
                modal.find('#editItemDisplayOrder').val(button.data('display-order'));
                modal.find('#editItemStatus').val(button.data('status'));
                
                // Update icon preview
                $('#editIconPreview').attr('class', button.data('icon-class'));
            });
            
            // Form submission with SweetAlert confirmation
            $('#addItemForm, #editItemForm').submit(function(e) {
                // Validate required fields
                const title = $(this).find('input[name="title"]').val().trim();
                const description = $(this).find('textarea[name="description"]').val().trim();
                
                if (!title || !description) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please fill in all required fields',
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
                
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                
                return true;
            });
        });
        
        // Update display order after sorting
        function updateDisplayOrder() {
            const orderData = {};
            $('#sortableItems .col-md-6').each(function(index) {
                const itemId = $(this).data('id');
                orderData[itemId] = index;
                
                // Update the order badge
                $(this).find('.order-badge').text(index + 1);
            });
            
            // Send AJAX request to update order
            $.ajax({
                url: '<?= url('admin/why-choose') ?>',
                method: 'POST',
                data: {
                    action: 'update_order',
                    order: orderData
                },
                success: function(response) {
                    // Show success notification
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Updated!',
                        text: 'Display order has been saved successfully',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update display order',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            });
        }
        
        // Confirm delete
        function confirmDelete(itemId) {
            Swal.fire({
                title: 'Delete Item?',
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
                    
                    $('#deleteItemId').val(itemId);
                    $('#deleteForm').submit();
                }
            });
        }
        
        // Toggle sidebar on mobile
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>