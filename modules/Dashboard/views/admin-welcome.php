<?php
/**
 * Admin Welcome Page (for non-super admins)
 * File: modules/Dashboard/views/admin-welcome.php
 */

require_once ROOT_PATH . '/helpers/AuthMiddleware.php';
$auth = new AuthMiddleware();
$currentUser = $auth->requireAuth(['dashboard.view']);

$pageTitle = 'Welcome';
?>

<?php include LAYOUTS_PATH . '/admin-header.php'; ?>

<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<main id="content" role="main" class="main">
    <div class="content container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <img class="img-fluid mb-4" src="<?= BASE_URL ?>/dashboard-assets/svg/illustrations/oc-hi-five.svg" alt="Welcome" style="max-width: 20rem;">
                    <h1 class="display-4 mb-3">Welcome, <?= htmlspecialchars($currentUser->firstname) ?>!</h1>
                    <p class="lead">You have successfully logged in to the CEP UoK Leadership Portal.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi-person-circle display-1 text-primary mb-3"></i>
                                <h4>Your Profile</h4>
                                <p class="text-muted">View and update your personal information</p>
                                <a href="<?= BASE_URL ?>/admin/profile" class="btn btn-outline-primary">Go to Profile</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi-people display-1 text-primary mb-3"></i>
                                <h4>Manage Members</h4>
                                <p class="text-muted">View and manage CEP members</p>
                                <a href="<?= BASE_URL ?>/admin/membership-management" class="btn btn-outline-primary">View Members</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi-calendar-event display-1 text-primary mb-3"></i>
                                <h4>Events & Programs</h4>
                                <p class="text-muted">Manage CEP events and programs</p>
                                <a href="<?= BASE_URL ?>/admin/programs-management" class="btn btn-outline-primary">View Events</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi-images display-1 text-primary mb-3"></i>
                                <h4>Gallery</h4>
                                <p class="text-muted">Manage photo and video gallery</p>
                                <a href="<?= BASE_URL ?>/admin/gallery-management" class="btn btn-outline-primary">View Gallery</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>