<!doctype html>
<html class="no-js" lang="zxx">

<?php
/**
 * Leadership Page - IMPROVED VERSION
 * File: modules/General/views/leadership.php
 * Non-sticky year selector with full committee visibility
 */

require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../modules/Leadership/models/LeadershipModel.php';

// Get database instance
$db = Database::getInstance();
$leadershipModel = new LeadershipModel($db);

// Fetch all years for the selector
$allYears = $leadershipModel->getAllYears();

// Get current year or selected year
$selectedYearId = isset($_GET['year']) ? (int)$_GET['year'] : null;

if (!$selectedYearId) {
    $currentYear = $leadershipModel->getCurrentYear();
    $selectedYearId = $currentYear ? $currentYear['id'] : ($allYears[0]['id'] ?? null);
}

// Fetch complete data for selected year
$yearData = $selectedYearId ? $leadershipModel->getCompleteYearData($selectedYearId) : null;
?>
<?php include get_layout('header-2'); ?>

<body data-res-from="1025">
    
    <style>
        /* ========== IMPROVED LEADERSHIP STYLES ========== */
        
        /* Year Selection Section - NON-STICKY */
        .year-selection-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            padding: 60px 0;
            border-bottom: 1px solid #e0e0e0;
            position: relative; /* Changed from sticky */
            z-index: 10;
        }
        
        .year-selector {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .year-selector-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .year-selector-title {
            font-family: 'Crimson Text', serif;
            font-size: 28px;
            font-weight: 700;
            color: #1B2845;
            margin: 0 0 10px 0;
        }
        
        .year-selector-subtitle {
            font-size: 15px;
            color: #666;
            margin: 0;
        }
        
        /* Year Grid - Show ALL years */
        .years-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 30px;
        }
        
        .year-card {
            background: white;
            padding: 20px;
            border: 2px solid #e0e0e0;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .year-card:hover {
            border-color: #D4AF37;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .year-card.active {
            background: linear-gradient(135deg, #800020, #A0002C);
            border-color: #D4AF37;
            box-shadow: 0 8px 24px rgba(128,0,32,0.2);
        }
        
        .year-card.active .year-label {
            color: white;
        }
        
        .year-card.active .year-period {
            color: rgba(255,255,255,0.9);
        }
        
        .year-label {
            font-family: 'Crimson Text', serif;
            font-size: 18px;
            font-weight: 700;
            color: #1B2845;
            margin: 0 0 6px 0;
            transition: color 0.3s ease;
        }
        
        .year-period {
            font-size: 13px;
            color: #666;
            margin: 0;
            transition: color 0.3s ease;
        }
        
        .current-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #D4AF37, #F4C430);
            color: #1B2845;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 10px;
            letter-spacing: 0.5px;
        }
        
        /* Committee Display Section */
        .committee-section {
            background: white;
            padding: 80px 0;
            min-height: auto; /* Changed from 60vh */
        }
        
        .committee-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Year Header */
        .year-header {
            text-align: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .year-header h2 {
            font-family: 'Crimson Text', serif;
            font-size: 32px;
            font-weight: 700;
            color: #800020;
            margin: 0 0 12px 0;
        }
        
        .year-header p {
            font-size: 16px;
            color: #666;
            margin: 0;
        }
        
        /* Session Toggle */
        .session-toggle {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-bottom: 50px;
        }
        
        .session-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            background: white;
            border: 2px solid #e0e0e0;
            font-family: 'Crimson Text', serif;
            font-size: 16px;
            font-weight: 600;
            color: #404040;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .session-btn:hover {
            border-color: #D4AF37;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .session-btn.active {
            background: linear-gradient(135deg, #800020, #A0002C);
            border-color: #D4AF37;
            color: white;
        }
        
        .session-btn i {
            font-size: 18px;
        }
        
        /* Leaders Grid */
        .leaders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }
        
        .leader-card {
            background: #fafafa;
            padding: 0;
            border: 2px solid #e8e8e8;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .leader-card:hover {
            border-color: #D4AF37;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        
        .leader-image {
            width: 100%;
            height: 280px;
            background: linear-gradient(135deg, #800020, #A0002C);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .leader-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .leader-image .placeholder-icon {
            font-size: 80px;
            color: rgba(255,255,255,0.3);
        }
        
        .leader-info {
            padding: 24px 20px;
        }
        
        .leader-position {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #D4AF37;
            margin: 0 0 8px 0;
        }
        
        .leader-name {
            font-family: 'Crimson Text', serif;
            font-size: 20px;
            font-weight: 700;
            color: #1B2845;
            margin: 0 0 6px 0;
        }
        
        .leader-abbr {
            font-size: 13px;
            color: #666;
            margin: 0;
        }
        
        /* Achievements Section */
        .achievements-section {
            margin-top: 60px;
            padding-top: 60px;
            border-top: 2px solid #f0f0f0;
        }
        
        .achievements-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .achievements-header i {
            font-size: 40px;
            color: #D4AF37;
            margin-bottom: 16px;
        }
        
        .achievements-header h3 {
            font-family: 'Crimson Text', serif;
            font-size: 28px;
            font-weight: 700;
            color: #1B2845;
            margin: 0 0 10px 0;
        }
        
        .achievements-header p {
            font-size: 15px;
            color: #666;
            margin: 0;
        }
        
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .achievement-card {
            background: white;
            padding: 28px 24px;
            border: 2px solid #e8e8e8;
            transition: all 0.3s ease;
        }
        
        .achievement-card:hover {
            border-color: #D4AF37;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        
        .achievement-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #800020, #A0002C);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }
        
        .achievement-icon i {
            font-size: 24px;
            color: #D4AF37;
        }
        
        .achievement-card h4 {
            font-family: 'Crimson Text', serif;
            font-size: 18px;
            font-weight: 700;
            color: #1B2845;
            margin: 0 0 10px 0;
        }
        
        .achievement-card p {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
            margin: 0 0 12px 0;
        }
        
        .achievement-date {
            font-size: 13px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .achievement-date i {
            color: #D4AF37;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #D4AF37;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-family: 'Crimson Text', serif;
            font-size: 24px;
            color: #1B2845;
            margin: 0 0 12px 0;
        }
        
        .empty-state p {
            font-size: 15px;
            color: #666;
            margin: 0;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .years-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 12px;
            }
            
            .year-card {
                padding: 16px;
            }
            
            .year-label {
                font-size: 16px;
            }
            
            .leaders-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .years-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            }
            
            .session-toggle {
                flex-direction: column;
                align-items: stretch;
            }
            
            .session-btn {
                justify-content: center;
            }
            
            .leaders-grid {
                grid-template-columns: 1fr;
            }
            
            .achievements-grid {
                grid-template-columns: 1fr;
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
            
            <!-- Leadership Hero -->
            <section class="leadership-hero">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <div class="container">
                        <!-- <div class="hero-icon">
                            <i class="fa fa-crown"></i>
                        </div> -->
                        <h1 class="hero-title">Our Leadership</h1>
                        <p class="hero-subtitle">Servant Leaders Raising Christ-Centered Students</p>
                        <div class="hero-verse">
                            <i class="fas fa-book-open"></i>
                            <p>"The greatest among you shall be your servant." <span>— Matthew 23:11</span></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Leadership Introduction -->
            <section class="leadership-intro pad-tb-100">
                <div class="container">
                    <div class="intro-content">
                        <span class="section-label">Servant Leadership</span>
                        <h2>Leading by Example, Serving with Love</h2>
                        <p class="lead-text">CEP UoK's leadership structure is built on biblical principles of servant leadership, accountability, and collective vision. Our leaders are students who have demonstrated spiritual maturity, commitment to Christ, and a heart for serving their fellow students.</p>
                        <div class="leadership-principles">
                            <div class="principle-item">
                                <i class="fas fa-praying-hands"></i>
                                <h4>Prayer-Led</h4>
                                <p>Every decision grounded in prayer and seeking God's will</p>
                            </div>
                            <div class="principle-item">
                                <i class="fas fa-balance-scale"></i>
                                <h4>Accountable</h4>
                                <p>Transparent and responsible to the local church and members</p>
                            </div>
                            <div class="principle-item">
                                <i class="fas fa-heart"></i>
                                <h4>Servant-Hearted</h4>
                                <p>Leading with humility, compassion, and genuine care</p>
                            </div>
                            <div class="principle-item">
                                <i class="fas fa-users"></i>
                                <h4>Collaborative</h4>
                                <p>Working together across sessions for unified impact</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Year Selection Section - NON-STICKY -->
            <section class="year-selection-section">
                <div class="container">
                    <div class="year-selector">
                        <div class="year-selector-header">
                            <h3 class="year-selector-title">Select Academic Year</h3>
                            <p class="year-selector-subtitle">View leadership committees from different academic years</p>
                        </div>
                        
                        <!-- Display ALL years in grid -->
                        <div class="years-grid">
                            <?php foreach ($allYears as $year): ?>
                            <div class="year-card <?= $year['id'] == $selectedYearId ? 'active' : '' ?>" 
                                 onclick="window.location.href='<?= url('leadership') ?>?year=<?= $year['id'] ?>'">
                                <?php if ($year['is_current']): ?>
                                <span class="current-badge">CURRENT</span>
                                <?php endif; ?>
                                <h4 class="year-label"><?= htmlspecialchars($year['year_label']) ?></h4>
                                <p class="year-period"><?= $year['year_start'] ?>-<?= $year['year_end'] ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Committee Display Section -->
            <section class="committee-section">
                <div class="container">
                    <?php if ($yearData): ?>
                    <!-- Committee Content -->
                    <div class="committee-content">
                        <!-- Year Header -->
                        <div class="year-header">
                            <h2 id="yearTitle"><?= htmlspecialchars($yearData['year']['year_label']) ?> Academic Year</h2>
                            <p id="yearDescription">
                                <?= htmlspecialchars($yearData['year']['description'] ?: 'Leadership serving CEP UoK') ?>
                            </p>
                        </div>

                        <?php if ($yearData['year']['has_dual_sessions']): ?>
                        <!-- Session Toggle (for years with 2 sessions) -->
                        <div class="session-toggle">
                            <button class="session-btn active" data-session="day">
                                <i class="fas fa-sun"></i>
                                Day Session
                            </button>
                            <button class="session-btn" data-session="weekend">
                                <i class="fas fa-calendar-week"></i>
                                Weekend Session
                            </button>
                        </div>

                        <!-- Day Session Leaders -->
                        <div class="session-content" data-session-content="day">
                            <h3 style="text-align: center; margin-bottom: 30px; font-family: 'Crimson Text', serif; color: #800020;">
                                <i class="fas fa-sun"></i> Day Session Committee
                            </h3>
                            <div class="leaders-grid">
                                <?php foreach ($yearData['day_session_leaders'] as $leader): ?>
                                <div class="leader-card">
                                    <div class="leader-image">
                                        <?php if ($leader['image_url']): ?>
                                        <img src="<?= img_url($leader['image_url']) ?>" alt="<?= htmlspecialchars($leader['full_name']) ?>">
                                        <?php else: ?>
                                        <i class="fas fa-user-circle placeholder-icon"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="leader-info">
                                        <p class="leader-position"><?= htmlspecialchars($leader['position_name']) ?></p>
                                        <h4 class="leader-name"><?= htmlspecialchars($leader['full_name']) ?></h4>
                                        <?php if ($leader['position_abbr']): ?>
                                        <p class="leader-abbr"><?= htmlspecialchars($leader['position_abbr']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Weekend Session Leaders -->
                        <div class="session-content" data-session-content="weekend" style="display: none;">
                            <h3 style="text-align: center; margin-bottom: 30px; font-family: 'Crimson Text', serif; color: #800020;">
                                <i class="fas fa-calendar-week"></i> Weekend Session Committee
                            </h3>
                            <div class="leaders-grid">
                                <?php foreach ($yearData['weekend_session_leaders'] as $leader): ?>
                                <div class="leader-card">
                                    <div class="leader-image">
                                        <?php if ($leader['image_url']): ?>
                                        <img src="<?= img_url($leader['image_url']) ?>" alt="<?= htmlspecialchars($leader['full_name']) ?>">
                                        <?php else: ?>
                                        <i class="fas fa-user-circle placeholder-icon"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="leader-info">
                                        <p class="leader-position"><?= htmlspecialchars($leader['position_name']) ?></p>
                                        <h4 class="leader-name"><?= htmlspecialchars($leader['full_name']) ?></h4>
                                        <?php if ($leader['position_abbr']): ?>
                                        <p class="leader-abbr"><?= htmlspecialchars($leader['position_abbr']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php else: ?>
                        <!-- Single Session Leaders -->
                        <div class="leaders-grid">
                            <?php foreach ($yearData['leaders'] as $leader): ?>
                            <div class="leader-card">
                                <div class="leader-image">
                                    <?php if ($leader['image_url']): ?>
                                    <img src="<?= img_url($leader['image_url']) ?>" alt="<?= htmlspecialchars($leader['full_name']) ?>">
                                    <?php else: ?>
                                    <i class="fas fa-user-circle placeholder-icon"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="leader-info">
                                    <p class="leader-position"><?= htmlspecialchars($leader['position_name']) ?></p>
                                    <h4 class="leader-name"><?= htmlspecialchars($leader['full_name']) ?></h4>
                                    <?php if ($leader['position_abbr']): ?>
                                    <p class="leader-abbr"><?= htmlspecialchars($leader['position_abbr']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Achievements Section -->
                        <?php if (!empty($yearData['achievements'])): ?>
                        <div class="achievements-section">
                            <div class="achievements-header">
                                <i class="fas fa-trophy"></i>
                                <h3>Key Achievements</h3>
                                <p>Major milestones and accomplishments during this term</p>
                            </div>
                            <div class="achievements-grid">
                                <?php foreach ($yearData['achievements'] as $achievement): ?>
                                <div class="achievement-card">
                                    <div class="achievement-icon">
                                        <i class="<?= htmlspecialchars($achievement['icon_class']) ?>"></i>
                                    </div>
                                    <h4><?= htmlspecialchars($achievement['achievement_title']) ?></h4>
                                    <p><?= htmlspecialchars($achievement['achievement_description']) ?></p>
                                    <?php if ($achievement['achievement_date']): ?>
                                    <span class="achievement-date">
                                        <i class="fas fa-calendar"></i>
                                        <?= date('F Y', strtotime($achievement['achievement_date'])) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php else: ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <h3>No Leadership Data Available</h3>
                        <p>Leadership information for this year is not yet available.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Leadership Legacy Section -->
            <section class="leadership-legacy bg-light pad-tb-100">
                <div class="container">
                    <div class="legacy-content">
                        <span class="section-label">Our Legacy</span>
                        <h2>A Heritage of Faithful Service</h2>
                        <p class="lead-text">Over the years, CEP UoK has been blessed with dedicated leaders who have sacrificed their time, energy, and resources to build God's kingdom on campus.</p>
                        <div class="legacy-stats">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <h3>100+</h3>
                                <p>Student Leaders</p>
                                <span>Served since 2016</span>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h3><?= count($allYears) ?></h3>
                                <p>Academic Years</p>
                                <span>Of committed service</span>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-hands-helping"></i>
                                </div>
                                <h3>2</h3>
                                <p>Active Sessions</p>
                                <span>Since 2022</span>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-cross"></i>
                                </div>
                                <h3>∞</h3>
                                <p>God's Faithfulness</p>
                                <span>Forever and ever</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Call to Leadership -->
            <section class="leadership-cta pad-tb-100">
                <div class="container">
                    <div class="cta-content text-center">
                        <div class="cta-icon">
                            <i class="fas fa-hands"></i>
                        </div>
                        <h2>Called to Lead?</h2>
                        <p>CEP UoK is always looking for committed students who desire to serve God and their fellow students. Leadership positions are open to ADEPR members who demonstrate spiritual maturity, servant-heartedness, and dedication to the fellowship's mission.</p>
                        <div class="cta-buttons">
                            <a href="<?= url('contact') ?>" class="btn btn-primary">
                                <i class="fas fa-envelope"></i> Express Interest
                            </a>
                            <a href="<?= url('about-cep') ?>" class="btn btn-secondary">
                                <i class="fas fa-info-circle"></i> Learn More
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
    
    <!-- Leadership Page Custom JS -->
    <script src="<?= js_url('leadership.js') ?>"></script>
    
    <script>
        // Session toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Session toggle (for dual session years)
            const sessionBtns = document.querySelectorAll('.session-btn');
            sessionBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const session = this.getAttribute('data-session');
                    
                    // Update button states
                    sessionBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Toggle session content
                    document.querySelectorAll('[data-session-content]').forEach(content => {
                        if (content.getAttribute('data-session-content') === session) {
                            content.style.display = 'block';
                            content.classList.add('active');
                        } else {
                            content.style.display = 'none';
                            content.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>