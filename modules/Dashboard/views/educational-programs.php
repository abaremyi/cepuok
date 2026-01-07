<?php
// modules/Dashboard/views/educational-programs.php
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
    
    if ($action === 'add_program') {
        try {
            $title = $_POST['title'];
            $subtitle = $_POST['subtitle'] ?? '';
            $description = $_POST['description'];
            $icon_class = $_POST['icon_class'] ?? 'fas fa-graduation-cap';
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            // Handle image upload
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/programs/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $filename = 'program-' . time() . '-' . rand(100, 999) . '.' . $extension;
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = '/programs/' . $filename;
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO educational_programs (title, subtitle, description, icon_class, image_url, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $description, $icon_class, $image_url, $display_order, $status]);
            
            header("Location: " . url('admin/educational-programs') . "?success=Program added successfully");
            exit;
        } catch (Exception $e) {
            header("Location: " . url('admin/educational-programs') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'update_program') {
        try {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $subtitle = $_POST['subtitle'] ?? '';
            $description = $_POST['description'];
            $icon_class = $_POST['icon_class'] ?? 'fas fa-graduation-cap';
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            // Check if new image uploaded
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/programs/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $filename = 'program-' . time() . '-' . rand(100, 999) . '.' . $extension;
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Delete old image if exists
                    $stmt = $pdo->prepare("SELECT image_url FROM educational_programs WHERE id = ?");
                    $stmt->execute([$id]);
                    $old_image = $stmt->fetchColumn();
                    
                    if ($old_image) {
                        $old_file = $root_path . '/img' . $old_image;
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    
                    $image_url = '/programs/' . $filename;
                    $stmt = $pdo->prepare("UPDATE educational_programs SET title = ?, subtitle = ?, description = ?, icon_class = ?, image_url = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$title, $subtitle, $description, $icon_class, $image_url, $display_order, $status, $id]);
                }
            } else {
                $stmt = $pdo->prepare("UPDATE educational_programs SET title = ?, subtitle = ?, description = ?, icon_class = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$title, $subtitle, $description, $icon_class, $display_order, $status, $id]);
            }
            
            header("Location: " . url('admin/educational-programs') . "?success=Program updated successfully");
            exit;
        } catch (Exception $e) {
            header("Location: " . url('admin/educational-programs') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'delete_program') {
        try {
            $id = $_POST['id'];
            
            // Delete associated image
            $stmt = $pdo->prepare("SELECT image_url FROM educational_programs WHERE id = ?");
            $stmt->execute([$id]);
            $image_url = $stmt->fetchColumn();
            
            if ($image_url) {
                $file = $root_path . '/img' . $image_url;
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            
            $stmt = $pdo->prepare("DELETE FROM educational_programs WHERE id = ?");
            $stmt->execute([$id]);
            
            header("Location: " . url('admin/educational-programs') . "?success=Program deleted successfully");
            exit;
        } catch (Exception $e) {
            header("Location: " . url('admin/educational-programs') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Fetch all programs
$stmt = $pdo->query("SELECT * FROM educational_programs ORDER BY display_order, created_at DESC");
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// List of FontAwesome icons for selection
$fontawesome_icons = [
    'fas fa-baby' => 'Baby',
    'fas fa-child' => 'Child',
    'fas fa-graduation-cap' => 'Graduation Cap',
    'fas fa-book' => 'Book',
    'fas fa-flask' => 'Flask',
    'fas fa-paint-brush' => 'Paint Brush',
    'fas fa-music' => 'Music',
    'fas fa-futbol' => 'Soccer',
    'fas fa-laptop' => 'Laptop',
    'fas fa-language' => 'Language',
    'fas fa-users' => 'Users',
    'fas fa-brain' => 'Brain',
    'fas fa-calculator' => 'Calculator',
    'fas fa-atom' => 'Atom',
    'fas fa-history' => 'History'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educational Programs Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .program-card-admin {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
            height: 100%;
        }
        .program-card-admin:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .program-icon-admin {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 20px auto 15px;
        }
        .program-image-admin {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .icon-preview {
            font-size: 24px;
            color: #667eea;
            margin-right: 10px;
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
        <!-- Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Educational Programs Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                        <i class="fas fa-plus me-2"></i> Add New Program
                    </button>
                </div>
            </div>
        </div>

        <!-- Programs Grid -->
        <div class="row">
            <?php if (empty($programs)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                            <h4>No programs added yet</h4>
                            <p class="text-muted">Click the "Add New Program" button to add your first educational program.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($programs as $program): ?>
                    <div class="col-md-4 mb-4">
                        <div class="program-card-admin">
                            <?php if (!empty($program['image_url'])): ?>
                                <img src="<?= img_url($program['image_url']) ?>" class="program-image-admin" alt="<?= htmlspecialchars($program['title']) ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="program-icon-admin">
                                    <i class="<?= $program['icon_class'] ?>"></i>
                                </div>
                                <h5 class="card-title text-center"><?= htmlspecialchars($program['title']) ?></h5>
                                <?php if (!empty($program['subtitle'])): ?>
                                    <h6 class="card-subtitle mb-2 text-muted text-center"><?= htmlspecialchars($program['subtitle']) ?></h6>
                                <?php endif; ?>
                                <p class="card-text"><?= substr(strip_tags($program['description']), 0, 100) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge <?= $program['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($program['status']) ?>
                                    </span>
                                    <span class="text-muted">Order: <?= $program['display_order'] ?></span>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editProgramModal"
                                            data-program-id="<?= $program['id'] ?>"
                                            data-title="<?= htmlspecialchars($program['title']) ?>"
                                            data-subtitle="<?= htmlspecialchars($program['subtitle']) ?>"
                                            data-description="<?= htmlspecialchars($program['description']) ?>"
                                            data-icon-class="<?= $program['icon_class'] ?>"
                                            data-display-order="<?= $program['display_order'] ?>"
                                            data-status="<?= $program['status'] ?>"
                                            data-image-url="<?= $program['image_url'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete(<?= $program['id'] ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Program Modal -->
    <div class="modal fade" id="addProgramModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Educational Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_program">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Program Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subtitle (Optional)</label>
                                <input type="text" class="form-control" name="subtitle">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="5" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon</label>
                                <select class="form-select" name="icon_class" id="iconSelect">
                                    <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                        <option value="<?= $icon ?>" data-icon="<?= $icon ?>">
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="mt-2">
                                    <small class="text-muted">Selected Icon:</small>
                                    <div id="iconPreview" class="icon-preview">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image (Optional)</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <small class="text-muted">Recommended size: 800x600px</small>
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
                        <button type="submit" class="btn btn-primary">Add Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Program Modal -->
    <div class="modal fade" id="editProgramModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Educational Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_program">
                        <input type="hidden" name="id" id="editProgramId">
                        <input type="hidden" name="current_image" id="editCurrentImage">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Program Title *</label>
                                <input type="text" class="form-control" name="title" id="editTitle" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subtitle (Optional)</label>
                                <input type="text" class="form-control" name="subtitle" id="editSubtitle">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="5" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon</label>
                                <select class="form-select" name="icon_class" id="editIconClass">
                                    <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                        <option value="<?= $icon ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="mt-2">
                                    <small class="text-muted">Current Icon:</small>
                                    <div id="editIconPreview" class="icon-preview">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image (Optional)</label>
                                <input type="file" class="form-control" name="image" accept="image/*" onchange="previewEditImage(this)">
                                <div class="mt-2">
                                    <img id="editImagePreview" src="" class="img-thumbnail" style="max-width: 150px; display: none;">
                                    <div id="editImagePlaceholder" class="text-muted small">No image selected</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editDisplayOrder" value="0">
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
                        <button type="submit" class="btn btn-primary">Update Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_program">
        <input type="hidden" name="id" id="deleteProgramId">
    </form>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Icon preview for add modal
        $('#iconSelect').change(function() {
            const selectedIcon = $(this).find(':selected').val();
            $('#iconPreview').html(`<i class="${selectedIcon}"></i>`);
        });

        // Edit modal data
        $('#editProgramModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            
            modal.find('#editProgramId').val(button.data('program-id'));
            modal.find('#editTitle').val(button.data('title'));
            modal.find('#editSubtitle').val(button.data('subtitle'));
            modal.find('#editDescription').val(button.data('description'));
            modal.find('#editIconClass').val(button.data('icon-class'));
            modal.find('#editDisplayOrder').val(button.data('display-order'));
            modal.find('#editStatus').val(button.data('status'));
            modal.find('#editCurrentImage').val(button.data('image-url'));
            
            // Update icon preview
            const iconClass = button.data('icon-class');
            $('#editIconPreview').html(`<i class="${iconClass}"></i>`);
            
            // Update image preview
            const imageUrl = '<?= img_url("") ?>' + button.data('image-url');
            const previewImg = modal.find('#editImagePreview');
            const placeholder = modal.find('#editImagePlaceholder');
            
            if (button.data('image-url')) {
                previewImg.attr('src', imageUrl);
                previewImg.show();
                placeholder.hide();
            } else {
                previewImg.hide();
                placeholder.show();
            }
        });

        // Preview image for edit
        function previewEditImage(input) {
            const preview = document.getElementById('editImagePreview');
            const placeholder = document.getElementById('editImagePlaceholder');
            const file = input.files[0];
            const reader = new FileReader();

            reader.onloadend = function() {
                preview.src = reader.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
                placeholder.style.display = 'block';
            }
        }

        // Confirm delete
        function confirmDelete(programId) {
            Swal.fire({
                title: 'Delete Program?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#deleteProgramId').val(programId);
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