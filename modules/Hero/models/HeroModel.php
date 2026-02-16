<?php
/**
 * CEP UoK Hero Model
 * File: modules/Hero/models/HeroModel.php
 * Handles hero slider database operations
 */

class HeroModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get active hero sliders
     */
    public function getHeroSliders($limit = null) {
        try {
            $query = "SELECT * FROM hero_sliders 
                     WHERE status = 'active' 
                     ORDER BY display_order ASC, created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $this->db->prepare($query);
            
            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Hero Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single slider by ID
     */
    public function getSliderById($id) {
        try {
            $query = "SELECT * FROM hero_sliders WHERE id = :id";
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
?>