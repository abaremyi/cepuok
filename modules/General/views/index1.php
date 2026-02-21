<!doctype html>
<html class="no-js" lang="en">

<?php
/**
 * Membership Page
 * File: modules/General/views/membership.php
 * Provides membership registration form and leader login
 */

// Use ROOT_PATH which is already defined by index.php router
if (!defined('ROOT_PATH')) {
    die('Direct access not allowed. Please access through the main router.');
}

require_once ROOT_PATH . '/config/database.php';

// Get database instance
try {
    $db = Database::getConnection();
} catch (Exception $e) {
    error_log("Membership Page DB Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>

<?php include get_layout('header-2'); ?>

<body data-res-from="1025">
    
    <?php include get_layout('loader'); ?>
    
    <div class="page-wrapper">
        <div class="page-wrapper-inner">
            <header>
                <?php include get_layout('mobile-header'); ?>

                <!-- Header (Navbar for other Devices-->
                <div class="header-inner header-1">
                    <!--Sticky part-->
                    <?php include get_layout('navbar-other'); ?>
                    <!--sticky-outer-->
                </div>
                <!-- .Header (Navbar for other Devices  -->
            </header>

            <style>
                /* ========== MEMBERSHIP PAGE STYLES ========== */
                
                /* Hero Section */
                .membership-hero {
                    position: relative;
                    min-height: 30vh;
                    max-height: 30vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, rgba(12, 23, 45, 0.85), rgba(12, 23, 45, 0.85) ),
                                url("<?= img_url('title-membership.jpg') ?>");
                    background-size: cover;
                    background-position: center;
                    background-attachment: fixed;
                }
                
                .hero-content {
                    position: relative;
                    z-index: 2;
                    text-align: center;
                    color: white;
                    padding: 80px 20px 60px;
                    max-width: 800px;
                    margin: 0 auto;
                }
                
                .hero-icon {
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 25px;
                    background: rgba(212, 175, 55, 0.2);
                    border: 3px solid #D4AF37;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 40px;
                    color: #D4AF37;
                }
                
                .hero-title {
                    font-family: 'Crimson Text', serif;
                    font-size: 48px;
                    font-weight: 700;
                    margin: 0 0 15px 0;
                    color: white;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                }
                
                .hero-subtitle {
                    font-size: 20px;
                    color: rgba(255,255,255,0.95);
                    line-height: 1.6;
                    margin: 0;
                }
                
                /* Tab Navigation */
                .tab-navigation {
                    background: #f8f9fa;
                    padding: 0;
                    border-bottom: 3px solid #e0e0e0;
                    position: sticky;
                    top: 0;
                    z-index: 100;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                
                .tab-nav-container {
                    max-width: 1200px;
                    margin: 0 auto;
                    display: flex;
                    justify-content: center;
                }
                
                .tab-btn {
                    flex: 1;
                    max-width: 400px;
                    padding: 20px 30px;
                    background: white;
                    border: none;
                    border-bottom: 4px solid transparent;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-family: 'Crimson Text', serif;
                    font-size: 20px;
                    font-weight: 600;
                    color: #666;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                }
                
                .tab-btn:hover {
                    background: #f8f9fa;
                    color: #b45816;
                }
                
                .tab-btn.active {
                    background: white;
                    border-bottom-color: #b45816;
                    color: #d96d20;
                }
                
                .tab-btn i {
                    font-size: 24px;
                }
                
                /* Main Content Section */
                .membership-content {
                    background: white;
                    padding: 60px 0;
                    min-height: 70vh;
                }
                
                .content-container {
                    max-width: 1000px;
                    margin: 0 auto;
                    padding: 0 20px;
                }
                
                .tab-content {
                    display: none;
                    animation: fadeIn 0.3s ease;
                }
                
                .tab-content.active {
                    display: block;
                }
                
                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                /* Form Styles */
                .form-section {
                    background: white;
                    padding: 40px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
                }
                
                .section-title {
                    font-family: 'Crimson Text', serif;
                    font-size: 28px;
                    font-weight: 700;
                    color: #d96d20;
                    margin: 0 0 10px 0;
                    text-align: center;
                }
                
                .section-subtitle {
                    text-align: center;
                    color: #666;
                    margin: 0 0 30px 0;
                    font-size: 16px;
                }
                
                .form-group {
                    margin-bottom: 25px;
                }
                
                .form-group label {
                    display: block;
                    font-weight: 600;
                    color: #333;
                    margin-bottom: 8px;
                    font-size: 15px;
                }
                
                .form-group label .required {
                    color: #dc3545;
                    margin-left: 3px;
                }
                
                .form-control {
                    width: 100%;
                    padding: 12px 15px;
                    border: 2px solid #e0e0e0;
                    border-radius: 6px;
                    font-size: 15px;
                    transition: all 0.3s ease;
                    font-family: inherit;
                }
                
                .form-control:focus {
                    outline: none;
                    border-color: #d96d20;
                    box-shadow: 0 0 0 3px rgba(128,0,32,0.1);
                }
                
                .form-control.error {
                    border-color: #dc3545;
                }
                
                .form-row {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                }
                
                .error-message {
                    color: #dc3545;
                    font-size: 13px;
                    margin-top: 5px;
                    display: none;
                }
                
                .error-message.show {
                    display: block;
                }
                
                /* Checkbox and Radio Groups */
                .checkbox-group, .radio-group {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 15px;
                }
                
                .checkbox-item, .radio-item {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .checkbox-item input[type="checkbox"],
                .radio-item input[type="radio"] {
                    width: 18px;
                    height: 18px;
                    cursor: pointer;
                }
                
                .checkbox-item label,
                .radio-item label {
                    margin: 0;
                    cursor: pointer;
                    font-weight: normal;
                }
                
                /* Talent Selection */
                .talents-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                    gap: 15px;
                    max-height: 400px;
                    overflow-y: auto;
                    padding: 20px;
                    background: #f8f9fa;
                    border-radius: 6px;
                    border: 2px solid #e0e0e0;
                }
                
                .talent-category {
                    margin-bottom: 20px;
                }
                
                .talent-category-title {
                    font-weight: 700;
                    color: #d96d20;
                    margin-bottom: 10px;
                    padding: 10px;
                    background: white;
                    border-left: 4px solid #D4AF37;
                }
                
                /* File Upload */
                .file-upload {
                    position: relative;
                    display: inline-block;
                    width: 100%;
                }
                
                .file-upload input[type="file"] {
                    display: none;
                }
                
                .file-upload-label {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                    padding: 15px;
                    border: 2px dashed #e0e0e0;
                    border-radius: 6px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    background: #f8f9fa;
                }
                
                .file-upload-label:hover {
                    border-color: #d96d20;
                    background: white;
                }
                
                .file-upload-label i {
                    font-size: 24px;
                    color: #c7621a;
                }
                
                .file-preview {
                    margin-top: 15px;
                    text-align: center;
                }
                
                .file-preview img {
                    max-width: 200px;
                    max-height: 200px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                
                /* Buttons */
                .btn-primary {
                    background: linear-gradient(135deg, #d96d20, #b35c1f);
                    color: white;
                    padding: 15px 40px;
                    border: none;
                    border-radius: 6px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    width: 100%;
                    margin-top: 10px;
                }
                
                .btn-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(128, 62, 0, 0.3);
                }
                
                .btn-primary:disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                    transform: none;
                }
                
                .btn-secondary {
                    background: white;
                    color: #800020;
                    border: 2px solid #800020;
                    padding: 12px 30px;
                    border-radius: 6px;
                    font-size: 15px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
                
                .btn-secondary:hover {
                    background: #800020;
                    color: white;
                }
                
                /* Login Form Specific */
                .login-form {
                    max-width: 500px;
                    margin: 0 auto;
                }
                
                .forgot-password {
                    text-align: right;
                    margin-top: 10px;
                }
                
                .forgot-password a {
                    color: #800020;
                    text-decoration: none;
                    font-size: 14px;
                }
                
                .forgot-password a:hover {
                    text-decoration: underline;
                }
                
                /* Alert Messages */
                .alert {
                    padding: 15px 20px;
                    border-radius: 6px;
                    margin-bottom: 25px;
                    display: none;
                }
                
                .alert.show {
                    display: block;
                }
                
                .alert-success {
                    background: #d4edda;
                    border: 1px solid #c3e6cb;
                    color: #155724;
                }
                
                .alert-error {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    color: #721c24;
                }
                
                .alert-info {
                    background: #d1ecf1;
                    border: 1px solid #bee5eb;
                    color: #0c5460;
                }
                
                /* Loading Spinner */
                .loading-spinner {
                    display: none;
                    text-align: center;
                    padding: 20px;
                }
                
                .loading-spinner.show {
                    display: block;
                }
                
                .spinner {
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #d96d20;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    animation: spin 1s linear infinite;
                    margin: 0 auto;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                /* Info Box */
                .info-box {
                    background: #f8f9fa;
                    border-left: 4px solid #D4AF37;
                    padding: 20px;
                    margin-bottom: 30px;
                    border-radius: 4px;
                }
                
                .info-box h4 {
                    font-family: 'Crimson Text', serif;
                    font-size: 20px;
                    font-weight: 700;
                    color: #d96d20;
                    margin: 0 0 10px 0;
                }
                
                .info-box ul {
                    margin: 10px 0 0 20px;
                    color: #666;
                }
                
                .info-box ul li {
                    margin-bottom: 5px;
                }
                
                /* Responsive */
                @media (max-width: 768px) {
                    .hero-title {
                        font-size: 36px;
                    }
                    
                    .hero-subtitle {
                        font-size: 16px;
                    }
                    
                    .form-row {
                        grid-template-columns: 1fr;
                    }
                    
                    .tab-nav-container {
                        flex-direction: column;
                    }
                    
                    .tab-btn {
                        max-width: 100%;
                    }
                    
                    .form-section {
                        padding: 25px 20px;
                    }
                    
                    .talents-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
            
            <!-- page-header -->
            <!-- <div class="page-title-wrap typo-white">
                <div class="page-title-wrap-inner section-bg-img" data-bg="img/title-membership.jpg">
					<span class="theme-overlay"></span>
                    <div class="container">
                        <div class="row text-center">
                            <div class="col-md-12">
                                <div class="page-title-inner">
									<div id="breadcrumb" class="breadcrumb margin-bottom-10">
                                        <a href="index-2.html" class="theme-color">Home</a>
                                        <span class="current">Member Registration | Portal Login</span>
                                    </div>
                                    <h1 class="page-title mb-0">Membership</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- page-header -->

            <!-- Hero Section -->
            <section class="membership-hero">
                <div class="hero-content">
                    <h1 class="hero-title">CEP Membership</h1>
                    <p class="hero-subtitle">Join our community or access the leadership portal to manage CEP activities</p>
                </div>
            </section>

            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <div class="tab-nav-container">
                    <button class="tab-btn active" data-tab="register">
                        <i class="fas fa-user-plus"></i>
                        <span>Member Registration</span>
                    </button>
                    <button class="tab-btn" data-tab="login">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Leader Portal Login</span>
                    </button>
                </div>
            </div>

            <!-- Main Content -->
            <section class="membership-content">
                <div class="content-container">
                    
                    <!-- Registration Tab -->
                    <div id="registerTab" class="tab-content active">
                        <div class="form-section">
                            <h2 class="section-title">Become a CEP Member</h2>
                            <p class="section-subtitle">Fill in your details to join our community</p>
                            
                            <div class="info-box">
                                <h4><i class="fas fa-info-circle"></i> Membership Types</h4>
                                <ul>
                                    <li><strong>Current Student & CEP Member:</strong> Currently enrolled students who are active members</li>
                                    <li><strong>POST CEPiens (Alumni):</strong> Former CEP members who have graduated</li>
                                    <li><strong>Frequent Visitor:</strong> Regular visitors who attend CEP events</li>
                                    <li><strong>Donor/Partner:</strong> Financial supporters and ministry partners</li>
                                </ul>
                            </div>

                            <div id="registrationAlert" class="alert"></div>
                            <div id="registrationLoading" class="loading-spinner">
                                <div class="spinner"></div>
                                <p>Processing your registration...</p>
                            </div>

                            <form id="registrationForm" enctype="multipart/form-data">
                                <!-- Membership Type -->
                                <div class="form-group">
                                    <label for="membershipType">Membership Type <span class="required">*</span></label>
                                    <select id="membershipType" name="membership_type_id" class="form-control" required>
                                        <option value="">-- Select Membership Type --</option>
                                    </select>
                                    <div class="error-message" id="membershipTypeError"></div>
                                </div>

                                <!-- Personal Information -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="firstname">First Name <span class="required">*</span></label>
                                        <input type="text" id="firstname" name="firstname" class="form-control" required>
                                        <div class="error-message" id="firstnameError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastname">Last Name <span class="required">*</span></label>
                                        <input type="text" id="lastname" name="lastname" class="form-control" required>
                                        <div class="error-message" id="lastnameError"></div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="email">Email Address <span class="required">*</span></label>
                                        <input type="email" id="email" name="email" class="form-control" required>
                                        <div class="error-message" id="emailError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number <span class="required">*</span></label>
                                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="+250..." required>
                                        <div class="error-message" id="phoneError"></div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="gender">Gender <span class="required">*</span></label>
                                        <select id="gender" name="gender" class="form-control" required>
                                            <option value="">-- Select Gender --</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                        <div class="error-message" id="genderError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="dateOfBirth">Date of Birth</label>
                                        <input type="date" id="dateOfBirth" name="date_of_birth" class="form-control">
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea id="address" name="address" class="form-control" rows="2" placeholder="Street address, city, province..."></textarea>
                                </div>

                                <!-- CEP Information -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="yearJoined">Year Started to Join CEP <span class="required">*</span></label>
                                        <input type="number" id="yearJoined" name="year_joined_cep" class="form-control" 
                                               min="2000" max="<?= date('Y') ?>" required>
                                        <div class="error-message" id="yearJoinedError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="church">Your Church <span class="required">*</span></label>
                                        <select id="church" name="church_id" class="form-control" required>
                                            <option value="">-- Select Church --</option>
                                        </select>
                                        <div class="error-message" id="churchError"></div>
                                    </div>
                                </div>

                                <div class="form-group" id="otherChurchGroup" style="display: none;">
                                    <label for="otherChurch">Please specify church name</label>
                                    <input type="text" id="otherChurch" name="other_church_name" class="form-control">
                                </div>

                                <!-- Spiritual Information -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="bornAgain">Are you born again? <span class="required">*</span></label>
                                        <select id="bornAgain" name="is_born_again" class="form-control" required>
                                            <option value="Prefer not to say">Prefer not to say</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="baptized">Are you baptized? <span class="required">*</span></label>
                                        <select id="baptized" name="is_baptized" class="form-control" required>
                                            <option value="Prefer not to say">Prefer not to say</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Talents/Gifts -->
                                <div class="form-group">
                                    <label>Talents/Gifts/Activities (Select all that apply)</label>
                                    <div id="talentsContainer" class="talents-grid">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>

                                <!-- Bio -->
                                <div class="form-group">
                                    <label for="bio">Tell us about yourself</label>
                                    <textarea id="bio" name="bio" class="form-control" rows="4" 
                                              placeholder="Share your testimony, interests, or how you'd like to contribute to CEP..."></textarea>
                                </div>

                                <!-- Profile Photo -->
                                <div class="form-group">
                                    <label for="profilePhoto">Profile Photo (Optional)</label>
                                    <div class="file-upload">
                                        <input type="file" id="profilePhoto" name="profile_photo" accept="image/*">
                                        <label for="profilePhoto" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Click to upload photo (Max 5MB)</span>
                                        </label>
                                    </div>
                                    <div id="photoPreview" class="file-preview"></div>
                                </div>

                                <button type="submit" class="btn-primary" id="submitBtn">
                                    <i class="fas fa-check-circle"></i> Submit Registration
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Login Tab -->
                    <div id="loginTab" class="tab-content">
                        <div class="form-section login-form">
                            <h2 class="section-title">Leader Portal Login</h2>
                            <p class="section-subtitle">Access the dashboard to manage CEP activities</p>

                            <div id="loginAlert" class="alert"></div>

                            <form id="loginForm">
                                <div class="form-group">
                                    <label for="loginIdentifier">Email, Phone, or Username <span class="required">*</span></label>
                                    <input type="text" id="loginIdentifier" name="identifier" class="form-control" required>
                                    <div class="error-message" id="loginIdentifierError"></div>
                                </div>

                                <div class="form-group">
                                    <label for="loginPassword">Password <span class="required">*</span></label>
                                    <input type="password" id="loginPassword" name="password" class="form-control" required>
                                    <div class="error-message" id="loginPasswordError"></div>
                                </div>

                                <div class="forgot-password">
                                    <a href="<?= url('forgot-password') ?>">Forgot Password?</a>
                                </div>

                                <button type="submit" class="btn-primary" id="loginBtn">
                                    <i class="fas fa-sign-in-alt"></i> Login to Portal
                                </button>
                            </form>

                            <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e0e0e0; text-align: center;">
                                <p style="color: #666; margin-bottom: 15px;">Don't have a leader account yet?</p>
                                <p style="color: #666; font-size: 14px;">
                                    Only CEP members from ADEPR can be granted leader access. 
                                    Please register as a member first, then contact the administrator for leader privileges.
                                </p>
                                <p style="color: #666; font-size: 14px;">Email: admin@cepuok.com | Password: 12345</p>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <?php include get_layout('footer'); ?>
        </div>
    </div>

    <?php include get_layout('scripts'); ?>

    <script>
        jQuery(document).ready(function($) {
            const BASE_URL = '<?= BASE_URL ?>';
            const IMG_URL = '<?= IMG_URL ?>';
            
            // Tab switching
            $('.tab-btn').on('click', function() {
                const targetTab = $(this).data('tab');
                
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                
                $('.tab-content').removeClass('active');
                $(`#${targetTab}Tab`).addClass('active');
                
                // Clear any alerts
                $('.alert').removeClass('show').html('');
            });

            // Load form data
            loadMembershipTypes();
            loadChurches();
            loadTalents();

            // Church selection handler
            $('#church').on('change', function() {
                const selectedOption = $(this).find('option:selected').text();
                if (selectedOption.toLowerCase().includes('other')) {
                    $('#otherChurchGroup').show();
                    $('#otherChurch').attr('required', true);
                } else {
                    $('#otherChurchGroup').hide();
                    $('#otherChurch').attr('required', false);
                }
            });

            // Email validation on blur
            $('#email').on('blur', function() {
                const email = $(this).val();
                if (email) {
                    checkEmailAvailability(email);
                }
            });

            // Profile photo preview
            $('#profilePhoto').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 5 * 1024 * 1024) {
                        showAlert('registrationAlert', 'error', 'File size exceeds 5MB limit');
                        $(this).val('');
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#photoPreview').html(`<img src="${e.target.result}" alt="Preview">`);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Registration form submission
            $('#registrationForm').on('submit', function(e) {
                e.preventDefault();
                
                if (!validateRegistrationForm()) {
                    return;
                }
                
                const formData = new FormData(this);
                
                // Get selected talents
                const selectedTalents = [];
                $('input[name="talents[]"]:checked').each(function() {
                    selectedTalents.push($(this).val());
                });
                formData.append('talents', JSON.stringify(selectedTalents));
                
                $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                $('#registrationLoading').addClass('show');
                
                $.ajax({
                    url: `${BASE_URL}/api/membership?action=register`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#registrationLoading').removeClass('show');
                        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Submit Registration');
                        
                        if (response.success) {
                            showAlert('registrationAlert', 'success', response.message);
                            $('#registrationForm')[0].reset();
                            $('#photoPreview').html('');
                            $('input[name="talents[]"]').prop('checked', false);
                            
                            // Scroll to top
                            $('html, body').animate({ scrollTop: $('.tab-navigation').offset().top }, 500);
                        } else {
                            showAlert('registrationAlert', 'error', response.message);
                        }
                    },
                    error: function(xhr) {
                        $('#registrationLoading').removeClass('show');
                        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Submit Registration');
                        
                        let message = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showAlert('registrationAlert', 'error', message);
                    }
                });
            });

            // Login form submission
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const identifier = $('#loginIdentifier').val();
                const password = $('#loginPassword').val();
                
                if (!identifier || !password) {
                    showAlert('loginAlert', 'error', 'Please fill in all fields');
                    return;
                }
                
                $('#loginBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Logging in...');
                
                $.ajax({
                    url: `${BASE_URL}/api/auth?action=login`,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        identifier: identifier,
                        password: password
                    }),
                    success: function(response) {
                        if (response.success) {
                            showAlert('loginAlert', 'success', 'Login successful! Redirecting...');
                            
                            // Redirect based on role
                            setTimeout(function() {
                                if (response.user.role_name === 'Super Admin') {
                                    window.location.href = `${BASE_URL}/admin/dashboard`;
                                } else {
                                    window.location.href = `${BASE_URL}/dashboard`;
                                }
                            }, 1000);
                        } else {
                            $('#loginBtn').prop('disabled', false).html('<i class="fas fa-sign-in-alt"></i> Login to Portal');
                            showAlert('loginAlert', 'error', response.message);
                        }
                    },
                    error: function(xhr) {
                        $('#loginBtn').prop('disabled', false).html('<i class="fas fa-sign-in-alt"></i> Login to Portal');
                        
                        let message = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showAlert('loginAlert', 'error', message);
                    }
                });
            });

            // Load membership types
            function loadMembershipTypes() {
                $.ajax({
                    url: `${BASE_URL}/api/membership?action=getMembershipTypes`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            response.data.forEach(function(type) {
                                $('#membershipType').append(
                                    `<option value="${type.id}">${type.type_name}</option>`
                                );
                            });
                        }
                    }
                });
            }

            // Load churches
            function loadChurches() {
                $.ajax({
                    url: `${BASE_URL}/api/membership?action=getChurches`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            response.data.forEach(function(church) {
                                $('#church').append(
                                    `<option value="${church.id}">${church.church_name}</option>`
                                );
                            });
                        }
                    }
                });
            }

            // Load talents
            function loadTalents() {
                $.ajax({
                    url: `${BASE_URL}/api/membership?action=getTalents`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            let html = '';
                            
                            for (const category in response.data) {
                                html += `<div class="talent-category">`;
                                html += `<div class="talent-category-title">${category}</div>`;
                                
                                response.data[category].forEach(function(talent) {
                                    html += `
                                        <div class="checkbox-item">
                                            <input type="checkbox" name="talents[]" value="${talent.id}" id="talent_${talent.id}">
                                            <label for="talent_${talent.id}">${talent.talent_name}</label>
                                        </div>
                                    `;
                                });
                                
                                html += `</div>`;
                            }
                            
                            $('#talentsContainer').html(html);
                        }
                    }
                });
            }

            // Check email availability
            function checkEmailAvailability(email) {
                $.ajax({
                    url: `${BASE_URL}/api/membership?action=checkEmail&email=${encodeURIComponent(email)}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.exists) {
                            $('#email').addClass('error');
                            $('#emailError').addClass('show').text('This email is already registered');
                        } else {
                            $('#email').removeClass('error');
                            $('#emailError').removeClass('show').text('');
                        }
                    }
                });
            }

            // Validate registration form
            function validateRegistrationForm() {
                let isValid = true;
                
                // Reset errors
                $('.form-control').removeClass('error');
                $('.error-message').removeClass('show');
                
                // Required fields
                const requiredFields = ['membershipType', 'firstname', 'lastname', 'email', 'phone', 
                                       'gender', 'yearJoined', 'church'];
                
                requiredFields.forEach(function(field) {
                    const $field = $(`#${field}`);
                    if (!$field.val()) {
                        $field.addClass('error');
                        $(`#${field}Error`).addClass('show').text('This field is required');
                        isValid = false;
                    }
                });
                
                // Email format
                const email = $('#email').val();
                if (email && !isValidEmail(email)) {
                    $('#email').addClass('error');
                    $('#emailError').addClass('show').text('Invalid email format');
                    isValid = false;
                }
                
                // Phone format
                const phone = $('#phone').val();
                if (phone && !phone.match(/^\+?[0-9]{10,15}$/)) {
                    $('#phone').addClass('error');
                    $('#phoneError').addClass('show').text('Invalid phone format');
                    isValid = false;
                }
                
                // Year validation
                const year = parseInt($('#yearJoined').val());
                const currentYear = new Date().getFullYear();
                if (year && (year < 2000 || year > currentYear)) {
                    $('#yearJoined').addClass('error');
                    $('#yearJoinedError').addClass('show').text(`Year must be between 2000 and ${currentYear}`);
                    isValid = false;
                }
                
                return isValid;
            }

            // Email validation helper
            function isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            // Show alert helper
            function showAlert(containerId, type, message) {
                const $alert = $(`#${containerId}`);
                $alert.removeClass('alert-success alert-error alert-info')
                      .addClass(`alert-${type}`)
                      .html(`<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`)
                      .addClass('show');
                
                // Auto hide after 10 seconds
                setTimeout(function() {
                    $alert.removeClass('show');
                }, 10000);
            }
        });
    </script>
</body>
</html>