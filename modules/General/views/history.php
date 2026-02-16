<!doctype html>
<html class="no-js" lang="zxx">

<?php
/**
 * History Page - IMPROVED VERSION
 * File: modules/General/views/history.php
 * Following about-cep.php organization
 */

require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../modules/Leadership/models/LeadershipModel.php';

// Get database instance
$db = Database::getInstance();
$leadershipModel = new LeadershipModel($db);

// Fetch history timeline
$historyTimeline = $leadershipModel->getHistoryTimeline();

// Fetch page content
$pageContent = [];
try {
    $query = "SELECT section_name, content FROM page_content 
              WHERE page_name = 'home' AND section_name IN ('history_video', 'history_title', 'history_description') 
              AND status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $pageContent[$row['section_name']] = $row['content'];
    }
} catch (PDOException $e) {
    error_log("Error fetching history content: " . $e->getMessage());
}

// Set defaults
$videoUrl = isset($pageContent['history_video']) ? $pageContent['history_video'] : 'https://www.youtube.com/embed/DaGMZsmDKBU';
$historyTitle = isset($pageContent['history_title']) ? $pageContent['history_title'] : 'Our Journey of Faith';
$historyDescription = isset($pageContent['history_description']) ? $pageContent['history_description'] : 'Journey through the remarkable history of CEP UoK.';
?>
    <?php include get_layout('header-2'); ?>

