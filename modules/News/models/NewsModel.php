
<?php
/**
 * News Model
 * File: modules/News/models/NewsModel.php
 * Handles all database operations for news and events
 */

class NewsModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get news items with pagination
     * @param int $limit Number of items to retrieve
     * @param int $offset Starting position
     * @param string $category Optional category filter
     * @param bool $upcoming Optional upcoming events filter
     * @return array Array of news items
     */
    public function getNewsItems($limit = 4, $offset = 0, $category = null, $upcoming = false) {
        try {
            $whereClause = "WHERE status = 'published'";
            $orderClause = "ORDER BY published_date DESC, created_at DESC";
            
            if ($category) {
                $whereClause .= " AND category = :category";
            }
            
            if ($upcoming) {
                $currentDate = date('Y-m-d');
                $whereClause .= " AND published_date >= :current_date";
                $orderClause = "ORDER BY published_date ASC";
            }
            
            $query = "SELECT 
                        id,
                        title,
                        excerpt,
                        description,
                        image_url,
                        thumbnail_url,
                        category,
                        published_date,
                        author,
                        views,
                        created_at,
                        event_location
                      FROM news_events 
                      $whereClause
                      $orderClause
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            
            if ($category) {
                $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            }
            
            if ($upcoming) {
                $stmt->bindParam(':current_date', $currentDate, PDO::PARAM_STR);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("News Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get featured news items
     * @param int $limit Number of items to retrieve
     * @return array Array of featured news items
    */
    public function getFeaturedNews($limit = 5) {
        try {
            $query = "SELECT 
                        id,
                        title,
                        excerpt,
                        description,
                        image_url,
                        thumbnail_url,
                        category,
                        published_date,
                        author,
                        views,
                        created_at,
                        event_location
                    FROM news_events 
                    WHERE status = 'published'
                    AND featured = 1
                    ORDER BY published_date DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("News Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of news items
     * @param string $category Optional category filter
     * @return int Total count
     */
    public function getTotalCount($category = null) {
        try {
            $whereClause = "WHERE status = 'published'";
            
            if ($category) {
                $whereClause .= " AND category = :category";
            }
            
            $query = "SELECT COUNT(*) as total FROM news_events $whereClause";
            $stmt = $this->db->prepare($query);
            
            if ($category) {
                $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("News Model Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get single news item by ID
     * @param int $id News ID
     * @return array|null News data or null
     */
    public function getNewsById($id) {
        try {
            $query = "SELECT * FROM news_events WHERE id = :id AND status = 'published'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $news = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Increment view count
            if ($news) {
                $this->incrementViews($id);
            }
            
            return $news;
            
        } catch (PDOException $e) {
            error_log("News Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Increment view count for a news item
     * @param int $id News ID
     * @return bool Success status
     */
    private function incrementViews($id) {
        try {
            $query = "UPDATE news_events SET views = views + 1 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("News Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get related news items
     * @param int $currentId Current news ID to exclude
     * @param string $category Category to match
     * @param int $limit Number of items
     * @return array Array of related news items
     */
    public function getRelatedNews($currentId, $category = null, $limit = 3) {
        try {
            $whereClause = "WHERE status = 'published' AND id != :current_id";
            
            if ($category) {
                $whereClause .= " AND category = :category";
            }
            
            $query = "SELECT 
                        id,
                        title,
                        excerpt,
                        image_url,
                        thumbnail_url,
                        category,
                        published_date
                      FROM news_events 
                      $whereClause
                      ORDER BY published_date DESC 
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':current_id', $currentId, PDO::PARAM_INT);
            
            if ($category) {
                $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("News Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get latest news items
     * @param int $limit Number of items
     * @return array Array of latest news items
     */
    public function getLatestNews($limit = 5) {
        try {
            $query = "SELECT 
                        id,
                        title,
                        excerpt,
                        image_url,
                        thumbnail_url,
                        published_date
                      FROM news_events 
                      WHERE status = 'published'
                      ORDER BY published_date DESC 
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("News Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upcoming events
     * @param int $limit Number of events
     * @return array Array of upcoming events
     */
    public function getUpcomingEvents($limit = 4) {
        try {
            $currentDate = date('Y-m-d');
            
            $query = "SELECT 
                        id,
                        title,
                        excerpt,
                        description,
                        image_url,
                        thumbnail_url,
                        category,
                        published_date,
                        event_location
                      FROM news_events 
                      WHERE status = 'published' 
                      AND category = 'event'
                      AND published_date >= :current_date
                      ORDER BY published_date ASC 
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':current_date', $currentDate, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("News Model Error: " . $e->getMessage());
            return [];
        }
    }
}
?>