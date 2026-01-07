<?php
// modules/Dashboard/views/gallery-management.php
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
if (!$decoded->is_super_admin && !in_array('website.manage_gallery', $decoded->permissions)) {
    header("Location: " . url('admin'));
    exit;
}

// Include database connection
require_once $root_path . "/config/database.php";
$pdo = Database::getConnection();

// Helper function to generate unique filename
function generateUniqueFilename($prefix, $extension) {
    $timestamp = time();
    $randomNumber = rand(100, 9999);
    return $prefix . '-' . $timestamp . '-' . $randomNumber . '.' . $extension;
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
    
    if ($action === 'add_gallery') {
        try {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $display_order = $_POST['display_order'];
            $status = $_POST['status'];
            
            // Handle image upload
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/gallery/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = getFileExtension($_FILES['image']['name']);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($extension, $allowed_extensions)) {
                    throw new Exception("Only JPG, PNG, GIF, and WebP images are allowed");
                }
                
                // Check file size (max 5MB)
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    throw new Exception("Image size must be less than 5MB");
                }
                
                $filename = generateUniqueFilename('Gallery', $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = '/gallery/' . $filename;
                    error_log("Gallery image uploaded: " . $image_url);
                } else {
                    throw new Exception("Failed to upload image");
                }
            } else {
                throw new Exception("Image is required");
            }
            
            $stmt = $pdo->prepare("INSERT INTO gallery_images (title, description, image_url, category, display_order, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $image_url, $category, $display_order, $status]);
            
            header("Location: " . url('admin/gallery') . "?success=Gallery image added successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error adding gallery image: " . $e->getMessage());
            header("Location: " . url('admin/gallery') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'update_gallery') {
        try {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $display_order = $_POST['display_order'];
            $status = $_POST['status'];
            
            // Handle image upload
            $image_url = $_POST['current_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/gallery/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = getFileExtension($_FILES['image']['name']);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($extension, $allowed_extensions)) {
                    throw new Exception("Only JPG, PNG, GIF, and WebP images are allowed");
                }
                
                // Check file size (max 5MB)
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    throw new Exception("Image size must be less than 5MB");
                }
                
                $filename = generateUniqueFilename('Gallery', $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Delete old image
                    if (!empty($_POST['current_image'])) {
                        $old_file = $root_path . '/img' . $_POST['current_image'];
                        deleteFileIfExists($old_file);
                    }
                    
                    $image_url = '/gallery/' . $filename;
                    error_log("Gallery image updated: " . $image_url);
                }
            }
            
            $stmt = $pdo->prepare("UPDATE gallery_images SET title = ?, description = ?, image_url = ?, category = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $description, $image_url, $category, $display_order, $status, $id]);
            
            header("Location: " . url('admin/gallery') . "?success=Gallery image updated successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error updating gallery image: " . $e->getMessage());
            header("Location: " . url('admin/gallery') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'delete_gallery') {
        try {
            $id = $_POST['id'];
            
            // Get image URL before deleting
            $stmt = $pdo->prepare("SELECT image_url FROM gallery_images WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetch();
            
            if ($image && !empty($image['image_url'])) {
                $file_path = $root_path . '/img' . $image['image_url'];
                deleteFileIfExists($file_path);
            }
            
            $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
            $stmt->execute([$id]);
            
            header("Location: " . url('admin/gallery') . "?success=Gallery image deleted successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error deleting gallery image: " . $e->getMessage());
            header("Location: " . url('admin/gallery') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    // Bulk Upload Handler
    if ($action === 'bulk_upload') {
        try {
            $category = $_POST['category'];
            $display_order_start = $_POST['display_order_start'];
            $default_title = $_POST['default_title'] ?: 'Gallery Image';
            $default_description = $_POST['default_description'];
            $status = $_POST['status'];
            
            if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
                throw new Exception("Please select at least one image");
            }
            
            $upload_dir = $root_path . '/img/gallery/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $uploaded_count = 0;
            $errors = [];
            $max_files = 10; // Limit bulk upload to 10 files at once
            
            $files_count = count($_FILES['images']['name']);
            if ($files_count > $max_files) {
                throw new Exception("Maximum $max_files files allowed in bulk upload");
            }
            
            for ($i = 0; $i < $files_count; $i++) {
                if ($_FILES['images']['error'][$i] === 0) {
                    $original_name = $_FILES['images']['name'][$i];
                    $tmp_name = $_FILES['images']['tmp_name'][$i];
                    $file_size = $_FILES['images']['size'][$i];
                    
                    $extension = getFileExtension($original_name);
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (!in_array($extension, $allowed_extensions)) {
                        $errors[] = "File '$original_name' has invalid type. Only JPG, PNG, GIF, and WebP are allowed.";
                        continue;
                    }
                    
                    // Check file size (max 5MB)
                    if ($file_size > 5 * 1024 * 1024) {
                        $errors[] = "File '$original_name' is too large. Maximum size is 5MB.";
                        continue;
                    }
                    
                    $filename = generateUniqueFilename('Bulk-' . ($i + 1), $extension);
                    $target_file = $upload_dir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $image_url = '/gallery/' . $filename;
                        
                        // Generate title with index
                        $title = $default_title . ' ' . ($display_order_start + $i + 1);
                        
                        $stmt = $pdo->prepare("INSERT INTO gallery_images (title, description, image_url, category, display_order, status) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $title,
                            $default_description,
                            $image_url,
                            $category,
                            $display_order_start + $i,
                            $status
                        ]);
                        
                        $uploaded_count++;
                    } else {
                        $errors[] = "Failed to upload '$original_name'";
                    }
                }
            }
            
            if ($uploaded_count > 0) {
                $message = "Successfully uploaded $uploaded_count image(s).";
                if (!empty($errors)) {
                    $message .= " Some errors occurred: " . implode(', ', $errors);
                }
                header("Location: " . url('admin/gallery') . "?success=" . urlencode($message));
            } else {
                throw new Exception("No images were uploaded. Errors: " . implode(', ', $errors));
            }
            exit;
        } catch (Exception $e) {
            error_log("Error in bulk upload: " . $e->getMessage());
            header("Location: " . url('admin/gallery') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    // Bulk Status Update Handler
    if ($action === 'bulk_status_update') {
        try {
            $ids = $_POST['image_ids'] ?? [];
            $status = $_POST['bulk_status'] ?? 'active';
            
            if (empty($ids)) {
                throw new Exception("No images selected");
            }
            
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("UPDATE gallery_images SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$status], $ids));
            
            $count = $stmt->rowCount();
            header("Location: " . url('admin/gallery') . "?success=Status updated for $count image(s)");
            exit;
        } catch (Exception $e) {
            error_log("Error in bulk status update: " . $e->getMessage());
            header("Location: " . url('admin/gallery') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    // Bulk Delete Handler
    if ($action === 'bulk_delete') {
        try {
            $ids = $_POST['image_ids'] ?? [];
            
            if (empty($ids)) {
                throw new Exception("No images selected");
            }
            
            // Get image URLs before deleting
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT image_url FROM gallery_images WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $images = $stmt->fetchAll();
            
            // Delete files
            foreach ($images as $image) {
                if (!empty($image['image_url'])) {
                    $file_path = $root_path . '/img' . $image['image_url'];
                    deleteFileIfExists($file_path);
                }
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            
            $count = $stmt->rowCount();
            header("Location: " . url('admin/gallery') . "?success=Deleted $count image(s) successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error in bulk delete: " . $e->getMessage());
            header("Location: " . url('admin/gallery') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Fetch all gallery images with search/filter
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT * FROM gallery_images WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY display_order, created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$gallery = $stmt->fetchAll();

// Get unique categories for filter
$categories_stmt = $pdo->query("SELECT DISTINCT category FROM gallery_images WHERE category IS NOT NULL ORDER BY category");
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - Mount Carmel School</title>
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
        .gallery-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .gallery-card:hover {
            transform: translateY(-5px);
        }
        .gallery-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .gallery-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .gallery-card:hover .gallery-actions {
            opacity: 1;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            left: 10px;
        }
        .image-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
            display: none;
        }
        .bulk-mode .image-checkbox {
            display: block;
        }
        .bulk-mode .category-badge {
            left: 40px;
        }
        .bulk-mode .gallery-actions {
            display: none;
        }
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
        .bulk-preview-img {
            height: 80px;
            object-fit: cover;
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
        
        <!-- Bulk Actions Bar (Hidden by default) -->
        <div id="bulkActionsBar" class="alert alert-info mb-3" style="display: none;">
            <div class="d-flex justify-content-between align-items-center">
                <span id="selectedCount">0 images selected</span>
                <div>
                    <button class="btn btn-sm btn-success me-2" onclick="showBulkStatusModal()">
                        <i class="fas fa-toggle-on me-1"></i> Change Status
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="confirmBulkDelete()">
                        <i class="fas fa-trash me-1"></i> Delete Selected
                    </button>
                    <button class="btn btn-sm btn-secondary ms-2" onclick="exitBulkMode()">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Photo Gallery Management</h2>
                    <div>
                        <button class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                            <i class="fas fa-upload me-2"></i> Bulk Upload
                        </button>
                        <button class="btn btn-outline-primary me-2" onclick="enterBulkMode()">
                            <i class="fas fa-tasks me-2"></i> Bulk Actions
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGalleryModal">
                            <i class="fas fa-plus me-2"></i> Add Single Image
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Search images..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="category" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter == $cat ? 'selected' : '' ?>>
                                    <?= ucfirst($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <div class="badge bg-light text-dark p-2">
                    Total: <?= count($gallery) ?> image(s)
                </div>
            </div>
        </div>
        
        <!-- Gallery Grid -->
        <div class="row" id="galleryContainer">
            <?php if (count($gallery) > 0): ?>
                <?php foreach ($gallery as $image): ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card gallery-card">
                        <div class="position-relative">
                            <input type="checkbox" class="form-check-input image-checkbox" 
                                   value="<?= $image['id'] ?>" onchange="updateSelectedCount()">
                            <img src="<?= img_url($image['image_url']) ?>" class="card-img-top" 
                                 alt="<?= htmlspecialchars($image['title']) ?>">
                            <span class="category-badge badge bg-info">
                                <?= ucfirst($image['category']) ?>
                            </span>
                            <div class="gallery-actions">
                                <button class="btn btn-sm btn-light me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editGalleryModal"
                                        data-id="<?= $image['id'] ?>"
                                        data-title="<?= htmlspecialchars($image['title']) ?>"
                                        data-description="<?= htmlspecialchars($image['description']) ?>"
                                        data-image="<?= $image['image_url'] ?>"
                                        data-category="<?= $image['category'] ?>"
                                        data-display-order="<?= $image['display_order'] ?>"
                                        data-status="<?= $image['status'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('image', <?= $image['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title mb-2"><?= htmlspecialchars($image['title']) ?></h6>
                            <?php if (!empty($image['description'])): ?>
                                <p class="card-text small text-muted mb-2">
                                    <?= substr(htmlspecialchars($image['description']), 0, 100) ?>...
                                </p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge <?= $image['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($image['status']) ?>
                                </span>
                                <div>
                                    <small class="text-muted">Order: <?= $image['display_order'] ?></small>
                                    <br>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($image['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h4>No gallery images found</h4>
                        <p class="text-muted">Add your first gallery image by clicking the "Add Single Image" button.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination (if needed) -->
        <?php if (count($gallery) > 12): ?>
        <div class="row">
            <div class="col-12">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Add Gallery Modal -->
    <div class="modal fade" id="addGalleryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Gallery Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_gallery">
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category">
                                    <option value="general">General</option>
                                    <option value="academics">Academics</option>
                                    <option value="events">Events</option>
                                    <option value="facilities">Facilities</option>
                                    <option value="campus">Campus</option>
                                    <option value="extracurricular">Extracurricular</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image *</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required 
                                   onchange="previewImage(this, 'addImagePreview')">
                            <div class="mt-2">
                                <img id="addImagePreview" src="" class="image-preview">
                            </div>
                            <small class="text-muted">Max size: 5MB. Supported formats: JPG, PNG, GIF, WebP</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive (Save as Draft)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Gallery Modal -->
    <div class="modal fade" id="editGalleryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Gallery Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_gallery">
                        <input type="hidden" name="id" id="editGalleryId">
                        <input type="hidden" name="current_image" id="editCurrentImage">
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" id="editTitle" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" id="editCategory">
                                    <option value="general">General</option>
                                    <option value="academics">Academics</option>
                                    <option value="events">Events</option>
                                    <option value="facilities">Facilities</option>
                                    <option value="campus">Campus</option>
                                    <option value="extracurricular">Extracurricular</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editDisplayOrder">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" 
                                   onchange="previewImage(this, 'editImagePreview')">
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
                        <button type="submit" class="btn btn-primary">Update Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Upload Modal -->
    <div class="modal fade" id="bulkUploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Image Upload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="bulkUploadForm">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You can upload up to 10 images at once. All images will be set with the same settings below.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Select Images *</label>
                            <input type="file" class="form-control" name="images[]" multiple accept="image/*" required 
                                   onchange="previewBulkImages(this)">
                            <small class="text-muted">Hold Ctrl (Cmd on Mac) to select multiple files. Max 10 files, 5MB each.</small>
                        </div>
                        
                        <div id="bulkPreview" class="row mb-3" style="display: none;">
                            <!-- Preview images will be inserted here -->
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" id="bulkCategory">
                                    <option value="general">General</option>
                                    <option value="academics">Academics</option>
                                    <option value="events">Events</option>
                                    <option value="facilities">Facilities</option>
                                    <option value="campus">Campus</option>
                                    <option value="extracurricular">Extracurricular</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order Start</label>
                                <input type="number" class="form-control" name="display_order_start" id="bulkDisplayOrder" value="0" min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Default Title</label>
                            <input type="text" class="form-control" name="default_title" id="bulkDefaultTitle" 
                                   placeholder="Gallery Image" value="Gallery Image">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Default Description</label>
                            <textarea class="form-control" name="default_description" id="bulkDefaultDescription" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="bulkStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive (Save as Draft)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="action" value="bulk_upload">Upload Images</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Status Update Modal -->
    <div class="modal fade" id="bulkStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status for Selected Images</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulkStatusForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="bulk_status_update">
                        <div id="bulkStatusImageIds"></div>
                        
                        <div class="mb-3">
                            <label class="form-label">Set Status</label>
                            <select class="form-select" name="bulk_status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This will update the status of all selected images.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_gallery">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <!-- Bulk Delete Form -->
    <form id="bulkDeleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="bulk_delete">
        <div id="bulkDeleteImageIds"></div>
    </form>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Edit modal data
        $('#editGalleryModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            
            modal.find('#editGalleryId').val(button.data('id'));
            modal.find('#editTitle').val(button.data('title'));
            modal.find('#editDescription').val(button.data('description'));
            modal.find('#editCurrentImage').val(button.data('image'));
            modal.find('#editCategory').val(button.data('category'));
            modal.find('#editDisplayOrder').val(button.data('display-order'));
            modal.find('#editStatus').val(button.data('status'));
            
            // Set image preview
            var imageUrl = '<?= img_url("") ?>' + button.data('image');
            modal.find('#editImagePreview').attr('src', imageUrl);
            modal.find('#editImagePreview').show();
        });
        
        // Preview image
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onloadend = function() {
                    preview.src = reader.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
        
        // Bulk image preview
        function previewBulkImages(input) {
            const preview = document.getElementById('bulkPreview');
            preview.innerHTML = '';
            
            if (input.files && input.files.length > 0) {
                preview.style.display = 'block';
                
                // Limit to 10 files
                const files = Array.from(input.files).slice(0, 10);
                
                files.forEach((file, index) => {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-2 mb-2';
                        
                        col.innerHTML = `
                            <div class="position-relative">
                                <img src="${e.target.result}" class="img-fluid rounded bulk-preview-img" 
                                     style="width: 100%; height: 80px; object-fit: cover;">
                                <span class="badge bg-secondary position-absolute top-0 end-0">${index + 1}</span>
                            </div>
                        `;
                        
                        preview.appendChild(col);
                    }
                    
                    reader.readAsDataURL(file);
                });
                
                // Update file count
                const fileCount = document.createElement('div');
                fileCount.className = 'col-12 mt-2';
                fileCount.innerHTML = `<small class="text-muted">${files.length} file(s) selected</small>`;
                preview.appendChild(fileCount);
            } else {
                preview.style.display = 'none';
            }
        }
        
        // Bulk Actions
        let isBulkMode = false;
        let selectedImages = [];
        
        function enterBulkMode() {
            isBulkMode = true;
            document.getElementById('galleryContainer').classList.add('bulk-mode');
            document.getElementById('bulkActionsBar').style.display = 'block';
            updateSelectedCount();
        }
        
        function exitBulkMode() {
            isBulkMode = false;
            document.getElementById('galleryContainer').classList.remove('bulk-mode');
            document.getElementById('bulkActionsBar').style.display = 'none';
            
            // Uncheck all checkboxes
            document.querySelectorAll('.image-checkbox').forEach(cb => {
                cb.checked = false;
            });
            selectedImages = [];
            updateSelectedCount();
        }
        
        function updateSelectedCount() {
            selectedImages = [];
            document.querySelectorAll('.image-checkbox:checked').forEach(cb => {
                selectedImages.push(cb.value);
            });
            
            const count = selectedImages.length;
            document.getElementById('selectedCount').textContent = `${count} image(s) selected`;
            
            if (count === 0 && isBulkMode) {
                exitBulkMode();
            }
        }
        
        function showBulkStatusModal() {
            if (selectedImages.length === 0) {
                Swal.fire('No Selection', 'Please select images first', 'warning');
                return;
            }
            
            const container = document.getElementById('bulkStatusImageIds');
            container.innerHTML = '';
            selectedImages.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'image_ids[]';
                input.value = id;
                container.appendChild(input);
            });
            
            const modal = new bootstrap.Modal(document.getElementById('bulkStatusModal'));
            modal.show();
        }
        
        function confirmBulkDelete() {
            if (selectedImages.length === 0) {
                Swal.fire('No Selection', 'Please select images to delete', 'warning');
                return;
            }
            
            Swal.fire({
                title: 'Delete Selected Images?',
                text: `This will delete ${selectedImages.length} image(s). This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const container = document.getElementById('bulkDeleteImageIds');
                    container.innerHTML = '';
                    selectedImages.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'image_ids[]';
                        input.value = id;
                        container.appendChild(input);
                    });
                    document.getElementById('bulkDeleteForm').submit();
                }
            });
        }
        
        // Confirm single delete
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
        
        // Select all images in bulk mode
        function selectAllImages() {
            document.querySelectorAll('.image-checkbox').forEach(cb => {
                cb.checked = true;
            });
            updateSelectedCount();
        }
        
        // Deselect all images in bulk mode
        function deselectAllImages() {
            document.querySelectorAll('.image-checkbox').forEach(cb => {
                cb.checked = false;
            });
            updateSelectedCount();
        }
    </script>
</body>
</html>