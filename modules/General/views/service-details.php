<!DOCTYPE html>
<html lang="en">

<?php include("../../../layouts/header.php");?>
<body>
    <!-- Preloader -->
    <div class="preloader-bg"></div>
    <div id="preloader">
        <div id="preloader-status">
            <div class="preloader-position loader"> <span></span> </div>
        </div>
    </div>
    
    <!-- Progress scroll totop -->
    <div class="progress-wrap cursor-pointer">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>
    
    <!-- Control Active Nav Link -->
     <?php 
        $home = 'off'; 
        $services = 'active'; 
        $work = 'off'; 
        $about = 'off'; 
        $news = 'off'; 
        $contacts = 'off'; 
     ?>
    
    <!-- Navbar -->
    <?php include("../../../layouts/navbar.php");?>

    <!-- Header Banner -->
    <section class="banner-header section-padding bg-img" data-overlay-dark="5" data-background="../../../img/services-3.jpg">
        <div class="v-middle">
            <div class="container">
                <div class="col-md-12">
                    <h6>Digital Transformation Services</h6>
                    <h1>
                        <?php 
                        $service = isset($_GET['service']) ? $_GET['service'] : 'software-development';
                        $serviceTitles = [
                            'software-development' => 'Software & Web Development',
                            'it-consultancy' => 'IT Consultancy & System Analysis',
                            'digital-marketing' => 'Digital Marketing & Creative Design',
                            'it-support' => 'IT Support & Maintenance',
                            'enterprise-data' => 'Enterprise Data Solutions',
                            'technology-partnership' => 'Technology Partnership',
                            'cloud-hosting' => 'Cloud & Data Management',
                            'ecommerce-solutions' => 'E-Commerce Solutions'
                        ];
                        echo $serviceTitles[$service] ?? 'Software & Web Development';
                        ?>
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <!-- Details -->
    <section class="service-details section-padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="row mb-60">
                        <div class="col-md-12">
                            <p class="mb-30">
                                Our <strong><?php echo $serviceTitles[$service] ?? 'Software & Web Development'; ?></strong> service is designed to drive your digital transformation journey, addressing specific business challenges while aligning with your strategic goals. At MUSHYA, we combine technical expertise with innovative thinking to deliver solutions that foster growth, efficiency, and sustainable progress.
                            </p>
                            <h3>Service Overview</h3>
                            <p class="mb-30">
                                We integrate industry best practices with cutting-edge technologies to create solutions that not only meet your current needs but also position your business for future growth. Our client-centric approach ensures we deeply understand your unique requirements and deliver tailored solutions that drive meaningful transformation.
                            </p>
                            <h3>Key Features & Benefits</h3>
                            <p class="mb-30">
                                Our <?php echo $serviceTitles[$service] ?? 'Software & Web Development'; ?> service delivers comprehensive value through scalable, secure, and innovative solutions designed to support your long-term business strategy and digital evolution.
                            </p>
                            <h3>Transformation Process</h3>
                            <p class="mb-30">
                                We follow a structured, agile implementation process that ensures efficient project execution and timely delivery. Our methodology emphasizes continuous communication, rigorous testing, and quality assurance to guarantee reliable, high-performing solutions.
                            </p>
                            <ul class="list-unstyled list mb-30">
                                <li>
                                    <div class="list-icon"> <span class="ti-check"></span> </div>
                                    <div class="list-text">
                                        <p><strong>Customized Solutions</strong> - Tailored to your specific business needs and transformation goals</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="list-icon"> <span class="ti-check"></span> </div>
                                    <div class="list-text">
                                        <p><strong>Expert Team</strong> - Seasoned professionals with diverse industry experience and technical expertise</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="list-icon"> <span class="ti-check"></span> </div>
                                    <div class="list-text">
                                        <p><strong>Comprehensive Support</strong> - Ongoing maintenance and optimization services for continuous improvement</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="list-icon"> <span class="ti-check"></span> </div>
                                    <div class="list-text">
                                        <p><strong>Scalable Architecture</strong> - Future-proof solutions designed for growth and adaptation</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="list-icon"> <span class="ti-check"></span> </div>
                                    <div class="list-text">
                                        <p><strong>Competitive Pricing</strong> - Flexible engagement models that align with your budget and objectives</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="list-icon"> <span class="ti-check"></span> </div>
                                    <div class="list-text">
                                        <p><strong>Innovation Focus</strong> - Cutting-edge solutions that drive digital renewal and business transformation</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- FAQs -->
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <h3 class="mb-4">Frequently Asked Questions</h3>
                            <ul class="accordion-box clearfix">
                                <li class="accordion block">
                                    <div class="acc-btn"><span class="count">1.</span> What is the typical timeline for this service?</div>
                                    <div class="acc-content">
                                        <div class="content">
                                            <div class="text">Project timelines vary based on scope and complexity. Typical web development projects take 4-8 weeks, while comprehensive software solutions may require 3-6 months. We provide detailed project roadmaps during the discovery phase, ensuring transparency and alignment with your transformation goals.</div>
                                        </div>
                                    </div>
                                </li>
                                <li class="accordion block">
                                    <div class="acc-btn"><span class="count">2.</span> What is your pricing model?</div>
                                    <div class="acc-content">
                                        <div class="content">
                                            <div class="text">We offer flexible pricing models including fixed-price projects, time and materials, and monthly retainers. After understanding your specific needs during our initial consultation, we'll recommend the most suitable approach and provide a detailed, transparent quote that aligns with your budget and expected outcomes.</div>
                                        </div>
                                    </div>
                                </li>
                                <li class="accordion block">
                                    <div class="acc-btn"><span class="count">3.</span> Do you provide ongoing support and maintenance?</div>
                                    <div class="acc-content">
                                        <div class="content">
                                            <div class="text">Absolutely. We offer comprehensive support and maintenance packages to ensure your systems continue to perform optimally. Our services include technical assistance, regular updates, security patches, performance monitoring, and proactive health checks to maintain system reliability and drive continuous improvement.</div>
                                        </div>
                                    </div>
                                </li>
                                <li class="accordion block">
                                    <div class="acc-btn"><span class="count">4.</span> Can you integrate with our existing systems?</div>
                                    <div class="acc-content">
                                        <div class="content">
                                            <div class="text">Yes, we have extensive experience integrating with diverse systems and platforms. We conduct thorough assessments of your current infrastructure to design solutions that seamlessly integrate with existing systems, ensuring minimal disruption while maximizing the value of your current technology investments.</div>
                                        </div>
                                    </div>
                                </li>
                                <li class="accordion block">
                                    <div class="acc-btn"><span class="count">5.</span> How do you ensure project quality and success?</div>
                                    <div class="acc-content">
                                        <div class="content">
                                            <div class="text">We employ rigorous quality assurance processes, including continuous testing, code reviews, and regular client feedback sessions. Our agile methodology ensures transparency and adaptability throughout the project lifecycle, while our experienced team follows industry best practices to deliver solutions that exceed expectations.</div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Sidebar -->
                <div class="col-lg-4 col-md-12">
                    <div class="sidebar-page">
                        <div class="title">
                            <h4>All Services</h4>
                        </div>
                        <div class="item">
                            <div class="features <?php echo $service == 'software-development' ? 'active' : ''; ?>">
                                <a href="service-details.php?service=software-development">
                                    <span><i class="ti-arrow-top-right"></i> Software & Web Development</span>
                                </a>
                            </div>
                            <div class="features <?php echo $service == 'it-consultancy' ? 'active' : ''; ?>">
                                <a href="service-details.php?service=it-consultancy">
                                    <span><i class="ti-arrow-top-right"></i> IT Consultancy & System Analysis</span>
                                </a>
                            </div>
                            <div class="features <?php echo $service == 'digital-marketing' ? 'active' : ''; ?>">
                                <a href="service-details.php?service=digital-marketing">
                                    <span><i class="ti-arrow-top-right"></i> Digital Marketing & Creative Design</span>
                                </a>
                            </div>
                            <div class="features <?php echo $service == 'it-support' ? 'active' : ''; ?>">
                                <a href="service-details.php?service=it-support">
                                    <span><i class="ti-arrow-top-right"></i> IT Support & Maintenance</span>
                                </a>
                            </div>
                            <div class="features <?php echo $service == 'enterprise-data' ? 'active' : ''; ?>">
                                <a href="service-details.php?service=enterprise-data">
                                    <span><i class="ti-arrow-top-right"></i> Enterprise Data Solutions</span>
                                </a>
                            </div>
                            <div class="features <?php echo $service == 'technology-partnership' ? 'active' : ''; ?>">
                                <a href="service-details.php?service=technology-partnership">
                                    <span><i class="ti-arrow-top-right"></i> Technology Partnership</span>
                                </a>
                            </div>
                            <div class="features <?php echo $service == 'cloud-hosting' ? 'active' : ''; ?>">
                                <a href="service-details.php?service=cloud-hosting">
                                    <span><i class="ti-arrow-top-right"></i> Cloud & Data Management</span>
                                </a>
                            </div>
                            <div class="features <?php echo $service == 'ecommerce-solutions' ? 'active' : ''; ?>">
                                <a href="service-details.php?service=ecommerce-solutions">
                                    <span><i class="ti-arrow-top-right"></i> E-Commerce Solutions</span>
                                </a>
                            </div>
                        </div>
                        <!-- CTA Box -->
                        <div class="sidebar-widget mb-50" style="padding:5px;">
                            <div class="widget-title text-center">
                                <h6>Ready to Transform?</h6>
                            </div>
                            <div class="widget-text text-center">
                                <p>Let's discuss how our <?php echo $serviceTitles[$service] ?? 'Software & Web Development'; ?> service can drive your digital transformation journey.</p>
                                <a href="contact.php" class="button-1 mt-15" style="margin-bottom:10px;">Get Free Consultation</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include("../../../layouts/footer.php");?>

    <!-- jQuery -->
    <?php include("../../../layouts/scripts.php");?>
</body>
</html>