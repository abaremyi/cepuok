<?php
/**
 * User Profile Page
 * File: modules/Dashboard/views/profile.php
 */
$pageTitle = 'My Profile';
$requiredPermission = null; // Any authenticated user can view their profile
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
require_once dirname(__DIR__, 3) . '/helpers/DateHelper.php';

// Get full user details including member information
require_once ROOT_PATH . '/modules/Authentication/controllers/UserController.php';
$userController = new UserController();
$userDetails = $userController->show($currentUser->user_id);

// Get user activity stats
$db = Database::getConnection();
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_sessions,
        MAX(created_at) as last_session
    FROM user_sessions 
    WHERE user_id = ?
");
$stmt->execute([$currentUser->user_id]);
$sessionStats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent activity
$stmt = $db->prepare("
    SELECT * FROM user_activity_log 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$currentUser->user_id]);
$recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to get user avatar
function getUserAvatar($user) {
    if (!empty($user['photo']) && file_exists(ROOT_PATH . '/uploads/' . $user['photo'])) {
        return BASE_URL . '/uploads/' . $user['photo'];
    }
    
    // Return random default avatar (1 or 2)
    $randomNum = rand(1, 2);
    return BASE_URL . '/uploads/users/avatar-default-' . $randomNum . '.jpg';
}

