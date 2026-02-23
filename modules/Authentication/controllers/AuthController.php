<?php
/**
 * Auth Controller
 * File: modules/Authentication/controllers/AuthController.php
 * Handles authentication business logic
 */

// Fix the database path - go up 3 levels to reach the root, then to config
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/UserModel.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/helpers/JWTHandler.php';

// Include PHPMailer
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    private $db;
    public $userModel;
    private $jwtHandler;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->userModel = new UserModel($this->db);
        $this->jwtHandler = new JWTHandler();
    }

    /**
     * Login user
     * @param string $identifier Email or phone number
     * @param string $password User password
     * @return array Response array with success, token, and user data
     */
    public function login($identifier, $password) {
        try {
            // Get user from database
            $user = $this->userModel->getUserByEmailOrPhone($identifier);
            
            if (!$user) {
                return [
                    'success' => false, 
                    'message' => 'Invalid credentials',
                ];
            }

            // Check if account is active
            if ($user['status'] !== 'active') {
                return [
                    'success' => false, 
                    'message' => 'Account is ' . $user['status'] . '. Please contact administrator.'
                ];
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                return [
                    'success' => false, 
                    'message' => 'Invalid credentials'
                ];
            }

            // Convert permissions string to array
            $permissions = $user['permissions'] ? explode(',', $user['permissions']) : [];

            // Prepare JWT payload
            $payload = [
                'user_id' => $user['id'],
                'username' => $user['username'] ?: $user['email'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role_id' => $user['role_id'],
                'role_name' => $user['role_name'],
                'is_super_admin' => (bool)$user['is_super_admin'],
                'permissions' => $permissions,
                'photo' => $user['photo'],
                'iat' => time(),
                'exp' => time() + (int)$_ENV['JWT_EXPIRATION_TIME']
            ];

            // Generate JWT token
            $token = $this->jwtHandler->generateToken($payload);

            // Update last login
            $this->userModel->updateLastLogin($user['id']);

            return [
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'firstname' => $user['firstname'],
                    'lastname' => $user['lastname'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'role_id' => $user['role_id'],
                    'role_name' => $user['role_name'],
                    'is_super_admin' => (bool)$user['is_super_admin'],
                    'permissions' => $permissions,
                    'photo' => $user['photo']
                ]
            ];

        } catch (Exception $e) {
            error_log("Auth Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Register new user
     * @param array $data User data
     * @param int $createdBy User ID who created this account
     * @return array Response array
     */
    public function register($data, $createdBy) {
        try {
            // Check if user exists
            if ($this->userModel->userExists($data['email'], $data['phone'])) {
                return [
                    'success' => false, 
                    'message' => 'Email or phone already registered'
                ];
            }

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['created_by'] = $createdBy;
            $data['status'] = $data['status'] ?? 'pending'; // Default status

            // Create user
            $userId = $this->userModel->createUser($data);
            
            if ($userId) {
                // Send welcome email
                $this->sendWelcomeEmail($data['email'], $data['firstname']);
                
                return [
                    'success' => true,
                    'message' => 'User registered successfully. Please check your email for confirmation.',
                    'user_id' => $userId
                ];
            }

            return [
                'success' => false, 
                'message' => 'Failed to register user'
            ];

        } catch (Exception $e) {
            error_log("Auth Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate JWT token
     * @param string $token JWT token
     * @return mixed Decoded token or false
     */
    public function validateToken($token) {
        return $this->jwtHandler->validateToken($token);
    }

    /**
     * Send welcome email to new user
     * @param string $email User email
     * @param string $name User name
     * @return bool Success status
     */
    private function sendWelcomeEmail($email, $name) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'abaremy1997@gmail.com';
            $mail->Password   = 'emnxgufwmehjdiii';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            
            // Recipients
            $mail->setFrom('abaremy1997@gmail.com', 'Mount Carmel School');
            $mail->addAddress($email, $name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Mount Carmel School';
            
            $emailBody = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background-color: #f9f9f9; border-radius: 0 0 10px 10px; }
                    .button { display: inline-block; padding: 12px 30px; background-color: #764ba2; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Welcome to Mount Carmel School!</h1>
                    </div>
                    <div class='content'>
                        <p>Dear {$name},</p>
                        <p>Thank you for registering with Mount Carmel School. We're excited to have you join our community!</p>
                        
                        <p><strong>What's Next?</strong></p>
                        <ul>
                            <li>Your account is currently pending approval</li>
                            <li>Our administrators will review your registration</li>
                            <li>You'll receive another email once your account is activated</li>
                            <li>After activation, you can log in and access all features</li>
                        </ul>
                        
                        <p>If you have any questions, please don't hesitate to contact us.</p>
                        
                        <a href='https://mountcarmel.ac.rw/login' class='button'>Visit Our Website</a>
                    </div>
                    <div class='footer'>
                        <p>Mount Carmel School<br>
                        Email: info@mountcarmel.ac.rw | Phone: +250 787 254 817</p>
                        <p>This is an automated message. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->Body = $emailBody;
            $mail->AltBody = "Welcome to Mount Carmel School, {$name}! Your account has been created and is pending approval. You'll receive a confirmation email once your account is activated.";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Welcome email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Logout user
     * @return array Response array
     */
    public function logout() {
        return [
            'success' => true, 
            'message' => 'Logged out successfully'
        ];
    }

    /**
     * Get user profile
     * @param int $userId User ID
     * @return array Response array with user data
     */
    public function getProfile($userId) {
        try {
            $user = $this->userModel->getUserById($userId);
            
            if (!$user) {
                return [
                    'success' => false, 
                    'message' => 'User not found'
                ];
            }

            // Remove sensitive data
            unset($user['password']);
            
            return [
                'success' => true, 
                'user' => $user
            ];

        } catch (Exception $e) {
            error_log("Auth Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve profile.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user profile
     * @param int $userId User ID
     * @param array $data Updated data
     * @return array Response array
     */
    public function updateProfile($userId, $data) {
        try {
            // Remove fields that shouldn't be updated via profile
            unset($data['password'], $data['role_id'], $data['status']);
            
            $success = $this->userModel->updateUser($userId, $data);
            
            if ($success) {
                return [
                    'success' => true, 
                    'message' => 'Profile updated successfully'
                ];
            }
            
            return [
                'success' => false, 
                'message' => 'Failed to update profile'
            ];

        } catch (Exception $e) {
            error_log("Auth Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update profile.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Change password
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return array Response array
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            $user = $this->userModel->getUserById($userId);
            
            if (!$user) {
                return [
                    'success' => false, 
                    'message' => 'User not found'
                ];
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return [
                    'success' => false, 
                    'message' => 'Current password is incorrect'
                ];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $success = $this->userModel->changePassword($userId, $hashedPassword);
            
            if ($success) {
                return [
                    'success' => true, 
                    'message' => 'Password changed successfully'
                ];
            }
            
            return [
                'success' => false, 
                'message' => 'Failed to change password'
            ];

        } catch (Exception $e) {
            error_log("Auth Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to change password.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Forgot password - Generate OTP
     * @param string $email User email
     * @return array Response array
     */
    public function forgotPassword($email) {
        try {
            $user = $this->userModel->getUserByEmailOrPhone($email);
            
            if (!$user) {
                return [
                    'success' => false, 
                    'message' => 'Email not found'
                ];
            }

            // Generate 6-digit OTP
            $otp = sprintf("%06d", random_int(0, 999999));
            
            // Debug logging
            error_log("Generated OTP for {$email}: {$otp}");
            
            // Store OTP in database (expiry is now handled by MySQL)
            $success = $this->userModel->storeResetToken($user['id'], $otp);
            
            if ($success) {
                // Send OTP via email
                $emailSent = $this->sendOtpEmail($user['email'], $user['firstname'], $otp);
                
                if ($emailSent) {
                    return [
                        'success' => true, 
                        'message' => 'OTP sent to your email. Please check your inbox.',
                        'debug_otp' => $otp // REMOVE THIS IN PRODUCTION - for testing only
                    ];
                } else {
                    return [
                        'success' => false, 
                        'message' => 'OTP generated but failed to send email. Please try again.'
                    ];
                }
            }
            
            return [
                'success' => false, 
                'message' => 'Failed to generate OTP'
            ];

        } catch (Exception $e) {
            error_log("Auth Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process request.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Reset password with OTP
     * @param string $email User email
     * @param string $otp OTP code
     * @param string $newPassword New password
     * @return array Response array
     */
    public function resetPassword($email, $otp, $newPassword) {
        try {
            // Verify OTP
            $userId = $this->userModel->verifyResetToken($email, $otp);
            
            if (!$userId) {
                return [
                    'success' => false, 
                    'message' => 'Invalid or expired OTP'
                ];
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $success = $this->userModel->changePassword($userId, $hashedPassword);
            
            if ($success) {
                // Clear reset token
                $this->userModel->clearResetToken($userId);
                
                // Get user details for email
                $user = $this->userModel->getUserById($userId);
                
                // Send confirmation email
                $this->sendResetConfirmationEmail($user['email'], $user['firstname']);
                
                return [
                    'success' => true, 
                    'message' => 'Password reset successfully. You can now login with your new password.'
                ];
            }
            
            return [
                'success' => false, 
                'message' => 'Failed to reset password'
            ];

        } catch (Exception $e) {
            error_log("Auth Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to reset password.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify OTP without resetting password
     * @param string $email User email
     * @param string $otp OTP code
     * @return array Response array
     */
    public function verifyOtp($email, $otp) {
        try {
            // Debug logging
            error_log("Verifying OTP for email: {$email}, OTP: {$otp}");
            
            $userId = $this->userModel->verifyResetToken($email, $otp);
            
            error_log("User ID from verification: " . ($userId ? $userId : 'NULL'));
            
            if ($userId) {
                return [
                    'success' => true, 
                    'message' => 'OTP verified successfully'
                ];
            }
            
            return [
                'success' => false, 
                'message' => 'Invalid or expired OTP. Please request a new one.'
            ];

        } catch (Exception $e) {
            error_log("Auth Controller Error in verifyOtp: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to verify OTP.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send OTP email for password reset
     * @param string $email User email
     * @param string $name User name
     * @param string $otp OTP code
     * @return bool Success status
     */
    private function sendOtpEmail($email, $name, $otp) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'abaremy1997@gmail.com';
            $mail->Password   = 'emnxgufwmehjdiii';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            
            // Recipients
            $mail->setFrom('abaremy1997@gmail.com', 'Mount Carmel School');
            $mail->addAddress($email, $name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP - Mount Carmel School';
            
            $emailBody = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background-color: #f9f9f9; border-radius: 0 0 10px 10px; }
                    .otp-box { background-color: #764ba2; color: white; font-size: 32px; font-weight: bold; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; letter-spacing: 8px; }
                    .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 15px 0; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Password Reset Request</h1>
                    </div>
                    <div class='content'>
                        <p>Dear {$name},</p>
                        <p>We received a request to reset your password for your Mount Carmel School account.</p>
                        
                        <p><strong>Your OTP (One-Time Password) is:</strong></p>
                        <div class='otp-box'>{$otp}</div>
                        
                        <div class='warning'>
                            <strong>⚠️ Important:</strong>
                            <ul style='margin: 10px 0; padding-left: 20px;'>
                                <li>This OTP is valid for <strong>5 minutes only</strong></li>
                                <li>Do not share this OTP with anyone</li>
                                <li>If you didn't request this, please ignore this email</li>
                            </ul>
                        </div>
                        
                        <p>To reset your password, enter this OTP in the password reset form on our website.</p>
                        
                        <p>If you have any concerns, please contact us immediately.</p>
                    </div>
                    <div class='footer'>
                        <p>Mount Carmel School<br>
                        Email: info@mountcarmel.ac.rw | Phone: +250 787 254 817</p>
                        <p>This is an automated message. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->Body = $emailBody;
            $mail->AltBody = "Your OTP for password reset is: {$otp}. This OTP is valid for 5 minutes only. If you didn't request this, please ignore this email.";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("OTP email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Send password reset confirmation email
     * @param string $email User email
     * @param string $name User name
     * @return bool Success status
     */
    private function sendResetConfirmationEmail($email, $name) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'abaremy1997@gmail.com';
            $mail->Password   = 'emnxgufwmehjdiii';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            
            // Recipients
            $mail->setFrom('abaremy1997@gmail.com', 'Mount Carmel School');
            $mail->addAddress($email, $name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Successfully Reset - Mount Carmel School';
            
            $emailBody = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background-color: #f9f9f9; border-radius: 0 0 10px 10px; }
                    .success-icon { font-size: 64px; text-align: center; color: #28a745; margin: 20px 0; }
                    .button { display: inline-block; padding: 12px 30px; background-color: #764ba2; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                    .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 15px 0; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Password Reset Successful</h1>
                    </div>
                    <div class='content'>
                        <div class='success-icon'>✅</div>
                        
                        <p>Dear {$name},</p>
                        <p>Your password has been successfully reset for your Mount Carmel School account.</p>
                        
                        <p><strong>What's Next?</strong></p>
                        <ul>
                            <li>You can now log in with your new password</li>
                            <li>Make sure to keep your password secure</li>
                            <li>Don't share your password with anyone</li>
                        </ul>
                        
                        <div class='warning'>
                            <strong>⚠️ Security Notice:</strong><br>
                            If you did not make this change, please contact us immediately at info@mountcarmel.ac.rw or call +250 787 254 817
                        </div>
                        
                        <center>
                            <a href='https://mountcarmel.ac.rw/login' class='button'>Login Now</a>
                        </center>
                        
                        <p style='margin-top: 20px;'>Thank you for using Mount Carmel School.</p>
                    </div>
                    <div class='footer'>
                        <p>Mount Carmel School<br>
                        Email: info@mountcarmel.ac.rw | Phone: +250 787 254 817</p>
                        <p>This is an automated message. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->Body = $emailBody;
            $mail->AltBody = "Your password has been successfully reset. You can now login with your new password. If you did not make this change, please contact us immediately.";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Reset confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Get user permissions
     * @param int $userId User ID
     * @return array Permissions array
    */
    public function getUserPermissions($userId) {
        try {
            $query = "SELECT p.module, p.action 
                      FROM users u
                      JOIN roles r ON u.role_id = r.id
                      JOIN role_permissions rp ON r.id = rp.role_id
                      JOIN permissions p ON rp.permission_id = p.id
                      WHERE u.id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $permissions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $permissions[] = $row['module'] . '.' . $row['action'];
            }
            
            return $permissions;
        } catch (PDOException $e) {
            error_log("Error getting user permissions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user session type based on leadership position
     * @param int $userId User ID
     * @return string|null Session type (day/weekend/both)
     */
    public function getUserSessionType($userId) {
        try {
            // Check if user is a leader in current year
            $query = "SELECT lm.session_type 
                      FROM leadership_members lm
                      JOIN leadership_years ly ON lm.year_id = ly.id
                      JOIN members m ON lm.full_name = CONCAT(m.firstname, ' ', m.lastname)
                      WHERE m.user_id = :user_id AND ly.is_current = 1 AND lm.status = 'active'
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['session_type'] : null;
        } catch (PDOException $e) {
            error_log("Error getting user session type: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extend session
     * @return array Response array
     */
    public function extendSession() {
        try {
            // Get token from cookie
            $token = $_COOKIE['auth_token'] ?? '';
            
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'No token provided'
                ];
            }
            
            // Validate current token
            $decoded = $this->jwtHandler->validateToken($token);
            
            if (!$decoded) {
                return [
                    'success' => false,
                    'message' => 'Invalid token'
                ];
            }
            
            // Generate new token with extended expiry
            $payload = [
                'user_id' => $decoded->user_id,
                'username' => $decoded->username,
                'firstname' => $decoded->firstname,
                'lastname' => $decoded->lastname,
                'email' => $decoded->email,
                'phone' => $decoded->phone,
                'role_id' => $decoded->role_id,
                'role_name' => $decoded->role_name,
                'is_super_admin' => $decoded->is_super_admin,
                'permissions' => $decoded->permissions,
                'photo' => $decoded->photo,
                'iat' => time(),
                'exp' => time() + (int)$_ENV['JWT_EXPIRATION_TIME']
            ];
            
            // Generate new token
            $newToken = $this->jwtHandler->generateToken($payload);
            
            // Set new cookie
            setcookie('auth_token', $newToken, [
                'expires' => time() + (int)$_ENV['JWT_EXPIRATION_TIME'],
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            // Update session last activity
            $_SESSION['last_activity'] = time();
            
            return [
                'success' => true,
                'message' => 'Session extended successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Extend session error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to extend session'
            ];
        }
    }
}
?>