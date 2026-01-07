<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

try {
    $db = Database::getInstance();
    
    // Get recent posts
    $recentStmt = $db->query("SELECT title, slug, image_path FROM news WHERE status = 'published' ORDER BY published_at DESC LIMIT 3");
    $recentPosts = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $recentHtml = '';
    foreach ($recentPosts as $post) {
        $recentHtml .= '
        <li>
            <div class="thum"> <img src="../../../img/' . htmlspecialchars($post['image_path']) . '" class="img-fluid" alt="' . htmlspecialchars($post['title']) . '"> </div> 
            <a href="post.php?slug=' . urlencode($post['slug']) . '">' . htmlspecialchars($post['title']) . '</a>
        </li>';
    }
    
    // Get archives
    $archiveStmt = $db->query("SELECT DISTINCT DATE_FORMAT(published_at, '%M %Y') as month_year, 
                              DATE_FORMAT(published_at, '%Y-%m') as month_num 
                              FROM news WHERE status = 'published' 
                              ORDER BY published_at DESC LIMIT 6");
    $archives = $archiveStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $archiveHtml = '';
    foreach ($archives as $archive) {
        $archiveHtml .= '<li><a href="news.php?archive=' . urlencode($archive['month_num']) . '">' . $archive['month_year'] . '</a></li>';
    }
    
    // Get tags
    $tagStmt = $db->query("SELECT DISTINCT tags FROM news WHERE status = 'published' AND tags IS NOT NULL");
    $allTags = [];
    while ($row = $tagStmt->fetch(PDO::FETCH_ASSOC)) {
        $tags = explode(',', $row['tags']);
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (!empty($tag) && !in_array($tag, $allTags)) {
                $allTags[] = $tag;
            }
        }
    }
    
    $tagHtml = '';
    foreach (array_slice($allTags, 0, 12) as $tag) {
        $tagHtml .= '<li><a href="news.php?tag=' . urlencode($tag) . '">' . htmlspecialchars($tag) . '</a></li>';
    }
    
    echo json_encode([
        'success' => true,
        'recentPosts' => $recentHtml,
        'archives' => $archiveHtml,
        'tags' => $tagHtml
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>