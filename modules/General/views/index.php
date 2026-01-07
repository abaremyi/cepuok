<!DOCTYPE html>
<html lang="en">

<?php 
// Include paths configuration
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";

// Include header
include_once get_layout('header');
?>

<body>
        
    <!-- Control Active Nav Link -->
     <?php 
        $home = 'active'; 
        $services = 'off'; 
        $work = 'off'; 
        $about = 'off'; 
        $news = 'off'; 
        $contacts = 'off'; 
     ?>
    <!-- Navbar -->
    <?php include_once get_layout('navbar'); ?>
    

    <!-- Hero Section - Carousel -->
    <section class="hero" id="home">
        <?php
        // Fetch hero sliders from database
        require_once $root_path . "/config/database.php";
        $pdo = Database::getConnection();
        
        $stmt = $pdo->query("SELECT * FROM hero_sliders WHERE status = 'active' ORDER BY display_order");
        $sliders = $stmt->fetchAll();
        
        $isFirst = true;
        foreach ($sliders as $index => $slider):
        ?>
        <div class="hero-slide hero-slide-<?= $index + 1 ?> <?= $isFirst ? 'active' : '' ?>" 
             style="background: linear-gradient(135deg, rgba(0, 121, 107, 0.85), rgba(26, 58, 82, 0.85)), url('<?= img_url($slider['image_url']) ?>') center/cover;">
            <div class="hero-content">
                <h1><?= htmlspecialchars($slider['title']) ?></h1>
                <p><?= htmlspecialchars($slider['description']) ?></p>
                <div class="hero-buttons">
                    <?php if (!empty($slider['button1_text'])): ?>
                    <a href="<?= htmlspecialchars($slider['button1_link']) ?>" class="btn btn-primary"><?= htmlspecialchars($slider['button1_text']) ?></a>
                    <?php endif; ?>
                    <?php if (!empty($slider['button2_text'])): ?>
                    <a href="<?= htmlspecialchars($slider['button2_link']) ?>" class="btn btn-secondary"><?= htmlspecialchars($slider['button2_text']) ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php 
        $isFirst = false;
        endforeach; 
        ?>
        
        <!-- Navigation Arrows -->
        <?php if (count($sliders) > 1): ?>
        <div class="carousel-arrow carousel-arrow-left" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </div>
        <div class="carousel-arrow carousel-arrow-right" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </div>
    
        <!-- Indicators -->
        <div class="carousel-indicators">
            <?php for ($i = 0; $i < count($sliders); $i++): ?>
            <div class="carousel-indicator <?= $i === 0 ? 'active' : '' ?>" onclick="goToSlide(<?= $i ?>)"></div>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </section>
    
    <!-- Welcome Section with Video -->
    <!-- Welcome Section with Video -->
