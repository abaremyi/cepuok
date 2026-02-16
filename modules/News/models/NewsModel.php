<?php
/**
 * CEP UoK News Model
 * File: modules/News/models/NewsModel.php
 * Handles news and events database operations
 */

class NewsModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get latest published news
     */
    public function getLatestNews($limit = 10, $category = null) {
        try {
            $query = "SELECT * FROM news_events 
                     WHERE status = 'published'";
            
            if ($category) {
                $query .= " AND category = :category";
            }
            
            $query .= " ORDER BY published_date DESC, created_at DESC 
                       LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            
            if ($category) {
                $stmt->bindParam(':category', $category);
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
     * Get news by ID
     */
    public function getNewsById($id) {
        try {
            $query = "SELECT * FROM news_events WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Increment view count
            $this->incrementViews($id);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("News Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Increment view count for news item
     */
    private function incrementViews($id) {
        try {
            $query = "UPDATE news_events SET views = views + 1 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("News Model Error: " . $e->getMessage());
        }
    }

    /**
     * Get featured news
     */
    public function getFeaturedNews($limit = 3) {
        try {
            $query = "SELECT * FROM news_events 
                     WHERE status = 'published' AND featured = 1 
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
}
?>