<?php
/**
 * Admission Controller
 * File: modules/Admission/controllers/AdmissionController.php
 * Handles admission business logic
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/AdmissionModel.php';

class AdmissionController {
    private $db;
    public $admissionModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->admissionModel = new AdmissionModel($this->db);
    }

    /**
     * Get all admission sections
     * @return array Response array with success and data
     */
    public function getAllSections() {
        try {
            $sections = $this->admissionModel->getAllSections();

            // Format data for display
            foreach ($sections as &$section) {
                // Create URL-friendly slug
                $section['slug'] = strtolower(str_replace(' ', '-', $section['title']));
                
                // Set default icon if not set
                if (empty($section['icon_class'])) {
                    $section['icon_class'] = 'fas fa-info-circle';
                }

                // Format date
                if (isset($section['created_at'])) {
                    $section['formatted_date'] = date('F j, Y', strtotime($section['created_at']));
                }

                // Format content for each section
                if (!empty($section['content'])) {
                    foreach ($section['content'] as &$content) {
                        $content['formatted_content'] = $this->formatContent($content['content'], $content['content_type']);
                        
                        // Get documents for content items
                        $content['documents'] = $this->admissionModel->getDocuments(null, $content['id']);
                    }
                }
                
                // Get documents for sections
                $section['documents'] = $this->admissionModel->getDocuments($section['id'], null);
            }

            return [
                'success' => true,
                'data' => $sections,
                'total' => count($sections)
            ];

        } catch (Exception $e) {
            error_log("Admission Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve admission information.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format content based on type
     * @param mixed $content Content data (could be array or string)
     * @param string $type Content type
     * @return string Formatted HTML content
     */
    private function formatContent($content, $type) {
        // If content is already a string, return it as is
        if (is_string($content)) {
            // Check if it's JSON string
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $content = $decoded;
            } else {
                // If not JSON, just return the string
                return htmlspecialchars($content);
            }
        }
        
        // Now $content should be an array
        if (is_array($content)) {
            switch ($type) {
                case 'requirement':
                    return $this->formatRequirements($content);
                case 'fee_structure':
                    return $this->formatFees($content);
                case 'registration':
                    return $this->formatRegistration($content);
                default:
                    return $this->formatGeneric($content);
            }
        }
        
        // Fallback: return as string
        return htmlspecialchars(is_string($content) ? $content : json_encode($content));
    }

    /**
     * Format requirements content
     * @param array $requirements Requirements data
     * @return string Formatted HTML
     */
    private function formatRequirements($requirements) {
        $html = '';
        
        if (isset($requirements['age'])) {
            $html .= '<div class="info-item"><strong>Age Requirement:</strong> ' . htmlspecialchars($requirements['age']) . '</div>';
        }
        
        if (isset($requirements['documents']) && is_array($requirements['documents'])) {
            $html .= '<div class="info-item"><strong>Required Documents:</strong><ul>';
            foreach ($requirements['documents'] as $document) {
                $html .= '<li>' . htmlspecialchars($document) . '</li>';
            }
            $html .= '</ul></div>';
        }
        
        if (isset($requirements['assessment'])) {
            $html .= '<div class="info-item"><strong>Assessment:</strong> ' . htmlspecialchars($requirements['assessment']) . '</div>';
        }
        
        // Handle additional fields dynamically
        foreach ($requirements as $key => $value) {
            if (!in_array($key, ['age', 'documents', 'assessment'])) {
                if (is_array($value)) {
                    $html .= '<div class="info-item"><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong><ul>';
                    foreach ($value as $item) {
                        $html .= '<li>' . htmlspecialchars($item) . '</li>';
                    }
                    $html .= '</ul></div>';
                } else {
                    $html .= '<div class="info-item"><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . htmlspecialchars($value) . '</div>';
                }
            }
        }
        
        return $html;
    }

    /**
     * Format fee structure content
     * @param array $fees Fee data
     * @return string Formatted HTML
     */
    private function formatFees($fees) {
        $html = '';
        
        if (isset($fees['tuition'])) {
            $html .= '<div class="fee-item"><strong>Tuition Fee:</strong> ' . htmlspecialchars($fees['tuition']) . '</div>';
        }
        
        if (isset($fees['registration'])) {
            $html .= '<div class="fee-item"><strong>Registration Fee:</strong> ' . htmlspecialchars($fees['registration']) . '</div>';
        }
        
        if (isset($fees['materials'])) {
            $html .= '<div class="fee-item"><strong>Materials Fee:</strong> ' . htmlspecialchars($fees['materials']) . '</div>';
        }
        
        if (isset($fees['transport'])) {
            $html .= '<div class="fee-item"><strong>Transport Fee:</strong> ' . htmlspecialchars($fees['transport']) . '</div>';
        }
        
        if (isset($fees['extracurricular'])) {
            $html .= '<div class="fee-item"><strong>Extracurricular Activities:</strong> ' . htmlspecialchars($fees['extracurricular']) . '</div>';
        }
        
        // Handle additional fee fields dynamically
        foreach ($fees as $key => $value) {
            if (!in_array($key, ['tuition', 'registration', 'materials', 'transport', 'extracurricular'])) {
                $html .= '<div class="fee-item"><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . htmlspecialchars($value) . '</div>';
            }
        }
        
        return $html;
    }

    /**
     * Format registration content
     * @param array $registration Registration data
     * @return string Formatted HTML
     */
    private function formatRegistration($registration) {
        $html = '';
        
        if (isset($registration['steps']) && is_array($registration['steps'])) {
            $html .= '<div class="info-item"><strong>Application Steps:</strong><ol>';
            foreach ($registration['steps'] as $step) {
                $html .= '<li>' . htmlspecialchars($step) . '</li>';
            }
            $html .= '</ol></div>';
        }
        
        if (isset($registration['timeline'])) {
            $html .= '<div class="info-item"><strong>Processing Timeline:</strong> ' . htmlspecialchars($registration['timeline']) . '</div>';
        }
        
        if (isset($registration['contact'])) {
            $html .= '<div class="info-item"><strong>Contact for Questions:</strong> ' . htmlspecialchars($registration['contact']) . '</div>';
        }
        
        // Handle additional fields dynamically
        foreach ($registration as $key => $value) {
            if (!in_array($key, ['steps', 'timeline', 'contact'])) {
                if (is_array($value)) {
                    $html .= '<div class="info-item"><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong><ul>';
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            $html .= '<li>' . htmlspecialchars(json_encode($item)) . '</li>';
                        } else {
                            $html .= '<li>' . htmlspecialchars($item) . '</li>';
                        }
                    }
                    $html .= '</ul></div>';
                } else {
                    $html .= '<div class="info-item"><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . htmlspecialchars($value) . '</div>';
                }
            }
        }
        
        return $html;
    }

    /**
     * Format generic content
     * @param array $data Generic data
     * @return string Formatted HTML
     */
    private function formatGeneric($data) {
        $html = '';
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $html .= '<div class="info-item"><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong><ul>';
                foreach ($value as $item) {
                    $html .= '<li>' . htmlspecialchars($item) . '</li>';
                }
                $html .= '</ul></div>';
            } else {
                $html .= '<div class="info-item"><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . htmlspecialchars($value) . '</div>';
            }
        }
        
        return $html;
    }

    /**
     * Get single section by ID
     * @param int $id Section ID
     * @return array Response array
     */
    public function getSectionById($id) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Section ID is required.'
                ];
            }

            $section = $this->admissionModel->getSectionById($id);

            if ($section) {
                $section['slug'] = strtolower(str_replace(' ', '-', $section['title']));
                
                if (isset($section['created_at'])) {
                    $section['formatted_date'] = date('F j, Y', strtotime($section['created_at']));
                }
                
                return [
                    'success' => true,
                    'data' => $section
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Admission section not found.'
                ];
            }

        } catch (Exception $e) {
            error_log("Admission Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve admission section.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get section by slug/title
     * @param string $slug Section slug
     * @return array Response array
     */
    public function getSectionBySlug($slug) {
        try {
            if (empty($slug)) {
                return [
                    'success' => false,
                    'message' => 'Section slug is required.'
                ];
            }

            $section = $this->admissionModel->getSectionBySlug($slug);

            if ($section) {
                $section['slug'] = strtolower(str_replace(' ', '-', $section['title']));
                
                if (isset($section['created_at'])) {
                    $section['formatted_date'] = date('F j, Y', strtotime($section['created_at']));
                }
                
                return [
                    'success' => true,
                    'data' => $section
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Admission section not found.'
                ];
            }

        } catch (Exception $e) {
            error_log("Admission Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve admission section.',
                'error' => $e->getMessage()
            ];
        }
    }
}
?>