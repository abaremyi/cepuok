<?php
/**
 * Add/Edit User Page
 * File: modules/Dashboard/views/users-add-user.php
 */

// Include required files
require_once ROOT_PATH . '/helpers/AuthMiddleware.php';
require_once ROOT_PATH . '/helpers/PermissionHelper.php';
require_once ROOT_PATH . '/modules/Authentication/controllers/UserController.php';

$auth = new AuthMiddleware();
$currentUser = $auth->requireAuth(['users.create', 'users.edit']);

// Get user permissions
$userPermissions = $currentUser->permissions ?? [];
$pageTitle = 'Add User';
$currentPage = 'users-add-user.php';

// Initialize controller
$userController = new UserController();
$roles = $userController->getRoles();
$availableMembers = $userController->getAvailableMembers();

$user = null;
$isEdit = false;

// Check if editing existing user
if (isset($_GET['id'])) {
    $isEdit = true;
    $pageTitle = 'Edit User';
    $user = $userController->show($_GET['id']);
    
    if (!$user) {
        header('Location: ' . BASE_URL . '/admin/users-management?error=User not found');
        exit;
    }
    
    // Check if can edit super admin
    if ($user['is_super_admin'] && !$currentUser->is_super_admin) {
        header('Location: ' . BASE_URL . '/admin/users-management?error=Cannot edit super admin');
        exit;
    }
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
                                <?= $isEdit ? 'Edit User' : 'Add New User' ?>
                            </li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">
                        <?= $isEdit ? 'Edit User: ' . htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) : 'Add New User' ?>
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

        <form id="userForm" method="POST" enctype="multipart/form-data">
            <?php if ($isEdit): ?>
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <!-- Basic Information Card -->
                    <div class="card mb-3 mb-lg-5">
                        <div class="card-header">
                            <h4 class="card-header-title">Basic Information</h4>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <label for="firstname" class="form-label">
                                            First Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="firstname" name="firstname" 
                                               value="<?= htmlspecialchars($user['firstname'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <label for="lastname" class="form-label">
                                            Last Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="lastname" name="lastname" 
                                               value="<?= htmlspecialchars($user['lastname'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <label for="email" class="form-label">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                               placeholder="+250 788 000 000">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                                               placeholder="Auto-generated if empty">
                                    </div>
                                </div>

                                <?php if (!$isEdit): ?>
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="password" class="form-label">
                                                Password <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?>
                                            </label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="password" name="password" 
                                                       <?= !$isEdit ? 'required' : '' ?>>
                                                <button class="btn btn-white" type="button" onclick="generatePassword()">
                                                    <i class="bi-shield-lock"></i> Generate
                                                </button>
                                            </div>
                                            <small class="form-text">Minimum 8 characters</small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <!-- End Basic Information Card -->

                    <!-- Role & Permissions Card -->
                    <div class="card mb-3 mb-lg-5">
                        <div class="card-header">
                            <h4 class="card-header-title">Role & Permissions</h4>
                        </div>

                        <div class="card-body">
                            <div class="mb-4">
                                <label for="role_id" class="form-label">
                                    Role <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    <option value="">Select a role...</option>
                                    <?php foreach ($roles as $role): ?>
                                        <?php if (!$role['is_super_admin'] || ($role['is_super_admin'] && $currentUser->is_super_admin)): ?>
                                            <option value="<?= $role['id'] ?>" 
                                                <?= (isset($user['role_id']) && $user['role_id'] == $role['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($role['name']) ?>
                                                <?= $role['is_super_admin'] ? '(Super Admin)' : '' ?>
                                                <?php if (!empty($role['description'])): ?>
                                                    - <?= htmlspecialchars($role['description']) ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="email_verified" 
                                                   name="email_verified" value="1" 
                                                   <?= (isset($user['email_verified']) && $user['email_verified']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="email_verified">
                                                Email Verified
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_adepr_member" 
                                                   name="is_adepr_member" value="1"
                                                   <?= (isset($user['is_adepr_member']) && $user['is_adepr_member']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="is_adepr_member">
                                                ADEPR Member <i class="bi-question-circle text-muted" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Required for CEP leadership positions"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="can_manage_website" 
                                                   name="can_manage_website" value="1"
                                                   <?= (isset($user['can_manage_website']) && $user['can_manage_website']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="can_manage_website">
                                                Can Manage Website
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Role & Permissions Card -->

                    <!-- Link to Member Card -->
                    <div class="card mb-3 mb-lg-5">
                        <div class="card-header">
                            <h4 class="card-header-title">Link to Member Profile</h4>
                        </div>

                        <div class="card-body">
                            <div class="mb-4">
                                <label for="member_id" class="form-label">Link Existing Member</label>
                                <select class="form-select" id="member_id" name="member_id">
                                    <option value="">None - Create standalone user</option>
                                    <?php foreach ($availableMembers as $member): ?>
                                        <?php if (!$user || !$user['member_id'] || $user['member_id'] == $member['id']): ?>
                                            <option value="<?= $member['id'] ?>" 
                                                <?= (isset($user['member_id']) && $user['member_id'] == $member['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($member['full_name']) ?> 
                                                (<?= htmlspecialchars($member['email']) ?>)
                                                <?= $member['membership_number'] ? ' - ' . $member['membership_number'] : '' ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text">
                                    Link this user account to an existing member profile. Only members without user accounts are shown.
                                </small>
                            </div>
                        </div>
                    </div>
                    <!-- End Link to Member Card -->
                </div>

                <div class="col-lg-4">
                    <!-- Status Card -->
                    <div class="card mb-3 mb-lg-5">
                        <div class="card-header">
                            <h4 class="card-header-title">Account Status</h4>
                        </div>

                        <div class="card-body">
                            <div class="mb-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="pending" <?= (isset($user['status']) && $user['status'] == 'pending') ? 'selected' : '' ?>>
                                        Pending
                                    </option>
                                    <option value="active" <?= (isset($user['status']) && $user['status'] == 'active') ? 'selected' : '' ?>>
                                        Active
                                    </option>
                                    <option value="inactive" <?= (isset($user['status']) && $user['status'] == 'inactive') ? 'selected' : '' ?>>
                                        Inactive
                                    </option>
                                    <option value="suspended" <?= (isset($user['status']) && $user['status'] == 'suspended') ? 'selected' : '' ?>>
                                        Suspended
                                    </option>
                                </select>
                            </div>

                            <?php if ($isEdit && $user['last_login']): ?>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi-clock me-1"></i> Last login: <?= date('M d, Y H:i', strtotime($user['last_login'])) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- End Status Card -->

                    <!-- Profile Photo Card -->
                    <div class="card mb-3 mb-lg-5">
                        <div class="card-header">
                            <h4 class="card-header-title">Profile Photo</h4>
                        </div>

                        <div class="card-body">
                            <!-- Avatar -->
                            <div class="d-flex align-items-center mb-4">
                                <div class="avatar avatar-xl avatar-circle me-3" id="photoPreviewContainer">
                                    <?php if ($isEdit && !empty($user['photo'])): ?>
                                        <img class="avatar-img" src="<?= BASE_URL ?>/uploads/<?= $user['photo'] ?>" 
                                             alt="Profile Photo" id="photoPreview">
                                    <?php else: ?>
                                        <div class="avatar avatar-xl avatar-soft-primary avatar-circle" id="photoPreviewPlaceholder">
                                            <span class="avatar-initials"><?= $isEdit ? strtoupper(substr($user['firstname'] ?? 'U', 0, 1) . substr($user['lastname'] ?? 'U', 0, 1)) : 'PH' ?></span>
                                        </div>
                                        <img class="avatar-img" src="" alt="Preview" id="photoPreview" style="display: none;">
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Profile photo</h5>
                                    <p class="text-body small mb-2">JPG, GIF or PNG. Max size 2MB.</p>
                                    
                                    <div class="d-grid">
                                        <label class="btn btn-white btn-sm mb-2">
                                            <i class="bi-upload me-1"></i> Upload Photo
                                            <input type="file" class="btn-file-input" id="photo" name="photo" 
                                                   accept="image/jpeg,image/png,image/gif" style="display: none;">
                                        </label>
                                        
                                        <?php if ($isEdit && !empty($user['photo'])): ?>
                                            <button type="button" class="btn btn-white btn-sm" onclick="removePhoto()">
                                                <i class="bi-trash me-1"></i> Remove
                                            </button>
                                            <input type="hidden" id="remove_photo" name="remove_photo" value="0">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <!-- End Avatar -->
                        </div>
                    </div>
                    <!-- End Profile Photo Card -->

                    <!-- Account Info Card (for edit mode) -->
                    <?php if ($isEdit): ?>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-header-title">Account Information</h4>
                            </div>

                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-5">Created</dt>
                                    <dd class="col-sm-7"><?= date('M d, Y', strtotime($user['created_at'])) ?></dd>

                                    <dt class="col-sm-5">Updated</dt>
                                    <dd class="col-sm-7"><?= date('M d, Y', strtotime($user['updated_at'])) ?></dd>

                                    <dt class="col-sm-5">Active Sessions</dt>
                                    <dd class="col-sm-7">
                                        <span class="badge bg-soft-info text-info">
                                            <?= $user['active_sessions'] ?? 0 ?>
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- End Account Info Card -->
                </div>
            </div>

            <!-- Form Actions -->
            <div class="position-fixed start-50 bottom-0 translate-middle-x w-100 zi-99 mb-3" style="max-width: 40rem;">
                <div class="card card-sm bg-dark border-dark mx-2">
                    <div class="card-body">
                        <div class="row justify-content-between align-items-center">
                            <div class="col">
                                <?php if ($isEdit && !$user['is_super_admin'] && hasPermission($userPermissions, 'users.delete')): ?>
                                    <button type="button" class="btn btn-ghost-danger" onclick="deleteUser(<?= $user['id'] ?>)">
                                        <i class="bi-trash me-1"></i> Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <div class="d-flex gap-3">
                                    <a href="<?= BASE_URL ?>/admin/users-management" class="btn btn-ghost-light">
                                        <i class="bi-x me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi-check-circle me-1"></i> <?= $isEdit ? 'Save Changes' : 'Create User' ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Form Actions -->
        </form>
    </div>

    <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Preview uploaded image
    $('#photo').change(function(e) {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#photoPreview').attr('src', e.target.result).show();
                $('#photoPreviewPlaceholder, #photoPreviewContainer .avatar-initials').hide();
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Form submission
    $('#userForm').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var url = '<?= BASE_URL ?>/api/users';
        var action = '<?= $isEdit ? 'update' : 'create' ?>';
        
        if ('<?= $isEdit ?>') {
            url += '?action=update&id=<?= $user['id'] ?? '' ?>';
        } else {
            url += '?action=create';
        }
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000
                    }).then(() => {
                        window.location.href = '<?= BASE_URL ?>/admin/users-management';
                    });
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                var response = xhr.responseJSON;
                Swal.fire('Error!', response?.message || 'An error occurred', 'error');
            }
        });
    });
});

