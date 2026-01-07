<?php
/**
 * Facilities Controller: modules/Facilities/controllers/FacilitiesController.php
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/FacilitiesModel.php';

class FacilitiesController {
    private $db;
    public $facilitiesModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->facilitiesModel = new FacilitiesModel($this->db);
    }

    /**
     * Get facilities for specific page
     */
    public function getFacilitiesByPage($pageType) {
        try {
            $facilities = $this->facilitiesModel->getFacilitiesByPage($pageType);
            
            if (empty($facilities)) {
                return [
                    'success' => false,
                    'message' => 'No facilities found for this page.'
                ];
            }

            return [
                'success' => true,
                'data' => $facilities,
                'total' => count($facilities)
            ];

        } catch (Exception $e) {
            error_log("Facilities Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load facilities.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get single facility by slug
     */
    public function getFacilityBySlug($slug) {
        try {
            if (empty($slug)) {
                return [
                    'success' => false,
                    'message' => 'Facility slug is required.'
                ];
            }

            $facility = $this->facilitiesModel->getFacilityBySlug($slug);

            if ($facility) {
                return [
                    'success' => true,
                    'data' => $facility
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Facility not found.'
                ];
            }

        } catch (Exception $e) {
            error_log("Facilities Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load facility.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get page title based on type
     */
    public function getPageTitle($pageType) {
        $titles = [
            'academic' => 'Academic Facilities',
            'sports' => 'Sports & Recreation',
            'services' => 'School Services'
        ];

        return $titles[$pageType] ?? 'Our Facilities';
    }

    /**
     * Get page description based on type
     */
    public function getPageDescription($pageType) {
        $descriptions = [
            'academic' => 'Discover our modern academic facilities designed to enhance learning and foster innovation.',
            'sports' => 'Explore our comprehensive sports and recreation programs promoting physical fitness and teamwork.',
            'services' => 'Learn about our essential services that support student wellbeing and convenience.'
        ];

        return $descriptions[$pageType] ?? 'Explore our school facilities.';
    }
}
?>