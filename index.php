<?php
// CEPUOK/index.php - Main Router


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include paths configuration - use require_once to prevent multiple inclusion
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
$path = isset($parsed_url['path']) ? rtrim($parsed_url['path'], '/') : '';
$query = isset($parsed_url['query']) ? $parsed_url['query'] : '';

// Define routes
$routes = [
    '' => 'modules/General/views/index.php',
    '/' => 'modules/General/views/index.php',
    '/home' => 'modules/General/views/index.php',
    '/about' => 'modules/General/views/about-cep.php', 
    '/about-cep' => 'modules/General/views/about-cep.php',
    '/history' => 'modules/General/views/history.php',
    '/leadership' => 'modules/General/views/leadership.php',
    '/contact' => 'modules/General/views/contact.php',
    '/departments' => 'modules/General/views/departments.php',
    '/gallery' => 'modules/General/views/gallery-photo.php',
    '/gallery-photo' => 'modules/General/views/gallery-photo.php',
    '/gallery-video' => 'modules/General/views/gallery-video.php',
    '/leadership-team' => 'modules/General/views/leadership-team.php',
    '/local-church' => 'modules/General/views/local-church.php',
    '/membership' => 'modules/General/views/membership.php',
    '/news' => 'modules/General/views/news.php',
    '/programs' => 'modules/General/views/programs.php',
    '/projects' => 'modules/General/views/projects.php',
    '/project-details' => 'modules/General/views/project-details.php',
    '/products' => 'modules/General/views/products.php',
    '/team' => 'modules/General/views/team.php',
    '/team-single' => 'modules/General/views/team-single.php',
    '/academic-facilities' => 'modules/General/views/academic-facilities.php',
    '/sports-facilities' => 'modules/General/views/sports-facilities.php',
    '/services-facilities' => 'modules/General/views/services-facilities.php',
    '/services' => 'modules/General/views/services.php',
    '/service-details' => 'modules/General/views/service-details.php',
    
    // Add static file routes
    '/static/get_projects' => 'modules/General/static/get_projects.php',
    '/static/get_project_gallery' => 'modules/General/static/get_project_gallery.php',
    '/static/get_related_projects' => 'modules/General/static/get_related_projects.php',
    
    // Add API routes
    '/api/leadership' => 'modules/Leadership/api/leadershipApi.php',
    '/api/administration' => 'modules/Administration/api/administrationApi.php',
    '/api/admission' => 'modules/Admission/api/admissionApi.php',
    '/api/contact' => 'modules/Contact/api/contactApi.php',
    '/api/facilities' => 'modules/Facilities/api/facilitiesApi.php',
    '/api/gallery' => 'modules/Gallery/api/galleryApi.php',
    '/api/hero' => 'modules/Hero/api/heroApi.php',
    '/api/membership' => 'modules/Membership/api/membershipApi.php',
    '/api/news' => 'modules/News/api/newsApi.php',
    '/api/programs' => 'modules/Programs/api/programsApi.php',
    '/api/testimonials' => 'modules/Testimonials/api/testimonialsApi.php',
    '/api/videos' => 'modules/Videos/api/videoApi.php',

    // Authentication routes
    '/forgot-password' => 'modules/Authentication/views/forgot-password.php',
    '/login' => 'modules/Authentication/views/login.php',
    '/logout' => 'modules/Authentication/views/logout.php', 
    '/register' => 'modules/Authentication/views/register.php',
    '/reset-password' => 'modules/Authentication/views/reset-password.php',
    '/verify-email' => 'modules/Authentication/views/verifyEmail.php',
    
    // Admin dashboard routes
    '/admin/dashboard' => 'modules/Dashboard/views/admin-dashboard.php',
    '/admin/welcome' => 'modules/Dashboard/views/admin-welcome.php',
    '/admin/profile' => 'modules/Dashboard/views/profile.php',
    '/admin/settings' => 'modules/Dashboard/views/settings.php',
    
    // Admin management routes
    '/admin/users-management' => 'modules/Dashboard/views/users-management.php',
    '/admin/users-add-user' => 'modules/Dashboard/views/users-add-user.php',
    '/admin/users-view' => 'modules/Dashboard/views/users-view.php',
    '/admin/roles-permissions-management' => 'modules/Dashboard/views/roles-permissions-management.php',
    '/admin/membership-management' => 'modules/Dashboard/views/membership-management.php',
    '/admin/membership-applications' => 'modules/Dashboard/views/membership-applications.php',
    '/admin/news-events-management' => 'modules/Dashboard/views/news-events-management.php',
    '/admin/gallery-management' => 'modules/Dashboard/views/gallery-management.php',
    '/admin/video-gallery-management' => 'modules/Dashboard/views/video-gallery-management.php',
    '/admin/testimonials-management' => 'modules/Dashboard/views/testimonials-management.php',
    '/admin/leadership-management' => 'modules/Dashboard/views/leadership-management.php',
    '/admin/programs-management' => 'modules/Dashboard/views/programs-management.php',
    '/admin/departments-management' => 'modules/Dashboard/views/departments-management.php',
    '/admin/messages-management' => 'modules/Dashboard/views/messages-management.php',
    
    // Session-specific routes
    '/admin/session/members' => 'modules/Dashboard/views/session-members.php',
    '/admin/session/activities' => 'modules/Dashboard/views/session-activities.php',
    '/admin/session/reports' => 'modules/Dashboard/views/session-reports.php',
    
    // API routes
    '/api/auth' => 'modules/Authentication/api/authApi.php',
    '/api/users' => 'modules/Authentication/api/userApi.php',
    '/api/dashboard' => 'modules/Dashboard/api/dashboardApi.php',
];

// Serve the appropriate file
if (array_key_exists($path, $routes)) {
    $file_path = ROOT_PATH . '/' . $routes[$path];
    if (file_exists($file_path)) {
        // Pass the query string to the included file
        if (!empty($query)) {
            parse_str($query, $_GET);
        }
        require_once $file_path;
    } else {
        http_response_code(404);
        echo "View file not found: " . $file_path;
    }
} else {
    // 404 - Page not found
    http_response_code(404);
    echo "Page not found: " . $path;
}

?>