<?php
// modules/Dashboard/views/admission-management.php
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
    
    if ($action === 'add_section') {
        try {
            $title = $_POST['title'];
            $subtitle = $_POST['subtitle'] ?? '';
            $icon_class = $_POST['icon_class'];
            $display_order = $_POST['display_order'] ?? 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate inputs
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
            
            $stmt = $pdo->prepare("INSERT INTO admission_sections (title, subtitle, icon_class, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $icon_class, $display_order, $is_active]);
            
            session_start();
            $_SESSION['success_message'] = 'Admission section added successfully!';
            $_SESSION['success_action'] = 'add_section';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to add section: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }
    
    if ($action === 'update_section') {
        try {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $subtitle = $_POST['subtitle'] ?? '';
            $icon_class = $_POST['icon_class'];
            $display_order = $_POST['display_order'] ?? 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate inputs
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
            
            $stmt = $pdo->prepare("UPDATE admission_sections SET title = ?, subtitle = ?, icon_class = ?, display_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $subtitle, $icon_class, $display_order, $is_active, $id]);
            
            session_start();
            $_SESSION['success_message'] = 'Section updated successfully!';
            $_SESSION['success_action'] = 'update_section';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update section: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }
    
    if ($action === 'delete_section') {
        try {
            $id = $_POST['id'];
            
            // First delete all content items for this section
            $stmt = $pdo->prepare("DELETE FROM admission_content WHERE section_id = ?");
            $stmt->execute([$id]);
            
            // Then delete the section
            $stmt = $pdo->prepare("DELETE FROM admission_sections WHERE id = ?");
            $stmt->execute([$id]);
            
            session_start();
            $_SESSION['success_message'] = 'Section and all its content deleted successfully!';
            $_SESSION['success_action'] = 'delete_section';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to delete section: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }
    
    if ($action === 'add_content') {
        try {
            $section_id = $_POST['section_id'];
            $content_type = $_POST['content_type'];
            $title = $_POST['title'];
            $content = $_POST['content'];
            $icon = $_POST['icon'] ?? '';
            $display_order = $_POST['display_order'] ?? 0;
            $metadata = $_POST['metadata'] ?? '{}';
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate inputs
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            
            // Validate JSON content
            if (!empty($content)) {
                $decoded_content = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON in content field');
                }
                $content = json_encode($decoded_content, JSON_UNESCAPED_UNICODE);
            } else {
                $content = '{}';
            }
            
            // Validate JSON metadata
            if (!empty($metadata)) {
                $decoded_metadata = json_decode($metadata, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON in metadata field');
                }
                $metadata = json_encode($decoded_metadata, JSON_UNESCAPED_UNICODE);
            } else {
                $metadata = '{}';
            }
            
            $stmt = $pdo->prepare("INSERT INTO admission_content (section_id, content_type, title, content, icon, display_order, metadata, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$section_id, $content_type, $title, $content, $icon, $display_order, $metadata, $is_active]);
            
            session_start();
            $_SESSION['success_message'] = 'Content item added successfully!';
            $_SESSION['success_action'] = 'add_content';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to add content: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }
    
    if ($action === 'update_content') {
        try {
            $id = $_POST['id'];
            $section_id = $_POST['section_id'];
            $content_type = $_POST['content_type'];
            $title = $_POST['title'];
            $content = $_POST['content'];
            $icon = $_POST['icon'] ?? '';
            $display_order = $_POST['display_order'] ?? 0;
            $metadata = $_POST['metadata'] ?? '{}';
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate inputs
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            
            // Validate JSON content
            if (!empty($content)) {
                $decoded_content = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON in content field');
                }
                $content = json_encode($decoded_content, JSON_UNESCAPED_UNICODE);
            } else {
                $content = '{}';
            }
            
            // Validate JSON metadata
            if (!empty($metadata)) {
                $decoded_metadata = json_decode($metadata, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON in metadata field');
                }
                $metadata = json_encode($decoded_metadata, JSON_UNESCAPED_UNICODE);
            } else {
                $metadata = '{}';
            }
            
            $stmt = $pdo->prepare("UPDATE admission_content SET section_id = ?, content_type = ?, title = ?, content = ?, icon = ?, display_order = ?, metadata = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$section_id, $content_type, $title, $content, $icon, $display_order, $metadata, $is_active, $id]);
            
            session_start();
            $_SESSION['success_message'] = 'Content item updated successfully!';
            $_SESSION['success_action'] = 'update_content';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update content: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }
    
    if ($action === 'delete_content') {
        try {
            $id = $_POST['id'];
            
            $stmt = $pdo->prepare("DELETE FROM admission_content WHERE id = ?");
            $stmt->execute([$id]);
            
            session_start();
            $_SESSION['success_message'] = 'Content item deleted successfully!';
            $_SESSION['success_action'] = 'delete_content';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to delete content: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }
    
    if ($action === 'update_section_order') {
        try {
            $orders = $_POST['order'] ?? [];
            
            foreach ($orders as $id => $order) {
                $stmt = $pdo->prepare("UPDATE admission_sections SET display_order = ? WHERE id = ?");
                $stmt->execute([$order, $id]);
            }
            
            session_start();
            $_SESSION['success_message'] = 'Section order updated successfully!';
            $_SESSION['success_action'] = 'section_order_update';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update section order: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }
    
    if ($action === 'update_content_order') {
        try {
            $orders = $_POST['order'] ?? [];
            $section_id = $_POST['section_id'];
            
            foreach ($orders as $id => $order) {
                $stmt = $pdo->prepare("UPDATE admission_content SET display_order = ? WHERE id = ? AND section_id = ?");
                $stmt->execute([$order, $id, $section_id]);
            }
            
            session_start();
            $_SESSION['success_message'] = 'Content order updated successfully!';
            $_SESSION['success_action'] = 'content_order_update';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update content order: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }

    if ($action === 'add_document') {
        try {
            $section_id = !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null;
            $content_id = !empty($_POST['content_id']) ? (int)$_POST['content_id'] : null;
            $title = $_POST['title'];
            $description = $_POST['description'] ?? '';
            $display_order = $_POST['display_order'] ?? 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate that at least one reference exists
            if (!$section_id && !$content_id) {
                throw new Exception('Document must be linked to a section or content item');
            }
            
            // Validate file upload
            if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Please select a file to upload');
            }
            
            $file = $_FILES['document_file'];
            
            // Validate file size (10MB max)
            $max_size = 10 * 1024 * 1024; // 10MB
            if ($file['size'] > $max_size) {
                throw new Exception('File size exceeds 10MB limit');
            }
            
            // Validate file type
            $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('File type not allowed. Allowed: PDF, Word, Excel, Images');
            }
            
            // Create uploads directory if it doesn't exist
            $upload_dir = ROOT_PATH . '/uploads/admission_documents/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $file_name = uniqid('doc_') . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                throw new Exception('Failed to upload file');
            }
            
            // Determine file type
            $file_type_map = [
                'pdf' => 'PDF',
                'doc' => 'Word',
                'docx' => 'Word',
                'xls' => 'Excel',
                'xlsx' => 'Excel',
                'jpg' => 'Image',
                'jpeg' => 'Image',
                'png' => 'Image'
            ];
            $file_type = $file_type_map[$file_extension] ?? 'Document';
            
            // Verify foreign key constraints if content_id is provided
            if ($content_id) {
                $checkStmt = $pdo->prepare("SELECT id FROM admission_content WHERE id = ?");
                $checkStmt->execute([$content_id]);
                if (!$checkStmt->fetch()) {
                    throw new Exception('Invalid content item selected');
                }
            }
            
            if ($section_id) {
                $checkStmt = $pdo->prepare("SELECT id FROM admission_sections WHERE id = ?");
                $checkStmt->execute([$section_id]);
                if (!$checkStmt->fetch()) {
                    throw new Exception('Invalid section selected');
                }
            }
            
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO admission_documents 
                                (section_id, content_id, title, description, file_name, file_url, file_type, file_size, display_order, is_active) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $file_url = '/uploads/admission_documents/' . $file_name;
            $stmt->execute([
                $section_id,          // This will be NULL if empty
                $content_id,         // This will be NULL if empty
                $title, 
                $description, 
                $file_name, 
                $file_url, 
                $file_type, 
                $file['size'], 
                $display_order, 
                $is_active
            ]);
            
            session_start();
            $_SESSION['success_message'] = 'Document uploaded successfully!';
            $_SESSION['success_action'] = 'add_document';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to upload document: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }

    if ($action === 'update_document') {
        try {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $description = $_POST['description'] ?? '';
            $display_order = $_POST['display_order'] ?? 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $current_file = $_POST['current_file'];
            
            $file_name = $current_file;
            $file_url = '/uploads/admission_documents/' . $current_file;
            $file_type = null;
            $file_size = null;
            
            // Handle file replacement
            if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['document_file'];
                
                // Validate file size (10MB max)
                $max_size = 10 * 1024 * 1024;
                if ($file['size'] > $max_size) {
                    throw new Exception('File size exceeds 10MB limit');
                }
                
                // Validate file type
                $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($file_extension, $allowed_extensions)) {
                    throw new Exception('File type not allowed. Allowed: PDF, Word, Excel, Images');
                }
                
                // Delete old file
                $old_file_path = ROOT_PATH . '/uploads/admission_documents/' . $current_file;
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
                
                // Generate new filename
                $file_name = uniqid('doc_') . '_' . time() . '.' . $file_extension;
                $file_path = ROOT_PATH . '/uploads/admission_documents/' . $file_name;
                
                // Move uploaded file
                if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                    throw new Exception('Failed to upload file');
                }
                
                // Determine file type
                $file_type_map = [
                    'pdf' => 'PDF',
                    'doc' => 'Word',
                    'docx' => 'Word',
                    'xls' => 'Excel',
                    'xlsx' => 'Excel',
                    'jpg' => 'Image',
                    'jpeg' => 'Image',
                    'png' => 'Image'
                ];
                $file_type = $file_type_map[$file_extension] ?? 'Document';
                $file_url = '/uploads/admission_documents/' . $file_name;
                $file_size = $file['size'];
            }
            
            // Update database
            if ($file_type) {
                $stmt = $pdo->prepare("UPDATE admission_documents SET 
                                    title = ?, description = ?, file_name = ?, file_url = ?, 
                                    file_type = ?, file_size = ?, display_order = ?, is_active = ?, 
                                    updated_at = NOW() WHERE id = ?");
                $stmt->execute([$title, $description, $file_name, $file_url, $file_type, $file_size, $display_order, $is_active, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE admission_documents SET 
                                    title = ?, description = ?, display_order = ?, is_active = ?, 
                                    updated_at = NOW() WHERE id = ?");
                $stmt->execute([$title, $description, $display_order, $is_active, $id]);
            }
            
            session_start();
            $_SESSION['success_message'] = 'Document updated successfully!';
            $_SESSION['success_action'] = 'update_document';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update document: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }

    if ($action === 'delete_document') {
        try {
            $id = $_POST['id'];
            
            // Get file info before deletion
            $stmt = $pdo->prepare("SELECT file_name FROM admission_documents WHERE id = ?");
            $stmt->execute([$id]);
            $document = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($document) {
                // Delete file from server
                $file_path = ROOT_PATH . '/uploads/admission_documents/' . $document['file_name'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM admission_documents WHERE id = ?");
            $stmt->execute([$id]);
            
            session_start();
            $_SESSION['success_message'] = 'Document deleted successfully!';
            $_SESSION['success_action'] = 'delete_document';
            
            header("Location: " . url('admin/admission'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to delete document: ' . $e->getMessage();
            header("Location: " . url('admin/admission'));
            exit;
        }
    }

}

// Helper function for file icons
function getFileIcon($fileType) {
    $icons = [
        'PDF' => 'fas fa-file-pdf text-danger',
        'Word' => 'fas fa-file-word text-primary',
        'Excel' => 'fas fa-file-excel text-success',
        'Image' => 'fas fa-file-image text-info'
    ];
    return $icons[$fileType] ?? 'fas fa-file text-secondary';
}

// Fetch all sections with their content
$stmt = $pdo->query("SELECT s.*, 
                    (SELECT COUNT(*) FROM admission_content WHERE section_id = s.id) as content_count
                    FROM admission_sections s 
                    ORDER BY s.display_order, s.created_at DESC");
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all content items grouped by section
$contentBySection = [];
foreach ($sections as $section) {
    $stmt = $pdo->prepare("SELECT * FROM admission_content WHERE section_id = ? ORDER BY display_order, created_at DESC");
    $stmt->execute([$section['id']]);
    $contentBySection[$section['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// FontAwesome icons for selection
$fontawesome_icons = [
    'fas fa-clipboard-check' => 'Requirements',
    'fas fa-money-bill-wave' => 'Fees',
    'fas fa-file-signature' => 'Registration',
    'fas fa-baby' => 'Nursery',
    'fas fa-child' => 'Child',
    'fas fa-graduation-cap' => 'Graduation',
    'fas fa-book' => 'Book',
    'fas fa-users' => 'Users',
    'fas fa-calendar-alt' => 'Calendar',
    'fas fa-file-alt' => 'Document',
    'fas fa-list-ol' => 'Steps',
    'fas fa-baby-carriage' => 'Nursery Items',
    'fas fa-plus-circle' => 'Additional',
    'fas fa-laptop' => 'Computer',
    'fas fa-mobile-alt' => 'Mobile',
    'fas fa-shield-alt' => 'Security',
    'fas fa-lightbulb' => 'Tips',
    'fas fa-credit-card' => 'Payment',
    'fas fa-bank' => 'Bank',
    'fas fa-cash-register' => 'Cash'
];

// Content types
$content_types = [
    'requirement' => 'Admission Requirement',
    'fee_structure' => 'Fee Structure',
    'registration' => 'Registration Info'
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
    <title>Admission Management - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .section-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.3s;
            height: 100%;
        }
        .section-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .section-card.ui-sortable-helper {
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transform: rotate(2deg);
        }
        .section-card.ui-sortable-placeholder {
            border: 2px dashed #00796B;
            background: rgba(0, 121, 107, 0.1);
            visibility: visible !important;
        }
        .section-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00796B, #004D40);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 15px;
        }
        .content-item-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .content-item-card:hover {
            border-color: #00796B;
        }
        .content-item-card.ui-sortable-helper {
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .content-item-card.ui-sortable-placeholder {
            border: 2px dashed #00796B;
            background: rgba(0, 121, 107, 0.05);
            min-height: 100px;
        }
        .sortable-handle {
            cursor: move;
            color: #9ca3af;
            font-size: 1.2rem;
        }
        .sortable-handle:hover {
            color: #00796B;
        }
        .order-badge {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #00796B;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
        }
        .drag-instructions {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid #00796B;
        }
        .content-type-badge {
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 10px;
        }
        .requirement-badge {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .fee-badge {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .registration-badge {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        .json-editor {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            min-height: 150px;
        }
        .metadata-preview {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            max-height: 100px;
            overflow-y: auto;
            font-size: 12px;
            font-family: monospace;
        }
        .collapse-toggle {
            cursor: pointer;
            transition: all 0.3s;
        }
        .collapse-toggle:hover {
            color: #00796B;
        }
        .content-count-badge {
            background: #00796B;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            margin-left: 5px;
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
                        <h2 class="mb-0">Admission Management</h2>
                        <p class="text-muted mb-0">Manage admission sections and content for the website</p>
                    </div>
                    <div>
                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                            <i class="fas fa-plus me-2"></i> Add Section
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addContentModal">
                            <i class="fas fa-file-alt me-2"></i> Add Content
                        </button>
                    </div>
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
                                <p class="mb-0 small text-muted">Drag sections by the handle (<i class="fas fa-arrows-alt"></i>) to reorder them. Click on sections to expand/collapse content items.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections Grid -->
        <div class="row" id="sortableSections">
            <?php if (empty($sections)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                            <h4>No admission sections added yet</h4>
                            <p class="text-muted">Click the "Add Section" button to add your first admission section.</p>
                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                                <i class="fas fa-plus me-2"></i> Add First Section
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($sections as $index => $section): ?>
                    <div class="col-md-6 col-lg-4 mb-4" data-id="<?= $section['id'] ?>">
                        <div class="card section-card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="sortable-handle me-2">
                                        <i class="fas fa-arrows-alt"></i>
                                    </span>
                                    <div class="order-badge">
                                        <?= $section['display_order'] + 1 ?>
                                    </div>
                                    <span class="badge bg-primary ms-2 content-count-badge" title="Content items">
                                        <?= $section['content_count'] ?>
                                    </span>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editSectionModal"
                                                    data-section-id="<?= $section['id'] ?>"
                                                    data-title="<?= htmlspecialchars($section['title']) ?>"
                                                    data-subtitle="<?= htmlspecialchars($section['subtitle'] ?? '') ?>"
                                                    data-icon-class="<?= $section['icon_class'] ?>"
                                                    data-display-order="<?= $section['display_order'] ?>"
                                                    data-is-active="<?= $section['is_active'] ?>">
                                                <i class="fas fa-edit me-2 text-primary"></i> Edit Section
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addContentModal"
                                                    data-section-id="<?= $section['id'] ?>">
                                                <i class="fas fa-plus me-2 text-success"></i> Add Content
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item add-document-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addDocumentModal"
                                                    data-section-id="<?= $section['id'] ?>">
                                                <i class="fas fa-paperclip me-2 text-info"></i> Add Document
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="confirmDeleteSection(<?= $section['id'] ?>)">
                                                <i class="fas fa-trash me-2"></i> Delete Section
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <div class="section-icon">
                                    <i class="<?= $section['icon_class'] ?>"></i>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($section['title']) ?></h5>
                                <?php if (!empty($section['subtitle'])): ?>
                                    <p class="card-text text-muted"><?= htmlspecialchars($section['subtitle']) ?></p>
                                <?php endif; ?>
                                <div class="mt-3">
                                    <span class="badge <?= $section['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $section['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between align-items-center collapse-toggle" 
                                     data-bs-toggle="collapse" 
                                     data-bs-target="#contentSection<?= $section['id'] ?>">
                                    <span>
                                        <i class="fas fa-file-alt me-2"></i>
                                        Content Items
                                        <span class="badge bg-secondary ms-2"><?= $section['content_count'] ?></span>
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="collapse mt-3" id="contentSection<?= $section['id'] ?>">
                                    <?php if (!empty($contentBySection[$section['id']])): ?>
                                        <div class="content-items-list" id="sortableContent<?= $section['id'] ?>">
                                            <?php foreach ($contentBySection[$section['id']] as $contentIndex => $content): ?>
                                                <div class="content-item-card p-3" data-id="<?= $content['id'] ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span class="sortable-handle me-2">
                                                                <i class="fas fa-arrows-alt"></i>
                                                            </span>
                                                            <strong><?= htmlspecialchars($content['title']) ?></strong>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle edit-content-btn" 
                                                                    type="button"
                                                                    data-content-id="<?= $content['id'] ?>"
                                                                    data-section-id="<?= $content['section_id'] ?>"
                                                                    data-content-type="<?= $content['content_type'] ?>"
                                                                    data-title="<?= htmlspecialchars($content['title']) ?>"
                                                                    data-content-json='<?= addslashes($content['content']) ?>'
                                                                    data-icon="<?= $content['icon'] ?? '' ?>"
                                                                    data-display-order="<?= $content['display_order'] ?>"
                                                                    data-metadata-json='<?= addslashes($content['metadata'] ?? '{}') ?>'
                                                                    data-is-active="<?= $content['is_active'] ?>">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <button class="dropdown-item edit-content-btn"
                                                                            data-content-id="<?= $content['id'] ?>"
                                                                            data-section-id="<?= $content['section_id'] ?>"
                                                                            data-content-type="<?= $content['content_type'] ?>"
                                                                            data-title="<?= htmlspecialchars($content['title']) ?>"
                                                                            data-content-json='<?= addslashes($content['content']) ?>'
                                                                            data-icon="<?= $content['icon'] ?? '' ?>"
                                                                            data-display-order="<?= $content['display_order'] ?>"
                                                                            data-metadata-json='<?= addslashes($content['metadata'] ?? '{}') ?>'
                                                                            data-is-active="<?= $content['is_active'] ?>">
                                                                        <i class="fas fa-edit me-2 text-primary"></i> Edit
                                                                    </button>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <button class="dropdown-item text-danger" onclick="confirmDeleteContent(<?= $content['id'] ?>)">
                                                                        <i class="fas fa-trash me-2"></i> Delete
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge content-type-badge <?= $content['content_type'] ?>-badge">
                                                            <?= $content_types[$content['content_type']] ?? $content['content_type'] ?>
                                                        </span>
                                                        <span class="badge <?= $content['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                            <?= $content['is_active'] ? 'Active' : 'Inactive' ?>
                                                        </span>
                                                    </div>
                                                    <?php 
                                                    // Parse metadata for preview
                                                    $metadata_preview = '';
                                                    if (!empty($content['metadata']) && $content['metadata'] !== '{}') {
                                                        $metadata_array = json_decode($content['metadata'], true);
                                                        if (json_last_error() === JSON_ERROR_NONE && is_array($metadata_array)) {
                                                            $metadata_preview = json_encode($metadata_array);
                                                        } else {
                                                            $metadata_preview = $content['metadata'];
                                                        }
                                                    }
                                                    ?>
                                                    <?php if (!empty($metadata_preview)): ?>
                                                        <div class="metadata-preview mt-2">
                                                            <small><strong>Metadata:</strong> <?= substr($metadata_preview, 0, 100) . (strlen($metadata_preview) > 100 ? '...' : '') ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <p class="text-muted mb-2">No content items for this section</p>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addContentModal"
                                                    data-section-id="<?= $section['id'] ?>">
                                                <i class="fas fa-plus me-1"></i> Add Content
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <!-- ***** THE DOCUMENT CODE HERE ***** -->
                                     <?php
                                    // Get documents for this section
                                    $documentsStmt = $pdo->prepare("SELECT * FROM admission_documents WHERE section_id = ? ORDER BY display_order ASC");
                                    $documentsStmt->execute([$section['id']]);
                                    $documents = $documentsStmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <?php if (!empty($documents)): ?>
                                        <div class="mt-4">
                                            <h6 class="text-muted mb-2"><i class="fas fa-paperclip me-2"></i>Section Documents</h6>
                                            <div class="list-group">
                                                <?php foreach ($documents as $doc): ?>
                                                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <?php $fileIcon = getFileIcon($doc['file_type'] ?? 'Document'); ?>
                                                            <i class="<?= $fileIcon ?> me-3"></i>
                                                            <div>
                                                                <strong><?= htmlspecialchars($doc['title']) ?></strong>
                                                                <?php if (!empty($doc['description'])): ?>
                                                                    <br><small class="text-muted"><?= htmlspecialchars($doc['description']) ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle edit-document-btn" 
                                                                    type="button"
                                                                    data-document-id="<?= $doc['id'] ?>"
                                                                    data-title="<?= htmlspecialchars($doc['title']) ?>"
                                                                    data-description="<?= htmlspecialchars($doc['description'] ?? '') ?>"
                                                                    data-filename="<?= $doc['file_name'] ?>"
                                                                    data-file-url="<?= $doc['file_url'] ?>"
                                                                    data-file-type="<?= $doc['file_type'] ?? 'Document' ?>"
                                                                    data-display-order="<?= $doc['display_order'] ?>"
                                                                    data-is-active="<?= $doc['is_active'] ?>">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a class="dropdown-item" href="<?= url($doc['file_url']) ?>" target="_blank">
                                                                        <i class="fas fa-eye me-2"></i> Preview
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item edit-document-btn"
                                                                            data-document-id="<?= $doc['id'] ?>"
                                                                            data-title="<?= htmlspecialchars($doc['title']) ?>"
                                                                            data-description="<?= htmlspecialchars($doc['description'] ?? '') ?>"
                                                                            data-filename="<?= $doc['file_name'] ?>"
                                                                            data-file-url="<?= $doc['file_url'] ?>"
                                                                            data-file-type="<?= $doc['file_type'] ?? 'Document' ?>"
                                                                            data-display-order="<?= $doc['display_order'] ?>"
                                                                            data-is-active="<?= $doc['is_active'] ?>">
                                                                        <i class="fas fa-edit me-2 text-primary"></i> Edit
                                                                    </button>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <button class="dropdown-item text-danger" onclick="confirmDeleteDocument(<?= $doc['id'] ?>)">
                                                                        <i class="fas fa-trash me-2"></i> Delete
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <!-- ================ END DOCUMENTS CODE ================ -->
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Admission Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addSectionForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_section">
                        
                        <div class="mb-3">
                            <label class="form-label">Section Title *</label>
                            <input type="text" class="form-control" name="title" required maxlength="255" 
                                   placeholder="e.g., Admission Requirements, Fee Structure">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <input type="text" class="form-control" name="subtitle" maxlength="500" 
                                   placeholder="Optional description">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Icon *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i id="addSectionIconPreview" class="fas fa-clipboard-check"></i>
                                </span>
                                <select class="form-select" name="icon_class" id="addSectionIconClass" required>
                                    <option value="">Select an icon</option>
                                    <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                        <option value="<?= $icon ?>"><?= $label ?> (<?= $icon ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0" min="0" max="999">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="addSectionActive" checked>
                                    <label class="form-check-label" for="addSectionActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Section Modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admission Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editSectionForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_section">
                        <input type="hidden" name="id" id="editSectionId">
                        
                        <div class="mb-3">
                            <label class="form-label">Section Title *</label>
                            <input type="text" class="form-control" name="title" id="editSectionTitle" required maxlength="255">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <input type="text" class="form-control" name="subtitle" id="editSectionSubtitle" maxlength="500">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Icon *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i id="editSectionIconPreview" class="fas fa-clipboard-check"></i>
                                </span>
                                <select class="form-select" name="icon_class" id="editSectionIconClass" required>
                                    <option value="">Select an icon</option>
                                    <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                        <option value="<?= $icon ?>"><?= $label ?> (<?= $icon ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editSectionDisplayOrder" min="0" max="999">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="editSectionActive">
                                    <label class="form-check-label" for="editSectionActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Content Modal -->
    <div class="modal fade" id="addContentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Content Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addContentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_content">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Section *</label>
                                <select class="form-select" name="section_id" id="addContentSectionId" required>
                                    <option value="">Select a section</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?= $section['id'] ?>"><?= htmlspecialchars($section['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Content Type *</label>
                                <select class="form-select" name="content_type" id="addContentType" required>
                                    <option value="">Select type</option>
                                    <?php foreach ($content_types as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" required maxlength="255" 
                                   placeholder="e.g., Nursery School Requirements">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Content *</label>
                            <textarea class="form-control json-editor" name="content" rows="6" required 
                                      placeholder='Enter content as JSON. Example: {"age": "3-5 years", "documents": ["Birth certificate"]}'></textarea>
                            <small class="text-muted">Enter content in JSON format. Use proper JSON syntax.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon</label>
                                <select class="form-select" name="icon" id="addContentIcon">
                                    <option value="">Select icon (optional)</option>
                                    <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                        <option value="<?= $icon ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0" min="0" max="999">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Metadata (JSON - Optional)</label>
                            <textarea class="form-control json-editor" name="metadata" rows="3" 
                                      placeholder='{"program": "nursery", "optional": true}'></textarea>
                            <small class="text-muted">Additional metadata in JSON format</small>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="addContentActive" checked>
                            <label class="form-check-label" for="addContentActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Content</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Content Modal -->
    <div class="modal fade" id="editContentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Content Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editContentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_content">
                        <input type="hidden" name="id" id="editContentId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Section *</label>
                                <select class="form-select" name="section_id" id="editContentSectionId" required>
                                    <option value="">Select a section</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?= $section['id'] ?>"><?= htmlspecialchars($section['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Content Type *</label>
                                <select class="form-select" name="content_type" id="editContentType" required>
                                    <option value="">Select type</option>
                                    <?php foreach ($content_types as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" id="editContentTitle" required maxlength="255">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Content *</label>
                            <textarea class="form-control json-editor" name="content" id="editContentContent" rows="6" required></textarea>
                            <small class="text-muted">Enter content in JSON format. Use proper JSON syntax.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon</label>
                                <select class="form-select" name="icon" id="editContentIcon">
                                    <option value="">Select icon (optional)</option>
                                    <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                        <option value="<?= $icon ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editContentDisplayOrder" min="0" max="999">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Metadata (JSON - Optional)</label>
                            <textarea class="form-control json-editor" name="metadata" id="editContentMetadata" rows="3"></textarea>
                            <small class="text-muted">Additional metadata in JSON format</small>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editContentActive">
                            <label class="form-check-label" for="editContentActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Content</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Forms -->
    <form id="deleteSectionForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_section">
        <input type="hidden" name="id" id="deleteSectionId">
    </form>
    
    <form id="deleteContentForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_content">
        <input type="hidden" name="id" id="deleteContentId">
    </form>
    
    <!-- Order Forms -->
    <form id="updateSectionOrderForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_section_order">
        <input type="hidden" name="order" id="sectionOrderData">
    </form>
    
    <form id="updateContentOrderForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_content_order">
        <input type="hidden" name="section_id" id="contentSectionId">
        <input type="hidden" name="order" id="contentOrderData">
    </form>

    <!-- Add Document Modal -->
    <div class="modal fade" id="addDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Supporting Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addDocumentForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_document">
                        <input type="hidden" name="section_id" id="addDocumentSectionId" value="">
                        <input type="hidden" name="content_id" id="addDocumentContentId" value="">
                        
                        <div class="mb-3">
                            <label class="form-label">Document Title *</label>
                            <input type="text" class="form-control" name="title" required 
                                placeholder="e.g., Admission Brochure, Fee Schedule PDF">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" 
                                    placeholder="Brief description of the document"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Upload File *</label>
                            <input type="file" class="form-control" name="document_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                            <small class="text-muted">Allowed: PDF, Word, Excel, Images (Max: 10MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0" min="0" max="999">
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="addDocumentActive" checked>
                            <label class="form-check-label" for="addDocumentActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Document Modal -->
    <div class="modal fade" id="editDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editDocumentForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_document">
                        <input type="hidden" name="id" id="editDocumentId">
                        <input type="hidden" name="current_file" id="editDocumentCurrentFile">
                        
                        <div class="mb-3">
                            <label class="form-label">Document Title *</label>
                            <input type="text" class="form-control" name="title" id="editDocumentTitle" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDocumentDescription" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Current File</label>
                            <div id="editDocumentCurrentFilePreview" class="border p-2 rounded bg-light">
                                <!-- Will be populated with JS -->
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Replace File (Optional)</label>
                            <input type="file" class="form-control" name="document_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <small class="text-muted">Leave empty to keep current file. Allowed: PDF, Word, Excel, Images (Max: 10MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" id="editDocumentDisplayOrder" min="0" max="999">
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editDocumentActive">
                            <label class="form-check-label" for="editDocumentActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Document Form -->
    <form id="deleteDocumentForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_document">
        <input type="hidden" name="id" id="deleteDocumentId">
    </form>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include_once 'components/admin-scripts.php'; ?>
    
    <script>
        $(document).ready(function() {
            // Show success/error messages
            <?php if ($success_message): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?= addslashes($success_message) ?>',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
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
                    timerProgressBar: true
                });
            <?php endif; ?>
            
            // Initialize section sortable
            $("#sortableSections").sortable({
                handle: ".sortable-handle",
                placeholder: "ui-sortable-placeholder",
                update: function(event, ui) {
                    updateSectionDisplayOrder();
                }
            });
            $("#sortableSections").disableSelection();
            
            // Initialize content sortable for each section
            <?php foreach ($sections as $section): ?>
                if ($('#sortableContent<?= $section['id'] ?>').length) {
                    $('#sortableContent<?= $section['id'] ?>').sortable({
                        handle: ".sortable-handle",
                        placeholder: "ui-sortable-placeholder",
                        update: function(event, ui) {
                            updateContentDisplayOrder(<?= $section['id'] ?>);
                        }
                    });
                    $('#sortableContent<?= $section['id'] ?>').disableSelection();
                }
            <?php endforeach; ?>
            
            // Section icon preview
            $('#addSectionIconClass').change(function() {
                const selectedIcon = $(this).val();
                $('#addSectionIconPreview').attr('class', selectedIcon);
            });
            
            $('#editSectionIconClass').change(function() {
                const selectedIcon = $(this).val();
                $('#editSectionIconPreview').attr('class', selectedIcon);
            });
            
            // Edit section modal data
            $('#editSectionModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#editSectionId').val(button.data('section-id'));
                modal.find('#editSectionTitle').val(button.data('title'));
                modal.find('#editSectionSubtitle').val(button.data('subtitle'));
                modal.find('#editSectionIconClass').val(button.data('icon-class'));
                modal.find('#editSectionDisplayOrder').val(button.data('display-order'));
                modal.find('#editSectionActive').prop('checked', button.data('is-active') == 1);
                
                // Update icon preview
                $('#editSectionIconPreview').attr('class', button.data('icon-class'));
            });
            
            // Edit content button click handler
            $(document).on('click', '.edit-content-btn', function(e) {
                e.preventDefault();
                
                const button = $(this);
                const modal = $('#editContentModal');
                
                modal.find('#editContentId').val(button.data('content-id'));
                modal.find('#editContentSectionId').val(button.data('section-id'));
                modal.find('#editContentType').val(button.data('content-type'));
                modal.find('#editContentTitle').val(button.data('title'));
                
                // Get the JSON content from data attribute
                let contentValue = button.data('content-json');
                try {
                    if (contentValue) {
                        // First, unescape the string (remove slashes before quotes)
                        contentValue = contentValue.replace(/\\"/g, '"');
                        
                        // Parse and re-stringify to ensure valid JSON
                        const parsed = JSON.parse(contentValue);
                        contentValue = JSON.stringify(parsed, null, 2);
                    } else {
                        contentValue = '{}';
                    }
                } catch (e) {
                    console.error('Error parsing content JSON:', e);
                    // If parsing fails, show raw content (unescaped)
                    contentValue = contentValue ? contentValue.replace(/\\"/g, '"') : '{}';
                }
                modal.find('#editContentContent').val(contentValue);
                
                modal.find('#editContentIcon').val(button.data('icon'));
                modal.find('#editContentDisplayOrder').val(button.data('display-order'));
                
                // Parse metadata JSON
                let metadataValue = button.data('metadata-json');
                try {
                    if (metadataValue && metadataValue !== '{}') {
                        // Unescape the string
                        metadataValue = metadataValue.replace(/\\"/g, '"');
                        
                        const parsed = JSON.parse(metadataValue);
                        metadataValue = JSON.stringify(parsed, null, 2);
                    } else {
                        metadataValue = '{}';
                    }
                } catch (e) {
                    console.error('Error parsing metadata JSON:', e);
                    metadataValue = metadataValue ? metadataValue.replace(/\\"/g, '"') : '{}';
                }
                modal.find('#editContentMetadata').val(metadataValue);
                
                modal.find('#editContentActive').prop('checked', button.data('is-active') == 1);
                
                modal.modal('show');
            });
                
            // Add content modal with section pre-selected
            $('#addContentModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                if (button.data('section-id')) {
                    $('#addContentSectionId').val(button.data('section-id'));
                }
            });
            
            // Function to properly stringify JSON before form submission
            function stringifyJSONFields() {
                // Content field
                const contentField = $('textarea[name="content"]');
                if (contentField.length) {
                    try {
                        let contentValue = contentField.val().trim();
                        
                        // If the value contains escaped quotes, unescape them first
                        if (contentValue.includes('\\"')) {
                            contentValue = contentValue.replace(/\\"/g, '"');
                        }
                        
                        // If it's a string that looks like JSON, parse and re-stringify it
                        if (contentValue && (contentValue.startsWith('{') || contentValue.startsWith('['))) {
                            const parsed = JSON.parse(contentValue);
                            contentField.val(JSON.stringify(parsed, null, 2));
                        }
                    } catch (e) {
                        console.error('Error processing content JSON:', e);
                        // If not valid JSON, leave as is
                    }
                }
                
                // Metadata field
                const metadataField = $('textarea[name="metadata"]');
                if (metadataField.length) {
                    try {
                        let metadataValue = metadataField.val().trim();
                        
                        // If the value contains escaped quotes, unescape them first
                        if (metadataValue.includes('\\"')) {
                            metadataValue = metadataValue.replace(/\\"/g, '"');
                        }
                        
                        // If it's a string that looks like JSON, parse and re-stringify it
                        if (metadataValue && (metadataValue.startsWith('{') || metadataValue.startsWith('['))) {
                            const parsed = JSON.parse(metadataValue);
                            metadataField.val(JSON.stringify(parsed, null, 2));
                        }
                    } catch (e) {
                        console.error('Error processing metadata JSON:', e);
                        // If not valid JSON, leave as is
                    }
                }
            }
                
            // Content form submission
            $('#addContentForm, #editContentForm').submit(function(e) {
                // First stringify JSON fields
                stringifyJSONFields();
                
                const contentField = $(this).find('textarea[name="content"]');
                const metadataField = $(this).find('textarea[name="metadata"]');
                
                // Validate required JSON content
                let contentValue = contentField.val().trim();
                
                if (!contentValue) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Empty Content',
                        text: 'Content field cannot be empty.',
                        confirmButtonColor: '#00796B'
                    });
                    contentField.focus();
                    return false;
                }
                
                // Unescape quotes before validation
                if (contentValue.includes('\\"')) {
                    contentValue = contentValue.replace(/\\"/g, '"');
                    contentField.val(contentValue);
                }
                
                try {
                    JSON.parse(contentValue);
                } catch (error) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid JSON',
                        html: 'Content field must contain valid JSON. Check your syntax.<br><br>' +
                              '<small>Error: ' + error.message + '</small><br>' +
                              '<small>Make sure quotes are properly formatted.</small>',
                        confirmButtonColor: '#00796B'
                    });
                    contentField.focus();
                    return false;
                }
                
                // Validate optional metadata JSON
                let metadataValue = metadataField.val().trim();
                if (metadataValue) {
                    // Unescape quotes before validation
                    if (metadataValue.includes('\\"')) {
                        metadataValue = metadataValue.replace(/\\"/g, '"');
                        metadataField.val(metadataValue);
                    }
                    
                    try {
                        JSON.parse(metadataValue);
                    } catch (error) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid JSON',
                            html: 'Metadata field must contain valid JSON or be empty.<br><br>' +
                                  '<small>Error: ' + error.message + '</small><br>' +
                                  '<small>Make sure quotes are properly formatted.</small>',
                            confirmButtonColor: '#00796B'
                        });
                        metadataField.focus();
                        return false;
                    }
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
                
            // Section form submission
            $('#addSectionForm, #editSectionForm').submit(function(e) {
                const title = $(this).find('input[name="title"]').val().trim();
                
                if (!title) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Title',
                        text: 'Please enter a section title',
                        confirmButtonColor: '#00796B'
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
        
        // Update section display order
        function updateSectionDisplayOrder() {
            const orderData = {};
            $('#sortableSections .col-md-6').each(function(index) {
                const sectionId = $(this).data('id');
                orderData[sectionId] = index;
                
                // Update the order badge
                $(this).find('.order-badge').text(index + 1);
            });
            
            // Send AJAX request to update order
            $.ajax({
                url: '<?= url('admin/admission') ?>',
                method: 'POST',
                data: {
                    action: 'update_section_order',
                    order: orderData
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Updated!',
                        text: 'Section order has been saved',
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
                        text: 'Failed to update section order',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            });
        }
        
        // Update content display order for a section
        function updateContentDisplayOrder(sectionId) {
            const orderData = {};
            $(`#sortableContent${sectionId} .content-item-card`).each(function(index) {
                const contentId = $(this).data('id');
                orderData[contentId] = index;
            });
            
            // Send AJAX request to update order
            $.ajax({
                url: '<?= url('admin/admission') ?>',
                method: 'POST',
                data: {
                    action: 'update_content_order',
                    section_id: sectionId,
                    order: orderData
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Updated!',
                        text: 'Content order has been saved',
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
                        text: 'Failed to update content order',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            });
        }
        
        // Confirm delete section
        function confirmDeleteSection(sectionId) {
            Swal.fire({
                title: 'Delete Section?',
                html: 'This will delete the section and all its content items.<br><strong>This action cannot be undone.</strong>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete everything!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    
                    $('#deleteSectionId').val(sectionId);
                    $('#deleteSectionForm').submit();
                }
            });
        }
        
        // Confirm delete content
        function confirmDeleteContent(contentId) {
            Swal.fire({
                title: 'Delete Content Item?',
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
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    
                    $('#deleteContentId').val(contentId);
                    $('#deleteContentForm').submit();
                }
            });
        }
        
        // JSON formatting helper
        function formatJSON(textarea) {
            try {
                const json = JSON.parse(textarea.value);
                textarea.value = JSON.stringify(json, null, 2);
            } catch (e) {
                // Not valid JSON, leave as is
            }
        }
        
        // Auto-format JSON on focus out
        $(document).on('blur', '.json-editor', function() {
            formatJSON(this);
        });

        // Add document modal handlers
        $(document).on('click', '.add-document-btn', function(e) {
            e.preventDefault();
            const button = $(this);
            const sectionId = button.data('section-id');
            const contentId = button.data('content-id');
            
            $('#addDocumentSectionId').val(sectionId || '');
            $('#addDocumentContentId').val(contentId || '');
            
            $('#addDocumentModal').modal('show');
        });

        $(document).on('click', '.edit-document-btn', function(e) {
            e.preventDefault();
            const button = $(this);
            
            $('#editDocumentId').val(button.data('document-id'));
            $('#editDocumentTitle').val(button.data('title'));
            $('#editDocumentDescription').val(button.data('description'));
            $('#editDocumentCurrentFile').val(button.data('filename'));
            $('#editDocumentDisplayOrder').val(button.data('display-order'));
            $('#editDocumentActive').prop('checked', button.data('is-active') == 1);
            
            // Show current file preview
            const fileUrl = button.data('file-url');
            const fileName = button.data('filename');
            const fileType = button.data('file-type');
            
            const fileIcon = getFileIcon(fileType);
            const filePreview = `
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="${fileIcon} fa-2x"></i>
                    </div>
                    <div>
                        <strong>${fileName}</strong><br>
                        <small class="text-muted">${fileUrl}</small>
                    </div>
                </div>
            `;
            $('#editDocumentCurrentFilePreview').html(filePreview);
            
            $('#editDocumentModal').modal('show');
        });

        function getFileIcon(fileType) {
            const icons = {
                'PDF': 'fas fa-file-pdf text-danger',
                'Word': 'fas fa-file-word text-primary',
                'Excel': 'fas fa-file-excel text-success',
                'Image': 'fas fa-file-image text-info'
            };
            return icons[fileType] || 'fas fa-file text-secondary';
        }

        function confirmDeleteDocument(documentId) {
            Swal.fire({
                title: 'Delete Document?',
                html: 'This will permanently delete the document file and its record.<br><strong>This action cannot be undone.</strong>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    
                    $('#deleteDocumentId').val(documentId);
                    $('#deleteDocumentForm').submit();
                }
            });
        }

        // Document form validation
        $('#addDocumentForm, #editDocumentForm').submit(function(e) {
            const fileInput = $(this).find('input[name="document_file"]');
            
            if (fileInput.length && fileInput[0].files.length > 0) {
                const file = fileInput[0].files[0];
                const maxSize = 10 * 1024 * 1024; // 10MB
                
                if (file.size > maxSize) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'File size exceeds 10MB limit. Please choose a smaller file.',
                        confirmButtonColor: '#00796B'
                    });
                    return false;
                }
                
                const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
                const extension = file.name.split('.').pop().toLowerCase();
                
                if (!allowedExtensions.includes(extension)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Allowed file types: PDF, Word, Excel, Images (JPG, PNG)',
                        confirmButtonColor: '#00796B'
                    });
                    return false;
                }
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
    </script>
</body>
</html>