<body data-res-from="1025">
    
    <style>
        /* ========== HISTORY PAGE STYLES ========== */
        
        /* Hero Section */
        .history-hero {
            position: relative;
            min-height: 35vh;
            max-height: 45vh;
            background: linear-gradient(135deg, rgba(12, 23, 45, 0.85), rgba(12, 23, 45, 0.85) ),
                                url("<?= img_url('title-history.jpg') ?>");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            padding: 80px 20px;
        }
        
        .hero-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: rgba(212, 175, 55, 0.2);
            border: 3px solid #D4AF37;
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
            margin: 0 0 16px 0;
            color: white;
        }
        
        .hero-subtitle {
            font-size: 20px;
            margin: 0 0 24px 0;
            color: rgba(255,255,255,0.9);
        }
        
        .hero-verse {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 30px;
            font-style: italic;
            color: rgba(255,255,255,0.8);
        }
        
        .hero-verse i {
            font-size: 20px;
            color: #D4AF37;
        }
        
        .hero-verse span {
            display: block;
            margin-top: 8px;
            font-size: 14px;
        }
        
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            color: rgba(255,255,255,0.7);
        }
        
        .scroll-icon {
            width: 30px;
            height: 50px;
            border: 2px solid rgba(255,255,255,0.5);
            margin: 0 auto 10px;
            position: relative;
        }
        
        .scroll-icon::before {
            content: '';
            width: 6px;
            height: 6px;
            background: rgba(255,255,255,0.7);
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            animation: scroll 2s infinite;
        }
        
        @keyframes scroll {
            0%, 20% { top: 10px; opacity: 1; }
            100% { top: 30px; opacity: 0; }
        }
        
        /* Video Section */
        .history-video-section {
            background: white;
            padding: 80px 0;
        }
        
        .video-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border: 2px solid #e0e0e0;
            background: #000;
        }
        
        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
        }
        
        .play-button {
            width: 80px;
            height: 80px;
            background: rgba(212,175,55,0.9);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .play-button:hover {
            background: #D4AF37;
            transform: scale(1.1);
        }
        
        .video-description {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            text-align: center;
        }
        
        /* Timeline Section */
        .milestones-section {
            background: #f8f9fa;
            padding: 80px 0;
        }
        
        .milestones-timeline {
            position: relative;
            max-width: 1000px;
            margin: 60px auto 0;
        }
        
        .milestones-timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 3px;
            background: linear-gradient(to bottom, #800020, #D4AF37);
            transform: translateX(-50%);
        }
        
        .milestone-item {
            position: relative;
            margin-bottom: 60px;
            width: 50%;
        }
        
        .milestone-item.left {
            left: 0;
            padding-right: 60px;
        }
        
        .milestone-item.right {
            left: 50%;
            padding-left: 60px;
        }
        
        .milestone-content {
            background: white;
            padding: 28px 24px;
            border: 2px solid #e0e0e0;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .milestone-content:hover {
            border-color: #D4AF37;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        
        .milestone-year {
            font-family: 'Crimson Text', serif;
            font-size: 32px;
            font-weight: 700;
            color: #800020;
            margin-bottom: 12px;
        }
        
        .milestone-content h3 {
            font-family: 'Crimson Text', serif;
            font-size: 20px;
            font-weight: 700;
            color: #1B2845;
            margin: 0 0 12px 0;
        }
        
        .milestone-content p {
            font-size: 15px;
            line-height: 1.7;
            color: #555;
            margin: 0;
        }
        
        .milestone-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #800020, #A0002C);
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            bottom: 20px;
            right: 20px;
        }
        
        .milestone-icon i {
            font-size: 24px;
            color: #D4AF37;
        }
        
        .timeline-dot {
            position: absolute;
            width: 20px;
            height: 20px;
            background: white;
            border: 4px solid #800020;
            top: 30px;
        }
        
        .milestone-item.left .timeline-dot {
            right: -10px;
        }
        
        .milestone-item.right .timeline-dot {
            left: -10px;
        }
        
        .timeline-dot.current {
            width: 28px;
            height: 28px;
            border-color: #D4AF37;
            border-width: 5px;
        }
        
        /* Legacy Section */
        .legacy-section {
            background: white;
            padding: 80px 0;
        }
        
        .legacy-content h3 {
            font-family: 'Crimson Text', serif;
            font-size: 24px;
            font-weight: 700;
            color: #1B2845;
            margin: 30px 0 16px 0;
        }
        
        .legacy-content p {
            font-size: 15px;
            line-height: 1.7;
            color: #555;
            margin-bottom: 16px;
        }
        
        .legacy-images {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .legacy-img {
            position: relative;
            overflow: hidden;
            border: 2px solid #e0e0e0;
        }
        
        .legacy-img.primary {
            grid-column: 1 / -1;
        }
        
        .legacy-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .legacy-img:hover img {
            transform: scale(1.05);
        }
        
        .img-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 20px;
            color: white;
        }
        
        .img-caption span {
            font-family: 'Crimson Text', serif;
            font-size: 24px;
            font-weight: 700;
            color: #D4AF37;
        }
        
        .img-caption p {
            margin: 8px 0 0 0;
            color: white;
        }
        
        /* Impact Stats */
        .impact-stats {
            background: linear-gradient(135deg, #800020, #A0002C);
            padding: 80px 0;
        }
        
        .stats-row {
            margin-top: 50px;
        }
        
        .stat-item {
            text-align: center;
            padding: 30px 20px;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            background: rgba(255,255,255,0.15);
            border-color: #D4AF37;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background: rgba(212,175,55,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-icon i {
            font-size: 28px;
            color: #D4AF37;
        }
        
        .stat-number {
            font-family: 'Crimson Text', serif;
            font-size: 48px;
            font-weight: 700;
            color: white;
            margin: 0 0 8px 0;
        }
        
        .stat-label {
            font-size: 16px;
            color: rgba(255,255,255,0.9);
            margin: 0;
        }
        
        /* Vision Forward */
        .vision-forward {
            background: white;
            padding: 80px 0;
        }
        
        .vision-image {
            border: 2px solid #e0e0e0;
        }
        
        .vision-image img {
            width: 100%;
            height: auto;
        }
        
        .vision-list {
            list-style: none;
            padding: 0;
            margin: 30px 0 0 0;
        }
        
        .vision-list li {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .vision-list li:last-child {
            border-bottom: none;
        }
        
        .vision-list i {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #800020;
            font-size: 18px;
            margin-top: 4px;
        }
        
        .vision-list strong {
            display: block;
            font-family: 'Crimson Text', serif;
            font-size: 18px;
            color: #1B2845;
            margin-bottom: 6px;
        }
        
        .vision-list p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        
        /* CTA Section */
        .history-cta {
            background: linear-gradient(135deg, #1B2845 0%, #800020 100%);
            padding: 80px 0;
        }
        
        .cta-content h2 {
            font-family: 'Crimson Text', serif;
            font-size: 36px;
            font-weight: 700;
            color: white;
            margin: 0 0 16px 0;
        }
        
        .cta-content p {
            font-size: 18px;
            color: rgba(255,255,255,0.9);
            max-width: 700px;
            margin: 0 auto 40px;
        }
        
        .cta-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Section Common Styles */
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
        
        .text-white {
            color: white !important;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 36px;
            }
            
            .milestones-timeline::before {
                left: 30px;
            }
            
            .milestone-item {
                width: 100%;
                left: 0 !important;
                padding-left: 80px !important;
                padding-right: 0 !important;
            }
            
            .milestone-item .timeline-dot {
                left: 21px !important;
                right: auto !important;
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 28px;
            }
            
            .section-title {
                font-size: 28px;
            }
            
            .legacy-images {
                grid-template-columns: 1fr;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
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
            
            <!-- History Hero Section -->
            <section class="history-hero">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <div class="container">
                        <h1 class="hero-title">Our History</h1>
                        <p class="hero-subtitle">A Legacy of Faith, Fellowship, and Transformation</p>
                        <div class="hero-verse">
                            <i class="fas fa-book-open"></i>
                            <p>"Remember the days of old; consider the generations long past." 
                            <span>— Deuteronomy 32:7</span></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Video Section -->
            <section class="history-video-section">
                <div class="container">
                    <div class="section-header text-center">
                        <span class="section-label">Watch Our Story</span>
                        <h2 class="section-title"><?= htmlspecialchars($historyTitle) ?></h2>
                        <p class="section-subtitle"><?= htmlspecialchars($historyDescription) ?></p>
                    </div>
                    
                    <div class="video-container">
                        <div class="video-wrapper">
                            <div class="video-overlay" id="videoOverlay">
                                <button class="play-button" id="playButton">
                                    <i class="fas fa-play"></i>
                                </button>
                            </div>
                            <iframe id="historyVideo" 
                                    src="<?= htmlspecialchars($videoUrl) ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                            </iframe>
                        </div>
                        <div class="video-description">
                            <p id="videoDescription"><?= htmlspecialchars($historyDescription) ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Timeline Section -->
            <section class="milestones-section">
                <div class="container">
                    <div class="section-header text-center">
                        <span class="section-label">Our Journey</span>
                        <h2 class="section-title">Key Milestones</h2>
                        <p class="section-subtitle">Celebrating God's faithfulness through the years</p>
                    </div>
                    
                    <div class="milestones-timeline">
                        <?php 
                        $isLeft = true;
                        foreach ($historyTimeline as $index => $milestone): 
                            $isCurrent = isset($milestone['is_current']) && $milestone['is_current'] == 1;
                        ?>
                        <div class="milestone-item <?= $isLeft ? 'left' : 'right' ?>">
                            <div class="milestone-content">
                                <div class="milestone-year"><?= htmlspecialchars($milestone['year']) ?></div>
                                <h3><?= htmlspecialchars($milestone['title']) ?></h3>
                                <p><?= htmlspecialchars($milestone['description']) ?></p>
                                <div class="milestone-icon">
                                    <i class="<?= htmlspecialchars($milestone['icon_class']) ?>"></i>
                                </div>
                            </div>
                            <div class="timeline-dot <?= $isCurrent ? 'current' : '' ?>"></div>
                        </div>
                        <?php 
                            $isLeft = !$isLeft;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </section>

            <!-- Legacy Section -->
            <section class="legacy-section">
                <div class="container">
                    <div class="section-header text-center">
                        <span class="section-label">Our Heritage</span>
                        <h2 class="section-title">Built on Faith and Service</h2>
                    </div>
                    
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="legacy-content">
                                <p>CEP UoK was founded in 2016 as a space where students at the University of Kigali could gather for prayer, worship, and fellowship. What started as a small group has grown into a vibrant community with dual sessions serving students across multiple campuses.</p>
                                
                                <h3>Growth and Expansion</h3>
                                <p>From 2016 to 2022, CEP operated as a unified fellowship. In 2023, recognizing the diverse schedules and needs of our members, we launched Day and Weekend sessions, allowing more students to participate in meaningful fellowship at times that work for them.</p>
                                
                                <h3>Emphasis on Self-Reliance</h3>
                                <p>From 2019 onwards, CEP leadership intentionally emphasized preparing members for life beyond university. We introduced continuous sensitization programs on entrepreneurship, job creation, and leadership accountability.</p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="legacy-images">
                                <div class="legacy-img primary">
                                    <img src="<?= img_url('about/legacy-1.jpg') ?>" alt="CEP Early Days" class="img-fluid" onerror="this.src='https://images.unsplash.com/photo-1529070538774-1843cb3265df?w=800'">
                                    <div class="img-caption">
                                        <span>2016</span>
                                        <p>The Beginning</p>
                                    </div>
                                </div>
                                <div class="legacy-img secondary">
                                    <img src="<?= img_url('about/legacy-2.jpg') ?>" alt="CEP Growth" class="img-fluid" onerror="this.src='https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=800'">
                                    <div class="img-caption">
                                        <span>2022</span>
                                        <p>Dual Sessions Era</p>
                                    </div>
                                </div>
                                <div class="legacy-img tertiary">
                                    <img src="<?= img_url('about/legacy-3.jpg') ?>" alt="CEP Today" class="img-fluid" onerror="this.src='https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800'">
                                    <div class="img-caption">
                                        <span>2026</span>
                                        <p>Thriving Today</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Impact Statistics -->
            <section class="impact-stats">
                <div class="container">
                    <div class="section-header text-center">
                        <span class="section-label text-white">Our Impact</span>
                        <h2 class="section-title text-white">By the Numbers</h2>
                        <p class="section-subtitle text-white">The fruit of faithful service and God's blessing</p>
                    </div>
                    
                    <div class="row stats-row">
                        <?php
                        // Fetch quick stats
                        try {
                            $query = "SELECT * FROM quick_stats WHERE status = 'active' ORDER BY display_order ASC LIMIT 8";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($stats)) {
                                // Default stats if none in database
                                $stats = [
                                    ['stat_icon' => 'fas fa-users', 'stat_value' => '500+', 'stat_label' => 'Active Members'],
                                    ['stat_icon' => 'fas fa-calendar', 'stat_value' => '10', 'stat_label' => 'Years of Service'],
                                    ['stat_icon' => 'fas fa-church', 'stat_value' => '200+', 'stat_label' => 'Weekly Attendees'],
                                    ['stat_icon' => 'fas fa-cross', 'stat_value' => '∞', 'stat_label' => 'God\'s Faithfulness']
                                ];
                            }
                            
                            foreach ($stats as $stat):
                        ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="<?= htmlspecialchars($stat['stat_icon']) ?>"></i>
                                </div>
                                <h3 class="stat-number" data-count="<?= htmlspecialchars($stat['stat_value']) ?>">
                                    <?= htmlspecialchars($stat['stat_value']) ?>
                                </h3>
                                <p class="stat-label"><?= htmlspecialchars($stat['stat_label']) ?></p>
                            </div>
                        </div>
                        <?php 
                            endforeach;
                        } catch (PDOException $e) {
                            error_log("Error fetching stats: " . $e->getMessage());
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Vision Forward -->
            <section class="vision-forward">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="vision-image">
                                <img src="<?= img_url('about/vision-forward.jpg') ?>" alt="Future Vision" class="img-fluid" onerror="this.src='https://images.unsplash.com/photo-1552664730-d307ca884978?w=800'">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="vision-content">
                                <span class="section-label">Looking Ahead</span>
                                <h2 class="section-title">Our Vision Forward</h2>
                                <p>As we look to the future, CEP UoK remains committed to raising Christ-centered leaders who will impact not only the university but also the Church and society at large.</p>
                                <ul class="vision-list">
                                    <li>
                                        <i class="fas fa-arrow-right"></i>
                                        <div>
                                            <strong>Expanded Ministry Reach</strong>
                                            <p>Extending our impact to more campuses and communities</p>
                                        </div>
                                    </li>
                                    <li>
                                        <i class="fas fa-arrow-right"></i>
                                        <div>
                                            <strong>Leadership Development</strong>
                                            <p>Producing more equipped, empowered, and effective Christian leaders</p>
                                        </div>
                                    </li>
                                    <li>
                                        <i class="fas fa-arrow-right"></i>
                                        <div>
                                            <strong>Economic Empowerment</strong>
                                            <p>Supporting members' entrepreneurship and self-reliance initiatives</p>
                                        </div>
                                    </li>
                                    <li>
                                        <i class="fas fa-arrow-right"></i>
                                        <div>
                                            <strong>Stronger Partnerships</strong>
                                            <p>Deepening relationships with local churches and ministry organizations</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Call to Action -->
            <section class="history-cta">
                <div class="container text-center">
                    <div class="cta-content">
                        <h2>Be Part of Our Story</h2>
                        <p>The history of CEP UoK is still being written, and you can be part of it. Join us as we continue to serve God, grow in faith, and impact the university for Christ.</p>
                        <div class="cta-buttons">
                            <a href="<?= url('contact') ?>" class="btn btn-light btn-lg">
                                <i class="fas fa-envelope"></i> Connect With Us
                            </a>
                            <a href="<?= url('leadership') ?>" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-users"></i> Meet Our Leaders
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
    
    <script>
        // Video play button
        document.addEventListener('DOMContentLoaded', function() {
            const playButton = document.getElementById('playButton');
            const videoOverlay = document.getElementById('videoOverlay');
            const video = document.getElementById('historyVideo');
            
            if (playButton && videoOverlay) {
                playButton.addEventListener('click', function() {
                    videoOverlay.style.display = 'none';
                    // Add autoplay to iframe src
                    const src = video.getAttribute('src');
                    video.setAttribute('src', src + '?autoplay=1');
                });
            }
        });
    </script>
</body>
</html>