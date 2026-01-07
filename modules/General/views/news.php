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
    $news = 'active';
    $contacts = 'off';
    ?>
    <!-- Navbar -->
    <?php include_once get_layout('navbar'); ?>

    <!-- Page Header -->
    <header class="news-page-header">
        <div class="container">
            <h1>News & Events</h1>
            <p>Stay updated with the latest happenings at Mount Carmel School</p>
            <div class="news-breadcrumb">
                <a href="/">Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>News & Events</span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="news-container">
        
        <!-- Sidebar -->
        <aside class="news-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="<?= img_url('logo-only.png') ?>" alt="Logo" onerror="this.style.display='none'">
                </div>
                <h1 class="sidebar-title">News & Events</h1>
                <p class="sidebar-subtitle">Stay Updated</p>
            </div>

            <!-- Recent Posts Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title">Recent Posts</h3>
                <div id="sidebarRecentPosts" class="sidebar-recent-posts">
                    <div class="widget-loading">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
            </div>

            <!-- Categories Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title">Categories</h3>
                <div class="sidebar-categories">
                    <button class="category-btn active" data-category="all">
                        <span>All Posts</span>
                    </button>
                    <button class="category-btn" data-category="news">
                        <span>School News</span>
                    </button>
                    <button class="category-btn" data-category="event">
                        <span>Events</span>
                    </button>
                    <button class="category-btn" data-category="announcement">
                        <span>Announcements</span>
                    </button>
                    <button class="category-btn" data-category="achievement">
                        <span>Achievements</span>
                    </button>
                </div>
            </div>

            <!-- About Widget -->
            <div class="sidebar-widget sidebar-about">
                <h3 class="widget-title">About</h3>
                <p>Stay connected with the latest news, events, and achievements from our school community. We share updates regularly to keep you informed.</p>
                <a href="#newsletter" class="subscribe-link">Subscribe <i class="fas fa-arrow-right"></i></a>
            </div>

            <!-- Social Links -->
            <div class="sidebar-social">
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" aria-label="RSS"><i class="fas fa-rss"></i></a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="news-main">
            
            <!-- Featured Post -->
            <article class="featured-post" id="featuredPost">
                <div class="post-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading featured post...</p>
                </div>
            </article>

            <!-- Posts Grid -->
            <div class="posts-grid" id="postsGrid">
                <div class="post-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading posts...</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-container" id="paginationContainer">
                <!-- Pagination will be loaded dynamically -->
            </div>

            <!-- Newsletter Section -->
            <section class="newsletter-section" id="newsletter">
                <div class="newsletter-content">
                    <h2>Stay Updated</h2>
                    <p>Subscribe to receive the latest news and updates directly in your inbox.</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <input type="email" placeholder="Enter your email" required>
                        <button type="submit" class="btn-subscribe">Subscribe</button>
                    </form>
                    <p class="newsletter-privacy">
                        <i class="fas fa-lock"></i> Your privacy matters. No spam, ever.
                    </p>
                </div>
            </section>

            <!-- Page Navigation -->
            <div class="page-navigation">
                <button class="nav-btn" id="prevPageBtn" disabled>
                    <i class="fas fa-arrow-left"></i> Previous Page
                </button>
                <button class="nav-btn" id="nextPageBtn">
                    Next Page <i class="fas fa-arrow-right"></i>
                </button>
            </div>

        </main>

    </div>

    <!-- Post Modal -->
    <div class="post-modal" id="postModal">
        <div class="modal-backdrop"></div>
        <article class="modal-content">
            <button class="modal-close" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-loading">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <div class="modal-body">
                <!-- Content loaded dynamically -->
            </div>
        </article>
    </div>

    <!-- Footer -->
    <?php include_once get_layout('footer'); ?>

    <!-- jQuery -->
    <?php include_once get_layout('scripts'); ?>

    <script>
    // Configuration
    const BASE_URL = '<?= url() ?>';
    const API_URL = BASE_URL + '/api/news';
    
    // State management
    let state = {
        currentPage: 1,
        itemsPerPage: 6,
        currentCategory: 'all',
        totalItems: 0,
        allPosts: [],
        recentPosts: []
    };

    // Initialize
    $(document).ready(function() {
        initializePage();
        setupEventListeners();
    });

    function initializePage() {
        loadFeaturedPost();
        loadPosts();
        loadRecentPosts();
    }

    function setupEventListeners() {
        // Category filters
        $('.category-btn').click(function() {
            $('.category-btn').removeClass('active');
            $(this).addClass('active');
            state.currentCategory = $(this).data('category');
            state.currentPage = 1;
            loadPosts();
        });

        // Newsletter form
        $('#newsletterForm').submit(function(e) {
            e.preventDefault();
            const email = $(this).find('input[type="email"]').val();
            if (validateEmail(email)) {
                showNotification('Thank you for subscribing!', 'success');
                $(this).trigger('reset');
            }
        });

        // Modal
        setupModal();

        // Page navigation
        $('#prevPageBtn').click(() => navigateToPage(state.currentPage - 1));
        $('#nextPageBtn').click(() => navigateToPage(state.currentPage + 1));
    }

    function loadFeaturedPost() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { action: 'get_featured', limit: 1 },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    displayFeaturedPost(response.data[0]);
                } else {
                    $('#featuredPost').html('<div class="no-content"><p>No featured post available.</p></div>');
                }
            },
            error: function() {
                $('#featuredPost').html('<div class="no-content"><p>Failed to load featured post.</p></div>');
            }
        });
    }

    function displayFeaturedPost(post) {
        const date = formatDate(post.published_date);
        const category = getCategoryLabel(post.category);
        
        const html = `
            <div class="featured-image">
                <img src="<?= img_url('${post.image_url || post.thumbnail_url || "news/default.jpg"}') ?>" 
                     alt="${post.title}"
                     onerror="this.src='https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&q=80'">
                <span class="featured-badge">${category}</span>
            </div>
            <div class="featured-content">
                <div class="post-meta">
                    <span class="post-date">${date}</span>
                    <span class="post-author">
                        <img src="<?= img_url('avatars/default.jpg') ?>" alt="${post.author || 'Admin'}" 
                             onerror="this.src='https://ui-avatars.com/api/?name=${post.author || 'Admin'}&background=00796B&color=fff'">
                        ${post.author || 'Admin'}
                    </span>
                </div>
                <h2 class="post-title">${post.title}</h2>
                <p class="post-excerpt">${post.excerpt || post.description.substring(0, 200) + '...'}</p>
                <button class="btn-read-more" onclick="openPostModal(${post.id})">
                    Continue Reading <i class="fas fa-arrow-right"></i>
                </button>
                <div class="post-stats">
                    <span><i class="fas fa-eye"></i> ${post.views || 0}</span>
                    <span><i class="fas fa-heart"></i> ${post.likes || 148}</span>
                </div>
            </div>
        `;
        
        $('#featuredPost').html(html);
    }

    function loadPosts() {
        const offset = (state.currentPage - 1) * state.itemsPerPage;
        
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: {
                action: 'get_news',
                limit: state.itemsPerPage,
                offset: offset,
                category: state.currentCategory === 'all' ? null : state.currentCategory
            },
            dataType: 'json',
            beforeSend: function() {
                $('#postsGrid').html('<div class="post-loading"><i class="fas fa-spinner fa-spin"></i><p>Loading posts...</p></div>');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    state.allPosts = response.data;
                    state.totalItems = response.total;
                    displayPosts(response.data);
                    updatePagination();
                } else {
                    $('#postsGrid').html('<div class="no-content"><i class="fas fa-inbox"></i><p>No posts found.</p></div>');
                }
            },
            error: function() {
                $('#postsGrid').html('<div class="no-content"><i class="fas fa-exclamation-circle"></i><p>Failed to load posts.</p></div>');
            }
        });
    }

    function displayPosts(posts) {
        let html = '';
        
        posts.forEach(post => {
            const date = formatDate(post.published_date);
            const category = getCategoryLabel(post.category);
            
            html += `
                <article class="post-card" data-category="${post.category}">
                    <div class="post-thumbnail">
                        <img src="<?= img_url('${post.image_url || post.thumbnail_url || "news/default.jpg"}') ?>" 
                             alt="${post.title}"
                             onerror="this.src='https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=800&q=80'">
                        <span class="post-category">${category}</span>
                    </div>
                    <div class="post-body">
                        <div class="post-meta">
                            <span class="post-date">${date}</span>
                            <span class="post-author">
                                <img src="https://ui-avatars.com/api/?name=${post.author || 'Admin'}&background=00796B&color=fff" alt="${post.author || 'Admin'}">
                                ${post.author || 'Admin'}
                            </span>
                        </div>
                        <h3 class="post-title">${post.title}</h3>
                        <p class="post-excerpt">${post.excerpt || post.description.substring(0, 120) + '...'}</p>
                        <button class="btn-read-more" onclick="openPostModal(${post.id})">
                            Continue Reading
                        </button>
                        <div class="post-footer">
                            <span><i class="fas fa-eye"></i> ${post.views || 0}</span>
                            <span><i class="fas fa-heart"></i> ${post.likes || 68}</span>
                        </div>
                    </div>
                </article>
            `;
        });
        
        $('#postsGrid').html(html);
    }

    function loadRecentPosts() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { action: 'get_latest', limit: 4 },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    displayRecentPosts(response.data);
                } else {
                    $('#sidebarRecentPosts').html('<p class="no-content-small">No recent posts.</p>');
                }
            },
            error: function() {
                $('#sidebarRecentPosts').html('<p class="no-content-small">Failed to load.</p>');
            }
        });
    }

    function displayRecentPosts(posts) {
        let html = '';
        
        posts.forEach(post => {
            const date = formatDate(post.published_date, 'short');
            
            html += `
                <div class="recent-post-item" onclick="openPostModal(${post.id})">
                    <div class="recent-post-thumb">
                        <img src="<?= img_url('${post.thumbnail_url || post.image_url || "news/default-thumb.jpg"}') ?>" 
                             alt="${post.title}"
                             onerror="this.src='https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=200&q=80'">
                    </div>
                    <div class="recent-post-info">
                        <h4>${post.title}</h4>
                        <span class="recent-post-date">${date}</span>
                    </div>
                </div>
            `;
        });
        
        $('#sidebarRecentPosts').html(html);
    }

    function updatePagination() {
        const totalPages = Math.ceil(state.totalItems / state.itemsPerPage);
        
        // Update page navigation buttons
        $('#prevPageBtn').prop('disabled', state.currentPage === 1);
        $('#nextPageBtn').prop('disabled', state.currentPage >= totalPages);
        
        // Build pagination
        if (totalPages <= 1) {
            $('#paginationContainer').empty();
            return;
        }
        
        let html = '<div class="pagination">';
        
        // Previous button
        html += `<button class="page-btn ${state.currentPage === 1 ? 'disabled' : ''}" 
                        onclick="navigateToPage(${state.currentPage - 1})" 
                        ${state.currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </button>`;
        
        // Page numbers
        for (let i = 1; i <= Math.min(totalPages, 5); i++) {
            html += `<button class="page-btn ${i === state.currentPage ? 'active' : ''}" 
                            onclick="navigateToPage(${i})">${i}</button>`;
        }
        
        if (totalPages > 5) {
            html += '<span class="page-dots">...</span>';
            html += `<button class="page-btn" onclick="navigateToPage(${totalPages})">${totalPages}</button>`;
        }
        
        // Next button
        html += `<button class="page-btn ${state.currentPage >= totalPages ? 'disabled' : ''}" 
                        onclick="navigateToPage(${state.currentPage + 1})"
                        ${state.currentPage >= totalPages ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>`;
        
        html += '</div>';
        $('#paginationContainer').html(html);
    }

    function navigateToPage(page) {
        const totalPages = Math.ceil(state.totalItems / state.itemsPerPage);
        if (page < 1 || page > totalPages) return;
        
        state.currentPage = page;
        loadPosts();
        
        // Smooth scroll to top of posts
        $('html, body').animate({
            scrollTop: $('#postsGrid').offset().top - 100
        }, 500);
    }

    function setupModal() {
        const modal = $('#postModal');
        
        $('.modal-close, .modal-backdrop').click(function() {
            closePostModal();
        });
        
        $(document).keydown(function(e) {
            if (e.key === 'Escape' && modal.hasClass('active')) {
                closePostModal();
            }
        });
    }

    function openPostModal(postId) {
        const modal = $('#postModal');
        modal.addClass('active');
        $('body').css('overflow', 'hidden');
        
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { action: 'get_news_by_id', id: postId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayPostModal(response.data);
                } else {
                    modal.find('.modal-body').html('<p class="error-message">Failed to load post.</p>');
                }
                modal.find('.modal-loading').removeClass('active');
            },
            error: function() {
                modal.find('.modal-body').html('<p class="error-message">Failed to load post.</p>');
                modal.find('.modal-loading').removeClass('active');
            }
        });
    }

    function displayPostModal(post) {
        const date = formatDate(post.published_date);
        const category = getCategoryLabel(post.category);
        
        const html = `
            <div class="modal-header-image">
                <img src="<?= img_url('${post.image_url || "news/default.jpg"}') ?>" alt="${post.title}"
                     onerror="this.src='https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&q=80'">
            </div>
            <div class="modal-article">
                <div class="modal-meta">
                    <span class="modal-category">${category}</span>
                    <span class="modal-date">${date}</span>
                </div>
                <h1 class="modal-title">${post.title}</h1>
                <div class="modal-author">
                    <img src="https://ui-avatars.com/api/?name=${post.author || 'Admin'}&background=00796B&color=fff" 
                         alt="${post.author || 'Admin'}">
                    <div>
                        <strong>${post.author || 'Admin'}</strong>
                        <span>Posted on ${date}</span>
                    </div>
                </div>
                <div class="modal-content-text">
                    ${post.description || post.excerpt || '<p>No content available.</p>'}
                </div>
                <div class="modal-stats">
                    <span><i class="fas fa-eye"></i> ${post.views || 0} views</span>
                    <span><i class="fas fa-heart"></i> ${post.likes || 148} likes</span>
                </div>
            </div>
        `;
        
        $('#postModal .modal-body').html(html);
    }

    function closePostModal() {
        $('#postModal').removeClass('active');
        $('body').css('overflow', '');
    }

    // Helper functions
    function formatDate(dateString, format = 'long') {
        const date = new Date(dateString);
        if (format === 'short') {
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }
        return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    }

    function getCategoryLabel(category) {
        const labels = {
            'news': 'School News',
            'event': 'Event',
            'announcement': 'Announcement',
            'achievement': 'Achievement'
        };
        return labels[category] || 'General';
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="notification notification-${type}">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(() => notification.addClass('show'), 10);
        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    function loadUpcomingEvents() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { 
                action: 'get_upcoming',
                limit: 3
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    upcomingEvents = response.data;
                    displayUpcomingEvents(upcomingEvents);
                } else {
                    displayNoUpcomingEvents();
                }
            },
            error: function(xhr, status, error) {
                console.error('Upcoming Events API Error:', error);
                displayNoUpcomingEvents();
            }
        });
    }

    function displayUpcomingEvents(events) {
        if (events.length === 0) {
            displayNoUpcomingEvents();
            return;
        }

        let html = '';
        events.forEach(function(event, index) {
            const date = new Date(event.published_date);
            const formattedDate = date.toLocaleDateString('en-US', { 
                weekday: 'long',
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            const time = date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });

            html += `
                <div class="event-timeline-item" data-index="${index}">
                    <div class="event-timeline-date">
                        <span class="event-date-day">${date.getDate()}</span>
                        <span class="event-date-month">${date.toLocaleString('en', { month: 'short' })}</span>
                    </div>
                    <div class="event-timeline-content">
                        <h3>${event.title}</h3>
                        <p>${event.excerpt || event.description.substring(0, 200) + '...'}</p>
                        <div class="event-timeline-meta">
                            <span><i class="fas fa-clock"></i> ${time}</span>
                            <span><i class="fas fa-map-marker-alt"></i> ${event.event_location || 'School Campus'}</span>
                        </div>
                        <a href="#" class="event-timeline-link" onclick="openNewsModal(${event.id}, event)">
                            View Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            `;
        });
        
        $('#upcomingEventsContainer').html(html);
        setupEventCardAnimations();
    }

    function displayNoUpcomingEvents() {
        const html = `
            <div class="no-events-message">
                <i class="fas fa-calendar-times"></i>
                <h3>No Upcoming Events</h3>
                <p>Check back soon for upcoming events at Mount Carmel School.</p>
            </div>
        `;
        $('#upcomingEventsContainer').html(html);
    }

    </script>

</body>
</html>