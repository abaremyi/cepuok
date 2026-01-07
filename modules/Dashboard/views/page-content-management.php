<?php
// modules/Dashboard/views/page-content-management.php
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

// Define pages and sections
$pages = [
    'home' => [
        'name' => 'Home Page',
        'sections' => [
            'welcome_section_title' => 'Welcome Section Title',
            'welcome_section_video' => 'Welcome Section Video URL',
            'welcome_intro_head' => 'Welcome Introduction Headline',
            'welcome_intro_paragraph' => 'Welcome Introduction Paragraph',
            'welcome_quote_title' => 'Welcome Quote Title',
            'welcome_quote_content' => 'Welcome Quote Content',
            'dir_letter_section_title' => 'Director Letter Section Title',
            'director_photo' => 'Director Photo',
            'director_name' => 'Director Name',
            'director_role' => 'Director Role/Position',
            'letter_text_title' => 'Letter Text Title',
            'letter_greeting' => 'Letter Greeting',
            'letter_paragraph_1' => 'Letter Paragraph 1',
            'letter_paragraph_2' => 'Letter Paragraph 2',
            'letter_paragraph_3' => 'Letter Paragraph 3',
            'letter_signature_name' => 'Signature Name',
            'letter_signature_role' => 'Signature Role'
        ]
    ],
    'about' => [
        'name' => 'About Us Page',
        'sections' => [
            'who_we_are' => 'Who We Are',
            'mission' => 'Mission',
            'vision' => 'Vision',
            'philosophy' => 'Philosophy',
            'history' => 'History',
            'core_values' => 'Core Values'
        ]
    ]
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_content') {
        try {
            $page_name = $_POST['page_name'];
            $section_name = $_POST['section_name'];
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'];
            $display_order = $_POST['display_order'] ?? 0;
            $status = $_POST['status'];
            
            // Handle image upload if provided
            $image_url = $_POST['current_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/';
                
                // Create subdirectory based on page if needed
                if (!empty($page_name) && $page_name != 'home') {
                    $upload_dir .= $page_name . '/';
                }
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = getFileExtension($_FILES['image']['name']);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($extension, $allowed_extensions)) {
                    throw new Exception('Invalid file type. Allowed: JPG, PNG, GIF, WebP');
                }
                
                $filename = generateUniqueFilename($page_name . '-' . $section_name, $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Delete old image
                    if (!empty($_POST['current_image'])) {
                        $old_file = $root_path . '/img' . $_POST['current_image'];
                        deleteFileIfExists($old_file);
                    }
                    
                    $image_url = '/' . $page_name . '/' . $filename;
                    if ($page_name == 'home') {
                        $image_url = '/' . $filename;
                    }
                    error_log("Page content image uploaded: " . $image_url);
                }
            }
            
            // Sanitize content (Quill.js already provides clean HTML)
            $content = htmlspecialchars_decode($content); // Decode since we're storing HTML
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            
            // Check if content exists
            $stmt = $pdo->prepare("SELECT id FROM page_content WHERE page_name = ? AND section_name = ?");
            $stmt->execute([$page_name, $section_name]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing content
                $stmt = $pdo->prepare("UPDATE page_content SET title = ?, content = ?, image_url = ?, display_order = ?, status = ?, updated_at = NOW() WHERE page_name = ? AND section_name = ?");
                $stmt->execute([$title, $content, $image_url, $display_order, $status, $page_name, $section_name]);
            } else {
                // Insert new content
                $stmt = $pdo->prepare("INSERT INTO page_content (page_name, section_name, title, content, image_url, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$page_name, $section_name, $title, $content, $image_url, $display_order, $status]);
            }
            
            // Set success message in session for SweetAlert
            session_start();
            $_SESSION['success_message'] = 'Content updated successfully!';
            $_SESSION['success_action'] = 'update';
            
            header("Location: " . url('admin/page-content') . "?page=" . urlencode($page_name));
            exit;
            
        } catch (Exception $e) {
            error_log("Error updating page content: " . $e->getMessage());
            
            // Set error message in session for SweetAlert
            session_start();
            $_SESSION['error_message'] = 'Failed to update content: ' . $e->getMessage();
            
            header("Location: " . url('admin/page-content') . "?page=" . urlencode($page_name));
            exit;
        }
    }
}

