<!DOCTYPE html>
<html lang="en">

<?php
// CEP UOK WEBSITE - HOMEPAGE (REDESIGNED)
// Include paths configuration
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";

// Include database and models
require_once get_db('database');
require_once MODULES_PATH . '/General/models/HomeModel.php';
require_once MODULES_PATH . '/Programs/models/DepartmentsModel.php';
require_once MODULES_PATH . '/Hero/models/HeroModel.php';
require_once MODULES_PATH . '/Testimonials/models/TestimonialModel.php';
require_once MODULES_PATH . '/News/models/NewsModel.php';

// Initialize database and models
$db = Database::getInstance();
$homeModel = new HomeModel($db);
$heroModel = new HeroModel($db);
$departmentsModel = new DepartmentsModel($db);
$testimonialModel = new TestimonialModel($db);
$newsModel = new NewsModel($db);

// Fetch sliders from database if not already loaded
if (!isset($sliders)) {
   $sliders = $heroModel->getHeroSliders();
}

// Fetch data
$pageContent = $homeModel->getPageContent('home');
$quickStats = $homeModel->getQuickStats();
$galleryImages = $homeModel->getFeaturedGalleryImages();
$recurringEvents = $homeModel->getRecurringEvents();
$departments = $departmentsModel->getDepartments(6);
$testimonials = $testimonialModel->getTestimonials(9);
$latestNews = $homeModel->getLatestNews(6);
$siteSettings = $homeModel->getSiteSettings();

// Helper function to get content by section
function getContent($pageContent, $section)
{
   foreach ($pageContent as $content) {
      if ($content['section_name'] === $section) {
         return $content['content'];
      }
   }
   return '';
}

// Include header
include_once get_layout('header');
?>