<section class="welcome-section" id="welcome">
    <div class="container">
        <?php
        // Fetch welcome section content from database
        $stmt = $pdo->prepare("SELECT * FROM page_content WHERE page_name = 'home' AND section_name IN (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['welcome_section_title', 'welcome_section_video', 'welcome_intro_head', 'welcome_intro_paragraph', 'welcome_quote_title', 'welcome_quote_content']);
        $welcome_content = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to associative array
        $welcome = [];
        foreach ($welcome_content as $content) {
            $welcome[$content['section_name']] = $content;
        }
        ?>
        
        <div class="w3l-heading">
            <h2 class="w3ls_head"><?= $welcome['welcome_section_title']['content'] ?? 'Welcome to Mount Carmel School' ?></h2>
        </div>
        
        <div class="row">
            <!-- Video Section -->
            <div class="col-md-6 welcome-left">
                <div class="video-container">
                    <div class="video-wrapper">
                        <?php if (!empty($welcome['welcome_section_video']['content'])): ?>
                            <iframe 
                                src="<?= htmlspecialchars($welcome['welcome_section_video']['content']) ?>" 
                                title="Welcome to Mount Carmel School"
                                frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
                            </iframe>
                        <?php else: ?>
                            <!-- Fallback to default video -->
                            <iframe 
                                src="https://www.youtube.com/embed/NZI3j_XpgWM?si=dbEgYZNAuGMkrNBl" 
                                title="Welcome to Mount Carmel School"
                                frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
                            </iframe>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Welcome Content -->
            <div class="col-md-6 welcome-right">
                <div class="welcome-intro">
                    <h3><?= $welcome['welcome_intro_head']['content'] ?? 'Excellence in Education Since 2013' ?></h3>
                    <p><?= $welcome['welcome_intro_paragraph']['content'] ?? 'Mount Carmel School is a nurturing bilingual institution...' ?></p>
                    
                    <div class="welcome-quote">
                        <i class="fas fa-quote-left"></i>
                        <p><strong><?= $welcome['welcome_quote_title']['content'] ?? 'Vision:' ?></strong> <?= $welcome['welcome_quote_content']['content'] ?? 'To bless Rwanda with GOD fearing citizens...' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <?php
        // Fetch quick stats
        $stmt = $pdo->query("SELECT * FROM quick_stats WHERE status = 'active' ORDER BY display_order");
        $quick_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="quick-stats">
            <div class="row">
                <?php if (empty($quick_stats)): ?>
                    <!-- Default stats -->
                    <div class="col-md-3 col-sm-6 stat-box">
                        <div class="stat-circle">
                            <div class="stat-number" data-count="10">0</div>
                            <div class="stat-plus">+</div>
                        </div>
                        <div class="stat-label">Years of Excellence</div>
                    </div>
                    <!-- ... other default stats ... -->
                <?php else: ?>
                    <?php foreach ($quick_stats as $stat): ?>
                        <div class="col-md-3 col-sm-6 stat-box">
                            <div class="stat-circle">
                                <div class="stat-number" data-count="<?= $stat['stat_value'] ?>">0</div>
                                <?php if (strpos($stat['stat_value'], '%') !== false): ?>
                                    <div class="stat-percent">%</div>
                                <?php endif; ?>
                            </div>
                            <div class="stat-label"><?= htmlspecialchars($stat['stat_label']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

    <!-- Director's Letter Section -->
<section class="directors-letter" id="directors-letter">
    <div class="container">
        <?php
        // Fetch director letter content
        $stmt = $pdo->prepare("SELECT * FROM page_content WHERE page_name = 'home' AND section_name LIKE 'dir_%' OR section_name LIKE 'letter_%' OR section_name IN ('director_photo', 'director_name', 'director_role')");
        $stmt->execute();
        $director_content = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $director = [];
        foreach ($director_content as $content) {
            $director[$content['section_name']] = $content;
        }
        ?>
        
        <div class="w3l-heading">
            <h2 class="w3ls_head"><?= $director['dir_letter_section_title']['content'] ?? 'A Message from Our Director' ?></h2>
        </div>
        <div class="letter-content">
            <div class="director-photo">
                <div class="director-card">
                    <img src="<?= !empty($director['director_photo']['image_url']) ? img_url($director['director_photo']['image_url']) : img_url('director-photo.jpg') ?>" alt="School Director">
                    <div class="director-name-badge">
                        <h3><?= $director['director_name']['content'] ?? 'SIBOMANA Gérard' ?></h3>
                        <p><?= $director['director_role']['content'] ?? 'Acting Legal Representative' ?></p>
                    </div>
                </div>
            </div>
            <div class="letter-text">
                <h2><?= $director['letter_text_title']['content'] ?? 'A Letter from the Acting Legal Representative' ?></h2>
                <p class="letter-greeting"><?= $director['letter_greeting']['content'] ?? 'Dear Parents and Guardians,' ?></p>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <?php if (!empty($director["letter_paragraph_{$i}"]['content'])): ?>
                        <p><?= $director["letter_paragraph_{$i}"]['content'] ?></p>
                    <?php endif; ?>
                <?php endfor; ?>
                <div class="letter-signature">
                    <p>With warm regards,</p>
                    <p class="signature-name"><?= $director['letter_signature_name']['content'] ?? 'SIBOMANA Gérard' ?></p>
                    <p><?= $director['letter_signature_role']['content'] ?? 'Acting Legal Representative' ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Photo Gallery Section -->
    <section class="photo-gallery-section" id="gallery">
        <div class="container">
            <div class="w3l-heading">
                <h2 class="w3ls_head">Campus Life Gallery</h2>
            </div>
            <div class="gallery-wrapper">
                <div class="gallery-grid" id="galleryGrid">
                    <!-- Gallery items will be loaded via AJAX -->
                    <div class="loading-spinner" style="grid-column: 1/-1; text-align: center; padding: 50px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: var(--primary-teal);"></i>
                    </div>
                </div>
                <div class="gallery-sidebar">
                    <div class="gallery-info-card">
                        <h3>Our Campus Life</h3>
                        <p>Experience the vibrant environment at Mount Carmel School through our photo gallery. From classroom activities to sports events, see how our students learn, grow, and thrive.</p>
                        <a href="<?= url('gallery') ?>" class="btn-view-gallery">
                            View Full Gallery
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Modal -->
    <div class="gallery-modal" id="galleryModal">
        <div class="gallery-modal-backdrop"></div>
        <div class="gallery-modal-content">
            <div class="gallery-modal-header">
                <h3 class="gallery-modal-title">Gallery</h3>
                <button class="gallery-modal-close" aria-label="Close gallery">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="gallery-modal-body">
                <div class="gallery-loading"></div>
                <div class="gallery-modal-counter"></div>
                <button class="gallery-modal-nav prev" aria-label="Previous image">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="gallery-modal-image-container">
                    <img class="gallery-modal-image" src="" alt="">
                </div>
                <button class="gallery-modal-nav next" aria-label="Next image">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="gallery-modal-footer">
                <p class="gallery-modal-description"></p>
            </div>
        </div>
    </div>

    <!-- Educational Programs Section -->
<div class="advantages" style="background: url('<?= img_url('partner-bg2.jpg') ?>') no-repeat; background-size: cover; background-attachment: fixed;">
    <div class="agile-dot">
        <div class="container">
            <div class="advantages-main">
                <div class="w3l-heading">
                    <h3 class="w3ls_head"><?= $pages_content['edu_program_head']['content'] ?? 'Our Educational Programs' ?></h3>
                </div>
                <div class="advantage-bottom">
                    <?php
                    // Fetch educational programs
                    $stmt = $pdo->query("SELECT * FROM educational_programs WHERE status = 'active' ORDER BY display_order LIMIT 3");
                    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    
                    <?php if (empty($programs)): ?>
                        <!-- Default programs -->
                        <div class="col-md-4 advantage-grid">
                            <div class="program-card">
                                <div class="program-icon">
                                    <i class="fas fa-baby"></i>
                                </div>
                                <h3>Nursery School</h3>
                                <p class="program-subtitle">Francophone Program</p>
                                <p>Safe and stimulating environment for early childhood development...</p>
                                <a href="#nursery" class="btn-program">Learn More</a>
                            </div>
                        </div>
                        <!-- ... other default programs ... -->
                    <?php else: ?>
                        <?php foreach ($programs as $program): ?>
                            <div class="col-md-4 advantage-grid">
                                <div class="program-card">
                                    <div class="program-icon">
                                        <i class="<?= $program['icon_class'] ?>"></i>
                                    </div>
                                    <h3><?= htmlspecialchars($program['title']) ?></h3>
                                    <?php if (!empty($program['subtitle'])): ?>
                                        <p class="program-subtitle"><?= htmlspecialchars($program['subtitle']) ?></p>
                                    <?php endif; ?>
                                    <p><?= substr(strip_tags($program['description']), 0, 120) ?>...</p>
                                    <a href="#<?= strtolower(str_replace(' ', '-', $program['title'])) ?>" class="btn-program">Learn More</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Why Choose MCS Section -->
<section class="why-choose" id="why-choose">
    <div class="container">
        <div class="w3l-heading">
            <h2 class="w3ls_head">Why Choose Mount Carmel School?</h2>
        </div>
        <div class="row">
            <?php
            // Fetch why choose items
            $stmt = $pdo->query("SELECT * FROM why_choose_items WHERE status = 'active' ORDER BY display_order");
            $why_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($why_items)) {
                // Default content
                $why_items = [
                    ['icon_class' => 'fas fa-user-graduate', 'title' => 'Experienced Faculty', 'description' => 'Our dedicated teachers are highly qualified...'],
                    // ... other default items
                ];
            }
            ?>
            
            <div class="col-md-6 why-left">
                <?php for ($i = 0; $i < min(3, count($why_items)); $i++): ?>
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="<?= $why_items[$i]['icon_class'] ?>"></i>
                        </div>
                        <div class="why-content">
                            <h3><?= htmlspecialchars($why_items[$i]['title']) ?></h3>
                            <p><?= htmlspecialchars($why_items[$i]['description']) ?></p>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
            <div class="col-md-6 why-right">
                <?php for ($i = 3; $i < min(10, count($why_items)); $i++): ?>
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="<?= $why_items[$i]['icon_class'] ?>"></i>
                        </div>
                        <div class="why-content">
                            <h3><?= htmlspecialchars($why_items[$i]['title']) ?></h3>
                            <p><?= htmlspecialchars($why_items[$i]['description']) ?></p>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>

    <!-- News and Events Section -->
    <section class="news-events" id="news">
        <div class="container">
            <div class="w3l-heading">
                <h2 class="w3ls_head">Latest News & Events</h2>
            </div>
            <div class="row" id="newsContainer">
                <!-- News items will be loaded via AJAX -->
                <div class="col-md-12" style="text-align: center; padding: 50px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: var(--primary-teal);"></i>
                </div>
            </div>
            <div class="text-center" style="margin-top: 40px;">
                <a href="<?= url('news') ?>" class="btn btn-secondary">View All News & Events</a>
            </div>
        </div>
    </section>

        <!-- Testimonials Section -->
        <section class="testimonials" id="testimonials">
            <div class="container">
                <div class="w3l-heading">
                    <h2 class="w3ls_head">What Parents Say About Our School</h2>
                </div>
                
                <div class="carousel-wrapper">
                    <button class="carousel-nav prev" id="testimonialPrev">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="carousel-nav next" id="testimonialNext">
                        <i class="fas fa-chevron-right"></i>
                    </button>

                    <div class="carousel-container">
                        <div class="carousel-track" id="testimonialTrack">
                            <!-- Testimonials will be loaded via AJAX -->
                            <div class="testimonial-slide" style="text-align: center; padding: 50px;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: var(--primary-teal);"></i>
                            </div>
                        </div>
                    </div>

                    <div class="carousel-dots" id="testimonialDots">
                        <!-- Dots will be generated dynamically -->
                    </div>
                </div>
            </div>
        </section>

    <!-- Footer -->
    <?php include_once get_layout('footer'); ?>

    <!-- jQuery -->
    <?php include_once get_layout('scripts'); ?>
    
    <!-- Custom Scripts for Homepage -->
    <script>
    // Base URL for API calls
    const BASE_URL = '<?= url() ?>';
    
    // Global variables for gallery and testimonials
    let galleryImages = [];
    let currentGalleryIndex = 0;
    let testimonials = [];
    let currentTestimonialIndex = 0;
    let testimonialInterval;
    
    $(document).ready(function() {
        loadGalleryImages();
        loadNewsItems();
        loadTestimonials();
        animateStats();
        setupGalleryModal();
        setupTestimonialCarousel();
    });

    function loadGalleryImages() {
        $.ajax({
            url: '<?= url('api/gallery') ?>',
            method: 'GET',
            data: { 
                action: 'get_images',
                limit: 6 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    galleryImages = response.data;
                    displayGalleryImages(galleryImages);
                } else {
                    $('#galleryGrid').html('<p style="grid-column: 1/-1; text-align: center; color: #6c757d;">No gallery images available.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Gallery API Error:', error);
                $('#galleryGrid').html('<p style="grid-column: 1/-1; text-align: center; color: #dc3545;">Failed to load gallery images.</p>');
            }
        });
    }

    function displayGalleryImages(images) {
        let html = '';
        images.forEach(function(image, index) {
            html += `
                <figure class="gallery-item" onclick="openGalleryModal(${index})">
                    <img src="<?= img_url('${image.image_url}') ?>" alt="${image.title || 'Gallery Image'}">
                    <figcaption>
                        <h3>${image.title || 'Campus Life'}</h3>
                        <p>${image.description || ''}</p>
                    </figcaption>
                </figure>
            `;
        });
        $('#galleryGrid').html(html);
    }

    function setupGalleryModal() {
        const modal = $('#galleryModal');
        const closeBtn = modal.find('.gallery-modal-close');
        const backdrop = modal.find('.gallery-modal-backdrop');
        const prevBtn = modal.find('.gallery-modal-nav.prev');
        const nextBtn = modal.find('.gallery-modal-nav.next');
        const modalImage = modal.find('.gallery-modal-image');
        const modalTitle = modal.find('.gallery-modal-title');
        const modalDescription = modal.find('.gallery-modal-description');
        const modalCounter = modal.find('.gallery-modal-counter');
        const loadingSpinner = modal.find('.gallery-loading');

        // Close modal when clicking close button or backdrop
        closeBtn.click(closeGalleryModal);
        backdrop.click(closeGalleryModal);
        
        // Navigation
        prevBtn.click(function() {
            navigateGallery(-1);
        });
        
        nextBtn.click(function() {
            navigateGallery(1);
        });
        
        // Keyboard navigation
        $(document).keydown(function(e) {
            if (modal.hasClass('active')) {
                if (e.key === 'Escape') {
                    closeGalleryModal();
                } else if (e.key === 'ArrowLeft') {
                    navigateGallery(-1);
                } else if (e.key === 'ArrowRight') {
                    navigateGallery(1);
                }
            }
        });
        
        // Swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        modal.on('touchstart', function(e) {
            touchStartX = e.originalEvent.changedTouches[0].screenX;
        });
        
        modal.on('touchend', function(e) {
            touchEndX = e.originalEvent.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next image
                    navigateGallery(1);
                } else {
                    // Swipe right - previous image
                    navigateGallery(-1);
                }
            }
        }
    }

    function openGalleryModal(index) {
        if (galleryImages.length === 0) return;
        
        currentGalleryIndex = index;
        updateGalleryModal();
        $('#galleryModal').addClass('active');
        $('body').css('overflow', 'hidden');
    }

    function closeGalleryModal() {
        $('#galleryModal').removeClass('active');
        $('body').css('overflow', '');
    }

    function navigateGallery(direction) {
        currentGalleryIndex += direction;
        
        // Loop around if at the beginning or end
        if (currentGalleryIndex < 0) {
            currentGalleryIndex = galleryImages.length - 1;
        } else if (currentGalleryIndex >= galleryImages.length) {
            currentGalleryIndex = 0;
        }
        
        updateGalleryModal();
    }

    function updateGalleryModal() {
        if (galleryImages.length === 0) return;
        
        const image = galleryImages[currentGalleryIndex];
        const modal = $('#galleryModal');
        const modalImage = modal.find('.gallery-modal-image');
        const loadingSpinner = modal.find('.gallery-loading');
        
        // Show loading
        loadingSpinner.addClass('active');
        modalImage.css('opacity', '0');
        
        // Load image
        modalImage.on('load', function() {
            loadingSpinner.removeClass('active');
            modalImage.css('opacity', '1');
        });
        
        modalImage.on('error', function() {
            loadingSpinner.removeClass('active');
            console.error('Failed to load image:', image.image_url);
        });
        
        modalImage.attr('src', '<?= img_url("' + image.image_url + '") ?>');
        modalImage.attr('alt', image.title || 'Gallery Image');
        modal.find('.gallery-modal-title').text(image.title || 'Gallery Image');
        modal.find('.gallery-modal-description').text(image.description || '');
        modal.find('.gallery-modal-counter').text(`${currentGalleryIndex + 1} / ${galleryImages.length}`);
        
        // Update navigation buttons
        const prevBtn = modal.find('.gallery-modal-nav.prev');
        const nextBtn = modal.find('.gallery-modal-nav.next');
        prevBtn.prop('disabled', currentGalleryIndex === 0);
        nextBtn.prop('disabled', currentGalleryIndex === galleryImages.length - 1);
    }

    function loadNewsItems() {
        $.ajax({
            url: '<?= url('api/news') ?>',
            method: 'GET',
            data: { 
                action: 'get_news',
                limit: 4 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    displayNewsItems(response.data);
                } else {
                    $('#newsContainer').html('<div class="col-md-12"><p style="text-align: center; color: #6c757d;">No news available at the moment.</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('News API Error:', error);
                $('#newsContainer').html('<div class="col-md-12"><p style="text-align: center; color: #dc3545;">Failed to load news items.</p></div>');
            }
        });
    }

    function displayNewsItems(newsItems) {
        let html = '';
        newsItems.forEach(function(news) {
            // Format date
            const date = new Date(news.created_at || news.date);
            const day = date.getDate();
            const month = date.toLocaleString('en', { month: 'short' });
            
            html += `
                <div class="col-md-3 col-sm-6 news-item">
                    <div class="news-card">
                        <div class="news-image">
                            <img src="<?= img_url('${news.image_url}') ?>" alt="${news.title}" class="img-responsive">
                            <div class="news-date">
                                <span class="day">${day}</span>
                                <span class="month">${month.toUpperCase()}</span>
                            </div>
                        </div>
                        <div class="news-content">
                            <h3>${news.title}</h3>
                            <p>${news.excerpt || (news.description ? news.description.substring(0, 100) + '...' : '')}</p>
                            <a href="${BASE_URL}/news-detail?id=${news.id}" class="read-more">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#newsContainer').html(html);
    }

    function loadTestimonials() {
    $.ajax({
        url: '<?= url('api/testimonials') ?>',
        method: 'GET',
        data: { 
            action: 'get_testimonials',
            limit: 10 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                testimonials = response.data;
                displayTestimonials(testimonials);
                setupTestimonialCarousel();
                startTestimonialCarousel();
            } else {
                $('#testimonialTrack').html('<div class="testimonial-slide"><p style="text-align: center; color: #6c757d; padding: 40px;">No testimonials available at the moment.</p></div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Testimonials API Error:', error);
            $('#testimonialTrack').html('<div class="testimonial-slide"><p style="text-align: center; color: #dc3545; padding: 40px;">Failed to load testimonials.</p></div>');
        }
    });
}

function displayTestimonials(testimonials) {
    let html = '';
    let dotsHtml = '';
    
    testimonials.forEach(function(testimonial, index) {
        // Get initials for avatar placeholder
        const names = testimonial.name.split(' ');
        const initials = names.length >= 2 
            ? (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase()
            : names[0].substring(0, 2).toUpperCase();
        
        // Generate star rating
        const rating = testimonial.rating || 5;
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                starsHtml += '<i class="fas fa-star"></i>';
            } else {
                starsHtml += '<i class="far fa-star"></i>';
            }
        }
        
        html += `
            <div class="testimonial-slide">
                <div class="testimonial-avatar">
                    ${testimonial.image_url ? 
                        `<img src="<?= img_url('${testimonial.image_url}') ?>" alt="${testimonial.name}">` : 
                        `<div class="avatar-placeholder">${initials}</div>`
                    }
                </div>
                <div class="testimonial-name">${testimonial.name}</div>
                <div class="testimonial-role">${testimonial.role || 'Parent'}</div>
                <div class="testimonial-text">
                    "${testimonial.content}"
                </div>
                <div class="testimonial-rating">
                    ${starsHtml}
                </div>
            </div>
        `;
    });
    
    $('#testimonialTrack').html(html);
    
    // Create dots based on number of slides
    const totalSlides = testimonials.length;
    const slidesPerView = getSlidesPerView();
    const totalDots = Math.ceil(totalSlides / slidesPerView);
    
    for (let i = 0; i < totalDots; i++) {
        dotsHtml += `<button class="carousel-dot ${i === 0 ? 'active' : ''}" data-index="${i}"></button>`;
    }
    
    $('#testimonialDots').html(dotsHtml);
}

function getSlidesPerView() {
    if (window.innerWidth <= 768) {
        return 1;
    } else if (window.innerWidth <= 992) {
        return 2;
    } else {
        return 3;
    }
}

function setupTestimonialCarousel() {
    const $track = $('#testimonialTrack');
    const $slides = $('.testimonial-slide');
    
    if ($slides.length === 0) return;
    
    let currentIndex = 0;
    let cardsPerView = getSlidesPerView();
    let autoPlayInterval;
    
    function updateCardsPerView() {
        cardsPerView = getSlidesPerView();
    }
    
    function moveCarousel() {
        const slideWidth = $slides.first().outerWidth(true);
        const offset = -currentIndex * slideWidth;
        $track.css('transform', `translateX(${offset}px)`);
        updateDots();
    }
    
    function updateDots() {
        $('.carousel-dot').removeClass('active');
        const activeDot = Math.floor(currentIndex / cardsPerView);
        $('.carousel-dot').eq(activeDot).addClass('active');
    }
    
    function nextSlide() {
        const maxIndex = $slides.length - cardsPerView;
        if (currentIndex < maxIndex) {
            currentIndex++;
        } else {
            currentIndex = 0;
        }
        moveCarousel();
    }
    
    function prevSlide() {
        const maxIndex = $slides.length - cardsPerView;
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            currentIndex = maxIndex;
        }
        moveCarousel();
    }
    
    function goToSlide(index) {
        currentIndex = index * cardsPerView;
        moveCarousel();
    }
    
    function startAutoPlay() {
        autoPlayInterval = setInterval(nextSlide, 5000);
    }
    
    function stopAutoPlay() {
        clearInterval(autoPlayInterval);
    }
    
    // Event listeners
    $('#testimonialNext').on('click', function() {
        stopAutoPlay();
        nextSlide();
        startAutoPlay();
    });
    
    $('#testimonialPrev').on('click', function() {
        stopAutoPlay();
        prevSlide();
        startAutoPlay();
    });
    
    $(document).on('click', '.carousel-dot', function() {
        stopAutoPlay();
        const dotIndex = $(this).data('index');
        goToSlide(dotIndex);
        startAutoPlay();
    });
    
    // Handle resize
    $(window).on('resize', function() {
        updateCardsPerView();
        currentIndex = 0;
        moveCarousel();
        // Recreate dots if needed
        const totalSlides = $slides.length;
        const totalDots = Math.ceil(totalSlides / cardsPerView);
        const currentDots = $('.carousel-dot').length;
        
        if (totalDots !== currentDots) {
            let dotsHtml = '';
            for (let i = 0; i < totalDots; i++) {
                dotsHtml += `<button class="carousel-dot ${i === 0 ? 'active' : ''}" data-index="${i}"></button>`;
            }
            $('#testimonialDots').html(dotsHtml);
        } else {
            updateDots();
        }
    });
    
    // Pause on hover
    $('.carousel-container').hover(
        function() { stopAutoPlay(); },
        function() { startAutoPlay(); }
    );
    
    // Initialize
    moveCarousel();
    startAutoPlay();
    
    // Make functions available globally for the carousel
    window.testimonialCarousel = {
        next: nextSlide,
        prev: prevSlide,
        goTo: goToSlide
    };
}

    // Animate Statistics Numbers
    function animateStats() {
        $('.stat-number').each(function() {
            const $this = $(this);
            const countTo = parseInt($this.attr('data-count'));
            
            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(this.countNum);
                }
            });
        });
    }

    // Debug function
    function debugGallery() {
        console.log('Gallery Items Found:', $('.gallery-item').length);
        console.log('Gallery Images Array:', galleryImages.length);
        console.log('Gallery Modal Element:', $('#galleryModal').length);
    }
    
    </script>
    
</body>
</html>