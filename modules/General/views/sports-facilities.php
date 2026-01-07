<!DOCTYPE html>
<html lang="en">

<?php
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
include_once get_layout('header');
?>

<body>

    <?php
    $home = 'off';
    $services = 'off';
    $work = 'off';
    $about = 'off';
    $news = 'off';
    $contacts = 'off';
    ?>
    
    <?php include_once get_layout('navbar'); ?>

    <!-- Page Header -->
    <header class="facilities-page-header sports-header">
        <div class="container">
            <h1>Sports & Recreation</h1>
            <p>Comprehensive facilities promoting physical fitness, teamwork, and healthy competition</p>
            <div class="facilities-breadcrumb">
                <a href="/">Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <a href="/facilities">Facilities</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>Sports & Recreation</span>
            </div>
        </div>
    </header>

    <!-- Facilities Tabs Section -->
    <section class="facilities-section">
        <div class="facilities-container">
            
            <!-- Facilities Tabs Navigation -->
            <div class="facilities-tabs-nav" id="facilitiesTabsNav">
                <div class="tabs-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading sports facilities...</p>
                </div>
            </div>

            <!-- Facilities Content -->
            <div class="facilities-content" id="facilitiesContent">
                <div class="content-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading content...</p>
                </div>
            </div>

        </div>
    </section>

    <?php include_once get_layout('footer'); ?>
    <?php include_once get_layout('scripts'); ?>

    <script>
