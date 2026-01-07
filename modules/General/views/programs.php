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
    <header class="programs-page-header">
        <div class="container">
            <h1>Educational Programs</h1>
            <p>Discover our comprehensive academic offerings</p>
            <div class="programs-breadcrumb">
                <a href="/">Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>Programs</span>
            </div>
        </div>
    </header>

    <!-- Programs Section -->
    <section class="programs-section">
        <div class="programs-container">
            
            <!-- Programs Tabs Navigation -->
            <div class="programs-tabs-nav" id="programsTabsNav">
                <div class="tabs-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading programs...</p>
                </div>
            </div>

            <!-- Programs Content -->
            <div class="programs-content" id="programsContent">
                <div class="content-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading content...</p>
                </div>
            </div>

        </div>
    </section>

    <!-- Why Choose Section -->
    <section class="why-choose-section">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose Mount Carmel?</h2>
                <p>Excellence in education, character development, and holistic growth</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3>Qualified Teachers</h3>
                    <p>Experienced and dedicated educators committed to student success</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Comprehensive Curriculum</h3>
                    <p>Well-rounded education in both English and French languages</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-cross"></i>
                    </div>
                    <h3>Christian Values</h3>
                    <p>Faith-based education nurturing moral and spiritual growth</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Small Class Sizes</h3>
                    <p>Personalized attention for every student's unique needs</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Join Our Community?</h2>
                <p>Start your child's educational journey with Mount Carmel School</p>
                <div class="cta-buttons">
                    <a href="https://docs.google.com/forms/d/1wogDmRr4HUKh4uqx9QpbI96s0o_EEOBoAkr2zM2k7Qw/edit" target="_blank" class="btn-primary">
                        <i class="fas fa-edit"></i> Apply Now
                    </a>
                    <a href="<?= url('contact') ?>" class="btn-secondary">
                        <i class="fas fa-phone"></i> Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include_once get_layout('footer'); ?>
    <?php include_once get_layout('scripts'); ?>

    <script>
    const BASE_URL = '<?= url() ?>';
    const API_URL = BASE_URL + '/api/programs';
    
    let state = {
        programs: [],
        activeProgram: null,
        targetProgram: null
    };

    $(document).ready(function() {
        checkUrlHash();
        loadPrograms();
    });

    function checkUrlHash() {
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            state.targetProgram = hash.toLowerCase();
        }
    }

    function loadPrograms() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            data: { action: 'get_all_programs' },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    state.programs = response.data;
                    displayProgramTabs(response.data);
                    
                    if (state.targetProgram) {
                        const targetIndex = response.data.findIndex(p => 
                            p.slug === state.targetProgram || 
                            p.title.toLowerCase().replace(/\s+/g, '-') === state.targetProgram
                        );
                        if (targetIndex !== -1) {
                            setTimeout(() => {
                                activateTab(targetIndex, true);
                            }, 300);
                        } else {
                            activateTab(0);
                        }
                    } else {
                        activateTab(0);
                    }
                } else {
                    showError('No programs available');
                }
            },
            error: function(xhr, status, error) {
                console.error('Programs API Error:', error);
                showError('Failed to load programs');
            }
        });
    }

    function displayProgramTabs(programs) {
        let tabsHtml = '';
        
        programs.forEach((program, index) => {
            const slug = program.slug || program.title.toLowerCase().replace(/\s+/g, '-');
            tabsHtml += `
                <button class="program-tab" data-index="${index}" data-slug="${slug}">
                    <div class="tab-icon">
                        <i class="${program.icon_class || 'fas fa-book'}"></i>
                    </div>
                    <div class="tab-info">
                        <h3>${program.title}</h3>
                        <p>${program.subtitle || ''}</p>
                    </div>
                </button>
            `;
        });
        
        $('#programsTabsNav').html(tabsHtml);
        
        $('.program-tab').click(function() {
            const index = $(this).data('index');
            activateTab(index, true);
        });
    }

    function activateTab(index, smooth = false) {
        const program = state.programs[index];
        if (!program) return;
        
        $('.program-tab').removeClass('active');
        $(`.program-tab[data-index="${index}"]`).addClass('active');
        
        state.activeProgram = program;
        
        if (smooth) {
            $('#programsContent').fadeOut(200, function() {
                displayProgramContent(program);
                $('#programsContent').fadeIn(400);
                
                $('html, body').animate({
                    scrollTop: $('#programsContent').offset().top - 100
                }, 600, 'easeInOutCubic');
            });
        } else {
            displayProgramContent(program);
        }
        
        const slug = program.slug || program.title.toLowerCase().replace(/\s+/g, '-');
        history.replaceState(null, null, `#${slug}`);
    }

    function displayProgramContent(program) {
        const imageUrl = program.image_url || 'programs/default.jpg';
        
        const html = `
            <div class="program-detail">
                <div class="program-image-wrapper">
                    <img src="<?= img_url('${imageUrl}') ?>" 
                         alt="${program.title}"
                         onerror="this.src='https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1200&q=80'"
                         class="program-image">
                    <div class="program-badge">
                        <i class="${program.icon_class || 'fas fa-book'}"></i>
                    </div>
                </div>
                <div class="program-info">
                    <div class="program-header">
                        <span class="program-label">TRAINING SPACES</span>
                        <h2 class="program-title">${program.title}</h2>
                        ${program.subtitle ? `<h3 class="program-subtitle">${program.subtitle}</h3>` : ''}
                    </div>
                    <div class="program-description">
                        <p>${program.description}</p>
                    </div>
                    <div class="program-features">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Qualified and experienced teachers</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Modern learning facilities</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Small class sizes for personalized attention</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Comprehensive curriculum</span>
                        </div>
                    </div>
                    <div class="program-actions">
                        <a href="https://docs.google.com/forms/d/1wogDmRr4HUKh4uqx9QpbI96s0o_EEOBoAkr2zM2k7Qw/edit" 
                           target="_blank" 
                           class="btn-view-details">
                            <i class="fas fa-edit"></i> Apply Now
                        </a>
                        <a href="<?= url('contact') ?>" class="btn-contact">
                            <i class="fas fa-phone"></i> Contact Us
                        </a>
                    </div>
                </div>
            </div>
        `;
        
        $('#programsContent').html(html);
    }

    function showError(message) {
        $('#programsTabsNav').html(`
            <div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>${message}</p>
            </div>
        `);
        $('#programsContent').html(`
            <div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>${message}</p>
            </div>
        `);
    }

    // Smooth scroll easing
    $.easing.easeInOutCubic = function(x, t, b, c, d) {
        if ((t/=d/2) < 1) return c/2*t*t*t + b;
        return c/2*((t-=2)*t*t + 2) + b;
    };
    </script>

    <style>
    /* Programs Page Header */
    .programs-page-header {
        /* background: linear-gradient(135deg, #00796B 0%, #004D40 100%); */
        background: linear-gradient(135deg, rgba(0, 121, 107, 0.9), rgba(26, 58, 82, 0.9)),
        url('<?= img_url("hero-programs.jpg") ?>');
        color: white;
        padding: 120px 0 80px;
        text-align: center;
        position: relative;
        background-size: cover;
        overflow: hidden;
    }

    .programs-page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect fill="rgba(255,255,255,0.05)" width="50" height="50"/></svg>');
        opacity: 0.1;
    }

    .programs-page-header .container {
        position: relative;
        z-index: 1;
    }

    .programs-page-header h1 {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        animation: fadeInDown 0.8s ease;
    }

    .programs-page-header p {
        font-size: 1.3rem;
        opacity: 0.95;
        margin-bottom: 2rem;
        animation: fadeInUp 0.8s ease 0.2s both;
    }

    .programs-breadcrumb {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.8rem;
        font-size: 1rem;
        animation: fadeIn 0.8s ease 0.4s both;
    }

    .programs-breadcrumb a {
        color: white;
        text-decoration: none;
        transition: opacity 0.3s;
    }

    .programs-breadcrumb a:hover {
        opacity: 0.8;
    }

    /* Programs Section */
    .programs-section {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .programs-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Programs Tabs Navigation */
    .programs-tabs-nav {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 3rem;
        overflow-x: auto;
        padding: 10px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .program-tab {
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

    .program-tab:hover {
        border-color: #00796B;
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 121, 107, 0.15);
    }

    .program-tab.active {
        background: linear-gradient(135deg, #00796B 0%, #004D40 100%);
        border-color: #00796B;
        color: white;
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 121, 107, 0.3);
    }

    .tab-icon {
        width: 60px;
        height: 60px;
        background: rgba(0, 121, 107, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #00796B;
        transition: all 0.3s;
    }

    .program-tab.active .tab-icon {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .tab-info h3 {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.3rem;
        color: #2c3e50;
        transition: color 0.3s;
    }

    .program-tab.active .tab-info h3 {
        color: white;
    }

    .tab-info p {
        font-size: 0.95rem;
        color: #7f8c8d;
        margin: 0;
        transition: color 0.3s;
    }

    .program-tab.active .tab-info p {
        color: rgba(255, 255, 255, 0.9);
    }

    /* Programs Content */
    .programs-content {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .program-detail {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        padding: 3rem;
    }

    .program-image-wrapper {
        position: relative;
        border-radius: 15px;
        overflow: hidden;
        height: 500px;
    }

    .program-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .program-image-wrapper:hover .program-image {
        transform: scale(1.05);
    }

    .program-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 70px;
        height: 70px;
        background: rgba(0, 121, 107, 0.95);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }

    .program-info {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .program-label {
        color: #00796B;
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 0.8rem;
        display: block;
    }

    .program-title {
        font-size: 2.8rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }

    .program-subtitle {
        font-size: 1.5rem;
        color: #00796B;
        font-weight: 500;
        margin-bottom: 1.5rem;
    }

    .program-description {
        font-size: 1.1rem;
        color: #555;
        line-height: 1.8;
        margin-bottom: 2rem;
    }

    .program-features {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 2.5rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 1.05rem;
        color: #2c3e50;
    }

    .feature-item i {
        color: #00796B;
        font-size: 1.2rem;
    }

    .program-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-view-details,
    .btn-contact {
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.05rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.8rem;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .btn-view-details {
        background: linear-gradient(135deg, #00796B 0%, #004D40 100%);
        color: white;
        box-shadow: 0 5px 20px rgba(0, 121, 107, 0.3);
    }

    .btn-view-details:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0, 121, 107, 0.4);
    }

    .btn-contact {
        background: white;
        color: #00796B;
        border: 2px solid #00796B;
    }

    .btn-contact:hover {
        background: #00796B;
        color: white;
        transform: translateY(-3px);
    }

    /* Why Choose Section */
    .why-choose-section {
        padding: 80px 0;
        background: white;
    }

    .section-header {
        text-align: center;
        margin-bottom: 4rem;
    }

    .section-header h2 {
        font-size: 2.8rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1rem;
    }

    .section-header p {
        font-size: 1.2rem;
        color: #7f8c8d;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .feature-card {
        background: white;
        padding: 2.5rem;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.4s;
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 121, 107, 0.15);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #00796B 0%, #004D40 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        color: white;
    }

    .feature-card h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1rem;
    }

    .feature-card p {
        font-size: 1.05rem;
        color: #7f8c8d;
        line-height: 1.6;
    }

    /* CTA Section */
    .cta-section {
        padding: 80px 0;
        background: linear-gradient(135deg, #00796B 0%, #004D40 100%);
        color: white;
        text-align: center;
    }

    .cta-content h2 {
        font-size: 2.8rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .cta-content p {
        font-size: 1.3rem;
        margin-bottom: 2.5rem;
        opacity: 0.95;
    }

    .cta-buttons {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-primary,
    .btn-secondary {
        padding: 1.2rem 3rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.8rem;
        transition: all 0.3s;
    }

    .btn-primary {
        background: white;
        color: #00796B;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(255, 255, 255, 0.3);
    }

    .btn-secondary {
        background: transparent;
        color: white;
        border: 2px solid white;
    }

    .btn-secondary:hover {
        background: white;
        color: #00796B;
        transform: translateY(-3px);
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

    /* Responsive Design */
    @media (max-width: 992px) {
        .program-detail {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .program-image-wrapper {
            height: 400px;
        }

        .programs-tabs-nav {
            flex-direction: column;
        }

        .program-tab {
            min-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .programs-page-header h1 {
            font-size: 2.5rem;
        }

        .program-title {
            font-size: 2rem;
        }

        .program-subtitle {
            font-size: 1.2rem;
        }

        .section-header h2 {
            font-size: 2rem;
        }

        .cta-content h2 {
            font-size: 2rem;
        }

        .program-detail {
            padding: 2rem;
        }
    }
    </style>

</body>
</html>