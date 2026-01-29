<?php
// CEPUOK/index.php - Main Router

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
    '/about' => 'modules/General/views/about.php', 
    '/contact' => 'modules/General/views/contact.php',
    '/departments' => 'modules/General/views/departments.php',
    '/gallery' => 'modules/General/views/gallery.php',
    '/leadership-team' => 'modules/General/views/leadership-team.php',
    '/local-church' => 'modules/General/views/local-church.php',
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
    '/video-gallery' => 'modules/General/views/videos.php',
    
    // Add static file routes
    '/static/get_projects' => 'modules/General/static/get_projects.php',
    '/static/get_project_gallery' => 'modules/General/static/get_project_gallery.php',
    '/static/get_related_projects' => 'modules/General/static/get_related_projects.php',
    
    // Add API routes
    '/api/administration' => 'modules/Administration/api/administrationApi.php',
    '/api/admission' => 'modules/Admission/api/admissionApi.php',
    '/api/contact' => 'modules/Contact/api/contactApi.php',
    '/api/facilities' => 'modules/Facilities/api/facilitiesApi.php',
    '/api/gallery' => 'modules/Gallery/api/galleryApi.php',
    '/api/hero' => 'modules/Hero/api/heroApi.php',
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
    
    // Dashboard routes
    '/admin' => 'modules/Dashboard/views/admin.php',
    '/dashboard' => 'modules/Dashboard/views/dashboard.php',
    '/parent' => 'modules/Dashboard/views/parent.php',
    '/student' => 'modules/Dashboard/views/student.php',
    '/teacher' => 'modules/Dashboard/views/teacher.php',
    
    // Admin management routes
    '/admin/admission' => 'modules/Dashboard/views/admission-management.php',
    '/admin/dashboard' => 'modules/Dashboard/views/admin-dashboard.php',
    '/admin/departments' => 'modules/Dashboard/views/departments-management.php',
    '/admin/educational-programs' => 'modules/Dashboard/views/educational-programs.php',
    '/admin/facilities' => 'modules/Dashboard/views/facilities-management.php',
    '/admin/gallery' => 'modules/Dashboard/views/gallery-management.php',
    '/admin/hero-sliders' => 'modules/Dashboard/views/hero-sliders.php',
    '/admin/leadership' => 'modules/Dashboard/views/leadership-management.php', 
    '/admin/news-events' => 'modules/Dashboard/views/news-events-management.php',
    '/admin/page-content' => 'modules/Dashboard/views/page-content-management.php',
    '/admin/quick-stats' => 'modules/Dashboard/views/quick-stats-management.php',
    '/admin/roles-permissions' => 'modules/Dashboard/views/roles-permissions-management.php',
    '/admin/testimonials' => 'modules/Dashboard/views/testimonials-management.php',
    '/admin/users-management' => 'modules/Dashboard/views/users-management.php',
    '/admin/video-gallery' => 'modules/Dashboard/views/video-gallery-management.php',
    '/admin/why-choose' => 'modules/Dashboard/views/why-choose-management.php',
    
    // API routes
    '/api/auth' => 'modules/Authentication/api/authApi.php',
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