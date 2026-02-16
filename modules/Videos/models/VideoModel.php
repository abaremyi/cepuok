<?php
/**
 * Video Model - CEP UOK WEBSITE
 * File: modules/Videos/models/VideoModel.php
 */

class VideoModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get videos with pagination
     */
    public function getVideos($limit = 12, $offset = 0, $status = 'active') {
        try {
            $query = "SELECT * FROM videos WHERE status = :status ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
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
     * Get total video count
     */
    public function getTotalCount($status = 'active') {
        try {
            $query = "SELECT COUNT(*) as total FROM videos WHERE status = :status";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Increment view count
     */
    public function incrementViews($id) {
        try {
            $query = "UPDATE videos SET views = COALESCE(views, 0) + 1 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Video Model Error: " . $e->getMessage());
            return false;
        }
    }
}
?>