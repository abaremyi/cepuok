// History Page JavaScript
// CEP UoK Website

document.addEventListener('DOMContentLoaded', function() {
    // Initialize video section
    initVideoSection();
    
    // Animate timeline on scroll
    initTimelineAnimations();
    
    // Load history data from backend
    loadHistoryData();
    
    // Initialize scroll-triggered animations
    initScrollAnimations();
});

/**
 * Initialize video section with play button functionality
 */
function initVideoSection() {
    const videoOverlay = document.getElementById('videoOverlay');
    const playButton = document.getElementById('playButton');
    const videoFrame = document.getElementById('historyVideo');
    
    if (playButton && videoOverlay && videoFrame) {
        playButton.addEventListener('click', function() {
            // Hide overlay
            videoOverlay.style.display = 'none';
            
            // Auto-play video by adding autoplay parameter
            const videoSrc = videoFrame.getAttribute('src');
            if (videoSrc && !videoSrc.includes('autoplay')) {
                videoFrame.setAttribute('src', videoSrc + '?autoplay=1');
            }
        });
    }
}

/**
 * Load history data from backend
 * This connects to your database to fetch video URL and description
 */
function loadHistoryData() {
    // Example PHP endpoint call
    fetch('api/get_history_content.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateHistoryContent(data);
            }
        })
        .catch(error => {
            console.error('Error loading history data:', error);
            // Set default values if fetch fails
            setDefaultHistoryContent();
        });
}

/**
 * Update history page content with data from backend
 */
function updateHistoryContent(data) {
    // Update video URL
    if (data.video_url) {
        const videoFrame = document.getElementById('historyVideo');
        if (videoFrame) {
            videoFrame.setAttribute('src', data.video_url);
        }
    }
    
    // Update video description
    if (data.video_description) {
        const descriptionElement = document.getElementById('videoDescription');
        if (descriptionElement) {
            descriptionElement.textContent = data.video_description;
        }
    }
    
    // Update milestones if provided
    if (data.milestones) {
        updateMilestones(data.milestones);
    }
}

/**
 * Set default content if backend data is unavailable
 */
function setDefaultHistoryContent() {
    const videoFrame = document.getElementById('historyVideo');
    const descriptionElement = document.getElementById('videoDescription');
    
    // You can fetch this from page_content table
    if (videoFrame) {
        // Default video URL from database
        videoFrame.setAttribute('src', 'https://www.youtube.com/embed/DaGMZsmDKBU');
    }
    
    if (descriptionElement) {
        descriptionElement.textContent = 'Journey through the remarkable history of CEP UoK, from our humble beginnings to becoming a vibrant community of faith at the University of Kigali.';
    }
}

/**
 * Update milestones dynamically (if stored in database)
 */
function updateMilestones(milestones) {
    const timelineContainer = document.querySelector('.milestones-timeline');
    if (!timelineContainer) return;
    
    // Clear existing milestones
    timelineContainer.innerHTML = '';
    
    // Add new milestones
    milestones.forEach((milestone, index) => {
        const isLeft = index % 2 === 0;
        const milestoneHTML = `
            <div class="milestone-item ${isLeft ? 'left' : 'right'}">
                <div class="milestone-content">
                    <div class="milestone-year">${milestone.year}</div>
                    <h3>${milestone.title}</h3>
                    <p>${milestone.description}</p>
                    <div class="milestone-icon">
                        <i class="${milestone.icon || 'fas fa-star'}"></i>
                    </div>
                </div>
                <div class="timeline-dot ${milestone.is_current ? 'current' : ''}"></div>
            </div>
        `;
        timelineContainer.insertAdjacentHTML('beforeend', milestoneHTML);
    });
    
    // Re-initialize animations for new elements
    initTimelineAnimations();
}

/**
 * Initialize timeline scroll animations
 */
function initTimelineAnimations() {
    const milestoneItems = document.querySelectorAll('.milestone-item');
    
    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateX(0)';
            }
        });
    }, observerOptions);
    
    milestoneItems.forEach((item, index) => {
        // Set initial state
        item.style.opacity = '0';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        
        // Different animation direction based on left/right
        if (item.classList.contains('left')) {
            item.style.transform = 'translateX(-50px)';
        } else {
            item.style.transform = 'translateX(50px)';
        }
        
        // Observe the item
        observer.observe(item);
    });
}

/**
 * Initialize general scroll animations
 */
function initScrollAnimations() {
    const sections = document.querySelectorAll('section:not(.history-hero)');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    sections.forEach(section => {
        // Skip milestone section as it has its own animation
        if (!section.classList.contains('milestones-section')) {
            section.style.opacity = '0';
            section.style.transform = 'translateY(30px)';
            section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(section);
        }
    });
}

/**
 * Animate timeline dots on scroll
 */
function animateTimelineDots() {
    const dots = document.querySelectorAll('.timeline-dot');
    
    const observerOptions = {
        threshold: 0.5
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.transform = 'scale(1)';
                entry.target.style.opacity = '1';
            }
        });
    }, observerOptions);
    
    dots.forEach(dot => {
        dot.style.transform = 'scale(0)';
        dot.style.opacity = '0';
        dot.style.transition = 'transform 0.4s ease, opacity 0.4s ease';
        observer.observe(dot);
    });
}

// Initialize dot animations
animateTimelineDots();

/**
 * Add parallax effect to hero
 */
function initParallaxEffect() {
    const hero = document.querySelector('.history-hero');
    if (!hero) return;
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallax = scrolled * 0.4;
        
        if (hero.querySelector('.hero-content')) {
            hero.querySelector('.hero-content').style.transform = `translateY(${parallax}px)`;
        }
    });
}

initParallaxEffect();

/**
 * Legacy image gallery hover effects
 */
function initGalleryEffects() {
    const galleryImages = document.querySelectorAll('.legacy-img');
    
    galleryImages.forEach(img => {
        img.addEventListener('mouseenter', function() {
            const imgElement = this.querySelector('img');
            if (imgElement) {
                imgElement.style.transform = 'scale(1.15)';
            }
        });
        
        img.addEventListener('mouseleave', function() {
            const imgElement = this.querySelector('img');
            if (imgElement) {
                imgElement.style.transform = 'scale(1)';
            }
        });
    });
}

initGalleryEffects();