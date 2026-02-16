// About Page JavaScript
// CEP UoK Website

document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for all internal links
    initSmoothScroll();
    
    // Animate elements on scroll
    initScrollAnimations();
    
    // Add parallax effect to hero
    initParallaxEffect();
    
    // Load data from backend (placeholder for PHP integration)
    loadAboutData();
});

/**
 * Initialize smooth scrolling for anchor links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Initialize scroll animations for sections
 */
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Add animation-ready class to sections
    const sections = document.querySelectorAll('section:not(.about-hero)');
    sections.forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(30px)';
        section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(section);
    });
}

/**
 * Add parallax effect to hero section
 */
function initParallaxEffect() {
    const hero = document.querySelector('.about-hero');
    if (!hero) return;
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallax = scrolled * 0.5;
        
        if (hero.querySelector('.hero-content')) {
            // hero.querySelector('.hero-content').style.transform = `translateY(${parallax}px)`;
        }
    });
}

/**
 * Load about page data from backend
 * This is a placeholder - replace with actual PHP/AJAX calls
 */
function loadAboutData() {
    // Example: Fetch data from PHP backend
    // fetch('api/get_about_content.php')
    //     .then(response => response.json())
    //     .then(data => {
    //         updateAboutContent(data);
    //     })
    //     .catch(error => console.error('Error loading about data:', error));
    
    console.log('About page loaded successfully');
}

/**
 * Update about content dynamically (for PHP integration)
 */
function updateAboutContent(data) {
    // Update mission
    if (data.mission) {
        const missionElement = document.querySelector('.mission-card .mission-list');
        if (missionElement) {
            missionElement.innerHTML = data.mission.map(item => `
                <li>
                    <i class="fas fa-check-circle"></i>
                    ${item}
                </li>
            `).join('');
        }
    }
    
    // Update vision
    if (data.vision) {
        const visionElement = document.querySelector('.vision-card p');
        if (visionElement) {
            visionElement.textContent = data.vision;
        }
    }
    
    // Update fellowship times
    if (data.fellowship_times) {
        updateFellowshipTimes(data.fellowship_times);
    }
}

/**
 * Update fellowship times dynamically
 */
function updateFellowshipTimes(times) {
    // Implementation for dynamic fellowship times
    // This can be connected to the recurring_events table
}

/**
 * Animate numbers (for stats if needed)
 */
function animateNumbers() {
    const numbers = document.querySelectorAll('[data-count]');
    
    numbers.forEach(number => {
        const target = parseInt(number.getAttribute('data-count'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                number.textContent = target;
                clearInterval(timer);
            } else {
                number.textContent = Math.floor(current);
            }
        }, 16);
    });
}

/**
 * Initialize hover effects for cards
 */
function initCardHoverEffects() {
    const cards = document.querySelectorAll('.mv-card, .time-card, .identity-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Initialize card hover effects
initCardHoverEffects();