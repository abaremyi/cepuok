function toggleMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            const menuOverlay = document.getElementById('menuOverlay');
            const hamburger = document.querySelector('.hamburger');
            
            mobileMenu.classList.toggle('active');
            menuOverlay.classList.toggle('active');
            hamburger.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        }

        // Mobile Dropdown Toggle
        function toggleMobileDropdown(element) {
            const dropdown = element.nextElementSibling;
            const icon = element.querySelector('.fa-chevron-down');
            
            // Close other dropdowns
            document.querySelectorAll('.mobile-dropdown-content').forEach(content => {
                if (content !== dropdown && content.classList.contains('active')) {
                    content.classList.remove('active');
                    content.previousElementSibling.querySelector('.fa-chevron-down').style.transform = 'rotate(0)';
                    content.previousElementSibling.classList.remove('active');
                }
            });
            
            dropdown.classList.toggle('active');
            element.classList.toggle('active');
        }

        // Navbar scroll effect
        let lastScroll = 0;
        const nav = document.getElementById('mainNav');
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });

        // Smooth Scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offset = 90;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.mobile-menu-items a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 992) {
                    setTimeout(() => {
                        toggleMenu();
                    }, 300);
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                const mobileMenu = document.getElementById('mobileMenu');
                const menuOverlay = document.getElementById('menuOverlay');
                const hamburger = document.querySelector('.hamburger');
                
                mobileMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
                hamburger.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Close mega menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.nav-menu')) {
                document.querySelectorAll('.mega-menu').forEach(menu => {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                });
            }
        });

        // Add loading animation
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.5s';
                document.body.style.opacity = '1';
            }, 100);
        });

        // Active menu item highlighting
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-menu a[href^="#"]');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.parentElement.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.parentElement.classList.add('active');
                }
            });
        });

        // Prevent body scroll when mobile menu is open
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    const mobileMenu = document.getElementById('mobileMenu');
                    if (mobileMenu.classList.contains('active')) {
                        document.body.style.position = 'fixed';
                        document.body.style.width = '100%';
                    } else {
                        document.body.style.position = '';
                        document.body.style.width = '';
                    }
                }
            });
        });

        observer.observe(document.getElementById('mobileMenu'), {
            attributes: true
        });

        // Add touch support for mobile mega menu
        if ('ontouchstart' in window) {
            document.querySelectorAll('.mega-menu-column ul li.has-submenu').forEach(item => {
                item.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    const submenu = item.querySelector('.submenu-right');
                    submenu.style.opacity = submenu.style.opacity === '1' ? '0' : '1';
                    submenu.style.visibility = submenu.style.visibility === 'visible' ? 'hidden' : 'visible';
                });
            });
        }

        // Hero Carousel
        let currentSlide = 0;
        const slides = document.querySelectorAll('.hero-slide');
        const indicators = document.querySelectorAll('.carousel-indicator');
        const totalSlides = slides.length;
        let autoPlayInterval;

        function showSlide(index) {
            // Remove active class from all slides and indicators
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));

            // Add active class to current slide and indicator
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
        }

        function changeSlide(direction) {
            currentSlide += direction;

            if (currentSlide >= totalSlides) {
                currentSlide = 0;
            } else if (currentSlide < 0) {
                currentSlide = totalSlides - 1;
            }

            showSlide(currentSlide);
            resetAutoPlay();
        }

        function goToSlide(index) {
            currentSlide = index;
            showSlide(currentSlide);
            resetAutoPlay();
        }

        function autoPlay() {
            autoPlayInterval = setInterval(() => {
                changeSlide(1);
            }, 5000); // Change slide every 5 seconds
        }

        function resetAutoPlay() {
            clearInterval(autoPlayInterval);
            autoPlay();
        }

        // Start autoplay when page loads
        autoPlay();

        // Pause autoplay when user hovers over carousel
        const heroSection = document.querySelector('.hero');
        heroSection.addEventListener('mouseenter', () => {
            clearInterval(autoPlayInterval);
        });

        heroSection.addEventListener('mouseleave', () => {
            autoPlay();
        });

