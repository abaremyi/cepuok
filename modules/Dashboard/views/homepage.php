<?php
require_once '../../../helpers/JWTHandler.php';
require_once '../../../config/database.php';

// Authentication check
if (!isset($_COOKIE['jwtToken'])) {
    header('Location: ../../Authentication/views/login.php');
    exit;
}

$jwtToken = $_COOKIE['jwtToken'];
$jwtHandler = new JWTHandler();
$decodedToken = $jwtHandler->validateToken($jwtToken);

if ($decodedToken === false) {
    header('Location: ../../Authentication/views/login.php');
    exit;
}

// Initialize database
$db = Database::getInstance();
$message = '';
$messageType = '';

// Handle all form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // 1. HERO SECTION MANAGEMENT
    if ($action === 'update_hero') {
        $title = $_POST['title'] ?? '';
        $subtitle = $_POST['subtitle'] ?? '';
        
        // Handle video upload
        $videoPath = null;
        if (isset($_FILES['hero_video']) && $_FILES['hero_video']['error'] == 0) {
            $uploadDir = '../../../assets/video/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'hero_' . time() . '_' . basename($_FILES['hero_video']['name']);
            $targetFilePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['hero_video']['tmp_name'], $targetFilePath)) {
                $videoPath = $fileName;
                
                // Delete old video if exists
                $oldVideo = $db->query("SELECT video_path FROM homepage_hero LIMIT 1")->fetchColumn();
                if ($oldVideo && file_exists($uploadDir . $oldVideo)) {
                    unlink($uploadDir . $oldVideo);
                }
            }
        }
        
        // Update database
        if ($videoPath) {
            $stmt = $db->prepare("UPDATE homepage_hero SET title = ?, subtitle = ?, video_path = ? WHERE id = 1");
            $result = $stmt->execute([$title, $subtitle, $videoPath]);
        } else {
            $stmt = $db->prepare("UPDATE homepage_hero SET title = ?, subtitle = ? WHERE id = 1");
            $result = $stmt->execute([$title, $subtitle]);
        }
        
        if ($result) {
            $message = "Hero section updated successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to update hero section.";
            $messageType = "danger";
        }
    }
    
    // 2. TESTIMONIALS MANAGEMENT
    elseif ($action === 'add_testimonial') {
        $name = $_POST['name'] ?? '';
        $title = $_POST['person_title'] ?? '';
        $content = $_POST['content'] ?? '';
        $displayOrder = $_POST['display_order'] ?? 0;
        
        // Handle photo upload
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $uploadDir = '../../../assets/img/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'testimonial_' . time() . '_' . basename($_FILES['photo']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
                    $photoPath = $fileName;
                }
            }
        }
        
        $stmt = $db->prepare("INSERT INTO testimonials (name, title, photo_path, content, display_order) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $title, $photoPath, $content, $displayOrder])) {
            $message = "Testimonial added successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to add testimonial.";
            $messageType = "danger";
        }
    }
    elseif ($action === 'update_testimonial') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $title = $_POST['person_title'] ?? '';
        $content = $_POST['content'] ?? '';
        $displayOrder = $_POST['display_order'] ?? 0;
        
        // Get current testimonial data
        $current = $db->prepare("SELECT photo_path FROM testimonials WHERE id = ?");
        $current->execute([$id]);
        $testimonial = $current->fetch(PDO::FETCH_ASSOC);
        
        // Handle photo upload
        $photoPath = $testimonial['photo_path'] ?? null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $uploadDir = '../../../assets/img/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'testimonial_' . time() . '_' . basename($_FILES['photo']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
                    // Delete old photo if exists
                    if ($photoPath && file_exists($uploadDir . $photoPath)) {
                        unlink($uploadDir . $photoPath);
                    }
                    $photoPath = $fileName;
                }
            }
        }
        
        $stmt = $db->prepare("UPDATE testimonials SET name = ?, title = ?, content = ?, display_order = ?, photo_path = ? WHERE id = ?");
        if ($stmt->execute([$name, $title, $content, $displayOrder, $photoPath, $id])) {
            $message = "Testimonial updated successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to update testimonial.";
            $messageType = "danger";
        }
    }
    elseif ($action === 'delete_testimonial') {
        $id = $_POST['id'] ?? 0;
        
        // Get testimonial data first to delete associated image
        $stmt = $db->prepare("SELECT photo_path FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        $testimonial = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($testimonial) {
            // Delete the image file if exists
            if (!empty($testimonial['photo_path'])) {
                $filePath = '../../../assets/img/' . $testimonial['photo_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Delete from database
            $deleteStmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
            if ($deleteStmt->execute([$id])) {
                $message = "Testimonial deleted successfully.";
                $messageType = "success";
            } else {
                $message = "Failed to delete testimonial.";
                $messageType = "danger";
            }
        } else {
            $message = "Testimonial not found.";
            $messageType = "warning";
        }
    }
    
    // 3. ACCOMMODATIONS MANAGEMENT
    elseif ($action === 'update_accommodation') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $buttonText = $_POST['button_text'] ?? 'Discover More';
        $buttonLink = $_POST['button_link'] ?? '#';
        
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploadDir = '../../../assets/image/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'accommodation_' . time() . '_' . basename($_FILES['image']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $imagePath = $fileName;
                    
                    // Delete old image if exists
                    $oldImage = $db->query("SELECT image_path FROM accommodations LIMIT 1")->fetchColumn();
                    if ($oldImage && file_exists($uploadDir . $oldImage)) {
                        unlink($uploadDir . $oldImage);
                    }
                }
            }
        }
        
        // Check if accommodation exists
        $exists = $db->query("SELECT COUNT(*) FROM accommodations")->fetchColumn();
        
        if ($exists) {
            if ($imagePath) {
                $stmt = $db->prepare("UPDATE accommodations SET title = ?, description = ?, image_path = ?, button_text = ?, button_link = ? WHERE id = 1");
                $result = $stmt->execute([$title, $description, $imagePath, $buttonText, $buttonLink]);
            } else {
                $stmt = $db->prepare("UPDATE accommodations SET title = ?, description = ?, button_text = ?, button_link = ? WHERE id = 1");
                $result = $stmt->execute([$title, $description, $buttonText, $buttonLink]);
            }
        } else {
            $stmt = $db->prepare("INSERT INTO accommodations (title, description, image_path, button_text, button_link) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([$title, $description, $imagePath, $buttonText, $buttonLink]);
        }
        
        if ($result) {
            $message = "Accommodation section updated successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to update accommodation section.";
            $messageType = "danger";
        }
    }
    
    // 4. LOCATIONS MANAGEMENT
    elseif ($action === 'update_location') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $title = $_POST['title'] ?? '';
        $tagline = $_POST['tagline'] ?? '';
        $description = $_POST['description'] ?? '';
        $fullDescription = $_POST['full_description'] ?? '';
        $inspiredUs = $_POST['inspired_us'] ?? '';
        $loveAbout = $_POST['love_about'] ?? '';
        $link = $_POST['link'] ?? '#';
        $displayOrder = $_POST['display_order'] ?? 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle main image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploadDir = '../../../assets/image/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'location_' . time() . '_' . basename($_FILES['image']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    // Get current image to delete it
                    $current = $db->prepare("SELECT image_path FROM locations WHERE id = ?");
                    $current->execute([$id]);
                    $location = $current->fetch(PDO::FETCH_ASSOC);
                    
                    if ($location && !empty($location['image_path']) && file_exists($uploadDir . $location['image_path'])) {
                        unlink($uploadDir . $location['image_path']);
                    }
                    
                    $imagePath = $fileName;
                }
            }
        }
        
        // Handle second image upload
        $imagePath2 = null;
        if (isset($_FILES['image2']) && $_FILES['image2']['error'] == 0) {
            $uploadDir = '../../../assets/image/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'location2_' . time() . '_' . basename($_FILES['image2']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES['image2']['tmp_name'], $targetFilePath)) {
                    // Get current image to delete it
                    $current = $db->prepare("SELECT image_path2 FROM locations WHERE id = ?");
                    $current->execute([$id]);
                    $location = $current->fetch(PDO::FETCH_ASSOC);
                    
                    if ($location && !empty($location['image_path2']) && file_exists($uploadDir . $location['image_path2'])) {
                        unlink($uploadDir . $location['image_path2']);
                    }
                    
                    $imagePath2 = $fileName;
                }
            }
        }
        
        // Handle third image upload
        $imagePath3 = null;
        if (isset($_FILES['image3']) && $_FILES['image3']['error'] == 0) {
            $uploadDir = '../../../assets/image/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'location3_' . time() . '_' . basename($_FILES['image3']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES['image3']['tmp_name'], $targetFilePath)) {
                    // Get current image to delete it
                    $current = $db->prepare("SELECT image_path3 FROM locations WHERE id = ?");
                    $current->execute([$id]);
                    $location = $current->fetch(PDO::FETCH_ASSOC);
                    
                    if ($location && !empty($location['image_path3']) && file_exists($uploadDir . $location['image_path3'])) {
                        unlink($uploadDir . $location['image_path3']);
                    }
                    
                    $imagePath3 = $fileName;
                }
            }
        }
        
        // Get current values for fields we're not updating
        $current = $db->prepare("SELECT image_path, image_path2, image_path3 FROM locations WHERE id = ?");
        $current->execute([$id]);
        $currentData = $current->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $db->prepare("UPDATE locations SET 
            name = ?, 
            title = ?,
            tagline = ?,
            description = ?,
            full_description = ?,
            inspired_us = ?,
            love_about = ?,
            link = ?,
            display_order = ?,
            is_active = ?,
            image_path = ?,
            image_path2 = ?,
            image_path3 = ?
            WHERE id = ?");
        
        $result = $stmt->execute([
            $name, 
            $title,
            $tagline,
            $description,
            $fullDescription,
            $inspiredUs,
            $loveAbout,
            $link,
            $displayOrder,
            $isActive,
            $imagePath ?? $currentData['image_path'],
            $imagePath2 ?? $currentData['image_path2'],
            $imagePath3 ?? $currentData['image_path3'],
            $id
        ]);
        
        if ($result) {
            $message = "Location updated successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to update location.";
            $messageType = "danger";
        }
    }
    
    // 5. PREMIUM FEATURES MANAGEMENT
    elseif ($action === 'update_premium_features') {
        $title = $_POST['title'] ?? '';
        $link = $_POST['link'] ?? '#';
        
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploadDir = '../../../assets/image/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'premium_' . time() . '_' . basename($_FILES['image']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $imagePath = $fileName;
                    
                    // Delete old image if exists
                    $oldImage = $db->query("SELECT image_path FROM premium_features LIMIT 1")->fetchColumn();
                    if ($oldImage && file_exists($uploadDir . $oldImage)) {
                        unlink($uploadDir . $oldImage);
                    }
                }
            }
        }
        
        // Check if record exists
        $exists = $db->query("SELECT COUNT(*) FROM premium_features")->fetchColumn();
        
        if ($exists) {
            if ($imagePath) {
                $stmt = $db->prepare("UPDATE premium_features SET title = ?, link = ?, image_path = ? WHERE id = 1");
                $result = $stmt->execute([$title, $link, $imagePath]);
            } else {
                $stmt = $db->prepare("UPDATE premium_features SET title = ?, link = ? WHERE id = 1");
                $result = $stmt->execute([$title, $link]);
            }
        } else {
            $stmt = $db->prepare("INSERT INTO premium_features (title, link, image_path) VALUES (?, ?, ?)");
            $result = $stmt->execute([$title, $link, $imagePath]);
        }
        
        if ($result) {
            $message = "Premium features section updated successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to update premium features section.";
            $messageType = "danger";
        }
    }
    
    // 6. ABOUT US SECTION MANAGEMENT
    elseif ($action === 'update_about_us') {
        $mainTitle = $_POST['main_title'] ?? '';
        $description = $_POST['description'] ?? '';
        $statsText1 = $_POST['stats_text_1'] ?? '';
        $statsValue1 = $_POST['stats_value_1'] ?? '';
        $statsText2 = $_POST['stats_text_2'] ?? '';
        $statsValue2 = $_POST['stats_value_2'] ?? '';
        $statsText3 = $_POST['stats_text_3'] ?? '';
        $statsValue3 = $_POST['stats_value_3'] ?? '';
        $buttonText = $_POST['button_text'] ?? '';
        $buttonLink = $_POST['button_link'] ?? '';
        
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] == 0) {
            $uploadDir = '../../../assets/image/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['about_image']['name'], PATHINFO_EXTENSION));
            $fileName = 'about_' . time() . '_' . uniqid() . '.' . $fileExt;
            $targetFilePath = $uploadDir . $fileName;
            
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileExt, $allowTypes)) {
                if (move_uploaded_file($_FILES['about_image']['tmp_name'], $targetFilePath)) {
                    // Delete old image if exists
                    $oldImage = $db->query("SELECT image_path FROM about_us LIMIT 1")->fetchColumn();
                    if ($oldImage && file_exists($uploadDir . $oldImage)) {
                        unlink($uploadDir . $oldImage);
                    }
                    $imagePath = $fileName;
                }
            }
        }
        
        // Handle right image upload
        $imagePath_right = null;
        if (isset($_FILES['about_image_right']) && $_FILES['about_image_right']['error'] == 0) {
            $uploadDir = '../../../assets/image/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['about_image_right']['name'], PATHINFO_EXTENSION));
            $fileName = 'about_right_' . time() . '_' . uniqid() . '.' . $fileExt;
            $targetFilePath = $uploadDir . $fileName;
            
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileExt, $allowTypes)) {
                if (move_uploaded_file($_FILES['about_image_right']['tmp_name'], $targetFilePath)) {
                    // Delete old image if exists
                    $oldImage = $db->query("SELECT image_path_right FROM about_us LIMIT 1")->fetchColumn();
                    if ($oldImage && file_exists($uploadDir . $oldImage)) {
                        unlink($uploadDir . $oldImage);
                    }
                    $imagePath_right = $fileName;
                }
            }
        }
        
        // Handle video upload
        $videoPath = null;
        if (isset($_FILES['about_video']) && $_FILES['about_video']['error'] == 0) {
            $uploadDir = '../../../assets/video/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'about_' . time() . '_' . basename($_FILES['about_video']['name']);
            $targetFilePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['about_video']['tmp_name'], $targetFilePath)) {
                // Delete old video if exists
                $oldVideo = $db->query("SELECT video_path FROM about_us LIMIT 1")->fetchColumn();
                if ($oldVideo && file_exists($uploadDir . $oldVideo)) {
                    unlink($uploadDir . $oldVideo);
                }
                $videoPath = $fileName;
            }
        }
        
        // Check if record exists
        $exists = $db->query("SELECT COUNT(*) FROM about_us")->fetchColumn();
        
        if ($exists) {
            // Get current values for fields we're not updating
            $current = $db->query("SELECT image_path, image_path_right, video_path FROM about_us LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("UPDATE about_us SET 
                main_title = ?, 
                description = ?,
                stats_text_1 = ?, stats_value_1 = ?,
                stats_text_2 = ?, stats_value_2 = ?,
                stats_text_3 = ?, stats_value_3 = ?,
                button_text = ?, button_link = ?,
                image_path = ?,
                image_path_right = ?,
                video_path = ?
                WHERE id = 1");
            
            $result = $stmt->execute([
                $mainTitle, $description,
                $statsText1, $statsValue1,
                $statsText2, $statsValue2,
                $statsText3, $statsValue3,
                $buttonText, $buttonLink,
                $imagePath ?? $current['image_path'],
                $imagePath_right ?? $current['image_path_right'],
                $videoPath ?? $current['video_path']
            ]);
        } else {
            $stmt = $db->prepare("INSERT INTO about_us (
                main_title, description,
                stats_text_1, stats_value_1,
                stats_text_2, stats_value_2,
                stats_text_3, stats_value_3,
                button_text, button_link,
                image_path, image_path_right, video_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $result = $stmt->execute([
                $mainTitle, $description,
                $statsText1, $statsValue1,
                $statsText2, $statsValue2,
                $statsText3, $statsValue3,
                $buttonText, $buttonLink,
                $imagePath ?? 'default.jpg',
                $imagePath_right ?? 'second.jpg',
                $videoPath
            ]);
        }
        
        if ($result) {
            $message = "About Us section updated successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to update About Us section.";
            $messageType = "danger";
        }
    }
}

