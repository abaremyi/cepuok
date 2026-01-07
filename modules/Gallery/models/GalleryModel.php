<?php
/**
 * Gallery Model
 * File: modules/Gallery/models/GalleryModel.php
 * Handles all database operations for gallery
 */

class GalleryModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get gallery images with pagination
     * @param int $limit Number of items to retrieve
     * @param int $offset Starting position
     * @param string $category Optional category filter
     * @param string $status Status filter (active/inactive)
     * @return array Array of gallery images
     */
    public function getGalleryImages($limit = 50, $offset = 0, $category = null, $status = 'active') {
        try {
            $whereClause = "WHERE status = :status";
            $params = [':status' => $status];
            
            if ($category && $category !== 'all' && $category !== '') {
                $whereClause .= " AND category = :category";
                $params[':category'] = $category;
            }
            
            $query = "SELECT 
                        id,
                        title,
                        description,
                        image_url,
                        thumbnail_url,
                        category,
                        display_order,
                        status,
                        created_at,
                        updated_at
                      FROM gallery_images 
                      $whereClause
                      ORDER BY display_order ASC, created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $type = PDO::PARAM_STR;
                if ($key === ':limit') $type = PDO::PARAM_INT;
                if ($key === ':offset') $type = PDO::PARAM_INT;
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
     * @param string $status Status filter
     * @return int Total count
     */
    public function getTotalCount($category = null, $status = 'active') {
        try {
            $whereClause = "WHERE status = :status";
            $params = [':status' => $status];
            
            if ($category && $category !== 'all' && $category !== '') {
                $whereClause .= " AND category = :category";
                $params[':category'] = $category;
            }
            
            $query = "SELECT COUNT(*) as total FROM gallery_images $whereClause";
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
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
            
            // Ensure we always have at least 'general' category
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
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Fix image URL if it has leading slash
            if ($result && isset($result['image_url']) && $result['image_url'][0] === '/') {
                $result['image_url'] = substr($result['image_url'], 1);
            }
            if ($result && isset($result['thumbnail_url']) && $result['thumbnail_url'] && $result['thumbnail_url'][0] === '/') {
                $result['thumbnail_url'] = substr($result['thumbnail_url'], 1);
            }
            
            return $result;
            
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
                        display_order,
                        created_at
                      FROM gallery_images 
                      WHERE status = 'active' AND category = :category
                      ORDER BY display_order ASC, created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fix image URLs if they have leading slashes
            foreach ($results as &$result) {
                if (isset($result['image_url']) && $result['image_url'][0] === '/') {
                    $result['image_url'] = substr($result['image_url'], 1);
                }
                if (isset($result['thumbnail_url']) && $result['thumbnail_url'] && $result['thumbnail_url'][0] === '/') {
                    $result['thumbnail_url'] = substr($result['thumbnail_url'], 1);
                }
            }
            
            return $results;
            
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
                      AND category IS NOT NULL
                      AND category != ''
                      GROUP BY category
                      ORDER BY category ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format as associative array
            $counts = ['all' => 0];
            foreach ($results as $row) {
                $category = $row['category'];
                $count = (int)$row['count'];
                $counts[$category] = $count;
                $counts['all'] += $count;
            }
            
            // Ensure all standard categories exist
            $standardCategories = ['academics', 'events', 'facilities', 'extracurricular', 'campus'];
            foreach ($standardCategories as $cat) {
                if (!isset($counts[$cat])) {
                    $counts[$cat] = 0;
                }
            }
            
            return $counts;
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return ['all' => 0, 'academics' => 0, 'events' => 0, 'facilities' => 0, 'extracurricular' => 0, 'campus' => 0];
        }
    }

    /**
     * Get featured/random images
     * @param int $limit Number of images
     * @return array Array of featured images
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
                        display_order
                      FROM gallery_images 
                      WHERE status = 'active'
                      ORDER BY RAND()
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fix image URLs if they have leading slashes
            foreach ($results as &$result) {
                if (isset($result['image_url']) && $result['image_url'][0] === '/') {
                    $result['image_url'] = substr($result['image_url'], 1);
                }
                if (isset($result['thumbnail_url']) && $result['thumbnail_url'] && $result['thumbnail_url'][0] === '/') {
                    $result['thumbnail_url'] = substr($result['thumbnail_url'], 1);
                }
            }
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get images for navigation (previous/next)
     * @param int $currentId Current image ID
     * @param string $category Optional category filter
     * @return array Navigation data
     */
    public function getNavigationImages($currentId, $category = null) {
        try {
            $whereClause = "WHERE status = 'active'";
            $params = [];
            
            if ($category && $category !== 'all' && $category !== '') {
                $whereClause .= " AND category = :category";
                $params[':category'] = $category;
            }
            
            $query = "SELECT id, title 
                      FROM gallery_images 
                      $whereClause
                      ORDER BY display_order ASC, created_at DESC";
            
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $navigation = ['prev' => null, 'next' => null];
            $currentIndex = -1;
            
            foreach ($images as $index => $image) {
                if ($image['id'] == $currentId) {
                    $currentIndex = $index;
                    break;
                }
            }
            
            if ($currentIndex !== -1) {
                // Previous image
                if ($currentIndex > 0) {
                    $navigation['prev'] = $images[$currentIndex - 1]['id'];
                }
                
                // Next image
                if ($currentIndex < count($images) - 1) {
                    $navigation['next'] = $images[$currentIndex + 1]['id'];
                }
            }
            
            return $navigation;
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return ['prev' => null, 'next' => null];
        }
    }

    /**
     * Create new gallery image
     * @param array $data Image data
     * @return int|false Image ID or false
     */
    public function createImage($data) {
        try {
            $query = "INSERT INTO gallery_images 
                      (title, description, image_url, thumbnail_url, category, display_order, status) 
                      VALUES 
                      (:title, :description, :image_url, :thumbnail_url, :category, :display_order, :status)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':image_url', $data['image_url'], PDO::PARAM_STR);
            $stmt->bindParam(':thumbnail_url', $data['thumbnail_url'], PDO::PARAM_STR);
            $stmt->bindParam(':category', $data['category'], PDO::PARAM_STR);
            
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
                if ($key === ':id' || $key === ':display_order') $type = PDO::PARAM_INT;
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

    /**
     * Update display order
     * @param int $id Image ID
     * @param int $order New order
     * @return bool Success status
     */
    public function updateDisplayOrder($id, $order) {
        try {
            $query = "UPDATE gallery_images SET display_order = :order, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':order', $order, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent images
     * @param int $limit Number of images
     * @return array Array of recent images
     */
    public function getRecentImages($limit = 10) {
        try {
            $query = "SELECT 
                        id,
                        title,
                        description,
                        image_url,
                        thumbnail_url,
                        category,
                        created_at
                      FROM gallery_images 
                      WHERE status = 'active'
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fix image URLs if they have leading slashes
            foreach ($results as &$result) {
                if (isset($result['image_url']) && $result['image_url'][0] === '/') {
                    $result['image_url'] = substr($result['image_url'], 1);
                }
                if (isset($result['thumbnail_url']) && $result['thumbnail_url'] && $result['thumbnail_url'][0] === '/') {
                    $result['thumbnail_url'] = substr($result['thumbnail_url'], 1);
                }
            }
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search images by keyword
     * @param string $keyword Search keyword
     * @param int $limit Number of results
     * @return array Search results
     */
    public function searchImages($keyword, $limit = 20) {
        try {
            $query = "SELECT 
                        id,
                        title,
                        description,
                        image_url,
                        thumbnail_url,
                        category
                      FROM gallery_images 
                      WHERE status = 'active'
                      AND (title LIKE :keyword OR description LIKE :keyword OR category LIKE :keyword)
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $searchKeyword = "%$keyword%";
            $stmt->bindParam(':keyword', $searchKeyword, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fix image URLs if they have leading slashes
            foreach ($results as &$result) {
                if (isset($result['image_url']) && $result['image_url'][0] === '/') {
                    $result['image_url'] = substr($result['image_url'], 1);
                }
                if (isset($result['thumbnail_url']) && $result['thumbnail_url'] && $result['thumbnail_url'][0] === '/') {
                    $result['thumbnail_url'] = substr($result['thumbnail_url'], 1);
                }
            }
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            return [];
        }
    }
}
?>