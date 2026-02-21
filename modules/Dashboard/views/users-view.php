<?php
/**
 * User View Page
 * File: modules/Dashboard/views/users-view.php
 */

// Include required files
require_once ROOT_PATH . '/helpers/AuthMiddleware.php';
require_once ROOT_PATH . '/helpers/PermissionHelper.php';
require_once ROOT_PATH . '/modules/Authentication/controllers/UserController.php';

$auth = new AuthMiddleware();
$currentUser = $auth->requireAuth(['users.view']);

// Get user permissions
$userPermissions = $currentUser->permissions ?? [];
$pageTitle = 'User Details';
$currentPage = 'users-view.php';

// Initialize controller
$userController = new UserController();

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . '/admin/users-management');
    exit;
}

$user = $userController->show($_GET['id']);

if (!$user) {
    header('Location: ' . BASE_URL . '/admin/users-management?error=User not found');
    exit;
}

include LAYOUTS_PATH . '/admin-header.php';
?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">

  <script src="<?= admin_js_url('hs.theme-appearance.js') ?>"></script>
  <script src="<?= ROOT_PATH ?>/dashboard-assets/vendor/hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js"></script>
  
  <?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
  <?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<main id="content" role="main" class="main">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="<?= BASE_URL ?>/admin/users-management">
                                    Users Management
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                User Details
                            </li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">
                        <?= htmlspecialchars($user['full_name'] ?? $user['firstname'] . ' ' . $user['lastname']) ?>
                    </h1>
                </div>

                <div class="col-sm-auto">
                    <a href="<?= BASE_URL ?>/admin/users-management" class="btn btn-dark">
                        <i class="bi-arrow-left me-1"></i> Back to Users
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-lg-4 mb-3 mb-lg-5">
                <!-- Profile Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="avatar avatar-xl avatar-circle mx-auto mb-3">
                                <?php if (!empty($user['photo'])): ?>
                                    <img class="avatar-img" src="<?= BASE_URL ?>/uploads/<?= $user['photo'] ?>" 
                                         alt="<?= htmlspecialchars($user['firstname']) ?>">
                                <?php else: ?>
                                    <div class="avatar avatar-xl avatar-soft-primary avatar-circle">
                                        <span class="avatar-initials">
                                            <?= strtoupper(substr($user['firstname'] ?? 'U', 0, 1) . substr($user['lastname'] ?? 'U', 0, 1)) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h4 class="mb-1"><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></h4>
                            
                            <?php
                            $statusClasses = [
                                'active' => 'bg-success',
                                'pending' => 'bg-warning',
                                'inactive' => 'bg-secondary',
                                'suspended' => 'bg-danger'
                            ];
                            ?>
                            <span class="badge <?= $statusClasses[$user['status']] ?? 'bg-secondary' ?> mb-2">
                                <?= ucfirst($user['status']) ?>
                            </span>
                            
                            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                            <?php if (!empty($user['phone'])): ?>
                                <p class="text-muted"><?= htmlspecialchars($user['phone']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <?php if (hasPermission($userPermissions, 'users.edit')): ?>
                                <a href="<?= BASE_URL ?>/admin/users-add-user?id=<?= $user['id'] ?>" 
                                   class="btn btn-primary">
                                    <i class="bi-pencil me-1"></i> Edit User
                                </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission($userPermissions, 'messages.view')): ?>
                                <button class="btn btn-white" onclick="sendMessage(<?= $user['id'] ?>)">
                                    <i class="bi-envelope me-1"></i> Send Message
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- End Profile Card -->
            </div>

            <div class="col-lg-8 mb-3 mb-lg-5">
                <!-- Details Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Account Details</h4>
                    </div>
                    
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Username</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($user['username'] ?? '—') ?></dd>
                            
                            <dt class="col-sm-4">Role</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-soft-dark text-dark">
                                    <?= htmlspecialchars($user['role_name'] ?? 'No Role') ?>
                                </span>
                                <?php if ($user['is_super_admin']): ?>
                                    <span class="badge bg-soft-primary text-primary ms-1">
                                        <i class="bi-shield-check"></i> Super Admin
                                    </span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Member ID</dt>
                            <dd class="col-sm-8">
                                <?php if ($user['member_id']): ?>
                                    <a href="<?= BASE_URL ?>/admin/membership-view?id=<?= $user['member_id'] ?>">
                                        <?= htmlspecialchars($user['membership_number'] ?? 'View Member') ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Not linked</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Email Verified</dt>
                            <dd class="col-sm-8">
                                <?php if ($user['email_verified']): ?>
                                    <span class="badge bg-soft-success text-success">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-soft-warning text-warning">No</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">ADEPR Member</dt>
                            <dd class="col-sm-8">
                                <?php if ($user['is_adepr_member']): ?>
                                    <span class="badge bg-soft-success text-success">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-soft-secondary text-secondary">No</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Can Manage Website</dt>
                            <dd class="col-sm-8">
                                <?php if ($user['can_manage_website']): ?>
                                    <span class="badge bg-soft-success text-success">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-soft-secondary text-secondary">No</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Last Login</dt>
                            <dd class="col-sm-8">
                                <?php if ($user['last_login']): ?>
                                    <?= date('M d, Y H:i:s', strtotime($user['last_login'])) ?>
                                    <br><small class="text-muted">
                                        (<?= time_elapsed_string($user['last_login']) ?>)
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Created</dt>
                            <dd class="col-sm-8">
                                <?= date('M d, Y H:i:s', strtotime($user['created_at'])) ?>
                            </dd>
                            
                            <dt class="col-sm-4">Updated</dt>
                            <dd class="col-sm-8">
                                <?= date('M d, Y H:i:s', strtotime($user['updated_at'])) ?>
                            </dd>
                        </dl>
                    </div>
                </div>
                <!-- End Details Card -->
                
                <?php if (!empty($user['bio'])): ?>
                    <!-- Bio Card -->
                    <div class="card mt-3 mt-lg-5">
                        <div class="card-header">
                            <h4 class="card-header-title">Bio</h4>
                        </div>
                        
                        <div class="card-body">
                            <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                        </div>
                    </div>
                    <!-- End Bio Card -->
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

<script>
function sendMessage(userId) {
    Swal.fire({
        title: 'Send Message',
        html: `
            <textarea id="messageText" class="swal2-textarea" placeholder="Type your message..."></textarea>
        `,
        showCancelButton: true,
        confirmButtonText: 'Send',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const message = document.getElementById('messageText').value;
            if (!message) {
                Swal.showValidationMessage('Please enter a message');
                return false;
            }
            
            return $.ajax({
                url: '<?= BASE_URL ?>/api/messages?action=send',
                type: 'POST',
                data: {
                    user_id: userId,
                    message: message
                }
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.value && result.value.success) {
            Swal.fire('Sent!', 'Message sent successfully', 'success');
        }
    });
}
</script>

</body>
</html>