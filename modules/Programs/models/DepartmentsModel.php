<?php
/**
 * CEP UoK Departments Model
 * File: modules/Programs/models/DepartmentsModel.php
 */

class DepartmentsModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all active departments
     */
    public function getDepartments($limit = null) {
        try {
            $query = "SELECT * FROM departments 
                     WHERE status = 'active' 
                     ORDER BY display_order ASC";
            
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
            error_log("DepartmentsModel Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single department by ID
     */
    public function getDepartmentById($id) {
        try {
            $query = "SELECT * FROM departments WHERE id = :id AND status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DepartmentsModel Error: " . $e->getMessage());
            return null;
        }
    }
}
?>