<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    $db = Database::getInstance();
    
    // Get parameters with defaults
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? 'all';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 6;
    
    // Validate page number
    if ($page < 1) $page = 1;
    
    // Calculate offset
    $offset = ($page - 1) * $perPage;
    
    // Build query
    $whereConditions = ["status = 'published'"];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(title LIKE ? OR short_description LIKE ? OR content LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($category != 'all') {
        $whereConditions[] = "category = ?";
        $params[] = $category;
    }
    
    $whereClause = implode(" AND ", $whereConditions);
    
    // Count total news
    $countQuery = "SELECT COUNT(*) as total FROM news WHERE $whereClause";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $totalNews = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalNews / $perPage);
    
    // Get news for current page
    $query = "SELECT * FROM news WHERE $whereClause ORDER BY published_at DESC LIMIT $perPage OFFSET $offset";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate HTML
    $html = '';
    if (count($news) > 0) {
        foreach ($news as $article) {
            $featuredClass = $article['featured'] ? 'featured-article' : '';
            $html .= '
            <div class="col-md-12 mb-40">
                <div class="item ' . $featuredClass . '">
                    <div class="post-img">
                        <a href="post.php?slug=' . urlencode($article['slug']) . '"> 
                            <img src="../../../img/' . htmlspecialchars($article['image_path']) . '" alt="' . htmlspecialchars($article['title']) . '"> 
                        </a>
                    </div>
                    <div class="post-cont"> 
                        <span class="category"><a href="news.php?category=' . urlencode($article['category']) . '">' . htmlspecialchars($article['category']) . '</a></span> 
                        <span class="calendar"><a href="news.php">' . date('d M, Y', strtotime($article['published_at'])) . '</a></span>';
            
            if ($article['featured']) {
                $html .= '<span class="featured-badge">Featured</span>';
            }
            
            $html .= '
                        <h5><a href="post.php?slug=' . urlencode($article['slug']) . '">' . htmlspecialchars($article['title']) . '</a></h5>
                        <p>' . htmlspecialchars($article['short_description']) . '</p> 
                        <a href="post.php?slug=' . urlencode($article['slug']) . '" class="button-4">Read More</a>
                    </div>
                </div>
            </div>';
        }
    } else {
        $html = '<div class="col-md-12 text-center"><p>No news articles found matching your criteria.</p></div>';
    }
    
    // Generate pagination
    $pagination = '';
    if ($totalPages > 1) {
        $pagination .= '<ul class="pagination-wrap">';
        
        // Previous button
        if ($page > 1) {
            $pagination .= '<li><a href="#" data-page="' . ($page - 1) . '"><i class="ti-angle-left"></i></a></li>';
        } else {
            $pagination .= '<li class="disabled"><span><i class="ti-angle-left"></i></span></li>';
        }
        
        // Page numbers
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $startPage + 4);
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            $active = $i == $page ? 'class="active"' : '';
            $pagination .= '<li><a href="#" data-page="' . $i . '" ' . $active . '>' . $i . '</a></li>';
        }
        
        // Next button
        if ($page < $totalPages) {
            $pagination .= '<li><a href="#" data-page="' . ($page + 1) . '"><i class="ti-angle-right"></i></a></li>';
        } else {
            $pagination .= '<li class="disabled"><span><i class="ti-angle-right"></i></span></li>';
        }
        
        $pagination .= '</ul>';
    }
    
    $response = [
        'success' => true,
        'html' => $html,
        'pagination' => $pagination,
        'total' => $totalNews,
        'totalPages' => $totalPages
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>