// ============================================
// STATISTICS COUNTER ANIMATION - Smooth & Professional
// ============================================
function animateCounter() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.ceil(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        updateCounter();
    });
}

// Intersection Observer for counter animation
const observerOptions = {
    threshold: 0.3,
    rootMargin: '0px'
};

const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounter();
            statsObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

// Initialize statistics observer
document.addEventListener('DOMContentLoaded', () => {
    const statisticsSection = document.querySelector('.statistics');
    if (statisticsSection) {
        statsObserver.observe(statisticsSection);
    }
});

// ============================================
// VIDEO MODAL - Enhanced User Experience
// ============================================
function initVideoModal() {
    const videoContainer = document.querySelector('.video-container');
    if (!videoContainer) return;
    
    // Create modal structure
    const videoModal = document.createElement('div');
    videoModal.className = 'video-modal';
    videoModal.innerHTML = `
        <div class="modal-backdrop"></div>
        <div class="modal-content-wrapper">
            <button class="close-modal" aria-label="Close video">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-video-container">
                <iframe 
                    class="modal-iframe"
                    src="" 
                    title="Welcome to Mount Carmel School"
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    referrerpolicy="strict-origin-when-cross-origin" 
                    allowfullscreen>
                </iframe>
            </div>
        </div>
    `;
    
    // Add modal styles
    const modalStyles = document.createElement('style');
    modalStyles.textContent = `
        .video-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .video-modal.active {
            display: flex;
        }
        
        .modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .modal-content-wrapper {
            position: relative;
            width: 100%;
            max-width: 1000px;
            z-index: 2;
            animation: modalSlideIn 0.4s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .modal-video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
        }
        
        .modal-iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .close-modal {
            position: absolute;
            top: -60px;
            right: 0;
            width: 48px;
            height: 48px;
            background: var(--primary-orange, #ff6b35);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
        }
        
        .close-modal:hover {
            background: white;
            color: var(--primary-orange, #ff6b35);
            transform: rotate(90deg) scale(1.1);
        }
        
        @media (max-width: 768px) {
            .close-modal {
                top: -50px;
                width: 42px;
                height: 42px;
                font-size: 18px;
            }
            
            .modal-video-container {
                border-radius: 12px;
            }
        }
    `;
    
    document.head.appendChild(modalStyles);
    document.body.appendChild(videoModal);
    
    const closeBtn = videoModal.querySelector('.close-modal');
    const modalIframe = videoModal.querySelector('.modal-iframe');
    const backdrop = videoModal.querySelector('.modal-backdrop');
    const videoSrc = 'https://www.youtube.com/embed/H03wb1cZCSQ?si=2DbuGZ1I6otMILSY';
    
    // Open modal
    const openModal = () => {
        videoModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        modalIframe.src = videoSrc + '&autoplay=1';
    };
    
    // Close modal
    const closeModal = () => {
        videoModal.classList.remove('active');
        document.body.style.overflow = '';
        modalIframe.src = '';
    };
    
    // Event listeners
    videoContainer.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    
    // Close with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && videoModal.classList.contains('active')) {
            closeModal();
        }
    });
}

// ============================================
// SMOOTH SCROLL FOR ANCHOR LINKS
// ============================================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#' || href === '#!') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const offsetTop = target.offsetTop - 80; // Account for fixed header
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ============================================
// ANIMATE ON SCROLL - Fade in elements
// ============================================
function initScrollAnimations() {
    const animateElements = document.querySelectorAll('.news-card, .program-card, .why-item, .testimonial-card');
    
    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
                scrollObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        scrollObserver.observe(el);
    });
}

// ============================================
// INITIALIZE ALL FUNCTIONS
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    initVideoModal();
    initSmoothScroll();
    initScrollAnimations();
    
    // Add loading state removal
    document.body.classList.add('loaded');
});

// ============================================
// PERFORMANCE OPTIMIZATION - Lazy loading images
// ============================================
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
                imageObserver.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}