<?php
/**
 * Testimonial Controller
 * File: modules/Testimonials/controllers/TestimonialController.php
 * Handles testimonial business logic
 */

// Fix the database path - go up 3 levels to reach the root, then to config
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/TestimonialModel.php';

class TestimonialController {
    private $db;
    public $testimonialModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->testimonialModel = new TestimonialModel($this->db);
    }

    /**
     * Get testimonials
     * @param array $params Parameters: limit
     * @return array Response array with success, data, and metadata
     */
    public function getTestimonials($params = []) {
        try {
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;

            // Validate limit (max 50)
            if ($limit > 50) {
                $limit = 50;
            }

            // Get testimonials
            $testimonials = $this->testimonialModel->getTestimonials($limit);

            return [
                'success' => true,
                'data' => $testimonials,
                'total' => count($testimonials),
                'limit' => $limit
            ];

        } catch (Exception $e) {
            error_log("Testimonial Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve testimonials.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get single testimonial
     * @param int $id Testimonial ID
     * @return array Response array
     */
    public function getTestimonialById($id) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Testimonial ID is required.'
                ];
            }

            $testimonial = $this->testimonialModel->getTestimonialById($id);

            if ($testimonial) {
                return [
                    'success' => true,
                    'data' => $testimonial
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Testimonial not found.'
                ];
            }

        } catch (Exception $e) {
            error_log("Testimonial Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve testimonial.',
                'error' => $e->getMessage()
            ];
        }
    }
}
?>