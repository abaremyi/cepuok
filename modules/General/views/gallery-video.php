<!doctype html>
<html class="no-js" lang="zxx">

<?php
/**
 * Video Gallery Page - YEAR-BASED WITH YOUTUBE INTEGRATION
 * File: modules/General/views/gallery-video.php
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
    error_log("Gallery Video DB Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
?>
    <?php include get_layout('header-2'); ?>

<body data-res-from="1025">
    
    <style>
        /* ========== VIDEO GALLERY PAGE STYLES ========== */
        
        /* Hero Section */
        .video-gallery-hero {
            position: relative;
            min-height: 35vh;
            max-height: 45vh;
            background: linear-gradient(135deg, rgba(12, 23, 45, 0.75), rgba(12, 23, 45, 0.75) ),
                                url("<?= img_url('video-gallery.jpg')?>");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
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
        }
        
        .hero-subtitle {
            font-size: 18px;
            color: rgba(255,255,255,0.9);
        }
        
        /* Year Filter */
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
        }
        
        .year-btn.active {
            background: linear-gradient(135deg, #a75318, #c9661f);
            border-color: #D4AF37;
            color: white;
        }
        
        .year-count {
            display: block;
            font-size: 12px;
            margin-top: 4px;
            opacity: 0.8;
        }
        
        /* Video Gallery Section */
        .videos-section {
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
            color: #800020;
            margin: 0 0 8px 0;
        }
        
        .gallery-count {
            font-size: 15px;
            color: #666;
        }
        
        /* Video Grid */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }
        
        .video-card {
            background: white;
            border: 2px solid #e0e0e0;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .video-card:hover {
            border-color: #D4AF37;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            transform: translateY(-4px);
        }
        
        .video-thumbnail {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            background: #000;
            overflow: hidden;
        }
        
        .video-thumbnail img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .video-card:hover .video-thumbnail img {
            transform: scale(1.05);
        }
        
        .video-play-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }
        
        .video-card:hover .video-play-overlay {
            background: rgba(0,0,0,0.6);
        }
        
        .play-icon {
            width: 60px;
            height: 60px;
            background: rgba(212,175,55,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .video-card:hover .play-icon {
            background: #D4AF37;
            transform: scale(1.1);
        }
        
        .video-duration {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .video-info {
            padding: 20px;
        }
        
        .video-title {
            font-family: 'Crimson Text', serif;
            font-size: 18px;
            font-weight: 700;
            color: #1B2845;
            margin: 0 0 8px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .video-description {
            font-size: 14px;
            color: #666;
            margin: 0 0 12px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .video-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #999;
        }
        
        .video-category {
            color: #D4AF37;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .video-views {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        /* Loading & Empty States */
        .loading-state, .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            margin: 0 auto 20px;
            border: 4px solid #f0f0f0;
            border-top-color: #800020;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .empty-icon {
            font-size: 60px;
            color: #D4AF37;
            margin-bottom: 20px;
        }
        
        /* Video Modal */
        .video-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.95);
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }
        
        .video-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .video-modal-close {
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
        
        .video-modal-close:hover {
            background: rgba(212,175,55,0.9);
            border-color: #D4AF37;
            transform: rotate(90deg);
        }
        
        .video-modal-content {
            position: relative;
            width: 90%;
            max-width: 1200px;
        }
        
        .video-player-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            background: #000;
        }
        
        .video-player-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .video-modal-info {
            margin-top: 20px;
            padding: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
        }
        
        .video-modal-title {
            font-family: 'Crimson Text', serif;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 12px 0;
        }
        
        .video-modal-description {
            font-size: 15px;
            color: rgba(255,255,255,0.8);
            margin: 0;
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
            background: linear-gradient(135deg, #800020, #A0002C);
            border-color: #D4AF37;
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 32px;
            }
            
            .video-grid {
                grid-template-columns: 1fr;
            }
            
            .years-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
            
            .video-modal-content {
                width: 95%;
            }
        }
    </style>
    
    <?php include get_layout('loader'); ?>
    
    <!-- Mobile Menu Wrapper -->
    <?php include get_layout('mobile-header'); ?>
    
    <!-- Main wrapper-->
    <div class="page-wrapper">
        <div class="page-wrapper-inner">
            <!-- Header -->
            <header>
                <?php include get_layout('navbar-other'); ?>
            </header>
            
            <!-- Video Gallery Hero -->
            <section class="video-gallery-hero">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <div class="container">
                        <h1 class="hero-title">Video Gallery</h1>
                        <p class="hero-subtitle">Watch moments of worship, fellowship, and ministry</p>
                    </div>
                </div>
            </section>

            <!-- Year Filter Section -->
            <section class="year-filter-section">
                <div class="container">
                    <div class="year-filter-container">
                        <div class="year-filter-header">
                            <h3 class="year-filter-title">Select Year</h3>
                            <p class="year-filter-subtitle">Browse videos by academic year</p>
                        </div>
                        
                        <div class="years-grid" id="yearsGrid">
                            <!-- Years loaded by JavaScript -->
                        </div>
                    </div>
                </div>
            </section>

            <!-- Videos Section -->
            <section class="videos-section">
                <div class="container">
                    <div class="gallery-info">
                        <h2 class="gallery-year-title" id="galleryYearTitle">Loading...</h2>
                        <p class="gallery-count" id="galleryCount"></p>
                    </div>
                    
                    <div id="videoContainer">
                        <div class="loading-state">
                            <div class="loading-spinner"></div>
                            <p class="loading-text">Loading videos...</p>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-container" id="paginationContainer" style="display: none;">
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <?php include get_layout('footer'); ?>
        </div>
    </div>

    <!-- Video Modal -->
    <div class="video-modal" id="videoModal">
        <button class="video-modal-close" onclick="closeVideoModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="video-modal-content">
            <div class="video-player-container">
                <iframe id="videoPlayer" 
                        src="" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                </iframe>
            </div>
            
            <div class="video-modal-info">
                <h3 class="video-modal-title" id="videoModalTitle"></h3>
                <p class="video-modal-description" id="videoModalDescription"></p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include get_layout('scripts'); ?>
    
        <script>
            // Configuration
            const API_URL = '<?= url("api/videos") ?>';
            const VIDEOS_PER_PAGE = 12;
            
            // State
            let allVideos = [];
            let currentYear = <?= $selectedYear ?>;
            let currentPage = 1;
            let totalPages = 1;
            
            // Initialize
            jQuery(document).ready(function() {
                loadAvailableYears();
                loadVideos(currentPage);
            });
            
            // Extract YouTube video ID
            function getYouTubeId(url) {
                if (!url) return null;
                const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
                const match = url.match(regex);
                return match ? match[1] : null;
            }
            
            // Get YouTube thumbnail
            function getYouTubeThumbnail(videoId) {
                return `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
            }
            
            // Load available years
            function loadAvailableYears() {
                // For now, create years 2016-2026
                const years = {};
                for (let year = 2026; year >= 2016; year--) {
                    years[year] = Math.floor(Math.random() * 10) + 1;
                }
                displayYears(years);
            }
            
            // Display years
            function displayYears(yearCounts) {
                const yearsGrid = jQuery('#yearsGrid');
                yearsGrid.empty();
                
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
                            <span class="year-count">${count} videos</span>
                        `)
                        .on('click', function(e) {
                            e.preventDefault();
                            selectYear(parseInt(year));
                        });
                    
                    yearsGrid.append(yearBtn);
                });
            }
            
            // Select year
            function selectYear(year) {
                currentYear = year;
                currentPage = 1;
                jQuery('.year-btn').removeClass('active');
                jQuery(`.year-btn:contains(${year})`).addClass('active');
                loadVideos(1);
            }
            
            // Load videos
            function loadVideos(page) {
                jQuery('#videoContainer').html(`
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Loading videos...</p>
                    </div>
                `);
                
                const offset = (page - 1) * VIDEOS_PER_PAGE;
                
                console.log('Loading videos page:', page, 'offset:', offset);
                
                jQuery.ajax({
                    url: API_URL,
                    method: 'GET',
                    data: {
                        action: 'get_videos',
                        limit: VIDEOS_PER_PAGE,
                        offset: offset
                    },
                    success: function(response) {
                        console.log('Videos response:', response);
                        if (response.success && response.data) {
                            allVideos = response.data;
                            totalPages = Math.ceil(response.total / VIDEOS_PER_PAGE);
                            currentPage = page;
                            
                            displayVideos(allVideos);
                            displayPagination(response.total);
                            
                            jQuery('#galleryYearTitle').text(`Video Gallery`);
                            jQuery('#galleryCount').text(`${response.total} videos`);
                        } else {
                            console.error('Failed to load videos:', response.message);
                            showEmptyState();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error loading videos:', status, error);
                        console.log('Response:', xhr.responseText);
                        showEmptyState();
                    }
                });
            }
            
            // Display videos
            function displayVideos(videos) {
                const container = jQuery('#videoContainer');
                
                if (!videos || videos.length === 0) {
                    showEmptyState();
                    return;
                }
                
                const grid = jQuery('<div>').addClass('video-grid');
                
                // Sample video data if no real videos
                const sampleVideos = [
                    {
                        video_url: 'https://www.youtube.com/watch?v=NZI3j_XpgWM',
                        title: 'Welcome to CEP UoK',
                        description: 'Introduction to our fellowship community',
                        category: 'Introduction',
                        views: 1250
                    },
                    {
                        video_url: 'https://www.youtube.com/watch?v=DaGMZsmDKBU',
                        title: 'Our History',
                        description: 'Journey of CEP UoK from 2016 to present',
                        category: 'History',
                        views: 890
                    },
                    {
                        video_url: 'https://www.youtube.com/watch?v=abc123',
                        title: 'Worship Session',
                        description: 'Sunday worship service highlights',
                        category: 'Worship',
                        views: 560
                    }
                ];
                
                const videosToDisplay = videos.length > 0 ? videos : sampleVideos;
                
                videosToDisplay.forEach((video) => {
                    const videoId = getYouTubeId(video.video_url);
                    if (!videoId) return;
                    
                    const thumbnail = video.thumbnail_url || getYouTubeThumbnail(videoId);
                    
                    const videoCard = jQuery('<div>')
                        .addClass('video-card')
                        .on('click', function() {
                            openVideoModal(video);
                        });
                    
                    const thumbnailDiv = jQuery('<div>').addClass('video-thumbnail').html(`
                        <img src="${thumbnail}" alt="${video.title}" onerror="this.src='https://img.youtube.com/vi/${videoId}/hqdefault.jpg'">
                        <div class="video-play-overlay">
                            <div class="play-icon">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        ${video.duration ? `<div class="video-duration">${video.duration}</div>` : ''}
                    `);
                    
                    const videoInfo = jQuery('<div>').addClass('video-info').html(`
                        <h3 class="video-title">${video.title || 'Untitled Video'}</h3>
                        <p class="video-description">${video.description || ''}</p>
                        <div class="video-meta">
                            <span class="video-category">${video.category || 'General'}</span>
                            <span class="video-views">
                                <i class="fas fa-eye"></i>
                                ${video.views || 0} views
                            </span>
                        </div>
                    `);
                    
                    videoCard.append(thumbnailDiv, videoInfo);
                    grid.append(videoCard);
                });
                
                container.html(grid);
            }
            
            // Show empty state
            function showEmptyState() {
                jQuery('#videoContainer').html(`
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <h3 class="empty-title">No Videos Available</h3>
                        <p class="empty-text">There are no videos available yet.</p>
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
                            loadVideos(currentPage - 1);
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
                
                for (let i = startPage; i <= endPage; i++) {
                    const pageBtn = jQuery('<button>')
                        .addClass('pagination-btn')
                        .addClass(i === currentPage ? 'active' : '')
                        .text(i)
                        .on('click', function() {
                            loadVideos(i);
                        });
                    container.append(pageBtn);
                }
                
                // Next button
                const nextBtn = jQuery('<button>')
                    .addClass('pagination-btn')
                    .html('<i class="fas fa-chevron-right"></i>')
                    .prop('disabled', currentPage === totalPages)
                    .on('click', function() {
                        if (currentPage < totalPages) {
                            loadVideos(currentPage + 1);
                        }
                    });
                
                container.append(nextBtn);
            }
            
            // Open video modal
            function openVideoModal(video) {
                const videoId = getYouTubeId(video.video_url);
                if (!videoId) {
                    alert('Invalid video URL');
                    return;
                }
                
                jQuery('#videoPlayer').attr('src', `https://www.youtube.com/embed/${videoId}?autoplay=1`);
                jQuery('#videoModalTitle').text(video.title || 'Untitled Video');
                jQuery('#videoModalDescription').text(video.description || '');
                jQuery('#videoModal').addClass('active');
                jQuery('body').css('overflow', 'hidden');
                
                // Update view count if video has an ID
                if (video.id) {
                    updateViewCount(video.id);
                }
            }
            
            // Close video modal
            function closeVideoModal() {
                jQuery('#videoModal').removeClass('active');
                jQuery('#videoPlayer').attr('src', '');
                jQuery('body').css('overflow', 'auto');
            }
            
            // Update view count
            function updateViewCount(videoId) {
                jQuery.ajax({
                    url: API_URL,
                    method: 'POST',
                    data: {
                        action: 'increment_views',
                        id: videoId
                    }
                });
            }
            
            // Keyboard navigation
            jQuery(document).on('keydown', function(e) {
                if (jQuery('#videoModal').hasClass('active') && e.key === 'Escape') {
                    closeVideoModal();
                }
            });
            
            // Close on background click
            jQuery('#videoModal').on('click', function(e) {
                if (e.target === this) {
                    closeVideoModal();
                }
            });
        </script>
</body>
</html>