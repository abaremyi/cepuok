<!doctype html>
<html class="no-js" lang="zxx">

<?php
/**
 * About CEP Page - IMPROVED VERSION
 * File: modules/General/views/about-cep.php
 * Enhanced design with better sections
 */

require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../config/database.php';

// Fetch page content from database
$db = Database::getInstance();
$pageContent = [];

try {
    $query = "SELECT section_name, title, content, image_url 
              FROM page_content 
              WHERE page_name = 'about_cep' AND status = 'active' 
              ORDER BY display_order ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $pageContent[$row['section_name']] = $row;
    }
} catch (PDOException $e) {
    error_log("Error fetching about content: " . $e->getMessage());
}

// Helper function to get content
function getContent($key, $field = 'content', $default = '')
{
    global $pageContent;
    return isset($pageContent[$key]) ? $pageContent[$key][$field] : $default;
}
?>
<?php include get_layout('header-2'); ?>

<body data-res-from="1025">

    <style>
        /* ========== IMPROVED STYLES ========== */

        /* Mission & Vision Section - Simplified Professional Design */
        .mission-vision-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            padding: 80px 0;
        }

        .mv-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .mv-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
        }

        .mv-card {
            background: white;
            padding: 35px 30px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
            position: relative;
        }

        .mv-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 0;
            background: linear-gradient(to bottom, #800020, #D4AF37);
            transition: height 0.3s ease;
        }

        .mv-card:hover::before {
            height: 100%;
        }

        .mv-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            transform: translateY(-3px);
        }

        .mv-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .mv-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #800020 0%, #A0002C 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .mv-icon i {
            font-size: 20px;
            color: white;
        }

        .mv-title {
            font-family: 'Crimson Text', serif;
            font-size: 22px;
            font-weight: 700;
            color: #1B2845;
            margin: 0;
        }

        .mv-content {
            font-size: 15px;
            line-height: 1.7;
            color: #404040;
        }

        /* Core Values Section - Improved Grid Design */
        .core-values-section {
            background: white;
            padding: 80px 0;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 50px;
        }

        .value-card {
            background: #fafafa;
            padding: 30px 25px;
            border: 1px solid #e8e8e8;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .value-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 0;
            background: linear-gradient(to right, rgba(128, 0, 32, 0.05), rgba(212, 175, 55, 0.05));
            transition: height 0.3s ease;
        }

        .value-card:hover {
            border-color: #D4AF37;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        }

        .value-card:hover::after {
            height: 100%;
        }

        .value-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #800020, #A0002C);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }

        .value-icon i {
            font-size: 24px;
            color: #D4AF37;
        }

        .value-title {
            font-family: 'Crimson Text', serif;
            font-size: 18px;
            font-weight: 700;
            color: #1B2845;
            margin: 0 0 10px 0;
        }

        .value-description {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* Fellowship Times - Full Border Cards */
        .fellowship-times-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            padding: 80px 0;
        }

        .session-group {
            margin-bottom: 50px;
        }

        .session-group:last-child {
            margin-bottom: 0;
        }

        .session-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .session-title {
            font-family: 'Crimson Text', serif;
            font-size: 26px;
            font-weight: 700;
            color: #800020;
            margin: 0 0 8px 0;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .session-title i {
            font-size: 24px;
            color: #D4AF37;
        }

        .session-subtitle {
            font-size: 15px;
            color: #666;
            margin: 0;
        }

        .times-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }

        .time-card {
            background: white;
            padding: 28px 24px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            position: relative;
        }

        .time-card:hover {
            border-color: #D4AF37;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }

        .time-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        .time-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #800020, #A0002C);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .time-icon i {
            font-size: 20px;
            color: #D4AF37;
        }

        .time-title {
            font-family: 'Crimson Text', serif;
            font-size: 19px;
            font-weight: 700;
            color: #1B2845;
            margin: 0;
        }

        .time-details {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .time-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #555;
        }

        .time-detail i {
            width: 18px;
            color: #800020;
            font-size: 13px;
        }

        /* Gallery Grid Styles - 5x5 Compact */
        .photo-gallery-container {
            width: 100%;
            position: relative;
        }

        .photo-gallery-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0;
            width: 100%;
            max-height: 600px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .photo-gallery-grid img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
        }

        .photo-gallery-grid img:hover {
            transform: scale(1.05);
            z-index: 2;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        /* Loading State */
        .gallery-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            font-size: 18px;
            color: #666;
        }

        .gallery-loading i {
            margin-right: 10px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Section Labels */
        .section-label {
            display: inline-block;
            font-family: 'Crimson Text', serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #D4AF37;
            margin-bottom: 12px;
        }

        .section-title {
            font-family: 'Crimson Text', serif;
            font-size: 36px;
            font-weight: 700;
            color: #1B2845;
            margin-bottom: 16px;
        }

        .section-subtitle {
            font-size: 16px;
            line-height: 1.7;
            color: #555;
            max-width: 800px;
            margin: 0 auto 40px;
        }

        .text-center {
            text-align: center;
        }

        /* Responsive Grid */
        @media (max-width: 992px) {
            .mv-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .photo-gallery-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .photo-gallery-grid img {
                height: 100px;
            }

            .times-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .photo-gallery-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .photo-gallery-grid img {
                height: 90px;
            }

            .section-title {
                font-size: 28px;
            }

            .values-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .photo-gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .photo-gallery-grid img {
                height: 80px;
            }
        }

        /* Lightbox Styles */
        .lightbox-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        .lightbox-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .lightbox-image {
            max-width: 100%;
            max-height: 85vh;
            object-fit: contain;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 40px;
            color: white;
            cursor: pointer;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.5);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            font-size: 30px;
            padding: 20px;
            cursor: pointer;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .lightbox-nav:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .lightbox-nav.prev {
            left: 20px;
        }

        .lightbox-nav.next {
            right: 20px;
        }

        .lightbox-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
            background: rgba(0, 0, 0, 0.7);
            padding: 15px 25px;
        }
    </style>

    <?php include get_layout('loader'); ?>

    <!-- Main wrapper-->
    <div class="page-wrapper">
        <div class="page-wrapper-inner">
            <!-- Header -->
            <header>
                <!-- Mobile Menu Wrapper -->
                <?php include get_layout('mobile-header'); ?>

                <!-- Header (Navbar for other Devices-->
                <div class="header-inner header-1">
                    <!--Sticky part-->
                    <?php include get_layout('navbar-other'); ?>
                    <!--sticky-outer-->
                </div>
                <!-- .Header (Navbar for other Devices  -->
            </header>

            <!-- About Hero Section -->
            <section class="about-hero">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <div class="container text-center">
                        <!-- <div class="hero-icon">
                            <i class="fa fa-church"></i>
                        </div> -->
                        <h1 class="hero-title">About CEP-UoK</h1>
                        <p class="hero-subtitle">Communauté des Étudiants Pentecôtistes à l'Université de Kigali</p>
                        <div class="hero-verse">
                            <i class="fa fa-book-open"></i>
                            <p>"For where two or three gather in my name, there am I with them." <span>— Matthew
                                    18:20</span></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Introduction Section -->
            <section class="about-intro pad-tb-100">
                <div class="container">
                    <div class="intro-content">
                        <span class="section-label">Who We Are</span>
                        <h2>A Christian Fellowship Transforming Campus Life</h2>
                        <p class="lead-text">CEP–UoK is a vibrant Christian students' fellowship that brings together
                            university students who desire to grow spiritually, live according to biblical values, and
                            serve God within the academic environment of the University of Kigali.</p>
                        <p>We exist as a platform for spiritual formation, leadership development, fellowship, and
                            holistic empowerment of students, equipping them to impact the Church, the University, and
                            society at large.</p>
                    </div>
                </div>
            </section>

            <!-- Mission & Vision Section - IMPROVED -->
            <section class="mission-vision-section">
                <div class="mv-container">
                    <div class="text-center">
                        <span class="section-label">Our Purpose</span>
                        <h2 class="section-title">Mission & Vision</h2>
                        <p class="section-subtitle">Guiding principles that shape everything we do at CEP-UoK</p>
                    </div>

                    <div class="mv-grid">
                        <!-- Vision Card -->
                        <div class="mv-card">
                            <div class="mv-header">
                                <div class="mv-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <h3 class="mv-title">Vision</h3>
                            </div>
                            <div class="mv-content">
                                <p>To raise Christ-centered leaders who honor God, uphold biblical values, and
                                    positively influence the Church, the University, and society.</p>
                            </div>
                        </div>

                        <!-- Mission Card -->
                        <div class="mv-card">
                            <div class="mv-header">
                                <div class="mv-icon">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                                <h3 class="mv-title">Mission</h3>
                            </div>
                            <div class="mv-content">
                                <p>To nurture students spiritually and holistically by equipping them to live out their
                                    Christian faith with responsibility, leadership, and impact through prayer, worship,
                                    biblical teaching, discipleship, and service.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Core Values Section - IMPROVED -->
            <section class="core-values-section">
                <div class="container">
                    <div class="text-center">
                        <span class="section-label">What We Stand For</span>
                        <h2 class="section-title">Core Values</h2>
                        <p class="section-subtitle">The principles that guide our fellowship and shape our community</p>
                    </div>

                    <div class="values-grid">
                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-bible"></i>
                            </div>
                            <h4 class="value-title">Faithfulness to Scripture</h4>
                            <p class="value-description">We are rooted in the Word of God, allowing it to guide our
                                beliefs, decisions, and actions in all aspects of life.</p>
                        </div>

                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-praying-hands"></i>
                            </div>
                            <h4 class="value-title">Prayer and Worship</h4>
                            <p class="value-description">We prioritize communion with God through consistent prayer and
                                heartfelt worship, recognizing our dependence on Him.</p>
                        </div>

                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h4 class="value-title">Integrity and Accountability</h4>
                            <p class="value-description">We operate with transparency, honesty, and responsibility in
                                all our dealings, both internally and externally.</p>
                        </div>

                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h4 class="value-title">Unity and Love</h4>
                            <p class="value-description">We foster a community of genuine love, mutual respect, and
                                unity that transcends backgrounds and denominations.</p>
                        </div>

                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <h4 class="value-title">Service and Responsibility</h4>
                            <p class="value-description">We are committed to serving God, the church, and our community
                                with excellence and a sense of responsibility.</p>
                        </div>

                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h4 class="value-title">Leadership and Excellence</h4>
                            <p class="value-description">We develop and empower servant leaders who pursue excellence in
                                their spiritual walk and professional endeavors.</p>
                        </div>

                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <h4 class="value-title">Self-Reliance and Empowerment</h4>
                            <p class="value-description">We encourage innovation, entrepreneurship, and economic
                                responsibility alongside spiritual growth.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Fellowship Times Section - IMPROVED -->
            <section class="fellowship-times-section">
                <div class="container">
                    <div class="text-center">
                        <span class="section-label">Join Us</span>
                        <h2 class="section-title">Fellowship Times</h2>
                        <p class="section-subtitle">Multiple opportunities to connect, worship, and grow together each
                            week</p>
                    </div>

                    <!-- Day Campus Session -->
                    <div class="session-group">
                        <div class="session-header">
                            <h3 class="session-title">
                                <i class="fas fa-sun"></i>
                                Day Campus Fellowship
                            </h3>
                            <p class="session-subtitle">Kacyiru Campus & Remera Campus</p>
                        </div>

                        <div class="times-grid">
                            <div class="time-card">
                                <div class="time-card-header">
                                    <div class="time-icon">
                                        <i class="fas fa-language"></i>
                                    </div>
                                    <h4 class="time-title">English Fellowship</h4>
                                </div>
                                <div class="time-details">
                                    <div class="time-detail">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Monday</span>
                                    </div>
                                    <div class="time-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>11:30 AM - 1:30 PM</span>
                                    </div>
                                    <div class="time-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Kacyiru Campus</span>
                                    </div>
                                </div>
                            </div>

                            <div class="time-card">
                                <div class="time-card-header">
                                    <div class="time-icon">
                                        <i class="fas fa-music"></i>
                                    </div>
                                    <h4 class="time-title">Kinyarwanda Worship</h4>
                                </div>
                                <div class="time-details">
                                    <div class="time-detail">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Wednesday</span>
                                    </div>
                                    <div class="time-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>11:30 AM - 1:30 PM</span>
                                    </div>
                                    <div class="time-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Kacyiru Campus</span>
                                    </div>
                                </div>
                            </div>

                            <div class="time-card">
                                <div class="time-card-header">
                                    <div class="time-icon">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <h4 class="time-title">Remera Campus</h4>
                                </div>
                                <div class="time-details">
                                    <div class="time-detail">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Friday</span>
                                    </div>
                                    <div class="time-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>11:30 AM - 1:30 PM</span>
                                    </div>
                                    <div class="time-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Remera Campus (New Campus)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Weekend Session -->
                    <div class="session-group">
                        <div class="session-header">
                            <h3 class="session-title">
                                <i class="fas fa-calendar-week"></i>
                                Weekend Fellowship
                            </h3>
                            <p class="session-subtitle">Both Campuses</p>
                        </div>

                        <div class="times-grid">
                            <div class="time-card">
                                <div class="time-card-header">
                                    <div class="time-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h4 class="time-title">Weekend Service</h4>
                                </div>
                                <div class="time-details">
                                    <div class="time-detail">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Saturday or Sunday</span>
                                    </div>
                                    <div class="time-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>2:00 PM - 3:30 PM</span>
                                    </div>
                                    <div class="time-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Both Campuses (As Scheduled)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Photo Gallery Section -->
            <section class="photo-gallery-section pad-tb-100 bg-light">
                <div class="container">
                    <div class="text-center">
                        <span class="section-label">Our Journey</span>
                        <h2 class="section-title">Photo Gallery</h2>
                        <p class="section-subtitle">Glimpses of fellowship, worship, and service</p>
                    </div>

                    <div class="photo-gallery-container">
                        <div class="photo-gallery-grid" id="photoGallery">
                            <div class="gallery-loading">
                                <i class="fas fa-spinner"></i>
                                <span>Loading gallery...</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center" style="margin-top: 40px;">
                        <a href="<?= url('gallery-photo') ?>" class="btn btn-primary">
                            <i class="fas fa-images"></i> View Full Gallery
                        </a>
                    </div>
                </div>
            </section>

            <!-- Identity Section -->
            <section class="identity-section pad-tb-100">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="identity-content">
                                <span class="section-label">Our Foundation</span>
                                <h2 class="section-title">Identity & Affiliation</h2>
                                <div class="identity-cards">
                                    <div class="identity-card">
                                        <i class="fas fa-cross"></i>
                                        <h4>Faith Orientation</h4>
                                        <p>Biblical and Pentecostal</p>
                                    </div>
                                    <div class="identity-card">
                                        <i class="fas fa-church"></i>
                                        <h4>Affiliation</h4>
                                        <p>ADEPR – Rwanda Pentecostal Church</p>
                                    </div>
                                    <div class="identity-card">
                                        <i class="fas fa-university"></i>
                                        <h4>Membership Base</h4>
                                        <p>Students of the University of Kigali</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="affiliation-content">
                                <span class="section-label">Our Spiritual Covering</span>
                                <h2 class="section-title">Church Affiliation</h2>
                                <p>CEP–UoK operates under the spiritual supervision of <strong>ADEPR Kimihurura
                                        International Service (Local Church)</strong> and functions in full compliance
                                    with:</p>
                                <ul class="affiliation-list">
                                    <li><i class="fas fa-check"></i> The doctrines and guidance of ADEPR – Rwanda
                                        Pentecostal Church</li>
                                    <li><i class="fas fa-check"></i> The rules, policies, and regulations of the
                                        University of Kigali</li>
                                    <li><i class="fas fa-check"></i> Applicable national laws governing student
                                        organizations</li>
                                </ul>
                                <p class="affiliation-note">CEP–UoK is a non-political, non-profit Christian fellowship
                                    committed to spiritual formation and leadership development.</p>
                                <a href="<?= url('local-church') ?>" class="btn btn-primary mt-3">
                                    <i class="fas fa-church"></i> Learn About Our Local Church
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Call to Action -->
            <section class="about-cta bg-primary text-white pad-tb-100">
                <div class="container text-center">
                    <div class="cta-content">
                        <div class="cta-icon">
                            <i class="fas fa-hands"></i>
                        </div>
                        <h2>Join Our Fellowship</h2>
                        <p>Whether you're a new student or have been at UoK for a while, there's a place for you in CEP.
                            Come experience genuine Christian fellowship, spiritual growth, and purposeful service.</p>
                        <div class="cta-buttons">
                            <a href="<?= url('contact') ?>" class="btn btn-light btn-lg">
                                <i class="fas fa-envelope"></i> Get Connected
                            </a>
                            <a href="<?= url('news') ?>" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-calendar"></i> View Events
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <?php include get_layout('footer'); ?>
        </div>
    </div>

    <!-- Lightbox -->
    <div class="lightbox-overlay" id="lightbox">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <button class="lightbox-nav prev" onclick="changeImage(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="lightbox-nav next" onclick="changeImage(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
        <div class="lightbox-info" id="lightboxInfo">
            <h3 id="lightboxTitle"></h3>
            <p id="lightboxDescription"></p>
        </div>
        <div class="lightbox-content">
            <img src="" alt="" class="lightbox-image" id="lightboxImage">
        </div>
        <div class="lightbox-controls">
            <button onclick="toggleAutoplay()" id="autoplayBtn" title="Autoplay">
                <i class="fas fa-play"></i>
            </button>
            <button onclick="downloadImage()" title="Download">
                <i class="fas fa-download"></i>
            </button>
            <button onclick="shareImage()" title="Share">
                <i class="fas fa-share-alt"></i>
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <?php include get_layout('scripts'); ?>

    <script>
        // Gallery API Configuration
        const API_URL = '<?= url("api/gallery") ?>';
        const IMG_URL = '<?= img_url("") ?>';
        const MAX_IMAGES = 30;

        // Lightbox State
        let currentImageIndex = 0;
        let images = [];
        let autoplayInterval = null;
        let isAutoplayOn = false;

        // Load Gallery Images
        document.addEventListener('DOMContentLoaded', function () {
            loadGalleryImages();
        });

        function loadGalleryImages() {
            fetch(API_URL + '?action=get_images&limit=' + MAX_IMAGES)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        images = data.data;
                        displayGallery(images);
                    } else {
                        document.getElementById('photoGallery').innerHTML =
                            '<div class="gallery-loading"><i class="fas fa-exclamation-circle"></i><span>No images available</span></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading gallery:', error);
                    document.getElementById('photoGallery').innerHTML =
                        '<div class="gallery-loading"><i class="fas fa-exclamation-circle"></i><span>Failed to load gallery</span></div>';
                });
        }

        function displayGallery(images) {
            const gallery = document.getElementById('photoGallery');
            gallery.innerHTML = '';

            images.slice(0, MAX_IMAGES).forEach((image, index) => {
                const img = document.createElement('img');
                img.src = IMG_URL + image.image_url;
                img.alt = image.title;
                img.setAttribute('data-title', image.title);
                img.setAttribute('data-description', image.description || '');
                img.setAttribute('data-category', image.category || '');
                img.onclick = () => openLightbox(index);
                img.onerror = function () {
                    this.src = 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=400&q=80';
                };
                gallery.appendChild(img);
            });
        }

        // Lightbox Functions
        function openLightbox(index) {
            currentImageIndex = index;
            updateLightboxImage();
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = 'auto';
            if (isAutoplayOn) {
                toggleAutoplay();
            }
        }

        function changeImage(direction) {
            currentImageIndex += direction;

            if (currentImageIndex < 0) {
                currentImageIndex = images.length - 1;
            } else if (currentImageIndex >= images.length) {
                currentImageIndex = 0;
            }

            updateLightboxImage();
        }

        function updateLightboxImage() {
            const image = images[currentImageIndex];
            document.getElementById('lightboxImage').src = IMG_URL + image.image_url;
            document.getElementById('lightboxTitle').textContent = image.title;
            document.getElementById('lightboxDescription').textContent = image.description || '';
        }

        function toggleAutoplay() {
            isAutoplayOn = !isAutoplayOn;
            const btn = document.getElementById('autoplayBtn');

            if (isAutoplayOn) {
                btn.innerHTML = '<i class="fas fa-pause"></i>';
                autoplayInterval = setInterval(() => changeImage(1), 3000);
            } else {
                btn.innerHTML = '<i class="fas fa-play"></i>';
                clearInterval(autoplayInterval);
            }
        }

        function downloadImage() {
            const image = images[currentImageIndex];
            const link = document.createElement('a');
            link.href = IMG_URL + image.image_url;
            link.download = image.title + '.jpg';
            link.click();
        }

        function shareImage() {
            const image = images[currentImageIndex];
            if (navigator.share) {
                navigator.share({
                    title: image.title,
                    text: image.description,
                    url: window.location.href
                });
            } else {
                alert('Sharing not supported on this browser');
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', function (e) {
            if (document.getElementById('lightbox').classList.contains('active')) {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') changeImage(-1);
                if (e.key === 'ArrowRight') changeImage(1);
            }
        });

        // Close on background click
        document.getElementById('lightbox').addEventListener('click', function (e) {
            if (e.target === this) {
                closeLightbox();
            }
        });
    </script>

    <!-- About Page Custom JS -->
    <script src="<?= js_url('about.js') ?>"></script>
</body>

</html>