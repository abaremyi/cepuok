<?php
require_once '../../../helpers/JWTHandler.php';
require_once '../../../config/database.php';

// Start the session or read the cookie
if (!isset($_COOKIE['jwtToken'])) {
    header('Location: ../../Authentication/views/login.php');
    exit;
}

// Retrieve the token from the cookie
$jwtToken = $_COOKIE['jwtToken'];

// Initialize JWT handler
$jwtHandler = new JWTHandler();

// Validate the token
$decodedToken = $jwtHandler->validateToken($jwtToken);

if ($decodedToken === false) {
    header('Location: ../../Authentication/views/login.php');
    exit;
}

// Process form submission for image upload
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $db = Database::getInstance();
    
    // Handle image upload
    if ($_POST['action'] === 'upload' && isset($_FILES['image'])) {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $displayOrder = $_POST['display_order'] ?? 0;
        
        $uploadDir = '../../../uploads/gallery/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        // Allow certain file formats
        $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array(strtolower($fileType), $allowTypes)) {
            // Upload file to server
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                // Insert image data into the database
                $stmt = $db->prepare("INSERT INTO gallery (title, description, image_path, category, is_featured, display_order) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
                $imagePath = $fileName;
                
                if ($stmt->execute([$title, $description, $imagePath, $category, $isFeatured, $displayOrder])) {
                    $message = "Image uploaded successfully.";
                    $messageType = "success";
                } else {
                    $message = "Failed to save image information in the database.";
                    $messageType = "danger";
                }
            } else {
                $message = "Failed to upload image.";
                $messageType = "danger";
            }
        } else {
            $message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            $messageType = "warning";
        }
    }
    
    // Handle delete image
    if ($_POST['action'] === 'delete' && isset($_POST['image_id'])) {
        $imageId = $_POST['image_id'];
        
        // Get image path before deleting the record
        $stmt = $db->prepare("SELECT image_path FROM gallery WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($image) {
            // Delete the database record
            $deleteStmt = $db->prepare("DELETE FROM gallery WHERE id = ?");
            
            if ($deleteStmt->execute([$imageId])) {
                // Delete the file from the server
                $filePath = '../../../uploads/gallery/' . $image['image_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                $message = "Image deleted successfully.";
                $messageType = "success";
            } else {
                $message = "Failed to delete image.";
                $messageType = "danger";
            }
        } else {
            $message = "Image not found.";
            $messageType = "warning";
        }
    }
    
    // Handle update image details
    if ($_POST['action'] === 'update' && isset($_POST['image_id'])) {
        $imageId = $_POST['image_id'];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $displayOrder = $_POST['display_order'] ?? 0;
        
        // Check if there's a new image to upload
        $updateImage = false;
        $newImagePath = null;
        
        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] == 0) {
            $uploadDir = '../../../uploads/gallery/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['new_image']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            
            // Allow certain file formats
            $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
            
            if (in_array(strtolower($fileType), $allowTypes)) {
                // Get the old image path before updating
                $stmtGetOld = $db->prepare("SELECT image_path FROM gallery WHERE id = ?");
                $stmtGetOld->execute([$imageId]);
                $oldImage = $stmtGetOld->fetch(PDO::FETCH_ASSOC);
                $oldImagePath = $oldImage ? $oldImage['image_path'] : null;
                
                // Upload new file to server
                if (move_uploaded_file($_FILES['new_image']['tmp_name'], $targetFilePath)) {
                    $newImagePath = $fileName;
                    $updateImage = true;
                    
                    // Delete the old file if it exists
                    if ($oldImagePath) {
                        $oldFilePath = $uploadDir . $oldImagePath;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                } else {
                    $message = "Failed to upload new image.";
                    $messageType = "danger";
                }
            } else {
                $message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
                $messageType = "warning";
            }
        }
        
        // Prepare SQL based on whether we're updating the image or not
        if ($updateImage) {
            $stmt = $db->prepare("UPDATE gallery SET 
                                  title = ?, 
                                  description = ?, 
                                  category = ?, 
                                  is_featured = ?, 
                                  display_order = ?,
                                  image_path = ? 
                                WHERE id = ?");
            $result = $stmt->execute([$title, $description, $category, $isFeatured, $displayOrder, $newImagePath, $imageId]);
        } else {
            $stmt = $db->prepare("UPDATE gallery SET 
                                  title = ?, 
                                  description = ?, 
                                  category = ?, 
                                  is_featured = ?, 
                                  display_order = ? 
                                WHERE id = ?");
            $result = $stmt->execute([$title, $description, $category, $isFeatured, $displayOrder, $imageId]);
        }
        
        if ($result) {
            $message = "Image details updated successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to update image details.";
            $messageType = "danger";
        }
    }
}

// Fetch all images from the database
$db = Database::getInstance();
$stmt = $db->query("SELECT * FROM gallery ORDER BY display_order ASC, created_at DESC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch distinct categories for the filter dropdown
$categoryStmt = $db->query("SELECT DISTINCT category FROM gallery WHERE category != '' ORDER BY category");
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<?php include('../../../layouts/admin_header.php'); ?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include('../../../layouts/admin_sidebar.php');?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include('../../../layouts/admin_navbar.php');?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Gallery Management</h1>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" 
                                data-toggle="modal" data-target="#uploadModal">
                            <i class="fas fa-upload fa-sm text-white-50"></i> Upload New Image
                        </button>
                    </div>

                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Content Row -->
                    <div class="row">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Gallery Images</h6>
                                    <div class="dropdown no-arrow">
                                        <select id="categoryFilter" class="form-control form-control-sm">
                                            <option value="">All Categories</option>
                                            <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category); ?>">
                                                <?php echo htmlspecialchars($category); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="row" id="galleryContainer">
                                        <?php if (count($images) > 0): ?>
                                            <?php foreach ($images as $image): ?>
                                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4 gallery-item" data-category="<?php echo htmlspecialchars($image['category']); ?>">
                                                <div class="card h-100">
                                                    <div class="position-relative">
                                                        <img src="<?php echo '../../../uploads/gallery/' . htmlspecialchars($image['image_path']); ?>" 
                                                             class="card-img-top" alt="<?php echo htmlspecialchars($image['title']); ?>"
                                                             style="height: 200px; object-fit: cover;">
                                                        <?php if ($image['is_featured']): ?>
                                                        <span class="badge badge-primary position-absolute" style="top: 10px; right: 10px;">
                                                            Featured
                                                        </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?php echo htmlspecialchars($image['title']); ?></h5>
                                                        <p class="card-text small text-muted">
                                                            <strong>Category:</strong> <?php echo htmlspecialchars($image['category']); ?><br>
                                                            <strong>Order:</strong> <?php echo htmlspecialchars($image['display_order']); ?><br>
                                                            <strong>Added:</strong> <?php echo date('M d, Y', strtotime($image['created_at'])); ?>
                                                        </p>
                                                    </div>
                                                    <div class="card-footer bg-transparent border-top-0">
                                                        <div class="btn-group w-100">
                                                            <button type="button" class="btn btn-sm btn-info" 
                                                                    data-toggle="modal" data-target="#editModal" 
                                                                    data-id="<?php echo $image['id']; ?>"
                                                                    data-title="<?php echo htmlspecialchars($image['title']); ?>"
                                                                    data-description="<?php echo htmlspecialchars($image['description']); ?>"
                                                                    data-category="<?php echo htmlspecialchars($image['category']); ?>"
                                                                    data-featured="<?php echo $image['is_featured']; ?>"
                                                                    data-order="<?php echo $image['display_order']; ?>"
                                                                    data-img-path="<?php echo '../../../uploads/gallery/' . htmlspecialchars($image['image_path']); ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" 
                                                                    data-toggle="modal" data-target="#deleteModal" 
                                                                    data-id="<?php echo $image['id']; ?>"
                                                                    data-title="<?php echo htmlspecialchars($image['title']); ?>">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-12">
                                                <div class="alert alert-info" role="alert">
                                                    No images found. Click the "Upload New Image" button to add images to the gallery.
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
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
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
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

    <!-- Upload Image Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">Upload New Image</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="image">Select Image</label>
                            <input type="file" class="form-control-file" id="image" name="image" required>
                            <small class="form-text text-muted">Accepted formats: JPG, JPEG, PNG, GIF. Max size: 5MB.</small>
                        </div>
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" class="form-control" id="category" name="category" list="existingCategories">
                            <datalist id="existingCategories">
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <small class="form-text text-muted">Examples: Rooms, Pool, Restaurant, Exterior, etc.</small>
                        </div>
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" min="0" value="0">
                            <small class="form-text text-muted">Lower numbers appear first. Images with the same order are sorted by date.</small>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured">
                            <label class="form-check-label" for="is_featured">Featured Image</label>
                            <small class="form-text text-muted">Featured images may appear in special locations on the website.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Image Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="image_id" id="edit_image_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Image Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="current_image">Current Image</label>
                            <div class="text-center">
                                <img id="current_image_preview" src="" alt="Current image" 
                                     class="img-fluid mb-2" style="max-height: 200px; max-width: 100%;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_image">Replace Image (Optional)</label>
                            <input type="file" class="form-control-file" id="new_image" name="new_image">
                            <small class="form-text text-muted">Leave empty to keep current image. Accepted formats: JPG, JPEG, PNG, GIF.</small>
                        </div>
                        <div class="form-group">
                            <label for="edit_title">Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_category">Category</label>
                            <input type="text" class="form-control" id="edit_category" name="category" list="existingCategories">
                        </div>
                        <div class="form-group">
                            <label for="edit_display_order">Display Order</label>
                            <input type="number" class="form-control" id="edit_display_order" name="display_order" min="0">
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_featured" name="is_featured">
                            <label class="form-check-label" for="edit_is_featured">Featured Image</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Image Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="image_id" id="delete_image_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the image "<span id="delete_image_title"></span>"?</p>
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

    <!-- Include Dashboard Scripts -->
    <?php include('../../../layouts/admin_scripts.php'); ?>

    <script>
        $(document).ready(function() {
            // Edit modal data
            $('#editModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var title = button.data('title');
                var description = button.data('description');
                var category = button.data('category');
                var featured = button.data('featured');
                var order = button.data('order');
                var imgPath = button.data('img-path');
                
                var modal = $(this);
                modal.find('#edit_image_id').val(id);
                modal.find('#edit_title').val(title);
                modal.find('#edit_description').val(description);
                modal.find('#edit_category').val(category);
                modal.find('#edit_display_order').val(order);
                modal.find('#current_image_preview').attr('src', imgPath);
                
                if (featured == 1) {
                    modal.find('#edit_is_featured').prop('checked', true);
                } else {
                    modal.find('#edit_is_featured').prop('checked', false);
                }
            });
            
            // Delete modal data
            $('#deleteModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var title = button.data('title');
                
                var modal = $(this);
                modal.find('#delete_image_id').val(id);
                modal.find('#delete_image_title').text(title);
            });
            
            // Category filter
            $('#categoryFilter').on('change', function() {
                var category = $(this).val();
                
                if (category === '') {
                    // Show all images
                    $('.gallery-item').show();
                } else {
                    // Hide all images first
                    $('.gallery-item').hide();
                    // Show only images with selected category
                    $('.gallery-item[data-category="' + category + '"]').show();
                }
            });

            // Preview for new image upload in edit modal
            $('#new_image').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#current_image_preview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>