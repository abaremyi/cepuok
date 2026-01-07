<?php
/**
 * Contact Controller
 * File: modules/Contact/controllers/ContactController.php
 * Handles contact form submission and email notifications
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/ContactModel.php';

// Include PHPMailer
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ContactController {
    private $db;
    public $contactModel;
    private $emailConfig;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->contactModel = new ContactModel($this->db);
        
        // Email configuration (matching AuthController)
        $this->emailConfig = [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_auth' => true,
            'smtp_username' => 'abaremy1997@gmail.com',
            'smtp_password' => 'emnxgufwmehjdiii',
            'smtp_secure' => PHPMailer::ENCRYPTION_SMTPS,
            'smtp_port' => 465,
            'from_email' => 'abaremy1997@gmail.com',
            'from_name' => 'Mount Carmel School',
            'admin_email' => 'info@mountcarmel.ac.rw',
            'admin_name' => 'Mount Carmel School Admin'
        ];
    }
    
    /**
     * Handle contact form submission
     * @param array $data Form data
     * @return array Response array
     */
    public function handleContactForm($data) {
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }

        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['phone']) || 
            empty($data['person_type']) || empty($data['subject']) || empty($data['message']) || 
            empty($data['inquiry_type'])) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }

        // Validate phone number (basic validation)
        if (!preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
            return ['success' => false, 'message' => 'Invalid phone number format.'];
        }

        // Save contact message to database
        if ($this->contactModel->saveContactMessage($data)) {
            // Send notification email to school admin
            $adminEmailSent = $this->sendAdminNotificationEmail($data);
            
            // Send confirmation email to visitor
            $clientEmailSent = $this->sendClientConfirmationEmail($data);

            if ($adminEmailSent && $clientEmailSent) {
                return [
                    'success' => true, 
                    'message' => 'Thank you for contacting Mount Carmel School! We have successfully received your inquiry and sent a confirmation to your email. Our team will review your message and respond within 24-72 hours during business days. If you need immediate assistance, please call us at +250 789 121 680.'
                ];
            } else if ($adminEmailSent) {
                return [
                    'success' => true, 
                    'message' => 'Thank you for contacting Mount Carmel School! Your message has been received. We will review it and respond within 24-72 hours. If you do not receive a response within this timeframe, please check your spam folder or call us at +250 789 121 680.'
                ];
            } else {
                return [
                    'success' => true, 
                    'message' => 'Your message has been saved. Due to technical issues with our email system, we could not send a confirmation. However, our team will still contact you within 24-72 hours. For immediate assistance, please call +250 789 121 680.'
                ];
            }
        }

        return ['success' => false, 'message' => 'Failed to send message. Please try again.'];
    }

    /**
     * Send notification email to school admin
     * @param array $data Contact form data
     * @return bool Success status
     */
    private function sendAdminNotificationEmail($data) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->emailConfig['smtp_host'];
            $mail->SMTPAuth   = $this->emailConfig['smtp_auth'];
            $mail->Username   = $this->emailConfig['smtp_username'];
            $mail->Password   = $this->emailConfig['smtp_password'];
            $mail->SMTPSecure = $this->emailConfig['smtp_secure'];
            $mail->Port       = $this->emailConfig['smtp_port'];
            
            // Recipients
            $mail->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name']);
            $mail->addAddress($this->emailConfig['admin_email'], $this->emailConfig['admin_name']);
            $mail->addReplyTo($data['email'], $data['name']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'New Contact Inquiry - ' . $this->getPersonTypeLabel($data['person_type']) . ' - ' . $this->getInquiryTypeLabel($data['inquiry_type']);
            
            $personTypeLabel = $this->getPersonTypeLabel($data['person_type']);
            $inquiryTypeLabel = $this->getInquiryTypeLabel($data['inquiry_type']);
            $currentDateTime = date('F j, Y, g:i a');
            
            $emailBody = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { 
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                        color: #333; 
                        line-height: 1.6;
                        margin: 0;
                        padding: 0;
                    }
                    .container { 
                        max-width: 600px; 
                        margin: 0 auto; 
                        background-color: #ffffff;
                    }
                    .header { 
                        background: linear-gradient(135deg, #00796B 0%, #004D40 100%);
                        color: white; 
                        padding: 30px 20px; 
                        text-align: center; 
                    }
                    .header h1 {
                        margin: 0;
                        font-size: 24px;
                    }
                    .content { 
                        padding: 30px 20px; 
                        background-color: #f9f9f9; 
                    }
                    .field { 
                        margin-bottom: 15px; 
                        padding: 12px;
                        background-color: white;
                        border-left: 4px solid #00796B;
                        border-radius: 4px;
                    }
                    .label { 
                        font-weight: bold; 
                        color: #00796B;
                        display: block;
                        margin-bottom: 5px;
                        font-size: 13px;
                        text-transform: uppercase;
                    }
                    .value {
                        color: #333;
                        font-size: 15px;
                    }
                    .badge-container {
                        display: flex;
                        gap: 10px;
                        margin-bottom: 20px;
                    }
                    .badge {
                        display: inline-block;
                        padding: 6px 12px;
                        color: white;
                        border-radius: 20px;
                        font-size: 12px;
                        font-weight: bold;
                    }
                    .badge-person {
                        background-color: #2196F3;
                    }
                    .badge-inquiry {
                        background-color: #4CAF50;
                    }
                    .urgent-notice {
                        background-color: #fff3cd;
                        border: 1px solid #ffeaa7;
                        border-left: 4px solid #fdcb6e;
                        padding: 15px;
                        margin: 20px 0;
                        border-radius: 4px;
                    }
                    .footer {
                        text-align: center;
                        padding: 20px;
                        color: #666;
                        font-size: 12px;
                        background-color: #f1f1f1;
                    }
                    .message-box {
                        background-color: white;
                        padding: 15px;
                        border-radius: 4px;
                        border: 1px solid #e0e0e0;
                        margin-top: 5px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>📬 New Contact Inquiry</h1>
                        <p style='margin: 10px 0 0 0; font-size: 14px;'>Mount Carmel School Website</p>
                        <p style='margin: 5px 0 0 0; font-size: 12px;'>Received: {$currentDateTime}</p>
                    </div>
                    <div class='content'>
                        <div class='badge-container'>
                            <span class='badge badge-person'>{$personTypeLabel}</span>
                            <span class='badge badge-inquiry'>{$inquiryTypeLabel}</span>
                        </div>
                        
                        <p style='margin-top: 0;'>Hello Admin,</p>
                        <p>A new inquiry has been received through the website contact form. Please review and respond promptly.</p>
                        
                        <div class='field'>
                            <span class='label'>Contact Information</span>
                            <div class='value'>
                                <strong>{$data['name']}</strong><br>
                                📧 {$data['email']}<br>
                                📱 {$data['phone']}
                            </div>
                        </div>
                        
                        <div class='field'>
                            <span class='label'>Subject</span>
                            <span class='value'><strong>{$data['subject']}</strong></span>
                        </div>
                        
                        <div class='field'>
                            <span class='label'>Message</span>
                            <div class='message-box'>
                                <p style='margin: 0; white-space: pre-wrap;'>{$data['message']}</p>
                            </div>
                        </div>
                        
                        <div class='urgent-notice'>
                            <p style='margin: 0 0 10px 0; font-weight: bold;'><i class='fas fa-clock'></i> Response Timeframe</p>
                            <p style='margin: 0;'>
                                • Please respond within <strong>24-72 hours</strong><br>
                                • For admissions inquiries, prioritize within <strong>24 hours</strong><br>
                                • If urgent, consider calling the visitor: <strong>{$data['phone']}</strong>
                            </p>
                        </div>
                        
                        <p style='margin-top: 20px;'>
                            <strong>📝 Action Required:</strong> 
                            <ol style='margin: 10px 0; padding-left: 20px;'>
                                <li>Review this inquiry</li>
                                <li>Respond via email or phone</li>
                                <li>Update the inquiry status in the system</li>
                                <li>If needed, forward to relevant department</li>
                            </ol>
                        </p>
                        
                        <p style='font-size: 13px; color: #666; margin-top: 25px;'>
                            <i>This inquiry was automatically generated from the Mount Carmel School website contact form.</i>
                        </p>
                    </div>
                    <div class='footer'>
                        <p>Mount Carmel School | Kigali, Rwanda</p>
                        <p>Phone: +250 789 121 680 | Email: info@mountcarmel.ac.rw</p>
                        <p>© " . date('Y') . " Mount Carmel School. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->Body = $emailBody;
            $mail->AltBody = "New contact inquiry from {$data['name']} ({$data['email']}, {$data['phone']}). Person Type: {$personTypeLabel}. Inquiry Type: {$inquiryTypeLabel}. Subject: {$data['subject']}. Message: {$data['message']}. Received: {$currentDateTime}. Please respond within 24-72 hours.";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Admin notification email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Send confirmation email to visitor
     * @param array $data Contact form data
     * @return bool Success status
     */
    private function sendClientConfirmationEmail($data) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->emailConfig['smtp_host'];
            $mail->SMTPAuth   = $this->emailConfig['smtp_auth'];
            $mail->Username   = $this->emailConfig['smtp_username'];
            $mail->Password   = $this->emailConfig['smtp_password'];
            $mail->SMTPSecure = $this->emailConfig['smtp_secure'];
            $mail->Port       = $this->emailConfig['smtp_port'];
            
            // Recipients
            $mail->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name']);
            $mail->addAddress($data['email'], $data['name']);
            $mail->addReplyTo($this->emailConfig['admin_email'], 'Mount Carmel School');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Thank You for Contacting Mount Carmel School - Inquiry Received';
            
            $personTypeLabel = $this->getPersonTypeLabel($data['person_type']);
            $inquiryTypeLabel = $this->getInquiryTypeLabel($data['inquiry_type']);
            $currentDateTime = date('F j, Y, g:i a');
            $referenceNumber = 'MC-' . date('Ymd') . '-' . strtoupper(substr(md5($data['email'] . time()), 0, 6));
            
            $emailBody = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { 
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                        color: #333; 
                        line-height: 1.6;
                        margin: 0;
                        padding: 0;
                    }
                    .container { 
                        max-width: 600px; 
                        margin: 0 auto; 
                        background-color: #ffffff;
                    }
                    .header { 
                        background: linear-gradient(135deg, #00796B 0%, #004D40 100%);
                        color: white; 
                        padding: 40px 20px; 
                        text-align: center; 
                    }
                    .header h1 {
                        margin: 0 0 10px 0;
                        font-size: 26px;
                    }
                    .logo {
                        font-size: 48px;
                        margin-bottom: 10px;
                    }
                    .content { 
                        padding: 35px 25px; 
                        background-color: #ffffff; 
                    }
                    .confirmation-box {
                        background-color: #e8f5e9;
                        border: 1px solid #c8e6c9;
                        border-left: 4px solid #4CAF50;
                        padding: 20px;
                        margin: 20px 0;
                        border-radius: 4px;
                    }
                    .reference-box {
                        background-color: #e3f2fd;
                        border: 1px solid #bbdefb;
                        padding: 15px;
                        margin: 20px 0;
                        border-radius: 4px;
                        text-align: center;
                    }
                    .timeline-box {
                        background-color: #fff8e1;
                        border: 1px solid #ffe082;
                        padding: 20px;
                        margin: 25px 0;
                        border-radius: 4px;
                    }
                    .contact-options {
                        background-color: #f3e5f5;
                        border: 1px solid #e1bee7;
                        padding: 20px;
                        margin: 25px 0;
                        border-radius: 4px;
                    }
                    .footer {
                        background-color: #f9f9f9;
                        text-align: center;
                        padding: 30px 20px;
                        color: #666;
                    }
                    .timeline-item {
                        display: flex;
                        align-items: flex-start;
                        margin-bottom: 15px;
                    }
                    .timeline-icon {
                        background-color: #00796B;
                        color: white;
                        width: 30px;
                        height: 30px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-right: 15px;
                        flex-shrink: 0;
                    }
                    .timeline-content {
                        flex: 1;
                    }
                    .contact-item {
                        margin: 12px 0;
                        font-size: 15px;
                    }
                    .contact-item strong {
                        color: #00796B;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <div class='logo'>🎓</div>
                        <h1>Your Inquiry Has Been Received</h1>
                        <p style='margin: 0; font-size: 16px; opacity: 0.95;'>Mount Carmel School - Contact Confirmation</p>
                    </div>
                    <div class='content'>
                        <div class='confirmation-box'>
                            <p style='margin: 0 0 10px 0; font-size: 16px;'><strong>✓ Confirmation Successful!</strong></p>
                            <p style='margin: 0;'>Dear {$data['name']}, thank you for contacting Mount Carmel School. We have successfully received your inquiry and it is now in our system.</p>
                        </div>
                        
                        <div class='reference-box'>
                            <p style='margin: 0; font-size: 14px;'><strong>Reference Number:</strong></p>
                            <p style='margin: 5px 0 0 0; font-size: 20px; font-weight: bold; color: #00796B;'>{$referenceNumber}</p>
                            <p style='margin: 10px 0 0 0; font-size: 12px;'>Please quote this number in any future correspondence.</p>
                        </div>
                        
                        <p><strong>📋 Inquiry Summary:</strong></p>
                        <ul style='margin: 10px 0 20px 0; padding-left: 20px;'>
                            <li><strong>Received:</strong> {$currentDateTime}</li>
                            <li><strong>Your Role:</strong> {$personTypeLabel}</li>
                            <li><strong>Inquiry Type:</strong> {$inquiryTypeLabel}</li>
                            <li><strong>Subject:</strong> {$data['subject']}</li>
                        </ul>
                        
                        <div class='timeline-box'>
                            <p style='margin: 0 0 15px 0; font-weight: bold; color: #5d4037;'><i class='fas fa-calendar-alt'></i> What Happens Next?</p>
                            
                            <div class='timeline-item'>
                                <div class='timeline-icon'>1</div>
                                <div class='timeline-content'>
                                    <p style='margin: 0 0 5px 0; font-weight: bold;'>Immediate (Now)</p>
                                    <p style='margin: 0; font-size: 14px;'>Your inquiry has been logged into our system and assigned to the relevant department.</p>
                                </div>
                            </div>
                            
                            <div class='timeline-item'>
                                <div class='timeline-icon'>2</div>
                                <div class='timeline-content'>
                                    <p style='margin: 0 0 5px 0; font-weight: bold;'>Within 24 Hours</p>
                                    <p style='margin: 0; font-size: 14px;'>Our team will review your inquiry and begin processing it.</p>
                                </div>
                            </div>
                            
                            <div class='timeline-item'>
                                <div class='timeline-icon'>3</div>
                                <div class='timeline-content'>
                                    <p style='margin: 0 0 5px 0; font-weight: bold;'>Within 24-72 Hours</p>
                                    <p style='margin: 0; font-size: 14px;'>You will receive a detailed response from our team via email or phone call.</p>
                                </div>
                            </div>
                            
                            <div style='margin-top: 15px; padding: 10px; background-color: #ffecb3; border-radius: 4px;'>
                                <p style='margin: 0; font-size: 13px; color: #5d4037;'>
                                    <strong>⚠️ Important:</strong> If you don't receive a response within 72 hours, please check your spam folder or contact us directly using the information below.
                                </p>
                            </div>
                        </div>
                        
                        <div class='contact-options'>
                            <p style='margin: 0 0 15px 0; font-weight: bold; color: #7b1fa2;'><i class='fas fa-phone-alt'></i> Need Immediate Assistance?</p>
                            <p style='margin: 0 0 15px 0;'>If your inquiry is urgent, we recommend contacting us directly:</p>
                            
                            <div class='contact-item'>
                                <strong>📞 Phone Support:</strong> +250 789 121 680<br>
                                <span style='font-size: 13px; color: #666;'>Monday - Friday, 8:00 AM - 5:00 PM</span>
                            </div>
                            
                            <div class='contact-item'>
                                <strong>📧 Email:</strong> info@mountcarmel.ac.rw<br>
                                <span style='font-size: 13px; color: #666;'>For general inquiries</span>
                            </div>
                            
                            <div class='contact-item'>
                                <strong>📧 Admissions:</strong> admissions@mountcarmel.ac.rw<br>
                                <span style='font-size: 13px; color: #666;'>For admission-related questions</span>
                            </div>
                            
                            <div class='contact-item'>
                                <strong>📍 Location:</strong> Kigali, Rwanda - Nyarugenge District
                            </div>
                        </div>
                        
                        <p style='margin-top: 25px; font-size: 14px; color: #666;'>
                            <strong>📚 Explore Our Resources:</strong><br>
                            While you wait, you might find these resources helpful:
                        </p>
                        <ul style='margin: 10px 0 20px 0; padding-left: 20px; font-size: 14px;'>
                            <li>Visit our website: <a href='https://mountcarmel.ac.rw' style='color: #00796B;'>mountcarmel.ac.rw</a></li>
                            <li>Check our FAQ section for quick answers</li>
                            <li>View our academic programs and facilities</li>
                        </ul>
                        
                        <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                            Warm regards,<br>
                            <strong>The Mount Carmel School Team</strong><br>
                            <span style='font-size: 13px; color: #666;'>Committed to Excellence in Education</span>
                        </p>
                    </div>
                    <div class='footer'>
                        <p style='font-weight: bold; color: #00796B; margin-bottom: 10px;'>MOUNT CARMEL SCHOOL</p>
                        <p>A Private Christian School | Building Future Leaders Through Excellence in Education</p>
                        <div style='margin: 20px 0;'>
                            <a href='#' style='color: #00796B; text-decoration: none; margin: 0 10px;'>Website</a> | 
                            <a href='#' style='color: #00796B; text-decoration: none; margin: 0 10px;'>Facebook</a> | 
                            <a href='#' style='color: #00796B; text-decoration: none; margin: 0 10px;'>Instagram</a> | 
                            <a href='#' style='color: #00796B; text-decoration: none; margin: 0 10px;'>YouTube</a>
                        </div>
                        <p style='font-size: 11px; margin-top: 20px; color: #999;'>
                            This is an automated confirmation email. Please do not reply directly to this message.<br>
                            If you need to update or cancel your inquiry, please contact us at info@mountcarmel.ac.rw<br>
                            © " . date('Y') . " Mount Carmel School. All rights reserved.
                        </p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->Body = $emailBody;
            $mail->AltBody = "CONFIRMATION: Thank you for contacting Mount Carmel School. We have received your inquiry (Reference: {$referenceNumber}) at {$currentDateTime}. Inquiry Type: {$inquiryTypeLabel}. Your Role: {$personTypeLabel}. Our team will review your message and respond within 24-72 hours. For urgent matters, call +250 789 121 680. If you don't receive a response within 72 hours, please check your spam folder or contact us directly.";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Client confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Get person type label
     * @param string $type Person type code
     * @return string Human-readable label
     */
    private function getPersonTypeLabel($type) {
        $labels = [
            'parent' => 'Current Parent',
            'student' => 'Student',
            'prospective_parent' => 'Prospective Parent',
            'guest' => 'Guest/Visitor',
            'alumni' => 'Alumni',
            'other' => 'Other'
        ];
        return $labels[$type] ?? 'Visitor';
    }

    /**
     * Get inquiry type label
     * @param string $type Inquiry type code
     * @return string Human-readable label
     */
    private function getInquiryTypeLabel($type) {
        $labels = [
            'admissions' => 'Admissions Inquiry',
            'programs' => 'Programs Information',
            'general' => 'General Inquiry',
            'visit' => 'School Visit Request',
            'support' => 'Support Request'
        ];
        return $labels[$type] ?? 'General Inquiry';
    }
}
?>