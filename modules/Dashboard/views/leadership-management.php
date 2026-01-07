<?php
// modules/Dashboard/views/leadership-management.php
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . '/helpers/JWTHandler.php';

$token = $_COOKIE['auth_token'] ?? '';
$jwtHandler = new JWTHandler();
$decoded = $token ? $jwtHandler->validateToken($token) : null;

if (!$decoded) {
    header("Location: " . url('login'));
    exit;
}

if (!$decoded->is_super_admin && !in_array('leadership.view', $decoded->permissions)) {
    header("Location: " . url('admin'));
    exit;
}

require_once $root_path . "/config/database.php";
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_leader') {
        try {
            $stmt = $pdo->prepare("SELECT id FROM leadership_team WHERE email = ?");
            $stmt->execute([trim($_POST['email'])]);
            if ($stmt->fetch()) {
                throw new Exception('Email already exists.');
            }
            
            $stmt = $pdo->prepare("INSERT INTO leadership_team (full_name, position, role_badge, short_bio, qualifications, email, phone, facebook_url, twitter_url, linkedin_url, whatsapp_number, join_date, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                trim($_POST['full_name']), trim($_POST['position']), trim($_POST['role_badge']),
                trim($_POST['short_bio']), trim($_POST['qualifications']), trim($_POST['email']),
                trim($_POST['phone']), trim($_POST['facebook_url']), trim($_POST['twitter_url']),
                trim($_POST['linkedin_url']), trim($_POST['whatsapp_number']), $_POST['join_date'],
                $_POST['display_order'] ?? 0, $_POST['status']
            ]);
            
            session_start();
            $_SESSION['success_message'] = 'Leadership member added successfully!';
            header("Location: " . url('admin/leadership'));
            exit;
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
            header("Location: " . url('admin/leadership'));
            exit;
        }
    }
    
    if ($action === 'update_leader') {
        try {
            $stmt = $pdo->prepare("SELECT id FROM leadership_team WHERE email = ? AND id != ?");
            $stmt->execute([trim($_POST['email']), $_POST['id']]);
            if ($stmt->fetch()) {
                throw new Exception('Email already exists.');
            }
            
            $stmt = $pdo->prepare("UPDATE leadership_team SET full_name=?, position=?, role_badge=?, short_bio=?, qualifications=?, email=?, phone=?, facebook_url=?, twitter_url=?, linkedin_url=?, whatsapp_number=?, join_date=?, display_order=?, status=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([
                trim($_POST['full_name']), trim($_POST['position']), trim($_POST['role_badge']),
                trim($_POST['short_bio']), trim($_POST['qualifications']), trim($_POST['email']),
                trim($_POST['phone']), trim($_POST['facebook_url']), trim($_POST['twitter_url']),
                trim($_POST['linkedin_url']), trim($_POST['whatsapp_number']), $_POST['join_date'],
                $_POST['display_order'] ?? 0, $_POST['status'], $_POST['id']
            ]);
            
            session_start();
            $_SESSION['success_message'] = 'Leadership member updated successfully!';
            header("Location: " . url('admin/leadership'));
            exit;
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
            header("Location: " . url('admin/leadership'));
            exit;
        }
    }
    
    if ($action === 'delete_leader') {
        try {
            // First, get the image URL to delete the file
            $stmt = $pdo->prepare("SELECT image_url FROM leadership_team WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $leader = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete the image file if exists
            if ($leader && !empty($leader['image_url'])) {
                $image_path = $root_path . '/img/' . $leader['image_url'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM leadership_team WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            session_start();
            $_SESSION['success_message'] = 'Leadership member deleted successfully!';
            header("Location: " . url('admin/leadership'));
            exit;
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
            header("Location: " . url('admin/leadership'));
            exit;
        }
    }
    
    if ($action === 'upload_leader_image') {
        try {
            $upload_dir = $root_path . '/img/leadership/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            if (isset($_FILES['leader_image']) && $_FILES['leader_image']['error'] === UPLOAD_ERR_OK) {
                // First, get the old image to delete it
                $stmt = $pdo->prepare("SELECT image_url FROM leadership_team WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $old_image = $stmt->fetchColumn();
                
                // Delete old image if exists
                if ($old_image && file_exists($root_path . '/img/' . $old_image)) {
                    unlink($root_path . '/img/' . $old_image);
                }
                
                // Generate unique filename
                $file_extension = strtolower(pathinfo($_FILES['leader_image']['name'], PATHINFO_EXTENSION));
                $file_name = 'leader_' . $_POST['id'] . '_' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;
                
                // Validate file type
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($file_extension, $allowed_types)) {
                    throw new Exception('Only JPG, JPEG, PNG & GIF allowed.');
                }
                
                // Validate file size
                if ($_FILES['leader_image']['size'] > 5000000) {
                    throw new Exception('File too large. Max 5MB.');
                }
                
                // Validate image dimensions
                $image_info = getimagesize($_FILES['leader_image']['tmp_name']);
                if (!$image_info) {
                    throw new Exception('Invalid image file.');
                }
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['leader_image']['tmp_name'], $target_file)) {
                    $stmt = $pdo->prepare("UPDATE leadership_team SET image_url = ? WHERE id = ?");
                    $stmt->execute(['leadership/' . $file_name, $_POST['id']]);
                    
                    session_start();
                    $_SESSION['success_message'] = 'Image uploaded successfully!';
                } else {
                    throw new Exception('Failed to upload image.');
                }
            } else {
                throw new Exception('No image file uploaded.');
            }
            
            header("Location: " . url('admin/leadership'));
            exit;
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Failed: ' . $e->getMessage();
            header("Location: " . url('admin/leadership'));
            exit;
        }
    }
}

