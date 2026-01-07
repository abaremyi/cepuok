<?php
class GalleryModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get all gallery images
    public function getAllImages($category = null, $featuredOnly = false, $limit = null) {
        $query = "SELECT * FROM gallery WHERE 1=1";
        $params = [];
        
        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }
        
        if ($featuredOnly) {
            $query .= " AND is_featured = 1";
        }
        
        $query .= " ORDER BY display_order ASC, created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT ?";
            $params[] = (int)$limit;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get distinct categories
    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM gallery WHERE category != '' ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Get a single image by ID
    public function getImageById($id) {
        $stmt = $this->db->prepare("SELECT * FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get featured images
    public function getFeaturedImages($limit = null) {
        return $this->getAllImages(null, true, $limit);
    }
    
    // Get images by category
    public function getImagesByCategory($category, $limit = null) {
        return $this->getAllImages($category, false, $limit);
    }
}