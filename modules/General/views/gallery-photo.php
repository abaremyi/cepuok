<!doctype html>
<html class="no-js" lang="zxx">

<?php
/**
 * Photo Gallery Page - YEAR-BASED WITH PAGINATION
 * File: modules/General/views/gallery-photo.php
 * 5 rows x 4 columns = 20 images per page with jQuery pagination
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
    error_log("Gallery Photo DB Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Get selected year (default to current year)
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
?>
    <?php include get_layout('header-2'); ?>

<body data-res-from="1025">
    
    <style>
        /* ========== PHOTO GALLERY PAGE STYLES ========== */
        
        /* Hero Section */
        /* .gallery-hero {
            position: relative;
            min-height: 60vh;
            max-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
        
            background:
                linear-gradient(135deg, rgba(12, 23, 45, 0.85), rgba(12, 23, 45, 0.85) ),
                url("../../../img/about/about-1.jpeg");
        
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
        } */
        
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
            padding: 60px 20px;
        }
        
        .hero-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: rgba(212, 175, 55, 0.2);
            border: 3px solid #D4AF37;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: #D4AF37;
        }
        
        .hero-title {
            font-family: 'Crimson Text', serif;
            font-size: 42px;
            font-weight: 700;
            margin: 0 0 12px 0;
            color: white;
        }
        
        .hero-subtitle {
            font-size: 18px;
            color: rgba(255,255,255,0.9);
        }
        
        /* Year Filter Section */
        .year-filter-section {
            background: #f8f9fa;
            padding: 40px 0;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .year-filter-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .year-filter-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .year-filter-title {
            font-family: 'Crimson Text', serif;
            font-size: 24px;
            font-weight: 700;
            color: #1B2845;
            margin: 0 0 8px 0;
        }
        
        .year-filter-subtitle {
            font-size: 14px;
            color: #666;
        }
        
        .years-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }
        
        .year-btn {
            padding: 14px 20px;
            background: white;
            border: 2px solid #e0e0e0;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Crimson Text', serif;
            font-size: 18px;
            font-weight: 600;
            color: #404040;
            text-decoration: none;
            display: block;
        }
        
        .year-btn:hover {
            border-color: #D4AF37;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            color: #800020;
        }
        
        .year-btn.active {
            background: linear-gradient(135deg, #bd5e1b, #be6d02);
            border-color: #D4AF37;
            color: white;
            box-shadow: 0 6px 16px rgba(128, 47, 0, 0.3);
        }
        
        .year-count {
            display: block;
            font-size: 12px;
            margin-top: 4px;
            opacity: 0.8;
        }
        
        /* Gallery Section */
        .photo-gallery-section {
            background: white;
            padding: 60px 0;
            min-height: 70vh;
        }
        
        .gallery-info {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .gallery-year-title {
            font-family: 'Crimson Text', serif;
            font-size: 32px;
            font-weight: 700;
            color: #d96d20;
            margin: 0 0 8px 0;
        }
        
        .gallery-count {
            font-size: 15px;
            color: #666;
        }
        
        /* Photo Grid - 5 rows x 4 columns = 20 images */
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 50px;
        }
        
        .photo-item {
            position: relative;
            aspect-ratio: 4/3;
            overflow: hidden;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f5f5f5;
        }
        
        .photo-item:hover {
            border-color: #D4AF37;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            transform: translateY(-4px);
        }
        
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .photo-item:hover img {
            transform: scale(1.08);
        }
        
        .photo-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 16px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .photo-item:hover .photo-overlay {
            opacity: 1;
        }
        
        .photo-title {
            color: white;
            font-family: 'Crimson Text', serif;
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }
        
        .photo-category {
            color: #D4AF37;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Loading State */
        .loading-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            margin: 0 auto 20px;
            border: 4px solid #f0f0f0;
            border-top-color: #802900;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 16px;
            color: #666;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-icon {
            font-size: 60px;
            color: #D4AF37;
            margin-bottom: 20px;
        }
        
        .empty-title {
            font-family: 'Crimson Text', serif;
            font-size: 24px;
            color: #1B2845;
            margin: 0 0 12px 0;
        }
        
        .empty-text {
            font-size: 15px;
            color: #666;
        }
        
        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 50px;
        }
        
        .pagination-btn {
            padding: 10px 16px;
            background: white;
            border: 2px solid #e0e0e0;
            color: #404040;
            font-family: 'Crimson Text', serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .pagination-btn:hover:not(:disabled) {
            border-color: #D4AF37;
            background: #f8f9fa;
        }
        
        .pagination-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        .pagination-btn.active {
            background: linear-gradient(135deg, #802900, #a02d00);
            border-color: #D4AF37;
            color: white;
        }
        
        .pagination-info {
            padding: 10px 20px;
            font-size: 14px;
            color: #666;
        }
        
        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }
        
        .lightbox.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .lightbox-close {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: rgba(0,0,0,0.7);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            font-size: 24px;
            cursor: pointer;
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .lightbox-close:hover {
            background: rgba(212,175,55,0.9);
            border-color: #D4AF37;
            transform: rotate(90deg);
        }
        
        .lightbox-nav {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 60px;
            background: rgba(0,0,0,0.7);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            font-size: 24px;
            cursor: pointer;
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .lightbox-nav:hover {
            background: rgba(212,175,55,0.9);
            border-color: #D4AF37;
        }
        
        .lightbox-nav.prev {
            left: 20px;
        }
        
        .lightbox-nav.next {
            right: 20px;
        }
        
        .lightbox-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .lightbox-image-container {
            position: relative;
            max-width: 100%;
            max-height: 80vh;
        }
        
        .lightbox-image {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .lightbox-image.rotated-90 {
            transform: rotate(90deg);
        }
        
        .lightbox-image.rotated-180 {
            transform: rotate(180deg);
        }
        
        .lightbox-image.rotated-270 {
            transform: rotate(270deg);
        }
        
        .lightbox-image.zoomed {
            transform: scale(1.5);
            cursor: zoom-out;
        }
        
        .lightbox-info {
            margin-top: 20px;
            padding: 20px;
            background: rgba(0,0,0,0.8);
            max-width: 600px;
            text-align: center;
        }
        
        .lightbox-title {
            font-family: 'Crimson Text', serif;
            font-size: 20px;
            font-weight: 600;
            color: white;
            margin: 0 0 8px 0;
        }
        
        .lightbox-description {
            font-size: 14px;
            color: rgba(255,255,255,0.8);
            margin: 0;
        }
        
        .lightbox-meta {
            margin-top: 12px;
            font-size: 12px;
            color: #D4AF37;
        }
        
        /* Lightbox Controls */
        .lightbox-controls {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            background: rgba(0,0,0,0.8);
            padding: 15px 25px;
            z-index: 10001;
        }
        
        .control-btn {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .control-btn:hover {
            background: rgba(212,175,55,0.8);
            border-color: #D4AF37;
        }
        
        .control-btn.active {
            background: #D4AF37;
            border-color: #D4AF37;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .photo-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 32px;
            }
            
            .photo-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .years-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
            
            .lightbox-nav,
            .lightbox-close {
                width: 45px;
                height: 45px;
                font-size: 20px;
            }
            
            .lightbox-controls {
                padding: 12px 20px;
                gap: 8px;
            }
            
            .control-btn {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .photo-grid {
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
            
            <!-- Gallery Hero -->
            <section class="gallery-hero">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <div class="container">
                        <h1 class="hero-title">Photo Gallery</h1>
                        <p class="hero-subtitle">Capturing moments of faith, fellowship, and service</p>
                    </div>
                </div>
            </section>

            <!-- Year Filter Section -->
            <section class="year-filter-section">
                <div class="container">
                    <div class="year-filter-container">
                        <div class="year-filter-header">
                            <h3 class="year-filter-title">Select Year</h3>
                            <p class="year-filter-subtitle">Browse photos by academic year</p>
                        </div>
                        
                        <div class="years-grid" id="yearsGrid">
                            <!-- Years will be loaded here by JavaScript -->
                        </div>
                    </div>
                </div>
            </section>

            <!-- Photo Gallery Section -->
            <section class="photo-gallery-section">
                <div class="container">
                    <div class="gallery-info">
                        <h2 class="gallery-year-title" id="galleryYearTitle">Loading...</h2>
                        <p class="gallery-count" id="galleryCount"></p>
                    </div>
                    
                    <div id="galleryContainer">
                        <!-- Loading state -->
                        <div class="loading-state">
                            <div class="loading-spinner"></div>
                            <p class="loading-text">Loading gallery...</p>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-container" id="paginationContainer" style="display: none;">
                        <!-- Pagination will be generated by JavaScript -->
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <?php include get_layout('footer'); ?>
        </div>
    </div>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close" onclick="closeLightbox()">
            <i class="fas fa-times"></i>
        </button>
        
        <button class="lightbox-nav prev" onclick="navigateImage(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <button class="lightbox-nav next" onclick="navigateImage(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <div class="lightbox-content">
            <div class="lightbox-image-container">
                <img src="" alt="" class="lightbox-image" id="lightboxImage">
            </div>
            
            <div class="lightbox-info" id="lightboxInfo">
                <h3 class="lightbox-title" id="lightboxTitle"></h3>
                <p class="lightbox-description" id="lightboxDescription"></p>
                <p class="lightbox-meta" id="lightboxMeta"></p>
            </div>
        </div>
        
        <div class="lightbox-controls">
            <button class="control-btn" onclick="rotateImage()" title="Rotate">
                <i class="fas fa-redo"></i>
            </button>
            <button class="control-btn" onclick="zoomImage()" title="Zoom">
                <i class="fas fa-search-plus"></i>
            </button>
            <button class="control-btn" onclick="toggleAutoplay()" id="autoplayBtn" title="Autoplay">
                <i class="fas fa-play"></i>
            </button>
            <button class="control-btn" onclick="downloadImage()" title="Download">
                <i class="fas fa-download"></i>
            </button>
            <button class="control-btn" onclick="shareImage()" title="Share">
                <i class="fas fa-share-alt"></i>
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <?php include get_layout('scripts'); ?>
    
    <script>
        // Configuration
        const API_URL = '<?= url("api/gallery") ?>';
        const IMG_URL = '<?= img_url("") ?>';
        const IMAGES_PER_PAGE = 20; // 5 rows x 4 columns
        
        // State
        let allImages = [];
        let currentYear = <?= $selectedYear ?>;
        let currentPage = 1;
        let totalPages = 1;
        let currentImageIndex = 0;
        let rotation = 0;
        let isZoomed = false;
        let autoplayInterval = null;
        let isAutoplayOn = false;
        
        // Initialize
        jQuery(document).ready(function() {
            loadAvailableYears();
            loadGallery(currentYear, currentPage);
        });
        
        // Load available years
        function loadAvailableYears() {
            console.log('Loading years from:', API_URL);
            jQuery.ajax({
                url: API_URL,
                method: 'GET',
                data: { action: 'get_year_counts' },
                success: function(response) {
                    console.log('Year counts response:', response);
                    if (response.success && response.data) {
                        displayYears(response.data);
                    } else {
                        console.error('Failed to load years:', response.message);
                        // Show dummy years if API fails
                        const dummyYears = {
                            2026: 25,
                            2025: 8,
                            2024: 12,
                            2023: 6,
                            2022: 5,
                            2021: 3
                        };
                        displayYears(dummyYears);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading years:', status, error);
                    console.log('Response:', xhr.responseText);
                    // Show dummy years on error
                    const dummyYears = {
                        2026: 25,
                        2025: 8,
                        2024: 12,
                        2023: 6,
                        2022: 5,
                        2021: 3
                    };
                    displayYears(dummyYears);
                }
            });
        }
        
        // Display years
        function displayYears(yearCounts) {
            const yearsGrid = jQuery('#yearsGrid');
            yearsGrid.empty();
            
            // Sort years descending
            const years = Object.keys(yearCounts).sort((a, b) => b - a);
            
            years.forEach(year => {
                const count = yearCounts[year];
                const isActive = parseInt(year) === currentYear;
                
                const yearBtn = jQuery('<a>')
                    .addClass('year-btn')
                    .addClass(isActive ? 'active' : '')
                    .attr('href', '#')
                    .html(`
                        ${year}
                        <span class="year-count">${count} photos</span>
                    `)
                    .on('click', function(e) {
                        e.preventDefault();
                        selectYear(parseInt(year));
                    });
                
                yearsGrid.append(yearBtn);
            });
            
            // If no years found, show message
            if (years.length === 0) {
                yearsGrid.html('<p class="empty-text">No years available</p>');
            }
        }
        
        // Select year
        function selectYear(year) {
            console.log('Selecting year:', year);
            currentYear = year;
            currentPage = 1;
            jQuery('.year-btn').removeClass('active');
            jQuery(`.year-btn:contains(${year})`).addClass('active');
            loadGallery(year, 1);
        }
        
        // Load gallery
        function loadGallery(year, page) {
            jQuery('#galleryContainer').html(`
                <div class="loading-state">
                    <div class="loading-spinner"></div>
                    <p class="loading-text">Loading gallery...</p>
                </div>
            `);
            
            const offset = (page - 1) * IMAGES_PER_PAGE;
            
            console.log('Loading gallery for year:', year, 'page:', page, 'offset:', offset);
            
            jQuery.ajax({
                url: API_URL,
                method: 'GET',
                data: {
                    action: 'get_images',
                    year: year,
                    limit: IMAGES_PER_PAGE,
                    offset: offset
                },
                success: function(response) {
                    console.log('Gallery response:', response);
                    if (response.success) {
                        allImages = response.data;
                        totalPages = Math.ceil(response.total / IMAGES_PER_PAGE);
                        currentPage = page;
                        
                        displayGallery(allImages);
                        displayPagination(response.total);
                        
                        jQuery('#galleryYearTitle').text(`${year} Gallery`);
                        jQuery('#galleryCount').text(`${response.total} photos`);
                        
                        // Update active year button
                        jQuery('.year-btn').removeClass('active');
                        jQuery(`.year-btn:contains(${year})`).addClass('active');
                    } else {
                        console.error('Failed to load gallery:', response.message);
                        showEmptyState();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading gallery:', status, error);
                    console.log('Response:', xhr.responseText);
                    showEmptyState();
                }
            });
        }
        
        // Display gallery
        function displayGallery(images) {
            const container = jQuery('#galleryContainer');
            
            if (!images || images.length === 0) {
                showEmptyState();
                return;
            }
            
            const grid = jQuery('<div>').addClass('photo-grid');
            
            images.forEach((image, index) => {
                const photoItem = jQuery('<div>')
                    .addClass('photo-item')
                    .on('click', function() {
                        openLightbox(index);
                    });
                
                const img = jQuery('<img>')
                    .attr('src', IMG_URL + image.image_url)
                    .attr('alt', image.title || 'Gallery Image')
                    .on('error', function() {
                        // Fallback image if the original fails to load
                        this.src = 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=400';
                    });
                
                const overlay = jQuery('<div>').addClass('photo-overlay').html(`
                    <p class="photo-category">${image.category || 'General'}</p>
                    <h4 class="photo-title">${image.title || 'Untitled'}</h4>
                `);
                
                photoItem.append(img, overlay);
                grid.append(photoItem);
            });
            
            container.html(grid);
        }
        
        // Show empty state
        function showEmptyState() {
            jQuery('#galleryContainer').html(`
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <h3 class="empty-title">No Photos Available</h3>
                    <p class="empty-text">There are no photos for this year yet.</p>
                </div>
            `);
            jQuery('#paginationContainer').hide();
        }
        
        // Display pagination
        function displayPagination(total) {
            const container = jQuery('#paginationContainer');
            
            if (totalPages <= 1) {
                container.hide();
                return;
            }
            
            container.show().empty();
            
            // Previous button
            const prevBtn = jQuery('<button>')
                .addClass('pagination-btn')
                .html('<i class="fas fa-chevron-left"></i>')
                .prop('disabled', currentPage === 1)
                .on('click', function() {
                    if (currentPage > 1) {
                        loadGallery(currentYear, currentPage - 1);
                    }
                });
            
            container.append(prevBtn);
            
            // Page numbers
            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            
            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }
            
            if (startPage > 1) {
                const firstBtn = jQuery('<button>')
                    .addClass('pagination-btn')
                    .text('1')
                    .on('click', function() {
                        loadGallery(currentYear, 1);
                    });
                container.append(firstBtn);
                
                if (startPage > 2) {
                    container.append(jQuery('<span>').addClass('pagination-info').text('...'));
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = jQuery('<button>')
                    .addClass('pagination-btn')
                    .addClass(i === currentPage ? 'active' : '')
                    .text(i)
                    .on('click', function() {
                        loadGallery(currentYear, i);
                    });
                container.append(pageBtn);
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    container.append(jQuery('<span>').addClass('pagination-info').text('...'));
                }
                
                const lastBtn = jQuery('<button>')
                    .addClass('pagination-btn')
                    .text(totalPages)
                    .on('click', function() {
                        loadGallery(currentYear, totalPages);
                    });
                container.append(lastBtn);
            }
            
            // Info
            const start = ((currentPage - 1) * IMAGES_PER_PAGE) + 1;
            const end = Math.min(currentPage * IMAGES_PER_PAGE, total);
            const info = jQuery('<span>')
                .addClass('pagination-info')
                .text(`${start}-${end} of ${total}`);
            container.append(info);
            
            // Next button
            const nextBtn = jQuery('<button>')
                .addClass('pagination-btn')
                .html('<i class="fas fa-chevron-right"></i>')
                .prop('disabled', currentPage === totalPages)
                .on('click', function() {
                    if (currentPage < totalPages) {
                        loadGallery(currentYear, currentPage + 1);
                    }
                });
            
            container.append(nextBtn);
        }
        
        // Lightbox functions
        function openLightbox(index) {
            currentImageIndex = index;
            rotation = 0;
            isZoomed = false;
            updateLightboxImage();
            jQuery('#lightbox').addClass('active');
            jQuery('body').css('overflow', 'hidden');
        }
        
        function closeLightbox() {
            jQuery('#lightbox').removeClass('active');
            jQuery('body').css('overflow', 'auto');
            if (isAutoplayOn) {
                toggleAutoplay();
            }
        }
        
        function navigateImage(direction) {
            currentImageIndex += direction;
            
            if (currentImageIndex < 0) {
                currentImageIndex = allImages.length - 1;
            } else if (currentImageIndex >= allImages.length) {
                currentImageIndex = 0;
            }
            
            rotation = 0;
            isZoomed = false;
            updateLightboxImage();
        }
        
        function updateLightboxImage() {
            const image = allImages[currentImageIndex];
            const imgElement = jQuery('#lightboxImage');
            
            imgElement
                .attr('src', IMG_URL + image.image_url)
                .attr('alt', image.title || 'Gallery Image')
                .removeClass('rotated-90 rotated-180 rotated-270 zoomed');
            
            jQuery('#lightboxTitle').text(image.title || 'Untitled');
            jQuery('#lightboxDescription').text(image.description || '');
            jQuery('#lightboxMeta').text(`${image.category || 'General'} • ${image.year || ''}`);
        }
        
        function rotateImage() {
            rotation = (rotation + 90) % 360;
            const imgElement = jQuery('#lightboxImage');
            
            imgElement.removeClass('rotated-90 rotated-180 rotated-270');
            
            if (rotation === 90) {
                imgElement.addClass('rotated-90');
            } else if (rotation === 180) {
                imgElement.addClass('rotated-180');
            } else if (rotation === 270) {
                imgElement.addClass('rotated-270');
            }
        }
        
        function zoomImage() {
            isZoomed = !isZoomed;
            jQuery('#lightboxImage').toggleClass('zoomed', isZoomed);
        }
        
        function toggleAutoplay() {
            isAutoplayOn = !isAutoplayOn;
            const btn = jQuery('#autoplayBtn');
            
            if (isAutoplayOn) {
                btn.html('<i class="fas fa-pause"></i>').addClass('active');
                autoplayInterval = setInterval(() => navigateImage(1), 3000);
            } else {
                btn.html('<i class="fas fa-play"></i>').removeClass('active');
                clearInterval(autoplayInterval);
            }
        }
        
        function downloadImage() {
            const image = allImages[currentImageIndex];
            const link = document.createElement('a');
            link.href = IMG_URL + image.image_url;
            link.download = (image.title || 'gallery_image') + '.jpg';
            link.click();
        }
        
        function shareImage() {
            const image = allImages[currentImageIndex];
            
            if (navigator.share) {
                navigator.share({
                    title: image.title || 'Gallery Image',
                    text: image.description || '',
                    url: window.location.href
                });
            } else {
                alert('Sharing not supported on this browser');
            }
        }
        
        // Keyboard navigation
        jQuery(document).on('keydown', function(e) {
            if (jQuery('#lightbox').hasClass('active')) {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') navigateImage(-1);
                if (e.key === 'ArrowRight') navigateImage(1);
                if (e.key === 'r' || e.key === 'R') rotateImage();
                if (e.key === 'z' || e.key === 'Z') zoomImage();
            }
        });
        
        // Close on background click
        jQuery('#lightbox').on('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });
    </script>
</body>
</html>