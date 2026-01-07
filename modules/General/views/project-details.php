<!DOCTYPE html>
<html lang="zxx">

<?php 
// Include path helper
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";

// Include header
include_once get_layout('header');
?>

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
        $services = 'off'; 
        $work = 'active'; 
        $about = 'off'; 
        $news = 'off'; 
        $contacts = 'off'; 
     ?>
    <!-- Navbar -->
    <?php include_once get_layout('navbar'); ?>
    
    <!-- Header Banner -->
    <section class="banner-header section-padding bg-img" data-overlay-dark="5" data-background="<?= img_url('projects/projects-bg.jpg') ?>">
        <div class="v-middle">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <?php
                        require_once $root_path . '/config/database.php';
                        $db = Database::getInstance();
                        $projectId = $_GET['project'] ?? 0;
                        
                        $stmt = $db->prepare("SELECT * FROM projects WHERE projid = ?");
                        $stmt->execute([$projectId]);
                        $project = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($project) {
                            echo '<h6>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $project['category']))) . '</h6>';
                            echo '<h1>' . htmlspecialchars($project['title']) . '</h1>';
                        } else {
                            echo '<h6>Project Not Found</h6>';
                            echo '<h1>404 - Project Not Found</h1>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Project Gallery Section -->
    <section class="section-padding">
        <div class="container">
            <?php if ($project): ?>
            <div class="row">
                <div class="col-md-12 text-center mb-50">
                    <div class="section-subtitle">Project Showcase</div>
                    <div class="section-title">Project Gallery</div>
                </div>
            </div>
            
            <!-- Main Project Image -->
            <div class="row mb-60">
                <div class="col-md-12">
                    <div class="main-project-image">
                        <img src="<?= img_url('projects/' . htmlspecialchars($project['image_path'] ?? 'default.jpg')) ?>" 
                             alt="<?php echo htmlspecialchars($project['title']); ?>" 
                             class="img-fluid rounded" 
                             id="main-project-img">
                    </div>
                </div>
            </div>
            
            <!-- Gallery Carousel -->
            <div class="row">
                <div class="col-md-12">
                    <div class="gallery-carousel-wrapper">
                        <div class="gallery-carousel owl-carousel owl-theme" id="gallery-carousel">
                            <!-- Gallery images will be loaded via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Project Details Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <?php if ($project): ?>
                    <div class="row mb-60">
                        <div class="col-md-12">
                            <h3 class="mb-4">Project Overview</h3>
                            <p class="mb-30"><?php echo htmlspecialchars($project['description']); ?></p>
                            
                            <?php if (!empty($project['technologies'])): ?>
                            <h4 class="mb-3">Technologies Used</h4>
                            <div class="tech-tags mb-4">
                                <?php
                                $technologies = explode(',', $project['technologies']);
                                foreach ($technologies as $tech) {
                                    echo '<span class="tech-tag">' . htmlspecialchars(trim($tech)) . '</span>';
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                            
                            <ul class="list-unstyled list mb-30">
                                <li>
                                    <div class="list-icon"> <span class="ti-check"></span> </div>
                                    <div class="list-text">
                                        <p>Professional Development</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="list-icon"> <span class="ti-check"></span> </div>
                                    <div class="list-text">
                                        <p>Quality Assurance</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="list-icon"> <span class="ti-check"></span> </div>
                                    <div class="list-text">
                                        <p>Ongoing Support</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Project Details Accordion -->
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="mb-4">Project Details</h3>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <ul class="accordion-box clearfix">
                                <li class="accordion block">
                                    <div class="acc-btn"><span class="count">1.</span> Project Requirements</div>
                                    <div class="acc-content">
                                        <div class="content">
                                            <div class="text">Detailed project requirements and specifications provided by the client. Our team works closely with stakeholders to ensure all functional and technical requirements are captured accurately.</div>
                                        </div>
                                    </div>
                                </li>
                                <li class="accordion block">
                                    <div class="acc-btn"><span class="count">2.</span> Development Process</div>
                                    <div class="acc-content">
                                        <div class="content">
                                            <div class="text">Our agile development process ensures timely delivery and quality results. We follow iterative development cycles with regular client feedback and continuous integration.</div>
                                        </div>
                                    </div>
                                </li>
                                <li class="accordion block">
                                    <div class="acc-btn"><span class="count">3.</span> Testing & Quality Assurance</div>
                                    <div class="acc-content">
                                        <div class="content">
                                            <div class="text">Comprehensive testing procedures to ensure bug-free performance. Our QA process includes unit testing, integration testing, user acceptance testing, and performance testing.</div>
                                        </div>
                                    </div>
                                </li>
                                <li class="accordion block">
                                    <div class="acc-btn"><span class="count">4.</span> Deployment & Support</div>
                                    <div class="acc-content">
                                        <div class="content">
                                            <div class="text">Smooth deployment process with ongoing maintenance and support. We ensure seamless transition to production environment and provide continuous monitoring and support services.</div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h3>Project Not Found</h3>
                            <p>The project you are looking for does not exist or has been removed.</p>
                            <a href="<?= url('projects') ?>" class="button-1 mt-20">Back to Projects</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar - Updated Project Information Section -->
                <div class="col-lg-4 col-md-12">
                    <?php if ($project): ?>
                    <div class="project-info-card mb-30">
                        <div class="project-info-header">
                            <div class="project-info-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h4>Project Information</h4>
                        </div>
                        
                        <div class="project-info-content">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-tasks"></i>
                                    <span>Project Status</span>
                                </div>
                                <div class="info-value">
                                    <span class="status-badge status-<?php 
                                        switch($project['status']) {
                                            case 'completed': echo 'completed'; break;
                                            case 'in_progress': echo 'progress'; break;
                                            case 'under_development': echo 'development'; break;
                                            case 'planning': echo 'planning'; break;
                                            default: echo 'default';
                                        }
                                    ?>"><?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-folder"></i>
                                    <span>Category</span>
                                </div>
                                <div class="info-value"><?php echo ucfirst(str_replace('_', ' ', $project['category'])); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-industry"></i>
                                    <span>Industry</span>
                                </div>
                                <div class="info-value"><?php echo ucfirst(str_replace('_', ' ', $project['industry'])); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-user-tie"></i>
                                    <span>Client</span>
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($project['client_name'] ?? 'Confidential'); ?></div>
                            </div>
                            
                            <?php if ($project['launch_date'] && $project['launch_date'] != '0000-00-00'): ?>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-rocket"></i>
                                    <span>Launch Date</span>
                                </div>
                                <div class="info-value"><?php echo date('M d, Y', strtotime($project['launch_date'])); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($project['project_duration']): ?>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-clock"></i>
                                    <span>Duration</span>
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($project['project_duration']); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($project['project_budget']): ?>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span>Budget</span>
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($project['project_budget']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="project-actions">
                        <?php if ($project['preview'] == 1 && !empty($project['project_url'])): ?>
                        <a href="<?php echo htmlspecialchars($project['project_url']); ?>" target="_blank" class="action-btn primary-btn">
                            <i class="fas fa-external-link-alt"></i>
                            Live Preview
                        </a>
                        <?php endif; ?>
                        <a href="<?= url('contact') ?>" class="action-btn secondary-btn">
                            <i class="fas fa-play-circle"></i>
                            Start Similar Project
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Related Projects -->
    <?php if ($project): ?>
    <section class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center mb-50">
                    <div class="section-subtitle">More Projects</div>
                    <div class="section-title">Related Projects</div>
                </div>
            </div>
            <div class="row" id="related-projects">
                <!-- Related projects will be loaded here via AJAX -->
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imagePreviewModalLabel">Project Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" alt="" class="img-fluid" id="modal-image">
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" id="prev-image">
                        <i class="ti-angle-left"></i> Previous
                    </button>
                    <span class="image-counter">1 of 1</span>
                    <button type="button" class="btn btn-secondary" id="next-image">
                        Next <i class="ti-angle-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include_once get_layout('footer'); ?>
    
    <!-- jQuery -->
    <?php include_once get_layout('scripts'); ?>
    
    <script>
    $(document).ready(function() {
        const projectId = <?php echo $projectId ?? 0; ?>;
        let galleryImages = [];
        let currentImageIndex = 0;
        
        // Load project gallery and related projects
        if (projectId > 0) {
            loadProjectGallery(projectId);
            loadRelatedProjects(projectId);
        }
        
        function loadProjectGallery(projectId) {
            $.ajax({
                url: '<?= url('static/get_project_gallery') ?>',
                type: 'GET',
                data: { project_id: projectId },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.images) {
                        galleryImages = response.images;
                        displayGalleryCarousel(response.images, response.hasMultiple);
                    } else {
                        // Fallback to main project image
                        const mainImage = '<?= img_url('projects/' . htmlspecialchars($project['image_path'] ?? 'default.jpg')) ?>';
                        galleryImages = [{url: mainImage, alt: 'Project Image', type: 'main'}];
                        displayGalleryCarousel(galleryImages, false);
                    }
                },
                error: function() {
                    // Fallback to main project image
                    const mainImage = '<?= img_url('projects/' . htmlspecialchars($project['image_path'] ?? 'default.jpg')) ?>';
                    galleryImages = [{url: mainImage, alt: 'Project Image', type: 'main'}];
                    displayGalleryCarousel(galleryImages, false);
                }
            });
        }
        
        function displayGalleryCarousel(images, hasMultiple) {
            let carouselHtml = '';
            images.forEach((image, index) => {
                carouselHtml += `
                    <div class="gallery-item" data-index="${index}">
                        <img src="${image.url}" 
                             alt="${image.alt || 'Project Image'}" 
                             class="img-fluid"
                             data-index="${index}">
                    </div>
                `;
            });
            
            $('#gallery-carousel').html(carouselHtml);
            
            // Initialize carousel with conditional settings
            $('.gallery-carousel').owlCarousel({
                loop: hasMultiple, // Only loop if multiple images
                margin: 15,
                nav: hasMultiple, // Only show nav if multiple images
                dots: false,
                responsive: {
                    0: {
                        items: hasMultiple ? 2 : 1 // Show 1 item if single image
                    },
                    600: {
                        items: hasMultiple ? 3 : 1
                    },
                    1000: {
                        items: hasMultiple ? 4 : 1
                    }
                }
            });
            
            // Set main image to first image
            if (images.length > 0) {
                $('#main-project-img').attr('src', images[0].url);
            }
            
            // Add click events to gallery items
            $('.gallery-item img').on('click', function() {
                const index = parseInt($(this).data('index'));
                openImageModal(index);
            });
        }
        
        function openImageModal(index) {
            currentImageIndex = index;
            const image = galleryImages[currentImageIndex];
            $('#modal-image').attr('src', image.url);
            updateModalCounter();
            $('#imagePreviewModal').modal('show');
        }
        
        function updateModalCounter() {
            $('.image-counter').text(`${currentImageIndex + 1} of ${galleryImages.length}`);
            
            // Show/hide navigation buttons based on number of images
            if (galleryImages.length <= 1) {
                $('#prev-image, #next-image').hide();
                $('.image-counter').hide();
            } else {
                $('#prev-image, #next-image').show();
                $('.image-counter').show();
            }
        }
        
        // Modal navigation
        $('#prev-image').on('click', function() {
            currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
            $('#modal-image').attr('src', galleryImages[currentImageIndex].url);
            updateModalCounter();
        });
        
        $('#next-image').on('click', function() {
            currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
            $('#modal-image').attr('src', galleryImages[currentImageIndex].url);
            updateModalCounter();
        });
        
        // Keyboard navigation in modal
        $(document).on('keydown', function(e) {
            if ($('#imagePreviewModal').is(':visible') && galleryImages.length > 1) {
                if (e.key === 'ArrowLeft') {
                    $('#prev-image').click();
                } else if (e.key === 'ArrowRight') {
                    $('#next-image').click();
                } else if (e.key === 'Escape') {
                    $('#imagePreviewModal').modal('hide');
                }
            }
        });
        
        function loadRelatedProjects(projectId) {
            $.ajax({
                url: '<?= url('static/get_related_projects') ?>',
                type: 'GET',
                data: { 
                    project_id: projectId,
                    category: '<?php echo $project['category'] ?? ""; ?>',
                    industry: '<?php echo $project['industry'] ?? ""; ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#related-projects').html(response.html);
                    } else {
                        $('#related-projects').html('<div class="col-md-12 text-center"><p>No related projects found.</p></div>');
                    }
                },
                error: function() {
                    $('#related-projects').html('<div class="col-md-12 text-center"><p>Error loading related projects.</p></div>');
                }
            });
        }
    });
    </script>

    <style>
        /* Your existing CSS styles remain the same */
        /* Main Project Image */
        .main-project-image {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .main-project-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        /* Gallery Carousel */
        .gallery-carousel-wrapper {
            margin: 30px 0;
        }
        
        .gallery-carousel .gallery-item {
            cursor: pointer;
            transition: transform 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .gallery-carousel .gallery-item:hover {
            transform: translateY(-5px);
        }
        
        .gallery-carousel .gallery-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        /* Single image in carousel - center it */
        .gallery-carousel.owl-carousel .owl-stage {
            display: flex;
            justify-content: center;
        }
        
        /* Technology Tags */
        .tech-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .tech-tag {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Updated Project Information Card */
        .project-info-card {
            background: white;
            border-radius: 5px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid #eef2f7;
        }
        
        .project-info-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); 
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .project-info-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
        }
        
        .project-info-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .project-info-content {
            padding: 20px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #64748b;
            font-weight: 500;
        }
        
        .info-label i {
            width: 16px;
            color: #4f46e5;
        }
        
        .info-value {
            font-weight: 600;
            color: #1e293b;
        }
        
        /* Status Badges */
        .status-badge {
            position: inherit;
            padding: 0px 8px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-completed {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-progress {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-development {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-planning {
            background: #f3e8ff;
            color: #7c3aed;
        }
        
        .status-default {
            background: #f1f5f9;
            color: #475569;
        }
        
        /* Action Buttons */
        .project-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 20px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .primary-btn {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }
        
        .primary-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3);
            color: white;
        }
        
        .secondary-btn {
            background: white;
            color: #4f46e5;
            border: 2px solid #4f46e5;
        }
        
        .secondary-btn:hover {
            background: #4f46e5;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(79, 70, 229, 0.2);
        }
        
        /* Modal Styles */
        #imagePreviewModal .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        #imagePreviewModal .modal-body {
            padding: 20px;
            background: #f8f9fa;
        }
        
        #modal-image {
            width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        
        .image-counter {
            font-weight: 600;
            color: #6c757d;
        }
        
        /* Owl Carousel Customization */
        .gallery-carousel .owl-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            transform: translateY(-50%);
        }
        
        .gallery-carousel .owl-prev,
        .gallery-carousel .owl-next {
            position: absolute;
            background: rgba(52, 152, 219, 0.8) !important;
            color: white !important;
            width: 40px;
            height: 40px;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
        }
        
        .gallery-carousel .owl-prev {
            left: -20px;
        }
        
        .gallery-carousel .owl-next {
            right: -20px;
        }
        
        .gallery-carousel .owl-prev:hover,
        .gallery-carousel .owl-next:hover {
            background: #3498db !important;
        }
        
        /* Hide navigation when only one image */
        .gallery-carousel .owl-nav.disabled {
            display: none !important;
        }
        
        @media (max-width: 768px) {
            .main-project-image img {
                height: 250px;
            }
            
            .gallery-carousel .gallery-item img {
                height: 120px;
            }
            
            .gallery-carousel .owl-prev {
                left: -10px;
            }
            
            .gallery-carousel .owl-next {
                right: -10px;
            }
            
            #imagePreviewModal .modal-dialog {
                margin: 20px;
            }
            
            .project-info-header {
                padding: 15px;
            }
            
            .project-info-content {
                padding: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .gallery-carousel .gallery-item img {
                height: 100px;
            }
        }
    </style>
</body>
</html>