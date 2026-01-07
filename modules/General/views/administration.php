<?php
// modules/General/views/administration.php

// Include paths configuration
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";

// Include header
include_once get_layout('header');

// Fetch administration data from API
function fetchAdministrationData() {
    $api_url = url() . '/api/administration';
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url . '?action=get_all_data');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            error_log("CURL Error: " . curl_error($ch));
            curl_close($ch);
            return getDefaultData();
        }
        
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['success']) && $data['success']) {
                return $data['data'];
            }
        }
        
        return getDefaultData();
        
    } catch (Exception $e) {
        error_log("API Fetch Error: " . $e->getMessage());
        return getDefaultData();
    }
}

function getDefaultData() {
    return [
        'leadership' => [],
        'orgChart' => null,
        'statistics' => [
            'total_leadership' => 0,
            'years_experience' => date('Y') - 2013,
            'quick_stats_years' => date('Y') - 2013
        ]
    ];
}

// Fetch data
$adminData = fetchAdministrationData();

// Extract data
$leadershipTeam = $adminData['leadership'] ?? [];
$orgChart = $adminData['orgChart'] ?? null;
$stats = $adminData['statistics'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Mount Carmel School</title>
    
    <!-- Include header scripts -->
    <?php include_once get_layout('header'); ?>
</head>

<body>

    <!-- Control Active Nav Link -->
    <?php
    $home = 'off';
    $services = 'off';
    $work = 'off';
    $about = 'active';
    $news = 'off';
    $contacts = 'off';
    ?>
    
    <!-- Navbar -->
    <?php include_once get_layout('navbar'); ?>

    <!-- Page Header -->
    <header class="admin-page-header">
        <div class="container">
            <h1>Our Administration</h1>
            <p>Meet the dedicated leaders shaping our educational excellence</p>
            <div class="admin-breadcrumb">
                <a href="/">Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>Administration</span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="admin-container">
        
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="<?= img_url('logo-only.png') ?>" alt="Logo" onerror="this.style.display='none'">
                </div>
                <h1 class="sidebar-title">Administration</h1>
                <p class="sidebar-subtitle">Leadership & Structure</p>
            </div>

            <!-- Quick Stats Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title">Quick Stats</h3>
                <div class="admin-stats">
                    <div class="stat-item">
                        <i class="fas fa-user-tie"></i>
                        <div>
                            <span class="stat-number" id="totalLeadership"><?= $stats['total_leadership'] ?? 0 ?></span>
                            <span class="stat-label">Leadership Team</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-award"></i>
                        <div>
                            <span class="stat-number"><?= $stats['quick_stats_years'] ?? 0 ?>+</span>
                            <span class="stat-label">Years Experience</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <span class="stat-number"><?= date('Y') - 2013 ?>+</span>
                            <span class="stat-label">Years Established</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Info Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title">Contact Info</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>admin@mountcarmel.ac.rw</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+250 789 121 680</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Kicukiro, Kigali</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Mon - Fri: 8AM - 5PM</span>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="sidebar-widget">
                <h3 class="widget-title">Quick Links</h3>
                <div class="quick-links">
                    <a href="#leadership" class="quick-link active">
                        <i class="fas fa-user-tie"></i>
                        <span>Leadership Team</span>
                    </a>
                    <a href="#structure" class="quick-link">
                        <i class="fas fa-project-diagram"></i>
                        <span>Organization Structure</span>
                    </a>
                    <a href="#contact" class="quick-link">
                        <i class="fas fa-headset"></i>
                        <span>Contact Administration</span>
                    </a>
                </div>
            </div>

            <!-- About Widget -->
            <div class="sidebar-widget sidebar-about">
                <h3 class="widget-title">About</h3>
                <p>Our administration team is committed to providing excellent leadership and ensuring the smooth operation of Mount Carmel School.</p>
                <a href="#contact" class="subscribe-link">Contact Us <i class="fas fa-arrow-right"></i></a>
            </div>

            <!-- Social Links -->
            <div class="sidebar-social">
                <a href="#" aria-label="Twitter">
                    <svg width="16" height="16" viewBox="0 0 1200 1227" fill="currentColor">
                        <path d="M714.163 519.284L1160.89 0H1055.03L667.137 450.887L357.328 0H0L468.492 681.821L0 1226.37H105.866L515.491 750.218L842.672 1226.37H1200L714.137 519.284H714.163ZM569.165 687.828L521.697 619.934L144.011 79.6944H306.615L611.412 515.685L658.88 583.579L1055.08 1150.3H892.476L569.165 687.854V687.828Z"/>
                    </svg>
                </a>
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" aria-label="RSS"><i class="fas fa-rss"></i></a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            
            <!-- Featured Leadership -->
            <section class="featured-leadership" id="leadership">
                <div class="section-header">
                    <h2>Leadership Team</h2>
                    <p>Our experienced administrators committed to student success and academic excellence</p>
                </div>
                
                <div class="leadership-grid" id="leadershipGrid">
                    <?php if (!empty($leadershipTeam)): ?>
                        <?php foreach ($leadershipTeam as $leader): ?>
                            <article class="leader-card" data-category="leadership">
                                <div class="leader-thumbnail">
                                    <img src="<?= !empty($leader['image_url']) ? img_url($leader['image_url']) : 'https://ui-avatars.com/api/?name=' . urlencode($leader['full_name']) . '&background=0d47a1&color=fff&size=400' ?>" 
                                         alt="<?= htmlspecialchars($leader['full_name']) ?>"
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($leader['full_name']) ?>&background=0d47a1&color=fff&size=400'">
                                    <?php if ($leader['role_badge']): ?>
                                        <span class="leader-badge"><?= htmlspecialchars($leader['role_badge']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="leader-body">
                                    <div class="leader-meta">
                                        <span class="leader-position"><?= htmlspecialchars($leader['position']) ?></span>
                                        
                                    </div>
                                    <h3 class="leader-title"><?= htmlspecialchars($leader['full_name']) ?></h3>
                                    
                                    <button class="btn-view-profile" onclick="openLeaderModal(<?= $leader['id'] ?>)">
                                        View Full Profile
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-content">
                            <i class="fas fa-user-tie"></i>
                            <p>Leadership team information coming soon.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Organizational Structure -->
            <section id="structure" class="org-structure-section">
                <div class="section-header">
                    <h2>Organizational Structure</h2>
                    <p>Our hierarchical framework ensuring effective communication and management</p>
                </div>
                
                <div class="org-chart-container">
                    <?php if ($orgChart && isset($orgChart['image_url'])): ?>
                        <div class="org-chart-image">
                            <img src="<?= img_url($orgChart['image_url']) ?>" 
                                 alt="Mount Carmel School Organizational Structure"
                                 onerror="this.src='<?= img_url('org-chart.png') ?>'">
                        </div>
                        <?php if (isset($orgChart['description'])): ?>
                            <p class="org-chart-description"><?= htmlspecialchars($orgChart['description']) ?></p>
                        <?php endif; ?>
                        <?php if (isset($orgChart['updated_at'])): ?>
                            <p class="org-chart-updated">
                                <i class="fas fa-clock"></i>
                                Last updated: <?= date('F j, Y', strtotime($orgChart['updated_at'])) ?>
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="org-chart-image">
                            <img src="<?= img_url('org-chart.png') ?>" 
                                 alt="Mount Carmel School Organizational Structure"
                                 onerror="this.src='https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&q=80'">
                        </div>
                        <p class="org-chart-description">Organizational chart showing the hierarchical structure of Mount Carmel School administration.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Contact CTA -->
            <section id="contact" class="contact-cta">
                <div class="cta-content">
                    <h2>Need to Contact Administration?</h2>
                    <p>Get in touch with our administrative team for inquiries, appointments, or more information.</p>
                    <div class="cta-buttons">
                        <a href="<?= url('contact') ?>" class="btn-primary">
                            <i class="fas fa-envelope"></i> Send Message
                        </a>
                        <a href="tel:+250789121680" class="btn-secondary">
                            <i class="fas fa-phone"></i> Call Now
                        </a>
                    </div>
                </div>
            </section>

        </main>

    </div>

    <!-- Leader Modal -->
    <div class="leader-modal" id="leaderModal">
        <div class="modal-backdrop" onclick="closeLeaderModal()"></div>
        <article class="modal-content">
            <button class="modal-close" aria-label="Close" onclick="closeLeaderModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-loading" id="modalLoading">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content loaded dynamically -->
            </div>
        </article>
    </div>

    <!-- Footer -->
    <?php include_once get_layout('footer'); ?>

    <!-- jQuery -->
    <?php include_once get_layout('scripts'); ?>

    <style>
    /* ============================================
       GLOBAL STYLES
       ============================================ */
    :root {
        --primary-color: #0d9488;
        --secondary-color: #0c4a6e;
        --accent-color: #d97706;
        --light-bg: #f8fafc;
        --dark-text: #1f2937;
        --light-text: #6b7280;
        --border-color: #e5e7eb;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.6;
        color: var(--dark-text);
        background-color: #fff;
    }

    /* ============================================
       PAGE HEADER
       ============================================ */
    .admin-page-header {
        background: linear-gradient(135deg, rgba(0, 73, 121, 0.9), rgba(26, 58, 82, 0.9)),
                    url('<?= img_url("administration.jpg") ?>');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 80px 0 60px;
        text-align: center;
        margin-bottom: 0;
    }

    .admin-page-header h1 {
        font-size: 2.8rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .admin-page-header p {
        font-size: 1.2rem;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto 30px;
    }

    .admin-breadcrumb {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 0.9rem;
    }

    .admin-breadcrumb a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .admin-breadcrumb a:hover {
        color: white;
    }

    .admin-breadcrumb span:last-child {
        color: white;
        font-weight: 500;
    }

    .admin-breadcrumb i {
        font-size: 0.8rem;
        opacity: 0.7;
    }

    /* ============================================
       MAIN CONTAINER LAYOUT
       ============================================ */
    .admin-container {
        display: flex;
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
        gap: 40px;
    }

    @media (max-width: 1024px) {
        .admin-container {
            flex-direction: column;
            padding: 30px 15px;
        }
    }

    /* ============================================
       SIDEBAR STYLES
       ============================================ */
    .admin-sidebar {
        flex: 0 0 320px;
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: var(--shadow);
        height: fit-content;
        position: sticky;
        top: 20px;
    }

    @media (max-width: 1024px) {
        .admin-sidebar {
            flex: none;
            width: 100%;
            position: static;
            margin-bottom: 30px;
        }
    }

    .sidebar-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .sidebar-logo {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: var(--light-bg);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid var(--primary-color);
    }

    .sidebar-logo img {
        width: 50px;
        height: 50px;
        object-fit: contain;
    }

    .sidebar-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 0 0 5px 0;
    }

    .sidebar-subtitle {
        font-size: 0.85rem;
        color: var(--light-text);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }

    .sidebar-widget {
        margin-bottom: 30px;
        padding-bottom: 25px;
        border-bottom: 1px solid var(--border-color);
    }

    .sidebar-widget:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .widget-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--dark-text);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
    }

    /* Quick Stats */
    .admin-stats {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: var(--light-bg);
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .stat-item:hover {
        background: #e8f4ff;
        transform: translateX(3px);
    }

    .stat-item i {
        font-size: 1.3rem;
        color: var(--primary-color);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .stat-number {
        display: block;
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--dark-text);
        line-height: 1;
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--light-text);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 3px;
    }

    /* Contact Info */
    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .contact-item i {
        color: var(--primary-color);
        font-size: 1rem;
        width: 20px;
        flex-shrink: 0;
    }

    .contact-item span {
        font-size: 0.9rem;
        color: var(--light-text);
        flex: 1;
    }

    /* Quick Links */
    .quick-links {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .quick-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        text-decoration: none;
        color: var(--dark-text);
        border-radius: 8px;
        transition: all 0.3s ease;
        background: var(--light-bg);
        border: 1px solid transparent;
    }

    .quick-link:hover,
    .quick-link.active {
        background: var(--primary-color);
        color: white;
        transform: translateX(3px);
        border-color: var(--primary-color);
    }

    .quick-link i {
        color: var(--primary-color);
        font-size: 1rem;
        width: 20px;
        transition: color 0.3s ease;
        flex-shrink: 0;
    }

    .quick-link:hover i,
    .quick-link.active i {
        color: white;
    }

    .quick-link span {
        font-size: 0.9rem;
        font-weight: 500;
        flex: 1;
    }

    /* About Widget */
    .sidebar-about p {
        font-size: 0.9rem;
        color: var(--light-text);
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .subscribe-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--primary-color);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: gap 0.3s ease;
    }

    .subscribe-link:hover {
        gap: 12px;
    }

    .subscribe-link i {
        font-size: 0.8rem;
    }

    /* Social Links */
    .sidebar-social {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }

    .sidebar-social a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: var(--light-bg);
        color: var(--light-text);
        border-radius: 50%;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .sidebar-social a:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-2px);
    }

    /* ============================================
       MAIN CONTENT AREA
       ============================================ */
    .admin-main {
        flex: 1;
        min-width: 0;
    }

    .section-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .section-header h2 {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 15px;
    }

    .section-header p {
        font-size: 1.1rem;
        color: var(--light-text);
        line-height: 1.6;
        max-width: 600px;
        margin: 0 auto;
    }

    /* ============================================
       LEADERSHIP GRID - FIXED TO SHOW 3 PER ROW
       ============================================ */
    .leadership-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-bottom: 50px;
    }

    @media (max-width: 1200px) {
        .leadership-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .leadership-grid {
            grid-template-columns: 1fr;
        }
    }

    .leader-card {
        background: white;
        /* border-radius: 12px; */
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
    }

    .leader-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .leader-thumbnail {
        position: relative;
        width: 100%;
        height: 350px;
        overflow: hidden;
        border: solid 15px white;
    }

    .leader-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .leader-card:hover .leader-thumbnail img {
        transform: scale(1.05);
    }

    .leader-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--accent-color);
        color: white;
        padding: 5px 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 15px;
        z-index: 1;
    }

    .leader-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex: 1;
        text-align: center;
    }

    .leader-meta {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-bottom: 10px;
        font-size: 0.85rem;
    }

    .leader-position {
        color: var(--primary-color);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
    }

    .leader-date {
        color: var(--light-text);
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.75rem;
    }

    .leader-date i {
        font-size: 0.7rem;
    }

    .leader-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark-text);
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .leader-qualifications {
        font-size: 0.8rem;
        color: var(--light-text);
        margin-bottom: 12px;
        display: flex;
        align-items: flex-start;
        gap: 5px;
        line-height: 1.4;
    }

    .leader-qualifications i {
        color: var(--primary-color);
        font-size: 0.85rem;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .leader-excerpt {
        font-size: 0.85rem;
        line-height: 1.5;
        color: var(--light-text);
        margin-bottom: 15px;
        flex: 1;
    }

    .leader-contact {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--border-color);
    }

    .contact-link {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--light-text);
        text-decoration: none;
        font-size: 0.8rem;
        transition: color 0.3s ease;
        overflow: hidden;
    }

    .contact-link:hover {
        color: var(--primary-color);
    }

    .contact-link i {
        color: var(--primary-color);
        font-size: 0.85rem;
        width: 16px;
        flex-shrink: 0;
    }

    .contact-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
        min-width: 0;
    }

    .btn-view-profile {
        width: 100%;
        padding: 10px 20px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-view-profile:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
    }

    /* ============================================
       ORGANIZATION STRUCTURE SECTION
       ============================================ */
    .org-structure-section {
        background: var(--light-bg);
        border-radius: 12px;
        padding: 50px 30px;
        margin-bottom: 50px;
    }

    .org-chart-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .org-chart-image {
        margin-bottom: 25px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .org-chart-image img {
        width: 100%;
        height: auto;
        display: block;
    }

    .org-chart-description {
        font-size: 1rem;
        color: var(--light-text);
        line-height: 1.6;
        margin-bottom: 15px;
        text-align: center;
    }

    .org-chart-updated {
        font-size: 0.85rem;
        color: var(--light-text);
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    /* ============================================
       CONTACT CTA
       ============================================ */
    .contact-cta {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border-radius: 12px;
        padding: 50px 30px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .contact-cta::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        opacity: 0.5;
    }

    .cta-content {
        position: relative;
        z-index: 1;
    }

    .contact-cta h2 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .contact-cta p {
        font-size: 1.05rem;
        opacity: 0.9;
        margin-bottom: 25px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .cta-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-primary,
    .btn-secondary {
        padding: 12px 25px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .btn-primary {
        background: white;
        color: var(--primary-color);
        border: 2px solid white;
    }

    .btn-primary:hover {
        background: #f8f9fa;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .btn-secondary {
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-3px);
        border-color: white;
    }

    /* ============================================
       NO CONTENT STATES
       ============================================ */
    .no-content {
        grid-column: 1 / -1;
        text-align: center;
        padding: 50px 20px;
        color: var(--light-text);
    }

    .no-content i {
        font-size: 3rem;
        margin-bottom: 15px;
        display: block;
        opacity: 0.3;
    }

    .no-content p {
        font-size: 1.1rem;
    }

    /* ============================================
       LEADER MODAL
       ============================================ */
    .leader-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .leader-modal.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(5px);
    }

    .modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.9);
        width: 40%;
        min-height: 87vh;
        max-width: 500px;
        max-height: 90vh;
        background: white;
        border-radius: 2px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transition: transform 0.3s ease;
    }
    @media (max-width: 768px) {
        .admin-page-header h1 {
            font-size: 1.8rem;
        }
        .modal-content {
            width: 90%;
        }
    }

    .leader-modal.active .modal-content {
        transform: translate(-50%, -50%) scale(1);
    }

    .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        background: white;
        border: none;
        border-radius: 50%;
        color: var(--dark-text);
        font-size: 1.2rem;
        cursor: pointer;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .modal-close:hover {
        background: var(--primary-color);
        color: white;
        transform: rotate(90deg);
    }

    .modal-loading {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }

    .modal-loading i {
        font-size: 2rem;
        color: var(--primary-color);
    }

    .modal-body {
        padding: 40px;
        overflow-y: auto;
        max-height: calc(90vh - 80px);
    }

    .modal-header-image {
        width: 100%;
        height: 450px;
        margin-bottom: 30px;
        border-radius: 8px;
        overflow: hidden;
    }

    .modal-header-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .modal-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .modal-category {
        background: var(--primary-color);
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .modal-date {
        color: var(--light-text);
        font-size: 0.9rem;
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark-text);
        margin-bottom: 20px;
        line-height: 1.3;
    }

    .modal-author {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .modal-author img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }

    .modal-author div {
        flex: 1;
    }

    .modal-author strong {
        display: block;
        font-size: 1.1rem;
        color: var(--dark-text);
        margin-bottom: 5px;
    }

    .modal-author span {
        font-size: 0.9rem;
        color: var(--light-text);
        text-align: justify;
    }

    .modal-content-text {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--dark-text);
        margin-bottom: 30px;
    }
    .modal-article {
        padding: 20px 40px;
    }

    .modal-content-text p {
        margin-bottom: 15px!important;
        text-align: justify!important;
        word-spacing: 2px;
    }

    .modal-content-text h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--dark-text);
        margin-top: 25px;
        margin-bottom: 15px;
    }

    .contact-details p {
        margin-bottom: 10px;
    }

    .contact-details strong {
        color: var(--dark-text);
        margin-right: 8px;
    }

    .contact-details a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .contact-details a:hover {
        text-decoration: underline;
    }

    /* ============================================
       RESPONSIVE DESIGN
       ============================================ */
    @media (max-width: 1200px) {
        .admin-container {
            padding: 30px 15px;
        }
        
        .admin-sidebar {
            flex: 0 0 280px;
        }
    }

    @media (max-width: 992px) {
        .admin-page-header {
            padding: 60px 0 40px;
        }
        
        .admin-page-header h1 {
            font-size: 2.2rem;
        }
        
        .admin-page-header p {
            font-size: 1.1rem;
        }
        
        .section-header h2 {
            font-size: 1.8rem;
        }
        
        .contact-cta {
            padding: 40px 20px;
        }
    }

    @media (max-width: 768px) {
        .admin-page-header h1 {
            font-size: 1.8rem;
        }
        
        .admin-page-header p {
            font-size: 1rem;
        }
        
        .admin-breadcrumb {
            font-size: 0.8rem;
        }
        
        .admin-sidebar {
            padding: 25px 20px;
        }
        
        .section-header h2 {
            font-size: 1.6rem;
        }
        
        .section-header p {
            font-size: 1rem;
        }
        
        .leader-body {
            padding: 20px;
        }
        
        .leader-title {
            font-size: 1.1rem;
        }
        
        .org-structure-section {
            padding: 40px 20px;
        }
        
        .contact-cta h2 {
            font-size: 1.6rem;
        }
        
        .contact-cta p {
            font-size: 1rem;
        }
        
        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .btn-primary,
        .btn-secondary {
            width: 100%;
            max-width: 280px;
            justify-content: center;
        }
        
        .modal-body {
            padding: 30px 20px;
        }
        
        .modal-title {
            font-size: 1.6rem;
        }
    }

    @media (max-width: 576px) {
        .admin-page-header {
            padding: 50px 0 30px;
        }
        
        .admin-page-header h1 {
            font-size: 1.6rem;
        }
        
        .sidebar-logo {
            width: 70px;
            height: 70px;
        }
        
        .sidebar-logo img {
            width: 40px;
            height: 40px;
        }
        
        .stat-item {
            padding: 12px;
            gap: 12px;
        }
        
        .stat-item i {
            width: 35px;
            height: 35px;
            font-size: 1.1rem;
        }
        
        .stat-number {
            font-size: 1.2rem;
        }
        
        .leader-thumbnail {
            height: 390px;
        }
        
        .contact-cta {
            padding: 30px 20px;
        }
        
        .contact-cta h2 {
            font-size: 1.4rem;
        }
    }

    /* ============================================
       ANIMATIONS
       ============================================ */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .leader-card {
        animation: fadeInUp 0.5s ease-out;
        animation-fill-mode: both;
    }

    /* Stagger animations */
    .leader-card:nth-child(1) { animation-delay: 0.1s; }
    .leader-card:nth-child(2) { animation-delay: 0.2s; }
    .leader-card:nth-child(3) { animation-delay: 0.3s; }
    .leader-card:nth-child(4) { animation-delay: 0.4s; }
    .leader-card:nth-child(5) { animation-delay: 0.5s; }
    .leader-card:nth-child(6) { animation-delay: 0.6s; }
    </style>

    <script>
    // Configuration
    const BASE_URL = '<?= url() ?>';
    const API_URL = BASE_URL + '/api/administration';
    
    // Initialize
    $(document).ready(function() {
        initializePage();
        setupEventListeners();
        animateCounters();
    });

    function initializePage() {
        updateActiveQuickLinks();
        setupSmoothScroll();
    }

    function setupEventListeners() {
        // Quick links navigation
        $('.quick-link').click(function(e) {
            e.preventDefault();
            const target = $(this).attr('href');
            $('.quick-link').removeClass('active');
            $(this).addClass('active');
            
            $('html, body').animate({
                scrollTop: $(target).offset().top - 80
            }, 600);
        });
        
        // Update active link on scroll
        $(window).scroll(throttle(updateActiveQuickLinks, 100));
    }

    function animateCounters() {
        const leadershipElement = document.getElementById('totalLeadership');
        if (leadershipElement) {
            const total = parseInt(leadershipElement.textContent);
            animateNumber(leadershipElement, total, 1200);
        }
    }

    function animateNumber(element, targetNumber, duration = 1000) {
        if (!element || targetNumber === 0) return;
        
        let start = 0;
        const increment = targetNumber / (duration / 16);
        let lastUpdate = Date.now();
        
        function update() {
            const now = Date.now();
            const delta = now - lastUpdate;
            
            if (delta < 16) {
                requestAnimationFrame(update);
                return;
            }
            
            start += increment;
            if (start >= targetNumber) {
                start = targetNumber;
                element.textContent = targetNumber.toLocaleString();
                return;
            }
            
            element.textContent = Math.floor(start).toLocaleString();
            lastUpdate = now;
            requestAnimationFrame(update);
        }
        
        requestAnimationFrame(update);
    }

    function updateActiveQuickLinks() {
        const sections = ['#leadership', '#structure', '#contact'];
        const scrollPosition = window.scrollY + 100;
        
        let currentSection = '';
        
        sections.forEach(section => {
            const element = $(section);
            if (element.length) {
                const offset = element.offset().top;
                const height = element.outerHeight();
                
                if (scrollPosition >= offset && scrollPosition < offset + height) {
                    currentSection = section;
                }
            }
        });
        
        if (currentSection) {
            $('.quick-link').removeClass('active');
            $(`.quick-link[href="${currentSection}"]`).addClass('active');
        }
    }

    function setupSmoothScroll() {
        $('a[href^="#"]').on('click', function(e) {
            if ($(this).attr('href') === '#') return;
            
            e.preventDefault();
            const target = $(this).attr('href');
            
            if ($(target).length) {
                $('html, body').animate({
                    scrollTop: $(target).offset().top - 80
                }, 600);
            }
        });
    }

    function openLeaderModal(leaderId) {
        const modal = $('#leaderModal');
        const modalBody = $('#modalBody');
        const modalLoading = $('#modalLoading');
        
        modal.addClass('active');
        $('body').css('overflow', 'hidden');
        modalLoading.show();
        modalBody.html('');
        
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { action: 'get_leadership_by_id', id: leaderId },
            dataType: 'json',
            success: function(response) {
                console.log('API Response:', response);
                if (response.success && response.data) {
                    displayLeaderModal(response.data);
                } else {
                    modalBody.html('<div style="padding: 40px; text-align: center;"><p class="error-message">Failed to load leader profile.</p></div>');
                }
                modalLoading.hide();
            },
            error: function(xhr, status, error) {
                console.error('API Error:', status, error);
                modalBody.html('<div style="padding: 40px; text-align: center;"><p class="error-message">Failed to load leader profile. Please try again.</p></div>');
                modalLoading.hide();
            }
        });
    }

    function displayLeaderModal(leader) {
        const joinDate = leader.join_date ? new Date(leader.join_date).toLocaleDateString('en-US', { 
            month: 'long', 
            day: 'numeric', 
            year: 'numeric' 
        }) : 'Not specified';
        
        const imageUrl = leader.image_url 
            ? '<?= img_url("") ?>' + leader.image_url 
            : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(leader.full_name) + '&background=0d47a1&color=fff&size=800';
        
        const html = `
            <div class="modal-header-image">
                <img src="${imageUrl}" alt="${leader.full_name}"
                     onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(leader.full_name)}&background=0d47a1&color=fff&size=800'">
            </div>
            <div class="modal-article">
                <div class="modal-meta">
                    <span class="modal-category">${leader.role_badge || 'Leadership'}</span>
                    <span class="modal-date">Member since ${joinDate}</span>
                </div>
                <h1 class="modal-title">${leader.full_name}</h1>
                <div class="modal-author">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(leader.full_name)}&background=0d47a1&color=fff" 
                         alt="${leader.full_name}">
                    <div>
                        <strong>${leader.position}</strong>
                        <span>${leader.qualifications || 'Qualified Professional'}</span>
                    </div>
                </div>
                <div class="modal-content-text">
                    <p>${leader.short_bio || 'No biography available.'}</p>
                    
                    ${leader.qualifications ? `<h3>Qualifications</h3><p>${leader.qualifications}</p>` : ''}
                    
                    ${leader.email || leader.phone ? `
                    <h3>Contact Information</h3>
                    <div class="contact-details">
                        ${leader.email ? `<p><strong>Email:</strong> <a href="mailto:${leader.email}">${leader.email}</a></p>` : ''}
                        ${leader.phone ? `<p><strong>Phone:</strong> <a href="tel:${leader.phone.replace(/[^\d+]/g, '')}">${leader.phone}</a></p>` : ''}
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        $('#modalBody').html(html);
    }

    function closeLeaderModal() {
        $('#leaderModal').removeClass('active');
        $('body').css('overflow', '');
    }

    // Utility function for throttling
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        }
    }

    // Handle window resize
    $(window).resize(throttle(handleResize, 250));

    function handleResize() {
        updateActiveQuickLinks();
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && $('#leaderModal').hasClass('active')) {
            closeLeaderModal();
        }
    });
    </script>

</body>
</html>