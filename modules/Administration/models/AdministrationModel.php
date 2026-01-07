<?php
/**
 * Administration Model
 * File: modules/Administration/models/AdministrationModel.php
 * Handles database operations for administration information
 */

class AdministrationModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all leadership team members
     * @return array Array of leadership members
     */
    public function getAllLeadership() {
        try {
            $query = "SELECT 
                        id,
                        full_name,
                        position,
                        role_badge,
                        short_bio,
                        qualifications,
                        email,
                        phone,
                        image_url,
                        facebook_url,
                        twitter_url,
                        linkedin_url,
                        whatsapp_number,
                        display_order,
                        join_date,
                        status,
                        created_at,
                        updated_at
                      FROM leadership_team 
                      WHERE status = 'active'
                      ORDER BY display_order ASC, full_name ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Administration Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get organization chart
     * @return array|null Organization chart data
     */
    public function getOrganizationChart() {
        try {
            $query = "SELECT 
                        title,
                        description,
                        image_url,
                        updated_at
                      FROM organization_chart 
                      WHERE status = 'active'
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Administration Model Org Chart Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get administration statistics
     * @return array Statistics data
     */
    public function getStatistics() {
        try {
            // Calculate years since establishment (2013)
            $currentYear = date('Y');
            $yearsExperience = $currentYear - 2013;
            
            // Get leadership count
            $query = "SELECT COUNT(*) as total_leadership FROM leadership_team WHERE status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get quick stats
            $statsQuery = "SELECT stat_value FROM quick_stats WHERE stat_name = 'stats_years' AND status = 'active'";
            $statsStmt = $this->db->prepare($statsQuery);
            $statsStmt->execute();
            $quickStats = $statsStmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_leadership' => intval($result['total_leadership']),
                'years_experience' => $yearsExperience,
                'quick_stats_years' => $quickStats['stat_value'] ?? $yearsExperience
            ];
            
        } catch (PDOException $e) {
            error_log("Administration Model Statistics Error: " . $e->getMessage());
            return [
                'total_leadership' => 0,
                'years_experience' => date('Y') - 2013,
                'quick_stats_years' => date('Y') - 2013
            ];
        }
    }

    /**
     * Get single leadership member by ID
     * @param int $id Leadership ID
     * @return array|null Leadership data
     */
    public function getLeadershipById($id) {
        try {
            $query = "SELECT * FROM leadership_team WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Administration Model Get Leadership Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add new leadership member
     * @param array $data Leadership data
     * @return bool Success status
     */
    public function addLeadership($data) {
        try {
            $query = "INSERT INTO leadership_team (
                        full_name, position, role_badge, short_bio, qualifications,
                        email, phone, image_url, facebook_url, twitter_url,
                        linkedin_url, whatsapp_number, display_order, join_date, status
                      ) VALUES (
                        :full_name, :position, :role_badge, :short_bio, :qualifications,
                        :email, :phone, :image_url, :facebook_url, :twitter_url,
                        :linkedin_url, :whatsapp_number, :display_order, :join_date, :status
                      )";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($data);
            
        } catch (PDOException $e) {
            error_log("Administration Model Add Leadership Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update leadership member
     * @param int $id Leadership ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function updateLeadership($id, $data) {
        try {
            $query = "UPDATE leadership_team SET
                        full_name = :full_name,
                        position = :position,
                        role_badge = :role_badge,
                        short_bio = :short_bio,
                        qualifications = :qualifications,
                        email = :email,
                        phone = :phone,
                        image_url = :image_url,
                        facebook_url = :facebook_url,
                        twitter_url = :twitter_url,
                        linkedin_url = :linkedin_url,
                        whatsapp_number = :whatsapp_number,
                        display_order = :display_order,
                        join_date = :join_date,
                        status = :status,
                        updated_at = NOW()
                      WHERE id = :id";
            
            $data['id'] = $id;
            $stmt = $this->db->prepare($query);
            return $stmt->execute($data);
            
        } catch (PDOException $e) {
            error_log("Administration Model Update Leadership Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete leadership member
     * @param int $id Leadership ID
     * @return bool Success status
     */
    public function deleteLeadership($id) {
        try {
            $query = "DELETE FROM leadership_team WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Administration Model Delete Leadership Error: " . $e->getMessage());
            return false;
        }
    }
}
?>