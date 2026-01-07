<?php
/**
 * Administration Controller
 * File: modules/Administration/controllers/AdministrationController.php
 * Handles administration business logic
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/AdministrationModel.php';

class AdministrationController {
    private $db;
    public $administrationModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->administrationModel = new AdministrationModel($this->db);
    }

    /**
     * Get all administration data
     * @return array Response array with all administration data
     */
    public function getAllData() {
        try {
            // Get all data
            $leadership = $this->administrationModel->getAllLeadership();
            $orgChart = $this->administrationModel->getOrganizationChart();
            $stats = $this->administrationModel->getStatistics();

            // Format leadership data
            foreach ($leadership as &$leader) {
                $leader = $this->formatLeaderData($leader);
            }

            // Format organization chart
            if ($orgChart) {
                $orgChart = $this->formatOrgChartData($orgChart);
            }

            return [
                'success' => true,
                'data' => [
                    'leadership' => $leadership,
                    'orgChart' => $orgChart,
                    'statistics' => $stats
                ],
                'counts' => [
                    'leadership' => count($leadership)
                ]
            ];

        } catch (Exception $e) {
            error_log("Administration Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve administration information.',
                'error' => $e->getMessage(),
                'data' => [
                    'leadership' => [],
                    'orgChart' => null,
                    'statistics' => [
                        'total_leadership' => 0,
                        'years_experience' => date('Y') - 2013
                    ]
                ]
            ];
        }
    }

    /**
     * Get administration statistics only
     * @return array Response array with statistics
     */
    public function getStatistics() {
        try {
            $stats = $this->administrationModel->getStatistics();

            return [
                'success' => true,
                'data' => $stats
            ];

        } catch (Exception $e) {
            error_log("Administration Controller Statistics Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve statistics.',
                'error' => $e->getMessage(),
                'data' => [
                    'total_leadership' => 0,
                    'years_experience' => date('Y') - 2013
                ]
            ];
        }
    }

    /**
     * Get leadership by ID
     * @param int $id Leadership ID
     * @return array Response array
     */
    public function getLeadershipById($id) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Leadership ID is required.'
                ];
            }

            $leader = $this->administrationModel->getLeadershipById($id);

            if ($leader) {
                $leader = $this->formatLeaderData($leader);
                
                return [
                    'success' => true,
                    'data' => $leader
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Leadership member not found.'
                ];
            }

        } catch (Exception $e) {
            error_log("Administration Controller Leadership Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve leadership information.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format leader data
     * @param array $leader Leader data
     * @return array Formatted leader data
     */
    private function formatLeaderData($leader) {
        // Ensure image URL
        if (empty($leader['image_url'])) {
            $leader['image_url'] = 'https://ui-avatars.com/api/?name=' . 
                                   urlencode($leader['full_name']) . 
                                   '&background=0d47a1&color=fff&size=400';
        }

        // Format date if exists
        if (isset($leader['join_date']) && $leader['join_date']) {
            $leader['formatted_join_date'] = date('F Y', strtotime($leader['join_date']));
        }

        if (isset($leader['created_at']) && $leader['created_at']) {
            $leader['formatted_created_date'] = date('F j, Y', strtotime($leader['created_at']));
        }

        return $leader;
    }

    /**
     * Format organization chart data
     * @param array $orgChart Organization chart data
     * @return array Formatted organization chart
     */
    private function formatOrgChartData($orgChart) {
        // Ensure image URL
        if (empty($orgChart['image_url'])) {
            $orgChart['image_url'] = 'org-chart.png';
        }

        // Format updated date
        if (isset($orgChart['updated_at']) && $orgChart['updated_at']) {
            $orgChart['formatted_updated_at'] = date('F j, Y', strtotime($orgChart['updated_at']));
        }

        return $orgChart;
    }

    /**
     * Add new leadership member
     * @param array $data Leadership data
     * @return array Response array
     */
    public function addLeadership($data) {
        try {
            // Validate required fields
            $required = ['full_name', 'position', 'email'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                    ];
                }
            }

            // Set defaults
            $data['status'] = $data['status'] ?? 'active';
            $data['display_order'] = $data['display_order'] ?? 0;
            $data['role_badge'] = $data['role_badge'] ?? 'Leadership';

            $success = $this->administrationModel->addLeadership($data);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Leadership member added successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to add leadership member.'
                ];
            }

        } catch (Exception $e) {
            error_log("Administration Controller Add Leadership Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to add leadership member.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update leadership member
     * @param int $id Leadership ID
     * @param array $data Updated data
     * @return array Response array
     */
    public function updateLeadership($id, $data) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Leadership ID is required.'
                ];
            }

            // Validate required fields
            $required = ['full_name', 'position', 'email'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                    ];
                }
            }

            $success = $this->administrationModel->updateLeadership($id, $data);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Leadership member updated successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update leadership member.'
                ];
            }

        } catch (Exception $e) {
            error_log("Administration Controller Update Leadership Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update leadership member.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete leadership member
     * @param int $id Leadership ID
     * @return array Response array
     */
    public function deleteLeadership($id) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Leadership ID is required.'
                ];
            }

            $success = $this->administrationModel->deleteLeadership($id);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Leadership member deleted successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete leadership member.'
                ];
            }

        } catch (Exception $e) {
            error_log("Administration Controller Delete Leadership Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete leadership member.',
                'error' => $e->getMessage()
            ];
        }
    }
}
?>