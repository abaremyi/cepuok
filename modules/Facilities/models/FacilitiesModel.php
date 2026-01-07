<?php
/**
 * Facilities Model: modules/Facilities/models/FacilitiesModel.php
 * Handles database operations for facilities
 */

class FacilitiesModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get facilities by page type
     */
    public function getFacilitiesByPage($pageType) {
        try {
            $query = "SELECT * FROM facilities_sections 
                      WHERE page_type = :page_type AND is_active = 1
                      ORDER BY display_order ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':page_type', $pageType, PDO::PARAM_STR);
            $stmt->execute();
            
            $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get additional data for each facility
            foreach ($facilities as &$facility) {
                $facility['features'] = $this->getFacilityFeatures($facility['id']);
                $facility['images'] = $this->getFacilityImages($facility['id']);
            }
            
            return $facilities;
            
        } catch (PDOException $e) {
            error_log("Facilities Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get facility by slug
     */
    public function getFacilityBySlug($slug) {
        try {
            $query = "SELECT * FROM facilities_sections 
                      WHERE slug = :slug AND is_active = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmt->execute();
            
            $facility = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($facility) {
                $facility['features'] = $this->getFacilityFeatures($facility['id']);
                $facility['images'] = $this->getFacilityImages($facility['id']);
            }
            
            return $facility;
            
        } catch (PDOException $e) {
            error_log("Facilities Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get facility features
     */
    private function getFacilityFeatures($facilityId) {
        try {
            $query = "SELECT * FROM facility_features 
                      WHERE facility_id = :facility_id 
                      ORDER BY display_order ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':facility_id', $facilityId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Facilities Features Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get facility images
     */
    private function getFacilityImages($facilityId) {
        try {
            $query = "SELECT * FROM facility_images 
                      WHERE facility_id = :facility_id 
                      ORDER BY display_order ASC, is_featured DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':facility_id', $facilityId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Facilities Images Error: " . $e->getMessage());
            return [];
        }
    }
}
?>