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
    $videos = 'active';
    ?>
    <!-- Navbar -->
    <?php include_once get_layout('navbar'); ?>

    <!-- Page Header -->
    <header class="video-page-header">
        <div class="container">
            <h1>Video Gallery</h1>
            <p>Watch our school's memorable moments and achievements</p>
            <div class="video-breadcrumb">
                <a href="/">Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>Videos</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="video-gallery-container">
        
        <!-- Category Filter Bar -->
        <div class="category-filter-bar">
            <div class="filter-wrapper">
                <button class="filter-btn active" data-category="all">
                    <i class="fas fa-th"></i> All Videos
                </button>
                <button class="filter-btn" data-category="events">
                    <i class="fas fa-calendar-alt"></i> Events
                </button>
                <button class="filter-btn" data-category="academics">
                    <i class="fas fa-graduation-cap"></i> Academics
                </button>
                <button class="filter-btn" data-category="sports">
                    <i class="fas fa-trophy"></i> Sports
                </button>
                <button class="filter-btn" data-category="extracurricular">
                    <i class="fas fa-music"></i> Extracurricular
                </button>
                <button class="filter-btn" data-category="campus">
                    <i class="fas fa-building"></i> Campus
                </button>
            </div>
        </div>

        <!-- Video Stats Bar -->
        <div class="video-stats-bar">
            <div class="stat-item">
                <i class="fas fa-video"></i>
                <div>
                    <span class="stat-number" id="totalVideos">0</span>
                    <span class="stat-label">Total Videos</span>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-eye"></i>
                <div>
                    <span class="stat-number" id="totalViews">0</span>
                    <span class="stat-label">Total Views</span>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-clock"></i>
                <div>
                    <span class="stat-number" id="totalDuration">0</span>
                    <span class="stat-label">Hours of Content</span>
                </div>
            </div>
        </div>

        <!-- Videos Grid -->
        <div class="videos-grid" id="videosGrid">
            <div class="video-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading videos...</p>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" id="paginationContainer"></div>

    </div>

    <!-- Video Player Modal -->
    <div class="video-modal" id="videoModal">
        <div class="modal-backdrop" onclick="closeVideoModal()"></div>
        <div class="video-modal-content">
            <button class="modal-close" onclick="closeVideoModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="video-player-wrapper">
                <div id="videoPlayerContainer"></div>
            </div>
            <div class="video-modal-info">
                <h2 id="modalVideoTitle"></h2>
                <div class="video-meta">
                    <span class="video-category" id="modalVideoCategory"></span>
                    <span class="video-views" id="modalVideoViews">
                        <i class="fas fa-eye"></i> <span id="viewCount">0</span> views
                    </span>
                    <span class="video-duration" id="modalVideoDuration">
                        <i class="fas fa-clock"></i> <span id="durationText">0:00</span>
                    </span>
                </div>
                <p class="video-description" id="modalVideoDescription"></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once get_layout('footer'); ?>

    <!-- jQuery -->
    <?php include_once get_layout('scripts'); ?>

    <style>
        /* Video Gallery Styles */
        .video-page-header {
            background: linear-gradient(135deg, rgba(0, 121, 107, 0.9), rgba(26, 58, 82, 0.9)),
        url('<?= img_url("video-gallery.jpg") ?>');
            color: white;
            padding: 80px 0 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .video-page-header::before {
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

        .video-page-header h1 {
            font-size: 3rem;
            margin: 0 0 1rem;
            position: relative;
            z-index: 1;
        }

        .video-page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .video-breadcrumb {
            margin-top: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .video-breadcrumb a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .video-breadcrumb a:hover {
            opacity: 1;
        }

        .video-breadcrumb span {
            margin: 0 10px;
            opacity: 0.6;
        }

        .video-gallery-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Category Filter Bar */
        .category-filter-bar {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filter-wrapper {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-btn {
            padding: 12px 25px;
            background: #f5f5f5;
            border: 2px solid transparent;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.95rem;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn i {
            font-size: 1rem;
        }

        .filter-btn:hover {
            background: #e3f2fd;
            border-color: rgb(6, 119, 156);
            color: rgb(6, 119, 156);
        }

        .filter-btn.active {
            background: rgb(6, 119, 156);
            color: white;
            border-color: rgb(6, 119, 156);
        }

        /* Video Stats Bar */
        .video-stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-item {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-item i {
            font-size: 2.5rem;
            color: #1565C0;
        }

        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            display: block;
            font-size: 0.9rem;
            color: #666;
        }

        /* Videos Grid */
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .video-card {
            background: white;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .video-thumbnail {
            position: relative;
            width: 100%;
            height: 220px;
            overflow: hidden;
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .video-card:hover .video-thumbnail img {
            transform: scale(1.1);
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
            transition: background 0.3s;
        }

        .video-card:hover .video-play-overlay {
            background: rgba(0,0,0,0.6);
        }

        .play-icon {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #1565C0;
            transition: transform 0.3s;
        }

        .video-card:hover .play-icon {
            transform: scale(1.2);
        }

        .video-duration-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .video-card-body {
            padding: 20px;
        }

        .video-card-category {
            display: inline-block;
            background: #e3f2fd;
            color: #1565C0;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-bottom: 10px;
            text-transform: capitalize;
        }

        .video-card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin: 0 0 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-card-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .video-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
            font-size: 0.85rem;
            color: #999;
        }

        .video-views {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Video Modal */
        .video-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 10000;
            animation: fadeIn 0.3s;
        }

        .video-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.9);
        }

        .video-modal-content {
            position: relative;
            background: white;
            border-radius: 15px;
            max-width: 1200px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            z-index: 1;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 45px;
            height: 45px;
            background: rgba(0,0,0,0.7);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            transition: background 0.3s;
        }

        .modal-close:hover {
            background: rgba(0,0,0,0.9);
        }

        .video-player-wrapper {
            position: relative;
            width: 100%;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            background: #000;
            /* border-radius: 15px 15px 0 0; */
            overflow: hidden;
        }

        #videoPlayerContainer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        #videoPlayerContainer iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .video-modal-info {
            padding: 30px;
        }

        #modalVideoTitle {
            font-size: 1.8rem;
            color: #333;
            margin: 0 0 15px;
        }

        .video-meta {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .video-category {
            background: #e3f2fd;
            color: #1565C0;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            text-transform: capitalize;
        }

        .video-views, .video-duration {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.95rem;
        }

        .video-description {
            color: #666;
            line-height: 1.8;
            font-size: 1rem;
        }

        /* Loading and Empty States */
        .video-loading, .no-videos {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .video-loading i, .no-videos i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #1565C0;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
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
            background: #1565C0;
            color: white;
            border-color: #1565C0;
        }

        .page-btn.active {
            background: #1565C0;
            color: white;
            border-color: #1565C0;
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
        @media (max-width: 768px) {
            .video-page-header h1 {
                font-size: 2rem;
            }

            .videos-grid {
                grid-template-columns: 1fr;
            }

            .filter-wrapper {
                justify-content: flex-start;
            }

            .filter-btn {
                font-size: 0.85rem;
                padding: 10px 15px;
            }

            .video-stats-bar {
                grid-template-columns: 1fr;
            }

            .video-modal-content {
                max-height: 100vh;
                border-radius: 0;
            }

            #modalVideoTitle {
                font-size: 1.3rem;
            }
        }
    </style>

    <script>
    // Configuration
    const BASE_URL = '<?= url() ?>';
    const API_URL = BASE_URL + '/api/videos';
    console.log("API_URL", API_URL);
    
    // State management
    let state = {
        currentPage: 1,
        itemsPerPage: 9,
        currentCategory: 'all',
        totalItems: 0,
        allVideos: [],
        filteredVideos: []
    };

    let currentVideo = null;

    // Initialize
    $(document).ready(function() {
        initializePage();
        setupEventListeners();
    });

    function initializePage() {
        loadVideos();
        loadStats();
    }

    function setupEventListeners() {
        // Category filters
        $('.filter-btn').click(function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            state.currentCategory = $(this).data('category');
            state.currentPage = 1;
            filterAndDisplayVideos();
        });

        // Keyboard navigation
        $(document).keydown(function(e) {
            if ($('#videoModal').hasClass('active') && e.key === 'Escape') {
                closeVideoModal();
            }
        });
    }

    function loadVideos() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { action: 'get_videos' },
            dataType: 'json',
            beforeSend: function() {
                $('#videosGrid').html('<div class="video-loading"><i class="fas fa-spinner fa-spin"></i><p>Loading videos...</p></div>');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    state.allVideos = response.data;
                    state.totalItems = response.total;
                    filterAndDisplayVideos();
                } else {
                    $('#videosGrid').html('<div class="no-videos"><i class="fas fa-video-slash"></i><p>No videos available.</p></div>');
                }
            },
            error: function() {
                $('#videosGrid').html('<div class="no-videos"><i class="fas fa-exclamation-circle"></i><p>Failed to load videos.</p></div>');
            }
        });
    }

    function loadStats() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { action: 'get_stats' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#totalVideos').text(response.data.total_videos || 0);
                    $('#totalViews').text(formatNumber(response.data.total_views || 0));
                    $('#totalDuration').text(response.data.total_hours || 0);
                }
            }
        });
    }

    function filterAndDisplayVideos() {
        if (state.currentCategory === 'all') {
            state.filteredVideos = state.allVideos;
        } else {
            state.filteredVideos = state.allVideos.filter(video => video.category === state.currentCategory);
        }

        const start = (state.currentPage - 1) * state.itemsPerPage;
        const end = start + state.itemsPerPage;
        const pageVideos = state.filteredVideos.slice(start, end);

        displayVideos(pageVideos);
        updatePagination();
    }

    function displayVideos(videos) {
        if (videos.length === 0) {
            $('#videosGrid').html('<div class="no-videos"><i class="fas fa-video-slash"></i><p>No videos in this category.</p></div>');
            return;
        }

        let html = '';
        videos.forEach((video, index) => {
            const thumbnail = getThumbnailUrl(video);
            html += `
                <div class="video-card" onclick='openVideoModal(${JSON.stringify(video).replace(/'/g, "&apos;")})'>
                    <div class="video-thumbnail">
                        <img src="${thumbnail}" alt="${video.title}" 
                             onerror="this.src='https://via.placeholder.com/350x220/1565C0/ffffff?text=Video'">
                        <div class="video-play-overlay">
                            <div class="play-icon">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        ${video.duration ? `<span class="video-duration-badge">${video.duration}</span>` : ''}
                    </div>
                    <div class="video-card-body">
                        <span class="video-card-category">${getCategoryLabel(video.category)}</span>
                        <h3 class="video-card-title">${video.title}</h3>
                        <p class="video-card-description">${video.description || ''}</p>
                        <div class="video-card-footer">
                            <span class="video-views">
                                <i class="fas fa-eye"></i> ${formatNumber(video.views)} views
                            </span>
                            ${video.duration ? `<span><i class="fas fa-clock"></i> ${video.duration}</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#videosGrid').html(html);
    }

    function getThumbnailUrl(video) {
        if (video.thumbnail_url) {
            return video.thumbnail_url;
        }
        
        if (video.video_type === 'youtube') {
            const videoId = extractYouTubeId(video.video_url);
            return `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
        }
        
        return 'https://via.placeholder.com/350x220/1565C0/ffffff?text=Video';
    }

    function extractYouTubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    function getCategoryLabel(category) {
        const labels = {
            'events': 'Events',
            'academics': 'Academics',
            'sports': 'Sports',
            'extracurricular': 'Extracurricular',
            'campus': 'Campus'
        };
        return labels[category] || 'General';
    }

    function formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }

    function openVideoModal(video) {
        currentVideo = video;
        $('#videoModal').addClass('active');
        $('body').css('overflow', 'hidden');
        
        $('#modalVideoTitle').text(video.title);
        $('#modalVideoCategory').text(getCategoryLabel(video.category));
        $('#modalVideoDescription').text(video.description || '');
        $('#viewCount').text(formatNumber(video.views));
        $('#durationText').text(video.duration || 'N/A');
        
        loadVideoPlayer(video);
        
        // Increment views
        incrementViews(video.id);
    }

    function loadVideoPlayer(video) {
        let playerHTML = '';
        
        if (video.video_type === 'youtube') {
            const videoId = extractYouTubeId(video.video_url);
            playerHTML = `
                <iframe 
                    src="https://www.youtube.com/embed/${videoId}?autoplay=1" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            `;
        } else if (video.video_type === 'vimeo') {
            const vimeoId = video.video_url.split('/').pop();
            playerHTML = `
                <iframe 
                    src="https://player.vimeo.com/video/${vimeoId}?autoplay=1" 
                    allow="autoplay; fullscreen; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            `;
        } else {
            playerHTML = `
                <video controls autoplay style="width:100%;height:100%;">
                    <source src="${video.video_url}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            `;
        }
        
        $('#videoPlayerContainer').html(playerHTML);
    }

    function closeVideoModal() {
        $('#videoModal').removeClass('active');
        $('body').css('overflow', '');
        $('#videoPlayerContainer').html('');
        currentVideo = null;
    }

    function incrementViews(videoId) {
        $.ajax({
            url: API_URL,
            method: 'POST',
            data: { 
                action: 'increment_views',
                id: videoId
            },
            dataType: 'json'
        });
    }

    function updatePagination() {
        const totalPages = Math.ceil(state.filteredVideos.length / state.itemsPerPage);
        
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
        const totalPages = Math.ceil(state.filteredVideos.length / state.itemsPerPage);
        if (page < 1 || page > totalPages) return;
        
        state.currentPage = page;
        filterAndDisplayVideos();
        $('html, body').animate({ scrollTop: 0 }, 500);
    }
    </script>

</body>
</html>