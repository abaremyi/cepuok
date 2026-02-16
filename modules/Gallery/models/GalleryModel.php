<?php
/**
 * Gallery Model - CEP UOK 
 * File: modules/Gallery/models/GalleryModel.php
 * Handles all database operations for gallery with year support
 */

class GalleryModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get gallery images with pagination and year filter
     * @param int $limit Number of items to retrieve
     * @param int $offset Starting position
     * @param string $category Optional category filter
     * @param int $year Optional year filter
     * @param string $status Status filter (active/inactive)
     * @return array Array of gallery images
     */
    public function getGalleryImages($limit = 50, $offset = 0, $category = null, $year = null, $status = 'active') {
        try {
            $whereClause = "WHERE status = :status";
            $params = [':status' => $status];
            
            if ($category && $category !== 'all' && $category !== '') {
                $whereClause .= " AND category = :category";
                $params[':category'] = $category;
            }
            
            if ($year && $year !== 'all') {
                $whereClause .= " AND year = :year";
                $params[':year'] = $year;
            }
            
            $query = "SELECT 
                        id,
                        title,
                        description,
                        image_url,
                        thumbnail_url,
                        category,
                        year,
                        display_order,
                        status,
                        created_at,
                        updated_at
                      FROM gallery_images 
                      $whereClause
                      ORDER BY year DESC, display_order ASC, id DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $type = PDO::PARAM_STR;
                if ($key === ':year') $type = PDO::PARAM_INT;
                $stmt->bindValue($key, $value, $type);
            }
            
            // Always bind limit and offset
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of gallery images
     * @param string $category Optional category filter
     * @param int $year Optional year filter
     * @param string $status Status filter
     * @return int Total count
     */
    public function getTotalCount($category = null, $year = null, $status = 'active') {
        try {
            $whereClause = "WHERE status = :status";
            $params = [':status' => $status];
            
            if ($category && $category !== 'all' && $category !== '') {
                $whereClause .= " AND category = :category";
                $params[':category'] = $category;
            }
            
            if ($year && $year !== 'all') {
                $whereClause .= " AND year = :year";
                $params[':year'] = $year;
            }
            
            $query = "SELECT COUNT(*) as total FROM gallery_images $whereClause";
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $type = PDO::PARAM_STR;
                if ($key === ':year') $type = PDO::PARAM_INT;
                $stmt->bindValue($key, $value, $type);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all gallery years
     * @return array Array of years
     */
    public function getGalleryYears() {
        try {
            $query = "SELECT DISTINCT year 
                      FROM gallery_images 
                      WHERE status = 'active' 
                      AND year IS NOT NULL
                      ORDER BY year DESC";
            
            $stmt = $this->db->query($query);
            $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return $years;
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get count of images per year
     * @return array Associative array of year counts
     */
    public function getYearCounts() {
        try {
            $query = "SELECT 
                        year,
                        COUNT(*) as count 
                      FROM gallery_images 
                      WHERE status = 'active' AND year IS NOT NULL
                      GROUP BY year
                      ORDER BY year DESC";
            
            $stmt = $this->db->query($query);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $counts = [];
            foreach ($results as $row) {
                $counts[$row['year']] = (int)$row['count'];
            }
            
            return $counts;
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get images by year
     * @param int $year Year
     * @param int $limit Number of images
     * @param int $offset Starting position
     * @return array Array of images
     */
    public function getImagesByYear($year, $limit = 20, $offset = 0) {
        try {
            $query = "SELECT 
                        id,
                        title,
                        description,
                        image_url,
                        thumbnail_url,
                        category,
                        year,
                        display_order,
                        created_at
                      FROM gallery_images 
                      WHERE status = 'active' AND year = :year
                      ORDER BY display_order ASC, id DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get gallery categories
     * @return array Array of categories
     */
    public function getCategories() {
        try {
            $query = "SELECT DISTINCT category 
                      FROM gallery_images 
                      WHERE status = 'active' 
                      AND category IS NOT NULL
                      AND category != ''
                      ORDER BY category ASC";
            
            $stmt = $this->db->query($query);
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($categories)) {
                return ['general'];
            }
            
            return $categories;
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return ['general'];
        }
    }

    /**
     * Get single gallery image by ID
     * @param int $id Image ID
     * @return array|null Image data or null
     */
    public function getImageById($id) {
        try {
            $query = "SELECT * FROM gallery_images WHERE id = :id AND status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get images by category
     * @param string $category Category name
     * @param int $limit Number of images
     * @return array Array of images
     */
    public function getImagesByCategory($category, $limit = 20) {
        try {
            $query = "SELECT 
                        id,
                        title,
                        description,
                        image_url,
                        thumbnail_url,
                        category,
                        year,
                        display_order,
                        created_at
                      FROM gallery_images 
                      WHERE status = 'active' AND category = :category
                      ORDER BY year DESC, display_order ASC, id DESC
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get count of images per category
     * @return array Associative array of category counts
     */
    public function getCategoryCounts() {
        try {
            $query = "SELECT 
                        category,
                        COUNT(*) as count 
                      FROM gallery_images 
                      WHERE status = 'active'
                      GROUP BY category
                      ORDER BY category ASC";
            
            $stmt = $this->db->query($query);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $counts = [];
            foreach ($results as $row) {
                $counts[$row['category']] = (int)$row['count'];
            }
            
            return $counts;
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get featured/random images
     * @param int $limit Number of images
     * @return array Array of images
     */
    public function getFeaturedImages($limit = 6) {
        try {
            $query = "SELECT 
                        id,
                        title,
                        description,
                        image_url,
                        thumbnail_url,
                        category,
                        year,
                        created_at
                      FROM gallery_images 
                      WHERE status = 'active'
                      ORDER BY RAND()
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new gallery image
     * @param array $data Image data
     * @return int|bool Image ID or false
     */
    public function createImage($data) {
        try {
            $query = "INSERT INTO gallery_images (
                        title, 
                        description, 
                        image_url, 
                        thumbnail_url, 
                        category,
                        year,
                        display_order, 
                        status
                      ) VALUES (
                        :title, 
                        :description, 
                        :image_url, 
                        :thumbnail_url, 
                        :category,
                        :year,
                        :display_order, 
                        :status
                      )";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':image_url', $data['image_url'], PDO::PARAM_STR);
            
            $thumbnail_url = isset($data['thumbnail_url']) ? $data['thumbnail_url'] : $data['image_url'];
            $stmt->bindParam(':thumbnail_url', $thumbnail_url, PDO::PARAM_STR);
            
            $category = isset($data['category']) ? $data['category'] : 'general';
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            
            $year = isset($data['year']) ? $data['year'] : date('Y');
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            
            $display_order = isset($data['display_order']) ? $data['display_order'] : 0;
            $stmt->bindParam(':display_order', $display_order, PDO::PARAM_INT);
            
            $status = isset($data['status']) ? $data['status'] : 'active';
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update gallery image
     * @param int $id Image ID
     * @param array $data Updated image data
     * @return bool Success status
     */
    public function updateImage($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];
            
            if (isset($data['title'])) {
                $fields[] = "title = :title";
                $params[':title'] = $data['title'];
            }
            
            if (isset($data['description'])) {
                $fields[] = "description = :description";
                $params[':description'] = $data['description'];
            }
            
            if (isset($data['image_url'])) {
                $fields[] = "image_url = :image_url";
                $params[':image_url'] = $data['image_url'];
            }
            
            if (isset($data['thumbnail_url'])) {
                $fields[] = "thumbnail_url = :thumbnail_url";
                $params[':thumbnail_url'] = $data['thumbnail_url'];
            }
            
            if (isset($data['category'])) {
                $fields[] = "category = :category";
                $params[':category'] = $data['category'];
            }
            
            if (isset($data['year'])) {
                $fields[] = "year = :year";
                $params[':year'] = $data['year'];
            }
            
            if (isset($data['display_order'])) {
                $fields[] = "display_order = :display_order";
                $params[':display_order'] = $data['display_order'];
            }
            
            if (isset($data['status'])) {
                $fields[] = "status = :status";
                $params[':status'] = $data['status'];
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $query = "UPDATE gallery_images SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            // Bind all parameters
            foreach ($params as $key => $value) {
                $type = PDO::PARAM_STR;
                if ($key === ':id' || $key === ':display_order' || $key === ':year') $type = PDO::PARAM_INT;
                $stmt->bindValue($key, $value, $type);
            }
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete gallery image
     * @param int $id Image ID
     * @return bool Success status
     */
    public function deleteImage($id) {
        try {
            $query = "DELETE FROM gallery_images WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return false;
        }
    }
}
?>