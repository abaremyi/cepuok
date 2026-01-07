<?php
// modules/Dashboard/views/video-gallery-management.php
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

// Helper function to extract YouTube/Vimeo video ID
function extractVideoId($url, $type) {
    if ($type === 'youtube') {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
        return isset($matches[1]) ? $matches[1] : '';
    } elseif ($type === 'vimeo') {
        preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $matches);
        return isset($matches[1]) ? $matches[1] : '';
    }
    return '';
}

// Helper function to generate thumbnail URL
function generateThumbnailUrl($videoId, $type) {
    if ($type === 'youtube') {
        return 'https://img.youtube.com/vi/' . $videoId . '/maxresdefault.jpg';
    } elseif ($type === 'vimeo') {
        $data = @file_get_contents('https://vimeo.com/api/v2/video/' . $videoId . '.json');
        if ($data) {
            $data = json_decode($data, true);
            return isset($data[0]['thumbnail_large']) ? $data[0]['thumbnail_large'] : '';
        }
    }
    return '';
}

// Helper function to get video duration (for local uploads)
function getVideoDuration($filePath) {
    $cmd = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filePath);
    $duration = shell_exec($cmd);
    if ($duration) {
        $seconds = intval($duration);
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
    return null;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_video') {
        try {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $video_type = $_POST['video_type'];
            $category = $_POST['category'];
            $display_order = $_POST['display_order'];
            $status = $_POST['status'];
            
            // Handle video based on type
            if ($video_type === 'youtube' || $video_type === 'vimeo') {
                $video_url = $_POST['video_url'] ?? '';
                $video_id = extractVideoId($video_url, $video_type);
                
                if (empty($video_id)) {
                    throw new Exception("Invalid " . $video_type . " URL");
                }
                
                // Generate thumbnail
                $thumbnail_url = generateThumbnailUrl($video_id, $video_type);
                
            } elseif ($video_type === 'local') {
                // Handle local video upload
                if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
                    $upload_dir = $root_path . '/uploads/videos/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $allowed_extensions = ['mp4', 'webm', 'ogg', 'mov'];
                    $extension = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($extension, $allowed_extensions)) {
                        throw new Exception("Only MP4, WebM, OGG, and MOV files are allowed");
                    }
                    
                    $filename = uniqid('video_') . '.' . $extension;
                    $target_file = $upload_dir . $filename;
                    
                    if (!move_uploaded_file($_FILES['video_file']['tmp_name'], $target_file)) {
                        throw new Exception("Failed to upload video file");
                    }
                    
                    $video_url = '/uploads/videos/' . $filename;
                    
                    // Generate thumbnail from local video (if ffmpeg is available)
                    $thumbnail_url = '';
                    if (function_exists('shell_exec')) {
                        $thumbnail_filename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
                        $thumbnail_path = $root_path . '/uploads/videos/thumbnails/' . $thumbnail_filename;
                        $thumbnail_dir = dirname($thumbnail_path);
                        
                        if (!is_dir($thumbnail_dir)) {
                            mkdir($thumbnail_dir, 0755, true);
                        }
                        
                        // Capture thumbnail at 5 seconds
                        $cmd = "ffmpeg -i " . escapeshellarg($target_file) . " -ss 00:00:05 -vframes 1 -q:v 2 " . escapeshellarg($thumbnail_path) . " 2>&1";
                        shell_exec($cmd);
                        
                        if (file_exists($thumbnail_path)) {
                            $thumbnail_url = '/uploads/videos/thumbnails/' . $thumbnail_filename;
                        }
                    }
                    
                    // Get video duration
                    $duration = getVideoDuration($target_file);
                    
                } else {
                    throw new Exception("Video file is required for local upload");
                }
            } else {
                throw new Exception("Invalid video type");
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO video_gallery 
                (title, description, video_url, video_type, thumbnail_url, category, duration, display_order, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $title,
                $description,
                $video_url,
                $video_type,
                $thumbnail_url ?? '',
                $category,
                $duration ?? '0:00',
                $display_order,
                $status
            ]);
            
            header("Location: " . url('admin/video-gallery') . "?success=Video added successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error adding video: " . $e->getMessage());
            header("Location: " . url('admin/video-gallery') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'update_video') {
        try {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $video_type = $_POST['video_type'];
            $category = $_POST['category'];
            $display_order = $_POST['display_order'];
            $status = $_POST['status'];
            
            // Get current video data
            $stmt = $pdo->prepare("SELECT video_url, video_type, thumbnail_url FROM video_gallery WHERE id = ?");
            $stmt->execute([$id]);
            $current_video = $stmt->fetch();
            
            $video_url = $current_video['video_url'];
            $thumbnail_url = $current_video['thumbnail_url'];
            $duration = null;
            
            if ($video_type === 'youtube' || $video_type === 'vimeo') {
                $new_video_url = $_POST['video_url'] ?? '';
                if (!empty($new_video_url)) {
                    $video_id = extractVideoId($new_video_url, $video_type);
                    if (empty($video_id)) {
                        throw new Exception("Invalid " . $video_type . " URL");
                    }
                    
                    $video_url = $new_video_url;
                    $thumbnail_url = generateThumbnailUrl($video_id, $video_type);
                }
            } elseif ($video_type === 'local') {
                // Handle local video update
                if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
                    // Delete old video file
                    if (!empty($current_video['video_url'])) {
                        $old_file = $root_path . $current_video['video_url'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                        
                        // Delete old thumbnail
                        if (!empty($current_video['thumbnail_url'])) {
                            $old_thumbnail = $root_path . $current_video['thumbnail_url'];
                            if (file_exists($old_thumbnail)) {
                                unlink($old_thumbnail);
                            }
                        }
                    }
                    
                    // Upload new video
                    $upload_dir = $root_path . '/uploads/videos/';
                    $allowed_extensions = ['mp4', 'webm', 'ogg', 'mov'];
                    $extension = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($extension, $allowed_extensions)) {
                        throw new Exception("Only MP4, WebM, OGG, and MOV files are allowed");
                    }
                    
                    $filename = uniqid('video_') . '.' . $extension;
                    $target_file = $upload_dir . $filename;
                    
                    if (!move_uploaded_file($_FILES['video_file']['tmp_name'], $target_file)) {
                        throw new Exception("Failed to upload video file");
                    }
                    
                    $video_url = '/uploads/videos/' . $filename;
                    $duration = getVideoDuration($target_file);
                    
                    // Generate new thumbnail
                    if (function_exists('shell_exec')) {
                        $thumbnail_filename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
                        $thumbnail_path = $root_path . '/uploads/videos/thumbnails/' . $thumbnail_filename;
                        $thumbnail_dir = dirname($thumbnail_path);
                        
                        if (!is_dir($thumbnail_dir)) {
                            mkdir($thumbnail_dir, 0755, true);
                        }
                        
                        $cmd = "ffmpeg -i " . escapeshellarg($target_file) . " -ss 00:00:05 -vframes 1 -q:v 2 " . escapeshellarg($thumbnail_path) . " 2>&1";
                        shell_exec($cmd);
                        
                        if (file_exists($thumbnail_path)) {
                            $thumbnail_url = '/uploads/videos/thumbnails/' . $thumbnail_filename;
                        }
                    }
                }
            }
            
            $stmt = $pdo->prepare("
                UPDATE video_gallery 
                SET title = ?, description = ?, video_url = ?, video_type = ?, 
                    thumbnail_url = ?, category = ?, duration = COALESCE(?, duration),
                    display_order = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $title,
                $description,
                $video_url,
                $video_type,
                $thumbnail_url,
                $category,
                $duration,
                $display_order,
                $status,
                $id
            ]);
            
            header("Location: " . url('admin/video-gallery') . "?success=Video updated successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error updating video: " . $e->getMessage());
            header("Location: " . url('admin/video-gallery') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    if ($action === 'delete_video') {
        try {
            $id = $_POST['id'];
            
            // Get video data before deleting
            $stmt = $pdo->prepare("SELECT video_url, thumbnail_url FROM video_gallery WHERE id = ?");
            $stmt->execute([$id]);
            $video = $stmt->fetch();
            
            // Delete local files if they exist
            if ($video) {
                if (!empty($video['video_url'])) {
                    $video_path = $root_path . $video['video_url'];
                    if (file_exists($video_path)) {
                        unlink($video_path);
                    }
                }
                
                if (!empty($video['thumbnail_url'])) {
                    $thumb_path = $root_path . $video['thumbnail_url'];
                    if (file_exists($thumb_path)) {
                        unlink($thumb_path);
                    }
                }
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM video_gallery WHERE id = ?");
            $stmt->execute([$id]);
            
            header("Location: " . url('admin/video-gallery') . "?success=Video deleted successfully");
            exit;
        } catch (Exception $e) {
            error_log("Error deleting video: " . $e->getMessage());
            header("Location: " . url('admin/video-gallery') . "?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Fetch all videos
$stmt = $pdo->query("SELECT * FROM video_gallery ORDER BY display_order, created_at DESC");
$videos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Gallery Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .video-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .video-card:hover {
            transform: translateY(-5px);
        }
        .video-thumbnail {
            position: relative;
            height: 180px;
            overflow: hidden;
        }
        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.7);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            text-decoration: none;
        }
        .video-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .video-card:hover .video-actions {
            opacity: 1;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            left: 10px;
        }
        .video-type-badge {
            position: absolute;
            bottom: 10px;
            left: 10px;
        }
        .video-duration {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
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
                    <h2 class="mb-0">Video Gallery Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                        <i class="fas fa-plus me-2"></i> Add New Video
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Videos Grid -->
        <div class="row">
            <?php if (count($videos) > 0): ?>
                <?php foreach ($videos as $video): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card video-card">
                        <div class="position-relative">
                            <div class="video-thumbnail">
                                <?php if (!empty($video['thumbnail_url'])): ?>
                                    <img src="<?= $video['thumbnail_url'] ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                                <?php else: ?>
                                    <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 180px;">
                                        <i class="fas fa-video fa-3x text-light"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="<?= $video['video_url'] ?>" target="_blank" class="play-btn">
                                    <i class="fas fa-play"></i>
                                </a>
                                
                                <div class="video-actions">
                                    <button class="btn btn-sm btn-light me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editVideoModal"
                                            data-id="<?= $video['id'] ?>"
                                            data-title="<?= htmlspecialchars($video['title']) ?>"
                                            data-description="<?= htmlspecialchars($video['description']) ?>"
                                            data-video-url="<?= $video['video_url'] ?>"
                                            data-video-type="<?= $video['video_type'] ?>"
                                            data-thumbnail-url="<?= $video['thumbnail_url'] ?>"
                                            data-category="<?= $video['category'] ?>"
                                            data-duration="<?= $video['duration'] ?>"
                                            data-display-order="<?= $video['display_order'] ?>"
                                            data-status="<?= $video['status'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete('video', <?= $video['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <span class="category-badge badge bg-info">
                                    <?= ucfirst($video['category']) ?>
                                </span>
                                
                                <span class="video-type-badge badge bg-primary">
                                    <?= ucfirst($video['video_type']) ?>
                                </span>
                                
                                <?php if (!empty($video['duration'])): ?>
                                    <span class="video-duration"><?= $video['duration'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title mb-2"><?= htmlspecialchars($video['title']) ?></h6>
                            <?php if (!empty($video['description'])): ?>
                                <p class="card-text small text-muted mb-2">
                                    <?= substr(htmlspecialchars($video['description']), 0, 100) ?>...
                                </p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge <?= $video['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($video['status']) ?>
                                </span>
                                <div class="text-muted">
                                    <small><i class="fas fa-eye me-1"></i><?= $video['views'] ?> views</small>
                                    <small class="ms-2">Order: <?= $video['display_order'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-video fa-3x text-muted mb-3"></i>
                        <h4>No videos found</h4>
                        <p class="text-muted">Add your first video by clicking the "Add New Video" button.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Video Modal -->
    <div class="modal fade" id="addVideoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="addVideoForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_video">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Video Type *</label>
                                <select class="form-select" name="video_type" id="videoTypeSelect" required>
                                    <option value="youtube">YouTube</option>
                                    <option value="vimeo">Vimeo</option>
                                    <option value="local">Local Upload</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <!-- URL Input (for YouTube/Vimeo) -->
                        <div class="mb-3" id="videoUrlField">
                            <label class="form-label">Video URL *</label>
                            <input type="url" class="form-control" name="video_url" 
                                   placeholder="https://www.youtube.com/watch?v=..." id="videoUrlInput">
                            <small class="text-muted">Enter the full YouTube or Vimeo URL</small>
                        </div>
                        
                        <!-- File Upload (for Local) -->
                        <div class="mb-3" id="videoFileField" style="display: none;">
                            <label class="form-label">Video File *</label>
                            <input type="file" class="form-control" name="video_file" 
                                   accept="video/*" id="videoFileInput">
                            <small class="text-muted">Max size: 50MB. Supported formats: MP4, WebM, OGG, MOV</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category">
                                    <option value="general">General</option>
                                    <option value="academics">Academics</option>
                                    <option value="events">Events</option>
                                    <option value="sports">Sports</option>
                                    <option value="extracurricular">Extracurricular</option>
                                    <option value="campus">Campus</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0" min="0">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Preview -->
                        <div class="mb-3" id="videoPreview" style="display: none;">
                            <label class="form-label">Preview</label>
                            <div class="ratio ratio-16x9">
                                <iframe id="videoPreviewFrame" src="" frameborder="0" 
                                        allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Video</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Video Modal -->
    <div class="modal fade" id="editVideoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editVideoForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_video">
                        <input type="hidden" name="id" id="editVideoId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" id="editTitle" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Video Type *</label>
                                <select class="form-select" name="video_type" id="editVideoTypeSelect" required>
                                    <option value="youtube">YouTube</option>
                                    <option value="vimeo">Vimeo</option>
                                    <option value="local">Local Upload</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                        </div>
                        
                        <!-- URL Input (for YouTube/Vimeo) -->
                        <div class="mb-3" id="editVideoUrlField">
                            <label class="form-label">Video URL *</label>
                            <input type="url" class="form-control" name="video_url" 
                                   id="editVideoUrlInput">
                            <small class="text-muted">Enter the full YouTube or Vimeo URL</small>
                        </div>
                        
                        <!-- File Upload (for Local) -->
                        <div class="mb-3" id="editVideoFileField" style="display: none;">
                            <label class="form-label">Video File</label>
                            <input type="file" class="form-control" name="video_file" 
                                   accept="video/*" id="editVideoFileInput">
                            <small class="text-muted">Leave empty to keep current file. Max size: 50MB</small>
                            <div class="mt-2">
                                <small>Current file: <span id="currentFileName" class="text-info"></span></small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" id="editCategory">
                                    <option value="general">General</option>
                                    <option value="academics">Academics</option>
                                    <option value="events">Events</option>
                                    <option value="sports">Sports</option>
                                    <option value="extracurricular">Extracurricular</option>
                                    <option value="campus">Campus</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editDisplayOrder" value="0" min="0">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="editStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Thumbnail Preview -->
                        <div class="mb-3">
                            <label class="form-label">Thumbnail Preview</label>
                            <img id="editThumbnailPreview" src="" class="img-fluid rounded" style="max-height: 200px; display: none;">
                            <div id="noThumbnail" class="text-muted">No thumbnail available</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Video</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_video">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Toggle between URL and File upload based on video type
        $('#videoTypeSelect, #editVideoTypeSelect').on('change', function() {
            const type = $(this).val();
            const isEdit = $(this).attr('id') === 'editVideoTypeSelect';
            const prefix = isEdit ? 'edit' : '';
            
            if (type === 'local') {
                $('#' + prefix + 'videoUrlField').hide();
                $('#' + prefix + 'videoUrlInput').prop('required', false);
                $('#' + prefix + 'videoFileField').show();
                $('#' + prefix + 'videoFileInput').prop('required', true);
            } else {
                $('#' + prefix + 'videoUrlField').show();
                $('#' + prefix + 'videoUrlInput').prop('required', true);
                $('#' + prefix + 'videoFileField').hide();
                $('#' + prefix + 'videoFileInput').prop('required', false);
            }
            
            if (!isEdit) {
                $('#videoPreview').hide();
                $('#videoPreviewFrame').attr('src', '');
            }
        });
        
        // Preview YouTube/Vimeo videos when URL changes
        $('#videoUrlInput').on('input', function() {
            const url = $(this).val();
            const type = $('#videoTypeSelect').val();
            
            if ((type === 'youtube' || type === 'vimeo') && url) {
                let embedUrl = '';
                
                if (type === 'youtube') {
                    const videoId = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
                    if (videoId) {
                        embedUrl = `https://www.youtube.com/embed/${videoId[1]}`;
                    }
                } else if (type === 'vimeo') {
                    const videoId = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
                    if (videoId) {
                        embedUrl = `https://player.vimeo.com/video/${videoId[1]}`;
                    }
                }
                
                if (embedUrl) {
                    $('#videoPreviewFrame').attr('src', embedUrl);
                    $('#videoPreview').show();
                } else {
                    $('#videoPreview').hide();
                }
            } else {
                $('#videoPreview').hide();
            }
        });
        
        // Edit modal data
        $('#editVideoModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            
            // Set basic data
            modal.find('#editVideoId').val(button.data('id'));
            modal.find('#editTitle').val(button.data('title'));
            modal.find('#editDescription').val(button.data('description'));
            modal.find('#editVideoTypeSelect').val(button.data('video-type'));
            modal.find('#editVideoUrlInput').val(button.data('video-url'));
            modal.find('#editCategory').val(button.data('category'));
            modal.find('#editDisplayOrder').val(button.data('display-order'));
            modal.find('#editStatus').val(button.data('status'));
            
            // Set current file name for local videos
            if (button.data('video-type') === 'local') {
                const fileName = button.data('video-url').split('/').pop();
                modal.find('#currentFileName').text(fileName);
            }
            
            // Show thumbnail preview
            const thumbnailUrl = button.data('thumbnail-url');
            if (thumbnailUrl) {
                modal.find('#editThumbnailPreview').attr('src', thumbnailUrl).show();
                modal.find('#noThumbnail').hide();
            } else {
                modal.find('#editThumbnailPreview').hide();
                modal.find('#noThumbnail').show();
            }
            
            // Trigger video type change to show correct fields
            modal.find('#editVideoTypeSelect').trigger('change');
        });
        
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
        
        // File size validation
        $('#videoFileInput, #editVideoFileInput').on('change', function() {
            const file = this.files[0];
            const maxSize = 50 * 1024 * 1024; // 50MB in bytes
            
            if (file && file.size > maxSize) {
                Swal.fire({
                    title: 'File Too Large',
                    text: 'Maximum file size is 50MB',
                    icon: 'error'
                });
                this.value = '';
            }
        });
    </script>
</body>
</html>