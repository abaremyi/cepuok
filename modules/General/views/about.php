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
    $home = 'active';
    $services = 'off';
    $work = 'off';
    $about = 'off';
    $news = 'off';
    $contacts = 'off';
    ?>
    <!-- Navbar -->
    <?php include_once get_layout('navbar'); ?>

    <!-- Page Header -->
    <header class="page-x-header">
        <div class="container">
            <h1>About Mount Carmel School</h1>
            <p>Excellence in Education, Rooted in Faith</p>
            <div class="page-x-breadcrumb">
                <a href="/">Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>About Us</span>
            </div>
        </div>
    </header>

    <!-- Who We Are Section -->
    <section class="who-we-are tmp-section">
        <div class="container">
            <div class="w3l-heading">
                <h2 class="w3ls_head">Who We Are</h2>
            </div>
            <div class="who-content">
                <div class="who-text">
                    <h3>Building Tomorrow's Leaders</h3>
                    <p>Mount Carmel School is a nurturing bilingual institution founded in 2013 by Reverend Pastor
                        Jeanne D'Arc Uwanyiligira. We are dedicated to providing quality education that combines
                        academic excellence with spiritual growth.</p>
                    <p>Our commitment extends beyond academics. We nurture curiosity, character, and a lifelong love of
                        learning through comprehensive programs in academics, outdoor education, arts, sports,
                        leadership, and community service.</p>
                    <div class="highlight-box">
                        <h4><i class="fas fa-quote-left"></i> Our Commitment</h4>
                        <p>"We encourage students to go beyond their limits — guided by exceptional teachers, small
                            class sizes, and personalized support. Our goal is to bless Rwanda with God-fearing
                            citizens, highly skilled and generation transformers for God's glory."</p>
                    </div>
                </div>
                <div class="who-image">
                    <img src="<?= img_url('about/school-venue-1.jpg') ?>"
                        alt="Mount Carmel School Campus">
                </div>
            </div>
        </div>
    </section>

    <!-- NEW: Mission, Vision & Philosophy -->
    <section class="mvp-about-section tmp-section" id="mvp-about-section"> <!-- Changed from mvp-section -->
        <div class="container">
            <div class="w3l-heading">
                <h2 class="w3ls_head">Mission, Vision & Philosophy</h2>
            </div>
            <div class="mvp-about-container"> <!-- Changed from mvp-container -->
                <!-- Mission Card -->
                <div class="cause-about-card"> <!-- Changed from cause-card -->
                    <div class="bg-about-thumbnail" style="background-image: url('<?= img_url('about/about-1.jpg') ?>')"></div> <!-- Changed from bg-thumbnail -->
                    <div class="card-about-content"> <!-- Changed from card-content -->
                        <img src="<?= img_url('about/mission2.png') ?>" alt="Mission Icon"
                            class="card-about-icon"> <!-- Changed from card-icon -->
                        <h3>Our Mission</h3>
                        <p>To train children to honor GOD, develop their potential skills; achieve excellence in
                            academics, wisdom and character.</p>
                    </div>
                </div>
    
                <!-- Vision Card -->
                <div class="cause-about-card">
                    <div class="bg-about-thumbnail" style="background-image: url('<?= img_url('about/about-2.jpg') ?>')"></div>
                    <div class="card-about-content">
                        <img src="<?= img_url('about/vision.png') ?>" alt="Vision Icon"
                            class="card-about-icon">
                        <h3>Our Vision</h3>
                        <p>To bless Rwanda with GOD fearing citizens, highly skilled and generation transformers for
                            GOD'S glory.</p>
                    </div>
                </div>
    
                <!-- Philosophy Card -->
                <div class="cause-about-card">
                    <div class="bg-about-thumbnail" style="background-image: url('<?= img_url('about/about-3.jpg') ?>')"></div>
                    <div class="card-about-content">
                        <img src="<?= img_url('about/philosophy.png') ?>" alt="Philosophy Icon"
                            class="card-about-icon">
                        <h3>Our Philosophy</h3>
                        <p>We believe in holistic education that nurtures the mind, body, and spirit. Every child is
                            unique and capable of excellence when given proper guidance, support, and a nurturing
                            environment rooted in faith.</p>
                    </div>
                </div>

                <!-- Motto Card -->
                
                <div class="cause-about-card">
                    <div class="bg-about-thumbnail" style="background-image: url('<?= img_url('about/about-4.jpg') ?>')"></div>
                    <div class="card-about-content">
                        <img src="<?= img_url('about/motto.png') ?>" alt="Motto Icon"
                            class="card-about-icon">
                        <h3>Our Motto</h3>
                        <p>"In God We Hope Wisdom and Knowledge" <br><em>Job 12:13</em></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- NEW: Mission, Vision & Philosophy -->

    <!-- NEW: Core Values -->
    <section class="values-section tmp-section">
        <div class="container">
            <div class="w3l-heading">
                <h2 class="w3ls_head">Our Core Values</h2>
            </div>
            <div class="values-container">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4>Academic Excellence</h4>
                    <p>We maintain high academic standards and provide rigorous, engaging curriculum.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-hands-holding"></i>
                    </div>
                    <h4>Stewardship</h4>
                    <p>Responsible management of resources entrusted to us for God's glory.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-hammer"></i>
                    </div>
                    <h4>Hard Work & Unity</h4>
                    <p>Diligence and collaboration as foundations for success and growth.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <h4>Patriotism</h4>
                    <p>Love for our country and commitment to its development.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-cross"></i>
                    </div>
                    <h4>Discipleship</h4>
                    <p>Following Christ's teachings and making disciples of all nations.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h4>Wisdom</h4>
                    <p>Pursuing knowledge and understanding guided by God's word.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Integrity</h4>
                    <p>Moral uprightness and consistency in words and actions.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4>Love for All</h4>
                    <p>Showing Christ's love to everyone regardless of background.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- History Section -->
    <section class="history-section tmp-section">
        <div class="container">
            <div class="w3l-heading">
                <h2 class="w3ls_head">Our History</h2>
            </div>
            <div class="history-content">
                <div class="history-text">
                    <h3>A Legacy of Faith and Excellence</h3>
                    <p>Mount Carmel School was founded in 2013 by <strong>Reverend Pastor Jeanne D'Arc Uwanyiligira</strong>, who was inspired by a deep desire to promote quality education rooted in Christian values. Her vision was to create an institution where academic excellence meets spiritual growth, forming the foundation of wisdom.</p>
                    
                    <p>From its humble beginnings, the school has remained steadfast on its path of seeking knowledge based on the Holy Scriptures. Our commitment to this founding principle has consistently produced students who grow not only in posture and mind but also in character and faith.</p>
                    
                    <div class="history-highlight">
                        <h4><i class="fas fa-award"></i> Academic Milestones</h4>
                        <p>In the 2021-2022 academic year, Mount Carmel School achieved remarkable success in the National Primary Leaving Examinations (PLE), with all students passing with First and Second Division honors. We proudly celebrated our student who became the <strong>2nd best pupil at national level</strong> and the <strong>top student in Kigali City</strong>.</p>
                    </div>
                    
                    <p>Today, we continue to build on this legacy with a team of professional teachers qualified at regional (EAC) standards. Our bilingual curriculum, offered from Nursery through Primary Six, ensures students master both English and French while receiving a comprehensive education that prepares them for global opportunities.</p>
                    
                    <blockquote class="history-quote">
                        <i class="fas fa-quote-left"></i>
                        <p>"We believe in the intersection of quality education and the fear of God—the beginning of true wisdom. Every child enrolled at Mount Carmel becomes a testament to effective growth in both mind and character."</p>
                        <cite>– Our Founding Principle</cite>
                    </blockquote>
                </div>
                <div class="history-image">
                    <div class="history-image-container">
                        <img src="<?= img_url('about/school-venue-2.jpg') ?>" 
                             alt="Mount Carmel School History">
                        <div class="history-image-caption">
                            <h4>Since 2013</h4>
                            <p>Building foundations for future generations</p>
                        </div>
                    </div>
                    
                    <div class="history-facts">
                        <div class="history-fact">
                            <i class="fas fa-calendar-check"></i>
                            <div>
                                <h5>Founded</h5>
                                <p>2013</p>
                            </div>
                        </div>
                        <div class="history-fact">
                            <i class="fas fa-user-graduate"></i>
                            <div>
                                <h5>Educational Sections</h5>
                                <p>Nursery to Upper Primary</p>
                            </div>
                        </div>
                        <div class="history-fact">
                            <i class="fas fa-language"></i>
                            <div>
                                <h5>Languages</h5>
                                <p>Bilingual (English & French)</p>
                            </div>
                        </div>
                        <div class="history-fact">
                            <i class="fas fa-trophy"></i>
                            <div>
                                <h5>National Recognition</h5>
                                <p>Top student in Kigali City</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Quick Links to Detailed Pages -->
    <section class="quick-links tmp-section" >
        <div class="container">
            <div class="w3l-heading">
                <h2 class="w3ls_head">Explore More</h2>
            </div>
            <div class="links-grid">
                <a href="<?= url('programs') ?>" class="link-card">
                    <div class="link-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Our Programs</h3>
                    <p>Explore our comprehensive educational programs from Nursery to Upper Primary</p>
                    <div class="link-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                <a href="<?= url('administration') ?>" class="link-card">
                    <div class="link-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>Administration Team</h3>
                    <p>Meet our dedicated leadership and teaching staff</p>
                    <div class="link-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                <a href="<?= url('gallery') ?>" class="link-card">
                    <div class="link-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <h3>Photo Gallery</h3>
                    <p>See our vibrant campus life and student activities</p>
                    <div class="link-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section tmp-section">
        <div class="container">
            <h2>Join the Mount Carmel Family</h2>
            <p>Discover how we can help your child reach their full potential</p>
            <div class="cta-buttons">
                <a href="/admissions" class="btn btn-primary">Start Application</a>
                <a href="/contact" class="btn btn-outline">Schedule a Visit</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include_once get_layout('footer'); ?>

    <!-- jQuery -->
    <?php include_once get_layout('scripts'); ?>

    <script>
        // Smooth scroll for internal links
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

        // Add animation on scroll
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

        // Animate elements on scroll
        document.querySelectorAll('.mvp-card, .value-card, .link-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });

        // Add active state to navigation
        window.addEventListener('scroll', () => {
            const scrollPos = window.scrollY;

            // Add shadow to header on scroll (if you have a fixed header)
            const header = document.querySelector('.page-x-header');
            if (scrollPos > 100) {
                header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            } else {
                header.style.boxShadow = 'none';
            }
        });
    </script>

</body>

</html>