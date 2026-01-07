<?php
// modules/Dashboard/views/hero-sliders.php
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . '/helpers/JWTHandler.php';

// Get token from cookie
$token = $_COOKIE['auth_token'] ?? '';

// Validate token
$jwtHandler = new JWTHandler();
$decoded = $token ? $jwtHandler->validateToken($token) : null;

if (!$decoded) {
    header("Location: " . url('login'));
    exit;
}

// Check permission
if (!$decoded->is_super_admin && !in_array('website.manage_sliders', $decoded->permissions)) {
    header("Location: " . url('admin'));
    exit;
}

// Include database connection
require_once $root_path . "/config/database.php";
$pdo = Database::getConnection();

// Helper function to generate unique filename
function generateUniqueFilename($prefix, $extension) {
    $randomNumber = rand(100, 9999);
    return $prefix . '-' . $randomNumber . '.' . $extension;
}

// Helper function to get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Helper function to delete file if exists
function deleteFileIfExists($filePath) {
    if (file_exists($filePath)) {
        unlink($filePath);
        error_log("Deleted file: " . $filePath);
        return true;
    }
    return false;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_slider') {
        try {
            $title = $_POST['title'];
            $subtitle = $_POST['subtitle'];
            $description = $_POST['description'];
            $button1_text = $_POST['button1_text'];
            $button1_link = $_POST['button1_link'];
            $button2_text = $_POST['button2_text'];
            $button2_link = $_POST['button2_link'];
            $display_order = $_POST['display_order'];
            $status = $_POST['status'];
            
            // Handle image upload
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/slider/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = getFileExtension($_FILES['image']['name']);
                $filename = generateUniqueFilename('Slider', $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = '/slider/' . $filename;
                    error_log("Slider image uploaded: " . $image_url);
                } else {
                    throw new Exception("Failed to upload image");
                }
            } else {
                throw new Exception("Image is required");
            }
            
            $stmt = $pdo->prepare("INSERT INTO hero_sliders (title, subtitle, description, image_url, button1_text, button1_link, button2_text, button2_link, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $description, $image_url, $button1_text, $button1_link, $button2_text, $button2_link, $display_order, $status]);
            
            header("Location: " . url('admin/hero-sliders') . "?success=Slider added successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error adding slider: " . $e->getMessage());
            header("Location: " . url('admin/hero-sliders') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'update_slider') {
        try {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $subtitle = $_POST['subtitle'];
            $description = $_POST['description'];
            $button1_text = $_POST['button1_text'];
            $button1_link = $_POST['button1_link'];
            $button2_text = $_POST['button2_text'];
            $button2_link = $_POST['button2_link'];
            $display_order = $_POST['display_order'];
            $status = $_POST['status'];
            
            // Handle image upload
            $image_url = $_POST['current_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/slider/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = getFileExtension($_FILES['image']['name']);
                $filename = generateUniqueFilename('Slider', $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Delete old image
                    if (!empty($_POST['current_image'])) {
                        $old_file = $root_path . '/img' . $_POST['current_image'];
                        deleteFileIfExists($old_file);
                    }
                    
                    $image_url = '/slider/' . $filename;
                    error_log("Slider image updated: " . $image_url);
                }
            }
            
            $stmt = $pdo->prepare("UPDATE hero_sliders SET title = ?, subtitle = ?, description = ?, image_url = ?, button1_text = ?, button1_link = ?, button2_text = ?, button2_link = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $subtitle, $description, $image_url, $button1_text, $button1_link, $button2_text, $button2_link, $display_order, $status, $id]);
            
            header("Location: " . url('admin/hero-sliders') . "?success=Slider updated successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error updating slider: " . $e->getMessage());
            header("Location: " . url('admin/hero-sliders') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'delete_slider') {
        try {
            $id = $_POST['id'];
            
            // Get image URL before deleting
            $stmt = $pdo->prepare("SELECT image_url FROM hero_sliders WHERE id = ?");
            $stmt->execute([$id]);
            $slider = $stmt->fetch();
            
            if ($slider && !empty($slider['image_url'])) {
                $file_path = $root_path . '/img' . $slider['image_url'];
                deleteFileIfExists($file_path);
            }
            
            $stmt = $pdo->prepare("DELETE FROM hero_sliders WHERE id = ?");
            $stmt->execute([$id]);
            
            header("Location: " . url('admin/hero-sliders') . "?success=Slider deleted successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error deleting slider: " . $e->getMessage());
            header("Location: " . url('admin/hero-sliders') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Fetch all sliders
$stmt = $pdo->query("SELECT * FROM hero_sliders ORDER BY display_order, created_at DESC");
$sliders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Sliders Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
</head>
<body>
    <!-- Include admin sidebar -->
    <?php include_once 'components/admin-sidebar.php'; ?>
    
    <!-- Include admin navbar -->
    <?php include_once 'components/admin-navbar.php'; ?>

    <!-- Page Content -->
    <div class="container-fluid mt-4">
        <!-- Success Message -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Error Message -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Hero Sliders Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSliderModal">
                        <i class="fas fa-plus me-2"></i> Add New Slider
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Sliders List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sliders List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Buttons</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sliders as $slider): ?>
                                    <tr>
                                        <td><?= $slider['id'] ?></td>
                                        <td>
                                            <?php if (!empty($slider['image_url'])): ?>
                                                <img src="<?= img_url($slider['image_url']) ?>" alt="Slider Image" class="image-preview">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($slider['title']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($slider['subtitle']) ?></small>
                                        </td>
                                        <td>
                                            <small>
                                                <?= htmlspecialchars($slider['button1_text']) ?> → <?= htmlspecialchars($slider['button1_link']) ?><br>
                                                <?= htmlspecialchars($slider['button2_text']) ?> → <?= htmlspecialchars($slider['button2_link']) ?>
                                            </small>
                                        </td>
                                        <td><?= $slider['display_order'] ?></td>
                                        <td>
                                            <span class="badge <?= $slider['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= ucfirst($slider['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn edit" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editSliderModal"
                                                        data-id="<?= $slider['id'] ?>"
                                                        data-title="<?= htmlspecialchars($slider['title']) ?>"
                                                        data-subtitle="<?= htmlspecialchars($slider['subtitle']) ?>"
                                                        data-description="<?= htmlspecialchars($slider['description']) ?>"
                                                        data-image="<?= $slider['image_url'] ?>"
                                                        data-button1-text="<?= htmlspecialchars($slider['button1_text']) ?>"
                                                        data-button1-link="<?= htmlspecialchars($slider['button1_link']) ?>"
                                                        data-button2-text="<?= htmlspecialchars($slider['button2_text']) ?>"
                                                        data-button2-link="<?= htmlspecialchars($slider['button2_link']) ?>"
                                                        data-display-order="<?= $slider['display_order'] ?>"
                                                        data-status="<?= $slider['status'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete" onclick="confirmDelete('slider', <?= $slider['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Slider Modal -->
    <div class="modal fade" id="addSliderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Slider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_slider">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subtitle</label>
                                <input type="text" class="form-control" name="subtitle">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Slider Image *</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required onchange="previewImage(this, 'addImagePreview')">
                            <div class="mt-2">
                                <img id="addImagePreview" src="" class="image-preview" style="display: none;">
                            </div>
                            <small class="text-muted">Recommended size: 1920x1080px</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Button 1 Text</label>
                                <input type="text" class="form-control" name="button1_text" placeholder="Learn More">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Button 1 Link</label>
                                <input type="text" class="form-control" name="button1_link" placeholder="#programs">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Button 2 Text</label>
                                <input type="text" class="form-control" name="button2_text" placeholder="Contact Us">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Button 2 Link</label>
                                <input type="text" class="form-control" name="button2_link" placeholder="/contact">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0">
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
                        <button type="submit" class="btn btn-primary">Add Slider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Slider Modal -->
    <div class="modal fade" id="editSliderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Slider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_slider">
                        <input type="hidden" name="id" id="editSliderId">
                        <input type="hidden" name="current_image" id="editCurrentImage">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" id="editTitle" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subtitle</label>
                                <input type="text" class="form-control" name="subtitle" id="editSubtitle">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Slider Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this, 'editImagePreview')">
                            <div class="mt-2">
                                <img id="editImagePreview" src="" class="image-preview">
                            </div>
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Button 1 Text</label>
                                <input type="text" class="form-control" name="button1_text" id="editButton1Text">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Button 1 Link</label>
                                <input type="text" class="form-control" name="button1_link" id="editButton1Link">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Button 2 Text</label>
                                <input type="text" class="form-control" name="button2_text" id="editButton2Text">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Button 2 Link</label>
                                <input type="text" class="form-control" name="button2_link" id="editButton2Link">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editDisplayOrder">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="editStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Slider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_slider">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include_once 'components/admin-scripts.php'; ?>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                "pageLength": 25,
                "responsive": true
            });
            
            // Edit modal data
            $('#editSliderModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#editSliderId').val(button.data('id'));
                modal.find('#editTitle').val(button.data('title'));
                modal.find('#editSubtitle').val(button.data('subtitle'));
                modal.find('#editDescription').val(button.data('description'));
                modal.find('#editCurrentImage').val(button.data('image'));
                modal.find('#editButton1Text').val(button.data('button1-text'));
                modal.find('#editButton1Link').val(button.data('button1-link'));
                modal.find('#editButton2Text').val(button.data('button2-text'));
                modal.find('#editButton2Link').val(button.data('button2-link'));
                modal.find('#editDisplayOrder').val(button.data('display-order'));
                modal.find('#editStatus').val(button.data('status'));
                
                // Set image preview
                var imageUrl = '<?= img_url("") ?>' + button.data('image');
                modal.find('#editImagePreview').attr('src', imageUrl);
                modal.find('#editImagePreview').show();
            });
        });
        
        // Preview image
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onloadend = function() {
                preview.src = reader.result;
                preview.style.display = 'block';
            }
            
            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
        
        // Confirm delete
        function confirmDelete(type, id) {
            Swal.fire({
                title: 'Delete ' + type + '?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteId').value = id;
                    document.getElementById('deleteForm').submit();
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