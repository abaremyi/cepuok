<?php
// modules/Dashboard/views/admin-dashboard.php
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . '/helpers/JWTHandler.php';

// Get token from cookie
$token = $_COOKIE['auth_token'] ?? '';

// Validate token
$jwtHandler = new JWTHandler();
$decoded = $token ? $jwtHandler->validateToken($token) : null;

if (!$decoded) {
    // If no valid token, redirect to login
    header("Location: " . url('login'));
    exit;
}

// Check admin access
$hasAdminAccess = $decoded->is_super_admin || 
                  in_array('dashboard.view', $decoded->permissions) ||
                  $decoded->role_id == 1 || 
                  $decoded->role_id == 2;

if (!$hasAdminAccess) {
    // Redirect based on role
    if ($decoded->role_id == 3) {
        header("Location: " . url('teacher'));
    } elseif ($decoded->role_id == 4) {
        header("Location: " . url('parent'));
    } elseif ($decoded->role_id == 5) {
        header("Location: " . url('student'));
    } else {
        header("Location: " . url('dashboard'));
    }
    exit;
}

// Include database connection
require_once $root_path . "/config/database.php";
$pdo = Database::getConnection();

// Get user's full name from database using the user_id from JWT
$fullName = "Administrator";
$userRole = "Admin";

