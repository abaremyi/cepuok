<?php
/**
 * Programs Controller
 * File: modules/Programs/controllers/ProgramsController.php
 * Handles programs business logic
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/ProgramsModel.php';

class ProgramsController {
    private $db;
    public $programsModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->programsModel = new ProgramsModel($this->db);
    }

    /**
     * Get all programs
     * @return array Response array with success and data
     */
    public function getAllPrograms() {
        try {
            $programs = $this->programsModel->getAllPrograms();

            // Format data for display
            foreach ($programs as &$program) {
                // Create URL-friendly slug
                $program['slug'] = strtolower(str_replace(' ', '-', $program['title']));
                
                // Set default icon if not set
                if (empty($program['icon_class'])) {
                    $program['icon_class'] = 'fas fa-book';
                }

                // Format date
                if (isset($program['created_at'])) {
                    $program['formatted_date'] = date('F j, Y', strtotime($program['created_at']));
                }
            }

            return [
                'success' => true,
                'data' => $programs,
                'total' => count($programs)
            ];

        } catch (Exception $e) {
            error_log("Programs Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve programs.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get single program by ID
     * @param int $id Program ID
     * @return array Response array
     */
    public function getProgramById($id) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Program ID is required.'
                ];
            }

            $program = $this->programsModel->getProgramById($id);

            if ($program) {
                // Create URL-friendly slug
                $program['slug'] = strtolower(str_replace(' ', '-', $program['title']));
                
                // Format date
                if (isset($program['created_at'])) {
                    $program['formatted_date'] = date('F j, Y', strtotime($program['created_at']));
                }
                
                return [
                    'success' => true,
                    'data' => $program
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Program not found.'
                ];
            }

        } catch (Exception $e) {
            error_log("Programs Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve program.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get program by title/slug
     * @param string $title Program title or slug
     * @return array Response array
     */
    public function getProgramByTitle($title) {
        try {
            if (empty($title)) {
                return [
                    'success' => false,
                    'message' => 'Program title is required.'
                ];
            }

            $program = $this->programsModel->getProgramByTitle($title);

            if ($program) {
                $program['slug'] = strtolower(str_replace(' ', '-', $program['title']));
                
                if (isset($program['created_at'])) {
                    $program['formatted_date'] = date('F j, Y', strtotime($program['created_at']));
                }
                
                return [
                    'success' => true,
                    'data' => $program
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Program not found.'
                ];
            }

        } catch (Exception $e) {
            error_log("Programs Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve program.',
                'error' => $e->getMessage()
            ];
        }
    }
}
?>