// Helper function to get user cover
function getUserCover($userId) {
    $coverPath = ROOT_PATH . '/uploads/users/cover-' . $userId . '.jpg';
    if (file_exists($coverPath)) {
        return BASE_URL . '/uploads/users/cover-' . $userId . '.jpg';
    }
    
    // Return random default cover
    $randomNum = rand(1, 2);
    return BASE_URL . '/uploads/users/cover-default-' . $randomNum . '.jpg';
}
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
                            <i class="bi-person-circle me-2"></i> My Profile
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-no-gutter">
                                <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">My Profile</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-sm-auto">
                        <a href="<?= url('admin/profile-settings') ?>" class="btn btn-primary">
                            <i class="bi-pencil-square me-1"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
            <!-- End Page Header -->

            <div class="row justify-content-lg-center">
                <div class="col-lg-10">
                    <!-- Profile Cover -->
                    <div class="profile-cover">
                        <div class="profile-cover-img-wrapper">
                            <img id="profileCoverImg" class="profile-cover-img" 
                                 src="<?= getUserCover($currentUser->user_id) ?>" 
                                 alt="Cover Image">
                            
                            <?php if ($isSuperAdmin || $currentUser->user_id == $currentUser->user_id): ?>
                            <!-- Custom File Cover -->
                            <div class="profile-cover-content profile-cover-uploader p-3">
                                <input type="file" class="js-file-attach profile-cover-uploader-input" 
                                       id="profileCoverUploader" 
                                       accept="image/jpeg,image/png,image/jpg"
                                       data-hs-file-attach-options='{
                                           "textTarget": "#profileCoverImg",
                                           "mode": "image",
                                           "targetAttr": "src",
                                           "allowTypes": [".png", ".jpeg", ".jpg"]
                                       }'>
                                <label class="profile-cover-uploader-label btn btn-sm btn-white" for="profileCoverUploader">
                                    <i class="bi-camera-fill me-1"></i>
                                    <span class="d-none d-sm-inline-block">Upload cover</span>
                                </label>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- End Profile Cover -->

                    <!-- Profile Header -->
                    <div class="text-center mb-5">
                        <!-- Avatar -->
                        <label class="avatar avatar-xxl avatar-circle profile-cover-avatar">
                            <?php if (!empty($userDetails['photo']) && file_exists(ROOT_PATH . '/uploads/' . $userDetails['photo'])): ?>
                                <img class="avatar-img" src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($userDetails['photo']) ?>" 
                                     alt="<?= htmlspecialchars($userDetails['firstname']) ?>">
                            <?php else: ?>
                                <div class="avatar avatar-xxl avatar-soft-primary avatar-circle">
                                    <span class="avatar-initials display-4">
                                        <?= strtoupper(substr($userDetails['firstname'] ?? '', 0, 1) . substr($userDetails['lastname'] ?? '', 0, 1)) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </label>
                        <!-- End Avatar -->

                        <h1 class="page-header-title">
                            <?= htmlspecialchars($userDetails['firstname'] . ' ' . $userDetails['lastname']) ?>
                            <?php if (!empty($userDetails['is_super_admin'])): ?>
                                <i class="bi-shield-check-fill fs-2 text-primary" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Super Administrator"></i>
                            <?php elseif ($userDetails['role_name'] == 'President'): ?>
                                <i class="bi-patch-check-fill fs-2 text-warning" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="CEP President"></i>
                            <?php endif; ?>
                        </h1>

                        <!-- User Info List -->
                        <ul class="list-inline list-px-2">
                            <li class="list-inline-item">
                                <i class="bi-briefcase me-1"></i>
                                <span><?= htmlspecialchars($userDetails['role_name'] ?? 'Member') ?></span>
                            </li>
                            <?php if (!empty($userDetails['member_id'])): ?>
                                <li class="list-inline-item">
                                    <i class="bi-person-badge me-1"></i>
                                    <span>Member #<?= htmlspecialchars($userDetails['membership_number'] ?? 'N/A') ?></span>
                                </li>
                            <?php endif; ?>
                            <li class="list-inline-item">
                                <i class="bi-calendar-week me-1"></i>
                                <span>Joined <?= date('F Y', strtotime($userDetails['created_at'] ?? 'now')) ?></span>
                            </li>
                        </ul>
                        <!-- End User Info List -->
                    </div>
                    <!-- End Profile Header -->

                    <!-- Nav -->
                    <div class="js-nav-scroller hs-nav-scroller-horizontal mb-5">
                        <ul class="nav nav-tabs align-items-center" id="profileTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="profile-overview-tab" href="#profile-overview" 
                                   data-bs-toggle="tab" role="tab">Overview</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-activity-tab" href="#profile-activity" 
                                   data-bs-toggle="tab" role="tab">Activity</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-security-tab" href="#profile-security" 
                                   data-bs-toggle="tab" role="tab">Security</a>
                            </li>
                            <li class="nav-item ms-auto">
                                <a class="btn btn-white btn-sm" href="<?= url('admin/profile-settings') ?>">
                                    <i class="bi-gear me-1"></i> Settings
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!-- End Nav -->

                    <!-- Tab Content -->
                    <div class="tab-content" id="profileTabContent">
                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="profile-overview" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-4">
                                    <!-- Profile Info Card -->
                                    <div class="card mb-3 mb-lg-5">
                                        <div class="card-header">
                                            <h4 class="card-header-title">Profile Information</h4>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled list-py-2 text-dark mb-0">
                                                <li class="pb-0">
                                                    <span class="card-subtitle">About</span>
                                                </li>
                                                <li>
                                                    <i class="bi-person dropdown-item-icon"></i> 
                                                    <?= htmlspecialchars($userDetails['firstname'] . ' ' . $userDetails['lastname']) ?>
                                                </li>
                                                <li>
                                                    <i class="bi-briefcase dropdown-item-icon"></i> 
                                                    <?= htmlspecialchars($userDetails['role_name'] ?? 'No role assigned') ?>
                                                </li>

                                                <li class="pt-4 pb-0">
                                                    <span class="card-subtitle">Contact</span>
                                                </li>
                                                <li>
                                                    <i class="bi-envelope dropdown-item-icon"></i> 
                                                    <?= htmlspecialchars($userDetails['email']) ?>
                                                </li>
                                                <?php if (!empty($userDetails['phone'])): ?>
                                                <li>
                                                    <i class="bi-telephone dropdown-item-icon"></i> 
                                                    <?= htmlspecialchars($userDetails['phone']) ?>
                                                </li>
                                                <?php endif; ?>

                                                <?php if (!empty($userDetails['member_id'])): ?>
                                                <li class="pt-4 pb-0">
                                                    <span class="card-subtitle">Member Details</span>
                                                </li>
                                                <li>
                                                    <i class="bi-calendar dropdown-item-icon"></i> 
                                                    Joined CEP: <?= $userDetails['year_joined_cep'] ?? 'N/A' ?>
                                                </li>
                                                <li>
                                                    <i class="bi-church dropdown-item-icon"></i> 
                                                    Church: <?= htmlspecialchars($userDetails['church_name'] ?? 'Not specified') ?>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- End Profile Info Card -->

                                    <!-- Session Info Card -->
                                    <div class="card card-lg mb-3 mb-lg-5">
                                        <div class="card-body text-center">
                                            <div class="mb-4">
                                                <img class="avatar avatar-xl avatar-4x3" 
                                                     src="<?= BASE_URL ?>/uploads/users/avatar-default-1.jpg" 
                                                     alt="Session Info"
                                                     onerror="this.src='<?= BASE_URL ?>/uploads/users/avatar-default-1.jpg';this.onerror=''">
                                            </div>
                                            <div class="mb-3">
                                                <h3>Current Session</h3>
                                                <p class="mb-1">
                                                    <span class="badge <?= ($currentUser->session_type ?? 'day') == 'day' ? 'bg-warning text-dark' : 'bg-primary' ?> fs-6 p-2">
                                                        <?= ($currentUser->session_type ?? 'day') == 'day' ? '☀️ Day CEP' : '🌙 Weekend CEP' ?>
                                                    </span>
                                                </p>
                                                <p class="text-muted small">
                                                    Last active: 
                                                    <?php 
                                                    $lastActive = $sessionStats['last_session'] ?? $userDetails['last_login'] ?? null;
                                                    if ($lastActive) {
                                                        echo date('M d, Y H:i', strtotime($lastActive));
                                                    } else {
                                                        echo 'Never';
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Session Info Card -->
                                </div>

                                <div class="col-lg-8">
                                    <!-- Activity Stream Card -->
                                    <div class="card mb-3 mb-lg-5">
                                        <div class="card-header card-header-content-between">
                                            <h4 class="card-header-title">Recent Activity</h4>
                                            <a class="btn btn-link" href="#profile-activity" 
                                               onclick="document.getElementById('profile-activity-tab').click()">
                                                View all <i class="bi-chevron-right"></i>
                                            </a>
                                        </div>
                                        <div class="card-body card-body-height">
                                            <?php if (empty($recentActivities)): ?>
                                                <div class="text-center py-5">
                                                    <img class="avatar avatar-xxl mb-3" 
                                                         src="<?= BASE_URL ?>/uploads/users/avatar-default-1.jpg" 
                                                         alt="No activity"
                                                         onerror="this.src='<?= BASE_URL ?>/uploads/users/avatar-default-1.jpg';this.onerror=''">
                                                    <p class="card-text text-muted">No recent activity</p>
                                                </div>
                                            <?php else: ?>
                                                <!-- Step Timeline -->
                                                <ul class="step step-icon-sm">
                                                    <?php foreach ($recentActivities as $activity): ?>
                                                    <li class="step-item">
                                                        <div class="step-content-wrapper">
                                                            <span class="step-icon step-icon-soft-<?= getActivityColor($activity['action']) ?>">
                                                                <?= getActivityIcon($activity['action']) ?>
                                                            </span>
                                                            <div class="step-content">
                                                                <h5 class="mb-1">
                                                                    <?= htmlspecialchars(ucfirst($activity['action'])) ?>
                                                                    <?php if (!empty($activity['module'])): ?>
                                                                        <span class="badge bg-soft-secondary text-secondary ms-2">
                                                                            <?= htmlspecialchars($activity['module']) ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </h5>
                                                                <p class="fs-5 mb-1">
                                                                    <?= htmlspecialchars($activity['description'] ?? 'No description') ?>
                                                                </p>
                                                                <span class="small text-muted text-uppercase">
                                                                    <?= time_elapsed_string($activity['created_at']) ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <!-- End Step Timeline -->
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- End Activity Stream Card -->
                                </div>
                            </div>
                        </div>
                        <!-- End Overview Tab -->

                        <!-- Activity Tab -->
                        <div class="tab-pane fade" id="profile-activity" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-header-title">Activity History</h4>
                                </div>
                                <div class="card-body">
                                    <div id="fullActivityLog">
                                        <!-- Will be loaded via AJAX -->
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary mb-3"></div>
                                            <p>Loading activity log...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Activity Tab -->

                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="profile-security" role="tabpanel">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h4 class="card-header-title">Security Settings</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Password Change Form -->
                                    <form id="changePasswordForm" class="mb-5">
                                        <h5 class="mb-3">Change Password</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Current Password</label>
                                                <input type="password" class="form-control" id="currentPassword" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">New Password</label>
                                                <input type="password" class="form-control" id="newPassword" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Confirm New Password</label>
                                                <input type="password" class="form-control" id="confirmPassword" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Update Password</button>
                                    </form>

                                    <!-- Sessions Table -->
                                    <h5 class="mb-3">Active Sessions</h5>
                                    <div class="table-responsive">
                                        <table class="table table-thead-bordered table-nowrap table-align-middle">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Device</th>
                                                    <th>Location</th>
                                                    <th>Last Active</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="sessionsList">
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">
                                                        <div class="spinner-border text-primary"></div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Security Tab -->
                    </div>
                    <!-- End Tab Content -->
                </div>
            </div>
        </div>

        <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
    </main>

    <?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

    <script>
    (function() {
        'use strict';

        // Load full activity log
        async function loadFullActivity() {
            const container = document.getElementById('fullActivityLog');
            
            try {
                const res = await fetch(`${BASE_URL}/api/users?action=activity&user_id=<?= $currentUser->user_id ?>`, {
                    credentials: 'include'
                });
                
                if (!res.ok) {
                    throw new Error('API endpoint not available');
                }
                
                const data = await res.json();
                
                if (data.success && data.data && data.data.length > 0) {
                    renderFullActivity(data.data);
                } else {
                    container.innerHTML = `
                        <div class="text-center py-5">
                            <img class="avatar avatar-xxl mb-3" 
                                 src="<?= BASE_URL ?>/uploads/users/avatar-default-1.jpg" 
                                 alt="No activity"
                                 onerror="this.src='<?= BASE_URL ?>/uploads/users/avatar-default-1.jpg';this.onerror=''">
                            <p class="text-muted">No activity found</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Failed to load activity:', error);
                container.innerHTML = `
                    <div class="text-center py-5">
                        <p class="text-muted">Unable to load activity log</p>
                        <small class="text-muted">Please try again later</small>
                    </div>
                `;
            }
        }

        function renderFullActivity(activities) {
            const container = document.getElementById('fullActivityLog');
            
            let html = '<div class="table-responsive"><table class="table table-thead-bordered table-nowrap">';
            html += '<thead class="thead-light"><tr><th>Date</th><th>Action</th><th>Module</th><th>Description</th></tr></thead><tbody>';
            
            activities.forEach(a => {
                html += `
                    <tr>
                        <td><small>${new Date(a.created_at).toLocaleString()}</small></td>
                        <td><span class="badge bg-soft-${getActivityColorClass(a.action)}">${escapeHtml(a.action)}</span></td>
                        <td>${escapeHtml(a.module || '—')}</td>
                        <td>${escapeHtml(a.description || '—')}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        function getActivityColorClass(action) {
            const colors = {
                'login': 'success',
                'logout': 'secondary',
                'create': 'primary',
                'update': 'info',
                'delete': 'danger',
                'status_change': 'warning'
            };
            return colors[action] || 'dark';
        }

        // Load active sessions
        async function loadSessions() {
            const tbody = document.getElementById('sessionsList');
            
            try {
                const res = await fetch(`${BASE_URL}/api/users?action=sessions&user_id=<?= $currentUser->user_id ?>`, {
                    credentials: 'include'
                });
                
                if (!res.ok) {
                    throw new Error('API endpoint not available');
                }
                
                const data = await res.json();
                
                if (data.success && data.data && data.data.length > 0) {
                    renderSessions(data.data);
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4">No active sessions</td></tr>';
                }
            } catch (error) {
                console.error('Failed to load sessions:', error);
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Unable to load sessions</td></tr>';
            }
        }

        function renderSessions(sessions) {
            const tbody = document.getElementById('sessionsList');
            let html = '';
            
            sessions.forEach(s => {
                const isCurrent = s.is_current ? '<span class="badge bg-success ms-2">Current</span>' : '';
                html += `
                    <tr>
                        <td>
                            <i class="bi-${getDeviceIcon(s.user_agent)} me-2"></i>
                            ${escapeHtml(getDeviceName(s.user_agent))} ${isCurrent}
                        </td>
                        <td>${escapeHtml(s.location || 'Unknown')}</td>
                        <td><small>${timeElapsed(s.last_activity)}</small></td>
                        <td class="text-end">
                            ${!s.is_current ? 
                                `<button class="btn btn-xs btn-outline-danger" onclick="revokeSession(${s.id})">
                                    <i class="bi-x-circle me-1"></i> Revoke
                                </button>` : ''}
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        // Helper functions
        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function timeElapsed(timestamp) {
            if (!timestamp) return 'Never';
            const diff = Math.floor((new Date() - new Date(timestamp)) / 1000);
            
            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            return new Date(timestamp).toLocaleDateString();
        }

        function getDeviceIcon(userAgent) {
            if (!userAgent) return 'phone';
            if (userAgent.includes('Windows')) return 'windows';
            if (userAgent.includes('Mac')) return 'apple';
            if (userAgent.includes('Linux')) return 'ubuntu';
            if (userAgent.includes('Android')) return 'android2';
            if (userAgent.includes('iPhone')) return 'apple';
            return 'phone';
        }

        function getDeviceName(userAgent) {
            if (!userAgent) return 'Unknown Device';
            if (userAgent.includes('Windows')) return 'Windows PC';
            if (userAgent.includes('Mac')) return 'Mac';
            if (userAgent.includes('Linux')) return 'Linux';
            if (userAgent.includes('Android')) return 'Android Device';
            if (userAgent.includes('iPhone')) return 'iPhone';
            return 'Unknown Device';
        }

        // Password change form
        document.getElementById('changePasswordForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const current = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;
            
            if (newPass !== confirm) {
                Swal.fire('Error', 'New passwords do not match', 'error');
                return;
            }
            
            if (newPass.length < 8) {
                Swal.fire('Error', 'Password must be at least 8 characters', 'error');
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
                    document.getElementById('changePasswordForm').reset();
                } else {
                    Swal.fire('Error', data.message || 'Failed to update password', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Network error', 'error');
            }
        });

        window.revokeSession = async function(sessionId) {
            const result = await Swal.fire({
                title: 'Revoke Session?',
                text: 'This will log out this device',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Revoke'
            });
            
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`${BASE_URL}/api/users?action=revoke_session`, {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ session_id: sessionId })
                    });
                    
                    const data = await res.json();
                    
                    if (data.success) {
                        Swal.fire('Revoked', 'Session has been revoked', 'success');
                        loadSessions();
                    } else {
                        Swal.fire('Error', data.message || 'Failed to revoke', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Network error', 'error');
                }
            }
        };

        // Load data when tabs are clicked
        document.getElementById('profile-activity-tab')?.addEventListener('shown.bs.tab', function() {
            loadFullActivity();
        });

        document.getElementById('profile-security-tab')?.addEventListener('shown.bs.tab', function() {
            loadSessions();
        });
    })();
    </script>

    <?php
    // Helper functions
    function getActivityColor($action) {
        $colors = [
            'login' => 'success',
            'logout' => 'secondary',
            'create' => 'primary',
            'update' => 'info',
            'delete' => 'danger',
            'status_change' => 'warning'
        ];
        return $colors[$action] ?? 'dark';
    }

    function getActivityIcon($action) {
        $icons = [
            'login' => '<i class="bi-box-arrow-in-right"></i>',
            'logout' => '<i class="bi-box-arrow-right"></i>',
            'create' => '<i class="bi-plus-circle"></i>',
            'update' => '<i class="bi-pencil"></i>',
            'delete' => '<i class="bi-trash"></i>',
            'status_change' => '<i class="bi-arrow-repeat"></i>'
        ];
        return $icons[$action] ?? '<i class="bi-record-circle"></i>';
    }
    ?>
</body>
</html>