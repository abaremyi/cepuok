<?php
/**
 * Profile Settings Page
 * File: modules/Dashboard/views/profile-settings.php
 */
$pageTitle = 'Profile Settings';
$requiredPermission = null; // Any authenticated user can edit their profile
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';

// Get full user details
require_once ROOT_PATH . '/modules/Authentication/controllers/UserController.php';
$userController = new UserController();
$userDetails = $userController->show($currentUser->user_id);

// Get available timezones
$timezones = DateTimeZone::listIdentifiers();
?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
    <?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>
    <script>
        (function() {
            var el = document.getElementById('sessionLockOverlay');
            if (el) el.dataset.email = <?= json_encode($currentUser->email ?? '') ?>;
        })();
    </script>

    <?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
    <?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

    <main id="content" role="main" class="main">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-sm">
                        <h1 class="page-header-title">
                            <i class="bi-gear me-2"></i> Profile Settings
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-no-gutter">
                                <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?= url('admin/profile') ?>">My Profile</a></li>
                                <li class="breadcrumb-item active">Settings</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-sm-auto">
                        <a href="<?= url('admin/profile') ?>" class="btn btn-ghost-secondary btn-sm">
                            <i class="bi-arrow-left me-1"></i> Back to Profile
                        </a>
                    </div>
                </div>
            </div>
            <!-- End Page Header -->

            <div class="row">
                <div class="col-lg-3">
                    <!-- Navbar -->
                    <div class="navbar-expand-lg navbar-vertical mb-3 mb-lg-5">
                        <div class="d-grid">
                            <button type="button" class="navbar-toggler btn btn-white mb-3" 
                                    data-bs-toggle="collapse" data-bs-target="#navbarVerticalNavMenu">
                                <span class="d-flex justify-content-between align-items-center">
                                    <span class="text-dark">Menu</span>
                                    <span class="navbar-toggler-default"><i class="bi-list"></i></span>
                                    <span class="navbar-toggler-toggled"><i class="bi-x"></i></span>
                                </span>
                            </button>
                        </div>

                        <div id="navbarVerticalNavMenu" class="collapse navbar-collapse">
                            <ul id="navbarSettings" class="js-sticky-block js-scrollspy card card-navbar-nav nav nav-tabs nav-lg nav-vertical"
                                data-hs-sticky-block-options='{
                                    "parentSelector": "#navbarVerticalNavMenu",
                                    "targetSelector": "#header",
                                    "breakpoint": "lg",
                                    "startPoint": "#navbarVerticalNavMenu",
                                    "endPoint": "#stickyBlockEndPoint",
                                    "stickyOffsetTop": 20
                                }'>
                                <li class="nav-item">
                                    <a class="nav-link active" href="#basicInfoSection">
                                        <i class="bi-person nav-icon"></i> Basic Information
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#contactSection">
                                        <i class="bi-envelope nav-icon"></i> Contact
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#passwordSection">
                                        <i class="bi-key nav-icon"></i> Password
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#preferencesSection">
                                        <i class="bi-gear nav-icon"></i> Preferences
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#twoFactorSection">
                                        <i class="bi-shield-lock nav-icon"></i> Two-factor Authentication
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#notificationsSection">
                                        <i class="bi-bell nav-icon"></i> Notifications
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div id="stickyBlockStartPoint"></div>

                    <div class="d-grid gap-3 gap-lg-5">
                        <!-- Card - Basic Information -->
                        <div class="card" id="basicInfoSection">
                            <div class="card-header">
                                <h4 class="card-title">Basic Information</h4>
                            </div>
                            <div class="card-body">
                                <!-- Profile Cover -->
                                <div class="profile-cover mb-4">
                                    <div class="profile-cover-img-wrapper">
                                        <?php
                                        $coverPath = ROOT_PATH . '/uploads/users/cover-' . $currentUser->user_id . '.jpg';
                                        $coverUrl = file_exists($coverPath) 
                                            ? BASE_URL . '/uploads/users/cover-' . $currentUser->user_id . '.jpg'
                                            : BASE_URL . '/uploads/users/cover-default-1.jpg';
                                        ?>
                                        <img id="profileCoverImg" class="profile-cover-img" 
                                             src="<?= $coverUrl ?>" 
                                             alt="Cover Image">
                                        
                                        <div class="profile-cover-content profile-cover-uploader p-3">
                                            <input type="file" class="js-file-attach profile-cover-uploader-input" 
                                                   id="profileCoverUploader"
                                                   data-hs-file-attach-options='{
                                                       "textTarget": "#profileCoverImg",
                                                       "mode": "image",
                                                       "targetAttr": "src",
                                                       "allowTypes": [".png", ".jpeg", ".jpg"]
                                                   }'>
                                            <label class="profile-cover-uploader-label btn btn-sm btn-white" 
                                                   for="profileCoverUploader">
                                                <i class="bi-camera-fill me-1"></i>
                                                <span class="d-none d-sm-inline-block">Upload cover</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Avatar -->
                                <div class="mb-4">
                                    <label class="avatar avatar-xxl avatar-circle avatar-uploader" 
                                           for="avatarUploader">
                                        <?php if (!empty($userDetails['photo']) && file_exists(ROOT_PATH . '/uploads/' . $userDetails['photo'])): ?>
                                            <img id="avatarImg" class="avatar-img" 
                                                 src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($userDetails['photo']) ?>" 
                                                 alt="Profile Photo">
                                        <?php else: ?>
                                            <div id="avatarPlaceholder" class="avatar-xxl avatar-soft-primary avatar-circle d-flex align-items-center justify-content-center">
                                                <span class="avatar-initials display-4">
                                                    <?= strtoupper(substr($userDetails['firstname'] ?? '', 0, 1) . substr($userDetails['lastname'] ?? '', 0, 1)) ?>
                                                </span>
                                            </div>
                                            <img id="avatarImg" class="avatar-img" style="display:none;">
                                        <?php endif; ?>
                                        
                                        <input type="file" class="js-file-attach avatar-uploader-input" 
                                               id="avatarUploader"
                                               data-hs-file-attach-options='{
                                                   "textTarget": "#avatarImg",
                                                   "mode": "image",
                                                   "targetAttr": "src",
                                                   "allowTypes": [".png", ".jpeg", ".jpg"]
                                               }'>
                                        <span class="avatar-uploader-trigger">
                                            <i class="bi-pencil-fill avatar-uploader-icon shadow-sm"></i>
                                        </span>
                                    </label>
                                </div>
                                <!-- Profile Cover -->

                                <!-- Avatar -->
                                <div class="mb-4">
                                    <label class="avatar avatar-xxl avatar-circle avatar-uploader" 
                                           for="avatarUploader">
                                        <?php if (!empty($userDetails['photo'])): ?>
                                            <img id="avatarImg" class="avatar-img" 
                                                 src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($userDetails['photo']) ?>" 
                                                 alt="Profile Photo">
                                        <?php else: ?>
                                            <div id="avatarPlaceholder" class="avatar-xxl avatar-soft-primary avatar-circle d-flex align-items-center justify-content-center">
                                                <span class="avatar-initials display-4">
                                                    <?= strtoupper(substr($userDetails['firstname'] ?? '', 0, 1) . substr($userDetails['lastname'] ?? '', 0, 1)) ?>
                                                </span>
                                            </div>
                                            <img id="avatarImg" class="avatar-img" style="display:none;">
                                        <?php endif; ?>
                                        
                                        <input type="file" class="js-file-attach avatar-uploader-input" 
                                               id="avatarUploader"
                                               data-hs-file-attach-options='{
                                                   "textTarget": "#avatarImg",
                                                   "mode": "image",
                                                   "targetAttr": "src",
                                                   "allowTypes": [".png", ".jpeg", ".jpg"]
                                               }'>
                                        <span class="avatar-uploader-trigger">
                                            <i class="bi-pencil-fill avatar-uploader-icon shadow-sm"></i>
                                        </span>
                                    </label>
                                </div>

                                <!-- Form -->
                                <form id="basicInfoForm">
                                    <div class="row mb-4">
                                        <div class="col-sm-6">
                                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="firstname" 
                                                   value="<?= htmlspecialchars($userDetails['firstname'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="lastname" 
                                                   value="<?= htmlspecialchars($userDetails['lastname'] ?? '') ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-sm-6">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" name="username" 
                                                   value="<?= htmlspecialchars($userDetails['username'] ?? '') ?>">
                                            <small class="form-text">Your unique username for login</small>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Display Name</label>
                                            <input type="text" class="form-control" name="display_name" 
                                                   value="<?= htmlspecialchars($userDetails['firstname'] ?? '') . ' ' . htmlspecialchars($userDetails['lastname'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-sm-6">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control" name="dob" 
                                                   value="<?= $userDetails['date_of_birth'] ?? '' ?>">
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Gender</label>
                                            <select class="form-select" name="gender">
                                                <option value="">Prefer not to say</option>
                                                <option value="Male" <?= ($userDetails['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
                                                <option value="Female" <?= ($userDetails['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Bio</label>
                                        <textarea class="form-control" name="bio" rows="3" 
                                                  placeholder="Tell us about yourself..."><?= htmlspecialchars($userDetails['bio'] ?? '') ?></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Card - Contact -->
                        <div class="card" id="contactSection">
                            <div class="card-header">
                                <h4 class="card-title">Contact Information</h4>
                            </div>
                            <div class="card-body">
                                <form id="contactInfoForm">
                                    <div class="row mb-4">
                                        <div class="col-sm-6">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?= htmlspecialchars($userDetails['email'] ?? '') ?>" required>
                                            <small class="form-text">We'll never share your email</small>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" name="phone" 
                                                   value="<?= htmlspecialchars($userDetails['phone'] ?? '') ?>"
                                                   placeholder="+250 788 000 000">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="2" 
                                                  placeholder="Your address"><?= htmlspecialchars($userDetails['address'] ?? '') ?></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Update Contact</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Card - Password -->
                        <div class="card" id="passwordSection">
                            <div class="card-header">
                                <h4 class="card-title">Change Password</h4>
                            </div>
                            <div class="card-body">
                                <form id="passwordForm">
                                    <div class="row mb-4">
                                        <div class="col-sm-4">
                                            <label class="form-label">Current Password</label>
                                            <input type="password" class="form-control" name="current_password" required>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label">New Password</label>
                                            <input type="password" class="form-control" name="new_password" required>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" name="confirm_password" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <h6>Password Requirements:</h6>
                                        <ul class="fs-6 text-muted">
                                            <li>Minimum 8 characters long</li>
                                            <li>At least one uppercase letter</li>
                                            <li>At least one number</li>
                                            <li>At least one special character</li>
                                        </ul>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Update Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Card - Preferences -->
                        <div class="card" id="preferencesSection">
                            <div class="card-header">
                                <h4 class="card-title">Preferences</h4>
                            </div>
                            <div class="card-body">
                                <form id="preferencesForm">
                                    <div class="row mb-4">
                                        <div class="col-sm-6">
                                            <label class="form-label">Language</label>
                                            <select class="form-select" name="language">
                                                <option value="en" selected>English</option>
                                                <option value="fr">Français</option>
                                                <option value="rw">Kinyarwanda</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Time Zone</label>
                                            <select class="form-select" name="timezone">
                                                <?php foreach ($timezones as $tz): ?>
                                                    <option value="<?= $tz ?>"><?= str_replace('_', ' ', $tz) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Date Format</label>
                                        <select class="form-select" name="date_format">
                                            <option value="Y-m-d">2024-01-31</option>
                                            <option value="m/d/Y">01/31/2024</option>
                                            <option value="d/m/Y">31/01/2024</option>
                                        </select>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Card - Two Factor -->
                        <div class="card" id="twoFactorSection">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0">Two-factor Authentication</h4>
                                    <span class="badge bg-soft-danger text-danger ms-2">Disabled</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="card-text">
                                    Add an extra layer of security to your account. Once enabled, you'll need to enter 
                                    a verification code from your authenticator app in addition to your password.
                                </p>
                                <button class="btn btn-primary" onclick="enable2FA()">
                                    <i class="bi-shield-lock me-1"></i> Enable Two-factor Authentication
                                </button>
                            </div>
                        </div>

                        <!-- Card - Notifications -->
                        <div class="card" id="notificationsSection">
                            <div class="card-header">
                                <h4 class="card-title">Notification Settings</h4>
                            </div>
                            <div class="card-body">
                                <form id="notificationsForm">
                                    <div class="mb-4">
                                        <h6>Email Notifications</h6>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="notifyLogin" checked>
                                            <label class="form-check-label" for="notifyLogin">
                                                New login alerts
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="notifyMembership" checked>
                                            <label class="form-check-label" for="notifyMembership">
                                                Membership updates
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="notifyEvents">
                                            <label class="form-check-label" for="notifyEvents">
                                                Event reminders
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h6>Push Notifications</h6>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="pushMessages" checked>
                                            <label class="form-check-label" for="pushMessages">
                                                Messages and mentions
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="pushTasks">
                                            <label class="form-check-label" for="pushTasks">
                                                Task assignments
                                            </label>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div id="stickyBlockEndPoint"></div>
                </div>
            </div>
        </div>

        <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
    </main>

    <?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

    <script>
    (function() {
        'use strict';

        // Basic Info Form
        document.getElementById('basicInfoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Add avatar if changed
            const avatarFile = document.getElementById('avatarUploader').files[0];
            if (avatarFile) {
                formData.append('photo', avatarFile);
            }
            
            try {
                const res = await fetch(`${BASE_URL}/api/users?action=update&id=<?= $currentUser->user_id ?>`, {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                });
                
                const data = await res.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Profile updated successfully',
                        timer: 2000
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to update', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Network error', 'error');
            }
        });

        // Contact Form
        document.getElementById('contactInfoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const res = await fetch(`${BASE_URL}/api/users?action=update&id=<?= $currentUser->user_id ?>`, {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                });
                
                const data = await res.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Contact information updated',
                        timer: 2000
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to update', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Network error', 'error');
            }
        });

        // Password Form
        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const current = this.current_password.value;
            const newPass = this.new_password.value;
            const confirm = this.confirm_password.value;
            
            if (newPass !== confirm) {
                Swal.fire('Error', 'New passwords do not match', 'error');
                return;
            }
            
            try {
                const res = await fetch(`${BASE_URL}/api/auth?action=change-password`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        current_password: current,
                        new_password: newPass
                    })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Password updated successfully',
                        timer: 2000
                    });
                    this.reset();
                } else {
                    Swal.fire('Error', data.message || 'Failed to update password', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Network error', 'error');
            }
        });

        // Preferences Form
        document.getElementById('preferencesForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            // Save preferences via API
            Swal.fire('Success', 'Preferences saved', 'success');
        });

        // Notifications Form
        document.getElementById('notificationsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            // Save notification settings via API
            Swal.fire('Success', 'Notification settings saved', 'success');
        });

        // Enable 2FA
        window.enable2FA = function() {
            Swal.fire({
                title: 'Enable Two-factor Authentication',
                html: `
                    <div class="text-center">
                        <p>Scan this QR code with your authenticator app:</p>
                        <div class="bg-light p-3 mb-3">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=otpauth://totp/CEP:<?= $currentUser->email ?>?secret=DEMO&issuer=CEP" 
                                 alt="QR Code" class="img-fluid">
                        </div>
                        <p class="mb-0">Or enter this code manually:</p>
                        <code class="bg-light p-2 d-inline-block mb-3">DEMO 1234 5678</code>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Enable',
                cancelButtonText: 'Cancel',
                width: '500px'
            });
        };

        // Avatar preview
        document.getElementById('avatarUploader').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPlaceholder')?.style.setProperty('display', 'none');
                    document.getElementById('avatarImg').style.display = 'block';
                    document.getElementById('avatarImg').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    })();
    </script>
</body>
</html>