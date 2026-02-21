<?php
/**
 * User Controller - For admin user management
 * File: modules/Authentication/controllers/UserController.php
 */

require_once __DIR__ . '/../models/UserModel.php';
require_once ROOT_PATH . '/helpers/UploadHelper.php';

class UserController {
    private $userModel;
    private $uploadHelper;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->uploadHelper = new UploadHelper();
    }

    /**
     * Get all users with filters
     */
    public function index($filters = []) {
        try {
            return $this->userModel->getAllUsers($filters);
        } catch (Exception $e) {
            error_log("UserController::index - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single user
     */
    public function show($id) {
        try {
            return $this->userModel->getUserById($id);
        } catch (Exception $e) {
            error_log("UserController::show - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new user (admin)
     */
    public function store($data, $files = null) {
        try {
            // Validate required fields
            $required = ['firstname', 'lastname', 'email'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception(ucfirst($field) . " is required");
                }
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            // Check if email exists
            if ($this->userModel->emailExists($data['email'])) {
                throw new Exception("Email already exists");
            }

            // Check username if provided
            if (!empty($data['username']) && $this->userModel->usernameExists($data['username'])) {
                throw new Exception("Username already exists");
            }

            // Handle photo upload
            if ($files && isset($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadHelper->uploadFile($files['photo'], 'users');
                if ($uploadResult['success']) {
                    $data['photo'] = $uploadResult['filepath'];
                }
            }

            // Hash password if provided
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            // Set default values
            $data['status'] = $data['status'] ?? 'pending';
            $data['email_verified'] = $data['email_verified'] ?? 0;
            $data['is_adepr_member'] = $data['is_adepr_member'] ?? 0;
            $data['can_manage_website'] = $data['can_manage_website'] ?? 0;
            
            // Set created_by from session
            session_start();
            $data['created_by'] = $_SESSION['user_id'] ?? null;

            // Generate username if not provided
            if (empty($data['username'])) {
                $data['username'] = $this->generateUsername($data['firstname'], $data['lastname']);
            }

            // Create user
            $userId = $this->userModel->createUser($data);

            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId
            ];

        } catch (Exception $e) {
            error_log("UserController::store - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user
     */
    public function update($id, $data, $files = null) {
        try {
            // Check if user exists
            $existingUser = $this->userModel->getUserById($id);
            if (!$existingUser) {
                throw new Exception("User not found");
            }

            // Don't allow editing super admin
            if ($existingUser['is_super_admin'] && (!isset($data['is_super_admin']) || !$data['is_super_admin'])) {
                throw new Exception("Cannot modify super admin user");
            }

            // Validate email if being updated
            if (isset($data['email']) && $data['email'] !== $existingUser['email']) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format");
                }
                if ($this->userModel->emailExists($data['email'], $id)) {
                    throw new Exception("Email already exists");
                }
            }

            // Check username if provided and changed
            if (!empty($data['username']) && $data['username'] !== $existingUser['username']) {
                if ($this->userModel->usernameExists($data['username'], $id)) {
                    throw new Exception("Username already exists");
                }
            }

            // Handle photo upload
            if ($files && isset($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadHelper->uploadFile($files['photo'], 'users');
                if ($uploadResult['success']) {
                    $data['photo'] = $uploadResult['filepath'];
                    
                    // Delete old photo
                    if (!empty($existingUser['photo'])) {
                        $this->uploadHelper->deleteFile($existingUser['photo']);
                    }
                }
            }

            // Remove photo if requested
            if (isset($data['remove_photo']) && $data['remove_photo'] == '1') {
                if (!empty($existingUser['photo'])) {
                    $this->uploadHelper->deleteFile($existingUser['photo']);
                }
                $data['photo'] = null;
            }

            // Update user
            $result = $this->userModel->updateUser($id, $data);

            return [
                'success' => $result,
                'message' => $result ? 'User updated successfully' : 'No changes made'
            ];

        } catch (Exception $e) {
            error_log("UserController::update - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete user
     */
    public function destroy($id) {
        try {
            // Check if user exists
            $user = $this->userModel->getUserById($id);
            if (!$user) {
                throw new Exception("User not found");
            }

            // Delete photo if exists
            if (!empty($user['photo'])) {
                $this->uploadHelper->deleteFile($user['photo']);
            }

            $result = $this->userModel->deleteUser($id);

            return [
                'success' => $result,
                'message' => $result ? 'User deleted successfully' : 'Failed to delete user'
            ];

        } catch (Exception $e) {
            error_log("UserController::destroy - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user status
     */
    public function updateStatus($id, $status) {
        try {
            $result = $this->userModel->updateStatus($id, $status);
            
            return [
                'success' => $result,
                'message' => $result ? 'Status updated successfully' : 'Failed to update status'
            ];

        } catch (Exception $e) {
            error_log("UserController::updateStatus - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user statistics
     */
    public function getStats() {
        try {
            return $this->userModel->getUserStats();
        } catch (Exception $e) {
            error_log("UserController::getStats - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all roles for dropdown
     */
    public function getRoles() {
        try {
            return $this->userModel->getAllRoles();
        } catch (Exception $e) {
            error_log("UserController::getRoles - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available members for linking
     */
    public function getAvailableMembers() {
        try {
            return $this->userModel->getAvailableMembers();
        } catch (Exception $e) {
            error_log("UserController::getAvailableMembers - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate username from first and last name
     */
    private function generateUsername($firstname, $lastname) {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstname . '.' . $lastname));
        $username = $base;
        $counter = 1;
        
        while ($this->userModel->usernameExists($username)) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }
}