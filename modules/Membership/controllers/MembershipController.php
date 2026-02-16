<?php
/**
 * Membership Controller
 * File: modules/Membership/controllers/MembershipController.php
 * Handles membership business logic
 */

require_once ROOT_PATH . '/modules/Membership/models/MembershipModel.php';
require_once ROOT_PATH . '/helpers/JWTHandler.php';

class MembershipController
{
    private $membershipModel;
    private $jwtHandler;

    public function __construct()
    {
        $this->membershipModel = new MembershipModel(Database::getInstance());
        $this->jwtHandler = new JWTHandler();
    }

    /**
     * Register new member
     * @param array $data Member data
     * @return array Response with success status and message
     */
    public function register($data)
    {
        try {
            // Validate required fields
            $requiredFields = ['firstname', 'lastname', 'email', 'phone', 'gender', 
                              'year_joined_cep', 'church_id', 'membership_type_id'];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Missing required field: $field"
                    ];
                }
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format'
                ];
            }

            // Check if email already exists
            if ($this->membershipModel->emailExists($data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Email address is already registered'
                ];
            }

            // Check if phone already exists
            if ($this->membershipModel->phoneExists($data['phone'])) {
                return [
                    'success' => false,
                    'message' => 'Phone number is already registered'
                ];
            }

            // Validate year_joined_cep
            $currentYear = (int)date('Y');
            if ($data['year_joined_cep'] < 2000 || $data['year_joined_cep'] > $currentYear) {
                return [
                    'success' => false,
                    'message' => 'Invalid year joined CEP'
                ];
            }

            // Handle profile photo upload if provided
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
                $uploadResult = $this->uploadProfilePhoto($_FILES['profile_photo']);
                if ($uploadResult['success']) {
                    $data['profile_photo'] = $uploadResult['filename'];
                } else {
                    return $uploadResult;
                }
            }

            // Set default status as pending
            $data['status'] = 'pending';

            // Create member
            $memberId = $this->membershipModel->createMember($data);

            if ($memberId) {
                // Add member talents if provided
                if (!empty($data['talents']) && is_array($data['talents'])) {
                    $this->membershipModel->addMemberTalents($memberId, $data['talents']);
                }

                // Log activity
                $this->membershipModel->logActivity($memberId, 'registration', 'Member registered');

                // Send confirmation email (implement this later)
                // $this->sendConfirmationEmail($data['email'], $data['firstname']);

                return [
                    'success' => true,
                    'message' => 'Registration successful! Your membership application is pending approval.',
                    'member_id' => $memberId,
                    'data' => [
                        'email' => $data['email'],
                        'firstname' => $data['firstname'],
                        'lastname' => $data['lastname']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create membership record'
                ];
            }

        } catch (Exception $e) {
            error_log("Membership Controller Error (register): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during registration. Please try again.'
            ];
        }
    }

    /**
     * Upload profile photo
     * @param array $file File data from $_FILES
     * @return array Result with filename or error
     */
    private function uploadProfilePhoto($file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Only JPG, JPEG, and PNG are allowed.'
            ];
        }

        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'message' => 'File size exceeds 5MB limit.'
            ];
        }

        $uploadDir = ROOT_PATH . '/uploads/members/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'member_' . uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => true,
                'filename' => 'uploads/members/' . $filename
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to upload file.'
            ];
        }
    }

    /**
     * Get member by ID
     * @param int $id Member ID
     * @return array Response with member data
     */
    public function getMember($id)
    {
        try {
            $member = $this->membershipModel->getMemberById($id);

            if ($member) {
                return [
                    'success' => true,
                    'data' => $member
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Member not found'
                ];
            }
        } catch (Exception $e) {
            error_log("Membership Controller Error (getMember): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while fetching member data'
            ];
        }
    }

    /**
     * Get all members with filters and pagination
     * @param array $filters Filter criteria
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return array Response with members data
     */
    public function getAllMembers($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $members = $this->membershipModel->getAllMembers($filters, $perPage, $offset);
            $total = $this->membershipModel->countMembers($filters);

            return [
                'success' => true,
                'data' => $members,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ];
        } catch (Exception $e) {
            error_log("Membership Controller Error (getAllMembers): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while fetching members'
            ];
        }
    }

    /**
     * Update member
     * @param int $id Member ID
     * @param array $data Updated data
     * @return array Response with success status
     */
    public function updateMember($id, $data)
    {
        try {
            // Check if member exists
            $member = $this->membershipModel->getMemberById($id);
            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Member not found'
                ];
            }

            // Validate email if changed
            if (isset($data['email']) && $data['email'] !== $member['email']) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    return [
                        'success' => false,
                        'message' => 'Invalid email format'
                    ];
                }

                if ($this->membershipModel->emailExists($data['email'], $id)) {
                    return [
                        'success' => false,
                        'message' => 'Email address is already registered'
                    ];
                }
            }

            // Validate phone if changed
            if (isset($data['phone']) && $data['phone'] !== $member['phone']) {
                if ($this->membershipModel->phoneExists($data['phone'], $id)) {
                    return [
                        'success' => false,
                        'message' => 'Phone number is already registered'
                    ];
                }
            }

            // Handle profile photo upload if provided
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
                $uploadResult = $this->uploadProfilePhoto($_FILES['profile_photo']);
                if ($uploadResult['success']) {
                    $data['profile_photo'] = $uploadResult['filename'];
                    
                    // Delete old photo if exists
                    if (!empty($member['profile_photo']) && file_exists(ROOT_PATH . '/' . $member['profile_photo'])) {
                        unlink(ROOT_PATH . '/' . $member['profile_photo']);
                    }
                }
            }

            // Update member
            $result = $this->membershipModel->updateMember($id, $data);

            if ($result) {
                // Update talents if provided
                if (isset($data['talents'])) {
                    // Delete existing talents
                    $this->membershipModel->deleteMemberTalents($id);
                    
                    // Add new talents
                    if (!empty($data['talents'])) {
                        $this->membershipModel->addMemberTalents($id, $data['talents']);
                    }
                }

                // Log activity
                $this->membershipModel->logActivity($id, 'profile_update', 'Member profile updated');

                return [
                    'success' => true,
                    'message' => 'Member updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update member'
                ];
            }

        } catch (Exception $e) {
            error_log("Membership Controller Error (updateMember): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while updating member'
            ];
        }
    }

    /**
     * Approve member
     * @param int $id Member ID
     * @param int $approvedBy User ID who approved
     * @return array Response with success status
     */
    public function approveMember($id, $approvedBy)
    {
        try {
            $member = $this->membershipModel->getMemberById($id);
            
            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Member not found'
                ];
            }

            if ($member['status'] !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Only pending members can be approved'
                ];
            }

            $result = $this->membershipModel->approveMember($id, $approvedBy);

            if ($result) {
                // Log activity
                $this->membershipModel->logActivity($id, 'status_change', 'Member approved');

                // Send approval email
                // $this->sendApprovalEmail($member['email'], $member['firstname']);

                return [
                    'success' => true,
                    'message' => 'Member approved successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to approve member'
                ];
            }

        } catch (Exception $e) {
            error_log("Membership Controller Error (approveMember): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while approving member'
            ];
        }
    }

    /**
     * Delete member
     * @param int $id Member ID
     * @return array Response with success status
     */
    public function deleteMember($id)
    {
        try {
            $member = $this->membershipModel->getMemberById($id);
            
            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Member not found'
                ];
            }

            // Delete profile photo if exists
            if (!empty($member['profile_photo']) && file_exists(ROOT_PATH . '/' . $member['profile_photo'])) {
                unlink(ROOT_PATH . '/' . $member['profile_photo']);
            }

            $result = $this->membershipModel->deleteMember($id);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Member deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete member'
                ];
            }

        } catch (Exception $e) {
            error_log("Membership Controller Error (deleteMember): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while deleting member'
            ];
        }
    }

    /**
     * Get membership types
     * @return array Response with membership types
     */
    public function getMembershipTypes()
    {
        try {
            $types = $this->membershipModel->getMembershipTypes();
            return [
                'success' => true,
                'data' => $types
            ];
        } catch (Exception $e) {
            error_log("Membership Controller Error (getMembershipTypes): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch membership types'
            ];
        }
    }

    /**
     * Get churches
     * @return array Response with churches
     */
    public function getChurches()
    {
        try {
            $churches = $this->membershipModel->getChurches();
            return [
                'success' => true,
                'data' => $churches
            ];
        } catch (Exception $e) {
            error_log("Membership Controller Error (getChurches): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch churches'
            ];
        }
    }

    /**
     * Get talents
     * @return array Response with talents
     */
    public function getTalents()
    {
        try {
            $talents = $this->membershipModel->getTalents();
            return [
                'success' => true,
                'data' => $talents
            ];
        } catch (Exception $e) {
            error_log("Membership Controller Error (getTalents): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch talents'
            ];
        }
    }

    /**
     * Get membership statistics
     * @return array Response with statistics
     */
    public function getStatistics()
    {
        try {
            $stats = $this->membershipModel->getStatistics();
            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            error_log("Membership Controller Error (getStatistics): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch statistics'
            ];
        }
    }

    /**
     * Check if email exists
     * @param string $email Email to check
     * @return array Response with exists status
     */
    public function checkEmail($email)
    {
        try {
            $exists = $this->membershipModel->emailExists($email);
            return [
                'success' => true,
                'exists' => $exists
            ];
        } catch (Exception $e) {
            error_log("Membership Controller Error (checkEmail): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to check email'
            ];
        }
    }
}