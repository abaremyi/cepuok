<?php
/**
 * Home Page Model
 * File: modules/General/models/HomeModel.php
 */

class HomeModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get quick stats
     * @return array Array of quick stats
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
            error_log("Home Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get social media links
     * @return array Array of social media
     */
    public function getSocialMedia() {
        try {
            $query = "SELECT * FROM social_media 
                      WHERE status = 'active' 
                      ORDER BY display_order ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Home Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get contact info
     * @return array Array of contact information
     */
    public function getContactInfo() {
        try {
            $query = "SELECT * FROM contact_info 
                      WHERE status = 'active' 
                      ORDER BY display_order ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Home Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get email contact
     * @return string|null Email address
     */
    public function getEmail() {
        try {
            $query = "SELECT value FROM contact_info 
                      WHERE type = 'email' AND status = 'active' 
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['value'] : 'cepuok01@gmail.com';
            
        } catch (PDOException $e) {
            error_log("Home Model Error: " . $e->getMessage());
            return 'cepuok01@gmail.com';
        }
    }
}