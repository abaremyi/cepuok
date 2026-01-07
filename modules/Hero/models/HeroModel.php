<?php
/**
 * Hero Slider Model
 * File: modules/Hero/models/HeroModel.php
 */

class HeroModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get active hero sliders
     * @param int $limit Number of sliders to retrieve
     * @return array Array of sliders
     */
    public function getSliders($limit = 10) {
        try {
            $query = "SELECT 
                        id,
                        title,
                        subtitle,
                        description,
                        image_url,
                        button1_text,
                        button1_link,
                        button2_text,
                        button2_link,
                        display_order
                      FROM hero_sliders 
                      WHERE status = 'active' 
                      ORDER BY display_order ASC, created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Hero Model Error: " . $e->getMessage());
            return [];
        }
    }
}
?>