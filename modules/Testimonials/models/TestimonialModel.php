<?php
/**
 * CEP UOK WEBSITE Testimonial Model
 * File: modules/Testimonials/models/TestimonialModel.php
 * Handles all database operations for testimonials
 */

class TestimonialModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get active testimonials
     * @param int $limit Number of testimonials to retrieve
     * @return array Array of testimonials
     */
    public function getTestimonials($limit = 10) {
        try {
            $query = "SELECT 
                        id,
                        name,
                        role,
                        content,
                        image_url,
                        display_order,
                        created_at
                      FROM testimonials 
                      WHERE status = 'active' 
                      ORDER BY display_order ASC, created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Testimonial Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single testimonial by ID
     * @param int $id Testimonial ID
     * @return array|null Testimonial data or null
     */
    public function getTestimonialById($id) {
        try {
            $query = "SELECT * FROM testimonials WHERE id = :id AND status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Testimonial Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all testimonials (including inactive)
     * @return array Array of all testimonials
     */
    public function getAllTestimonials() {
        try {
            $query = "SELECT * FROM testimonials ORDER BY display_order ASC, created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Testimonial Model Error: " . $e->getMessage());
            return [];
        }
    }
}
?>