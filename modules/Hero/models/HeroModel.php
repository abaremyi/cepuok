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
            $query = "SELECT * FROM hero_sliders 
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

    /**
     * Get single slider by ID
     * @param int $id Slider ID
     * @return array|null Slider data or null
     */
    public function getSliderById($id) {
        try {
            $query = "SELECT * FROM hero_sliders WHERE id = :id AND status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Hero Model Error: " . $e->getMessage());
            return null;
        }
    }
}