// Generate random password
function generatePassword() {
    var length = 12;
    var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    var password = "";
    
    for (var i = 0; i < length; i++) {
        var randomIndex = Math.floor(Math.random() * charset.length);
        password += charset[randomIndex];
    }
    
    $('#password').val(password);
    
    // Copy to clipboard
    navigator.clipboard.writeText(password).then(function() {
        Swal.fire({
            icon: 'info',
            title: 'Password Generated',
            text: 'Password has been copied to clipboard',
            timer: 2000
        });
    });
}

// Remove photo
function removePhoto() {
    $('#remove_photo').val('1');
    $('#photoPreview').hide();
    $('#photoPreviewPlaceholder').show();
    $('#photo').val('');
    Swal.fire({
        icon: 'info',
        title: 'Photo Marked for Removal',
        text: 'The profile photo will be removed when you save',
        timer: 2000
    });
}

// Delete user (from edit page)
function deleteUser(userId) {
    Swal.fire({
        title: 'Delete User?',
        text: "This action cannot be undone!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= BASE_URL ?>/api/users?action=delete',
                type: 'DELETE',
                data: { user_id: userId },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'User has been deleted.', 'success')
                            .then(() => {
                                window.location.href = '<?= BASE_URL ?>/admin/users-management';
                            });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to delete user', 'error');
                }
            });
        }
    });
}

// Auto-generate username from name fields
$('#firstname, #lastname').on('change', function() {
    if (!$('#username').val()) {
        var first = $('#firstname').val().toLowerCase().replace(/[^a-z0-9]/g, '');
        var last = $('#lastname').val().toLowerCase().replace(/[^a-z0-9]/g, '');
        if (first && last) {
            $('#username').val(first + '.' + last);
        }
    }
});
</script>

</body>
</html>