<?php
// modules/Dashboard/views/testimonials-management.php
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
if (!$decoded->is_super_admin && !in_array('website.manage_testimonials', $decoded->permissions)) {
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
    
    if ($action === 'add_testimonial') {
        try {
            $name = $_POST['name'];
            $role = $_POST['role'];
            $content = $_POST['content'];
            $rating = $_POST['rating'];
            $display_order = $_POST['display_order'];
            $status = $_POST['status'];
            
            // Handle image upload (optional for testimonials)
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/testimonials/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = getFileExtension($_FILES['image']['name']);
                $filename = generateUniqueFilename('Testimonial', $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = '/testimonials/' . $filename;
                    error_log("Testimonial image uploaded: " . $image_url);
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO testimonials (name, role, content, image_url, rating, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $role, $content, $image_url, $rating, $display_order, $status]);
            
            header("Location: " . url('admin/testimonials') . "?success=Testimonial added successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error adding testimonial: " . $e->getMessage());
            header("Location: " . url('admin/testimonials') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'update_testimonial') {
        try {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $role = $_POST['role'];
            $content = $_POST['content'];
            $rating = $_POST['rating'];
            $display_order = $_POST['display_order'];
            $status = $_POST['status'];
            
            // Handle image upload
            $image_url = $_POST['current_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/testimonials/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = getFileExtension($_FILES['image']['name']);
                $filename = generateUniqueFilename('Testimonial', $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Delete old image
                    if (!empty($_POST['current_image'])) {
                        $old_file = $root_path . '/img' . $_POST['current_image'];
                        deleteFileIfExists($old_file);
                    }
                    
                    $image_url = '/testimonials/' . $filename;
                    error_log("Testimonial image updated: " . $image_url);
                }
            }
            
            $stmt = $pdo->prepare("UPDATE testimonials SET name = ?, role = ?, content = ?, image_url = ?, rating = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $role, $content, $image_url, $rating, $display_order, $status, $id]);
            
            header("Location: " . url('admin/testimonials') . "?success=Testimonial updated successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error updating testimonial: " . $e->getMessage());
            header("Location: " . url('admin/testimonials') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'delete_testimonial') {
        try {
            $id = $_POST['id'];
            
            // Get image URL before deleting
            $stmt = $pdo->prepare("SELECT image_url FROM testimonials WHERE id = ?");
            $stmt->execute([$id]);
            $testimonial = $stmt->fetch();
            
            if ($testimonial && !empty($testimonial['image_url'])) {
                $file_path = $root_path . '/img' . $testimonial['image_url'];
                deleteFileIfExists($file_path);
            }
            
            $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
            $stmt->execute([$id]);
            
            header("Location: " . url('admin/testimonials') . "?success=Testimonial deleted successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error deleting testimonial: " . $e->getMessage());
            header("Location: " . url('admin/testimonials') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Fetch all testimonials
$stmt = $pdo->query("SELECT * FROM testimonials ORDER BY display_order, created_at DESC");
$testimonials = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonials Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .star-rating {
            color: #ffc107;
            font-size: 14px;
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
                    <h2 class="mb-0">Testimonials Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
                        <i class="fas fa-plus me-2"></i> Add New Testimonial
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Testimonials List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Testimonials</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Name & Role</th>
                                        <th>Testimonial</th>
                                        <th>Rating</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($testimonials as $testimonial): ?>
                                    <tr>
                                        <td><?= $testimonial['id'] ?></td>
                                        <td>
                                            <?php if (!empty($testimonial['image_url'])): ?>
                                                <img src="<?= img_url($testimonial['image_url']) ?>" alt="Testimonial Image" class="image-preview">
                                            <?php else: ?>
                                                <div class="image-preview d-flex align-items-center justify-content-center bg-light">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($testimonial['name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($testimonial['role']) ?></small>
                                        </td>
                                        <td>
                                            <small><?= substr(htmlspecialchars($testimonial['content']), 0, 100) ?>...</small>
                                        </td>
                                        <td>
                                            <div class="star-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $testimonial['rating']): ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td><?= $testimonial['display_order'] ?></td>
                                        <td>
                                            <span class="badge <?= $testimonial['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= ucfirst($testimonial['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn edit" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editTestimonialModal"
                                                        data-id="<?= $testimonial['id'] ?>"
                                                        data-name="<?= htmlspecialchars($testimonial['name']) ?>"
                                                        data-role="<?= htmlspecialchars($testimonial['role']) ?>"
                                                        data-content="<?= htmlspecialchars($testimonial['content']) ?>"
                                                        data-image="<?= $testimonial['image_url'] ?>"
                                                        data-rating="<?= $testimonial['rating'] ?>"
                                                        data-display-order="<?= $testimonial['display_order'] ?>"
                                                        data-status="<?= $testimonial['status'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete" onclick="confirmDelete('testimonial', <?= $testimonial['id'] ?>)">
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

    <!-- Add Testimonial Modal -->
    <div class="modal fade" id="addTestimonialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_testimonial">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role/Title *</label>
                                <input type="text" class="form-control" name="role" placeholder="Parent of Grade 5 Student" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Testimonial Content *</label>
                            <textarea class="form-control" name="content" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rating</label>
                                <select class="form-select" name="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>" <?= $i == 5 ? 'selected' : '' ?>><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Profile Image (Optional)</label>
                            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this, 'addImagePreview')">
                            <div class="mt-2">
                                <img id="addImagePreview" src="" class="image-preview" style="display: none;">
                            </div>
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
                        <button type="submit" class="btn btn-primary">Add Testimonial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Testimonial Modal -->
    <div class="modal fade" id="editTestimonialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_testimonial">
                        <input type="hidden" name="id" id="editTestimonialId">
                        <input type="hidden" name="current_image" id="editCurrentImage">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name *</label>
                                <input type="text" class="form-control" name="name" id="editName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role/Title *</label>
                                <input type="text" class="form-control" name="role" id="editRole" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Testimonial Content *</label>
                            <textarea class="form-control" name="content" id="editContent" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rating</label>
                                <select class="form-select" name="rating" id="editRating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editDisplayOrder">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Profile Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this, 'editImagePreview')">
                            <div class="mt-2">
                                <img id="editImagePreview" src="" class="image-preview">
                            </div>
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="editStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Testimonial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_testimonial">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                "pageLength": 25,
                "responsive": true,
                "order": [[0, 'desc']]
            });
            
            // Edit modal data
            $('#editTestimonialModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#editTestimonialId').val(button.data('id'));
                modal.find('#editName').val(button.data('name'));
                modal.find('#editRole').val(button.data('role'));
                modal.find('#editContent').val(button.data('content'));
                modal.find('#editCurrentImage').val(button.data('image'));
                modal.find('#editRating').val(button.data('rating'));
                modal.find('#editDisplayOrder').val(button.data('display-order'));
                modal.find('#editStatus').val(button.data('status'));
                
                // Set image preview
                var imageUrl = '<?= img_url("") ?>' + button.data('image');
                if (button.data('image')) {
                    modal.find('#editImagePreview').attr('src', imageUrl);
                    modal.find('#editImagePreview').show();
                } else {
                    modal.find('#editImagePreview').attr('src', '');
                    modal.find('#editImagePreview').hide();
                }
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