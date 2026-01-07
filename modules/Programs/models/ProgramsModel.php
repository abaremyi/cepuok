<?php
/**
 * Programs Model
 * File: modules/Programs/models/ProgramsModel.php
 * Handles all database operations for educational programs
 */

class ProgramsModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all active programs
     * @return array Array of programs
     */
    public function getAllPrograms() {
        try {
            $query = "SELECT 
                        id,
                        title,
                        subtitle,
                        description,
                        icon_class,
                        image_url,
                        display_order,
                        status,
                        created_at
                      FROM educational_programs 
                      WHERE status = 'active'
                      ORDER BY display_order ASC, id ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Programs Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single program by ID
     * @param int $id Program ID
     * @return array|null Program data or null
     */
    public function getProgramById($id) {
        try {
            $query = "SELECT * FROM educational_programs WHERE id = :id AND status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Programs Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get program by title (for URL routing)
     * @param string $title Program title
     * @return array|null Program data or null
     */
    public function getProgramByTitle($title) {
        try {
            // Convert URL-friendly title back to searchable format
            $searchTitle = str_replace('-', ' ', $title);
            
            $query = "SELECT * FROM educational_programs 
                      WHERE LOWER(REPLACE(title, ' ', '-')) = LOWER(:title) 
                      AND status = 'active'";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Programs Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get total count of active programs
     * @return int Total count
     */
    public function getTotalCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM educational_programs WHERE status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("Programs Model Error: " . $e->getMessage());
            return 0;
        }
    }
}
?>