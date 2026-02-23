<?php
/**
 * Membership Page - CEPUOK
 * File: modules/General/views/membership.php
 * Public page for: Member Registration & Leadership Login
 * New fields: cep_session, faculty, program, academic_year, church_name (text)
 */

if (!defined('ROOT_PATH')) {
    die('Direct access not allowed.');
}
require_once ROOT_PATH . '/config/database.php';
try { $db = Database::getConnection(); } catch (Exception $e) { die("Database error."); }
?>
<!doctype html>
<html class="no-js" lang="en">
<?php include get_layout('header-2'); ?>
<body data-res-from="1025">
<?php include get_layout('loader'); ?>

<div class="page-wrapper">
  <div class="page-wrapper-inner">
    <header>
      <?php include get_layout('mobile-header'); ?>
      <div class="header-inner header-1">
        <?php include get_layout('navbar-other'); ?>
      </div>
    </header>

<style>
/* ========== MEMBERSHIP PAGE STYLES v2.1 ========== */
:root {
  --cep-orange: #d96d20;
  --cep-dark: #0c172d;
  --cep-light-orange: #f5e6d8;
  --cep-day-color: #fd7e14;
  --cep-weekend-color: #0d6efd;
}

.membership-hero {
  position: relative;
  min-height: 32vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(12,23,45,0.88), rgba(180,88,22,0.75)),
              url("<?= img_url('title-membership.JPG') ?>");
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
}
.hero-content {
  position: relative;
  z-index: 2;
  text-align: center;
  color: white;
  padding: 90px 20px 60px;
}
.hero-title {
  font-family: 'Crimson Text', serif;
  font-size: 48px;
  font-weight: 700;
  margin: 0 0 12px;
  text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
}
.hero-subtitle { font-size: 18px; color: rgba(255,255,255,0.9); margin: 0; }

