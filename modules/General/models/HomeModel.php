<?php
/**
 * CEP UoK Home Model
 * File: modules/General/models/HomeModel.php
 * Handles all database operations for homepage content
 */

class HomeModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get page content by section
     */
    public function getPageContent($page_name, $section_name = null) {
        try {
            if ($section_name) {
                $query = "SELECT * FROM page_content 
                         WHERE page_name = :page_name 
                         AND section_name = :section_name 
                         AND status = 'active'";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':page_name', $page_name);
                $stmt->bindParam(':section_name', $section_name);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $query = "SELECT * FROM page_content 
                         WHERE page_name = :page_name 
                         AND status = 'active'
                         ORDER BY display_order ASC";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':page_name', $page_name);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("HomeModel Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get quick stats
     */
    public function getQuickStats() {
        try {
            $query = "SELECT * FROM quick_stats 
                     WHERE status = 'active' 
                     ORDER BY display_order ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("HomeModel Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get featured gallery images (one per category)
     */
    public function getFeaturedGalleryImages() {
        try {
            $query = "SELECT * FROM gallery_images 
                     WHERE is_featured = 1 
                     AND status = 'active' 
                     ORDER BY display_order ASC
                     LIMIT 9";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("HomeModel Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recurring events (fellowship schedule)
     */
    public function getRecurringEvents() {
        try {
            $query = "SELECT * FROM recurring_events 
                     WHERE status = 'active' 
                     ORDER BY display_order ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("HomeModel Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get site settings
     */
    public function getSiteSettings($key = null) {
        try {
            if ($key) {
                $query = "SELECT setting_value FROM site_settings WHERE setting_key = :key";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':key', $key);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? $result['setting_value'] : null;
            } else {
                $query = "SELECT setting_key, setting_value FROM site_settings";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $settings = [];
                foreach ($results as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
                return $settings;
            }
        } catch (PDOException $e) {
            error_log("HomeModel Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get latest news for homepage
     */
    public function getLatestNews($limit = 6) {
        try {
            $query = "SELECT * FROM news_events 
                     WHERE status = 'published' 
                     ORDER BY published_date DESC, created_at DESC 
                     LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("HomeModel Error: " . $e->getMessage());
            return [];
        }
    }
}
?>