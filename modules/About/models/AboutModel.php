<?php
/**
 * About Section Model
 * File: modules/About/models/AboutModel.php
 */

class AboutModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get about section by name
     * @param string $sectionName Section name
     * @return array|null Section data or null
     */
    public function getSection($sectionName) {
        try {
            $query = "SELECT * FROM about_sections 
                      WHERE section_name = :section_name 
                      AND status = 'active'";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':section_name', $sectionName, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("About Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get features for about section
     * @param int $sectionId About section ID
     * @return array Array of features
     */
    public function getFeatures($sectionId) {
        try {
            $query = "SELECT * FROM features 
                      WHERE about_section_id = :section_id 
                      AND status = 'active' 
                      ORDER BY display_order ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("About Model Error: " . $e->getMessage());
            return [];
        }
    }
}