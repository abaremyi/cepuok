<?php
/**
 * Contact Model
 * File: modules/Contact/models/ContactModel.php
 * Handles database operations for contact messages
 */

class ContactModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Save contact message to database
     * @param array $data Contact form data
     * @return bool Success status
     */
    public function saveContactMessage($data) {
        try {
            $query = "INSERT INTO contact_messages 
                      (name, email, phone, subject, message, person_type, inquiry_type, created_at) 
                      VALUES (:name, :email, :phone, :subject, :message, :person_type, :inquiry_type, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
            $stmt->bindParam(':subject', $data['subject'], PDO::PARAM_STR);
            $stmt->bindParam(':message', $data['message'], PDO::PARAM_STR);
            $stmt->bindParam(':person_type', $data['person_type'], PDO::PARAM_STR);
            $stmt->bindParam(':inquiry_type', $data['inquiry_type'], PDO::PARAM_STR);
         
            if ($stmt->execute()) {
                return true; 
            } else {
                error_log("Database error: " . implode(", ", $stmt->errorInfo()));
                return false; 
            }
        } catch (PDOException $e) {
            error_log("PDO Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all contact messages with pagination
     * @param int $limit Number of messages per page
     * @param int $offset Starting position
     * @return array Array of contact messages
     */
    public function getAllMessages($limit = 20, $offset = 0) {
        try {
            $query = "SELECT * FROM contact_messages 
                      ORDER BY created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Contact Model Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of messages
     * @return int Total count
     */
    public function getTotalCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM contact_messages";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("Contact Model Error: " . $e->getMessage());
            return 0;
        }
    }
}
?>