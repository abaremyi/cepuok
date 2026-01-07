<!DOCTYPE html>
<html lang="en">

<?php 
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
include_once get_layout('header');
?>

<body>
    
    <?php 
        $home = 'off'; 
        $services = 'off'; 
        $work = 'off'; 
        $about = 'off'; 
        $news = 'off'; 
        $contacts = 'active'; 
    ?>
    
    <?php include_once get_layout('navbar'); ?>

    <!-- Page Header -->
    <header class="contact-page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Mount Carmel is ready to provide the right solution according to your needs</p>
            <div class="contact-breadcrumb">
                <a href="<?= url() ?>">Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>Contact</span>
            </div>
        </div>
    </header>

    <!-- Contact Section -->
    <section class="contact-main-section">
        <div class="container">
            <div class="contact-wrapper">
                
                <!-- Get in Touch Section -->
                <div class="get-in-touch" data-aos="fade-right">
                    <h2>Get in touch</h2>
                    <p class="touch-description">We're here to help and answer any questions you might have. We look forward to hearing from you.</p>

                    <!-- Contact Info Cards -->
                    <div class="contact-info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Head Office</h4>
                            <p>Kigali, Rwanda<br>Nyarugenge District</p>
                        </div>
                    </div>

                    <div class="contact-info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email Us</h4>
                            <p>info@mountcarmel.ac.rw<br>admissions@mountcarmel.ac.rw</p>
                        </div>
                    </div>

                    <div class="contact-info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Call Us</h4>
                            <p>Phone: +250 789 121 680<br></p>
                        </div>
                    </div>

                    <!-- Response Time Information -->
                    <div class="response-time-info">
                        <h4><i class="fas fa-clock"></i> Response Time</h4>
                        <p>We strive to respond to all inquiries within <strong>24-72 hours</strong> during business days.</p>
                        <p>For urgent matters, please call us directly.</p>
                    </div>

                    <!-- Social Media -->
                    <div class="social-media-section">
                        <h4>Follow our social media</h4>
                        <div class="social-links">
                            <a href="#" class="social-btn" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-btn" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-btn" aria-label="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-btn" aria-label="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form Section -->
                <div class="contact-form-section" data-aos="fade-left">
                    <h2>Send us a message</h2>
                    
                    <form id="contactForm" class="compact-contact-form">
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       placeholder="Full Name *" 
                                       required>
                            </div>

                            <div class="form-group">
                                <select id="person_type" name="person_type" required>
                                    <option value="">I am a... *</option>
                                    <option value="parent">Current Parent</option>
                                    <option value="prospective_parent">Prospective Parent</option>
                                    <option value="student">Student</option>
                                    <option value="alumni">Alumni</option>
                                    <option value="guest">Guest/Visitor</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       placeholder="Phone Number *" 
                                       required>
                            </div>

                            <div class="form-group">
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       placeholder="Email Address *" 
                                       required>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <select id="inquiry_type" name="inquiry_type" required>
                                <option value="">Subject of Inquiry *</option>
                                <option value="admissions">Admissions Inquiry</option>
                                <option value="programs">Programs Information</option>
                                <option value="visit">School Visit Request</option>
                                <option value="general">General Inquiry</option>
                                <option value="support">Support Request</option>
                            </select>
                        </div>

                        <input type="hidden" id="subject" name="subject" value="Contact Form Submission">

                        <div class="form-group full-width">
                            <textarea id="message" 
                                      name="message" 
                                      rows="5" 
                                      placeholder="Your Message *" 
                                      required></textarea>
                        </div>

                        <div class="form-notice">
                            <p><i class="fas fa-info-circle"></i> Fields marked with * are required. We aim to respond within 24-72 hours.</p>
                        </div>

                        <button type="submit" class="submit-button">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section" id="map">
        <div class="container">
            <div class="map-wrapper" data-aos="fade-up">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3987.497740050782!2d30.05231477499659!3d-1.9492449985734248!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dca4258ed8a797%3A0x52fefce07781d7c7!2sKiyovu%2C%20Kigali%2C%20Rwanda!5e0!3m2!1sen!2srw!4v1646760525018!5m2!1sen!2srw" 
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <!-- FAQ Section with Accordion -->
    <section class="faq-section">
        <div class="container">
            <div class="faq-header" data-aos="fade-up">
                <h2>Frequently Asked Questions</h2>
                <p>Quick answers to common questions about Mount Carmel School</p>
            </div>

            <div class="accordion-wrapper" data-aos="fade-up" data-aos-delay="200">
                
                <div class="accordion-item">
                    <div class="accordion-header">
                        <h3>What are your admission requirements?</h3>
                        <i class="fas fa-chevron-down accordion-icon"></i>
                    </div>
                    <div class="accordion-content">
                        <p>We welcome students of all backgrounds. Our admission requirements vary by program level. For Nursery School, we require birth certificates and immunization records. For Primary levels, we conduct a simple assessment to ensure appropriate grade placement. Contact our admissions office for specific requirements based on the program level you're interested in.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <h3>Can I schedule a school tour?</h3>
                        <i class="fas fa-chevron-down accordion-icon"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Yes! We encourage prospective families to visit our campus and experience Mount Carmel School firsthand. Tours are available Monday through Friday between 9:00 AM and 3:00 PM. Select "School Visit Request" in the contact form above, call us directly at +250 789 121 680, or email admissions@mountcarmel.ac.rw to schedule your visit.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <h3>What programs do you offer?</h3>
                        <i class="fas fa-chevron-down accordion-icon"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Mount Carmel School offers three main educational programs: <strong>Nursery School</strong> (ages 3-5) with our Francophone program providing a safe, play-based learning environment; <strong>Lower Primary</strong> (P1-P3) focusing on foundational skills in both English and French; and <strong>Upper Primary</strong> (P4-P6) preparing students for secondary school with advanced bilingual education and comprehensive curriculum.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <h3>How quickly will you respond to my inquiry?</h3>
                        <i class="fas fa-chevron-down accordion-icon"></i>
                    </div>
                    <div class="accordion-content">
                        <p>We typically respond to all inquiries within <strong>24-72 hours</strong> during business days (Monday-Friday, 8:00 AM - 5:00 PM). For urgent matters, please call us directly at +250 789 121 680. If you submit an inquiry over the weekend, you can expect a response by the following Tuesday. If you haven't received a response within 72 hours, please check your spam folder or call us directly.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <h3>What makes Mount Carmel School different?</h3>
                        <i class="fas fa-chevron-down accordion-icon"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Mount Carmel School stands out through our commitment to bilingual education (English and French), small class sizes for personalized attention, experienced and qualified teachers, Christian values-based education, modern facilities including computer labs and libraries, and a comprehensive curriculum that nurtures both academic excellence and character development.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <h3>Do you provide transportation services?</h3>
                        <i class="fas fa-chevron-down accordion-icon"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Yes, we offer school transportation services for students across various areas in Kigali. Our buses are well-maintained, driven by experienced drivers, and supervised by staff members to ensure student safety. Transportation fees are separate from tuition. Contact us for route information and availability in your area.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php include_once get_layout('footer'); ?>
    <?php include_once get_layout('scripts'); ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        // Accordion functionality
        $('.accordion-header').on('click', function() {
            const $item = $(this).closest('.accordion-item');
            const $content = $item.find('.accordion-content');
            const $icon = $(this).find('.accordion-icon');

            // Close all other accordions
            $('.accordion-item').not($item).removeClass('active');
            $('.accordion-content').not($content).slideUp(300);
            $('.accordion-icon').not($icon).removeClass('rotated');

            // Toggle current accordion
            $item.toggleClass('active');
            $content.slideToggle(300);
            $icon.toggleClass('rotated');
        });

        // Phone number formatting
        $('#phone').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length > 0) {
                if (value.startsWith('250')) {
                    value = '+' + value;
                } else if (!value.startsWith('+')) {
                    value = '+250' + value;
                }
            }
            $(this).val(value);
        });

        // Form submission
        $('#contactForm').on('submit', function(e) {
            e.preventDefault();

            // Get form data
            const name = $('#name').val().trim();
            const email = $('#email').val().trim();
            const phone = $('#phone').val().trim();
            const person_type = $('#person_type').val();
            const inquiry_type = $('#inquiry_type').val();
            const message = $('#message').val().trim();

            // Create subject from inquiry type
            const subjectMap = {
                'admissions': 'Admissions Inquiry',
                'programs': 'Programs Information',
                'visit': 'School Visit Request',
                'general': 'General Inquiry',
                'support': 'Support Request'
            };
            const subject = subjectMap[inquiry_type] || 'Contact Form Submission';

            if (!name || !email || !phone || !person_type || !inquiry_type || !message) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please fill in all required fields marked with *.',
                    confirmButtonColor: '#00796B'
                });
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Sending Your Message',
                html: 'Please wait while we process your inquiry...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = {
                name: name,
                email: email,
                phone: phone,
                subject: subject,
                message: message,
                person_type: person_type,
                inquiry_type: inquiry_type,
                action: 'submit_contact'
            };

            $.ajax({
                type: 'POST',
                url: '<?= url('api/contact') ?>',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Message Sent Successfully!',
                            html: response.message,
                            confirmButtonColor: '#00796B',
                            confirmButtonText: 'Great!'
                        }).then(() => {
                            $('#contactForm')[0].reset();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message,
                            confirmButtonColor: '#00796B'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        html: 'Unable to send your message. Please try again or contact us directly:<br><strong>Phone:</strong> +250 789 121 680<br><strong>Email:</strong> info@mountcarmel.ac.rw',
                        confirmButtonColor: '#00796B'
                    });
                }
            });
        });

        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            const target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 800);
            }
        });

        // AOS Animation
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });
        }
    });
    </script>

    <style>
    /* Contact Page Styles */
    .contact-page-header {
        /* background: linear-gradient(135deg, #00796B 0%, #004D40 100%); */
        background: linear-gradient(135deg, rgba(0, 121, 107, 0.9), rgba(26, 58, 82, 0.9)),
        url('<?= img_url("hero-contact.jpg") ?>');
        color: white;
        padding: 100px 0 70px;
        text-align: center;
        position: relative;
        overflow: hidden;
        background-size: cover;
    }

    .contact-page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect fill="rgba(255,255,255,0.05)" width="50" height="50"/></svg>');
        opacity: 0.1;
    }

    .contact-page-header .container {
        position: relative;
        z-index: 1;
    }

    .contact-page-header h1 {
        font-size: 2.8rem;
        font-weight: 700;
        margin-bottom: 0.8rem;
    }

    .contact-page-header p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 1.5rem;
    }

    .contact-breadcrumb {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.8rem;
        font-size: 0.95rem;
    }

    .contact-breadcrumb a {
        color: white;
        text-decoration: none;
        transition: opacity 0.3s;
    }

    .contact-breadcrumb a:hover {
        opacity: 0.8;
    }

    /* Contact Main Section */
    .contact-main-section {
        padding: 80px 0;
        background: white;
    }

    .contact-wrapper {
        display: grid;
        grid-template-columns: 400px 1fr;
        gap: 4rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Get in Touch Section */
    .get-in-touch {
        background: #ffffff;
    }

    .get-in-touch h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1rem;
    }

    .touch-description {
        color: #7f8c8d;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .contact-info-card {
        display: flex;
        gap: 1.2rem;
        margin-bottom: 1.8rem;
        padding-bottom: 1.8rem;
        border-bottom: 1px solid #e0e0e0;
    }

    .contact-info-card:last-of-type {
        border-bottom: none;
    }

    .info-icon {
        width: 50px;
        height: 50px;
        background: rgba(6, 119, 156, 0.9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: white;
        flex-shrink: 0;
    }

    .info-content h4 {
        font-size: 1.1rem;
        color: #2c3e50;
        margin-bottom: 0.4rem;
        font-weight: 600;
    }

    .info-content p {
        color: #7f8c8d;
        margin: 0;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    /* Response Time Info */
    .response-time-info {
        margin-top: 2.5rem;
        padding: 1.5rem;
        background: #f0f9ff;
        border-left: 4px solid #1e88e5;
        border-radius: 4px;
    }

    .response-time-info h4 {
        font-size: 1.05rem;
        color: #2c3e50;
        margin-bottom: 0.8rem;
        font-weight: 600;
    }

    .response-time-info h4 i {
        color: #1e88e5;
        margin-right: 0.5rem;
    }

    .response-time-info p {
        color: #666;
        margin: 0.5rem 0;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    /* Social Media Section */
    .social-media-section {
        margin-top: 2.5rem;
    }

    .social-media-section h4 {
        font-size: 1.05rem;
        color: #2c3e50;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .social-links {
        display: flex;
        gap: 0.8rem;
    }

    .social-btn {
        width: 40px;
        height: 40px;
        background: #1e88e5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        text-decoration: none;
        transition: all 0.3s;
    }

    .social-btn:hover {
        background: #1565c0;
        transform: translateY(-3px);
    }

    /* Compact Contact Form */
    .contact-form-section {
        background: #f8f9fa;
        padding: 2.5rem;
        border-radius: 15px;
    }

    .contact-form-section h2 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 2rem;
    }

    .compact-contact-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .compact-contact-form input,
    .compact-contact-form select,
    .compact-contact-form textarea {
        padding: 0.75rem 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.3s;
        background: white;
    }

    .compact-contact-form input:focus,
    .compact-contact-form select:focus,
    .compact-contact-form textarea:focus {
        outline: none;
        border-color: #1e88e5;
        box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
    }

    .compact-contact-form textarea {
        resize: vertical;
        min-height: 120px;
    }

    .compact-contact-form select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
    }

    .form-notice {
        margin-top: 0.5rem;
        padding: 0.8rem;
        background: #fff8e1;
        border-left: 4px solid #ffb300;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #5d4037;
    }

    .form-notice i {
        color: #ffb300;
        margin-right: 0.5rem;
    }

    .submit-button {
        background: rgba(6, 119, 156, 0.9);
        color: white;
        padding: 0.9rem 3rem;
        border: none;
        border-radius: 25px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        align-self: flex-start;
        margin-top: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .submit-button:hover {
        background: #1565c0;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30, 136, 229, 0.3);
    }

    .submit-button i {
        font-size: 0.9rem;
    }

    /* Map Section */
    .map-section {
        padding: 0;
        background: white;
    }

    .map-wrapper {
        border-radius: 0;
        overflow: hidden;
    }

    /* FAQ Accordion Section */
    .faq-section {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .faq-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .faq-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.8rem;
    }

    .faq-header p {
        font-size: 1.1rem;
        color: #7f8c8d;
    }

    .accordion-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .accordion-item {
        background: white;
        border-radius: 10px;
        margin-bottom: 1rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s;
    }

    .accordion-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .accordion-item.active {
        box-shadow: 0 4px 15px rgba(30, 136, 229, 0.15);
    }

    .accordion-header {
        padding: 1.3rem 1.5rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
        transition: background 0.3s;
    }

    .accordion-header:hover {
        background: #f8f9fa;
    }

    .accordion-item.active .accordion-header {
        background: #f0f7ff;
    }

    .accordion-header h3 {
        font-size: 1.1rem;
        color: #2c3e50;
        margin: 0;
        font-weight: 600;
        flex: 1;
    }

    .accordion-icon {
        color: #1e88e5;
        font-size: 1rem;
        transition: transform 0.3s;
    }

    .accordion-icon.rotated {
        transform: rotate(180deg);
    }

    .accordion-content {
        display: none;
        padding: 0 1.5rem 1.5rem 1.5rem;
    }

    .accordion-content p {
        color: #555;
        line-height: 1.7;
        margin: 0;
        font-size: 0.98rem;
    }

    .accordion-content strong {
        color: #00796B;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .contact-wrapper {
            grid-template-columns: 1fr;
            gap: 3rem;
        }

        .get-in-touch {
            order: 2;
        }

        .contact-form-section {
            order: 1;
        }
    }

    @media (max-width: 768px) {
        .contact-page-header h1 {
            font-size: 2rem;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .contact-form-section {
            padding: 2rem;
        }

        .faq-header h2 {
            font-size: 2rem;
        }

        .accordion-header h3 {
            font-size: 1rem;
        }

        .contact-main-section {
            padding: 60px 0;
        }

        .faq-section {
            padding: 60px 0;
        }
    }

    @media (max-width: 480px) {
        .contact-page-header {
            padding: 80px 0 60px;
        }

        .contact-page-header h1 {
            font-size: 1.8rem;
        }

        .submit-button {
            width: 100%;
            justify-content: center;
        }
    }
    </style>

</body>
</html>