<body data-res-from="1025">
   <!-- Page Loader, Zmm Wrapper, Overlay Search -->
   <?php include_once get_layout('loader'); ?>
   <!-- Main wrapper-->
   <div class="page-wrapper">
      <div class="page-wrapper-inner">
         <header>
            <!--Mobile Header-->
            <?php include_once get_layout(layout_name: 'mobile-header'); ?>

            <!--Header-->
            <div class="header-inner header-1 header-absolute">
               <!--Topbar-->
               <?php include_once get_layout(layout_name: 'topbar'); ?>

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

            </div>
         </header>
         <!-- header -->
         <!-- Revolution Slider Section -->
         <?php include_once get_layout('hero-slider'); ?>
         <!-- Revolution Slider Section End -->
         <!-- Page Content -->
         <div class="content-wrapper pad-none">
            <div class="content-inner">
               
               <!-- Events Section (Recurring Fellowship Schedule) -->
               <section class="events-section pad-tb-0 broken-top-50 pt-sm-5 pt-xl-0 pad-bottom-md-none">
                  <div class="container">
                     <div class="row">
                        <div class="owl-carousel events-main-wrapper events-style-1" data-loop="1" data-nav="0"
                           data-dots="1" data-autoplay="0" data-autoplaypause="1" data-autoplaytime="5000"
                           data-smartspeed="1000" data-margin="30" data-items="2" data-items-tab="1" data-items-mob="1">
                           <?php foreach ($recurringEvents as $event): ?>
                              <div class="item">
                                 <div class="events-inner">
                                    <div class="events-item">
                                       <div class="media">
                                          <div class="event-date me-4">
                                             <?= substr($event['day_of_week'], 0, 3) ?>
                                             <span class="event-time"><?= date('g:i a', strtotime($event['start_time'])) ?></span>
                                          </div>
                                          <div class="media-body">
                                             <div class="event-content">
                                                <div class="event-title">
                                                   <h5><a href="#"><?= htmlspecialchars($event['title']) ?></a></h5>
                                                </div>
                                                <p class="mb-2 text-muted">
                                                   <i class="ti-location-pin"></i>
                                                   <?= htmlspecialchars($event['campus']) ?>
                                                </p>
                                                <div class="read-more"><a href="<?= url('news') ?>">View Details</a></div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           <?php endforeach; ?>
                        </div>
                     </div>
                  </div>
               </section>
               <!-- Events Section End -->

               <?php
               $welcomeVideoUrl = getContent($pageContent, 'welcome_video');
               $videoId = '';
               if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $welcomeVideoUrl, $matches)) {
                  $videoId = $matches[1];
               }
               $ytThumbnail = $videoId ? "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg" : img_url('about/about-1.jpeg');
               
               $welcomeTitle = getContent($pageContent, 'about_title') ?: 'CEP Community';
               $welcomeDesc = getContent($pageContent, 'about_description') ?: 'A vibrant fellowship where students, staff, and alumni gather to grow spiritually, serve the community, and impact the world with love and purpose.';
               $welcomeVision = getContent($pageContent, 'about_vision') ?: 'Building leaders of integrity, faith, and excellence — transforming society through Christ-centered values.';
               $feature1_title = getContent($pageContent, 'about_feature1_title') ?: 'The prevention and relief of poverty';
               $feature1_desc = getContent($pageContent, 'about_feature1_desc') ?: 'Providing items and services to individuals in need and/or charities, or other organisations working to prevent or relieve poverty.';
               $feature2_title = getContent($pageContent, 'about_feature2_title') ?: 'The advancement of education';
               $feature2_desc = getContent($pageContent, 'about_feature2_desc') ?: 'By supporting children and young people in East Africa through education, skills development, and the provision of school facilities.';
               ?>
               
               <!-- REDESIGNED WELCOME SECTION - CLEAN SPLIT LAYOUT -->
               <section class="welcome-split">
                  <div class="welcome-split-container">
                     <!-- Left: Video Column -->
                     <div class="welcome-split-video">
                        <div class="video-wrapper" onclick="openVideoModal('<?= htmlspecialchars($videoId) ?>')" role="button" tabindex="0">
                           <img class="video-bg" src="<?= $ytThumbnail ?>" alt="CEP Welcome Video" onerror="this.src='<?= img_url('about/about-1.jpeg') ?>'">
                           <div class="video-overlay-split">
                              <div class="play-button">
                                 <svg width="28" height="32" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                              </div>
                           </div>
                           <div class="video-caption">A Warm Welcome from CEP UoK</div>
                        </div>
                     </div>
                     
                     <!-- Right: Content Column - Clean White with Feature Cards -->
                     <div class="welcome-split-content">
                        <span class="content-tag">Welcome to CEP</span>
                        <h1 class="content-title"><?= htmlspecialchars($welcomeTitle) ?></h1>
                        <p class="content-description"><?= htmlspecialchars($welcomeDesc) ?></p>
                        
                        <!-- Feature Cards - Designed like reference image -->
                        <div class="feature-cards">
                            <div class="feature-card">
                                <div class="feature-card-icon">✦</div>
                                <div class="feature-card-content">
                                    <h3 class="feature-card-title"><?= htmlspecialchars($feature1_title) ?></h3>
                                    <p class="feature-card-desc"><?= htmlspecialchars($feature1_desc) ?></p>
                                </div>
                            </div>
                            <div class="feature-card">
                                <div class="feature-card-icon">✦</div>
                                <div class="feature-card-content">
                                    <h3 class="feature-card-title"><?= htmlspecialchars($feature2_title) ?></h3>
                                    <p class="feature-card-desc"><?= htmlspecialchars($feature2_desc) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="content-quote"><?= htmlspecialchars($welcomeVision) ?></div>
                        
                        <a href="<?= url('about') ?>" class="content-button">Learn More →</a>
                     </div>
                  </div>
               </section>

               <!-- REDESIGNED STATS SECTION -->
               <?php if (!empty($quickStats)): ?>
               <section class="stats-modern">
                  <div class="stats-modern-container">
                     <?php foreach ($quickStats as $stat): ?>
                        <div class="stat-modern-card">
                            <div class="stat-modern-icon"><i class="<?= htmlspecialchars($stat['stat_icon']) ?>"></i></div>
                            <div class="stat-modern-number"><?= htmlspecialchars($stat['stat_value']) ?></div>
                            <div class="stat-modern-label"><?= htmlspecialchars($stat['stat_label']) ?></div>
                        </div>
                     <?php endforeach; ?>
                  </div>
               </section>
               <?php endif; ?>

               <!-- Gallery Section -->
               <section class="photo-gallery-section">
                  <div class="container">
                     <div class="row">
                        <div class="offset-md-2 col-md-8">
                           <div class="title-wrap text-center margin-bottom-60">
                              <div class="section-title">
                                 <span class="sub-title theme-color text-uppercase">Our Gallery</span>
                                 <h2 class="section-title margin-top-5">Moments of Fellowship</h2>
                                 <span class="border-bottom center"></span>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="gallery-wrapper">
                        <div class="gallery-grid">
                           <?php foreach ($galleryImages as $index => $image): ?>
                              <figure data-index="<?= $index ?>">
                                 <img src="<?= img_url($image['image_url']) ?>" alt="<?= htmlspecialchars($image['title']) ?>" loading="lazy">
                                 <figcaption>
                                    <h3><?= htmlspecialchars($image['title']) ?></h3>
                                    <p><?= htmlspecialchars($image['category']) ?></p>
                                 </figcaption>
                              </figure>
                           <?php endforeach; ?>
                        </div>
                        <div class="gallery-sidebar">
                           <div class="gallery-info-card">
                              <h3>Celebrating Faith</h3>
                              <p>Explore moments from our fellowship, events, and community service activities that showcase the vibrant spirit of CEP UoK.</p>
                              <a href="<?= url('gallery-photo') ?>" class="btn-view-gallery">View Full Gallery <i class="ti-arrow-right"></i></a>
                           </div>
                        </div>
                     </div>
                  </div>
               </section>

               <div id="galleryModal" class="gallery-modal">
                  <div class="gallery-modal-content">
                     <button class="gallery-modal-close" onclick="closeGalleryModal()">&times;</button>
                     <button class="gallery-modal-nav prev" onclick="navigateGallery(-1)"><i class="ti-angle-left"></i></button>
                     <button class="gallery-modal-nav next" onclick="navigateGallery(1)"><i class="ti-angle-right"></i></button>
                     <div class="gallery-modal-image-container"><img id="galleryModalImage" class="gallery-modal-image" src="" alt=""></div>
                     <div class="gallery-modal-info"><h3 id="galleryModalTitle"></h3><p id="galleryModalCategory"></p></div>
                  </div>
               </div>

               <!-- Get a Quote Section (History Video) -->
               <section id="get-quote-section" class="get-quote-section section-bg-img" data-bg="img/bg/bg-1.jpg">
                  <div class="container">
                     <div class="row text-center">
                        <div class="col-md-12">
                           <div class="get-quote-1">
                              <div class="video-wrap wrap-stretch relative margin-bottom-50">
                                 <div class="video-wrap-details">
                                    <div class="video-play-btn text-center">
                                       <?php
                                       $historyVideo = getContent($pageContent, 'history_video');
                                       $historyVideoId = '';
                                       if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $historyVideo, $matches)) {
                                          $historyVideoId = $matches[1];
                                       }
                                       ?>
                                       <div class="video-icon">
                                          <a class="popup-youtube box-shadow1" href="javascript:void(0);" onclick="openVideoModal('<?= $historyVideoId ?>')">
                                             <i class="ti-control-play"></i>
                                          </a>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="title-wrap mb-0">
                                 <div class="section-title typo-white margin-bottom-40">
                                    <h2 class="title mb-3"><?= getContent($pageContent, 'history_title') ?></h2>
                                    <span class="dancing-text"><?= getContent($pageContent, 'history_description') ?></span>
                                 </div>
                                 <div class="get-quote-btn">
                                    <a class="btn btn-default" href="<?= url('about') ?>" title="Learn More">Read Full Story</a>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </section>

               <!-- Departments Section -->
               <section id="ministries-section" class="ministries-section pad-top-95 pad-bottom-70">
                  <div class="container">
                     <div class="row">
                        <div class="offset-md-2 col-md-8">
                           <div class="title-wrap text-center">
                              <div class="section-title">
                                 <span class="sub-title theme-color text-uppercase">CEP Departments</span>
                                 <h2 class="section-title margin-top-5">Our Departments</h2>
                                 <span class="border-bottom center"></span>
                              </div>
                           </div>
                        </div>
                        <div class="owl-carousel ministries-main-wrapper" data-loop="1" data-nav="1" data-dots="0" data-autoplay="0" data-autoplaypause="1" data-autoplaytime="5000" data-smartspeed="1000" data-margin="30" data-items="3" data-items-tab="2" data-items-mob="1">
                           <?php foreach ($departments as $dept): ?>
                              <div class="item">
                                 <div class="ministries-box-style-2">
                                    <div class="ministries-inner">
                                       <div class="ministries-thumb">
                                          <img class="img-fluid squared w-100" src="<?= img_url($dept['image_url']) ?>" width="360" height="240" alt="<?= htmlspecialchars($dept['title']) ?>">
                                       </div>
                                       <div class="ministries-content pad-30">
                                          <div class="ministries-title margin-bottom-15">
                                             <h4><a href="<?= url('departments') ?>" class="ministries-link"><?= htmlspecialchars($dept['title']) ?></a></h4>
                                          </div>
                                          <?php if ($dept['subtitle']): ?>
                                             <div class="ministries-subtitle text-muted mb-2"><em><?= htmlspecialchars($dept['subtitle']) ?></em></div>
                                          <?php endif; ?>
                                          <div class="ministries-desc"><p><?= htmlspecialchars(substr($dept['description'], 0, 120)) ?>...</p></div>
                                          <div class="ministries-link margin-top-20"><a href="<?= url('departments') ?>" class="link">Read More</a></div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           <?php endforeach; ?>
                        </div>
                     </div>
                  </div>
               </section>

               <!-- Contact Section -->
               <section class="contact-form-section typo-white section-bg-img o-visible pad-top-80 pad-bottom-160" data-bg="img/bg/bg-1.jpg">
                  <div class="shape-bottom" data-negative="false">
                     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none">
                        <path class="shape-fill" opacity="0.33" d="M473,67.3c-203.9,88.3-263.1-34-320.3,0C66,119.1,0,59.7,0,59.7V0h1000v59.7 c0,0-62.1,26.1-94.9,29.3c-32.8,3.3-62.8-12.3-75.8-22.1C806,49.6,745.3,8.7,694.9,4.7S492.4,59,473,67.3z"></path>
                        <path class="shape-fill" opacity="0.66" d="M734,67.3c-45.5,0-77.2-23.2-129.1-39.1c-28.6-8.7-150.3-10.1-254,39.1 s-91.7-34.4-149.2,0C115.7,118.3,0,39.8,0,39.8V0h1000v36.5c0,0-28.2-18.5-92.1-18.5C810.2,18.1,775.7,67.3,734,67.3z"></path>
                        <path class="shape-fill" d="M766.1,28.9c-200-57.5-266,65.5-395.1,19.5C242,1.8,242,5.4,184.8,20.6C128,35.8,132.3,44.9,89.9,52.5C28.6,63.7,0,0,0,0 h1000c0,0-9.9,40.9-83.6,48.1S829.6,47,766.1,28.9z"></path>
                     </svg>
                  </div>
                  <div class="container">
                     <div class="row">
                        <div class="col-xl-4 pe-xl-4 pb-5 pb-xl-0">
                           <div class="flip-box broken-top-115 verticalMove">
                              <div class="flip-box-inner imghvr-flip-3d-horz">
                                 <div class="flip-box-front">
                                    <div class="flip-box-icon margin-bottom-40"><span class="text-center flip-icon-middle ti-headphone-alt"></span></div>
                                    <h3 class="flip-box-title margin-bottom-30">Call Us</h3>
                                    <div class="flip-content">
                                       <p><?= $siteSettings['contact_address'] ?? 'KG 541 St, Kigali, Rwanda' ?></p>
                                       <p><a href="tel:<?= str_replace(' ', '', $siteSettings['contact_phone1'] ?? '+250791619272') ?>"><?= $siteSettings['contact_phone1'] ?? '+250 791 619 272' ?></a></p>
                                       <p><a href="/cdn-cgi/l/email-protection#4a7675776a6e39233e2f192f3e3e23242d39116d2925243e2b293e152f272b23266d176a75756a6d292f3a3f25217a7b0a2d272b2326642925276d6a7574"><?= $siteSettings['contact_email'] ?? 'cepuok01@gmail.com' ?></a></p>
                                    </div>
                                 </div>
                                 <div class="flip-box-back">
                                    <h3 class="flip-box-title">Call Us</h3>
                                    <div class="flip-content">
                                       <p><?= $siteSettings['contact_address'] ?? 'KG 541 St, Kigali, Rwanda' ?></p>
                                       <p><a href="tel:<?= str_replace(' ', '', $siteSettings['contact_phone1'] ?? '+250791619272') ?>"><?= $siteSettings['contact_phone1'] ?? '+250 791 619 272' ?></a></p>
                                       <p><a href="/cdn-cgi/l/email-protection#f0cccfcdd0d483998495a3958484999e9783abd7939f9e84919384af959d91999cd7add0cfcfd0d7939580859f9bc0c1b0979d91999cde939f9dd7d0cfce"><?= $siteSettings['contact_email'] ?? 'cepuok01@gmail.com' ?></a></p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-xl-8 ps-xl-4">
                           <div class="section-title-wrapper">
                              <div class="title-wrap mb-0">
                                 <div class="section-title">
                                    <span class="sub-title theme-color text-uppercase">Get In Touch</span>
                                    <h2 class="section-title margin-top-5">Don't hesitate Contact Us</h2>
                                    <span class="border-bottom"></span>
                                 </div>
                                 <div class="pad-top-15"><p class="margin-bottom-10">Feel free to Contact Us. We'd love to hear from you and answer any questions about CEP UoK fellowship, events, or how to get involved.</p></div>
                              </div>
                              <div class="button-section margin-top-25"><a class="btn btn-default" href="<?= url('contact') ?>" title="Contact Us">Contact Us</a></div>
                           </div>
                        </div>
                     </div>
                  </div>
               </section>

               <!-- Blog Section (News) -->
               <section class="blog-section pad-top-50 pad-bottom-95">
                  <div class="container">
                     <div class="row">
                        <div class="col-md-12">
                           <div class="title-wrap text-center">
                              <div class="section-title">
                                 <span class="sub-title theme-color text-uppercase">Latest Updates</span>
                                 <h2 class="section-title margin-top-5">News & Events</h2>
                                 <span class="border-bottom center"></span>
                              </div>
                           </div>
                           <div class="row">
                              <div class="owl-carousel blog-main-wrapper blog-style-1" data-loop="1" data-nav="0" data-dots="1" data-autoplay="0" data-autoplaypause="1" data-autoplaytime="5000" data-smartspeed="1000" data-margin="30" data-items="3" data-items-tab="2" data-items-mob="1">
                                 <?php foreach (array_slice($latestNews, 0, 6) as $news): ?>
                                    <div class="item">
                                       <div class="blog-inner">
                                          <div class="blog-thumb relative">
                                             <img src="<?= img_url($news['image_url']) ?>" class="img-fluid" width="768" height="600" alt="<?= htmlspecialchars($news['title']) ?>" />
                                             <div class="top-meta">
                                                <ul class="top-meta-list"><li><div class="post-date"><a href="<?= url('news') ?>"><i class="ti-calendar"></i> <?= date('M d, Y', strtotime($news['published_date'])) ?></a></div></li></ul>
                                             </div>
                                          </div>
                                          <div class="blog-details">
                                             <div class="blog-title"><h4 class="margin-bottom-10"><a href="<?= url('news') ?>" class="blog-name"><?= htmlspecialchars($news['title']) ?></a></h4></div>
                                             <div class="post-desc mt-2"><div class="blog-link"><a href="<?= url('news') ?>" class="link font-w-500">Read More</a></div></div>
                                          </div>
                                       </div>
                                    </div>
                                 <?php endforeach; ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </section>

               <!-- Testimonials Section -->
               <?php if (!empty($testimonials)): ?>
               <section class="testimonials-section">
                  <div class="container">
                     <div class="row">
                        <div class="offset-md-2 col-md-8">
                           <div class="title-wrap text-center margin-bottom-60">
                              <div class="section-title">
                                 <span class="sub-title theme-color text-uppercase">Testimonials</span>
                                 <h2 class="section-title margin-top-5">What People Say</h2>
                                 <span class="border-bottom center"></span>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="carousel-wrapper">
                        <button class="carousel-nav prev" onclick="moveCarousel(-1)"><i class="ti-angle-left"></i></button>
                        <button class="carousel-nav next" onclick="moveCarousel(1)"><i class="ti-angle-right"></i></button>
                        <div class="carousel-container">
                           <div class="carousel-track" id="testimonialTrack">
                              <?php foreach ($testimonials as $testimonial): ?>
                                 <div class="testimonial-slide">
                                    <div class="testimonial-avatar">
                                       <?php if ($testimonial['image_url']): ?>
                                          <img src="<?= img_url($testimonial['image_url']) ?>" alt="<?= htmlspecialchars($testimonial['name']) ?>">
                                       <?php else: ?>
                                          <div class="avatar-placeholder"><?= strtoupper(substr($testimonial['name'], 0, 1)) ?></div>
                                       <?php endif; ?>
                                    </div>
                                    <div class="testimonial-name"><?= htmlspecialchars($testimonial['name']) ?></div>
                                    <div class="testimonial-role"><?= htmlspecialchars($testimonial['role']) ?></div>
                                    <div class="testimonial-text">"<?= htmlspecialchars($testimonial['content']) ?>"</div>
                                 </div>
                              <?php endforeach; ?>
                           </div>
                        </div>
                        <div class="carousel-dots" id="testimonialDots"></div>
                     </div>
                  </div>
               </section>
               <?php endif; ?>

            </div>
         </div>
      </div>
   </div>

   <!-- Footer -->
   <?php include_once get_layout('footer'); ?>

   <!-- Video Modal - Simple & Clean -->
   <div id="videoModal" class="video-modal">
      <div class="video-modal-overlay" onclick="closeVideoModal()"></div>
      <div class="video-modal-container">
         <div class="video-modal-header">
            <button class="video-modal-close" onclick="closeVideoModal()">✕</button>
            <button class="video-modal-mini" onclick="toggleMiniView()">🗗</button>
         </div>
         <div class="video-modal-content">
            <iframe id="videoIframe" class="video-iframe" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
         </div>
      </div>
   </div>

   <!-- Styles -->
   <style>
   /* Welcome Split Section - Clean Design */
   .welcome-split {
      width: 100%;
      background: #ffffff;
      padding: 80px 0;
   }
   .welcome-split-container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 40px;
      display: flex;
      align-items: stretch;
      gap: 60px;
      flex-wrap: wrap;
   }
   .welcome-split-video {
      flex: 1;
      min-width: 280px;
   }
   .video-wrapper {
      position: relative;
      width: 100%;
      height: 100%;
      min-height: 380px;
      background: #1a1a2e;
      cursor: pointer;
      overflow: hidden;
   }
   .video-bg {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
   }
   .video-wrapper:hover .video-bg {
      transform: scale(1.03);
   }
   .video-overlay-split {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.35);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.3s;
   }
   .video-wrapper:hover .video-overlay-split {
      background: rgba(0,0,0,0.5);
   }
   .play-button {
      width: 70px;
      height: 70px;
      background: rgba(240,165,0,0.95);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.3s, background 0.3s;
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
   }
   .video-wrapper:hover .play-button {
      transform: scale(1.1);
      background: #f0a500;
   }
   .play-button svg {
       margin-left: 5px;
   }
   .video-caption {
      position: absolute;
      bottom: 20px;
      left: 20px;
      color: white;
      font-size: 13px;
      font-weight: 500;
      background: rgba(0,0,0,0.6);
      padding: 6px 14px;
      border-radius: 4px;
      letter-spacing: 0.5px;
   }
   .welcome-split-content {
      flex: 1;
      background: #ffffff;
      padding: 20px 0;
   }
   .content-tag {
      display: inline-block;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #f0a500;
      margin-bottom: 20px;
      border-left: 3px solid #f0a500;
      padding-left: 12px;
   }
   .content-title {
      font-size: 38px;
      font-weight: 700;
      line-height: 1.2;
      margin: 0 0 20px 0;
      color: #111827;
      letter-spacing: -0.3px;
   }
   .content-description {
      font-size: 16px;
      line-height: 1.65;
      color: #4b5563;
      margin-bottom: 32px;
   }
   
   /* Feature Cards - Design matching reference image */
   .feature-cards {
      display: flex;
      flex-direction: column;
      gap: 28px;
      margin: 0 0 28px 0;
   }
   .feature-card {
      display: flex;
      gap: 20px;
      align-items: flex-start;
      padding: 8px 0;
      border-bottom: 1px solid #e5e7eb;
   }
   .feature-card:last-child {
      border-bottom: none;
   }
   .feature-card-icon {
      font-size: 28px;
      color: #f0a500;
      flex-shrink: 0;
      line-height: 1;
      font-weight: 500;
   }
   .feature-card-content {
      flex: 1;
   }
   .feature-card-title {
      font-size: 18px;
      font-weight: 700;
      color: #111827;
      margin: 0 0 8px 0;
      line-height: 1.3;
   }
   .feature-card-desc {
      font-size: 14px;
      line-height: 1.5;
      color: #6b7280;
      margin: 0;
   }
   
   .content-quote {
      font-size: 15px;
      line-height: 1.6;
      color: #6b7280;
      font-style: italic;
      padding: 20px 0 20px 20px;
      border-left: 3px solid #f0a500;
      background: #fafafc;
      margin: 28px 0;
   }
   .content-button {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: #f0a500;
      color: #111827;
      font-weight: 600;
      padding: 12px 28px;
      text-decoration: none;
      font-size: 14px;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
   }
   .content-button:hover {
      background: #d48f0b;
      color: white;
      transform: translateX(5px);
   }
   
   /* Stats Modern Section */
   .stats-modern {
      background: #111827;
      padding: 60px 0;
   }
   .stats-modern-container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 40px;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 40px;
   }
   .stat-modern-card {
      flex: 1;
      min-width: 160px;
      text-align: center;
      padding: 24px 20px;
      background: rgba(255,255,255,0.03);
      border-bottom: 2px solid rgba(240,165,0,0.5);
      transition: all 0.3s;
   }
   .stat-modern-card:hover {
      background: rgba(240,165,0,0.08);
      transform: translateY(-5px);
   }
   .stat-modern-icon {
      font-size: 36px;
      color: #f0a500;
      margin-bottom: 16px;
   }
   .stat-modern-number {
      font-size: 36px;
      font-weight: 800;
      color: white;
      line-height: 1;
      margin-bottom: 8px;
   }
   .stat-modern-label {
      font-size: 13px;
      color: #9ca3af;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 500;
   }
   
   /* Video Modal - Simple & Clean */
   .video-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 99999;
      align-items: center;
      justify-content: center;
   }
   .video-modal.open {
      display: flex;
   }
   .video-modal-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.92);
      cursor: pointer;
   }
   .video-modal-container {
      position: relative;
      width: 90%;
      max-width: 1200px;
      background: #000;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      z-index: 100000;
   }
   .video-modal.mini .video-modal-container {
      position: fixed;
      bottom: 24px;
      right: 24px;
      width: 380px;
      max-width: calc(100vw - 48px);
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
      cursor: pointer;
   }
   .video-modal-header {
      position: absolute;
      top: 12px;
      right: 12px;
      display: flex;
      gap: 10px;
      z-index: 10;
   }
   .video-modal-close,
   .video-modal-mini {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: white;
      font-size: 18px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      font-weight: 400;
      line-height: 1;
   }
   .video-modal-close:hover,
   .video-modal-mini:hover {
      background: rgba(240, 165, 0, 0.9);
      transform: scale(1.05);
   }
   .video-modal.mini .video-modal-header {
      top: 8px;
      right: 8px;
   }
   .video-modal.mini .video-modal-close,
   .video-modal.mini .video-modal-mini {
      width: 30px;
      height: 30px;
      font-size: 14px;
   }
   .video-modal-content {
      position: relative;
      width: 100%;
      padding-bottom: 56.25%;
      background: #000;
   }
   .video-iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: none;
   }
   
   @media (max-width: 768px) {
      .video-modal.mini .video-modal-container {
         width: 300px;
      }
      .feature-card-title {
         font-size: 16px;
      }
      .feature-card-desc {
         font-size: 13px;
      }
      .feature-card-icon {
         font-size: 24px;
      }
   }
   @media (max-width: 991px) {
      .welcome-split-container { flex-direction: column; gap: 40px; padding: 0 24px; }
      .welcome-split-video { width: 100%; }
      .video-wrapper { min-height: 320px; }
      .content-title { font-size: 30px; }
      .stats-modern-container { gap: 20px; padding: 0 24px; }
      .stat-modern-card { min-width: 140px; padding: 20px 16px; }
      .stat-modern-number { font-size: 28px; }
   }
   @media (max-width: 560px) {
      .welcome-split { padding: 50px 0; }
      .welcome-split-container { padding: 0 16px; }
      .content-title { font-size: 26px; }
      .feature-card { gap: 12px; }
   }
   </style>

   <!-- jQuery -->
   <?php include_once get_layout('scripts'); ?>
   <?php include_once get_layout('hero-slider-scripts'); ?>

   <script>
   // Video Modal Functions
   let isMiniView = false;
   let currentVideoId = '';
   
   function openVideoModal(videoId) {
      if (!videoId) return;
      currentVideoId = videoId;
      const modal = document.getElementById('videoModal');
      const iframe = document.getElementById('videoIframe');
      
      isMiniView = false;
      modal.classList.remove('mini');
      iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0&modestbranding=1';
      modal.classList.add('open');
      document.body.style.overflow = 'hidden';
   }
   
   function closeVideoModal() {
      const modal = document.getElementById('videoModal');
      const iframe = document.getElementById('videoIframe');
      modal.classList.remove('open', 'mini');
      iframe.src = '';
      isMiniView = false;
      document.body.style.overflow = 'auto';
   }
   
   function toggleMiniView() {
      const modal = document.getElementById('videoModal');
      isMiniView = !isMiniView;
      if (isMiniView) {
         modal.classList.add('mini');
         document.body.style.overflow = 'auto';
      } else {
         modal.classList.remove('mini');
         document.body.style.overflow = 'hidden';
      }
   }
   
   document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
         const modal = document.getElementById('videoModal');
         if (modal.classList.contains('open')) {
            if (isMiniView) {
               toggleMiniView();
            } else {
               closeVideoModal();
            }
         }
      }
   });
   
   // Gallery Functions
   const galleryImages = <?= json_encode($galleryImages) ?>;
   let currentGalleryIndex = 0;
   
   document.querySelectorAll('.gallery-grid figure').forEach(figure => { 
      figure.addEventListener('click', function () { 
         currentGalleryIndex = parseInt(this.dataset.index); 
         openGalleryModal(); 
      }); 
   });
   
   function openGalleryModal() { 
      const modal = document.getElementById('galleryModal'); 
      const image = galleryImages[currentGalleryIndex]; 
      document.getElementById('galleryModalImage').src = '<?= BASE_URL ?>' + image.image_url; 
      document.getElementById('galleryModalTitle').textContent = image.title; 
      document.getElementById('galleryModalCategory').textContent = image.category; 
      modal.style.display = 'block'; 
      document.body.style.overflow = 'hidden'; 
   }
   
   function closeGalleryModal() { 
      document.getElementById('galleryModal').style.display = 'none'; 
      document.body.style.overflow = 'auto'; 
   }
   
   function navigateGallery(direction) { 
      currentGalleryIndex += direction; 
      if (currentGalleryIndex < 0) currentGalleryIndex = galleryImages.length - 1; 
      else if (currentGalleryIndex >= galleryImages.length) currentGalleryIndex = 0; 
      const image = galleryImages[currentGalleryIndex]; 
      document.getElementById('galleryModalImage').src = '<?= BASE_URL ?>' + image.image_url; 
      document.getElementById('galleryModalTitle').textContent = image.title; 
      document.getElementById('galleryModalCategory').textContent = image.category; 
   }
   
   document.addEventListener('keydown', function (e) { 
      if (e.key === 'Escape') closeGalleryModal(); 
      const modal = document.getElementById('galleryModal'); 
      if (modal.style.display === 'block') { 
         if (e.key === 'ArrowLeft') navigateGallery(-1); 
         if (e.key === 'ArrowRight') navigateGallery(1); 
      } 
   });
   
   document.getElementById('galleryModal')?.addEventListener('click', function (e) { 
      if (e.target === this) closeGalleryModal(); 
   });
   
   // Testimonial Carousel
   (function() {
      document.addEventListener('DOMContentLoaded', function() {
         let currentSlide = 0; 
         const track = document.getElementById('testimonialTrack'); 
         const slides = document.querySelectorAll('.testimonial-slide'); 
         const totalSlides = slides.length; 
         let slidesToShow = 3;
         
         function updateSlidesToShow() { 
            if (window.innerWidth <= 768) slidesToShow = 1; 
            else if (window.innerWidth <= 992) slidesToShow = 2; 
            else slidesToShow = 3; 
         }
         
         function moveCarousel(direction) { 
            updateSlidesToShow(); 
            const maxSlides = Math.max(0, totalSlides - slidesToShow); 
            currentSlide += direction; 
            if (currentSlide < 0) currentSlide = maxSlides; 
            else if (currentSlide > maxSlides) currentSlide = 0; 
            updateCarousel(); 
         }
         
         function updateCarousel() { 
            if (!track) return; 
            const offset = -(currentSlide * (100 / slidesToShow)); 
            track.style.transform = `translateX(${offset}%)`; 
            track.style.transition = 'transform 0.5s ease-in-out'; 
            updateDots(); 
         }
         
         function createDots() { 
            const dotsContainer = document.getElementById('testimonialDots'); 
            if (!dotsContainer) return; 
            dotsContainer.innerHTML = ''; 
            updateSlidesToShow(); 
            const maxSlides = Math.max(1, totalSlides - slidesToShow + 1); 
            for (let i = 0; i < maxSlides; i++) { 
               const dot = document.createElement('button'); 
               dot.className = 'carousel-dot'; 
               if (i === 0) dot.classList.add('active'); 
               dot.addEventListener('click', () => { currentSlide = i; updateCarousel(); }); 
               dotsContainer.appendChild(dot); 
            } 
         }
         
         function updateDots() { 
            const dots = document.querySelectorAll('.carousel-dot'); 
            dots.forEach((dot, index) => { dot.classList.toggle('active', index === currentSlide); }); 
         }
         
         function initCarousel() { 
            if (!track || slides.length === 0) return; 
            updateSlidesToShow(); 
            createDots(); 
            updateCarousel(); 
            slides.forEach(slide => { 
               slide.style.flex = `0 0 calc(${100 / slidesToShow}% - ${(20 * (slidesToShow - 1)) / slidesToShow}px)`; 
            }); 
            const prevBtn = document.querySelector('.carousel-nav.prev'); 
            const nextBtn = document.querySelector('.carousel-nav.next'); 
            if (prevBtn) prevBtn.onclick = () => moveCarousel(-1); 
            if (nextBtn) nextBtn.onclick = () => moveCarousel(1); 
            setInterval(() => moveCarousel(1), 10000); 
         }
         
         if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initCarousel); 
         else initCarousel();
         
         window.addEventListener('resize', function() { 
            updateSlidesToShow(); 
            createDots(); 
            updateCarousel(); 
            slides.forEach(slide => { 
               slide.style.flex = `0 0 calc(${100 / slidesToShow}% - ${(20 * (slidesToShow - 1)) / slidesToShow}px)`; 
            }); 
         });
      });
   })();
   </script>
</body>
</html>