<?php
/**
 * Video Controller
 * File: modules/Videos/controllers/VideoController.php
 * Handles video gallery business logic
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/VideoModel.php';

class VideoController {
    private $db;
    public $videoModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->videoModel = new VideoModel($this->db);
    }

    /**
     * Get videos with pagination and filters
     * @param array $params Parameters: limit, offset, category, status
     * @return array Response array with success, data, and metadata
     */
    public function getVideos($params = []) {
        try {
            $limit = isset($params['limit']) ? (int)$params['limit'] : 50;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            $category = isset($params['category']) ? $params['category'] : null;
            $status = isset($params['status']) ? $params['status'] : 'active';

            // Validate limit (max 100)
            if ($limit > 100) {
                $limit = 100;
            }

            // Get videos
            $videos = $this->videoModel->getVideos($limit, $offset, $category, $status);

            // Get total count
            $total = $this->videoModel->getTotalCount($category, $status);

            return [
                'success' => true,
                'data' => $videos,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'category' => $category
            ];

        } catch (Exception $e) {
            error_log("Video Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve videos.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get single video by ID
     * @param int $id Video ID
     * @return array Response array
     */
    public function getVideoById($id) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Video ID is required.'
                ];
            }

            $video = $this->videoModel->getVideoById($id);

            if ($video) {
                return [
                    'success' => true,
                    'data' => $video
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Video not found.'
                ];
            }

        } catch (Exception $e) {
            error_log("Video Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve video.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get videos by category
     * @param string $category Category name
     * @param int $limit Number of videos
     * @return array Response array
     */
    public function getVideosByCategory($category, $limit = 20) {
        try {
            if (empty($category)) {
                return [
                    'success' => false,
                    'message' => 'Category is required.'
                ];
            }

            $videos = $this->videoModel->getVideosByCategory($category, $limit);

            return [
                'success' => true,
                'data' => $videos,
                'category' => $category
            ];

        } catch (Exception $e) {
            error_log("Video Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve videos by category.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get video statistics
     * @return array Response array with stats
     */
    public function getVideoStats() {
        try {
            $stats = $this->videoModel->getVideoStats();

            return [
                'success' => true,
                'data' => $stats
            ];

        } catch (Exception $e) {
            error_log("Video Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve video statistics.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Increment video views
     * @param int $id Video ID
     * @return array Response array
     */
    public function incrementViews($id) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Video ID is required.'
                ];
            }

            $success = $this->videoModel->incrementViews($id);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Views updated successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update views.'
                ];
            }

        } catch (Exception $e) {
            error_log("Video Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update views.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get featured/popular videos
     * @param int $limit Number of videos
     * @return array Response array
     */
    public function getFeaturedVideos($limit = 6) {
        try {
            $videos = $this->videoModel->getFeaturedVideos($limit);

            return [
                'success' => true,
                'data' => $videos
            ];

        } catch (Exception $e) {
            error_log("Video Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve featured videos.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create new video
     * @param array $data Video data
     * @return array Response array
     */
    public function createVideo($data) {
        try {
            // Validate required fields
            if (empty($data['title']) || empty($data['video_url'])) {
                return [
                    'success' => false,
                    'message' => 'Title and video URL are required.'
                ];
            }

            $videoId = $this->videoModel->createVideo($data);

            if ($videoId) {
                return [
                    'success' => true,
                    'message' => 'Video added successfully.',
                    'video_id' => $videoId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to add video.'
                ];
            }

        } catch (Exception $e) {
            error_log("Video Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create video.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update video
     * @param int $id Video ID
     * @param array $data Updated video data
     * @return array Response array
     */
    public function updateVideo($id, $data) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Video ID is required.'
                ];
            }

            $success = $this->videoModel->updateVideo($id, $data);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Video updated successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update video.'
                ];
            }

        } catch (Exception $e) {
            error_log("Video Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update video.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete video
     * @param int $id Video ID
     * @return array Response array
     */
    public function deleteVideo($id) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Video ID is required.'
                ];
            }

            $success = $this->videoModel->deleteVideo($id);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Video deleted successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete video.'
                ];
            }

        } catch (Exception $e) {
            error_log("Video Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete video.',
                'error' => $e->getMessage()
            ];
        }
    }
}
?>