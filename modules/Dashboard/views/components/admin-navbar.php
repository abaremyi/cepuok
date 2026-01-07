<?php
// modules/Dashboard/views/components/admin-navbar.php
?>
<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <button class="btn btn-outline-primary d-lg-none me-2" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-dark">Welcome, <?php echo htmlspecialchars($decoded->firstname . ' ' . $decoded->lastname); ?></h5>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="dropdown me-3">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger rounded-pill">3</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Notifications</h6>
                        <a class="dropdown-item" href="#"><i class="fas fa-user-plus text-primary me-2"></i> New user registration</a>
                        <a class="dropdown-item" href="#"><i class="fas fa-exclamation-triangle text-warning me-2"></i> System maintenance</a>
                        <a class="dropdown-item" href="#"><i class="fas fa-check-circle text-success me-2"></i> Backup completed</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="#">View all</a>
                    </div>
                </div>
                
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <img src="<?php echo img_url($decoded->photo); ?>" 
                             class="user-avatar me-2" 
                             alt="Profile" 
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($decoded->firstname . '+' . $decoded->lastname); ?>&background=667eea&color=fff'">
                        <span><?php echo htmlspecialchars($decoded->firstname); ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Signed in as</h6>
                        <h6 class="dropdown-header fw-bold"><?php echo htmlspecialchars($decoded->role_name); ?></h6>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="loadContent('profile')">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                        <a class="dropdown-item" href="#" onclick="changePassword()">
                            <i class="fas fa-key me-2"></i> Change Password
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="#" onclick="logout()">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>