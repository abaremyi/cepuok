<?php
// CEPUOK/index.php - Main Router

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include paths configuration
require_once 'config/paths.php';

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Remove the base path if exists
$base_path = str_replace('/index.php', '', $script_name);
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Remove query string and trailing slash
$parsed_url = parse_url($request_uri);
$path  = isset($parsed_url['path'])  ? rtrim($parsed_url['path'], '/') : '';
$query = isset($parsed_url['query']) ? $parsed_url['query']            : '';

// ─────────────────────────────────────────────────────────────────────────────
// ROUTE TABLE
// ─────────────────────────────────────────────────────────────────────────────
$routes = [

    // ── Public / General ─────────────────────────────────────────────────────
    ''                         => 'modules/General/views/index.php',
    '/'                        => 'modules/General/views/index.php',
    '/home'                    => 'modules/General/views/index.php',
    '/about'                   => 'modules/General/views/about-cep.php',
    '/about-cep'               => 'modules/General/views/about-cep.php',
    '/history'                 => 'modules/General/views/history.php',
    '/leadership'              => 'modules/General/views/leadership.php',
    '/contact'                 => 'modules/General/views/contact.php',
    '/departments'             => 'modules/General/views/departments.php',
    '/gallery'                 => 'modules/General/views/gallery-photo.php',
    '/gallery-photo'           => 'modules/General/views/gallery-photo.php',
    '/gallery-video'           => 'modules/General/views/gallery-video.php',
    '/leadership-team'         => 'modules/General/views/leadership-team.php',
    '/local-church'            => 'modules/General/views/local-church.php',
    '/membership'              => 'modules/General/views/membership.php',
    '/news'                    => 'modules/General/views/news.php',
    '/programs'                => 'modules/General/views/programs.php',
    '/projects'                => 'modules/General/views/projects.php',
    '/project-details'         => 'modules/General/views/project-details.php',
    '/products'                => 'modules/General/views/products.php',
    '/team'                    => 'modules/General/views/team.php',
    '/team-single'             => 'modules/General/views/team-single.php',
    '/academic-facilities'     => 'modules/General/views/academic-facilities.php',
    '/sports-facilities'       => 'modules/General/views/sports-facilities.php',
    '/services-facilities'     => 'modules/General/views/services-facilities.php',
    '/services'                => 'modules/General/views/services.php',
    '/service-details'         => 'modules/General/views/service-details.php',

    // ── Static endpoints ──────────────────────────────────────────────────────
    '/static/get_projects'         => 'modules/General/static/get_projects.php',
    '/static/get_project_gallery'  => 'modules/General/static/get_project_gallery.php',
    '/static/get_related_projects' => 'modules/General/static/get_related_projects.php',

    // ── Public / General API ──────────────────────────────────────────────────
    '/api/leadership'    => 'modules/Leadership/api/leadershipApi.php',
    '/api/administration'=> 'modules/Administration/api/administrationApi.php',
    '/api/admission'     => 'modules/Admission/api/admissionApi.php',
    '/api/contact'       => 'modules/Contact/api/contactApi.php',
    '/api/facilities'    => 'modules/Facilities/api/facilitiesApi.php',
    '/api/gallery'       => 'modules/Gallery/api/galleryApi.php',
    '/api/hero'          => 'modules/Hero/api/heroApi.php',
    '/api/membership'    => 'modules/Membership/api/membershipApi.php',
    '/api/news'          => 'modules/News/api/newsApi.php',
    '/api/programs'      => 'modules/Programs/api/programsApi.php',
    '/api/testimonials'  => 'modules/Testimonials/api/testimonialsApi.php',
    '/api/videos'        => 'modules/Videos/api/videoApi.php',
    '/api/departments'   => 'modules/Departments/api/departmentsApi.php',

    // ── Domain-specific APIs ──────────────────────────────────────────────────
    '/api/supporters'    => 'modules/Supporters/api/supportersApi.php',
    '/api/families'      => 'modules/Families/api/familiesApi.php',   
    '/api/finance'       => 'modules/Finance/api/financeApi.php',
    '/api/projects'      => 'modules/Projects/api/projectsApi.php',
    '/api/choir'         => 'modules/Choir/api/choirApi.php',           
    '/api/reports'       => 'modules/Reports/api/reportsApi.php',       

    // ── Authentication ────────────────────────────────────────────────────────
    '/forgot-password'   => 'modules/Authentication/views/forgot-password.php',
    '/login'             => 'modules/Authentication/views/login.php',
    '/logout'            => 'modules/Authentication/views/logout.php',
    '/register'          => 'modules/Authentication/views/register.php',
    '/reset-password'    => 'modules/Authentication/views/reset-password.php',
    '/verify-email'      => 'modules/Authentication/views/verifyEmail.php',

    // ── Authentication API ────────────────────────────────────────────────────
    '/api/auth'          => 'modules/Authentication/api/authApi.php',
    '/api/users'         => 'modules/Authentication/api/userApi.php',

    // ── Admin — Dashboard ─────────────────────────────────────────────────────
    '/admin/dashboard'   => 'modules/Dashboard/views/admin-dashboard.php',
    '/admin/welcome'     => 'modules/Dashboard/views/admin-welcome.php',
    '/admin/profile'     => 'modules/Dashboard/views/profile.php',
    '/admin/settings'    => 'modules/Dashboard/views/settings.php',

    // ── Admin — Dashboard API ─────────────────────────────────────────────────
    '/api/dashboard'     => 'modules/Dashboard/api/dashboardApi.php',

    // ── Admin — User Management ───────────────────────────────────────────────
    '/admin/users-management'             => 'modules/Dashboard/views/users-management.php',
    '/admin/users-add-user'               => 'modules/Dashboard/views/users-add-user.php',
    '/admin/users-view'                   => 'modules/Dashboard/views/users-view.php',
    '/admin/roles-permissions-management' => 'modules/Dashboard/views/roles-permissions-management.php',

    // ── Admin — Membership Management ────────────────────────────────────────
    '/admin/membership-management'   => 'modules/Dashboard/views/membership-management.php',
    '/admin/membership-applications' => 'modules/Dashboard/views/membership-applications.php',
    '/admin/member-add'              => 'modules/Dashboard/views/member-add.php',
    '/admin/member-edit'             => 'modules/Dashboard/views/member-edit.php',
    '/admin/member-view'             => 'modules/Dashboard/views/member-view.php',
    '/admin/member-families'         => 'modules/Dashboard/views/member-families.php',     // UPDATED (card grid view)
    '/admin/family-detail'           => 'modules/Dashboard/views/family-detail.php',        // NEW

    // ── Admin — Supporters ────────────────────────────────────────────────────
    '/admin/supporters-management'   => 'modules/Dashboard/views/supporters-management.php', // UPDATED
    '/admin/supporter-detail'        => 'modules/Dashboard/views/supporter-detail.php',       // NEW

    // ── Admin — Finance ───────────────────────────────────────────────────────
    // Legacy finance pages (keep for backward compatibility)
    '/admin/finance-management'  => 'modules/Dashboard/views/finance-management.php',
    '/admin/finance-transactions'=> 'modules/Dashboard/views/finance-transactions.php',

    // New dedicated finance pages
    '/admin/finance-dashboard'      => 'modules/Dashboard/views/finance-dashboard.php',      // NEW
    '/admin/finance-revenue'        => 'modules/Dashboard/views/finance-revenue.php',         // NEW
    '/admin/finance-budget'         => 'modules/Dashboard/views/finance-budget.php',          // NEW
    '/admin/finance-fund-requests'  => 'modules/Dashboard/views/finance-fund-requests.php',  // NEW
    '/admin/finance-disbursements'  => 'modules/Dashboard/views/finance-disbursements.php',  // NEW
    '/admin/finance-reports'        => 'modules/Dashboard/views/finance-reports.php',         // UPDATED

    // ── Admin — Content Management ────────────────────────────────────────────
    '/admin/departments-management'   => 'modules/Dashboard/views/departments-management.php',
    '/admin/gallery-management'       => 'modules/Dashboard/views/gallery-management.php',
    '/admin/video-gallery-management' => 'modules/Dashboard/views/video-gallery-management.php',
    '/admin/leadership-management'    => 'modules/Dashboard/views/leadership-management.php',
    '/admin/news-events-management'   => 'modules/Dashboard/views/news-events-management.php',
    '/admin/programs-management'      => 'modules/Dashboard/views/programs-management.php',
    '/admin/testimonials-management'  => 'modules/Dashboard/views/testimonials-management.php',
    '/admin/messages-management'      => 'modules/Dashboard/views/messages-management.php',

    // ── Admin — Session-specific ──────────────────────────────────────────────
    '/admin/session/members'    => 'modules/Dashboard/views/session-members.php',
    '/admin/session/activities' => 'modules/Dashboard/views/session-activities.php',
    '/admin/session/reports'    => 'modules/Dashboard/views/session-reports.php',

    // ── Admin — Projects ──────────────────────────────────────────────────────
    '/admin/projects-management' => 'modules/Dashboard/views/projects-management.php',  // UPDATED (full Kanban)
    '/admin/project-add'         => 'modules/Dashboard/views/project-add.php',
    '/admin/project-edit'        => 'modules/Dashboard/views/project-edit.php',

    // ── Admin — Choir ─────────────────────────────────────────────────────────
    // Legacy pages
    '/admin/choir-management'    => 'modules/Dashboard/views/choir-management.php',
    '/admin/choir-schedule'      => 'modules/Dashboard/views/choir-schedule.php',

    // New dedicated choir pages
    '/admin/choir-members'       => 'modules/Dashboard/views/choir-members.php',    // UPDATED (tabular + stats)
    '/admin/choir-songs'         => 'modules/Dashboard/views/choir-songs.php',       // NEW
    '/admin/choir-attendance'    => 'modules/Dashboard/views/choir-attendance.php',  // NEW
    '/admin/choir-projects'      => 'modules/Dashboard/views/choir-projects.php',    // NEW

    // ── Admin — Reports ───────────────────────────────────────────────────────
    '/admin/reports'             => 'modules/Dashboard/views/reports.php',
    '/admin/reports-overview'    => 'modules/Dashboard/views/reports-overview.php',  // NEW (tabbed overview)
    '/admin/reports-members'     => 'modules/Dashboard/views/reports-members.php',   // NEW (→ redirects to overview)
    '/admin/reports-membership'  => 'modules/Dashboard/views/reports-membership.php',
    '/admin/reports-finance'     => 'modules/Dashboard/views/reports-finance.php',   // UPDATED (→ redirects to overview)
    '/admin/reports-attendance'  => 'modules/Dashboard/views/reports-attendance.php',

];

// ─────────────────────────────────────────────────────────────────────────────
// DISPATCH
// ─────────────────────────────────────────────────────────────────────────────
if (array_key_exists($path, $routes)) {
    $file_path = ROOT_PATH . '/' . $routes[$path];
    if (file_exists($file_path)) {
        if (!empty($query)) {
            parse_str($query, $_GET);
        }
        require_once $file_path;
    } else {
        http_response_code(404);
        echo "View file not found: " . $file_path;
    }
} else {
    http_response_code(404);
    echo "Page not found: " . $path;
}