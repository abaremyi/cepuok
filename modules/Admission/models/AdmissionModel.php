<?php
/**
 * Admission Model
 * File: modules/Admission/models/AdmissionModel.php
 * Handles database operations for admission information
 */

class AdmissionModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all active admission sections
     * @return array Array of admission sections
     */
    public function getAllSections() {
        try {
            $query = "SELECT 
                        id,
                        title,
                        subtitle,
                        icon_class,
                        display_order,
                        created_at
                      FROM admission_sections 
                      WHERE is_active = 1
                      ORDER BY display_order ASC, id ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get content for each section
            foreach ($sections as &$section) {
                $section['content'] = $this->getSectionContent($section['id']);
            }
            
            return $sections;
            
        } catch (PDOException $e) {
            error_log("Admission Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get content for a specific section
     * @param int $sectionId Section ID
     * @return array Array of content items
     */
    private function getSectionContent($sectionId) {
        try {
            $query = "SELECT 
                        id,
                        content_type,
                        title,
                        content,
                        icon,
                        display_order,
                        metadata,
                        created_at
                      FROM admission_content 
                      WHERE section_id = :section_id 
                      AND is_active = 1
                      ORDER BY display_order ASC, id ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
            $stmt->execute();
            
            $contentItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($contentItems as &$item) {
                if (!empty($item['content'])) {
                    $decoded = json_decode($item['content'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $item['content'] = $decoded;
                    }
                }
                
                if (!empty($item['metadata'])) {
                    $decoded = json_decode($item['metadata'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $item['metadata'] = $decoded;
                    }
                }
            }
            
            return $contentItems;
            
        } catch (PDOException $e) {
            error_log("Admission Model Content Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single section by ID
     * @param int $id Section ID
     * @return array|null Section data or null
     */
    public function getSectionById($id) {
        try {
            $query = "SELECT * FROM admission_sections WHERE id = :id AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $section = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($section) {
                $section['content'] = $this->getSectionContent($section['id']);
            }
            
            return $section;
            
        } catch (PDOException $e) {
            error_log("Admission Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get section by slug/title
     * @param string $slug Section slug
     * @return array|null Section data or null
     */
    public function getSectionBySlug($slug) {
        try {
            $searchSlug = str_replace('-', ' ', $slug);
            
            $query = "SELECT * FROM admission_sections 
                      WHERE LOWER(REPLACE(title, ' ', '-')) = LOWER(:slug) 
                      AND is_active = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmt->execute();
            
            $section = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($section) {
                $section['content'] = $this->getSectionContent($section['id']);
            }
            
            return $section;
            
        } catch (PDOException $e) {
            error_log("Admission Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get documents for a section or content item
     * @param int|null $section_id Section ID
     * @param int|null $content_id Content ID
     * @return array Array of documents
    */
    public function getDocuments($section_id = null, $content_id = null) {
        try {
            if ($section_id && $content_id) {
                $query = "SELECT * FROM admission_documents 
                        WHERE (section_id = :section_id OR content_id = :content_id) 
                        AND is_active = 1 
                        ORDER BY display_order ASC, created_at DESC";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
                $stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
            } elseif ($section_id) {
                $query = "SELECT * FROM admission_documents 
                        WHERE section_id = :section_id 
                        AND is_active = 1 
                        ORDER BY display_order ASC, created_at DESC";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
            } elseif ($content_id) {
                $query = "SELECT * FROM admission_documents 
                        WHERE content_id = :content_id 
                        AND is_active = 1 
                        ORDER BY display_order ASC, created_at DESC";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
            } else {
                return [];
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Admission Model Documents Error: " . $e->getMessage());
            return [];
        }
    }
}
?>