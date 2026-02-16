// Leadership Page JavaScript
// CEP UoK Website - Dynamic Leadership Display

// Global state
let currentYear = null;
let currentSession = 'day';
let leadershipData = {};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the page
    initLeadershipPage();
});

/**
 * Initialize leadership page
 */
function initLeadershipPage() {
    // Load years and generate tabs
    loadYears();
    
    // Initialize scroll animations
    initScrollAnimations();
    
    // Add event listeners
    setupEventListeners();
}

/**
 * Load available years from database
 */
function loadYears() {
    showLoading(true);
    
    // Fetch years from PHP backend
    fetch('api/get_leadership_years.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.years) {
                generateYearTabs(data.years);
                generateYearDropdown(data.years);
                
                // Load most recent year by default
                if (data.years.length > 0) {
                    selectYear(data.years[0]);
                }
            } else {
                // Use default years if API fails
                useDefaultYears();
            }
        })
        .catch(error => {
            console.error('Error loading years:', error);
            useDefaultYears();
        });
}

/**
 * Use default years if database is not available
 */
function useDefaultYears() {
    const years = [
        '2025-2026', '2024-2025', '2023-2024', '2022-2023', 
        '2021-2022', '2020-2021', '2019-2020', '2018-2019', 
        '2017-2018', '2016-2017'
    ];
    
    generateYearTabs(years);
    generateYearDropdown(years);
    selectYear(years[0]);
}

/**
 * Generate year tabs
 */
function generateYearTabs(years) {
    const tabsContainer = document.getElementById('yearTabs');
    if (!tabsContainer) return;
    
    tabsContainer.innerHTML = '';
    
    years.forEach(year => {
        const tab = document.createElement('button');
        tab.className = 'year-tab';
        tab.textContent = year;
        tab.setAttribute('data-year', year);
        
        tab.addEventListener('click', () => selectYear(year));
        
        tabsContainer.appendChild(tab);
    });
}

/**
 * Generate year dropdown for mobile
 */
function generateYearDropdown(years) {
    const dropdown = document.getElementById('yearDropdown');
    if (!dropdown) return;
    
    dropdown.innerHTML = '';
    
    years.forEach(year => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        dropdown.appendChild(option);
    });
    
    dropdown.addEventListener('change', (e) => {
        selectYear(e.target.value);
    });
}

/**
 * Select a year and load its data
 */
function selectYear(year) {
    currentYear = year;
    
    // Update active tab
    document.querySelectorAll('.year-tab').forEach(tab => {
        tab.classList.remove('active');
        if (tab.getAttribute('data-year') === year) {
            tab.classList.add('active');
        }
    });
    
    // Update dropdown
    const dropdown = document.getElementById('yearDropdown');
    if (dropdown) {
        dropdown.value = year;
    }
    
    // Load leadership data for this year
    loadLeadershipData(year);
}

/**
 * Load leadership data for a specific year
 */
