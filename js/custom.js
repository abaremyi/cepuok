// ============================================
// MOBILE MENU TOGGLE
// ============================================
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

// ============================================
// NAVBAR SCROLL EFFECT
// ============================================
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

// ============================================
// MEGA MENU HOVER HANDLING - FIXED
// ============================================
function initMegaMenus() {
    // Initialize all mega menus to hidden state
    document.querySelectorAll('.mega-menu').forEach(menu => {
        menu.style.opacity = '0';
        menu.style.visibility = 'hidden';
        menu.style.transition = 'opacity 0.3s ease, visibility 0.3s ease';
    });
    
    // Add hover event listeners for desktop mega menus
    document.querySelectorAll('.nav-menu > li').forEach(menuItem => {
        const megaMenu = menuItem.querySelector('.mega-menu');
        
        if (megaMenu) {
            menuItem.addEventListener('mouseenter', () => {
                // Close all other mega menus first
                document.querySelectorAll('.mega-menu').forEach(otherMenu => {
                    if (otherMenu !== megaMenu) {
                        otherMenu.style.opacity = '0';
                        otherMenu.style.visibility = 'hidden';
                    }
                });
                
                // Show current mega menu
                megaMenu.style.opacity = '1';
                megaMenu.style.visibility = 'visible';
            });
            
            menuItem.addEventListener('mouseleave', (e) => {
                // Check if mouse is moving to the mega menu
                const relatedTarget = e.relatedTarget;
                if (!megaMenu.contains(relatedTarget)) {
                    megaMenu.style.opacity = '0';
                    megaMenu.style.visibility = 'hidden';
                }
            });
            
            // Also handle hover on the mega menu itself
            megaMenu.addEventListener('mouseenter', () => {
                megaMenu.style.opacity = '1';
                megaMenu.style.visibility = 'visible';
            });
            
            megaMenu.addEventListener('mouseleave', () => {
                megaMenu.style.opacity = '0';
                megaMenu.style.visibility = 'hidden';
            });
        }
    });
    
    // Handle click on navigation items to toggle mega menus on mobile
    document.querySelectorAll('.nav-menu > li > a').forEach(link => {
        link.addEventListener('click', (e) => {
            if (window.innerWidth <= 992) { // Mobile
                const parentLi = link.parentElement;
                const megaMenu = parentLi.querySelector('.mega-menu');
                
                if (megaMenu) {
                    e.preventDefault();
                    
                    // Toggle current mega menu
                    const isVisible = megaMenu.style.opacity === '1';
                    
                    // Close all other mega menus
                    document.querySelectorAll('.mega-menu').forEach(otherMenu => {
                        if (otherMenu !== megaMenu) {
                            otherMenu.style.opacity = '0';
                            otherMenu.style.visibility = 'hidden';
                        }
                    });
                    
                    // Toggle current
                    megaMenu.style.opacity = isVisible ? '0' : '1';
                    megaMenu.style.visibility = isVisible ? 'hidden' : 'visible';
                }
            }
        });
    });
}

// ============================================
// IMPROVED CLICK-OUTSIDE DETECTION FOR MEGA MENUS
// ============================================
function initClickOutsideDetection() {
    document.addEventListener('click', (e) => {
        const megaMenus = document.querySelectorAll('.mega-menu');
        const navItems = document.querySelectorAll('.nav-menu > li');
        let clickedInsideNav = false;
        
        // Check if click is inside navigation structure
        navItems.forEach(item => {
            if (item.contains(e.target)) {
                clickedInsideNav = true;
            }
        });
        
        // Check if click is directly on a mega menu
        megaMenus.forEach(menu => {
            if (menu.contains(e.target)) {
                clickedInsideNav = true;
            }
        });
        
        // Only close mega menus if clicked outside navigation
        if (!clickedInsideNav) {
            megaMenus.forEach(menu => {
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
            });
        }
    });
    
    // Close mega menus with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.mega-menu').forEach(menu => {
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
            });
        }
    });
}

