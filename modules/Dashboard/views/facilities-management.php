<?php
// modules/Dashboard/views/facilities-management.php
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
    
    if ($action === 'add_facility') {
        try {
            $page_type = $_POST['page_type'];
            $title = $_POST['title'];
            $slug = $_POST['slug'];
            $subtitle = $_POST['subtitle'] ?? '';
            $icon_class = $_POST['icon_class'];
            $featured_image = $_POST['featured_image'] ?? '';
            $short_description = $_POST['short_description'] ?? '';
            $detailed_content = $_POST['detailed_content'] ?? '';
            $display_order = $_POST['display_order'] ?? 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate inputs
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $slug = htmlspecialchars(strtolower(str_replace(' ', '-', $slug)), ENT_QUOTES, 'UTF-8');
            $subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
            $short_description = htmlspecialchars($short_description, ENT_QUOTES, 'UTF-8');
            $detailed_content = htmlspecialchars($detailed_content, ENT_QUOTES, 'UTF-8');
            
            $stmt = $pdo->prepare("INSERT INTO facilities_sections (page_type, title, slug, subtitle, icon_class, featured_image, short_description, detailed_content, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$page_type, $title, $slug, $subtitle, $icon_class, $featured_image, $short_description, $detailed_content, $display_order, $is_active]);
            
            session_start();
            $_SESSION['success_message'] = 'Facility added successfully!';
            $_SESSION['success_action'] = 'add_facility';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to add facility: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'update_facility') {
        try {
            $id = $_POST['id'];
            $page_type = $_POST['page_type'];
            $title = $_POST['title'];
            $slug = $_POST['slug'];
            $subtitle = $_POST['subtitle'] ?? '';
            $icon_class = $_POST['icon_class'];
            $featured_image = $_POST['featured_image'] ?? '';
            $short_description = $_POST['short_description'] ?? '';
            $detailed_content = $_POST['detailed_content'] ?? '';
            $display_order = $_POST['display_order'] ?? 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate inputs
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $slug = htmlspecialchars(strtolower(str_replace(' ', '-', $slug)), ENT_QUOTES, 'UTF-8');
            $subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
            $short_description = htmlspecialchars($short_description, ENT_QUOTES, 'UTF-8');
            $detailed_content = htmlspecialchars($detailed_content, ENT_QUOTES, 'UTF-8');
            
            $stmt = $pdo->prepare("UPDATE facilities_sections SET page_type = ?, title = ?, slug = ?, subtitle = ?, icon_class = ?, featured_image = ?, short_description = ?, detailed_content = ?, display_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$page_type, $title, $slug, $subtitle, $icon_class, $featured_image, $short_description, $detailed_content, $display_order, $is_active, $id]);
            
            session_start();
            $_SESSION['success_message'] = 'Facility updated successfully!';
            $_SESSION['success_action'] = 'update_facility';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update facility: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'delete_facility') {
        try {
            $id = $_POST['id'];
            
            // First delete all features and images for this facility
            $stmt = $pdo->prepare("DELETE FROM facility_features WHERE facility_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $pdo->prepare("DELETE FROM facility_images WHERE facility_id = ?");
            $stmt->execute([$id]);
            
            // Then delete the facility
            $stmt = $pdo->prepare("DELETE FROM facilities_sections WHERE id = ?");
            $stmt->execute([$id]);
            
            session_start();
            $_SESSION['success_message'] = 'Facility and all its related content deleted successfully!';
            $_SESSION['success_action'] = 'delete_facility';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to delete facility: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'add_feature') {
        try {
            $facility_id = $_POST['facility_id'];
            $title = $_POST['title'];
            $description = $_POST['description'] ?? '';
            $icon = $_POST['icon'] ?? '';
            $display_order = $_POST['display_order'] ?? 0;
            
            // Validate inputs
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
            
            $stmt = $pdo->prepare("INSERT INTO facility_features (facility_id, title, description, icon, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$facility_id, $title, $description, $icon, $display_order]);
            
            session_start();
            $_SESSION['success_message'] = 'Feature added successfully!';
            $_SESSION['success_action'] = 'add_feature';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to add feature: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'update_feature') {
        try {
            $id = $_POST['id'];
            $facility_id = $_POST['facility_id'];
            $title = $_POST['title'];
            $description = $_POST['description'] ?? '';
            $icon = $_POST['icon'] ?? '';
            $display_order = $_POST['display_order'] ?? 0;
            
            // Validate inputs
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
            
            $stmt = $pdo->prepare("UPDATE facility_features SET title = ?, description = ?, icon = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$title, $description, $icon, $display_order, $id]);
            
            session_start();
            $_SESSION['success_message'] = 'Feature updated successfully!';
            $_SESSION['success_action'] = 'update_feature';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update feature: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'delete_feature') {
        try {
            $id = $_POST['id'];
            
            $stmt = $pdo->prepare("DELETE FROM facility_features WHERE id = ?");
            $stmt->execute([$id]);
            
            session_start();
            $_SESSION['success_message'] = 'Feature deleted successfully!';
            $_SESSION['success_action'] = 'delete_feature';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to delete feature: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'add_image') {
        try {
            $facility_id = $_POST['facility_id'];
            $title = $_POST['title'];
            $description = $_POST['description'] ?? '';
            $display_order = $_POST['display_order'] ?? 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/facilities/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $extension = getFileExtension($_FILES['image']['name']);
                
                if (!in_array($extension, $allowed_extensions)) {
                    throw new Exception("Invalid file format. Allowed formats: JPG, PNG, GIF, WebP");
                }
                
                if ($_FILES['image']['size'] > 5242880) { // 5MB
                    throw new Exception("File size too large. Maximum size is 5MB");
                }
                
                // Get facility name for filename prefix
                $stmt = $pdo->prepare("SELECT slug FROM facilities_sections WHERE id = ?");
                $stmt->execute([$facility_id]);
                $facility = $stmt->fetch();
                $facility_slug = $facility['slug'] ?? 'facility';
                
                $filename = generateUniqueFilename($facility_slug, $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = '/facilities/' . $filename;
                    
                    // If this is featured, unfeature all other images for this facility
                    if ($is_featured) {
                        $stmt = $pdo->prepare("UPDATE facility_images SET is_featured = 0 WHERE facility_id = ?");
                        $stmt->execute([$facility_id]);
                    }
                    
                    $stmt = $pdo->prepare("INSERT INTO facility_images (facility_id, title, filename, image_url, description, display_order, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$facility_id, $title, $filename, $image_url, $description, $display_order, $is_featured]);
                    
                    session_start();
                    $_SESSION['success_message'] = 'Image added successfully!';
                    $_SESSION['success_action'] = 'add_image';
                    
                    header("Location: " . url('admin/facilities'));
                    exit;
                } else {
                    throw new Exception("Failed to upload image");
                }
            } else {
                throw new Exception("Please select an image to upload");
            }
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to add image: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'update_image') {
        try {
            $id = $_POST['id'];
            $facility_id = $_POST['facility_id'];
            $title = $_POST['title'];
            $description = $_POST['description'] ?? '';
            $display_order = $_POST['display_order'] ?? 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            // Get current image info
            $stmt = $pdo->prepare("SELECT filename, image_url FROM facility_images WHERE id = ?");
            $stmt->execute([$id]);
            $currentImage = $stmt->fetch();
            
            $filename = $currentImage['filename'];
            $image_url = $currentImage['image_url'];
            
            // Handle new image upload if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = $root_path . '/img/facilities/';
                
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $extension = getFileExtension($_FILES['image']['name']);
                
                if (!in_array($extension, $allowed_extensions)) {
                    throw new Exception("Invalid file format. Allowed formats: JPG, PNG, GIF, WebP");
                }
                
                if ($_FILES['image']['size'] > 5242880) {
                    throw new Exception("File size too large. Maximum size is 5MB");
                }
                
                // Delete old image if exists
                if ($filename) {
                    $old_file = $upload_dir . $filename;
                    deleteFileIfExists($old_file);
                }
                
                // Get facility name for filename prefix
                $stmt = $pdo->prepare("SELECT slug FROM facilities_sections WHERE id = ?");
                $stmt->execute([$facility_id]);
                $facility = $stmt->fetch();
                $facility_slug = $facility['slug'] ?? 'facility';
                
                $filename = generateUniqueFilename($facility_slug, $extension);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = '/facilities/' . $filename;
                } else {
                    throw new Exception("Failed to upload image");
                }
            }
            
            // If this is featured, unfeature all other images for this facility
            if ($is_featured) {
                $stmt = $pdo->prepare("UPDATE facility_images SET is_featured = 0 WHERE facility_id = ? AND id != ?");
                $stmt->execute([$facility_id, $id]);
            }
            
            $stmt = $pdo->prepare("UPDATE facility_images SET title = ?, filename = ?, image_url = ?, description = ?, display_order = ?, is_featured = ? WHERE id = ?");
            $stmt->execute([$title, $filename, $image_url, $description, $display_order, $is_featured, $id]);
            
            session_start();
            $_SESSION['success_message'] = 'Image updated successfully!';
            $_SESSION['success_action'] = 'update_image';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update image: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'delete_image') {
        try {
            $id = $_POST['id'];
            
            // Get image info before deleting
            $stmt = $pdo->prepare("SELECT filename FROM facility_images WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetch();
            
            if ($image && $image['filename']) {
                $upload_dir = $root_path . '/img/facilities/';
                $file_path = $upload_dir . $image['filename'];
                deleteFileIfExists($file_path);
            }
            
            $stmt = $pdo->prepare("DELETE FROM facility_images WHERE id = ?");
            $stmt->execute([$id]);
            
            session_start();
            $_SESSION['success_message'] = 'Image deleted successfully!';
            $_SESSION['success_action'] = 'delete_image';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to delete image: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'update_facility_order') {
        try {
            $orders = $_POST['order'] ?? [];
            
            foreach ($orders as $id => $order) {
                $stmt = $pdo->prepare("UPDATE facilities_sections SET display_order = ? WHERE id = ?");
                $stmt->execute([$order, $id]);
            }
            
            session_start();
            $_SESSION['success_message'] = 'Facility order updated successfully!';
            $_SESSION['success_action'] = 'facility_order_update';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update facility order: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'update_feature_order') {
        try {
            $orders = $_POST['order'] ?? [];
            $facility_id = $_POST['facility_id'];
            
            foreach ($orders as $id => $order) {
                $stmt = $pdo->prepare("UPDATE facility_features SET display_order = ? WHERE id = ? AND facility_id = ?");
                $stmt->execute([$order, $id, $facility_id]);
            }
            
            session_start();
            $_SESSION['success_message'] = 'Feature order updated successfully!';
            $_SESSION['success_action'] = 'feature_order_update';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update feature order: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
    
    if ($action === 'update_image_order') {
        try {
            $orders = $_POST['order'] ?? [];
            $facility_id = $_POST['facility_id'];
            
            foreach ($orders as $id => $order) {
                $stmt = $pdo->prepare("UPDATE facility_images SET display_order = ? WHERE id = ? AND facility_id = ?");
                $stmt->execute([$order, $id, $facility_id]);
            }
            
            session_start();
            $_SESSION['success_message'] = 'Image order updated successfully!';
            $_SESSION['success_action'] = 'image_order_update';
            
            header("Location: " . url('admin/facilities'));
            exit;
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed to update image order: ' . $e->getMessage();
            header("Location: " . url('admin/facilities'));
            exit;
        }
    }
}

// Fetch all facilities with their counts
$stmt = $pdo->query("SELECT f.*, 
                    (SELECT COUNT(*) FROM facility_features WHERE facility_id = f.id) as feature_count,
                    (SELECT COUNT(*) FROM facility_images WHERE facility_id = f.id) as image_count
                    FROM facilities_sections f 
                    ORDER BY f.page_type, f.display_order, f.created_at DESC");
$facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all features grouped by facility
$featuresByFacility = [];
$imagesByFacility = [];
foreach ($facilities as $facility) {
    // Get features
    $stmt = $pdo->prepare("SELECT * FROM facility_features WHERE facility_id = ? ORDER BY display_order, created_at DESC");
    $stmt->execute([$facility['id']]);
    $featuresByFacility[$facility['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get images
    $stmt = $pdo->prepare("SELECT * FROM facility_images WHERE facility_id = ? ORDER BY is_featured DESC, display_order, created_at DESC");
    $stmt->execute([$facility['id']]);
    $imagesByFacility[$facility['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// FontAwesome icons for selection
$fontawesome_icons = [
    'fas fa-laptop-code' => 'Computer Lab',
    'fas fa-book-reader' => 'Library',
    'fas fa-futbol' => 'Sports',
    'fas fa-swimmer' => 'Swimming',
    'fas fa-utensils' => 'School Feeding',
    'fas fa-bus' => 'School Transport',
    'fas fa-building' => 'Building',
    'fas fa-graduation-cap' => 'Academic',
    'fas fa-running' => 'Running',
    'fas fa-basketball-ball' => 'Basketball',
    'fas fa-volleyball-ball' => 'Volleyball',
    'fas fa-child' => 'Child',
    'fas fa-users' => 'Users',
    'fas fa-desktop' => 'Computer',
    'fas fa-cogs' => 'Gears',
    'fas fa-chalkboard-teacher' => 'Interactive',
    'fas fa-books' => 'Books',
    'fas fa-couch' => 'Comfort',
    'fas fa-tablet-alt' => 'Digital',
    'fas fa-user-tie' => 'Professional',
    'fas fa-baseball-ball' => 'Sports Equipment',
    'fas fa-life-ring' => 'Lifeguard',
    'fas fa-thermometer-half' => 'Temperature',
    'fas fa-clipboard-check' => 'Nutritionist',
    'fas fa-shield-alt' => 'Safety',
    'fas fa-map-marker-alt' => 'GPS',
    'fas fa-user-shield' => 'Attendant'
];

// Page types
$page_types = [
    'academic' => 'Academic Facilities',
    'sports' => 'Sports & Recreation',
    'services' => 'School Services'
];

// Page type colors for badges
$page_type_colors = [
    'academic' => 'bg-primary',
    'sports' => 'bg-success',
    'services' => 'bg-purple'
];

// Page type icons
$page_type_icons = [
    'academic' => 'fas fa-graduation-cap',
    'sports' => 'fas fa-futbol',
    'services' => 'fas fa-concierge-bell'
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
    <title>Facilities Management - Mount Carmel School</title>
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
        .facility-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.3s;
            height: 100%;
        }
        .facility-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .facility-card.ui-sortable-helper {
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transform: rotate(2deg);
        }
        .facility-card.ui-sortable-placeholder {
            border: 2px dashed #00796B;
            background: rgba(0, 121, 107, 0.1);
            visibility: visible !important;
        }
        .facility-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 15px;
        }
        .academic-icon {
            background: linear-gradient(135deg, #2980b9, #1a5276);
            color: white;
        }
        .sports-icon {
            background: linear-gradient(135deg, #27ae60, #15653d);
            color: white;
        }
        .services-icon {
            background: linear-gradient(135deg, #9b59b6, #5b2c83);
            color: white;
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
        .feature-badge {
            background-color: #e3f2fd;
            color: #1565c0;
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 10px;
        }
        .image-badge {
            background-color: #f3e5f5;
            color: #7b1fa2;
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 10px;
        }
        .featured-badge {
            background-color: #fff3cd;
            color: #856404;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
        }
        .collapse-toggle {
            cursor: pointer;
            transition: all 0.3s;
        }
        .collapse-toggle:hover {
            color: #00796B;
        }
        .count-badge {
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            margin-left: 5px;
        }
        .image-preview-container {
            max-width: 200px;
            margin: 10px 0;
        }
        .image-preview {
            max-width: 100%;
            height: auto;
            max-height: 150px;
            object-fit: contain;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 5px;
            background: #f8f9fa;
        }
        .bg-purple {
            background-color: #9b59b6 !important;
        }
        .text-purple {
            color: #9b59b6 !important;
        }
        .border-purple {
            border-color: #9b59b6 !important;
        }
        .page-type-filter {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .page-type-btn {
            border: 2px solid #dee2e6;
            background: white;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .page-type-btn:hover, .page-type-btn.active {
            border-color: #00796B;
            background: #f0f9ff;
        }
        .page-type-btn.academic.active {
            border-color: #2980b9;
            background: #e3f2fd;
        }
        .page-type-btn.sports.active {
            border-color: #27ae60;
            background: #e8f5e9;
        }
        .page-type-btn.services.active {
            border-color: #9b59b6;
            background: #f3e5f5;
        }
        .preview-content {
            max-height: 100px;
            overflow: hidden;
            position: relative;
        }
        .preview-content::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(to bottom, transparent, #f8f9fa);
        }
        .form-preview-image {
            max-width: 300px;
            max-height: 200px;
            object-fit: contain;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
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
                        <h2 class="mb-0">Facilities Management</h2>
                        <p class="text-muted mb-0">Manage academic, sports, and service facilities for the website</p>
                    </div>
                    <div>
                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addFacilityModal">
                            <i class="fas fa-plus me-2"></i> Add Facility
                        </button>
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addFeatureModal">
                            <i class="fas fa-star me-2"></i> Add Feature
                        </button>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addImageModal">
                            <i class="fas fa-image me-2"></i> Add Image
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Type Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-type-filter">
                    <h6 class="mb-3">Filter by Page Type:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="page-type-btn all active" onclick="filterFacilities('all')">
                            <i class="fas fa-layer-group me-2"></i> All Facilities
                        </button>
                        <button class="page-type-btn academic" onclick="filterFacilities('academic')">
                            <i class="fas fa-graduation-cap me-2"></i> Academic
                        </button>
                        <button class="page-type-btn sports" onclick="filterFacilities('sports')">
                            <i class="fas fa-futbol me-2"></i> Sports & Recreation
                        </button>
                        <button class="page-type-btn services" onclick="filterFacilities('services')">
                            <i class="fas fa-concierge-bell me-2"></i> School Services
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
                                <p class="mb-0 small text-muted">Drag facilities by the handle (<i class="fas fa-arrows-alt"></i>) to reorder them. Click on facilities to expand/collapse features and images.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Facilities Grid -->
        <div class="row" id="sortableFacilities">
            <?php if (empty($facilities)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h4>No facilities added yet</h4>
                            <p class="text-muted">Click the "Add Facility" button to add your first facility.</p>
                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addFacilityModal">
                                <i class="fas fa-plus me-2"></i> Add First Facility
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($facilities as $index => $facility): ?>
                    <div class="col-md-6 col-lg-4 mb-4 facility-item" data-id="<?= $facility['id'] ?>" data-page-type="<?= $facility['page_type'] ?>">
                        <div class="card facility-card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="sortable-handle me-2">
                                        <i class="fas fa-arrows-alt"></i>
                                    </span>
                                    <div class="order-badge <?= $page_type_colors[$facility['page_type']] ?>">
                                        <?= $facility['display_order'] + 1 ?>
                                    </div>
                                    <span class="badge <?= $page_type_colors[$facility['page_type']] ?> ms-2" title="Page Type">
                                        <?= $page_types[$facility['page_type']] ?>
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
                                                    data-bs-target="#editFacilityModal"
                                                    data-facility-id="<?= $facility['id'] ?>"
                                                    data-page-type="<?= $facility['page_type'] ?>"
                                                    data-title="<?= htmlspecialchars($facility['title']) ?>"
                                                    data-slug="<?= htmlspecialchars($facility['slug']) ?>"
                                                    data-subtitle="<?= htmlspecialchars($facility['subtitle'] ?? '') ?>"
                                                    data-icon-class="<?= $facility['icon_class'] ?>"
                                                    data-featured-image="<?= htmlspecialchars($facility['featured_image'] ?? '') ?>"
                                                    data-short-description="<?= htmlspecialchars($facility['short_description'] ?? '') ?>"
                                                    data-detailed-content="<?= htmlspecialchars($facility['detailed_content'] ?? '') ?>"
                                                    data-display-order="<?= $facility['display_order'] ?>"
                                                    data-is-active="<?= $facility['is_active'] ?>">
                                                <i class="fas fa-edit me-2 text-primary"></i> Edit Facility
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addFeatureModal"
                                                    data-facility-id="<?= $facility['id'] ?>">
                                                <i class="fas fa-plus me-2 text-success"></i> Add Feature
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addImageModal"
                                                    data-facility-id="<?= $facility['id'] ?>">
                                                <i class="fas fa-image me-2 text-info"></i> Add Image
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="confirmDeleteFacility(<?= $facility['id'] ?>)">
                                                <i class="fas fa-trash me-2"></i> Delete Facility
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <div class="facility-icon <?= $facility['page_type'] ?>-icon">
                                    <i class="<?= $facility['icon_class'] ?>"></i>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($facility['title']) ?></h5>
                                <?php if (!empty($facility['subtitle'])): ?>
                                    <p class="card-text text-muted small"><?= htmlspecialchars($facility['subtitle']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($facility['short_description'])): ?>
                                    <div class="preview-content mt-2">
                                        <p class="card-text small text-muted"><?= htmlspecialchars(substr($facility['short_description'], 0, 150)) . (strlen($facility['short_description']) > 150 ? '...' : '') ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="mt-3">
                                    <span class="badge <?= $facility['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $facility['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                    <span class="badge bg-light text-dark ms-2 feature-badge">
                                        <i class="fas fa-star me-1"></i> <?= $facility['feature_count'] ?> Features
                                    </span>
                                    <span class="badge bg-light text-dark ms-2 image-badge">
                                        <i class="fas fa-image me-1"></i> <?= $facility['image_count'] ?> Images
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <!-- Features Toggle -->
                                <div class="d-flex justify-content-between align-items-center collapse-toggle mb-2" 
                                     data-bs-toggle="collapse" 
                                     data-bs-target="#featuresSection<?= $facility['id'] ?>">
                                    <span>
                                        <i class="fas fa-star me-2"></i>
                                        Key Features
                                        <span class="badge bg-secondary count-badge"><?= $facility['feature_count'] ?></span>
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="collapse mb-3" id="featuresSection<?= $facility['id'] ?>">
                                    <?php if (!empty($featuresByFacility[$facility['id']])): ?>
                                        <div class="features-list" id="sortableFeatures<?= $facility['id'] ?>">
                                            <?php foreach ($featuresByFacility[$facility['id']] as $featureIndex => $feature): ?>
                                                <div class="content-item-card p-3" data-id="<?= $feature['id'] ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span class="sortable-handle me-2">
                                                                <i class="fas fa-arrows-alt"></i>
                                                            </span>
                                                            <div>
                                                                <strong><?= htmlspecialchars($feature['title']) ?></strong>
                                                                <?php if (!empty($feature['icon'])): ?>
                                                                    <i class="<?= $feature['icon'] ?> ms-2 text-muted"></i>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle edit-feature-btn" 
                                                                    type="button"
                                                                    data-feature-id="<?= $feature['id'] ?>"
                                                                    data-facility-id="<?= $feature['facility_id'] ?>"
                                                                    data-title="<?= htmlspecialchars($feature['title']) ?>"
                                                                    data-description="<?= htmlspecialchars($feature['description'] ?? '') ?>"
                                                                    data-icon="<?= $feature['icon'] ?? '' ?>"
                                                                    data-display-order="<?= $feature['display_order'] ?>">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <button class="dropdown-item edit-feature-btn"
                                                                            data-feature-id="<?= $feature['id'] ?>"
                                                                            data-facility-id="<?= $feature['facility_id'] ?>"
                                                                            data-title="<?= htmlspecialchars($feature['title']) ?>"
                                                                            data-description="<?= htmlspecialchars($feature['description'] ?? '') ?>"
                                                                            data-icon="<?= $feature['icon'] ?? '' ?>"
                                                                            data-display-order="<?= $feature['display_order'] ?>">
                                                                        <i class="fas fa-edit me-2 text-primary"></i> Edit
                                                                    </button>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <button class="dropdown-item text-danger" onclick="confirmDeleteFeature(<?= $feature['id'] ?>)">
                                                                        <i class="fas fa-trash me-2"></i> Delete
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($feature['description'])): ?>
                                                        <p class="small text-muted mb-0"><?= htmlspecialchars(substr($feature['description'], 0, 100)) . (strlen($feature['description']) > 100 ? '...' : '') ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-2">
                                            <p class="text-muted mb-2 small">No features added yet</p>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addFeatureModal"
                                                    data-facility-id="<?= $facility['id'] ?>">
                                                <i class="fas fa-plus me-1"></i> Add Feature
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Images Toggle -->
                                <div class="d-flex justify-content-between align-items-center collapse-toggle mb-2" 
                                     data-bs-toggle="collapse" 
                                     data-bs-target="#imagesSection<?= $facility['id'] ?>">
                                    <span>
                                        <i class="fas fa-image me-2"></i>
                                        Gallery Images
                                        <span class="badge bg-secondary count-badge"><?= $facility['image_count'] ?></span>
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="collapse" id="imagesSection<?= $facility['id'] ?>">
                                    <?php if (!empty($imagesByFacility[$facility['id']])): ?>
                                        <div class="images-list" id="sortableImages<?= $facility['id'] ?>">
                                            <?php foreach ($imagesByFacility[$facility['id']] as $imageIndex => $image): ?>
                                                <div class="content-item-card p-3" data-id="<?= $image['id'] ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span class="sortable-handle me-2">
                                                                <i class="fas fa-arrows-alt"></i>
                                                            </span>
                                                            <div>
                                                                <strong><?= htmlspecialchars($image['title']) ?></strong>
                                                                <?php if ($image['is_featured']): ?>
                                                                    <span class="featured-badge">Featured</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle edit-image-btn" 
                                                                    type="button"
                                                                    data-image-id="<?= $image['id'] ?>"
                                                                    data-facility-id="<?= $image['facility_id'] ?>"
                                                                    data-title="<?= htmlspecialchars($image['title']) ?>"
                                                                    data-image-url="<?= htmlspecialchars($image['image_url']) ?>"
                                                                    data-description="<?= htmlspecialchars($image['description'] ?? '') ?>"
                                                                    data-display-order="<?= $image['display_order'] ?>"
                                                                    data-is-featured="<?= $image['is_featured'] ?>">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <button class="dropdown-item edit-image-btn"
                                                                            data-image-id="<?= $image['id'] ?>"
                                                                            data-facility-id="<?= $image['facility_id'] ?>"
                                                                            data-title="<?= htmlspecialchars($image['title']) ?>"
                                                                            data-image-url="<?= htmlspecialchars($image['image_url']) ?>"
                                                                            data-description="<?= htmlspecialchars($image['description'] ?? '') ?>"
                                                                            data-display-order="<?= $image['display_order'] ?>"
                                                                            data-is-featured="<?= $image['is_featured'] ?>">
                                                                        <i class="fas fa-edit me-2 text-primary"></i> Edit
                                                                    </button>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <button class="dropdown-item text-danger" onclick="confirmDeleteImage(<?= $image['id'] ?>)">
                                                                        <i class="fas fa-trash me-2"></i> Delete
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($image['description'])): ?>
                                                        <p class="small text-muted mb-2"><?= htmlspecialchars(substr($image['description'], 0, 80)) . (strlen($image['description']) > 80 ? '...' : '') ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($image['image_url'])): ?>
                                                        <div class="image-preview-container">
                                                            <img src="<?= img_url(htmlspecialchars($image['image_url'])) ?>" 
                                                                 alt="<?= htmlspecialchars($image['title']) ?>" 
                                                                 class="image-preview">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-2">
                                            <p class="text-muted mb-2 small">No images added yet</p>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addImageModal"
                                                    data-facility-id="<?= $facility['id'] ?>">
                                                <i class="fas fa-plus me-1"></i> Add Image
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Facility Modal -->
    <div class="modal fade" id="addFacilityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Facility</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addFacilityForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_facility">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Page Type *</label>
                                <select class="form-select" name="page_type" id="addFacilityPageType" required>
                                    <option value="">Select page type</option>
                                    <?php foreach ($page_types as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i id="addFacilityIconPreview" class="fas fa-info-circle"></i>
                                    </span>
                                    <select class="form-select" name="icon_class" id="addFacilityIconClass" required>
                                        <option value="">Select an icon</option>
                                        <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                            <option value="<?= $icon ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" required maxlength="255" 
                                       placeholder="e.g., Computer Lab, Swimming Pool" id="addFacilityTitle">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Slug *</label>
                                <input type="text" class="form-control" name="slug" required maxlength="255" 
                                       placeholder="e.g., computer-lab" id="addFacilitySlug">
                                <small class="text-muted">URL-friendly version of title</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <input type="text" class="form-control" name="subtitle" maxlength="500" 
                                   placeholder="Brief description (appears under title)">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Featured Image Path</label>
                            <input type="text" class="form-control" name="featured_image" 
                                   placeholder="/facilities/computer-lab.jpg">
                            <small class="text-muted">Path to main image (e.g., /facilities/filename.jpg)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Short Description *</label>
                            <textarea class="form-control" name="short_description" rows="3" required 
                                      placeholder="Brief description that appears on cards"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Detailed Content (HTML allowed)</label>
                            <textarea class="form-control" name="detailed_content" rows="8" 
                                      placeholder="<h3>Detailed Content</h3><p>Full description with HTML formatting...</p>"></textarea>
                            <small class="text-muted">Supports HTML tags for rich content</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0" min="0" max="999">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="addFacilityActive" checked>
                                    <label class="form-check-label" for="addFacilityActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Facility</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Facility Modal -->
    <div class="modal fade" id="editFacilityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Facility</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editFacilityForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_facility">
                        <input type="hidden" name="id" id="editFacilityId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Page Type *</label>
                                <select class="form-select" name="page_type" id="editFacilityPageType" required>
                                    <option value="">Select page type</option>
                                    <?php foreach ($page_types as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i id="editFacilityIconPreview" class="fas fa-info-circle"></i>
                                    </span>
                                    <select class="form-select" name="icon_class" id="editFacilityIconClass" required>
                                        <option value="">Select an icon</option>
                                        <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                            <option value="<?= $icon ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" id="editFacilityTitle" required maxlength="255">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Slug *</label>
                                <input type="text" class="form-control" name="slug" id="editFacilitySlug" required maxlength="255">
                                <small class="text-muted">URL-friendly version of title</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <input type="text" class="form-control" name="subtitle" id="editFacilitySubtitle" maxlength="500">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Featured Image Path</label>
                            <input type="text" class="form-control" name="featured_image" id="editFacilityFeaturedImage">
                            <small class="text-muted">Path to main image (e.g., /facilities/filename.jpg)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Short Description *</label>
                            <textarea class="form-control" name="short_description" id="editFacilityShortDescription" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Detailed Content (HTML allowed)</label>
                            <textarea class="form-control" name="detailed_content" id="editFacilityDetailedContent" rows="8"></textarea>
                            <small class="text-muted">Supports HTML tags for rich content</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editFacilityDisplayOrder" min="0" max="999">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="editFacilityActive">
                                    <label class="form-check-label" for="editFacilityActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Facility</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Feature Modal -->
    <div class="modal fade" id="addFeatureModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Key Feature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addFeatureForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_feature">
                        
                        <div class="mb-3">
                            <label class="form-label">Facility *</label>
                            <select class="form-select" name="facility_id" id="addFeatureFacilityId" required>
                                <option value="">Select a facility</option>
                                <?php foreach ($facilities as $facility): ?>
                                    <option value="<?= $facility['id'] ?>"><?= htmlspecialchars($facility['title']) ?> (<?= $page_types[$facility['page_type']] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" required maxlength="255" 
                                   placeholder="e.g., High-Speed Computers, Professional Coaching">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Brief description of this feature"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon</label>
                                <select class="form-select" name="icon" id="addFeatureIcon">
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Feature</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Feature Modal -->
    <div class="modal fade" id="editFeatureModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Key Feature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editFeatureForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_feature">
                        <input type="hidden" name="id" id="editFeatureId">
                        <input type="hidden" name="facility_id" id="editFeatureFacilityId">
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" id="editFeatureTitle" required maxlength="255">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editFeatureDescription" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon</label>
                                <select class="form-select" name="icon" id="editFeatureIcon">
                                    <option value="">Select icon (optional)</option>
                                    <?php foreach ($fontawesome_icons as $icon => $label): ?>
                                        <option value="<?= $icon ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editFeatureDisplayOrder" min="0" max="999">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Feature</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Image Modal -->
    <div class="modal fade" id="addImageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Gallery Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addImageForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_image">
                        
                        <div class="mb-3">
                            <label class="form-label">Facility *</label>
                            <select class="form-select" name="facility_id" id="addImageFacilityId" required>
                                <option value="">Select a facility</option>
                                <?php foreach ($facilities as $facility): ?>
                                    <option value="<?= $facility['id'] ?>"><?= htmlspecialchars($facility['title']) ?> (<?= $page_types[$facility['page_type']] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" required maxlength="255" 
                                   placeholder="e.g., Computer Lab Interior, Swimming Pool View">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image *</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required 
                                   onchange="previewImage(this, 'addImagePreview')">
                            <div class="mt-2">
                                <img id="addImagePreview" src="" class="form-preview-image">
                            </div>
                            <small class="text-muted">Max file size: 5MB. Allowed formats: JPG, PNG, GIF, WebP</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2" 
                                      placeholder="Optional description for this image"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0" min="0" max="999">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="addImageFeatured">
                                    <label class="form-check-label" for="addImageFeatured">Set as Featured Image</label>
                                    <small class="d-block text-muted">Only one featured image per facility</small>
                                </div>
                            </div>
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

    <!-- Edit Image Modal -->
    <div class="modal fade" id="editImageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Gallery Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editImageForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_image">
                        <input type="hidden" name="id" id="editImageId">
                        <input type="hidden" name="facility_id" id="editImageFacilityId">
                        <input type="hidden" name="current_image" id="editCurrentImage">
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" id="editImageTitle" required maxlength="255">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" 
                                   onchange="previewImage(this, 'editImagePreviewNew')">
                            <div class="mt-2">
                                <img id="editImagePreviewNew" src="" class="form-preview-image">
                            </div>
                            <small class="text-muted">Leave empty to keep current image. Max file size: 5MB</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editImageDescription" rows="2"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editImageDisplayOrder" min="0" max="999">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="editImageFeatured">
                                    <label class="form-check-label" for="editImageFeatured">Set as Featured Image</label>
                                    <small class="d-block text-muted">Only one featured image per facility</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="image-preview-container">
                            <label class="form-label">Current Image:</label>
                            <img src="" alt="Preview" id="editImagePreview" class="image-preview">
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

    <!-- Delete Forms -->
    <form id="deleteFacilityForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_facility">
        <input type="hidden" name="id" id="deleteFacilityId">
    </form>
    
    <form id="deleteFeatureForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_feature">
        <input type="hidden" name="id" id="deleteFeatureId">
    </form>
    
    <form id="deleteImageForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_image">
        <input type="hidden" name="id" id="deleteImageId">
    </form>
    
    <!-- Order Forms -->
    <form id="updateFacilityOrderForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_facility_order">
        <input type="hidden" name="order" id="facilityOrderData">
    </form>
    
    <form id="updateFeatureOrderForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_feature_order">
        <input type="hidden" name="facility_id" id="featureFacilityId">
        <input type="hidden" name="order" id="featureOrderData">
    </form>
    
    <form id="updateImageOrderForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_image_order">
        <input type="hidden" name="facility_id" id="imageFacilityId">
        <input type="hidden" name="order" id="imageOrderData">
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
            
            // Initialize facility sortable
            $("#sortableFacilities").sortable({
                handle: ".sortable-handle",
                placeholder: "ui-sortable-placeholder",
                update: function(event, ui) {
                    updateFacilityDisplayOrder();
                }
            });
            $("#sortableFacilities").disableSelection();
            
            // Initialize feature sortable for each facility
            <?php foreach ($facilities as $facility): ?>
                if ($('#sortableFeatures<?= $facility['id'] ?>').length) {
                    $('#sortableFeatures<?= $facility['id'] ?>').sortable({
                        handle: ".sortable-handle",
                        placeholder: "ui-sortable-placeholder",
                        update: function(event, ui) {
                            updateFeatureDisplayOrder(<?= $facility['id'] ?>);
                        }
                    });
                    $('#sortableFeatures<?= $facility['id'] ?>').disableSelection();
                }
                
                if ($('#sortableImages<?= $facility['id'] ?>').length) {
                    $('#sortableImages<?= $facility['id'] ?>').sortable({
                        handle: ".sortable-handle",
                        placeholder: "ui-sortable-placeholder",
                        update: function(event, ui) {
                            updateImageDisplayOrder(<?= $facility['id'] ?>);
                        }
                    });
                    $('#sortableImages<?= $facility['id'] ?>').disableSelection();
                }
            <?php endforeach; ?>
            
            // Facility icon preview
            $('#addFacilityIconClass').change(function() {
                const selectedIcon = $(this).val();
                $('#addFacilityIconPreview').attr('class', selectedIcon);
            });
            
            $('#editFacilityIconClass').change(function() {
                const selectedIcon = $(this).val();
                $('#editFacilityIconPreview').attr('class', selectedIcon);
            });
            
            // Auto-generate slug from title
            $('#addFacilityTitle').on('blur', function() {
                const title = $(this).val();
                if (title && !$('#addFacilitySlug').val()) {
                    const slug = title.toLowerCase()
                        .replace(/[^\w\s-]/g, '') // Remove special characters
                        .replace(/\s+/g, '-') // Replace spaces with hyphens
                        .replace(/-+/g, '-'); // Remove consecutive hyphens
                    $('#addFacilitySlug').val(slug);
                }
            });
            
            // Edit facility modal data
            $('#editFacilityModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                
                modal.find('#editFacilityId').val(button.data('facility-id'));
                modal.find('#editFacilityPageType').val(button.data('page-type'));
                modal.find('#editFacilityTitle').val(button.data('title'));
                modal.find('#editFacilitySlug').val(button.data('slug'));
                modal.find('#editFacilitySubtitle').val(button.data('subtitle'));
                modal.find('#editFacilityIconClass').val(button.data('icon-class'));
                modal.find('#editFacilityFeaturedImage').val(button.data('featured-image'));
                modal.find('#editFacilityShortDescription').val(button.data('short-description'));
                modal.find('#editFacilityDetailedContent').val(button.data('detailed-content'));
                modal.find('#editFacilityDisplayOrder').val(button.data('display-order'));
                modal.find('#editFacilityActive').prop('checked', button.data('is-active') == 1);
                
                // Update icon preview
                $('#editFacilityIconPreview').attr('class', button.data('icon-class'));
            });
            
            // Edit feature button click handler
            $(document).on('click', '.edit-feature-btn', function(e) {
                e.preventDefault();
                
                const button = $(this);
                const modal = $('#editFeatureModal');
                
                modal.find('#editFeatureId').val(button.data('feature-id'));
                modal.find('#editFeatureFacilityId').val(button.data('facility-id'));
                modal.find('#editFeatureTitle').val(button.data('title'));
                modal.find('#editFeatureDescription').val(button.data('description'));
                modal.find('#editFeatureIcon').val(button.data('icon'));
                modal.find('#editFeatureDisplayOrder').val(button.data('display-order'));
                
                modal.modal('show');
            });
            
            // Edit image button click handler
            $(document).on('click', '.edit-image-btn', function(e) {
                e.preventDefault();
                
                const button = $(this);
                const modal = $('#editImageModal');
                
                modal.find('#editImageId').val(button.data('image-id'));
                modal.find('#editImageFacilityId').val(button.data('facility-id'));
                modal.find('#editImageTitle').val(button.data('title'));
                modal.find('#editImageDescription').val(button.data('description'));
                modal.find('#editImageDisplayOrder').val(button.data('display-order'));
                modal.find('#editImageFeatured').prop('checked', button.data('is-featured') == 1);
                modal.find('#editCurrentImage').val(button.data('image-url'));
                
                // Show current image preview
                const imageUrl = '<?= img_url("") ?>' + button.data('image-url');
                $('#editImagePreview').attr('src', imageUrl).attr('alt', button.data('title'));
                
                modal.modal('show');
            });
                
            // Add feature modal with facility pre-selected
            $('#addFeatureModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                if (button.data('facility-id')) {
                    $('#addFeatureFacilityId').val(button.data('facility-id'));
                }
            });
            
            // Add image modal with facility pre-selected
            $('#addImageModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                if (button.data('facility-id')) {
                    $('#addImageFacilityId').val(button.data('facility-id'));
                }
            });
                
            // Facility form submission
            $('#addFacilityForm, #editFacilityForm').submit(function(e) {
                const title = $(this).find('input[name="title"]').val().trim();
                const slug = $(this).find('input[name="slug"]').val().trim();
                const shortDescription = $(this).find('textarea[name="short_description"]').val().trim();
                
                if (!title) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Title',
                        text: 'Please enter a facility title',
                        confirmButtonColor: '#00796B'
                    });
                    return false;
                }
                
                if (!slug) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Slug',
                        text: 'Please enter a URL-friendly slug',
                        confirmButtonColor: '#00796B'
                    });
                    return false;
                }
                
                if (!shortDescription) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Short Description',
                        text: 'Please enter a short description',
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
            
            // Feature form submission
            $('#addFeatureForm, #editFeatureForm').submit(function(e) {
                const title = $(this).find('input[name="title"]').val().trim();
                
                if (!title) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Title',
                        text: 'Please enter a feature title',
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
            
            // Image form submission
            $('#addImageForm, #editImageForm').submit(function(e) {
                const title = $(this).find('input[name="title"]').val().trim();
                const formId = $(this).attr('id');
                
                if (!title) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Title',
                        text: 'Please enter an image title',
                        confirmButtonColor: '#00796B'
                    });
                    return false;
                }
                
                // For add form, check if image is selected
                if (formId === 'addImageForm') {
                    const imageInput = $(this).find('input[name="image"]')[0];
                    if (!imageInput.files || !imageInput.files[0]) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Missing Image',
                            text: 'Please select an image to upload',
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
        });
        
        // Image preview function
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
        
        // Filter facilities by page type
        function filterFacilities(pageType) {
            $('.page-type-btn').removeClass('active');
            
            if (pageType === 'all') {
                $('.page-type-btn.all').addClass('active');
                $('.facility-item').show();
            } else {
                $(`.page-type-btn.${pageType}`).addClass('active');
                $('.facility-item').hide();
                $(`.facility-item[data-page-type="${pageType}"]`).show();
            }
        }
        
        // Update facility display order
        function updateFacilityDisplayOrder() {
            const orderData = {};
            $('#sortableFacilities .facility-item').each(function(index) {
                const facilityId = $(this).data('id');
                orderData[facilityId] = index;
                
                // Update the order badge
                $(this).find('.order-badge').text(index + 1);
            });
            
            // Send AJAX request to update order
            $.ajax({
                url: '<?= url('admin/facilities') ?>',
                method: 'POST',
                data: {
                    action: 'update_facility_order',
                    order: orderData
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Updated!',
                        text: 'Facility order has been saved',
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
                        text: 'Failed to update facility order',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            });
        }
        
        // Update feature display order for a facility
        function updateFeatureDisplayOrder(facilityId) {
            const orderData = {};
            $(`#sortableFeatures${facilityId} .content-item-card`).each(function(index) {
                const featureId = $(this).data('id');
                orderData[featureId] = index;
            });
            
            // Send AJAX request to update order
            $.ajax({
                url: '<?= url('admin/facilities') ?>',
                method: 'POST',
                data: {
                    action: 'update_feature_order',
                    facility_id: facilityId,
                    order: orderData
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Updated!',
                        text: 'Feature order has been saved',
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
                        text: 'Failed to update feature order',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            });
        }
        
        // Update image display order for a facility
        function updateImageDisplayOrder(facilityId) {
            const orderData = {};
            $(`#sortableImages${facilityId} .content-item-card`).each(function(index) {
                const imageId = $(this).data('id');
                orderData[imageId] = index;
            });
            
            // Send AJAX request to update order
            $.ajax({
                url: '<?= url('admin/facilities') ?>',
                method: 'POST',
                data: {
                    action: 'update_image_order',
                    facility_id: facilityId,
                    order: orderData
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Updated!',
                        text: 'Image order has been saved',
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
                        text: 'Failed to update image order',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            });
        }
        
        // Confirm delete facility
        function confirmDeleteFacility(facilityId) {
            Swal.fire({
                title: 'Delete Facility?',
                html: 'This will delete the facility and all its features and images.<br><strong>This action cannot be undone.</strong>',
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
                    
                    $('#deleteFacilityId').val(facilityId);
                    $('#deleteFacilityForm').submit();
                }
            });
        }
        
        // Confirm delete feature
        function confirmDeleteFeature(featureId) {
            Swal.fire({
                title: 'Delete Feature?',
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
                    
                    $('#deleteFeatureId').val(featureId);
                    $('#deleteFeatureForm').submit();
                }
            });
        }
        
        // Confirm delete image
        function confirmDeleteImage(imageId) {
            Swal.fire({
                title: 'Delete Image?',
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
                    
                    $('#deleteImageId').val(imageId);
                    $('#deleteImageForm').submit();
                }
            });
        }
    </script>
</body>
</html>