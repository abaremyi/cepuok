<?php
/**
 * Hero Slider Controller
 * File: modules/Hero/controllers/HeroController.php
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/HeroModel.php';

class HeroController {
    private $db;
    public $heroModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->heroModel = new HeroModel($this->db);
    }

    /**
     * Get hero sliders
     * @param array $params Parameters: limit
     * @return array Response array with success, data, and metadata
     */
    public function getSliders($params = []) {
        try {
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;

            // Validate limit (max 10)
            if ($limit > 10) {
                $limit = 10;
            }

            // Get sliders
            $sliders = $this->heroModel->getSliders($limit);

            return [
                'success' => true,
                'data' => $sliders,
                'total' => count($sliders),
                'limit' => $limit
            ];

        } catch (Exception $e) {
            error_log("Hero Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve hero sliders.',
                'error' => $e->getMessage()
            ];
        }
    }
}
?>