try {
    $stmt = $pdo->prepare("SELECT u.firstname, u.lastname, r.name as role_name FROM users u 
                          LEFT JOIN roles r ON u.role_id = r.id 
                          WHERE u.id = ?");
    $stmt->execute([$decoded->user_id]);
    $userData = $stmt->fetch();
    
    if ($userData) {
        $fullName = trim($userData['firstname'] . ' ' . $userData['lastname']);
        $userRole = $userData['role_name'] ?? 'Admin';
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
}

// Get statistics from database
try {
    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'] ?? 0;
    
    // Total Students (users with role_id = 5)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role_id = 5");
    $totalStudents = $stmt->fetch()['total'] ?? 0;
    
    // Total Teachers (users with role_id = 3)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role_id = 3");
    $totalTeachers = $stmt->fetch()['total'] ?? 0;
    
    // Pending Messages (contact messages with status 'new')
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'");
    $pendingMessages = $stmt->fetch()['total'] ?? 0;
    
    // Total Gallery Images
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM gallery_images WHERE status = 'active'");
    $totalGalleryImages = $stmt->fetch()['total'] ?? 0;
    
    // Total Videos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM video_gallery WHERE status = 'active'");
    $totalVideos = $stmt->fetch()['total'] ?? 0;
    
    // Total News/Events
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM news_events WHERE status = 'published'");
    $totalNews = $stmt->fetch()['total'] ?? 0;
    
    // Recent Contact Messages (last 7 days)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recentContacts = $stmt->fetch()['total'] ?? 0;
    
    // Get recent activities
    $recentActivities = [];
    
    // Get recent user logins
    $stmt = $pdo->query("SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) as full_name, u.last_login, r.name as role_name 
                         FROM users u 
                         LEFT JOIN roles r ON u.role_id = r.id 
                         WHERE u.last_login IS NOT NULL 
                         ORDER BY u.last_login DESC 
                         LIMIT 5");
    $recentLogins = $stmt->fetchAll();
    
    foreach ($recentLogins as $login) {
        if ($login['last_login']) {
            $recentActivities[] = [
                'time' => $login['last_login'],
                'activity' => 'User login',
                'user' => !empty($login['full_name']) ? $login['full_name'] : 'User ' . $login['id'],
                'role' => $login['role_name'] ?? 'User',
                'status' => 'success'
            ];
        }
    }
    
    // Get recent news additions (last 5 days)
    $stmt = $pdo->query("SELECT title, author, created_at FROM news_events 
                         WHERE status = 'published' AND created_at >= DATE_SUB(NOW(), INTERVAL 5 DAY) 
                         ORDER BY created_at DESC 
                         LIMIT 3");
    $recentNews = $stmt->fetchAll();
    
    foreach ($recentNews as $news) {
        $recentActivities[] = [
            'time' => $news['created_at'],
            'activity' => 'News added: ' . (strlen($news['title']) > 30 ? substr($news['title'], 0, 30) . '...' : $news['title']),
            'user' => !empty($news['author']) ? $news['author'] : 'Admin',
            'role' => 'Content Manager',
            'status' => 'success'
        ];
    }
    
    // Get recent gallery uploads (last 5 days)
    $stmt = $pdo->query("SELECT title, created_at FROM gallery_images 
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 DAY) 
                         ORDER BY created_at DESC 
                         LIMIT 3");
    $recentGallery = $stmt->fetchAll();
    
    foreach ($recentGallery as $gallery) {
        $recentActivities[] = [
            'time' => $gallery['created_at'],
            'activity' => 'Gallery image uploaded: ' . (strlen($gallery['title']) > 30 ? substr($gallery['title'], 0, 30) . '...' : $gallery['title']),
            'user' => 'Media Manager',
            'role' => 'Media',
            'status' => 'success'
        ];
    }
    
    // Get recent contact messages (last 3 days)
    $stmt = $pdo->query("SELECT name, email, created_at FROM contact_messages 
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY) 
                         ORDER BY created_at DESC 
                         LIMIT 3");
    $recentContactsList = $stmt->fetchAll();
    
    foreach ($recentContactsList as $contact) {
        $recentActivities[] = [
            'time' => $contact['created_at'],
            'activity' => 'Contact form submitted',
            'user' => htmlspecialchars($contact['name']) . ' (' . htmlspecialchars($contact['email']) . ')',
            'role' => 'Visitor',
            'status' => 'new'
        ];
    }
    
    // Sort activities by time (most recent first)
    usort($recentActivities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
    // Get only the 6 most recent activities
    $recentActivities = array_slice($recentActivities, 0, 6);
    
} catch (Exception $e) {
    error_log("Error fetching dashboard data: " . $e->getMessage());
    // Set default values if there's an error
    $totalUsers = 0;
    $totalStudents = 0;
    $totalTeachers = 0;
    $pendingMessages = 0;
    $totalGalleryImages = 0;
    $totalVideos = 0;
    $totalNews = 0;
    $recentContacts = 0;
    $recentActivities = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- jQuery (needed for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Include admin styles -->
    <?php include_once 'components/admin-styles.php'; ?>
    
    <style>
        .dashboard-section {
            margin-bottom: 30px;
        }
        .dashboard-section h3 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .quick-action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 20px;
            border-radius: 12px;
            background: white;
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
            height: 100%;
            text-align: center;
            text-decoration: none;
            color: var(--dark-color);
        }
        .quick-action-btn:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        .quick-action-btn:hover i {
            color: white !important;
        }
        .quick-action-btn i {
            font-size: 32px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        .quick-action-btn h6 {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .quick-action-btn small {
            font-size: 12px;
            opacity: 0.8;
        }
        .activity-time {
            font-size: 12px;
            color: #6b7280;
        }
        .activity-user {
            font-weight: 500;
        }
        .activity-role {
            font-size: 11px;
            color: #9ca3af;
        }
        .stat-trend {
            font-size: 12px;
            display: block;
        }
        .stat-trend.up {
            color: var(--success-color);
        }
        .stat-trend.down {
            color: var(--danger-color);
        }
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .welcome-card h4 {
            margin-bottom: 10px;
        }
        .welcome-card p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        .welcome-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 32px;
            color: white;
        }
        .welcome-info {
            flex: 1;
        }
        .stat-card .count {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        .stat-card .label {
            color: #6b7280;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }
        .no-data i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .activity-status.success {
            background-color: var(--success-color);
        }
        .activity-status.new {
            background-color: var(--warning-color);
        }
        .stat-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 24px;
        }
        .stat-users { background: rgba(102, 126, 234, 0.1); color: var(--primary-color); }
        .stat-students { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
        .stat-teachers { background: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
        .stat-messages { background: rgba(59, 130, 246, 0.1); color: var(--info-color); }
        .stat-gallery { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
        .stat-videos { background: rgba(249, 115, 22, 0.1); color: #f97316; }
        .stat-news { background: rgba(20, 184, 166, 0.1); color: #14b8a6; }
        .stat-contacts { background: rgba(236, 72, 153, 0.1); color: #ec4899; }
    </style>
</head>
<body>
    <!-- Include admin sidebar -->
    <?php include_once 'components/admin-sidebar.php'; ?>
    
    <!-- Include admin navbar -->
    <?php include_once 'components/admin-navbar.php'; ?>

    <!-- Page Content -->
    <div id="contentArea" class="mt-4">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <div class="d-flex align-items-center">
                <div class="welcome-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="welcome-info">
                    <h4>Welcome back, <?= htmlspecialchars($fullName) ?>! 👋</h4>
                    <p>Here's what's happening with your school today. You are logged in as <strong><?= htmlspecialchars($userRole) ?></strong>.</p>
                    <small><i class="fas fa-clock me-1"></i> <?= date('l, F j, Y') ?> • <?= date('h:i A') ?></small>
                </div>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon-wrapper stat-users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="count"><?= number_format($totalUsers) ?></div>
                    <div class="label">Total Users</div>
                    <span class="stat-trend up">
                        <i class="fas fa-user-check me-1"></i>All system accounts
                    </span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon-wrapper stat-students">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="count"><?= number_format($totalStudents) ?></div>
                    <div class="label">Students</div>
                    <span class="stat-trend <?= $totalStudents > 0 ? 'up' : 'down' ?>">
                        <i class="fas <?= $totalStudents > 0 ? 'fa-user-graduate' : 'fa-user-slash' ?> me-1"></i>
                        <?= $totalStudents > 0 ? 'Active students' : 'No students yet' ?>
                    </span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon-wrapper stat-teachers">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="count"><?= number_format($totalTeachers) ?></div>
                    <div class="label">Teachers</div>
                    <span class="stat-trend <?= $totalTeachers > 0 ? 'up' : 'down' ?>">
                        <i class="fas <?= $totalTeachers > 0 ? 'fa-user-tie' : 'fa-user-times' ?> me-1"></i>
                        <?= $totalTeachers > 0 ? 'Teaching staff' : 'No teachers yet' ?>
                    </span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon-wrapper stat-messages">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="count"><?= number_format($pendingMessages) ?></div>
                    <div class="label">New Messages</div>
                    <span class="stat-trend <?= $pendingMessages > 0 ? 'down' : 'up' ?>">
                        <?php if ($pendingMessages > 0): ?>
                            <i class="fas fa-exclamation-circle me-1"></i>Requires attention
                        <?php else: ?>
                            <i class="fas fa-check-circle me-1"></i>All caught up
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Content Stats -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon-wrapper stat-gallery">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="count"><?= number_format($totalGalleryImages) ?></div>
                    <div class="label">Gallery Images</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon-wrapper stat-videos">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="count"><?= number_format($totalVideos) ?></div>
                    <div class="label">Videos</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon-wrapper stat-news">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="count"><?= number_format($totalNews) ?></div>
                    <div class="label">News & Events</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon-wrapper stat-contacts">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="count"><?= number_format($recentContacts) ?></div>
                    <div class="label">Recent Contacts (7d)</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Activity -->
        <div class="row g-4">
            <!-- Quick Actions -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Quick Actions</h5>
                        <span class="badge bg-primary">Most Used</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="<?= url('admin/users-management') ?>" class="quick-action-btn">
                                    <i class="fas fa-users"></i>
                                    <h6>Manage Users</h6>
                                    <small>Add/edit users</small>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?= url('admin/news-events') ?>" class="quick-action-btn">
                                    <i class="fas fa-newspaper"></i>
                                    <h6>Add News</h6>
                                    <small>Post updates</small>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?= url('admin/gallery') ?>" class="quick-action-btn">
                                    <i class="fas fa-images"></i>
                                    <h6>Manage Gallery</h6>
                                    <small>Photos & albums</small>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?= url('admin/video-gallery') ?>" class="quick-action-btn">
                                    <i class="fas fa-video"></i>
                                    <h6>Video Gallery</h6>
                                    <small>Upload videos</small>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?= url('admin/hero-sliders') ?>" class="quick-action-btn">
                                    <i class="fas fa-sliders-h"></i>
                                    <h6>Home Sliders</h6>
                                    <small>Banner images</small>
                                </a>
                            </div>
                            <div class="col-6">
                                <?php if ($pendingMessages > 0): ?>
                                    <a href="<?= url('admin/contact-messages') ?>" class="quick-action-btn" style="border-color: #f59e0b;">
                                        <i class="fas fa-inbox"></i>
                                        <h6>Messages</h6>
                                        <small><?= $pendingMessages ?> pending</small>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('admin/contact-messages') ?>" class="quick-action-btn">
                                        <i class="fas fa-inbox"></i>
                                        <h6>Messages</h6>
                                        <small>View inquiries</small>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Activity</h5>
                        <small class="text-muted">Last 7 days</small>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($recentActivities)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentActivities as $index => $activity): ?>
                                    <div class="activity-item px-4">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="activity-status <?= $activity['status'] ?>"></span>
                                                    <div class="activity-user">
                                                        <?= htmlspecialchars($activity['activity']) ?>
                                                    </div>
                                                </div>
                                                <div class="activity-time mb-1">
                                                    <i class="far fa-clock me-1"></i>
                                                    <?php
                                                    $time = strtotime($activity['time']);
                                                    $now = time();
                                                    $diff = $now - $time;
                                                    
                                                    if ($diff < 60) {
                                                        echo 'Just now';
                                                    } elseif ($diff < 3600) {
                                                        echo floor($diff / 60) . ' minutes ago';
                                                    } elseif ($diff < 86400) {
                                                        echo floor($diff / 3600) . ' hours ago';
                                                    } elseif ($diff < 604800) {
                                                        echo floor($diff / 86400) . ' days ago';
                                                    } else {
                                                        echo date('M d, Y', $time);
                                                    }
                                                    ?>
                                                </div>
                                                <div class="activity-role">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?= htmlspecialchars($activity['user']) ?>
                                                    <?php if (!empty($activity['role'])): ?>
                                                        <span class="ms-2">
                                                            <i class="fas fa-tag me-1"></i>
                                                            <?= htmlspecialchars($activity['role']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-history"></i>
                                <p class="mb-2">No recent activities found</p>
                                <small class="text-muted">Activities will appear here as they happen</small>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($pendingMessages > 0): ?>
                            <div class="alert alert-warning m-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                                    <div>
                                        <strong>Attention needed!</strong>
                                        <p class="mb-0">You have <?= $pendingMessages ?> unread message(s) from website visitors.</p>
                                        <a href="<?= url('admin/contact-messages') ?>" class="btn btn-sm btn-warning mt-2">
                                            <i class="fas fa-inbox me-1"></i> View Messages
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Section -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Website Content Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <div class="stat-icon-wrapper stat-gallery mx-auto mb-3">
                                        <i class="fas fa-images"></i>
                                    </div>
                                    <h4 class="mb-1"><?= number_format($totalGalleryImages) ?></h4>
                                    <p class="text-muted mb-0">Gallery Images</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <div class="stat-icon-wrapper stat-videos mx-auto mb-3">
                                        <i class="fas fa-video"></i>
                                    </div>
                                    <h4 class="mb-1"><?= number_format($totalVideos) ?></h4>
                                    <p class="text-muted mb-0">Videos</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <div class="stat-icon-wrapper stat-news mx-auto mb-3">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                    <h4 class="mb-1"><?= number_format($totalNews) ?></h4>
                                    <p class="text-muted mb-0">News/Events</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <div class="stat-icon-wrapper stat-contacts mx-auto mb-3">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                    <h4 class="mb-1"><?= number_format($recentContacts) ?></h4>
                                    <p class="text-muted mb-0">Recent Contacts</p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($totalUsers == 0): ?>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Getting Started:</strong> You have no users in the system yet. 
                                <a href="<?= url('admin/users-management') ?>" class="alert-link">Add your first user</a> 
                                to start managing the website.
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($totalGalleryImages == 0): ?>
                            <div class="alert alert-secondary mt-2">
                                <i class="fas fa-images me-2"></i>
                                <strong>Gallery Empty:</strong> Add images to showcase your school. 
                                <a href="<?= url('admin/gallery') ?>" class="alert-link">Upload gallery photos</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Include admin scripts -->
    <?php include_once 'components/admin-scripts.php'; ?>
    
    <script>
        // Update time in welcome card
        function updateCurrentTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            const timeString = now.toLocaleDateString('en-US', options);
            document.querySelector('.welcome-card small').innerHTML = 
                `<i class="fas fa-clock me-1"></i> ${timeString}`;
        }
        
        // Update time every minute
        updateCurrentTime();
        setInterval(updateCurrentTime, 60000);
        
        // Auto-refresh dashboard every 5 minutes
        setInterval(function() {
            // You can implement AJAX refresh here if needed
            console.log('Dashboard auto-refresh at: ' + new Date().toLocaleTimeString());
        }, 300000);
        
        // Initialize tooltips
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Add pulse animation to pending messages alert
            <?php if ($pendingMessages > 0): ?>
            setInterval(function() {
                $('.alert-warning').toggleClass('border border-warning');
            }, 1000);
            <?php endif; ?>
        });
    </script>
</body>
</html>