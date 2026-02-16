<?php
/**
 * Video Controller - CEP UOK WEBSITE
 * File: modules/Videos/controllers/VideoController.php
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/VideoModel.php';

class VideoController {
    private $db;
    public $videoModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->videoModel = new VideoModel($this->db);
    }

    public function getVideos($params = []) {
        try {
            $limit = isset($params['limit']) ? (int)$params['limit'] : 12;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            $status = isset($params['status']) ? $params['status'] : 'active';

            if ($limit > 50) $limit = 50;

            $videos = $this->videoModel->getVideos($limit, $offset, $status);
            $total = $this->videoModel->getTotalCount($status);

            return [
                'success' => true,
                'data' => $videos,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
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

    public function incrementViews($id) {
        try {
            $success = $this->videoModel->incrementViews($id);
            return [
                'success' => $success,
                'message' => $success ? 'View count updated' : 'Failed to update views'
            ];
        } catch (Exception $e) {
            error_log("Video Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to increment views.',
                'error' => $e->getMessage()
            ];
        }
    }
}
?>