/* Tab Navigation */
.tab-navigation {
  background: #fff;
  border-bottom: 3px solid #e0e0e0;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
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
  padding: 18px 30px;
  background: #f8f9fa;
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
.tab-btn:hover { background: #f0f0f0; color: var(--cep-orange); }
.tab-btn.active { background: white; border-bottom-color: var(--cep-orange); color: var(--cep-orange); }

/* Main content */
.membership-content { background: #f4f6f9; padding: 50px 0; min-height: 70vh; }
.content-container { max-width: 960px; margin: 0 auto; padding: 0 20px; }
.tab-content { display: none; animation: fadeSlideIn 0.3s ease; }
.tab-content.active { display: block; }
@keyframes fadeSlideIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Form Section */
.form-section {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.07);
  margin-bottom: 24px;
}
.section-header {
  text-align: center;
  margin-bottom: 32px;
  padding-bottom: 20px;
  border-bottom: 2px solid #f0f0f0;
}
.section-title {
  font-family: 'Crimson Text', serif;
  font-size: 28px;
  font-weight: 700;
  color: var(--cep-orange);
  margin: 0 0 8px;
}
.section-subtitle { color: #777; font-size: 15px; margin: 0; }

/* Step indicator */
.step-indicator {
  display: flex;
  justify-content: center;
  gap: 0;
  margin: 0 0 36px;
  background: #f8f9fa;
  border-radius: 50px;
  padding: 4px;
  max-width: 480px;
  margin-left: auto;
  margin-right: auto;
}
.step-item {
  flex: 1;
  padding: 10px 20px;
  border-radius: 50px;
  text-align: center;
  font-size: 13px;
  font-weight: 600;
  color: #999;
  cursor: default;
  transition: all 0.3s;
}
.step-item.active {
  background: var(--cep-orange);
  color: white;
  box-shadow: 0 2px 10px rgba(217,109,32,0.35);
}
.step-item.completed { color: var(--cep-orange); }
.step-item.completed::before { content: '✓ '; }

/* Form Groups */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-row.three { grid-template-columns: 1fr 1fr 1fr; }
@media (max-width: 640px) { .form-row, .form-row.three { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 20px; }
.form-group label {
  display: block;
  font-weight: 600;
  color: #333;
  margin-bottom: 7px;
  font-size: 14px;
}
.form-group label .required { color: #e74c3c; margin-left: 3px; }
.form-control {
  width: 100%;
  padding: 11px 14px;
  border: 1.5px solid #ddd;
  border-radius: 8px;
  font-size: 15px;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
  background: #fafafa;
}
.form-control:focus {
  border-color: var(--cep-orange);
  box-shadow: 0 0 0 3px rgba(217,109,32,0.12);
  outline: none;
  background: white;
}
.form-control.error { border-color: #e74c3c; }
.error-message { color: #e74c3c; font-size: 12px; margin-top: 4px; display: none; }
.error-message.show { display: block; }

/* Session selector - special styling */
.session-selector {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-top: 4px;
}
.session-option { position: relative; }
.session-option input[type="radio"] { position: absolute; opacity: 0; }
.session-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px 16px;
  border: 2px solid #ddd;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.25s;
  text-align: center;
  background: #fafafa;
}
.session-label:hover { border-color: var(--cep-orange); background: var(--cep-light-orange); }
.session-option input[type="radio"]:checked + .session-label {
  border-color: var(--cep-orange);
  background: var(--cep-light-orange);
  box-shadow: 0 2px 10px rgba(217,109,32,0.2);
}
.session-icon { font-size: 32px; margin-bottom: 8px; }
.session-name { font-weight: 700; font-size: 16px; color: #333; }
.session-desc { font-size: 12px; color: #777; margin-top: 3px; }
.session-badge {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 20px;
  margin-top: 5px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.badge-day { background: #fff3e0; color: var(--cep-day-color); }
.badge-weekend { background: #e8f0fe; color: var(--cep-weekend-color); }

/* Talent checkboxes */
.talent-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px; }
.talent-category { margin-bottom: 16px; }
.talent-category-title { font-weight: 700; font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
.checkbox-item { display: flex; align-items: center; gap: 8px; padding: 6px 10px; border-radius: 6px; transition: background 0.2s; }
.checkbox-item:hover { background: #f5f5f5; }
.checkbox-item input { width: 16px; height: 16px; accent-color: var(--cep-orange); }
.checkbox-item label { font-size: 14px; color: #444; cursor: pointer; margin: 0; }

/* Navigation buttons */
.form-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 30px;
  padding-top: 24px;
  border-top: 1px solid #eee;
}
.btn-primary-cep {
  padding: 12px 32px;
  background: var(--cep-orange);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.btn-primary-cep:hover { background: #b85c18; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(217,109,32,0.3); }
.btn-primary-cep:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.btn-outline-cep {
  padding: 12px 28px;
  background: white;
  color: var(--cep-orange);
  border: 2px solid var(--cep-orange);
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-outline-cep:hover { background: var(--cep-light-orange); }

/* Alerts */
.alert-box {
  padding: 14px 18px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: none;
  align-items: center;
  gap: 10px;
  font-weight: 500;
}
.alert-box.show { display: flex; }
.alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
.alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
.alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }

/* Login Form */
.login-card {
  background: white;
  border-radius: 14px;
  padding: 48px 48px 40px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.1);
  max-width: 480px;
  margin: 0 auto;
}
.login-logo {
  text-align: center;
  margin-bottom: 28px;
}
.login-logo img { height: 64px; }
.login-heading { font-family: 'Crimson Text', serif; font-size: 30px; font-weight: 700; color: #1a1a1a; margin: 0 0 6px; text-align: center; }
.login-subheading { color: #888; font-size: 14px; text-align: center; margin: 0 0 28px; }
.login-divider { height: 1px; background: #eee; margin: 20px 0; position: relative; }
.login-divider span {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  padding: 0 12px;
  color: #aaa;
  font-size: 13px;
}
.forgot-link { font-size: 13px; color: var(--cep-orange); text-decoration: none; }
.forgot-link:hover { text-decoration: underline; }

/* Forgot Password Modal */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(3px);
}
.modal-overlay.show { display: flex; }
.modal-card {
  background: white;
  border-radius: 14px;
  padding: 40px;
  max-width: 440px;
  width: 90%;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}
.modal-title { font-family: 'Crimson Text', serif; font-size: 24px; font-weight: 700; color: #222; margin: 0; }
.modal-close { background: none; border: none; font-size: 22px; cursor: pointer; color: #aaa; padding: 0 4px; }
.modal-close:hover { color: #333; }
.modal-step { display: none; }
.modal-step.active { display: block; }

/* Success state */
.success-card {
  text-align: center;
  padding: 40px 20px;
  animation: fadeSlideIn 0.4s ease;
}
.success-icon { font-size: 64px; color: #28a745; margin-bottom: 16px; }
.success-title { font-family: 'Crimson Text', serif; font-size: 28px; font-weight: 700; color: #333; }
.success-text { color: #666; margin: 8px 0 24px; }

/* Info note */
.info-note {
  background: #f0f7ff;
  border-left: 4px solid #0d6efd;
  padding: 12px 16px;
  border-radius: 0 8px 8px 0;
  font-size: 13px;
  color: #444;
  margin-bottom: 20px;
}
.info-note i { color: #0d6efd; margin-right: 6px; }

/* Input with icon */
.input-group-cep { position: relative; }
.input-group-cep .form-control { padding-left: 42px; }
.input-group-cep .input-icon {
  position: absolute;
  left: 13px;
  top: 50%;
  transform: translateY(-50%);
  color: #aaa;
  font-size: 16px;
}
.input-group-cep .toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #aaa;
  cursor: pointer;
  font-size: 16px;
}
.input-group-cep .toggle-password:hover { color: #555; }
</style>

<!-- HERO -->
<section class="membership-hero">
  <div class="hero-content">
    <h1 class="hero-title"><i class="fas fa-users" style="color: var(--cep-orange);"></i> Membership</h1>
    <p class="hero-subtitle">Join the Pentecostal Students Community at University of Kigali</p>
  </div>
</section>

<!-- TAB NAVIGATION -->
<div class="tab-navigation">
  <div class="tab-nav-container">
    <button class="tab-btn active" data-tab="register">
      <i class="fas fa-user-plus"></i> Register as Member
    </button>
    <button class="tab-btn" data-tab="login">
      <i class="fas fa-sign-in-alt"></i> Leadership Login
    </button>
  </div>
</div>

<!-- MAIN CONTENT -->
<section class="membership-content">
  <div class="content-container">

    <!-- ========== REGISTRATION TAB ========== -->
    <div class="tab-content active" id="tab-register">
      <div id="registrationForm">
        <!-- Step indicator -->
        <div class="step-indicator">
          <div class="step-item active" id="step-indicator-1">1. Personal Info</div>
          <div class="step-item" id="step-indicator-2">2. CEP Details</div>
          <div class="step-item" id="step-indicator-3">3. Gifts & Bio</div>
        </div>

        <div id="regAlert" class="alert-box"></div>

        <!-- STEP 1: Personal Information -->
        <div class="form-section" id="regStep1">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-user me-2"></i>Personal Information</h2>
            <p class="section-subtitle">Please fill in your personal details accurately</p>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>First Name <span class="required">*</span></label>
              <input type="text" id="firstname" class="form-control" placeholder="Your first name">
              <span class="error-message" id="firstnameError"></span>
            </div>
            <div class="form-group">
              <label>Last Name <span class="required">*</span></label>
              <input type="text" id="lastname" class="form-control" placeholder="Your last name">
              <span class="error-message" id="lastnameError"></span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Email Address <span class="required">*</span></label>
              <div class="input-group-cep">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" id="email" class="form-control" placeholder="your.email@example.com">
              </div>
              <span class="error-message" id="emailError"></span>
            </div>
            <div class="form-group">
              <label>Phone Number <span class="required">*</span></label>
              <div class="input-group-cep">
                <i class="fas fa-phone input-icon"></i>
                <input type="tel" id="phone" class="form-control" placeholder="+250 7XX XXX XXX">
              </div>
              <span class="error-message" id="phoneError"></span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Gender <span class="required">*</span></label>
              <select id="gender" class="form-control">
                <option value="">Select gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
              <span class="error-message" id="genderError"></span>
            </div>
            <div class="form-group">
              <label>Date of Birth</label>
              <input type="date" id="dateOfBirth" class="form-control" max="<?= date('Y-m-d') ?>">
            </div>
          </div>

          <div class="form-group">
            <label>Address (District / Sector)</label>
            <input type="text" id="address" class="form-control" placeholder="e.g. Kicukiro, Kigali">
          </div>

          <div class="form-nav">
            <div></div>
            <button class="btn-primary-cep" onclick="goToStep(2)">
              Next: CEP Details <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </div>

        <!-- STEP 2: CEP Details -->
        <div class="form-section" id="regStep2" style="display:none;">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-church me-2"></i>CEP & Academic Details</h2>
            <p class="section-subtitle">Tell us about your involvement with CEP and your studies</p>
          </div>

          <!-- Membership Type -->
          <div class="form-group">
            <label>Membership Type <span class="required">*</span></label>
            <select id="membershipType" class="form-control">
              <option value="">Select membership type...</option>
            </select>
            <span class="error-message" id="membershipTypeError"></span>
          </div>

          <!-- CEP Session - PROMINENT CHOICE -->
          <div class="form-group">
            <label>CEP Session <span class="required">*</span><br>
              <small style="font-weight:400; color:#888;">Which CEP session do you primarily attend?</small>
            </label>
            <div class="session-selector">
              <div class="session-option">
                <input type="radio" name="cep_session" id="sessionDay" value="day" checked>
                <label class="session-label" for="sessionDay">
                  <span class="session-icon">☀️</span>
                  <span class="session-name">Day CEP</span>
                  <span class="session-desc">Mon, Wed, Thu services</span>
                  <span class="session-badge badge-day">Daytime</span>
                </label>
              </div>
              <div class="session-option">
                <input type="radio" name="cep_session" id="sessionWeekend" value="weekend">
                <label class="session-label" for="sessionWeekend">
                  <span class="session-icon">🌙</span>
                  <span class="session-name">Weekend CEP</span>
                  <span class="session-desc">Sunday services</span>
                  <span class="session-badge badge-weekend">Weekend</span>
                </label>
              </div>
            </div>
            <span class="error-message" id="cep_sessionError"></span>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Year Joined CEP <span class="required">*</span></label>
              <select id="yearJoined" class="form-control">
                <option value="">Select year...</option>
                <?php for ($y = date('Y'); $y >= 2016; $y--): ?>
                  <option value="<?= $y ?>"><?= $y ?></option>
                <?php endfor; ?>
              </select>
              <span class="error-message" id="yearJoinedError"></span>
            </div>
            <div class="form-group">
              <label>Academic Year</label>
              <select id="academicYear" class="form-control">
                <option value="">Select year of study...</option>
                <option>Year 1</option>
                <option>Year 2</option>
                <option>Year 3</option>
                <option>Year 4</option>
                <option>Year 5</option>
                <option>Graduate</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Faculty / School <span class="required">*</span></label>
              <select id="faculty" class="form-control">
                <option value="">Select faculty...</option>
                <option value="Information Technology">Information Technology (IT)</option>
                <option value="Law">Law</option>
                <option value="Finance">Finance</option>
                <option value="Accounting">Accounting</option>
                <option value="Procurement">Procurement</option>
                <option value="Education">Education</option>
                <option value="Economics">Economics</option>
                <option value="Graduate School">Graduate School</option>
                <option value="Other">Other</option>
              </select>
              <span class="error-message" id="facultyError"></span>
            </div>
            <div class="form-group">
              <label>Program / Course</label>
              <input type="text" id="program" class="form-control" placeholder="e.g. BSc Computer Science">
            </div>
          </div>

          <!-- Church - Now plain text field -->
          <div class="form-group">
            <label>Church You Attend</label>
            <div class="input-group-cep">
              <i class="fas fa-church input-icon"></i>
              <input type="text" id="churchName" class="form-control" 
                     placeholder="e.g. ADEPR Kimihurura, EERN Kacyiru, etc.">
            </div>
            <small style="color:#888; font-size:12px; margin-top:4px; display:block;">
              Type the name of your local church (leave blank if not applicable)
            </small>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Are you Born Again?</label>
              <select id="isBornAgain" class="form-control">
                <option value="Prefer not to say">Prefer not to say</option>
                <option value="Yes">Yes</option>
                <option value="No">Not yet</option>
              </select>
            </div>
            <div class="form-group">
              <label>Are you Baptized?</label>
              <select id="isBaptized" class="form-control">
                <option value="Prefer not to say">Prefer not to say</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
              </select>
            </div>
          </div>

          <div class="form-nav">
            <button class="btn-outline-cep" onclick="goToStep(1)">
              <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="btn-primary-cep" onclick="goToStep(3)">
              Next: Gifts & Bio <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </div>

        <!-- STEP 3: Talents & Bio -->
        <div class="form-section" id="regStep3" style="display:none;">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-star me-2"></i>Gifts, Talents & Bio</h2>
            <p class="section-subtitle">Share your gifts and a little about yourself</p>
          </div>

          <!-- Talents -->
          <div class="form-group">
            <label>Gifts & Talents <small style="font-weight:400; color:#888;">(Select all that apply)</small></label>
            <div id="talentsContainer" class="talent-grid">
              <div style="color:#aaa; font-size:14px; padding:12px;">
                <i class="fas fa-spinner fa-spin"></i> Loading talents...
              </div>
            </div>
          </div>

          <!-- Profile Photo -->
          <div class="form-group">
            <label>Profile Photo <small style="font-weight:400; color:#888;">(Optional, max 2MB)</small></label>
            <input type="file" id="profilePhoto" class="form-control" accept="image/jpeg,image/png,image/jpg">
            <div id="photoPreview" style="margin-top:10px; display:none;">
              <img id="photoImg" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid var(--cep-orange);">
            </div>
          </div>

          <!-- Bio -->
          <div class="form-group">
            <label>Brief Introduction / Bio <small style="font-weight:400; color:#888;">(Optional)</small></label>
            <textarea id="bio" class="form-control" rows="4" 
                      placeholder="Tell us something about yourself, your testimony, or why you joined CEP..."></textarea>
          </div>

          <div class="info-note">
            <i class="fas fa-info-circle"></i>
            By submitting, you confirm that all information provided is accurate. Your application will be reviewed by CEP leadership within 2-3 days.
          </div>

          <div class="form-nav">
            <button class="btn-outline-cep" onclick="goToStep(2)">
              <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="btn-primary-cep" id="submitRegBtn" onclick="submitRegistration()">
              <i class="fas fa-paper-plane"></i> Submit Application
            </button>
          </div>
        </div>
      </div>

      <!-- Success State (hidden) -->
      <div id="registrationSuccess" style="display:none;">
        <div class="form-section">
          <div class="success-card">
            <div class="success-icon"><i class="fas fa-check-circle"></i></div>
            <h2 class="success-title">Application Submitted!</h2>
            <p class="success-text">
              Thank you for registering with <strong>CEP UoK</strong>!<br>
              Your membership application is now under review by our leadership team.<br>
              You will receive a notification within <strong>2-3 days</strong>.
            </p>
            <p style="color:#999; font-size:14px;">Registered for: <strong id="successSession"></strong></p>
            <a href="<?= BASE_URL ?>/" class="btn-primary-cep" style="text-decoration:none; display:inline-flex;">
              <i class="fas fa-home"></i> Back to Homepage
            </a>
          </div>
        </div>
      </div>
    </div>
    <!-- ========== END REGISTER TAB ========== -->

    <!-- ========== LOGIN TAB ========== -->
    <div class="tab-content" id="tab-login">
      <div class="login-card">
        <div class="login-logo">
          <img src="<?= img_url('logo-dark.png') ?>" alt="CEP UoK Logo" onerror="this.style.display='none'">
        </div>
        <h2 class="login-heading">Leadership Portal</h2>
        <p class="login-subheading">Sign in to access the CEP Management Portal</p>

        <div id="loginAlert" class="alert-box"></div>

        <div class="form-group">
          <label>Email or Phone <span class="required">*</span></label>
          <div class="input-group-cep">
            <i class="fas fa-user input-icon"></i>
            <input type="text" id="loginIdentifier" class="form-control" placeholder="Email or phone number">
          </div>
        </div>

        <div class="form-group">
          <label style="display:flex; justify-content:space-between;">
            <span>Password <span class="required">*</span></span>
            <a href="#" class="forgot-link" id="forgotPasswordLink">Forgot password?</a>
          </label>
          <div class="input-group-cep">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="loginPassword" class="form-control" placeholder="Enter your password">
            <button class="toggle-password" type="button" id="togglePwd">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:400;">
            <input type="checkbox" id="rememberMe" style="accent-color: var(--cep-orange);">
            Remember me for 7 days
          </label>
        </div>

        <button class="btn-primary-cep w-100" id="loginBtn" onclick="doLogin()" style="width:100%; justify-content:center; padding:14px;">
          <i class="fas fa-sign-in-alt"></i> Sign In to Portal
        </button>

        <div class="login-divider"><span>or</span></div>

        <p style="text-align:center; font-size:14px; color:#888; margin:0;">
          Not a leader? <a href="#" style="color: var(--cep-orange);" onclick="switchToTab('register')">Register as a member</a>
        </p>

        <div class="info-note" style="margin-top:20px; margin-bottom:0;">
          <i class="fas fa-shield-alt"></i>
          Portal access is restricted to active CEP committee members. Contact your session President if you need access.
        </div>
      </div>
    </div>
    <!-- ========== END LOGIN TAB ========== -->

  </div><!-- .content-container -->
</section>

<!-- FORGOT PASSWORD MODAL -->
<div class="modal-overlay" id="forgotPasswordModal">
  <div class="modal-card">
    <div class="modal-header">
      <h3 class="modal-title" id="modalTitle">Reset Password</h3>
      <button class="modal-close" onclick="closeForgotModal()">&times;</button>
    </div>
    <div id="fpAlert" class="alert-box"></div>

    <!-- Step 1: Enter email -->
    <div class="modal-step active" id="fpStep1">
      <p style="color:#666; font-size:14px; margin:0 0 20px;">Enter your registered email address and we'll send a verification code.</p>
      <div class="form-group">
        <label>Email Address <span class="required">*</span></label>
        <div class="input-group-cep">
          <i class="fas fa-envelope input-icon"></i>
          <input type="email" id="fpEmail" class="form-control" placeholder="your.email@example.com">
        </div>
      </div>
      <button class="btn-primary-cep w-100" onclick="sendOTP()" id="sendOtpBtn" style="width:100%;justify-content:center;">
        <i class="fas fa-paper-plane"></i> Send Verification Code
      </button>
    </div>

    <!-- Step 2: Enter OTP -->
    <div class="modal-step" id="fpStep2">
      <p style="color:#666; font-size:14px; margin:0 0 20px;">A 6-digit code has been sent to <strong id="fpEmailDisplay"></strong></p>
      <div class="form-group">
        <label>Verification Code <span class="required">*</span></label>
        <input type="text" id="fpOtp" class="form-control" placeholder="Enter 6-digit code" maxlength="6"
               style="text-align:center; font-size:24px; letter-spacing:8px; font-weight:700;">
      </div>
      <button class="btn-primary-cep w-100" onclick="verifyOTP()" id="verifyOtpBtn" style="width:100%;justify-content:center;">
        <i class="fas fa-check"></i> Verify Code
      </button>
      <p style="text-align:center; margin-top:12px; font-size:13px; color:#aaa;">
        Didn't receive it? <a href="#" style="color:var(--cep-orange);" onclick="sendOTP()">Resend</a>
      </p>
    </div>

    <!-- Step 3: New password -->
    <div class="modal-step" id="fpStep3">
      <div class="form-group">
        <label>New Password <span class="required">*</span></label>
        <div class="input-group-cep">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" id="fpNewPassword" class="form-control" placeholder="Min. 8 characters">
        </div>
      </div>
      <div class="form-group">
        <label>Confirm Password <span class="required">*</span></label>
        <div class="input-group-cep">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" id="fpConfirmPassword" class="form-control" placeholder="Repeat your password">
        </div>
      </div>
      <button class="btn-primary-cep w-100" onclick="resetPassword()" id="resetPwdBtn" style="width:100%;justify-content:center;">
        <i class="fas fa-save"></i> Save New Password
      </button>
    </div>
  </div>
</div>

<?php include get_layout('footer'); ?>
</div></div>

<?php include get_layout('scripts'); ?>

<script>
const BASE_URL = '<?= BASE_URL ?>';
let fpResetEmail = '';
let fpOtpVerified = '';
let currentRegStep = 1;

// ============================================================
// TAB SWITCHING
// ============================================================
function switchToTab(tabName) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
  document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
  document.getElementById(`tab-${tabName}`).classList.add('active');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => switchToTab(btn.dataset.tab));
});

// ============================================================
// MULTI-STEP FORM
// ============================================================
function goToStep(step) {
  // Validate before proceeding
  if (step > currentRegStep) {
    if (!validateStep(currentRegStep)) return;
  }

  [1,2,3].forEach(s => {
    const el = document.getElementById(`regStep${s}`);
    if (el) el.style.display = s === step ? 'block' : 'none';
    const ind = document.getElementById(`step-indicator-${s}`);
    if (ind) {
      ind.classList.remove('active', 'completed');
      if (s < step) ind.classList.add('completed');
      else if (s === step) ind.classList.add('active');
    }
  });

  currentRegStep = step;
  window.scrollTo({ top: 300, behavior: 'smooth' });
}

function validateStep(step) {
  clearErrors();
  let valid = true;

  if (step === 1) {
    valid = required('firstname', 'First name is required') && valid;
    valid = required('lastname', 'Last name is required') && valid;
    valid = required('gender', 'Please select gender') && valid;
    const email = val('email');
    if (!email) { showFieldError('email', 'Email is required'); valid = false; }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showFieldError('email', 'Invalid email format'); valid = false; }
    const phone = val('phone');
    if (!phone) { showFieldError('phone', 'Phone is required'); valid = false; }
    else if (!/^\+?[0-9]{10,15}$/.test(phone.replace(/\s/g,''))) { showFieldError('phone', 'Invalid phone format'); valid = false; }
  }

  if (step === 2) {
    valid = required('membershipType', 'Please select membership type') && valid;
    valid = required('yearJoined', 'Please select year joined') && valid;
    valid = required('faculty', 'Please select your faculty') && valid;
    // Check session is selected
    if (!document.querySelector('input[name="cep_session"]:checked')) {
      showFieldError('cep_session', 'Please select your CEP session'); valid = false;
    }
  }

  return valid;
}

function required(id, msg) {
  if (!val(id)) { showFieldError(id, msg); return false; }
  return true;
}
function val(id) {
  const el = document.getElementById(id);
  return el ? el.value.trim() : '';
}
function showFieldError(id, msg) {
  const el = document.getElementById(id);
  if (el) el.classList.add('error');
  const err = document.getElementById(id + 'Error');
  if (err) { err.textContent = msg; err.classList.add('show'); }
}
function clearErrors() {
  document.querySelectorAll('.form-control').forEach(e => e.classList.remove('error'));
  document.querySelectorAll('.error-message').forEach(e => { e.textContent=''; e.classList.remove('show'); });
}

// ============================================================
// LOAD FORM DATA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  loadMembershipTypes();
  loadTalents();

  // Toggle password visibility
  const togglePwd = document.querySelector('#togglePwd');
  const loginPassword = document.querySelector('#loginPassword');

  if (togglePwd && loginPassword) {
    togglePwd.addEventListener('click', function() {
      const icon = this.querySelector('i');
      const isPassword = loginPassword.getAttribute('type') === 'password';

      loginPassword.setAttribute('type', isPassword ? 'text' : 'password');

      // Toggle icon classes
      icon.classList.toggle('fa-eye', !isPassword);
      icon.classList.toggle('fa-eye-slash', isPassword);
    });
  }

  // Email availability check on blur
  const emailInput = document.querySelector('#email');
  if (emailInput) {
    emailInput.addEventListener('blur', async function() {
      const email = this.value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!email || !emailRegex.test(email)) return;

      try {
        const response = await fetch(`${BASE_URL}/api/membership?action=checkEmail&email=${encodeURIComponent(email)}`);
        const res = await response.json();

        if (res.exists) {
          showFieldError('email', 'This email is already registered');
        } else {
          this.classList.remove('error');
        }
      } catch (error) {
        console.error('Error checking email:', error);
      }
    });
  }

  // Phone availability check
  const phoneInput = document.querySelector('#phone');
  if (phoneInput) {
    phoneInput.addEventListener('blur', function() {
      // Could add phone check here
    });
  }

  // Photo preview
  const profilePhoto = document.querySelector('#profilePhoto');
  const photoImg = document.querySelector('#photoImg');
  const photoPreview = document.querySelector('#photoPreview');

  if (profilePhoto) {
    profilePhoto.addEventListener('change', function() {
      const file = this.files[0];
      const maxSize = 2 * 1024 * 1024; // 2MB

      if (file && file.size <= maxSize) {
        const reader = new FileReader();
        reader.onload = e => {
          photoImg.src = e.target.result;
          photoPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else if (file) {
        alert('Photo must be less than 2MB');
        this.value = '';
      }
    });
  }

  // Login on Enter key
  const loginInputs = document.querySelectorAll('#loginPassword, #loginIdentifier');
  loginInputs.forEach(input => {
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') doLogin();
    });
  });

  // Forgot password modal
  const forgotLink = document.querySelector('#forgotPasswordLink');
  if (forgotLink) {
    forgotLink.addEventListener('click', (e) => {
      e.preventDefault();
      openForgotModal();
    });
  }
});

async function loadMembershipTypes() {
  const selectElement = document.querySelector('#membershipType');
  
  try {
    const response = await fetch(`${BASE_URL}/api/membership?action=getMembershipTypes`);
    
    // Check if the HTTP request actually succeeded
    if (!response.ok) throw new Error('Network response was not ok');

    const result = await response.json();

    if (result.success && result.data) {
      // Clear existing options first if necessary
      // selectElement.innerHTML = '<option value="">Select Type</option>';

      result.data.forEach(type => {
        const option = document.createElement('option');
        option.value = type.id;
        option.textContent = type.type_name;
        selectElement.appendChild(option);
      });
    }
  } catch (error) {
    console.error('Failed to load membership types:', error);
  }
}

async function loadTalents() {
  const container = document.querySelector('#talentsContainer');
  if (!container) return;

  try {
    const response = await fetch(`${BASE_URL}/api/membership?action=getTalents`);
    if (!response.ok) throw new Error('Failed to fetch talents');

    const res = await response.json();

    if (res.success && res.data) {
      let html = '';

      // Loop through the object categories (e.g., "Music", "Dance")
      for (const [category, talents] of Object.entries(res.data)) {
        html += `
          <div class="talent-category">
            <div class="talent-category-title">
              <i class="fas fa-tag me-1"></i>${category}
            </div>`;

        // Loop through each talent in that category
        talents.forEach(t => {
          html += `
            <div class="checkbox-item">
              <input type="checkbox" name="talents[]" value="${t.id}" id="t_${t.id}">
              <label for="t_${t.id}">${t.talent_name}</label>
            </div>`;
        });

        html += '</div>';
      }

      // Inject the completed HTML string into the container
      container.innerHTML = html;
    }
  } catch (error) {
    console.error('Error loading talents:', error);
  }
}

// ============================================================
// SUBMIT REGISTRATION
// ============================================================
async function submitRegistration() {
  if (!validateStep(3)) return;

  // 1. Setup elements and loading state
  const submitBtn = document.querySelector('#submitRegBtn');
  const regForm = document.querySelector('#registrationForm');
  const successDiv = document.querySelector('#registrationSuccess');
  
  const originalBtnHtml = '<i class="fas fa-paper-plane"></i> Submit Application';
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

  // 2. Gather Data
  const sessionVal = document.querySelector('input[name="cep_session"]:checked')?.value || 'day';
  const formData = new FormData();

  // Helper to match your 'val()' function logic
  const getVal = (id) => document.getElementById(id)?.value || '';

  const fields = [
    'membershipType', 'firstname', 'lastname', 'email', 'phone', 
    'gender', 'dateOfBirth', 'address', 'yearJoined', 'faculty', 
    'program', 'academicYear', 'churchName', 'isBornAgain', 
    'isBaptized', 'bio'
  ];

  // Map local IDs to your API keys
  fields.forEach(field => {
    // Note: You might need to map 'membershipType' to 'membership_type_id' specifically
    let apiKey = field.replace(/([A-Z])/g, "_$1").toLowerCase(); // simple camelToSnake helper
    if (field === 'membershipType') apiKey = 'membership_type_id';
    if (field === 'yearJoined') apiKey = 'year_joined_cep';
    
    formData.append(apiKey, getVal(field));
  });

  formData.append('cep_session', sessionVal);

  // Add Talents
  document.querySelectorAll('input[name="talents[]"]:checked').forEach(c => {
    formData.append('talents[]', c.value);
  });

  // Add Photo
  const photo = document.getElementById('profilePhoto').files[0];
  if (photo) formData.append('profile_photo', photo);

  // 3. The Fetch Request
  try {
    const response = await fetch(`${BASE_URL}/api/membership?action=register`, {
      method: 'POST',
      body: formData // Fetch handles Content-Type automatically for FormData
    });

    const res = await response.json();

    submitBtn.disabled = false;
    submitBtn.innerHTML = originalBtnHtml;

    if (res.success) {
      const sessionLabels = { day: 'Day CEP', weekend: 'Weekend CEP' };
      document.querySelector('#successSession').textContent = sessionLabels[sessionVal] || sessionVal;

      // Vanilla JS Fade Out/In replacement
      regForm.style.transition = 'opacity 0.3s';
      regForm.style.opacity = '0';
      
      setTimeout(() => {
        regForm.style.display = 'none';
        successDiv.style.display = 'block';
        successDiv.style.opacity = '0';
        setTimeout(() => successDiv.style.opacity = '1', 10);
        
        window.scrollTo({ top: 200, behavior: 'smooth' });
      }, 300);

    } else {
      showAlert('regAlert', 'error', res.message || 'Registration failed.');
    }
  } catch (error) {
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalBtnHtml;
    showAlert('regAlert', 'error', 'Server error. Please try again.');
  }
}

// ============================================================
// LOGIN
// ============================================================
async function doLogin() {
  const identifier = document.getElementById('loginIdentifier')?.value;
  const password = document.getElementById('loginPassword')?.value;
  const loginBtn = document.querySelector('#loginBtn');

  // 1. Validation
  if (!identifier || !password) {
    showAlert('loginAlert', 'error', 'Please enter your email/phone and password.');
    return;
  }

  // 2. Loading State
  const originalBtnHtml = '<i class="fas fa-sign-in-alt"></i> Sign In to Portal';
  loginBtn.disabled = true;
  loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

  try {
    // 3. The Fetch Request
    const response = await fetch(`${BASE_URL}/api/auth?action=login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ identifier, password })
    });

    const res = await response.json();

    if (res.success) {
      showAlert('loginAlert', 'success', 'Login successful! Redirecting...');
      
      setTimeout(() => {
        window.location.href = `${BASE_URL}/admin/dashboard`;
      }, 800);
    } else {
      // Re-enable button on logical failure
      loginBtn.disabled = false;
      loginBtn.innerHTML = originalBtnHtml;
      showAlert('loginAlert', 'error', res.message || 'Invalid credentials.');
    }

  } catch (error) {
    // 4. Error Handling (Connection/Server issues)
    loginBtn.disabled = false;
    loginBtn.innerHTML = originalBtnHtml;
    showAlert('loginAlert', 'error', 'Connection error. Please try again.');
    console.error('Login Error:', error);
  }
}

// ============================================================
// FORGOT PASSWORD MODAL
// ============================================================
function openForgotModal() {
  document.getElementById('forgotPasswordModal').classList.add('show');
  resetFpModal();
}
function closeForgotModal() {
  document.getElementById('forgotPasswordModal').classList.remove('show');
}
function resetFpModal() {
  ['fpStep1','fpStep2','fpStep3'].forEach((id, i) => {
    document.getElementById(id).classList.toggle('active', i === 0);
  });
  document.getElementById('modalTitle').textContent = 'Reset Password';
  $('#fpEmail,#fpOtp,#fpNewPassword,#fpConfirmPassword').val('');
  $('#fpAlert').removeClass('show');
  fpResetEmail = ''; fpOtpVerified = '';
}

function showFpStep(step) {
  ['fpStep1','fpStep2','fpStep3'].forEach((id, i) => {
    document.getElementById(id).classList.toggle('active', i === step - 1);
  });
}

function sendOTP() {
  const email = val('fpEmail');
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showAlert('fpAlert', 'error', 'Please enter a valid email address.'); return;
  }
  $('#sendOtpBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
  $.ajax({
    url: `${BASE_URL}/api/auth?action=forgot-password`,
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ email }),
    success: function(res) {
      $('#sendOtpBtn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Verification Code');
      if (res.success) {
        fpResetEmail = email;
        $('#fpEmailDisplay').text(email);
        document.getElementById('modalTitle').textContent = 'Enter Verification Code';
        showFpStep(2);
        showAlert('fpAlert', 'success', 'Verification code sent!');
      } else {
        showAlert('fpAlert', 'error', res.message || 'Email not found.');
      }
    },
    error: function() {
      $('#sendOtpBtn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Verification Code');
      showAlert('fpAlert', 'error', 'Failed to send OTP. Please try again.');
    }
  });
}

function verifyOTP() {
  const otp = val('fpOtp');
  if (!otp || otp.length < 6) { showAlert('fpAlert', 'error', 'Enter the 6-digit code.'); return; }
  $('#verifyOtpBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Verifying...');
  $.ajax({
    url: `${BASE_URL}/api/auth?action=verify-otp`,
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ email: fpResetEmail, otp }),
    success: function(res) {
      $('#verifyOtpBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Verify Code');
      if (res.success) {
        fpOtpVerified = otp;
        document.getElementById('modalTitle').textContent = 'Set New Password';
        showFpStep(3);
        showAlert('fpAlert', 'success', 'Code verified! Set your new password.');
      } else {
        showAlert('fpAlert', 'error', res.message || 'Invalid code.');
      }
    },
    error: function() {
      $('#verifyOtpBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Verify Code');
      showAlert('fpAlert', 'error', 'Verification failed. Please try again.');
    }
  });
}

function resetPassword() {
  const pwd = $('#fpNewPassword').val();
  const confirm = $('#fpConfirmPassword').val();
  if (!pwd || pwd.length < 8) { showAlert('fpAlert', 'error', 'Password must be at least 8 characters.'); return; }
  if (pwd !== confirm) { showAlert('fpAlert', 'error', 'Passwords do not match.'); return; }
  $('#resetPwdBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
  $.ajax({
    url: `${BASE_URL}/api/auth?action=reset-password`,
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ email: fpResetEmail, otp: fpOtpVerified, password: pwd }),
    success: function(res) {
      $('#resetPwdBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save New Password');
      if (res.success) {
        showAlert('fpAlert', 'success', 'Password reset successfully! You can now log in.');
        setTimeout(() => { closeForgotModal(); }, 2000);
      } else {
        showAlert('fpAlert', 'error', res.message || 'Reset failed.');
      }
    },
    error: function() {
      $('#resetPwdBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save New Password');
      showAlert('fpAlert', 'error', 'Failed to reset password. Please try again.');
    }
  });
}

// Close modal on overlay click
document.getElementById('forgotPasswordModal').addEventListener('click', function(e) {
  if (e.target === this) closeForgotModal();
});

// ============================================================
// HELPERS
// ============================================================
function showAlert(containerId, type, message) {
  const el = document.getElementById(containerId);
  if (!el) return;
  const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
  el.className = `alert-box alert-${type} show`;
  el.innerHTML = `<i class="fas fa-${icons[type] || 'info-circle'}"></i> <span>${message}</span>`;
  setTimeout(() => el.classList.remove('show'), 8000);
}
</script>
</body>
</html>