const BASE_URL = '<?= url() ?>';
const API_URL = BASE_URL + '/api/facilities';
const PAGE_TYPE = 'sports';

        let state = {
            facilities: [],
            activeFacility: null,
            activeImage: null,
            currentImageIndex: 0
        };

        $(document).ready(function() {
            checkUrlHash();
            loadFacilities();
        });

        function checkUrlHash() {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                state.targetFacility = hash;
            }
        }

        function loadFacilities() {
            $.ajax({
                url: API_URL,
                method: 'GET',
                data: { 
                    action: 'get_page_facilities',
                    page_type: PAGE_TYPE 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        state.facilities = response.data;
                        displayFacilityTabs(response.data);
                        
                        if (state.targetFacility) {
                            const targetFacility = response.data.find(f => 
                                f.slug === state.targetFacility
                            );
                            if (targetFacility) {
                                setTimeout(() => {
                                    activateTab(targetFacility.slug, true);
                                }, 300);
                            } else {
                                activateTab(response.data[0].slug);
                            }
                        } else {
                            activateTab(response.data[0].slug);
                        }
                    } else {
                        showError('No facilities information available');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Facilities API Error:', error);
                    showError('Failed to load facilities information');
                }
            });
        }

        function displayFacilityTabs(facilities) {
            let tabsHtml = '';
            
            facilities.forEach((facility, index) => {
                tabsHtml += `
                    <button class="facility-tab" data-slug="${facility.slug}">
                        <div class="tab-icon">
                            <i class="${facility.icon_class || 'fas fa-info-circle'}"></i>
                        </div>
                        <div class="tab-info">
                            <h3>${facility.title}</h3>
                            <p>${facility.subtitle || ''}</p>
                        </div>
                    </button>
                `;
            });
            
            $('#facilitiesTabsNav').html(tabsHtml);
            
            $('.facility-tab').click(function() {
                const slug = $(this).data('slug');
                activateTab(slug, true);
            });
        }

        function activateTab(slug, smooth = false) {
            const facility = state.facilities.find(f => f.slug === slug);
            if (!facility) return;
            
            $('.facility-tab').removeClass('active');
            $(`.facility-tab[data-slug="${slug}"]`).addClass('active');
            
            state.activeFacility = facility;
            state.activeImage = facility.images && facility.images.length > 0 ? facility.images[0] : null;
            state.currentImageIndex = 0;
            
            if (smooth) {
                $('#facilitiesContent').fadeOut(200, function() {
                    displayFacilityContent(facility);
                    $('#facilitiesContent').fadeIn(400);
                    
                    $('html, body').animate({
                        scrollTop: $('#facilitiesContent').offset().top - 100
                    }, 600, 'easeInOutCubic');
                });
            } else {
                displayFacilityContent(facility);
            }
            
            history.replaceState(null, null, `#${slug}`);
        }

        function displayFacilityContent(facility) {
            let html = `
                <div class="facility-detail">
                    <div class="facility-header">
                        <span class="facility-label">ACADEMIC FACILITY</span>
                        <h2 class="facility-title">${facility.title}</h2>
                        ${facility.subtitle ? `<h3 class="facility-subtitle">${facility.subtitle}</h3>` : ''}
                    </div>
                    
                    <div class="facility-main-content">
                        <div class="content-with-image">
                            <div class="facility-image-container">
                                ${facility.featured_image ? `
                                <div class="main-facility-image" id="mainFacilityImage">
                                    <img src="${BASE_URL}/img${facility.featured_image}" 
                                        alt="${facility.title}"
                                        class="featured-image">
                                    <div class="image-overlay">
                                        <button class="view-gallery-btn" onclick="openImageGallery()">
                                            <i class="fas fa-images"></i> View Gallery
                                        </button>
                                    </div>
                                </div>
                                ` : ''}
                                
                                ${facility.images && facility.images.length > 1 ? `
                                <div class="image-thumbnails">
                                    ${facility.images.slice(0, 4).map((image, index) => `
                                    <div class="thumbnail ${index === 0 ? 'active' : ''}" 
                                        onclick="changeMainImage('${image.image_url}', ${index})">
                                        <img src="${BASE_URL}/img${image.thumbnail_url || image.image_url}" 
                                            alt="${image.title}">
                                    </div>
                                    `).join('')}
                                    ${facility.images.length > 4 ? `
                                    <div class="thumbnail more-thumbs" onclick="openImageGallery()">
                                        <span>+${facility.images.length - 4}</span>
                                        <p>More</p>
                                    </div>
                                    ` : ''}
                                </div>
                                ` : ''}
                            </div>
                            
                            <div class="facility-description">
                                <div class="short-description">
                                    <p>${facility.short_description || ''}</p>
                                </div>
                                
                                ${facility.detailed_content ? `
                                <div class="detailed-content">
                                    ${facility.detailed_content}
                                </div>
                                ` : ''}
                                
                                ${facility.features && facility.features.length > 0 ? `
                                <div class="facility-features">
                                    <h4><i class="fas fa-star"></i> Key Features</h4>
                                    <div class="features-grid">
                                        ${facility.features.map(feature => `
                                        <div class="feature-item">
                                            ${feature.icon ? `<i class="${feature.icon}"></i>` : ''}
                                            <div class="feature-content">
                                                <h5>${feature.title}</h5>
                                                ${feature.description ? `<p>${feature.description}</p>` : ''}
                                            </div>
                                        </div>
                                        `).join('')}
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                
                ${facility.images && facility.images.length > 0 ? `
                <!-- Image Gallery Modal -->
                <div class="image-gallery-modal" id="imageGalleryModal">
                    <div class="gallery-modal-content">
                        <div class="gallery-header">
                            <h3>${facility.title} Gallery</h3>
                            <button class="close-gallery" onclick="closeImageGallery()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="gallery-main">
                            <div class="gallery-image-container">
                                <img src="" alt="" id="galleryMainImage">
                            </div>
                            <div class="gallery-controls">
                                <button class="gallery-nav prev" onclick="navigateGallery(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div class="image-counter">
                                    <span id="currentImage">1</span> / <span id="totalImages">${facility.images.length}</span>
                                </div>
                                <button class="gallery-nav next" onclick="navigateGallery(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            <div class="image-description">
                                <p id="currentImageDescription"></p>
                            </div>
                        </div>
                        <div class="gallery-thumbnails">
                            ${facility.images.map((image, index) => `
                            <div class="gallery-thumbnail ${index === 0 ? 'active' : ''}" 
                                onclick="selectGalleryImage(${index})">
                                <img src="${BASE_URL}/img${image.thumbnail_url || image.image_url}" 
                                    alt="${image.title}">
                            </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                ` : ''}
            `;
            
            $('#facilitiesContent').html(html);
            
            // Initialize gallery if exists
            if (facility.images && facility.images.length > 0) {
                updateGalleryDisplay();
            }
        }

        function changeMainImage(imageUrl, index) {
            $('#mainFacilityImage img').attr('src', BASE_URL + '/img' + imageUrl);
            $('.image-thumbnails .thumbnail').removeClass('active');
            $(`.image-thumbnails .thumbnail:nth-child(${index + 1})`).addClass('active');
            state.currentImageIndex = index;
            state.activeImage = state.activeFacility.images[index];
        }

        function openImageGallery() {
            $('#imageGalleryModal').fadeIn();
            $('body').addClass('modal-open');
        }

        function closeImageGallery() {
            $('#imageGalleryModal').fadeOut();
            $('body').removeClass('modal-open');
        }

        function selectGalleryImage(index) {
            state.currentImageIndex = index;
            updateGalleryDisplay();
        }

        function navigateGallery(direction) {
            const totalImages = state.activeFacility.images.length;
            let newIndex = state.currentImageIndex + direction;
            
            if (newIndex < 0) newIndex = totalImages - 1;
            if (newIndex >= totalImages) newIndex = 0;
            
            state.currentImageIndex = newIndex;
            updateGalleryDisplay();
        }

        function updateGalleryDisplay() {
            const image = state.activeFacility.images[state.currentImageIndex];
            $('#galleryMainImage').attr('src', BASE_URL + '/img' + image.image_url);
            $('#currentImage').text(state.currentImageIndex + 1);
            $('#totalImages').text(state.activeFacility.images.length);
            $('#currentImageDescription').text(image.description || image.title);
            
            $('.gallery-thumbnail').removeClass('active');
            $(`.gallery-thumbnail:nth-child(${state.currentImageIndex + 1})`).addClass('active');
        }

        function showError(message) {
            $('#facilitiesTabsNav').html(`
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>${message}</p>
                </div>
            `);
            $('#facilitiesContent').html(`
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>${message}</p>
                </div>
            `);
        }

        // Close gallery on ESC key
        $(document).keyup(function(e) {
            if (e.key === "Escape" && $('#imageGalleryModal').is(':visible')) {
                closeImageGallery();
            }
        });

        // Smooth scroll easing
        $.easing.easeInOutCubic = function(x, t, b, c, d) {
            if ((t/=d/2) < 1) return c/2*t*t*t + b;
            return c/2*((t-=2)*t*t + 2) + b;
        };
    </script>

    <style>
    /* Facilities Page Header */
    .facilities-page-header {
        background: linear-gradient(135deg, rgba(0, 121, 107, 0.9), rgba(26, 58, 82, 0.9)),
                    url('<?= img_url("hero-facilities.jpg") ?>');
        color: white;
        padding: 120px 0 80px;
        text-align: center;
        position: relative;
        background-size: cover;
        overflow: hidden;
    }

    .academic-header {
        background: linear-gradient(135deg, rgba(41, 128, 185, 0.9), rgba(26, 82, 118, 0.9)),
                    url('<?= img_url("academic-hero.jpg") ?>');
    }

    .sports-header {
        background: linear-gradient(135deg, rgba(0, 121, 107, 0.9), rgba(26, 58, 82, 0.9)),
                    url('<?= img_url("sports-hero.jpg") ?>');
        background-size: cover;
    }

    .services-header {
        background: linear-gradient(135deg, rgba(0, 87, 121, 0.9), rgba(32, 66, 128, 0.9)),
                    url('<?= img_url("services-hero.jpg") ?>');
    }

    .facilities-page-header .container {
        position: relative;
        z-index: 1;
    }

    .facilities-page-header h1 {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        animation: fadeInDown 0.8s ease;
    }

    .facilities-page-header p {
        font-size: 1.3rem;
        opacity: 0.95;
        margin-bottom: 2rem;
        animation: fadeInUp 0.8s ease 0.2s both;
    }

    .facilities-breadcrumb {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.8rem;
        font-size: 1rem;
        animation: fadeIn 0.8s ease 0.4s both;
    }

    .facilities-breadcrumb a {
        color: white;
        text-decoration: none;
        transition: opacity 0.3s;
    }

    .facilities-breadcrumb a:hover {
        opacity: 0.8;
    }

    /* Facilities Section */
    .facilities-section {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .facilities-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Facilities Tabs Navigation */
    .facilities-tabs-nav {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 3rem;
        overflow-x: auto;
        padding: 10px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .facility-tab {
        flex: 1;
        min-width: 250px;
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 1.8rem;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: left;
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }

    .facility-tab:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .academic-header .facility-tab:hover {
        border-color: #2980b9;
        box-shadow: 0 10px 30px rgba(41, 128, 185, 0.15);
    }

    .sports-header .facility-tab:hover {
        border-color: #27ae60;
        box-shadow: 0 10px 30px rgba(39, 174, 96, 0.15);
    }

    .services-header .facility-tab:hover {
        border-color: #9b59b6;
        box-shadow: 0 10px 30px rgba(155, 89, 182, 0.15);
    }

    .facility-tab.active {
        border-color: #2c3e50;
        /* color: white; */
        transform: translateY(-5px);
    }

    .academic-header .facility-tab.active {
        background: linear-gradient(135deg, #2980b9 0%, #1a5276 100%);
        border-color: #2980b9;
        box-shadow: 0 10px 30px rgba(41, 128, 185, 0.3);
    }

    .sports-header .facility-tab.active {
        background: linear-gradient(135deg, #27ae60 0%, #15653d 100%);
        border-color: #2c3e50
        box-shadow: 0 10px 30px rgba(39, 174, 96, 0.3);
    }

    .services-header .facility-tab.active {
        background: linear-gradient(135deg, #9b59b6 0%, #5b2c83 100%);
        border-color: #9b59b6;
        box-shadow: 0 10px 30px rgba(155, 89, 182, 0.3);
    }

    .tab-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        transition: all 0.3s;
    }

    .academic-header .tab-icon {
        background: rgba(41, 128, 185, 0.1);
        color: #2980b9;
    }

    .sports-header .tab-icon {
        background: rgba(39, 174, 96, 0.1);
        color: #27ae60;
    }

    .services-header .tab-icon {
        background: rgba(155, 89, 182, 0.1);
        color: #9b59b6;
    }

    .facility-tab.active .tab-icon {
        background: rgba(255, 255, 255, 0.2);
        color: #2c3e50;
    }

    .tab-info h3 {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.3rem;
        color: #2c3e50;
        transition: color 0.3s;
    }

    .facility-tab.active .tab-info h3 {
        color: #2c3e50;
    }

    .tab-info p {
        font-size: 0.95rem;
        color: #7f8c8d;
        margin: 0;
        transition: color 0.3s;
    }

    .facility-tab.active .tab-info p {
        color: rgba(44, 62, 80, 0.9);
    }

    /* Facilities Content */
    .facilities-content {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .facility-detail {
        padding: 3rem;
    }

    .facility-header {
        margin-bottom: 2.5rem;
        text-align: center;
    }

    .facility-label {
        color: #00796B;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 0.8rem;
        display: block;
        text-align: center;
        letter-spacing: 15px;
    }

    .academic-header ~ .facilities-section .facility-label {
        color: #2980b9;
    }

    .sports-header ~ .facilities-section .facility-label {
        color: #27ae60;
    }

    .services-header ~ .facilities-section .facility-label {
        color: #9b59b6;
    }

    .facility-title {
        font-size: 2.8rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }

    .facility-subtitle {
        font-size: 1.2rem;
        color: #00796B;
        font-weight: 500;
        margin-bottom: 1.5rem;
        text-align: center;
        letter-spacing: 2px;
    }

    .academic-header ~ .facilities-section .facility-subtitle {
        color: #2980b9;
    }

    .sports-header ~ .facilities-section .facility-subtitle {
        color: #27ae60;
    }

    .services-header ~ .facilities-section .facility-subtitle {
        color: #9b59b6;
    }

    /* Main Content Layout */
    .facility-main-content {
        margin-top: 2rem;
    }

    .content-with-image {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: start;
    }

    .facility-image-container {
        position: sticky;
        top: 20px;
    }

    .main-facility-image {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .main-facility-image img {
        width: 100%;
        height: 400px;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .main-facility-image:hover img {
        transform: scale(1.05);
    }

    .image-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        padding: 2rem;
        display: flex;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .main-facility-image:hover .image-overlay {
        opacity: 1;
    }

    .view-gallery-btn {
        background: white;
        color: #2c3e50;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
    }

    .view-gallery-btn:hover {
        background: #00796B;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 121, 107, 0.3);
    }

    /* Image Thumbnails */
    .image-thumbnails {
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }

    .thumbnail {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s;
    }

    .thumbnail:hover, .thumbnail.active {
        border-color: #00796B;
        transform: translateY(-2px);
    }

    .thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .more-thumbs {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border: 2px dashed #ddd;
    }

    .more-thumbs span {
        font-size: 1.5rem;
        font-weight: bold;
        color: #00796B;
    }

    .more-thumbs p {
        margin: 0;
        font-size: 0.8rem;
        color: #7f8c8d;
    }

    /* Facility Description */
    .facility-description {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .short-description {
        font-size: 1.2rem;
        line-height: 1.8;
        color: #555;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #00796B;
    }

    .detailed-content {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #555;
    }

    .detailed-content h3, 
    .detailed-content h4 {
        color: #2c3e50;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }

    .detailed-content ul, 
    .detailed-content ol {
        margin: 1rem 0 1rem 2rem;
        padding: 0;
    }

    .detailed-content li {
        margin-bottom: 0.8rem;
        line-height: 1.6;
    }

    /* Features Section */
    .facility-features {
        margin-top: 2rem;
    }

    .facility-features h4 {
        font-size: 1.5rem;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .feature-item {
        display: flex;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        transition: transform 0.3s;
    }

    .feature-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .feature-item i {
        font-size: 2rem;
        color: #00796B;
        margin-top: 0.5rem;
    }

    .feature-content h5 {
        font-size: 1.2rem;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .feature-content p {
        color: #7f8c8d;
        line-height: 1.6;
        margin: 0;
    }

    /* Image Gallery Modal */
    .image-gallery-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 1000;
        padding: 20px;
    }

    .modal-open {
        overflow: hidden;
    }

    .gallery-modal-content {
        max-width: 1200px;
        margin: 0 auto;
        height: 100%;
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 10px;
        overflow: hidden;
    }

    .gallery-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background: #2c3e50;
        color: white;
    }

    .gallery-header h3 {
        margin: 0;
        font-size: 1.5rem;
    }

    .close-gallery {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.5rem;
    }

    .gallery-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 2rem;
    }

    .gallery-image-container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    #galleryMainImage {
        max-width: 100%;
        max-height: 500px;
        object-fit: contain;
    }

    .gallery-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2rem;
        margin: 1.5rem 0;
    }

    .gallery-nav {
        background: #00796B;
        color: white;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .gallery-nav:hover {
        background: #004D40;
        transform: scale(1.1);
    }

    .image-counter {
        font-size: 1.2rem;
        color: #2c3e50;
        font-weight: 600;
    }

    .image-description {
        text-align: center;
        padding: 1rem;
        color: #555;
    }

    .gallery-thumbnails {
        display: flex;
        gap: 0.5rem;
        padding: 1rem;
        background: #f8f9fa;
        overflow-x: auto;
    }

    .gallery-thumbnail {
        width: 80px;
        height: 80px;
        border-radius: 5px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        flex-shrink: 0;
    }

    .gallery-thumbnail:hover,
    .gallery-thumbnail.active {
        border-color: #00796B;
    }

    .gallery-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .content-with-image {
            grid-template-columns: 1fr;
        }
        
        .facility-image-container {
            position: static;
        }
        
        .facilities-tabs-nav {
            flex-direction: column;
        }
        
        .facility-tab {
            min-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .facilities-page-header h1 {
            font-size: 2.5rem;
        }
        
        .facility-title {
            font-size: 2rem;
        }
        
        .facility-detail {
            padding: 2rem;
        }
        
        .features-grid {
            grid-template-columns: 1fr;
        }
        
        .gallery-modal-content {
            height: 90vh;
            margin: 5vh auto;
        }
    }

    @media (max-width: 480px) {
        .facilities-page-header {
            padding: 80px 0 60px;
        }
        
        .facilities-page-header h1 {
            font-size: 2rem;
        }
        
        .image-thumbnails .thumbnail {
            width: 60px;
            height: 60px;
        }
    }

    /* Loading States */
    .tabs-loading,
    .content-loading,
    .error-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem;
        text-align: center;
    }

    .tabs-loading i,
    .content-loading i,
    .error-state i {
        font-size: 3rem;
        color: #00796B;
        margin-bottom: 1rem;
    }

    .tabs-loading p,
    .content-loading p,
    .error-state p {
        font-size: 1.1rem;
        color: #7f8c8d;
    }

    .fa-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    </style>

</body>
</html>