// ============================================
// ENHANCED TOUCH SUPPORT FOR MOBILE
// ============================================
function initTouchSupport() {
    if ('ontouchstart' in window) {
        document.querySelectorAll('.mega-menu-column ul li.has-submenu').forEach(item => {
            item.addEventListener('touchstart', (e) => {
                e.stopPropagation(); // Prevent event from bubbling up
                
                // Close all other submenus first
                document.querySelectorAll('.submenu-right').forEach(submenu => {
                    if (submenu !== item.querySelector('.submenu-right')) {
                        submenu.style.opacity = '0';
                        submenu.style.visibility = 'hidden';
                    }
                });
                
                const submenu = item.querySelector('.submenu-right');
                const isVisible = submenu.style.opacity === '1';
                
                submenu.style.opacity = isVisible ? '0' : '1';
                submenu.style.visibility = isVisible ? 'hidden' : 'visible';
                
                // Also ensure parent mega menu stays visible
                const megaMenu = item.closest('.mega-menu');
                if (megaMenu) {
                    megaMenu.style.opacity = '1';
                    megaMenu.style.visibility = 'visible';
                }
            });
        });
    }
}

// ============================================
// SMOOTH SCROLLING
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
                
                // Close mobile menu if open
                if (window.innerWidth <= 992) {
                    const mobileMenu = document.getElementById('mobileMenu');
                    if (mobileMenu.classList.contains('active')) {
                        toggleMenu();
                    }
                }
                
                // Close mega menus
                document.querySelectorAll('.mega-menu').forEach(menu => {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                });
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

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

// ============================================
// WINDOW RESIZE HANDLER
// ============================================
window.addEventListener('resize', () => {
    if (window.innerWidth > 992) {
        const mobileMenu = document.getElementById('mobileMenu');
        const menuOverlay = document.getElementById('menuOverlay');
        const hamburger = document.querySelector('.hamburger');
        
        mobileMenu.classList.remove('active');
        menuOverlay.classList.remove('active');
        hamburger.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset all mobile dropdowns
        document.querySelectorAll('.mobile-dropdown-content').forEach(content => {
            content.classList.remove('active');
            content.previousElementSibling.querySelector('.fa-chevron-down').style.transform = 'rotate(0)';
            content.previousElementSibling.classList.remove('active');
        });
    } else {
        // On mobile, ensure mega menus are hidden
        document.querySelectorAll('.mega-menu').forEach(menu => {
            menu.style.opacity = '0';
            menu.style.visibility = 'hidden';
        });
    }
});

// ============================================
// PAGE LOAD ANIMATION
// ============================================
window.addEventListener('load', () => {
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s';
        document.body.style.opacity = '1';
    }, 100);
});

// ============================================
// ACTIVE MENU ITEM HIGHLIGHTING
// ============================================
function initActiveMenuHighlight() {
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
}

// ============================================
// PREVENT BODY SCROLL WHEN MOBILE MENU IS OPEN
// ============================================
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

// ============================================
// HERO CAROUSEL
// ============================================
function initHeroCarousel() {
    const slides = document.querySelectorAll('.hero-slide');
    const indicators = document.querySelectorAll('.carousel-indicator');
    
    if (slides.length === 0) return; // No carousel on this page
    
    let currentSlide = 0;
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

    // Add click events to indicators
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => goToSlide(index));
    });

    // Start autoplay when page loads
    autoPlay();

    // Pause autoplay when user hovers over carousel
    const heroSection = document.querySelector('.hero');
    if (heroSection) {
        heroSection.addEventListener('mouseenter', () => {
            clearInterval(autoPlayInterval);
        });

        heroSection.addEventListener('mouseleave', () => {
            autoPlay();
        });
    }
}

// ============================================
// STATISTICS COUNTER ANIMATION
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

// ============================================
// VIDEO MODAL
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
// ANIMATE ON SCROLL
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
// PERFORMANCE OPTIMIZATION - Lazy loading images
// ============================================
function initLazyLoading() {
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
}

// ============================================
// INITIALIZE ALL FUNCTIONS
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    // Initialize mega menu functionality FIRST
    initMegaMenus();
    initClickOutsideDetection();
    initTouchSupport();
    
    // Initialize other components
    initSmoothScroll();
    initActiveMenuHighlight();
    initHeroCarousel();
    initVideoModal();
    initScrollAnimations();
    initLazyLoading();
    
    // Initialize statistics observer
    const statisticsSection = document.querySelector('.statistics');
    if (statisticsSection) {
        statsObserver.observe(statisticsSection);
    }
    
    // Add loaded class to body
    document.body.classList.add('loaded');
});

// ============================================
// GLOBAL EXPORT (for debugging)
// ============================================
window.MCSCustom = {
    toggleMenu,
    toggleMobileDropdown,
    initMegaMenus,
    initSmoothScroll
};