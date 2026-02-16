<?php
/**
 * Leadership Model
 * File: modules/Leadership/models/LeadershipModel.php
 * Handles all database operations for leadership data
 */

class LeadershipModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all leadership years
     * @return array Array of leadership years
     */
    public function getAllYears() {
        try {
            $query = "SELECT * FROM leadership_years 
                      WHERE status = 'active' 
                      ORDER BY display_order ASC, year_start DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Leadership Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get leadership year by ID
     * @param int $yearId Year ID
     * @return array|null Year data or null
     */
    public function getYearById($yearId) {
        try {
            $query = "SELECT * FROM leadership_years WHERE id = :id AND status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $yearId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Leadership Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current year leadership
     * @return array|null Current year data or null
     */
    public function getCurrentYear() {
        try {
            $query = "SELECT * FROM leadership_years WHERE is_current = 1 AND status = 'active' LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Leadership Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get leadership members for a specific year and session
     * @param int $yearId Year ID
     * @param string $session Session type ('day', 'weekend', or 'both')
     * @return array Array of leadership members
     */
    public function getMembersByYear($yearId, $session = null) {
        try {
            $query = "SELECT lm.*, lp.position_name, lp.position_abbr, lp.position_level
                      FROM leadership_members lm
                      INNER JOIN leadership_positions lp ON lm.position_id = lp.id
                      WHERE lm.year_id = :year_id AND lm.status = 'active'";
            
            if ($session) {
                $query .= " AND (lm.session_type = :session OR lm.session_type = 'both')";
            }
            
            $query .= " ORDER BY lp.position_level ASC, lm.display_order ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':year_id', $yearId, PDO::PARAM_INT);
            
            if ($session) {
                $stmt->bindParam(':session', $session, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Leadership Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all leadership positions
     * @return array Array of positions
     */
    public function getAllPositions() {
        try {
            $query = "SELECT * FROM leadership_positions 
                      WHERE status = 'active' 
                      ORDER BY position_level ASC, display_order ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Leadership Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get achievements for a specific year
     * @param int $yearId Year ID
     * @return array Array of achievements
     */
    public function getAchievementsByYear($yearId) {
        try {
            $query = "SELECT * FROM leadership_achievements 
                      WHERE year_id = :year_id AND status = 'active' 
                      ORDER BY display_order ASC, achievement_date DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':year_id', $yearId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Leadership Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get complete leadership data for a year
     * @param int $yearId Year ID
     * @return array Complete leadership data
     */
    public function getCompleteYearData($yearId) {
        try {
            $year = $this->getYearById($yearId);
            
            if (!$year) {
                return null;
            }
            
            $data = [
                'year' => $year,
                'achievements' => $this->getAchievementsByYear($yearId)
            ];
            
            if ($year['has_dual_sessions']) {
                $data['day_session_leaders'] = $this->getMembersByYear($yearId, 'day');
                $data['weekend_session_leaders'] = $this->getMembersByYear($yearId, 'weekend');
            } else {
                $data['leaders'] = $this->getMembersByYear($yearId);
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log("Leadership Model Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get history timeline
     * @return array Array of history milestones
     */
    public function getHistoryTimeline() {
        try {
            $query = "SELECT * FROM cep_history_timeline 
                      WHERE status = 'active' 
                      ORDER BY display_order ASC, year ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Leadership Model Error: " . $e->getMessage());
            return [];
        }
    }
}