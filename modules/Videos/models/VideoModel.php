<?php
/**
 * Video Model
 * File: modules/Videos/models/VideoModel.php
 * Handles all database operations for video gallery
 */

class VideoModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get videos with pagination
     * @param int $limit Number of items to retrieve
     * @param int $offset Starting position
     * @param string $category Optional category filter
     * @param string $status Status filter (active/inactive)
     * @return array Array of videos
     */
    public function getVideos($limit = 50, $offset = 0, $category = null, $status = 'active') {
        try {
            $whereClause = "WHERE status = :status";
            
            if ($category && $category !== 'all') {
                $whereClause .= " AND category = :category";
            }
            
            $query = "SELECT 
                        id,
                        title,
                        description,
                        video_url,
                        video_type,
                        thumbnail_url,
                        category,
                        duration,
                        views,
                        display_order,
                        status,
                        created_at,
                        updated_at
                      FROM video_gallery 
                      $whereClause
                      ORDER BY display_order ASC, created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            if ($category && $category !== 'all') {
                $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of videos
     * @param string $category Optional category filter
     * @param string $status Status filter
     * @return int Total count
     */
    public function getTotalCount($category = null, $status = 'active') {
        try {
            $whereClause = "WHERE status = :status";
            
            if ($category && $category !== 'all') {
                $whereClause .= " AND category = :category";
            }
            
            $query = "SELECT COUNT(*) as total FROM video_gallery $whereClause";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            if ($category && $category !== 'all') {
                $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get single video by ID
     * @param int $id Video ID
     * @return array|null Video data or null
     */
    public function getVideoById($id) {
        try {
            $query = "SELECT * FROM video_gallery WHERE id = :id AND status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get videos by category
     * @param string $category Category name
     * @param int $limit Number of videos
     * @return array Array of videos
     */
    public function getVideosByCategory($category, $limit = 20) {
        try {
            $query = "SELECT 
                        id,
                        title,
                        description,
                        video_url,
                        video_type,
                        thumbnail_url,
                        category,
                        duration,
                        views,
                        display_order
                      FROM video_gallery 
                      WHERE status = 'active' AND category = :category
                      ORDER BY display_order ASC, created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get video statistics
     * @return array Video statistics
     */
    public function getVideoStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_videos,
                        SUM(views) as total_views,
                        SUM(CASE 
                            WHEN duration IS NOT NULL THEN 
                                TIME_TO_SEC(STR_TO_DATE(duration, '%i:%s'))
                            ELSE 0 
                        END) / 3600 as total_hours
                      FROM video_gallery 
                      WHERE status = 'active'";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_videos' => (int)$result['total_videos'],
                'total_views' => (int)$result['total_views'],
                'total_hours' => round((float)$result['total_hours'], 1)
            ];
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return [
                'total_videos' => 0,
                'total_views' => 0,
                'total_hours' => 0
            ];
        }
    }

    /**
     * Increment video views
     * @param int $id Video ID
     * @return bool Success status
     */
    public function incrementViews($id) {
        try {
            $query = "UPDATE video_gallery SET views = views + 1 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get featured/popular videos (by views)
     * @param int $limit Number of videos
     * @return array Array of featured videos
     */
    public function getFeaturedVideos($limit = 6) {
        try {
            $query = "SELECT 
                        id,
                        title,
                        description,
                        video_url,
                        video_type,
                        thumbnail_url,
                        category,
                        duration,
                        views
                      FROM video_gallery 
                      WHERE status = 'active'
                      ORDER BY views DESC, created_at DESC
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new video
     * @param array $data Video data
     * @return int|false Video ID or false
     */
    public function createVideo($data) {
        try {
            $query = "INSERT INTO video_gallery 
                      (title, description, video_url, video_type, thumbnail_url, category, duration, display_order, status) 
                      VALUES 
                      (:title, :description, :video_url, :video_type, :thumbnail_url, :category, :duration, :display_order, :status)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':video_url', $data['video_url'], PDO::PARAM_STR);
            
            $video_type = isset($data['video_type']) ? $data['video_type'] : 'youtube';
            $stmt->bindParam(':video_type', $video_type, PDO::PARAM_STR);
            
            $thumbnail_url = isset($data['thumbnail_url']) ? $data['thumbnail_url'] : null;
            $stmt->bindParam(':thumbnail_url', $thumbnail_url, PDO::PARAM_STR);
            
            $category = isset($data['category']) ? $data['category'] : 'general';
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            
            $duration = isset($data['duration']) ? $data['duration'] : null;
            $stmt->bindParam(':duration', $duration, PDO::PARAM_STR);
            
            $display_order = isset($data['display_order']) ? $data['display_order'] : 0;
            $stmt->bindParam(':display_order', $display_order, PDO::PARAM_INT);
            
            $status = isset($data['status']) ? $data['status'] : 'active';
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update video
     * @param int $id Video ID
     * @param array $data Updated video data
     * @return bool Success status
     */
    public function updateVideo($id, $data) {
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
            
            if (isset($data['video_url'])) {
                $fields[] = "video_url = :video_url";
                $params[':video_url'] = $data['video_url'];
            }
            
            if (isset($data['video_type'])) {
                $fields[] = "video_type = :video_type";
                $params[':video_type'] = $data['video_type'];
            }
            
            if (isset($data['thumbnail_url'])) {
                $fields[] = "thumbnail_url = :thumbnail_url";
                $params[':thumbnail_url'] = $data['thumbnail_url'];
            }
            
            if (isset($data['category'])) {
                $fields[] = "category = :category";
                $params[':category'] = $data['category'];
            }
            
            if (isset($data['duration'])) {
                $fields[] = "duration = :duration";
                $params[':duration'] = $data['duration'];
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
            
            $query = "UPDATE video_gallery SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete video
     * @param int $id Video ID
     * @return bool Success status
     */
    public function deleteVideo($id) {
        try {
            $query = "DELETE FROM video_gallery WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update display order
     * @param int $id Video ID
     * @param int $order New order
     * @return bool Success status
     */
    public function updateDisplayOrder($id, $order) {
        try {
            $query = "UPDATE video_gallery SET display_order = :order WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':order', $order, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return false;
        }
    }
}
?>