$stmt = $pdo->query("SELECT * FROM leadership_team ORDER BY display_order, full_name");
$leadership = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statsStmt = $pdo->query("SELECT COUNT(*) as total_leadership, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active_leaders, AVG(YEAR(CURDATE())-YEAR(join_date)) as avg_years_service FROM leadership_team");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

session_start();
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leadership Management</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php include_once 'components/admin-styles.php'; ?>
    <style>
        .leader-card{border:1px solid #e5e7eb;border-radius:10px;transition:all .3s;height:100%;display:flex;flex-direction:column}
        .leader-card:hover{box-shadow:0 5px 15px rgba(0,0,0,.1);transform:translateY(-5px)}
        .leader-image{width:100%;height:220px;object-fit:cover;border-radius:10px 10px 0 0}
        .leader-info{padding:15px;flex:1;display:flex;flex-direction:column}
        .leader-name{font-size:1.1rem;font-weight:600;margin-bottom:5px;color:#1f2937;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .leader-position{font-size:.85rem;color:#667eea;margin-bottom:8px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .leader-role{display:inline-block;background:#e0f2fe;color:#0369a1;padding:3px 10px;border-radius:15px;font-size:.7rem;font-weight:600;margin-bottom:10px}
        .leader-bio{font-size:.8rem;color:#6b7280;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:10px;flex:1}
        .leader-email{font-size:.75rem;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:10px}
        .leader-email i{margin-right:5px;color:#667eea}
        .leader-footer{margin-top:auto;padding-top:10px;border-top:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center}
        .stats-card{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:10px;padding:20px}
        .stats-icon{font-size:2.5rem;opacity:.8}
        .stats-number{font-size:2rem;font-weight:bold;margin:10px 0}
        .stats-label{font-size:.9rem;opacity:.9}
        .empty-state{text-align:center;padding:3rem 1rem;color:#9ca3af}
        .empty-state i{font-size:3rem;margin-bottom:1rem;opacity:.5}
        .current-image-container{margin-bottom:15px;text-align:center}
        .current-image{max-width:100%;max-height:200px;border-radius:8px;border:2px solid #e5e7eb}
        .no-image{color:#9ca3af;font-style:italic}
    </style>
</head>
<body>
    <?php include_once 'components/admin-sidebar.php'; ?>
    <?php include_once 'components/admin-navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2 class="mb-0">Leadership Management</h2>
                        <p class="text-muted mb-0">Manage school leadership team</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaderModal">
                        <i class="fas fa-plus me-2"></i>Add New Leader
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <i class="fas fa-user-tie stats-icon"></i>
                    <div class="stats-number"><?= $stats['total_leadership']??0 ?></div>
                    <div class="stats-label">Total Leadership</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card" style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%)">
                    <i class="fas fa-users stats-icon"></i>
                    <div class="stats-number"><?= $stats['active_leaders']??0 ?></div>
                    <div class="stats-label">Active Leaders</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card" style="background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%)">
                    <i class="fas fa-calendar-alt stats-icon"></i>
                    <div class="stats-number"><?= round($stats['avg_years_service']??0,1) ?> yrs</div>
                    <div class="stats-label">Avg Service Years</div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php if(empty($leadership)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body empty-state">
                            <i class="fas fa-user-tie"></i>
                            <h4>No leadership members added yet</h4>
                            <p class="text-muted mb-4">Add your first leadership member to get started</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaderModal">
                                <i class="fas fa-plus me-2"></i>Add First Leader
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($leadership as $leader): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="leader-card">
                            <div class="position-relative">
                                <img src="<?= !empty($leader['image_url'])?img_url($leader['image_url']):'https://ui-avatars.com/api/?name='.urlencode($leader['full_name']).'&background=667eea&color=fff&size=400' ?>" 
                                     alt="<?= htmlspecialchars($leader['full_name']) ?>" 
                                     class="leader-image"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($leader['full_name']) ?>&background=667eea&color=fff&size=400'">
                                <div class="position-absolute top-0 end-0 p-2">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item edit-leader-btn" type="button" 
                                                        data-bs-toggle="modal" data-bs-target="#editLeaderModal"
                                                        data-id="<?= $leader['id'] ?>"
                                                        data-full-name="<?= htmlspecialchars($leader['full_name']) ?>"
                                                        data-position="<?= htmlspecialchars($leader['position']) ?>"
                                                        data-role-badge="<?= htmlspecialchars($leader['role_badge']) ?>"
                                                        data-short-bio="<?= htmlspecialchars($leader['short_bio']) ?>"
                                                        data-qualifications="<?= htmlspecialchars($leader['qualifications']) ?>"
                                                        data-email="<?= htmlspecialchars($leader['email']) ?>"
                                                        data-phone="<?= htmlspecialchars($leader['phone']) ?>"
                                                        data-facebook-url="<?= htmlspecialchars($leader['facebook_url']) ?>"
                                                        data-twitter-url="<?= htmlspecialchars($leader['twitter_url']) ?>"
                                                        data-linkedin-url="<?= htmlspecialchars($leader['linkedin_url']) ?>"
                                                        data-whatsapp-number="<?= htmlspecialchars($leader['whatsapp_number']) ?>"
                                                        data-join-date="<?= $leader['join_date'] ?>"
                                                        data-display-order="<?= $leader['display_order'] ?>"
                                                        data-status="<?= $leader['status'] ?>">
                                                    <i class="fas fa-edit me-2 text-primary"></i>Edit
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item upload-image-btn" type="button" 
                                                        data-bs-toggle="modal" data-bs-target="#uploadImageModal"
                                                        data-id="<?= $leader['id'] ?>"
                                                        data-name="<?= htmlspecialchars($leader['full_name']) ?>"
                                                        data-image-url="<?= !empty($leader['image_url'])?htmlspecialchars($leader['image_url']):'' ?>">
                                                    <i class="fas fa-camera me-2 text-info"></i>Upload/Change Image
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" type="button" 
                                                        onclick="confirmDelete(<?= $leader['id'] ?>,'<?= htmlspecialchars($leader['full_name']) ?>')">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="leader-info">
                                <h6 class="leader-name" title="<?= htmlspecialchars($leader['full_name']) ?>">
                                    <?= htmlspecialchars($leader['full_name']) ?>
                                </h6>
                                <div class="leader-position" title="<?= htmlspecialchars($leader['position']) ?>">
                                    <?= htmlspecialchars($leader['position']) ?>
                                </div>
                                <?php if($leader['role_badge']): ?>
                                    <span class="leader-role"><?= htmlspecialchars($leader['role_badge']) ?></span>
                                <?php endif; ?>
                                <div class="leader-bio"><?= htmlspecialchars($leader['short_bio']) ?></div>
                                <div class="leader-email" title="<?= htmlspecialchars($leader['email']) ?>">
                                    <i class="fas fa-envelope"></i><?= htmlspecialchars($leader['email']) ?>
                                </div>
                                <div class="leader-footer">
                                    <span class="badge bg-<?= $leader['status']==='active'?'success':'secondary' ?>">
                                        <?= ucfirst($leader['status']) ?>
                                    </span>
                                    <small class="text-muted">Order: <?= $leader['display_order'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="addLeaderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Leadership Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addLeaderForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_leader">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position *</label>
                                <input type="text" class="form-control" name="position" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role Badge</label>
                                <input type="text" class="form-control" name="role_badge" value="Leadership">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" placeholder="+250 788 000 000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Join Date</label>
                                <input type="date" class="form-control" name="join_date">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Qualifications</label>
                                <textarea class="form-control" name="qualifications" rows="2"></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" value="0" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Short Biography *</label>
                            <textarea class="form-control" name="short_bio" rows="3" required maxlength="500"></textarea>
                            <small class="text-muted">Maximum 500 characters</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Facebook URL</label>
                                <input type="url" class="form-control" name="facebook_url">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Twitter URL</label>
                                <input type="url" class="form-control" name="twitter_url">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control" name="linkedin_url">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp Number</label>
                                <input type="tel" class="form-control" name="whatsapp_number">
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
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Leader</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editLeaderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Leadership Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editLeaderForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_leader">
                        <input type="hidden" name="id" id="editLeaderId">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" id="editFullName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position *</label>
                                <input type="text" class="form-control" name="position" id="editPosition" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role Badge</label>
                                <input type="text" class="form-control" name="role_badge" id="editRoleBadge">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" id="editEmail" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" id="editPhone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Join Date</label>
                                <input type="date" class="form-control" name="join_date" id="editJoinDate">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Qualifications</label>
                                <textarea class="form-control" name="qualifications" id="editQualifications" rows="2"></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="editDisplayOrder" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Short Biography *</label>
                            <textarea class="form-control" name="short_bio" id="editShortBio" rows="3" required maxlength="500"></textarea>
                            <small class="text-muted">Maximum 500 characters</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Facebook URL</label>
                                <input type="url" class="form-control" name="facebook_url" id="editFacebookUrl">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Twitter URL</label>
                                <input type="url" class="form-control" name="twitter_url" id="editTwitterUrl">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control" name="linkedin_url" id="editLinkedinUrl">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp Number</label>
                                <input type="tel" class="form-control" name="whatsapp_number" id="editWhatsappNumber">
                            </div>
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
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Leader</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadImageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadImageModalLabel">Upload Leadership Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="uploadImageForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="upload_leader_image">
                        <input type="hidden" name="id" id="uploadLeaderId">
                        <div class="current-image-container mb-3">
                            <p class="mb-2"><strong>Current Image:</strong></p>
                            <div id="currentImagePreview">
                                <!-- Current image will be shown here -->
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select New Image</label>
                            <input type="file" class="form-control" name="leader_image" accept="image/*" required>
                            <small class="text-muted">Max 5MB. JPG, PNG, GIF. Recommended: 400x500 pixels</small>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>Uploading a new image will replace the existing one.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-2"></i>Upload New Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none">
        <input type="hidden" name="action" value="delete_leader">
        <input type="hidden" name="id" id="deleteLeaderId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include_once 'components/admin-scripts.php'; ?>
    
    <script>
        $(document).ready(function(){
            <?php if($success_message): ?>
                Swal.fire({icon:'success',title:'Success!',text:'<?= addslashes($success_message) ?>',toast:true,position:'top-end',showConfirmButton:false,timer:3000,timerProgressBar:true});
            <?php endif; ?>
            
            <?php if($error_message): ?>
                Swal.fire({icon:'error',title:'Error!',text:'<?= addslashes($error_message) ?>',toast:true,position:'top-end',showConfirmButton:false,timer:5000,timerProgressBar:true});
            <?php endif; ?>
            
            // Edit button click handler - FIXED
            $('.edit-leader-btn').on('click',function(){
                const btn = $(this);
                $('#editLeaderId').val(btn.data('id'));
                $('#editFullName').val(btn.data('full-name'));
                $('#editPosition').val(btn.data('position'));
                $('#editRoleBadge').val(btn.data('role-badge'));
                $('#editShortBio').val(btn.data('short-bio'));
                $('#editQualifications').val(btn.data('qualifications'));
                $('#editEmail').val(btn.data('email'));
                $('#editPhone').val(btn.data('phone'));
                $('#editFacebookUrl').val(btn.data('facebook-url'));
                $('#editTwitterUrl').val(btn.data('twitter-url'));
                $('#editLinkedinUrl').val(btn.data('linkedin-url'));
                $('#editWhatsappNumber').val(btn.data('whatsapp-number'));
                $('#editJoinDate').val(btn.data('join-date'));
                $('#editDisplayOrder').val(btn.data('display-order'));
                $('#editStatus').val(btn.data('status'));
            });
            
            // Upload image button click handler - IMPROVED
            $('.upload-image-btn').on('click',function(){
                const btn = $(this);
                const leaderId = btn.data('id');
                const leaderName = btn.data('name');
                const imageUrl = btn.data('image-url');
                
                $('#uploadLeaderId').val(leaderId);
                $('#uploadImageModalLabel').text('Upload Image for ' + leaderName);
                
                // Show current image preview
                let currentImageHtml = '';
                if (imageUrl) {
                    currentImageHtml = `<img src="<?= img_url('') ?>${imageUrl}" alt="Current Image" class="current-image" onerror="this.parentElement.innerHTML='<p class=\'no-image\'>No image uploaded</p>'">`;
                } else {
                    currentImageHtml = '<p class="no-image">No image uploaded</p>';
                }
                $('#currentImagePreview').html(currentImageHtml);
            });
            
            // Form validation for add/edit
            $('#addLeaderForm, #editLeaderForm').submit(function(e){
                const email = $(this).find('input[name="email"]').val().trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if(!emailRegex.test(email)){
                    e.preventDefault();
                    Swal.fire({icon:'error',title:'Invalid Email',text:'Please enter a valid email address',confirmButtonColor:'#667eea'});
                    return false;
                }
                
                const shortBio = $(this).find('textarea[name="short_bio"]').val().trim();
                if(shortBio.length > 500){
                    e.preventDefault();
                    Swal.fire({icon:'error',title:'Bio Too Long',text:'Short biography must not exceed 500 characters',confirmButtonColor:'#667eea'});
                    return false;
                }
                
                Swal.fire({title:'Processing...',text:'Please wait',allowOutsideClick:false,didOpen:()=>{Swal.showLoading()}});
                return true;
            });
            
            // Form validation for image upload
            $('#uploadImageForm').submit(function(e){
                const fileInput = $(this).find('input[name="leader_image"]')[0];
                
                if(fileInput.files.length === 0){
                    e.preventDefault();
                    Swal.fire({icon:'warning',title:'No File Selected',text:'Please select an image file',confirmButtonColor:'#667eea'});
                    return false;
                }
                
                const file = fileInput.files[0];
                const allowedTypes = ['image/jpeg','image/jpg','image/png','image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if(!allowedTypes.includes(file.type)){
                    e.preventDefault();
                    Swal.fire({icon:'error',title:'Invalid File Type',text:'Only JPG, JPEG, PNG, and GIF files are allowed',confirmButtonColor:'#667eea'});
                    return false;
                }
                
                if(file.size > maxSize){
                    e.preventDefault();
                    Swal.fire({icon:'error',title:'File Too Large',text:'Maximum file size is 5MB',confirmButtonColor:'#667eea'});
                    return false;
                }
                
                Swal.fire({title:'Uploading...',text:'Please wait',allowOutsideClick:false,didOpen:()=>{Swal.showLoading()}});
                return true;
            });
            
            // Clear image preview when modal is hidden
            $('#uploadImageModal').on('hidden.bs.modal', function () {
                $('#currentImagePreview').html('');
                $('#uploadImageForm')[0].reset();
            });
        });
        
        function confirmDelete(leaderId, leaderName){
            Swal.fire({
                title:'Delete Leadership Member?',
                html:`Are you sure you want to delete <strong>${leaderName}</strong>?<br><br>This action cannot be undone.`,
                icon:'warning',
                showCancelButton:true,
                confirmButtonColor:'#ef4444',
                cancelButtonColor:'#6c757d',
                confirmButtonText:'Yes, delete it!',
                cancelButtonText:'Cancel',
                reverseButtons:true
            }).then((result)=>{
                if(result.isConfirmed){
                    Swal.fire({title:'Deleting...',text:'Please wait',allowOutsideClick:false,didOpen:()=>{Swal.showLoading()}});
                    $('#deleteLeaderId').val(leaderId);
                    $('#deleteForm').submit();
                }
            });
        }
        
        function toggleSidebar(){
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>