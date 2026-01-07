<?php
// modules/Dashboard/views/news-events-management.php
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
if (!$decoded->is_super_admin && !in_array('website.manage_news', $decoded->permissions)) {
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
    
    if ($action === 'add_news') {
        try {
            $title = $_POST['title'];
            $excerpt = $_POST['excerpt'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $author = $_POST['author'];
            $published_date = $_POST['published_date'];
            $featured = isset($_POST['featured']) ? 1 : 0;
            $status = $_POST['status'];
            
            // For events
            $event_location = $_POST['event_location'] ?? null;
            $event_time = $_POST['event_time'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            
            // Handle image upload
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/news/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = getFileExtension($_FILES['image']['name']);
                $filename = generateUniqueFilename('News', $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = '/news/' . $filename;
                    error_log("News image uploaded: " . $image_url);
                } else {
                    throw new Exception("Failed to upload image");
                }
            } else {
                throw new Exception("Image is required");
            }
            
            $stmt = $pdo->prepare("INSERT INTO news_events (title, excerpt, description, image_url, category, author, published_date, end_date, status, featured, event_location, event_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $excerpt, $description, $image_url, $category, $author, $published_date, $end_date, $status, $featured, $event_location, $event_time, $end_time]);
            
            header("Location: " . url('admin/news-events') . "?success=News/Event added successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error adding news: " . $e->getMessage());
            header("Location: " . url('admin/news-events') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'update_news') {
        try {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $excerpt = $_POST['excerpt'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $author = $_POST['author'];
            $published_date = $_POST['published_date'];
            $featured = isset($_POST['featured']) ? 1 : 0;
            $status = $_POST['status'];
            
            // For events
            $event_location = $_POST['event_location'] ?? null;
            $event_time = $_POST['event_time'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            
            // Handle image upload
            $image_url = $_POST['current_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/news/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = getFileExtension($_FILES['image']['name']);
                $filename = generateUniqueFilename('News', $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Delete old image
                    if (!empty($_POST['current_image'])) {
                        $old_file = $root_path . '/img' . $_POST['current_image'];
                        deleteFileIfExists($old_file);
                    }
                    
                    $image_url = '/news/' . $filename;
                    error_log("News image updated: " . $image_url);
                }
            }
            
            $stmt = $pdo->prepare("UPDATE news_events SET title = ?, excerpt = ?, description = ?, image_url = ?, category = ?, author = ?, published_date = ?, end_date = ?, status = ?, featured = ?, event_location = ?, event_time = ?, end_time = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $excerpt, $description, $image_url, $category, $author, $published_date, $end_date, $status, $featured, $event_location, $event_time, $end_time, $id]);
            
            header("Location: " . url('admin/news-events') . "?success=News/Event updated successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error updating news: " . $e->getMessage());
            header("Location: " . url('admin/news-events') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'delete_news') {
        try {
            $id = $_POST['id'];
            
            // Get image URL before deleting
            $stmt = $pdo->prepare("SELECT image_url FROM news_events WHERE id = ?");
            $stmt->execute([$id]);
            $news = $stmt->fetch();
            
            if ($news && !empty($news['image_url'])) {
                $file_path = $root_path . '/img' . $news['image_url'];
                deleteFileIfExists($file_path);
            }
            
            $stmt = $pdo->prepare("DELETE FROM news_events WHERE id = ?");
            $stmt->execute([$id]);
            
            header("Location: " . url('admin/news-events') . "?success=News/Event deleted successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error deleting news: " . $e->getMessage());
            header("Location: " . url('admin/news-events') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Fetch all news and events
$stmt = $pdo->query("SELECT * FROM news_events ORDER BY published_date DESC, created_at DESC");
$news = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Events Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- CKEditor (Free alternative to TinyMCE) -->
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    
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
                    <h2 class="mb-0">News & Events Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                        <i class="fas fa-plus me-2"></i> Add New
                    </button>
                </div>
            </div>
        </div>
        
        <!-- News List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All News & Events</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Featured</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($news as $item): ?>
                                    <tr>
                                        <td><?= $item['id'] ?></td>
                                        <td>
                                            <?php if (!empty($item['image_url'])): ?>
                                                <img src="<?= img_url($item['image_url']) ?>" alt="News Image" class="image-preview">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($item['title']) ?></strong><br>
                                            <small class="text-muted"><?= substr(htmlspecialchars($item['excerpt']), 0, 100) ?>...</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= ucfirst($item['category']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($item['published_date'])) ?></td>
                                        <td><?= htmlspecialchars($item['author']) ?></td>
                                        <td>
                                            <span class="badge <?= $item['status'] === 'published' ? 'bg-success' : ($item['status'] === 'draft' ? 'bg-warning' : 'bg-secondary') ?>">
                                                <?= ucfirst($item['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($item['featured']): ?>
                                                <span class="badge bg-primary">Featured</span>
                                            <?php else: ?>
                                                <span class="text-muted">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn edit" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editNewsModal"
                                                        data-id="<?= $item['id'] ?>"
                                                        data-title="<?= htmlspecialchars($item['title']) ?>"
                                                        data-excerpt="<?= htmlspecialchars($item['excerpt']) ?>"
                                                        data-description="<?= htmlspecialchars($item['description']) ?>"
                                                        data-image="<?= $item['image_url'] ?>"
                                                        data-category="<?= $item['category'] ?>"
                                                        data-author="<?= htmlspecialchars($item['author']) ?>"
                                                        data-published-date="<?= $item['published_date'] ?>"
                                                        data-end-date="<?= $item['end_date'] ?>"
                                                        data-event-location="<?= htmlspecialchars($item['event_location']) ?>"
                                                        data-event-time="<?= $item['event_time'] ?>"
                                                        data-end-time="<?= $item['end_time'] ?>"
                                                        data-featured="<?= $item['featured'] ?>"
                                                        data-status="<?= $item['status'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete" onclick="confirmDelete('item', <?= $item['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <a href="<?= url('news') ?>" target="_blank" class="action-btn view" title="View on website">
                                                    <i class="fas fa-eye"></i>
                                                </a>
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

    <!-- Add News Modal -->
    <div class="modal fade" id="addNewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add News/Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_news">
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category" id="addCategory" required>
                                    <option value="news">School News</option>
                                    <option value="event">Event</option>
                                    <option value="announcement">Announcement</option>
                                    <option value="achievement">Achievement</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Excerpt/Summary *</label>
                            <textarea class="form-control" name="excerpt" rows="2" required></textarea>
                            <small class="text-muted">Brief summary (appears in listings)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Full Description *</label>
                            <textarea class="form-control" name="description" id="addDescription" rows="6" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Featured Image *</label>
                                <input type="file" class="form-control" name="image" accept="image/*" required onchange="previewImage(this, 'addImagePreview')">
                                <div class="mt-2">
                                    <img id="addImagePreview" src="" class="image-preview" style="display: none;">
                                </div>
                                <small class="text-muted">Recommended size: 800x600px</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Author *</label>
                                <input type="text" class="form-control" name="author" value="Admin" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Published Date *</label>
                                <input type="date" class="form-control" name="published_date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="published" selected>Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Event specific fields -->
                        <div id="addEventFields" style="display: none;">
                            <h6 class="mt-3 mb-3">Event Details</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Location</label>
                                    <input type="text" class="form-control" name="event_location" placeholder="e.g., School Campus">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Time</label>
                                    <input type="time" class="form-control" name="event_time">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end_date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Time</label>
                                    <input type="time" class="form-control" name="end_time">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="featured" id="featured">
                                    <label class="form-check-label" for="featured">
                                        Mark as Featured
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add News/Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit News Modal -->
    <div class="modal fade" id="editNewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit News/Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_news">
                        <input type="hidden" name="id" id="editNewsId">
                        <input type="hidden" name="current_image" id="editCurrentImage">
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" id="editTitle" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category" id="editCategory" required>
                                    <option value="news">School News</option>
                                    <option value="event">Event</option>
                                    <option value="announcement">Announcement</option>
                                    <option value="achievement">Achievement</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Excerpt/Summary *</label>
                            <textarea class="form-control" name="excerpt" id="editExcerpt" rows="2" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Full Description *</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="6" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Featured Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this, 'editImagePreview')">
                                <div class="mt-2">
                                    <img id="editImagePreview" src="" class="image-preview">
                                </div>
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Author *</label>
                                <input type="text" class="form-control" name="author" id="editAuthor" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Published Date *</label>
                                <input type="date" class="form-control" name="published_date" id="editPublishedDate" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" id="editStatus" required>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Event specific fields -->
                        <div id="editEventFields" style="display: none;">
                            <h6 class="mt-3 mb-3">Event Details</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Location</label>
                                    <input type="text" class="form-control" name="event_location" id="editEventLocation">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Time</label>
                                    <input type="time" class="form-control" name="event_time" id="editEventTime">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end_date" id="editEndDate">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Time</label>
                                    <input type="time" class="form-control" name="end_time" id="editEndTime">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="featured" id="editFeatured">
                                    <label class="form-check-label" for="editFeatured">
                                        Mark as Featured
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update News/Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_news">
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
                "responsive": true,
                "order": [[0, 'desc']]
            });
            
            // Initialize CKEditor
            CKEDITOR.replace('addDescription', {
                toolbar: [
                    ['Bold', 'Italic', 'Underline', 'Strike'],
                    ['NumberedList', 'BulletedList'],
                    ['Link', 'Unlink'],
                    ['Format', 'Font', 'FontSize'],
                    ['TextColor', 'BGColor'],
                    ['JustifyLeft', 'JustifyCenter', 'JustifyRight'],
                    ['RemoveFormat', 'Source']
                ],
                height: 300
            });
            
            CKEDITOR.replace('editDescription', {
                toolbar: [
                    ['Bold', 'Italic', 'Underline', 'Strike'],
                    ['NumberedList', 'BulletedList'],
                    ['Link', 'Unlink'],
                    ['Format', 'Font', 'FontSize'],
                    ['TextColor', 'BGColor'],
                    ['JustifyLeft', 'JustifyCenter', 'JustifyRight'],
                    ['RemoveFormat', 'Source']
                ],
                height: 300
            });
            
            // Show/hide event fields based on category
            $('#addCategory').change(function() {
                if ($(this).val() === 'event') {
                    $('#addEventFields').show();
                } else {
                    $('#addEventFields').hide();
                }
            });
            
            $('#editCategory').change(function() {
                if ($(this).val() === 'event') {
                    $('#editEventFields').show();
                } else {
                    $('#editEventFields').hide();
                }
            });
            
            // Edit modal data
            $('#editNewsModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#editNewsId').val(button.data('id'));
                modal.find('#editTitle').val(button.data('title'));
                modal.find('#editExcerpt').val(button.data('excerpt'));
                CKEDITOR.instances.editDescription.setData(button.data('description'));
                modal.find('#editCurrentImage').val(button.data('image'));
                modal.find('#editCategory').val(button.data('category'));
                modal.find('#editAuthor').val(button.data('author'));
                modal.find('#editPublishedDate').val(button.data('published-date'));
                modal.find('#editEndDate').val(button.data('end-date'));
                modal.find('#editEventLocation').val(button.data('event-location'));
                modal.find('#editEventTime').val(button.data('event-time'));
                modal.find('#editEndTime').val(button.data('end-time'));
                modal.find('#editStatus').val(button.data('status'));
                modal.find('#editFeatured').prop('checked', button.data('featured') == 1);
                
                // Set image preview
                var imageUrl = '<?= img_url("") ?>' + button.data('image');
                modal.find('#editImagePreview').attr('src', imageUrl);
                if (button.data('image')) {
                    modal.find('#editImagePreview').show();
                } else {
                    modal.find('#editImagePreview').hide();
                }
                
                // Show/hide event fields
                if (button.data('category') === 'event') {
                    modal.find('#editEventFields').show();
                } else {
                    modal.find('#editEventFields').hide();
                }
            });
            
            // Edit category change
            $('#editCategory').change(function() {
                if ($(this).val() === 'event') {
                    $('#editEventFields').show();
                } else {
                    $('#editEventFields').hide();
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