// Fetch all current data
$hero = $db->query("SELECT * FROM homepage_hero LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
$testimonials = $db->query("SELECT * FROM testimonials ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
$accommodation = $db->query("SELECT * FROM accommodations LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
$locations = $db->query("SELECT * FROM locations ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
$aboutUs = $db->query("SELECT * FROM about_us LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
$premiumFeatures = $db->query("SELECT * FROM premium_features LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
?>

<!DOCTYPE html>
<html lang="en">
<?php include('../../../layouts/admin_header.php'); ?>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include('../../../layouts/admin_sidebar.php'); ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php include('../../../layouts/admin_navbar.php'); ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Homepage Content Management</h1>
                    </div>

                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Hero Section Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Hero Section</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_hero">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($hero['title'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Subtitle</label>
                                    <textarea name="subtitle" class="form-control" rows="3" required><?php echo htmlspecialchars($hero['subtitle'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Hero Video</label>
                                    <?php if (!empty($hero['video_path'])): ?>
                                    <p>Current video: <?php echo htmlspecialchars($hero['video_path']); ?></p>
                                    <?php endif; ?>
                                    <input type="file" name="hero_video" class="form-control-file">
                                    <small class="form-text text-muted">Leave empty to keep current video</small>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Hero Section</button>
                            </form>
                        </div>
                    </div>

                    <!-- About Us Section Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">About Us Section</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_about_us">
                                <div class="form-group">
                                    <label>Main Title (HTML allowed for span tags)</label>
                                    <input type="text" name="main_title" class="form-control" value="<?php echo htmlspecialchars($aboutUs['main_title'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($aboutUs['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Stat 1 Text</label>
                                            <input type="text" name="stats_text_1" class="form-control" value="<?php echo htmlspecialchars($aboutUs['stats_text_1'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Stat 1 Value</label>
                                            <input type="text" name="stats_value_1" class="form-control" value="<?php echo htmlspecialchars($aboutUs['stats_value_1'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Stat 2 Text</label>
                                            <input type="text" name="stats_text_2" class="form-control" value="<?php echo htmlspecialchars($aboutUs['stats_text_2'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Stat 2 Value</label>
                                            <input type="text" name="stats_value_2" class="form-control" value="<?php echo htmlspecialchars($aboutUs['stats_value_2'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Stat 3 Text</label>
                                            <input type="text" name="stats_text_3" class="form-control" value="<?php echo htmlspecialchars($aboutUs['stats_text_3'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Stat 3 Value</label>
                                            <input type="text" name="stats_value_3" class="form-control" value="<?php echo htmlspecialchars($aboutUs['stats_value_3'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Button Text</label>
                                    <input type="text" name="button_text" class="form-control" value="<?php echo htmlspecialchars($aboutUs['button_text'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Button Link</label>
                                    <input type="text" name="button_link" class="form-control" value="<?php echo htmlspecialchars($aboutUs['button_link'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>About Image</label>
                                    <?php if (!empty($aboutUs['image_path'])): ?>
                                    <p>Current image: <?php echo htmlspecialchars($aboutUs['image_path']); ?></p>
                                    <img src="../../../assets/image/<?php echo htmlspecialchars($aboutUs['image_path']); ?>" width="200" class="mb-2">
                                    <?php endif; ?>
                                    <input type="file" name="about_image" class="form-control-file">
                                </div>
                                
                                <div class="form-group">
                                    <label>Right Side Photo</label>
                                    <?php if (!empty($aboutUs['image_path_right'])): ?>
                                    <p>Current image: <?php echo htmlspecialchars($aboutUs['image_path_right']); ?></p>
                                    <img src="../../../assets/image/<?php echo htmlspecialchars($aboutUs['image_path_right']); ?>" width="200" class="mb-2">
                                    <?php endif; ?>
                                    <input type="file" name="about_image_right" class="form-control-file">
                                </div>
                                
                                <div class="form-group">
                                    <label>About Video</label>
                                    <?php if (!empty($aboutUs['video_path'])): ?>
                                    <p>Current video: <?php echo htmlspecialchars($aboutUs['video_path']); ?></p>
                                    <?php endif; ?>
                                    <input type="file" name="about_video" class="form-control-file">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update About Us Section</button>
                            </form>
                        </div>
                    </div>

                    <!-- Accommodation Section Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Accommodation Section</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_accommodation">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($accommodation['title'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($accommodation['description'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Button Text</label>
                                    <input type="text" name="button_text" class="form-control" value="<?php echo htmlspecialchars($accommodation['button_text'] ?? 'Discover More'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Button Link</label>
                                    <input type="text" name="button_link" class="form-control" value="<?php echo htmlspecialchars($accommodation['button_link'] ?? '#'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Accommodation Image</label>
                                    <?php if (!empty($accommodation['image_path'])): ?>
                                    <p>Current image: <?php echo htmlspecialchars($accommodation['image_path']); ?></p>
                                    <img src="../../../assets/image/<?php echo htmlspecialchars($accommodation['image_path']); ?>" width="200" class="mb-2">
                                    <?php endif; ?>
                                    <input type="file" name="image" class="form-control-file">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Accommodation Section</button>
                            </form>
                        </div>
                    </div>

                    <!-- Locations Section Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Locations</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Title</th>
                                            <th>Tagline</th>
                                            <th>Image</th>
                                            <th>Order</th>
                                            <th>Active</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($locations as $location): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($location['name']); ?></td>
                                            <td><?php echo htmlspecialchars($location['title']); ?></td>
                                            <td><?php echo htmlspecialchars($location['tagline']); ?></td>
                                            <td>
                                                <?php if (!empty($location['image_path'])): ?>
                                                <img src="../../../assets/image/<?php echo htmlspecialchars($location['image_path']); ?>" width="100">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($location['display_order']); ?></td>
                                            <td><?php echo $location['is_active'] ? 'Yes' : 'No'; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info edit-location" 
                                                        data-id="<?php echo $location['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($location['name']); ?>"
                                                        data-title="<?php echo htmlspecialchars($location['title']); ?>"
                                                        data-tagline="<?php echo htmlspecialchars($location['tagline']); ?>"
                                                        data-description="<?php echo htmlspecialchars($location['description']); ?>"
                                                        data-full_description="<?php echo htmlspecialchars($location['full_description']); ?>"
                                                        data-inspired_us="<?php echo htmlspecialchars($location['inspired_us']); ?>"
                                                        data-love_about="<?php echo htmlspecialchars($location['love_about']); ?>"
                                                        data-link="<?php echo htmlspecialchars($location['link']); ?>"
                                                        data-order="<?php echo $location['display_order']; ?>"
                                                        data-is_active="<?php echo $location['is_active']; ?>"
                                                        data-image_path="<?php echo !empty($location['image_path']) ? '../../../assets/image/'.htmlspecialchars($location['image_path']) : ''; ?>"
                                                        data-image_path2="<?php echo !empty($location['image_path2']) ? '../../../assets/image/'.htmlspecialchars($location['image_path2']) : ''; ?>"
                                                        data-image_path3="<?php echo !empty($location['image_path3']) ? '../../../assets/image/'.htmlspecialchars($location['image_path3']) : ''; ?>">
                                                    Edit
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Premium Features Section Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Premium Features Section</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_premium_features">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($premiumFeatures['title'] ?? 'Elevate Your stay with Premium Features and Services'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" name="link" class="form-control" value="<?php echo htmlspecialchars($premiumFeatures['link'] ?? '#'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Background Image</label>
                                    <?php if (!empty($premiumFeatures['image_path'])): ?>
                                    <p>Current image: <?php echo htmlspecialchars($premiumFeatures['image_path']); ?></p>
                                    <img src="../../../assets/image/<?php echo htmlspecialchars($premiumFeatures['image_path']); ?>" width="200">
                                    <?php endif; ?>
                                    <input type="file" name="image" class="form-control-file">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Premium Features Section</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Testimonials Section Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Testimonials</h6>
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addTestimonialModal">
                                Add New
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Name</th>
                                            <th>Title</th>
                                            <th>Content</th>
                                            <th>Order</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($testimonials as $testimonial): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($testimonial['photo_path'])): ?>
                                                <img src="../../../assets/img/<?php echo htmlspecialchars($testimonial['photo_path']); ?>" width="50">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($testimonial['name']); ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['title']); ?></td>
                                            <td><?php echo substr(htmlspecialchars($testimonial['content']), 0, 100); ?>...</td>
                                            <td><?php echo htmlspecialchars($testimonial['display_order']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info edit-testimonial" 
                                                        data-id="<?php echo $testimonial['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($testimonial['name']); ?>"
                                                        data-title="<?php echo htmlspecialchars($testimonial['title']); ?>"
                                                        data-content="<?php echo htmlspecialchars($testimonial['content']); ?>"
                                                        data-order="<?php echo $testimonial['display_order']; ?>">
                                                    Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-testimonial" 
                                                        data-id="<?php echo $testimonial['id']; ?>">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; EVERRETREAT <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="../../Authentication/views/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Testimonial Modal -->
    <div class="modal fade" id="addTestimonialModal" tabindex="-1" role="dialog" aria-labelledby="addTestimonialModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_testimonial">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTestimonialModalLabel">Add New Testimonial</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Title (e.g., Customer, Partner)</label>
                            <input type="text" name="person_title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Photo</label>
                            <input type="file" name="photo" class="form-control-file">
                        </div>
                        <div class="form-group">
                            <label>Testimonial Content</label>
                            <textarea name="content" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Testimonial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Testimonial Modal -->
    <div class="modal fade" id="editTestimonialModal" tabindex="-1" role="dialog" aria-labelledby="editTestimonialModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_testimonial">
                    <input type="hidden" name="id" id="edit_testimonial_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTestimonialModalLabel">Edit Testimonial</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" id="edit_testimonial_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="person_title" id="edit_testimonial_title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Current Photo</label>
                            <div id="current_testimonial_photo"></div>
                            <label>Change Photo (Optional)</label>
                            <input type="file" name="photo" class="form-control-file">
                        </div>
                        <div class="form-group">
                            <label>Testimonial Content</label>
                            <textarea name="content" id="edit_testimonial_content" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Display Order</label>
                            <input type="number" name="display_order" id="edit_testimonial_order" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Testimonial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Testimonial Modal -->
    <div class="modal fade" id="deleteTestimonialModal" tabindex="-1" role="dialog" aria-labelledby="deleteTestimonialModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="action" value="delete_testimonial">
                    <input type="hidden" name="id" id="delete_testimonial_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteTestimonialModalLabel">Delete Testimonial</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this testimonial?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Location Modal -->
    <div class="modal fade" id="editLocationModal" tabindex="-1" role="dialog" aria-labelledby="editLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_location">
                    <input type="hidden" name="id" id="edit_location_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editLocationModalLabel">Edit Location</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" id="edit_location_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" id="edit_location_title" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Tagline</label>
                                    <input type="text" name="tagline" id="edit_location_tagline" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" id="edit_location_description" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Full Description</label>
                                    <textarea name="full_description" id="edit_location_full_description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>What Inspired Us</label>
                                    <textarea name="inspired_us" id="edit_location_inspired_us" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>What We Love About</label>
                                    <textarea name="love_about" id="edit_location_love_about" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" name="link" id="edit_location_link" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Display Order</label>
                                    <input type="number" name="display_order" id="edit_location_order" class="form-control" min="0">
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" name="is_active" id="edit_location_is_active" class="form-check-input" value="1">
                                    <label class="form-check-label" for="edit_location_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Main Image</label>
                                    <div id="current_location_image"></div>
                                    <input type="file" name="image" class="form-control-file">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Second Image</label>
                                    <div id="current_location_image2"></div>
                                    <input type="file" name="image2" class="form-control-file">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Third Image</label>
                                    <div id="current_location_image3"></div>
                                    <input type="file" name="image3" class="form-control-file">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Location</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Dashboard Scripts -->
    <?php include('../../../layouts/admin_scripts.php'); ?>

    <script>
        $(document).ready(function() {
            // Testimonial Edit Button
            $('.edit-testimonial').click(function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var title = $(this).data('title');
                var content = $(this).data('content');
                var order = $(this).data('order');
                var photoPath = $(this).closest('tr').find('img').attr('src');
                
                $('#edit_testimonial_id').val(id);
                $('#edit_testimonial_name').val(name);
                $('#edit_testimonial_title').val(title);
                $('#edit_testimonial_content').val(content);
                $('#edit_testimonial_order').val(order);
                
                // Display current photo if exists
                $('#current_testimonial_photo').html('');
                if (photoPath) {
                    $('#current_testimonial_photo').html('<img src="' + photoPath + '" width="100">');
                }
                
                $('#editTestimonialModal').modal('show');
            });
            
            // Testimonial Delete Button
            $('.delete-testimonial').click(function() {
                var id = $(this).data('id');
                $('#delete_testimonial_id').val(id);
                $('#deleteTestimonialModal').modal('show');
            });
            
            // Location Edit Button
            $('.edit-location').click(function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var title = $(this).data('title');
                var tagline = $(this).data('tagline');
                var description = $(this).data('description');
                var fullDescription = $(this).data('full_description');
                var inspiredUs = $(this).data('inspired_us');
                var loveAbout = $(this).data('love_about');
                var link = $(this).data('link');
                var order = $(this).data('order');
                var isActive = $(this).data('is_active');
                var imagePath = $(this).data('image_path');
                var imagePath2 = $(this).data('image_path2');
                var imagePath3 = $(this).data('image_path3');
                
                $('#edit_location_id').val(id);
                $('#edit_location_name').val(name);
                $('#edit_location_title').val(title);
                $('#edit_location_tagline').val(tagline);
                $('#edit_location_description').val(description);
                $('#edit_location_full_description').val(fullDescription);
                $('#edit_location_inspired_us').val(inspiredUs);
                $('#edit_location_love_about').val(loveAbout);
                $('#edit_location_link').val(link);
                $('#edit_location_order').val(order);
                $('#edit_location_is_active').prop('checked', isActive == 1);
                
                // Display current images if they exist
                $('#current_location_image, #current_location_image2, #current_location_image3').html('');
                
                if (imagePath) {
                    $('#current_location_image').html('<img src="' + imagePath + '" width="100" class="mb-2">');
                }
                if (imagePath2) {
                    $('#current_location_image2').html('<img src="' + imagePath2 + '" width="100" class="mb-2">');
                }
                if (imagePath3) {
                    $('#current_location_image3').html('<img src="' + imagePath3 + '" width="100" class="mb-2">');
                }
                
                $('#editLocationModal').modal('show');
            });
        });
    </script>
</body>
</html>