function loadLeadershipData(year) {
    showLoading(true);
    
    // Fetch from PHP backend
    fetch(`api/get_leadership.php?year=${encodeURIComponent(year)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                leadershipData[year] = data;
                displayLeadershipData(data);
            } else {
                showEmptyState();
            }
        })
        .catch(error => {
            console.error('Error loading leadership data:', error);
            // Use sample data for demonstration
            loadSampleData(year);
        });
}

/**
 * Load sample data for demonstration (replace with actual database data)
 */
function loadSampleData(year) {
    // Determine if year has two sessions (2022 onwards)
    const yearNum = parseInt(year.split('-')[0]);
    const hasTwoSessions = yearNum >= 2022;
    
    const sampleData = {
        success: true,
        year: year,
        has_two_sessions: hasTwoSessions,
        day_session: generateSampleLeaders('Day', yearNum),
        weekend_session: hasTwoSessions ? generateSampleLeaders('Weekend', yearNum) : null,
        achievements: generateSampleAchievements(year)
    };
    
    leadershipData[year] = sampleData;
    displayLeadershipData(sampleData);
}

/**
 * Generate sample leaders (for demonstration)
 */
function generateSampleLeaders(session, year) {
    const positions = [
        'President',
        'Vice President',
        'Secretary',
        'Treasurer',
        'Evangelism Coordinator',
        'Choir Leader',
        'Social Affairs Coordinator',
        'Protocol Coordinator',
        'Media Team Leader'
    ];
    
    return positions.map((position, index) => ({
        id: `${session}-${index}`,
        name: `Leader Name ${index + 1}`,
        title: position,
        photo: `/img/leaders/${year}/${session.toLowerCase()}-${index + 1}.jpg`,
        session: session
    }));
}

/**
 * Generate sample achievements
 */
function generateSampleAchievements(year) {
    return [
        {
            title: 'Successful Annual Conference',
            description: 'Hosted a transformative annual conference with over 200 students in attendance, featuring powerful worship and impactful teaching.',
            icon: 'fas fa-users'
        },
        {
            title: 'Campus Evangelism Initiative',
            description: 'Reached over 500 students through systematic campus evangelism, leading to 50+ salvations and baptisms.',
            icon: 'fas fa-bullhorn'
        },
        {
            title: 'Community Service Projects',
            description: 'Conducted 5 major community service projects, impacting local communities around the university.',
            icon: 'fas fa-hands-helping'
        },
        {
            title: 'Leadership Training Program',
            description: 'Trained 30+ students in leadership skills, biblical knowledge, and ministry preparation.',
            icon: 'fas fa-chalkboard-teacher'
        }
    ];
}

/**
 * Display leadership data on the page
 */
function displayLeadershipData(data) {
    showLoading(false);
    
    // Update year header
    updateYearHeader(data);
    
    // Show/hide session toggle
    const sessionToggle = document.getElementById('sessionToggle');
    if (data.has_two_sessions) {
        sessionToggle.style.display = 'flex';
        currentSession = 'day'; // Reset to day session
        updateSessionButtons();
    } else {
        sessionToggle.style.display = 'none';
    }
    
    // Display leaders
    displayLeaders(data);
    
    // Display achievements
    displayAchievements(data.achievements);
    
    // Show content
    document.getElementById('committeeContent').style.display = 'block';
    document.getElementById('emptyState').style.display = 'none';
}

/**
 * Update year header information
 */
function updateYearHeader(data) {
    const yearTitle = document.getElementById('yearTitle');
    const yearDescription = document.getElementById('yearDescription');
    
    if (yearTitle) {
        yearTitle.textContent = `${data.year} Academic Year`;
    }
    
    if (yearDescription) {
        const yearNum = parseInt(data.year.split('-')[0]);
        const isCurrent = yearNum === 2025;
        yearDescription.textContent = isCurrent 
            ? 'Current leadership serving CEP UoK' 
            : `Leadership committee for the ${data.year} term`;
    }
}

/**
 * Display leaders based on current session
 */
function displayLeaders(data) {
    const daySessionContainer = document.getElementById('daySessionLeaders');
    const weekendSessionContainer = document.getElementById('weekendSessionLeaders');
    
    // Always show day session
    if (daySessionContainer) {
        daySessionContainer.style.display = 'block';
        const dayGrid = document.getElementById('dayLeadersGrid');
        if (dayGrid && data.day_session) {
            renderLeaders(dayGrid, data.day_session);
        }
    }
    
    // Show weekend session if it exists
    if (weekendSessionContainer) {
        if (data.has_two_sessions && data.weekend_session) {
            weekendSessionContainer.style.display = 'block';
            const weekendGrid = document.getElementById('weekendLeadersGrid');
            if (weekendGrid) {
                renderLeaders(weekendGrid, data.weekend_session);
            }
        } else {
            weekendSessionContainer.style.display = 'none';
        }
    }
    
    // If session toggle is visible, handle visibility
    if (data.has_two_sessions) {
        handleSessionVisibility();
    }
}

/**
 * Render leaders into a grid
 */
function renderLeaders(container, leaders) {
    container.innerHTML = '';
    
    leaders.forEach(leader => {
        const leaderCard = createLeaderCard(leader);
        container.appendChild(leaderCard);
    });
    
    // Animate cards
    animateLeaderCards(container);
}

/**
 * Create a leader card element
 */
function createLeaderCard(leader) {
    const card = document.createElement('div');
    card.className = 'leader-card';
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    
    card.innerHTML = `
        <div class="leader-photo">
            <img src="${leader.photo}" alt="${leader.name}" 
                 onerror="this.src='/img/leaders/placeholder.jpg'">
            <div class="leader-badge">${leader.session || 'Day'} Session</div>
        </div>
        <div class="leader-info">
            <div class="leader-title">${leader.title}</div>
            <h3 class="leader-name">${leader.name}</h3>
            ${leader.quote ? `<p class="leader-quote">"${leader.quote}"</p>` : ''}
        </div>
    `;
    
    return card;
}

/**
 * Animate leader cards on render
 */
function animateLeaderCards(container) {
    const cards = container.querySelectorAll('.leader-card');
    
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

/**
 * Display achievements
 */
function displayAchievements(achievements) {
    const achievementsGrid = document.getElementById('achievementsGrid');
    if (!achievementsGrid) return;
    
    achievementsGrid.innerHTML = '';
    
    if (!achievements || achievements.length === 0) {
        document.getElementById('achievementsContainer').style.display = 'none';
        return;
    }
    
    document.getElementById('achievementsContainer').style.display = 'block';
    
    achievements.forEach(achievement => {
        const card = document.createElement('div');
        card.className = 'achievement-card';
        card.style.opacity = '0';
        card.style.transform = 'translateX(-20px)';
        
        card.innerHTML = `
            <div class="achievement-icon">
                <i class="${achievement.icon || 'fas fa-star'}"></i>
            </div>
            <h4>${achievement.title}</h4>
            <p>${achievement.description}</p>
        `;
        
        achievementsGrid.appendChild(card);
    });
    
    // Animate achievement cards
    animateAchievements();
}

/**
 * Animate achievement cards
 */
function animateAchievements() {
    const cards = document.querySelectorAll('.achievement-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateX(0)';
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    cards.forEach(card => observer.observe(card));
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Session toggle buttons
    const sessionButtons = document.querySelectorAll('.session-btn');
    sessionButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            currentSession = btn.getAttribute('data-session');
            updateSessionButtons();
            handleSessionVisibility();
        });
    });
}

/**
 * Update session button active states
 */
function updateSessionButtons() {
    document.querySelectorAll('.session-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-session') === currentSession) {
            btn.classList.add('active');
        }
    });
}

/**
 * Handle session container visibility
 */
function handleSessionVisibility() {
    const dayContainer = document.getElementById('daySessionLeaders');
    const weekendContainer = document.getElementById('weekendSessionLeaders');
    
    if (currentSession === 'day') {
        dayContainer.style.display = 'block';
        weekendContainer.style.display = 'none';
    } else {
        dayContainer.style.display = 'none';
        weekendContainer.style.display = 'block';
    }
    
    // Scroll to leaders section
    const leadersContainer = document.getElementById('leadersContainer');
    if (leadersContainer) {
        leadersContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

/**
 * Show/hide loading state
 */
function showLoading(show) {
    const loadingState = document.getElementById('loadingState');
    const committeeContent = document.getElementById('committeeContent');
    
    if (show) {
        loadingState.style.display = 'block';
        committeeContent.style.display = 'none';
    } else {
        loadingState.style.display = 'none';
    }
}

/**
 * Show empty state
 */
function showEmptyState() {
    showLoading(false);
    document.getElementById('committeeContent').style.display = 'none';
    document.getElementById('emptyState').style.display = 'block';
}

/**
 * Initialize scroll animations
 */
function initScrollAnimations() {
    const sections = document.querySelectorAll('section:not(.leadership-hero)');
    
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
        section.style.opacity = '0';
        section.style.transform = 'translateY(30px)';
        section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(section);
    });
}

/**
 * Parallax effect for hero
 */
function initParallaxEffect() {
    const hero = document.querySelector('.leadership-hero');
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

// Export functions for external use if needed
window.CEPLeadership = {
    selectYear,
    loadLeadershipData,
    currentYear: () => currentYear,
    currentSession: () => currentSession
};