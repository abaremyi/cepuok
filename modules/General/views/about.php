<?php
/**
 * About CEP Page
 * File: modules/General/views/about-cep.php
 */

require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../config/database.php';

// Fetch page content from database
$db = Database::getInstance();
$pageContent = [];

$home='';
$if_not_home_class = '';

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
function getContent($key, $field = 'content', $default = '') {
    global $pageContent;
    return isset($pageContent[$key]) ? $pageContent[$key][$field] : $default;
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <?php include get_layout('header'); ?>
    <title>About CEP - <?php echo getContent('hero_title', 'content', 'CEP University of Kigali'); ?></title>
    
    <!-- Google Fonts for the beautiful typography -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Lora:ital,wght@0,400;0,600;1,400&family=Crimson+Text:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- CEP Custom Styles -->
    <link rel="stylesheet" href="<?= css_url('cep-custom.css') ?>">
</head>

<body data-res-from="1025">
    <?php include get_layout('loader'); ?>
    
    <!-- Mobile Menu Wrapper -->
    <?php include get_layout('mobile-header'); ?>
    
    <!-- Main wrapper-->
    <div class="page-wrapper">
        <div class="page-wrapper-inner">
            <!-- Header -->
            <header>
                <?php include get_layout('topbar'); ?>
                <?php include get_layout('navbar'); ?>
            </header>
            
            <!-- About Hero Section -->
            <section class="about-hero">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <div class="container">
                        <div class="hero-icon">
                            <i class="fas fa-church"></i>
                        </div>
                        <h1 class="hero-title">
                            <?= getContent('hero_title', 'content', 'About CEP UoK') ?>
                        </h1>
                        <p class="hero-subtitle">
                            <?= getContent('hero_subtitle', 'content', 'Communauté des Étudiants Pentecôtistes à l\'Université de Kigali') ?>
                        </p>
                        <div class="hero-verse">
                            <i class="fas fa-book-open"></i>
                            <p>
                                <?= getContent('hero_verse', 'content', '"For where two or three gather in my name, there am I with them."') ?>
                                <span><?= getContent('hero_verse_ref', 'content', '— Matthew 18:20') ?></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="scroll-indicator">
                    <div class="scroll-icon"></div>
                    <span>Scroll to explore</span>
                </div>
            </section>

            <!-- Who We Are Section -->
            <section class="who-we-are-section pad-tb-100">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="section-intro">
                                <span class="section-label">Our Identity</span>
                                <h2 class="section-title">
                                    <?= getContent('who_title', 'content', 'Who We Are') ?>
                                </h2>
                                <div class="intro-content">
                                    <?= getContent('who_content', 'content', '<p>CEP–UoK (Communauté des Étudiants Pentecôtistes à l\'Université de Kigali) is a Christian students\' fellowship that brings together university students who desire to grow spiritually, live according to biblical values, and serve God within the academic environment of the University of Kigali.</p>') ?>
                                </div>
                                <div class="identity-features">
                                    <div class="feature-item">
                                        <i class="fas fa-cross"></i>
                                        <h4>Biblical & Pentecostal</h4>
                                        <p>Rooted in Scripture and the power of the Holy Spirit</p>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-users"></i>
                                        <h4>Student-Led</h4>
                                        <p>By students, for students, empowering peer ministry</p>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-hands-helping"></i>
                                        <h4>Service-Oriented</h4>
                                        <p>Impacting campus, church, and community</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="identity-image">
                                <img src="<?= getContent('who_image', 'image_url', img_url('about/who-we-are.jpg')) ?>" alt="CEP UoK Fellowship" class="img-fluid">
                                <div class="image-overlay">
                                    <div class="overlay-content">
                                        <h3>Since 2016</h3>
                                        <p>Growing in Faith & Service</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Mission & Vision Section -->
            <section class="mission-vision-section bg-light pad-tb-100">
                <div class="container">
                    <div class="section-header text-center">
                        <span class="section-label">Our Purpose</span>
                        <h2 class="section-title">Mission & Vision</h2>
                        <p class="section-subtitle">Called to serve, empowered to lead, committed to Christ</p>
                    </div>
                    
                    <div class="row mv-cards">
                        <!-- Vision Card -->
                        <div class="col-lg-6 mb-4">
                            <div class="mv-card vision-card">
                                <div class="mv-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <h3 class="mv-title">Our Vision</h3>
                                <div class="mv-content">
                                    <p><?= getContent('vision', 'content', 'To raise Christ-centered leaders who honor God, uphold biblical values, and positively influence the Church, the University, and society.') ?></p>
                                </div>
                                <div class="mv-decoration">
                                    <i class="fas fa-mountain"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mission Card -->
                        <div class="col-lg-6 mb-4">
                            <div class="mv-card mission-card">
                                <div class="mv-icon">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                                <h3 class="mv-title">Our Mission</h3>
                                <div class="mv-content">
                                    <p><?= getContent('mission_intro', 'content', 'CEP–UoK\'s mission is to nurture students spiritually and holistically by equipping them to live out their Christian faith with responsibility, leadership, and impact.') ?></p>
                                    <ul class="mission-list">
                                        <li><i class="fas fa-check-circle"></i> Build students spiritually through prayer, worship, and biblical teaching</li>
                                        <li><i class="fas fa-check-circle"></i> Disciple and mentor students into mature Christian leadership</li>
                                        <li><i class="fas fa-check-circle"></i> Foster unity, love, and mutual support among members</li>
                                        <li><i class="fas fa-check-circle"></i> Advance evangelism and outreach within and beyond the university</li>
                                        <li><i class="fas fa-check-circle"></i> Collaborate with the local church and university authorities</li>
                                        <li><i class="fas fa-check-circle"></i> Promote self-reliance, leadership accountability, and economic empowerment</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Core Values Section -->
            <section class="core-values-section pad-tb-100">
                <div class="container">
                    <div class="section-header text-center">
                        <span class="section-label">What We Stand For</span>
                        <h2 class="section-title">Core Values</h2>
                        <p class="section-subtitle">Principles that guide our fellowship and ministry</p>
                    </div>
                    
                    <div class="row values-grid">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="value-card">
                                <div class="value-icon">
                                    <i class="fas fa-bible"></i>
                                </div>
                                <h4>Faithfulness to Scripture</h4>
                                <p>The Bible is our foundation, guide, and ultimate authority in all we do.</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="value-card">
                                <div class="value-icon">
                                    <i class="fas fa-praying-hands"></i>
                                </div>
                                <h4>Prayer & Worship</h4>
                                <p>We prioritize communion with God through consistent prayer and heartfelt worship.</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="value-card">
                                <div class="value-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h4>Integrity & Accountability</h4>
                                <p>We uphold honesty, transparency, and responsibility in all our actions.</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="value-card">
                                <div class="value-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h4>Unity & Love</h4>
                                <p>We cultivate genuine fellowship and demonstrate Christ's love to all.</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="value-card">
                                <div class="value-icon">
                                    <i class="fas fa-hands"></i>
                                </div>
                                <h4>Service & Responsibility</h4>
                                <p>We serve God and others with dedication, humility, and excellence.</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="value-card">
                                <div class="value-icon">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <h4>Leadership & Excellence</h4>
                                <p>We develop Christ-like leaders who pursue excellence in all endeavors.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Fellowship Times Section -->
            <section class="fellowship-times-section bg-light pad-tb-100">
                <div class="container">
                    <div class="section-header text-center">
                        <span class="section-label">Join Us</span>
                        <h2 class="section-title">Fellowship Times</h2>
                        <p class="section-subtitle">We meet regularly for worship, prayer, and fellowship</p>
                    </div>
                    
                    <div class="row times-grid">
                        <?php
                        // Fetch fellowship times from recurring_events table
                        try {
                            $query = "SELECT * FROM recurring_events WHERE status = 'active' ORDER BY display_order ASC";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $fellowshipTimes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($fellowshipTimes as $time):
                                $dayIcon = [
                                    'Monday' => 'fa-sun',
                                    'Wednesday' => 'fa-star',
                                    'Thursday' => 'fa-calendar',
                                    'Sunday' => 'fa-praying-hands'
                                ];
                                $icon = isset($dayIcon[$time['day_of_week']]) ? $dayIcon[$time['day_of_week']] : 'fa-calendar-alt';
                        ?>
                        <div class="col-lg-6 mb-4">
                            <div class="time-card">
                                <div class="time-icon">
                                    <i class="fas <?= $icon ?>"></i>
                                </div>
                                <h4><?= htmlspecialchars($time['title']) ?></h4>
                                <div class="time-details">
                                    <p><i class="fas fa-calendar-day"></i> <?= $time['day_of_week'] ?></p>
                                    <p><i class="fas fa-clock"></i> <?= date('g:i A', strtotime($time['start_time'])) ?> - <?= date('g:i A', strtotime($time['end_time'])) ?></p>
                                    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($time['campus']) ?></p>
                                </div>
                                <p class="time-description"><?= htmlspecialchars($time['description']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php } catch (PDOException $e) {
                            error_log("Error fetching fellowship times: " . $e->getMessage());
                        } ?>
                    </div>
                </div>
            </section>

            <!-- Affiliation Section -->
            <section class="affiliation-section pad-tb-100">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="affiliation-image">
                                <img src="<?= img_url('about/affiliation.jpg') ?>" alt="ADEPR Church" class="img-fluid">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="affiliation-content">
                                <span class="section-label">Our Spiritual Covering</span>
                                <h2 class="section-title">Church Affiliation</h2>
                                <p><?= getContent('affiliation', 'content', 'CEP–UoK operates under the spiritual supervision of <strong>ADEPR Kimihurura International Service (Local Church)</strong> and functions in full compliance with:') ?></p>
                                <ul class="affiliation-list">
                                    <li><i class="fas fa-check"></i> The doctrines and guidance of ADEPR – Rwanda Pentecostal Church</li>
                                    <li><i class="fas fa-check"></i> The rules, policies, and regulations of the University of Kigali</li>
                                    <li><i class="fas fa-check"></i> Applicable national laws governing student organizations</li>
                                </ul>
                                <p class="affiliation-note">CEP–UoK is a non-political, non-profit Christian fellowship committed to spiritual formation and leadership development.</p>
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
                        <p>Whether you're a new student or have been at UoK for a while, there's a place for you in CEP. Come experience genuine Christian fellowship, spiritual growth, and purposeful service.</p>
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

    <!-- Scripts -->
    <?php include get_layout('scripts'); ?>
    
    <!-- About Page Custom JS -->
    <script src="<?= js_url('about.js') ?>"></script>
</body>
</html>