<!-- Top Bar -->
<div class="top-bar">
    <div class="top-container">
        <div class="top-bar-left">
            <span>Follow Us:</span>
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
        <div class="top-bar-right">
            <span><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</span>
            <span><i class="fas fa-envelope"></i> info@mountcarmel.ac.rw</span>
            <span><i class="fas fa-phone"></i> +250789121680</span>
        </div>
    </div>
</div>

<!-- Navigation -->
<nav id="mainNav">
        <div class="nav-container">
            <!-- Logo with School Crest -->
            <a href="<?= url() ?>" class="logo">
                <div class="logo-image">
                    <img src="<?= img_url('logo-only.png') ?>" alt="MCS Logo" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div class="logo-text">
                    <span class="main">Mount Carmel</span>
                    <span class="sub">A PRIVATE CHRISTIAN SCHOOL</span>
                </div>
            </a>

            <!-- Desktop Menu -->
            <ul class="nav-menu">
                <li><a href="<?= url() ?>">Home</a></li>
                
                <!-- About with Mega Menu -->
                <li>
                    <a href="#about">
                        About
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="mega-menu">
                        <div class="mega-menu-content">
                            <div class="mega-menu-column">
                                <h4><i class="fas fa-school"></i> School Info</h4>
                                <ul>
                                    <li><a href="<?= url('about') ?>"><i class="fas fa-circle"></i> Who We Are</a></li>
                                    <li><a href="<?= url('about#mvp-about-section') ?>"><i class="fas fa-circle"></i> Mission, Vision & Philosophy</a></li>
                                    <li><a href="<?= url('administration') ?>"><i class="fas fa-circle"></i> Administration</a></li>
                                </ul>
                            </div>
                            <div class="mega-menu-column">
                                <h4><i class="fas fa-images"></i> Media</h4>
                                <ul>
                                    <li><a href="<?= url('gallery') ?>"><i class="fas fa-circle"></i> Photo Gallery</a></li>
                                    <li><a href="<?= url('video-gallery') ?>"><i class="fas fa-circle"></i> Videos</a></li>
                                    <li><a href="<?= url('news') ?>"><i class="fas fa-circle"></i> News & Events</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Academics Program -->
                <li>
                    <a href="<?= url('programs') ?>">
                        Programs
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="mega-menu">
                        <div class="mega-menu-content">
                            <div class="mega-menu-column">
                                <h4><i class="fas fa-book-open"></i> Programs</h4>
                                <ul>
                                    <li><a href="<?= url('programs#nursery-school') ?>"><i class="fas fa-circle"></i> Nursery School</a></li>
                                    <li><a href="<?= url('programs#lower-primary') ?>"><i class="fas fa-circle"></i> Lower Primary</a></li>
                                    <li><a href="<?= url('programs#upper-primary') ?>"><i class="fas fa-circle"></i> Upper Primary</a></li>
                                </ul>
                            </div>
                            <div class="mega-menu-column">
                                <h4><i class="fas fa-book-open"></i> Curriculum</h4>
                                <ul>
                                    <li><a href="<?= url('programs#curriculum') ?>"><i class="fas fa-circle"></i> Our Curriculum</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Facilities with Sub-menu on Right -->
                <li>
                    <a href="#facilities">
                        Facilities
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="mega-menu">
                        <div class="mega-menu-content">
                            <div class="mega-menu-column">
                                <h4><i class="fas fa-building"></i> Academic</h4>
                                <ul>
                                    <li><a href="<?= url('academic-facilities#computer-lab') ?>"><i class="fas fa-circle"></i> Computer Lab</a></li>
                                    <li><a href="<?= url('academic-facilities#school-library') ?>"><i class="fas fa-circle"></i> School Library</a></li>
                                </ul>
                            </div>
                            <div class="mega-menu-column">
                                <h4><i class="fas fa-dumbbell"></i> Sports & Recreation</h4>
                                <ul>
                                    <li><a href="<?= url('sports-facilities#sports-activities') ?>"><i class="fas fa-circle"></i> Sports Activities</a></li>
                                    <li><a href="<?= url('sports-facilities#swimming-courses') ?>"><i class="fas fa-circle"></i> Swimming Courses</a></li>
                                </ul>
                            </div>
                            <div class="mega-menu-column">
                                <h4><i class="fas fa-utensils"></i> Services</h4>
                                <ul>
                                    <li><a href="<?= url('services-facilities#school-feeding') ?>"><i class="fas fa-circle"></i> School Feeding</a></li>
                                    <li><a href="<?= url('services-facilities#school-transport') ?>"><i class="fas fa-circle"></i> School Transport</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Admission -->
                <li>
                    <a href="<?= url('admission') ?>">
                        Admission
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="mega-menu">
                        <div class="mega-menu-content">
                            <div class="mega-menu-column">
                                <h4><i class="fas fa-book-open"></i> Admission</h4>
                                <ul>
                                    <li><a href="<?= url('admission#requirement') ?>"><i class="fas fa-circle"></i> Admission Requirements</a></li>
                                    <li><a href="<?= url('admission#fee-structure') ?>"><i class="fas fa-circle"></i> Fee Structure</a></li>
                                    <li><a href="https://docs.google.com/forms/d/1wogDmRr4HUKh4uqx9QpbI96s0o_EEOBoAkr2zM2k7Qw/edit" target="_blank"><i class="fas fa-circle"></i> Online Registration</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <li><a href="<?= url('contact') ?>">Contact</a></li>
            </ul>

            <a href="https://docs.google.com/forms/d/1wogDmRr4HUKh4uqx9QpbI96s0o_EEOBoAkr2zM2k7Qw/edit" target="_blank" class="apply-btn">
                <i class="fas fa-edit"></i> APPLY NOW
            </a>

            <div class="hamburger" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <div class="mobile-logo">
                <div class="mobile-logo-image">
                    <img src="<?= img_url('logo-only.png') ?>" alt="MCS Logo" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div class="mobile-logo-text">
                    <div class="main">Mount Carmel</div>
                    <div class="sub">A PRIVATE CHRISTIAN SCHOOL</div>
                </div>
            </div>
            <button class="close-btn" onclick="toggleMenu()">&times;</button>
        </div>
        <ul class="mobile-menu-items">
            <li><a href="<?= url() ?>" onclick="toggleMenu()">Home</a></li>
            
            <!-- About Dropdown -->
            <li>
                <div class="mobile-dropdown-toggle" onclick="toggleMobileDropdown(this)">
                    <span><i class="fas fa-info-circle"></i> About</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="mobile-dropdown-content">
                    <a href="<?= url('about') ?>" onclick="toggleMenu()"><i class="fas fa-school"></i> Who We Are</a>
                    <a href="<?= url('about#mvp-about-section') ?>" onclick="toggleMenu()"><i class="fas fa-bullseye"></i> Mission & Vision</a>
                    <a href="<?= url('administration') ?>" onclick="toggleMenu()"><i class="fas fa-users"></i> Administration</a>
                    <a href="<?= url('gallery') ?>" onclick="toggleMenu()"><i class="fas fa-images"></i> Photo Gallery</a>
                    <a href="<?= url('news') ?>" onclick="toggleMenu()"><i class="fas fa-newspaper"></i> News & Events</a>
                </div>
            </li>

            <!-- Academic Programs -->
            <li>
                <div class="mobile-dropdown-toggle" onclick="toggleMobileDropdown(this)">
                    <span><i class="fas fa-graduation-cap"></i> Programs</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="mobile-dropdown-content">
                    <a href="<?= url('programs#nursery-school') ?>" onclick="toggleMenu()"><i class="fas fa-baby"></i> Nursery School</a>
                    <a href="<?= url('programs#lower-primary') ?>" onclick="toggleMenu()"><i class="fas fa-child"></i> Lower Primary</a>
                    <a href="<?= url('programs#upper-primary') ?>" onclick="toggleMenu()"><i class="fas fa-graduation-cap"></i> Upper Primary</a>
                    <a href="<?= url('programs#curriculum') ?>" onclick="toggleMenu()"><i class="fas fa-book-open"></i> Curriculum</a>
                </div>
            </li>

            <!-- Facilities Dropdown -->
            <li>
                <div class="mobile-dropdown-toggle" onclick="toggleMobileDropdown(this)">
                    <span><i class="fas fa-building"></i> Facilities</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="mobile-dropdown-content">
                    <a href="<?= url('academic-facilities#computer-lab') ?>" onclick="toggleMenu()"><i class="fas fa-laptop"></i> Computer Lab</a>
                    <a href="<?= url('academic-facilities#school-library') ?>" onclick="toggleMenu()"><i class="fas fa-flask"></i> School Library</a>
                    <a href="<?= url('sports-facilities#sports-activities') ?>" onclick="toggleMenu()"><i class="fas fa-futbol"></i> Sports Activities</a>
                    <a href="<?= url('sports-facilities#swimming-courses') ?>" onclick="toggleMenu()"><i class="fas fa-swimming-pool"></i> Swimming Pool</a>
                    <a href="<?= url('services-facilities#school-transport') ?>" onclick="toggleMenu()"><i class="fas fa-bus"></i> Transport</a>
                    <a href="<?= url('services-facilities#school-feeding') ?>" onclick="toggleMenu()"><i class="fas fa-utensils"></i> School Feeding</a>
                </div>
            </li>

            <!-- Admission -->
            <li>
                <div class="mobile-dropdown-toggle" onclick="toggleMobileDropdown(this)">
                    <span><i class="fas fa-user-graduate"></i> Admission</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="mobile-dropdown-content">
                    <a href="<?= url('admission#requirement') ?>" onclick="toggleMenu()"><i class="fas fa-baby"></i> Admission Requirements</a>
                    <a href="<?= url('admission#fee-structure') ?>" onclick="toggleMenu()"><i class="fas fa-child"></i> Fee Structure</a>
                    <a href="https://docs.google.com/forms/d/1wogDmRr4HUKh4uqx9QpbI96s0o_EEOBoAkr2zM2k7Qw/edit" target="_blank" onclick="toggleMenu()"><i class="fas fa-graduation-cap"></i> Online Registration</a>
                </div>
            </li>

            <li><a href="<?= url('contact') ?>" onclick="toggleMenu()"><i class="fas fa-envelope"></i> Contact</a></li>
        </ul>

        <div class="mobile-apply-btn">
            <a href="https://docs.google.com/forms/d/1wogDmRr4HUKh4uqx9QpbI96s0o_EEOBoAkr2zM2k7Qw/edit" target="_blank" class="apply-btn" onclick="toggleMenu()">
                <i class="fas fa-edit"></i> APPLY NOW
            </a>
        </div>
    </div>
    <div class="menu-overlay" id="menuOverlay" onclick="toggleMenu()"></div>