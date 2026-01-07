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
    $home = 'off';
    $services = 'off';
    $work = 'off';
    $about = 'off';
    $news = 'off';
    $contacts = 'off';
    $gallery = 'active';
    ?>
    <!-- Navbar -->
    <?php include_once get_layout('navbar'); ?>

    <!-- Page Header -->
    <header class="gallery-page-header">
        <div class="container">
            <h1>Our Gallery</h1>
            <p>Explore moments and memories from Mount Carmel School</p>
            <div class="gallery-breadcrumb">
                <a href="/">Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>Gallery</span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="gallery-container">
        
        <!-- Sidebar -->
        <aside class="gallery-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="<?= img_url('logo-only.png') ?>" alt="Logo" onerror="this.style.display='none'">
                </div>
                <h1 class="sidebar-title">Photo Gallery</h1>
                <p class="sidebar-subtitle">Browse by Category</p>
            </div>

            <!-- Categories Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title">Categories</h3>
                <div class="sidebar-categories">
                    <button class="category-btn active" data-category="all">
                        <span>All Photos</span>
                        <span class="category-count" id="count-all">0</span>
                    </button>
                    <button class="category-btn" data-category="academics">
                        <span>Academics</span>
                        <span class="category-count" id="count-academics">0</span>
                    </button>
                    <button class="category-btn" data-category="events">
                        <span>Events</span>
                        <span class="category-count" id="count-events">0</span>
                    </button>
                    <button class="category-btn" data-category="facilities">
                        <span>Facilities</span>
                        <span class="category-count" id="count-facilities">0</span>
                    </button>
                    <button class="category-btn" data-category="extracurricular">
                        <span>Extracurricular</span>
                        <span class="category-count" id="count-extracurricular">0</span>
                    </button>
                    <button class="category-btn" data-category="campus">
                        <span>Campus Life</span>
                        <span class="category-count" id="count-campus">0</span>
                    </button>
                </div>
            </div>

            <!-- Stats Widget -->
            <div class="sidebar-widget sidebar-stats">
                <h3 class="widget-title">Gallery Stats</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <i class="fas fa-images"></i>
                        <span class="stat-value" id="totalImages">0</span>
                        <span class="stat-label">Total Photos</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-folder"></i>
                        <span class="stat-value" id="totalCategories">5</span>
                        <span class="stat-label">Categories</span>
                    </div>
                </div>
            </div>

            <!-- About Widget -->
            <div class="sidebar-widget sidebar-about">
                <h3 class="widget-title">About Our Gallery</h3>
                <p>Discover the vibrant life at Mount Carmel School through our photo gallery. From academic achievements to memorable events, explore the moments that make our school special.</p>
            </div>

            <!-- Social Links -->
            <div class="sidebar-social">
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="gallery-main">
            
            <!-- Gallery Grid -->
            <div class="gallery-grid" id="galleryGrid">
                <div class="gallery-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading gallery...</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-container" id="paginationContainer">
                <!-- Pagination will be loaded dynamically -->
            </div>

        </main>

    </div>

    <!-- Lightbox Modal -->
    <div class="lightbox" id="lightbox">
        <div class="lightbox-header">
            <div class="image-counter" id="imageCounter">1 / 10</div>
            <div class="lightbox-controls">
                <button class="control-btn" id="shareBtn" title="Share">📤</button>
                <button class="control-btn" id="rotateRightBtn" title="Rotate Right">↻</button>
                <button class="control-btn" id="rotateLeftBtn" title="Rotate Left">↺</button>
                <button class="control-btn" id="flipVerticalBtn" title="Flip Vertical">⇅</button>
                <button class="control-btn" id="flipHorizontalBtn" title="Flip Horizontal">⇆</button>
                <button class="control-btn" id="fullscreenBtn" title="Fullscreen">⛶</button>
                <button class="control-btn" id="playBtn" title="Play Slideshow">▶</button>
                <button class="control-btn" id="zoomBtn" title="Zoom">🔍</button>
                <button class="control-btn" id="downloadBtn" title="Download">⬇</button>
                <button class="control-btn" id="closeBtn" title="Close">✕</button>
            </div>
        </div>

        <div class="lightbox-main">
            <button class="nav-arrow left" id="prevBtn">‹</button>
            <div class="lightbox-image-container">
                <img src="" alt="" class="lightbox-image" id="lightboxImage">
                <div class="image-info" id="imageInfo"></div>
            </div>
            <button class="nav-arrow right" id="nextBtn">›</button>
        </div>

        <div class="lightbox-thumbnails" id="thumbnailsContainer"></div>
    </div>

    <!-- Footer -->
    <?php include_once get_layout('footer'); ?>

    <!-- jQuery -->
    <?php include_once get_layout('scripts'); ?>

    <style>
        @import url("https://fonts.googleapis.com/css?family=Arvo");
        
        /* Gallery Page Styles */
        .gallery-page-header {
            background: linear-gradient(135deg, rgba(0, 121, 107, 0.9), rgba(26, 58, 82, 0.9)),
        url('<?= img_url("photo-gallery.jpg") ?>');
            color: white;
            padding: 80px 0 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .gallery-page-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            opacity: 0.3;
        }

        .gallery-page-header h1 {
            font-size: 3rem;
            margin: 0 0 1rem;
            position: relative;
            z-index: 1;
        }

        .gallery-page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .gallery-breadcrumb {
            margin-top: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .gallery-breadcrumb a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .gallery-breadcrumb a:hover {
            opacity: 1;
        }

        .gallery-breadcrumb span {
            margin: 0 10px;
            opacity: 0.6;
        }

        .gallery-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 40px;
        }

        /* Sidebar Styles */
        .gallery-sidebar {
            position: sticky;
            top: 20px;
            height: fit-content;
        }

        .sidebar-header {
            text-align: center;
            padding: 30px 20px;
            /* background: linear-gradient(135deg, #00796B 0%, #004D40 100%); */
            color: white;
            border-radius: 1px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .sidebar-logo img {
            width: 60px;
            height: 60px;
            /* border-radius: 50%; */
            margin-bottom: 15px;
        }

        .sidebar-title {
            font-size: 1.5rem;
            margin: 0 0 5px;
        }

        .sidebar-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .sidebar-widget {
            background: white;
            /* border-radius: 15px; */
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .widget-title {
            font-size: 1.2rem;
            margin: 0 0 20px;
            color: #333;
            font-weight: 600;
        }

        .sidebar-categories {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .category-btn {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: #f5f5f5;
            border: 2px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.95rem;
            color: #555;
        }

        .category-btn:hover {
            background: #e8f5e9;
            border-color: #00796B;
        }

        .category-btn.active {
            background: #00796B;
            color: white;
            border-color: #00796B;
        }

        .category-count {
            background: rgba(0,0,0,0.1);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.85rem;
        }

        .category-btn.active .category-count {
            background: rgba(255,255,255,0.2);
        }

        .sidebar-stats {
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 10px;
        }

        .stat-item i {
            font-size: 1.8rem;
            color: #00796B;
            margin-bottom: 10px;
        }

        .stat-value {
            display: block;
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            display: block;
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .sidebar-about p {
            color: #666;
            line-height: 1.6;
            margin: 0;
        }

        .sidebar-social {
            display: flex;
            justify-content: center;
            gap: 15px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .sidebar-social a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            border-radius: 50%;
            color: #00796B;
            transition: all 0.3s;
        }

        .sidebar-social a:hover {
            background: #00796B;
            color: white;
            transform: translateY(-3px);
        }

        /* Gallery Grid Styles */
        .gallery-main {
            min-height: 600px;
        }

        .gallery-grid {
            display: grid;
            grid-gap: 30px;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            grid-auto-rows: 200px;
            grid-auto-flow: row dense;
        }

        .gallery-item {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            background: #0c9a9a;
            color: #fff;
            background-size: cover;
            background-position: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
            cursor: pointer;
            overflow: hidden;
        }

        .gallery-item img {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-item::after {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 100%);
            opacity: 1;
            transition: opacity 0.3s;
            z-index: 1;
        }

        .gallery-item:hover {
            transform: scale(1.05);
            z-index: 10;
        }

        .gallery-item:hover::after {
            opacity: 0.3;
        }

        .gallery-item:nth-child(3n) {
            grid-row-end: span 2;
        }

        .gallery-item:nth-child(5n) {
            grid-column-end: span 2;
        }

        .item-details {
            position: relative;
            z-index: 2;
            padding: 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
            transform: translateY(100%);
            transition: transform 0.3s;
        }

        .gallery-item:hover .item-details {
            transform: translateY(0);
        }

        .item-details h3 {
            margin: 0 0 8px;
            font-size: 1.2rem;
            color: white;
        }

        .item-details p {
            margin: 0;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.9);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item-category {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #00796B;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            z-index: 2;
            text-transform: capitalize;
        }

        .gallery-loading {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .gallery-loading i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #00796B;
        }

        .no-content {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-content i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ddd;
        }

        /* Lightbox Styles */
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }

        .lightbox.active {
            display: flex;
            flex-direction: column;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .lightbox-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            color: white;
            background: rgba(0, 0, 0, 0.5);
        }

        .image-counter {
            font-size: 1.2rem;
            font-weight: 500;
        }

        .lightbox-controls {
            display: flex;
            gap: 15px;
        }

        .control-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            width: 45px;
            height: 45px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 1.2rem;
            border-radius: 5px;
        }

        .control-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }

        .lightbox-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .lightbox-image-container {
            max-width: 90%;
            max-height: 80vh;
            position: relative;
        }

        .lightbox-image {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .lightbox-image.zoomed {
            transform: scale(2);
            cursor: zoom-out;
        }

        .image-info {
            text-align: center;
            color: white;
            margin-top: 20px;
            padding: 15px;
            background: rgba(0,0,0,0.5);
            border-radius: 10px;
        }

        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            width: 60px;
            height: 60px;
            cursor: pointer;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 10;
            border-radius: 50%;
        }

        .nav-arrow:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-50%) scale(1.1);
        }

        .nav-arrow.left {
            left: 30px;
        }

        .nav-arrow.right {
            right: 30px;
        }

        .lightbox-thumbnails {
            display: flex;
            gap: 10px;
            padding: 20px 30px;
            overflow-x: auto;
            background: rgba(0,0,0,0.3);
        }

        .lightbox-thumbnails::-webkit-scrollbar {
            height: 8px;
        }

        .lightbox-thumbnails::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .lightbox-thumbnails::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 4px;
        }

        .thumbnail {
            min-width: 100px;
            height: 70px;
            cursor: pointer;
            overflow: hidden;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            opacity: 0.6;
            border-radius: 5px;
        }

        .thumbnail:hover {
            opacity: 0.8;
        }

        .thumbnail.active {
            border-color: #0c9a9a;
            opacity: 1;
            box-shadow: 0 0 20px rgba(12, 154, 154, 0.6);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Pagination */
        .pagination-container {
            margin-top: 40px;
            display: flex;
            justify-content: center;
        }

        .pagination {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .page-btn {
            padding: 10px 15px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            color: #333;
        }

        .page-btn:hover:not(.disabled) {
            background: #00796B;
            color: white;
            border-color: #00796B;
        }

        .page-btn.active {
            background: #00796B;
            color: white;
            border-color: #00796B;
        }

        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-dots {
            padding: 0 5px;
            color: #999;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .gallery-container {
                grid-template-columns: 1fr;
            }

            .gallery-sidebar {
                position: relative;
                top: 0;
            }

            .sidebar-header {
                display: flex;
                align-items: center;
                text-align: left;
                gap: 20px;
            }

            .sidebar-categories {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .category-btn {
                flex: 1;
                min-width: 150px;
            }
        }

        @media (max-width: 768px) {
            .gallery-page-header h1 {
                font-size: 2rem;
            }

            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
                grid-auto-rows: 150px;
            }

            .nav-arrow {
                width: 45px;
                height: 45px;
                font-size: 1.5rem;
            }

            .nav-arrow.left {
                left: 10px;
            }

            .nav-arrow.right {
                right: 10px;
            }

            .control-btn {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .lightbox-controls {
                gap: 8px;
            }
        }
    </style>

    <script>
    // Configuration
    const BASE_URL = '<?= url() ?>';
    const API_URL = BASE_URL + '/api/gallery';
    const IMG_URL = '<?= img_url('') ?>';
    
    // State management
    let state = {
        currentPage: 1,
        itemsPerPage: 9,
        currentCategory: 'all',
        totalItems: 0,
        allImages: [],
        filteredImages: []
    };

    let lightboxState = {
        currentIndex: 0,
        rotation: 0,
        flipH: false,
        flipV: false,
        isZoomed: false,
        isPlaying: false,
        slideInterval: null
    };

    // Initialize
    $(document).ready(function() {
        initializePage();
        setupEventListeners();
    });

    function initializePage() {
        loadGalleryImages();
        loadCategoryCounts();
    }

    function setupEventListeners() {
        // Category filters
        $('.category-btn').click(function() {
            $('.category-btn').removeClass('active');
            $(this).addClass('active');
            state.currentCategory = $(this).data('category');
            state.currentPage = 1;
            filterAndDisplayImages();
        });

        // Lightbox controls
        setupLightbox();

        // Keyboard navigation
        $(document).keydown(function(e) {
            if ($('#lightbox').hasClass('active')) {
                switch(e.key) {
                    case 'Escape':
                        closeLightbox();
                        break;
                    case 'ArrowRight':
                        nextImage();
                        break;
                    case 'ArrowLeft':
                        prevImage();
                        break;
                }
            }
        });
    }

    function loadGalleryImages() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { action: 'get_gallery' },
            dataType: 'json',
            beforeSend: function() {
                $('#galleryGrid').html('<div class="gallery-loading"><i class="fas fa-spinner fa-spin"></i><p>Loading gallery...</p></div>');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    state.allImages = response.data;
                    state.totalItems = response.total;
                    $('#totalImages').text(response.total);
                    filterAndDisplayImages();
                } else {
                    $('#galleryGrid').html('<div class="no-content"><i class="fas fa-images"></i><p>No images found in gallery.</p></div>');
                }
            },
            error: function() {
                $('#galleryGrid').html('<div class="no-content"><i class="fas fa-exclamation-circle"></i><p>Failed to load gallery images.</p></div>');
            }
        });
    }

    function loadCategoryCounts() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { action: 'get_category_counts' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const counts = response.data;
                    $('#count-all').text(counts.all || 0);
                    $('#count-academics').text(counts.academics || 0);
                    $('#count-events').text(counts.events || 0);
                    $('#count-facilities').text(counts.facilities || 0);
                    $('#count-extracurricular').text(counts.extracurricular || 0);
                    $('#count-campus').text(counts.campus || 0);
                }
            }
        });
    }

    function filterAndDisplayImages() {
        // Filter images by category
        if (state.currentCategory === 'all') {
            state.filteredImages = state.allImages;
        } else {
            state.filteredImages = state.allImages.filter(img => img.category === state.currentCategory);
        }

        // Calculate pagination
        const start = (state.currentPage - 1) * state.itemsPerPage;
        const end = start + state.itemsPerPage;
        const pageImages = state.filteredImages.slice(start, end);

        displayImages(pageImages);
        updatePagination();
    }

    function displayImages(images) {
        if (images.length === 0) {
            $('#galleryGrid').html('<div class="no-content"><i class="fas fa-images"></i><p>No images in this category.</p></div>');
            return;
        }

        let html = '';
        images.forEach((image, index) => {
            const globalIndex = (state.currentPage - 1) * state.itemsPerPage + index;
            html += `
                <div class="gallery-item" onclick="openLightbox(${globalIndex})">
                    <img src="${IMG_URL}${image.image_url}" alt="${image.title}" 
                         onerror="this.src='https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=800&q=80'">
                    <span class="item-category">${getCategoryLabel(image.category)}</span>
                    <div class="item-details">
                        <h3>${image.title}</h3>
                        <p>${image.description || ''}</p>
                    </div>
                </div>
            `;
        });
        
        $('#galleryGrid').html(html);
    }

    function getCategoryLabel(category) {
        const labels = {
            'academics': 'Academics',
            'events': 'Events',
            'facilities': 'Facilities',
            'extracurricular': 'Extracurricular',
            'campus': 'Campus Life'
        };
        return labels[category] || 'General';
    }

    function updatePagination() {
        const totalPages = Math.ceil(state.filteredImages.length / state.itemsPerPage);
        
        if (totalPages <= 1) {
            $('#paginationContainer').empty();
            return;
        }
        
        let html = '<div class="pagination">';
        
        html += `<button class="page-btn ${state.currentPage === 1 ? 'disabled' : ''}" 
                        onclick="navigateToPage(${state.currentPage - 1})" 
                        ${state.currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </button>`;
        
        for (let i = 1; i <= Math.min(totalPages, 5); i++) {
            html += `<button class="page-btn ${i === state.currentPage ? 'active' : ''}" 
                            onclick="navigateToPage(${i})">${i}</button>`;
        }
        
        if (totalPages > 5) {
            html += '<span class="page-dots">...</span>';
            html += `<button class="page-btn" onclick="navigateToPage(${totalPages})">${totalPages}</button>`;
        }
        
        html += `<button class="page-btn ${state.currentPage >= totalPages ? 'disabled' : ''}" 
                        onclick="navigateToPage(${state.currentPage + 1})"
                        ${state.currentPage >= totalPages ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>`;
        
        html += '</div>';
        $('#paginationContainer').html(html);
    }

    function navigateToPage(page) {
        const totalPages = Math.ceil(state.filteredImages.length / state.itemsPerPage);
        if (page < 1 || page > totalPages) return;
        
        state.currentPage = page;
        filterAndDisplayImages();
        $('html, body').animate({ scrollTop: 0 }, 500);
    }

    function setupLightbox() {
        $('#closeBtn').click(closeLightbox);
        $('#prevBtn').click(prevImage);
        $('#nextBtn').click(nextImage);
        
        $('#rotateRightBtn').click(() => {
            lightboxState.rotation += 90;
            applyTransforms();
        });
        
        $('#rotateLeftBtn').click(() => {
            lightboxState.rotation -= 90;
            applyTransforms();
        });
        
        $('#flipHorizontalBtn').click(() => {
            lightboxState.flipH = !lightboxState.flipH;
            applyTransforms();
        });
        
        $('#flipVerticalBtn').click(() => {
            lightboxState.flipV = !lightboxState.flipV;
            applyTransforms();
        });
        
        $('#zoomBtn').click(() => {
            lightboxState.isZoomed = !lightboxState.isZoomed;
            applyTransforms();
        });
        
        $('#playBtn').click(toggleSlideshow);
        $('#fullscreenBtn').click(toggleFullscreen);
        $('#downloadBtn').click(downloadImage);
        $('#shareBtn').click(shareImage);
        
        $('#lightbox').click(function(e) {
            if (e.target === this) closeLightbox();
        });
    }

    function openLightbox(index) {
        lightboxState.currentIndex = index;
        $('#lightbox').addClass('active');
        $('body').css('overflow', 'hidden');
        showImage(index);
        createThumbnails();
    }

    function closeLightbox() {
        $('#lightbox').removeClass('active');
        $('body').css('overflow', '');
        stopSlideshow();
        resetTransforms();
    }

    function showImage(index) {
        const images = state.filteredImages;
        if (index < 0 || index >= images.length) return;
        
        lightboxState.currentIndex = index;
        const image = images[index];
        
        $('#lightboxImage').attr('src', IMG_URL + image.image_url);
        $('#imageCounter').text(`${index + 1} / ${images.length}`);
        $('#imageInfo').html(`<strong>${image.title}</strong><br>${image.description || ''}`);
        
        $('.thumbnail').removeClass('active');
        $(`.thumbnail[data-index="${index}"]`).addClass('active');
        
        const activeThumbnail = document.querySelector(`.thumbnail[data-index="${index}"]`);
        if (activeThumbnail) {
            activeThumbnail.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
        
        resetTransforms();
    }

    function createThumbnails() {
        let html = '';
        state.filteredImages.forEach((image, index) => {
            html += `
                <div class="thumbnail ${index === lightboxState.currentIndex ? 'active' : ''}" 
                     data-index="${index}" onclick="showImage(${index})">
                    <img src="${IMG_URL}${image.thumbnail_url || image.image_url}" alt="${image.title}">
                </div>
            `;
        });
        $('#thumbnailsContainer').html(html);
    }

    function nextImage() {
        const newIndex = (lightboxState.currentIndex + 1) % state.filteredImages.length;
        showImage(newIndex);
    }

    function prevImage() {
        const newIndex = (lightboxState.currentIndex - 1 + state.filteredImages.length) % state.filteredImages.length;
        showImage(newIndex);
    }

    function resetTransforms() {
        lightboxState.rotation = 0;
        lightboxState.flipH = false;
        lightboxState.flipV = false;
        lightboxState.isZoomed = false;
        applyTransforms();
    }

    function applyTransforms() {
        const scaleX = lightboxState.flipH ? -1 : 1;
        const scaleY = lightboxState.flipV ? -1 : 1;
        const scale = lightboxState.isZoomed ? 2 : 1;
        $('#lightboxImage').css('transform', 
            `rotate(${lightboxState.rotation}deg) scaleX(${scaleX}) scaleY(${scaleY}) scale(${scale})`);
        $('#lightboxImage').toggleClass('zoomed', lightboxState.isZoomed);
    }

    function toggleSlideshow() {
        if (lightboxState.isPlaying) {
            stopSlideshow();
        } else {
            startSlideshow();
        }
    }

    function startSlideshow() {
        lightboxState.isPlaying = true;
        $('#playBtn').text('⸫');
        lightboxState.slideInterval = setInterval(nextImage, 3000);
    }

    function stopSlideshow() {
        lightboxState.isPlaying = false;
        $('#playBtn').text('▶');
        clearInterval(lightboxState.slideInterval);
    }

    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.getElementById('lightbox').requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }

    function downloadImage() {
        const image = state.filteredImages[lightboxState.currentIndex];
        const link = document.createElement('a');
        link.href = IMG_URL + image.image_url;
        link.download = image.title.replace(/\s/g, '_') + '.jpg';
        link.click();
    }

    function shareImage() {
        const image = state.filteredImages[lightboxState.currentIndex];
        if (navigator.share) {
            navigator.share({
                title: image.title,
                text: `Check out this photo: ${image.title}`,
                url: window.location.href
            });
        } else {
            alert('Sharing is not supported on this browser');
        }
    }
    </script>

</body>
</html>