// Get current page
$current_page = $_GET['page'] ?? 'home';
if (!array_key_exists($current_page, $pages)) {
    $current_page = 'home';
}

// Fetch all content for current page
$stmt = $pdo->prepare("SELECT * FROM page_content WHERE page_name = ? ORDER BY display_order");
$stmt->execute([$current_page]);
$page_content = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create associative array for easy access
$content_by_section = [];
foreach ($page_content as $content) {
    $content_by_section[$content['section_name']] = $content;
}

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
    <title>Page Content Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Quill.js CSS -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .section-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .content-preview {
            max-height: 150px;
            overflow: hidden;
            position: relative;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .content-preview:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: linear-gradient(transparent, rgba(255,255,255,0.9));
        }
        .img-thumbnail-custom {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }
        .badge-custom {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        .edit-btn {
            transition: all 0.3s;
        }
        .edit-btn:hover {
            transform: scale(1.05);
        }
        .section-empty {
            color: #9ca3af;
            font-style: italic;
        }
        .video-url-preview {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        /* Quill Editor Custom Styles */
        .ql-toolbar {
            border-radius: 8px 8px 0 0;
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
        }
        .ql-container {
            border-radius: 0 0 8px 8px;
            border: 1px solid #e5e7eb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 1rem;
            min-height: 200px;
        }
        .ql-editor {
            min-height: 200px;
        }
        .ql-editor.ql-blank::before {
            color: #9ca3af;
            font-style: normal;
            font-size: 0.9rem;
        }
        /* Ensure Quill editor fits in modal */
        .modal-body .ql-container {
            min-height: 150px;
        }
        .modal-body .ql-editor {
            min-height: 150px;
        }
        .editor-container {
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
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
                    <h2 class="mb-0">Page Content Management</h2>
                    <div>
                        <span class="badge bg-primary me-2">Page: <?= htmlspecialchars($pages[$current_page]['name']) ?></span>
                        <span class="badge bg-info">Sections: <?= count($pages[$current_page]['sections']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Select Page</h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <?php foreach ($pages as $page_key => $page_info): ?>
                                <a href="<?= url('admin/page-content') ?>?page=<?= $page_key ?>" 
                                   class="btn <?= $current_page == $page_key ? 'btn-primary' : 'btn-outline-primary' ?>">
                                    <i class="fas fa-file-alt me-2"></i><?= $page_info['name'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Sections -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= $pages[$current_page]['name'] ?> - Sections</h5>
                        <p class="text-muted mb-0 small">Click Edit to modify each section's content</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($pages[$current_page]['sections'] as $section_key => $section_name): ?>
                                <?php
                                $content = $content_by_section[$section_key] ?? null;
                                $content_text = $content['content'] ?? '';
                                $content_title = $content['title'] ?? $section_name;
                                $content_image = $content['image_url'] ?? '';
                                $content_status = $content['status'] ?? 'active';
                                $content_order = $content['display_order'] ?? 0;
                                
                                // Check if it's a video URL section
                                $is_video_section = strpos($section_key, 'video') !== false;
                                ?>
                                
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card section-card">
                                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                            <h6 class="mb-0 text-dark">
                                                <i class="fas fa-edit me-2 text-primary"></i>
                                                <?= $section_name ?>
                                            </h6>
                                            <button class="btn btn-sm btn-primary edit-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editContentModal"
                                                    data-section-key="<?= $section_key ?>"
                                                    data-section-name="<?= htmlspecialchars($section_name) ?>"
                                                    data-page-name="<?= $current_page ?>"
                                                    data-title="<?= htmlspecialchars($content_title) ?>"
                                                    data-content="<?= htmlspecialchars($content_text) ?>"
                                                    data-image="<?= htmlspecialchars($content_image) ?>"
                                                    data-display-order="<?= $content_order ?>"
                                                    data-status="<?= $content_status ?>">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex">
                                                <?php if (!empty($content_image)): ?>
                                                    <div class="me-3">
                                                        <img src="<?= img_url($content_image) ?>" 
                                                             class="img-thumbnail-custom" 
                                                             alt="<?= htmlspecialchars($content_title) ?>"
                                                             onerror="this.src='<?= img_url('placeholder.jpg') ?>'">
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="flex-grow-1">
                                                    <?php if (!empty($content_title) && $content_title != $section_name): ?>
                                                        <h6 class="mb-2 text-dark"><?= htmlspecialchars($content_title) ?></h6>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($content_text)): ?>
                                                        <?php if ($is_video_section): ?>
                                                            <div class="video-url-preview" title="<?= htmlspecialchars($content_text) ?>">
                                                                <i class="fas fa-video me-1 text-danger"></i>
                                                                <?= htmlspecialchars(substr($content_text, 0, 40)) ?>...
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="content-preview mb-2">
                                                                <?= strip_tags(html_entity_decode($content_text)) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="section-empty mb-2">
                                                            <i class="fas fa-exclamation-circle me-1"></i>
                                                            No content yet
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                                        <span class="badge <?= $content_status === 'active' ? 'bg-success' : 'bg-secondary' ?> badge-custom">
                                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                            <?= ucfirst($content_status) ?>
                                                        </span>
                                                        <span class="text-muted small">
                                                            <i class="fas fa-sort-numeric-down me-1"></i>
                                                            Order: <?= $content_order ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Content Modal -->
    <div class="modal fade" id="editContentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="contentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_content">
                        <input type="hidden" name="page_name" id="editPageName">
                        <input type="hidden" name="section_name" id="editSectionName">
                        <input type="hidden" name="current_image" id="editCurrentImage">
                        <!-- Hidden input for Quill content -->
                        <input type="hidden" name="content" id="editorContent">
                        
                        <div class="mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" id="editSectionDisplay" readonly>
                            <small class="text-muted" id="sectionDescription"></small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Title (Optional)</label>
                            <input type="text" class="form-control" name="title" id="editTitle">
                            <small class="text-muted">Leave empty to use section name as title</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Content *</label>
                            <div class="editor-container">
                                <div id="editor" style="height: 200px;"></div>
                            </div>
                            <small class="text-muted">Use the toolbar above to format your content</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image (Optional)</label>
                            <div class="input-group">
                                <input type="file" class="form-control" name="image" accept="image/*" 
                                       onchange="previewImage(this, 'editImagePreview')">
                            </div>
                            <div class="mt-2">
                                <img id="editImagePreview" src="" class="img-thumbnail-custom me-3" style="display: none;">
                                <div>
                                    <small class="text-muted d-block">Leave empty to keep current image</small>
                                    <small class="text-muted">Recommended size: 800x600px | Max: 2MB</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editDisplayOrder" min="0" max="999">
                                <small class="text-muted">Lower numbers appear first</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="editStatus">
                                    <option value="active">Active (Visible on website)</option>
                                    <option value="inactive">Inactive (Hidden on website)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Content
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include_once 'components/admin-scripts.php'; ?>
    
    <!-- Quill.js -->
    <script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
    
    <script>
        // Initialize Quill editor
        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            placeholder: 'Enter your content here...'
        });
        
        // Variable to track unsaved changes
        let hasUnsavedChanges = false;
        
        // Track changes in Quill editor
        quill.on('text-change', function() {
            hasUnsavedChanges = true;
        });
        
        // Track changes in other form fields
        $('#editTitle, #editDisplayOrder').on('input', function() {
            hasUnsavedChanges = true;
        });
        
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
            
            // Form submission with SweetAlert confirmation
            $('#contentForm').submit(function(e) {
                // Get Quill content
                const quillContent = quill.root.innerHTML;
                const plainText = quill.getText().trim();
                
                // Update hidden input with Quill content
                $('#editorContent').val(quillContent);
                
                // Validate required fields
                const sectionName = $('#editSectionDisplay').val();
                
                if (!plainText) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Content',
                        text: 'Please enter content for ' + sectionName,
                        confirmButtonColor: '#667eea'
                    });
                    return false;
                }
                
                // Show loading
                Swal.fire({
                    title: 'Updating Content...',
                    text: 'Please wait while we save your changes',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                
                // Reset unsaved changes flag
                hasUnsavedChanges = false;
                
                return true;
            });
        });
        
        // Edit modal data
        $('#editContentModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            
            modal.find('#editPageName').val(button.data('page-name'));
            modal.find('#editSectionName').val(button.data('section-key'));
            modal.find('#editSectionDisplay').val(button.data('section-name'));
            modal.find('#editTitle').val(button.data('title'));
            modal.find('#editCurrentImage').val(button.data('image'));
            modal.find('#editDisplayOrder').val(button.data('display-order'));
            modal.find('#editStatus').val(button.data('status'));
            
            // Reset unsaved changes flag when opening modal
            hasUnsavedChanges = false;
            
            // Set section description based on type
            const sectionKey = button.data('section-key');
            let description = 'Edit this section content';
            
            if (sectionKey.includes('video')) {
                description = 'Enter YouTube or Vimeo embed URL (e.g., https://www.youtube.com/embed/VIDEO_ID)';
            } else if (sectionKey.includes('photo')) {
                description = 'Upload director or staff photo (Recommended: 500x500px)';
            } else if (sectionKey.includes('paragraph')) {
                description = 'Enter detailed content with formatting';
            } else if (sectionKey.includes('quote')) {
                description = 'Enter inspirational quote or vision statement';
            }
            
            modal.find('#sectionDescription').text(description);
            
            // Set Quill editor content
            quill.setContents(quill.clipboard.convert(button.data('content')));
            
            // Set image preview
            var imageUrl = '<?= img_url("") ?>' + button.data('image');
            var previewImg = modal.find('#editImagePreview');
            if (button.data('image')) {
                previewImg.attr('src', imageUrl);
                previewImg.show();
            } else {
                previewImg.attr('src', '');
                previewImg.hide();
            }
        });
        
        // Clear Quill editor when modal is hidden
        $('#editContentModal').on('hidden.bs.modal', function() {
            quill.setText('');
            $('#editorContent').val('');
            hasUnsavedChanges = false;
        });
        
        // Confirm before leaving modal with unsaved changes
        $('#editContentModal').on('hide.bs.modal', function(e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                Swal.fire({
                    title: 'Unsaved Changes',
                    text: 'You have unsaved changes. Are you sure you want to close?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, close',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        hasUnsavedChanges = false;
                        $('#editContentModal').modal('hide');
                    }
                });
            }
        });
        
        // Preview image
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onloadend = function() {
                preview.src = reader.result;
                preview.style.display = 'block';
                hasUnsavedChanges = true; // Consider file change as unsaved change
            }
            
            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
        
        // Toggle sidebar on mobile
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
        
        // Add image upload handler for Quill
        quill.getModule('toolbar').addHandler('image', function() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();
            
            input.onchange = async function() {
                const file = input.files[0];
                if (!file) return;
                
                // Show loading for image upload
                Swal.fire({
                    title: 'Uploading Image...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                
                // In a real application, you would upload the image to your server here
                // For now, we'll use a placeholder approach
                const reader = new FileReader();
                reader.onload = function(e) {
                    const range = quill.getSelection();
                    quill.insertEmbed(range.index, 'image', e.target.result);
                    
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Image Added!',
                        text: 'Image inserted into content',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    
                    hasUnsavedChanges = true;
                };
                reader.readAsDataURL(file);
            };
        });
    </script>
</body>
</html>