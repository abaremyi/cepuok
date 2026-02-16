<!DOCTYPE html>
<html lang="en">

<?php
// CEP UOK WEBSITE - HOMEPAGE
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
                     <!-- Row -->
                     <div class="row">
                        <!--Events Main Slider-->
                        <div class="owl-carousel events-main-wrapper events-style-1" data-loop="1" data-nav="0"
                           data-dots="1" data-autoplay="0" data-autoplaypause="1" data-autoplaytime="5000"
                           data-smartspeed="1000" data-margin="30" data-items="2" data-items-tab="1" data-items-mob="1">

                           <?php foreach ($recurringEvents as $event): ?>
                              <!--Item-->
                              <div class="item">
                                 <!--Events Inner-->
                                 <div class="events-inner">
                                    <div class="events-item">
                                       <div class="media">
                                          <div class="event-date me-4">
                                             <?= substr($event['day_of_week'], 0, 3) ?>
                                             <span
                                                class="event-time"><?= date('g:i a', strtotime($event['start_time'])) ?></span>
                                          </div>
                                          <div class="media-body">
                                             <!-- Event Content -->
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
                                 <!--Events Inner Ends-->
                              </div>
                              <!--Item Ends-->
                           <?php endforeach; ?>

                        </div>
                        <!--Events Owl Slider-->
                     </div>
                     <!-- Row -->
                  </div>
                  <!-- Container -->
               </section>
               <!-- Events Section End -->

               <!-- About Section -->
               <section id="section-about" class="section-about pad-top-90">
                  <div class="container">
                     <!-- Row -->
                     <div class="row">
                        <!-- Col -->
                        <div class="col-xl-6 align-self-center">
                           <!-- about wrap -->
                           <div class="about-wrap relative">
                              <div class="about-wrap-inner">
                                 <!-- about details -->
                                 <div class="about-wrap-details">
                                    <!-- about button -->
                                    <div class="text-center">
                                       <div class="about-img bf-pattern mb-5 mb-xl-0">
                                          <?php
                                          $welcomeVideo = getContent($pageContent, 'welcome_video');
                                          $videoId = '';
                                          if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $welcomeVideo, $matches)) {
                                             $videoId = $matches[1];
                                          }
                                          ?>
                                          <div class="video-wrapper position-relative" style="cursor: pointer;">
                                             <img src="<?= img_url('about/about-1.jpeg') ?>" class="img-fluid"
                                                alt="about-img">
                                             <div class="play-button position-absolute"
                                                onclick="openVideoModal('<?= $videoId ?>')" style="top: 50%; left: 50%; transform: translate(-50%, -50%); 
                                                         background: rgba(255,255,255,0.9); width: 70px; height: 70px; 
                                                         border-radius: 50%; display: flex; align-items: center; 
                                                         justify-content: center; cursor: pointer;">
                                                <i class="ti-control-play"
                                                   style="font-size: 30px; color: #e74c3c; margin-left: 5px;"></i>
                                             </div>
                                          </div>
                                       </div>
                                       <!-- .col -->
                                    </div>
                                 </div>
                                 <!-- about details End-->
                              </div>
                           </div>
                           <!-- about wrap end -->
                        </div>
                        <!-- .col -->
                        <!-- Col -->
                        <div class="col-xl-6 px-3 ps-xl-0">
                           <div class="title-wrap margin-bottom-30">
                              <div class="section-title">
                                 <span class="sub-title theme-color text-uppercase">About Us</span>
                                 <h2 class="section-title margin-top-5"><?= getContent($pageContent, 'about_title') ?>
                                 </h2>
                                 <span class="border-bottom"></span>
                              </div>
                              <div class="pad-top-15">
                                 <p class="margin-bottom-20"><?= getContent($pageContent, 'about_description') ?></p>
                                 <p class="styled-text"><?= getContent($pageContent, 'about_vision') ?></p>
                              </div>
                           </div>
                           <div class="row">
                              <!-- Feature Box -->
                              <div class="col-md-6">
                                 <div class="feature-box-wrap f-box-style-1 mb-md-0 mb-sm-4 relative">
                                    <div class="feature-box-details">
                                       <div class="feature-icon margin-bottom-25">
                                          <span
                                             class="<?= getContent($pageContent, 'about_feature1_icon') ?> b-radius-50 d-block"></span>
                                       </div>
                                       <div class="feature-content">
                                          <div class="feature-title relative margin-bottom-15">
                                             <h4><?= getContent($pageContent, 'about_feature1_title') ?></h4>
                                          </div>
                                          <p class="mb-0"><?= getContent($pageContent, 'about_feature1_desc') ?></p>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <!-- Feature Box End -->
                              <!-- Feature Box -->
                              <div class="col-md-6">
                                 <div class="feature-box-wrap f-box-style-1 relative">
                                    <div class="feature-box-details">
                                       <div class="feature-icon margin-bottom-25">
                                          <span
                                             class="<?= getContent($pageContent, 'about_feature2_icon') ?> b-radius-50 d-block"></span>
                                       </div>
                                       <div class="feature-content">
                                          <div class="feature-title relative margin-bottom-15">
                                             <h4><?= getContent($pageContent, 'about_feature2_title') ?></h4>
                                          </div>
                                          <p class="mb-0"><?= getContent($pageContent, 'about_feature2_desc') ?></p>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <!-- Feature Box End -->
                           </div>
                           <div class="button-section margin-top-35">
                              <a class="btn btn-default" href="<?= url('about') ?>" title="Learn More">Learn More</a>
                           </div>
                        </div>
                        <!-- Col -->
                     </div>
                     <!-- Row -->
                  </div>
                  <!-- Container -->
               </section>
               <!-- About Section End -->

               <!-- Quick Stats Section (New) -->
               <?php if (!empty($quickStats)): ?>
                  <section class="stats-section pad-top-60 pad-bottom-60 bg-theme">
                     <div class="container">
                        <div class="row text-center">
                           <?php foreach ($quickStats as $stat): ?>
                              <div class="col-6 col-md-4 col-lg-3 mb-4 mb-lg-0">
                                 <div class="stat-item typo-white">
                                    <i class="<?= $stat['stat_icon'] ?> fa-2x mb-3"></i>
                                    <h2 class="stat-number font-weight-bold mb-1"><?= $stat['stat_value'] ?></h2>
                                    <p class="stat-label"><?= $stat['stat_label'] ?></p>
                                 </div>
                              </div>
                           <?php endforeach; ?>
                        </div>
                     </div>
                  </section>
               <?php endif; ?>
               <!-- Quick Stats Section End -->

               <!-- Gallery Section (New) -->
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
                                 <img src="<?= img_url($image['image_url']) ?>"
                                    alt="<?= htmlspecialchars($image['title']) ?>" loading="lazy">
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
                              <p>Explore moments from our fellowship, events, and community service activities that
                                 showcase the vibrant spirit of CEP UoK.</p>
                              <a href="<?= url('gallery-photo') ?>" class="btn-view-gallery">
                                 View Full Gallery
                                 <i class="ti-arrow-right"></i>
                              </a>
                           </div>
                        </div>
                     </div>
                  </div>
               </section>

               <!-- Gallery Modal -->
               <div id="galleryModal" class="gallery-modal">
                  <div class="gallery-modal-content">
                     <button class="gallery-modal-close" onclick="closeGalleryModal()">&times;</button>
                     <button class="gallery-modal-nav prev" onclick="navigateGallery(-1)">
                        <i class="ti-angle-left"></i>
                     </button>
                     <button class="gallery-modal-nav next" onclick="navigateGallery(1)">
                        <i class="ti-angle-right"></i>
                     </button>

                     <div class="gallery-modal-image-container">
                        <img id="galleryModalImage" class="gallery-modal-image" src="" alt="">
                     </div>

                     <div class="gallery-modal-info">
                        <h3 id="galleryModalTitle"></h3>
                        <p id="galleryModalCategory"></p>
                     </div>
                  </div>
               </div>
               <!-- Gallery Section End -->

               <!-- Get a Quote Section (History Video) -->
               <section id="get-quote-section" class="get-quote-section section-bg-img" data-bg="img/bg/bg-1.jpg">
                  <div class="container">
                     <!-- Row -->
                     <div class="row text-center">
                        <!-- Col -->
                        <div class="col-md-12">
                           <div class="get-quote-1">
                              <!-- video wrap -->
                              <div class="video-wrap wrap-stretch relative margin-bottom-50">
                                 <!-- video details -->
                                 <div class="video-wrap-details">
                                    <!-- video button -->
                                    <div class="video-play-btn text-center">
                                       <?php
                                       $historyVideo = getContent($pageContent, 'history_video');
                                       $historyVideoId = '';
                                       if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $historyVideo, $matches)) {
                                          $historyVideoId = $matches[1];
                                       }
                                       ?>
                                       <div class="video-icon">
                                          <a class="popup-youtube box-shadow1" href="javascript:void(0);"
                                             onclick="openVideoModal('<?= $historyVideoId ?>')">
                                             <i class="ti-control-play"></i>
                                          </a>
                                       </div>
                                    </div>
                                 </div>
                                 <!-- video details End-->
                              </div>
                              <!-- video wrap end -->
                              <div class="title-wrap mb-0">
                                 <div class="section-title typo-white margin-bottom-40">
                                    <h2 class="title mb-3"><?= getContent($pageContent, 'history_title') ?></h2>
                                    <span
                                       class="dancing-text"><?= getContent($pageContent, 'history_description') ?></span>
                                 </div>
                                 <div class="get-quote-btn">
                                    <a class="btn btn-default" href="<?= url('about') ?>" title="Learn More">Read Full
                                       Story</a>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <!-- Col -->
                     </div>
                     <!-- Row -->
                  </div>
                  <!-- Container -->
               </section>
               <!-- Get a Quote Section End -->

               <!-- Departments Section (was Ministries) -->
               <section id="ministries-section" class="ministries-section pad-top-95 pad-bottom-70">
                  <div class="container">
                     <!-- Row -->
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

                        <!--Departments Main Slider-->
                        <div class="owl-carousel ministries-main-wrapper" data-loop="1" data-nav="1" data-dots="0"
                           data-autoplay="0" data-autoplaypause="1" data-autoplaytime="5000" data-smartspeed="1000"
                           data-margin="30" data-items="3" data-items-tab="2" data-items-mob="1">

                           <?php foreach ($departments as $dept): ?>
                              <!--Item-->
                              <div class="item">
                                 <div class="ministries-box-style-2">
                                    <!-- Department Inner -->
                                    <div class="ministries-inner">
                                       <div class="ministries-thumb">
                                          <img class="img-fluid squared w-100" src="<?= img_url($dept['image_url']) ?>"
                                             width="360" height="240" alt="<?= htmlspecialchars($dept['title']) ?>">
                                       </div>
                                       <!-- Department Content -->
                                       <div class="ministries-content pad-30">
                                          <div class="ministries-title margin-bottom-15">
                                             <h4><a href="<?= url('departments') ?>" class="ministries-link">
                                                   <?= htmlspecialchars($dept['title']) ?>
                                                </a></h4>
                                          </div>
                                          <?php if ($dept['subtitle']): ?>
                                             <div class="ministries-subtitle text-muted mb-2">
                                                <em><?= htmlspecialchars($dept['subtitle']) ?></em>
                                             </div>
                                          <?php endif; ?>
                                          <div class="ministries-desc">
                                             <p><?= htmlspecialchars(substr($dept['description'], 0, 120)) ?>...</p>
                                          </div>
                                          <div class="ministries-link margin-top-20">
                                             <a href="<?= url('departments') ?>" class="link">Read More</a>
                                          </div>
                                       </div>
                                    </div>
                                    <!-- Department Inner Ends -->
                                 </div>
                              </div>
                              <!--Item Ends-->
                           <?php endforeach; ?>

                        </div>
                        <!--Departments Owl Slider-->
                     </div>
                     <!-- Row -->
                  </div>
                  <!-- Container -->
               </section>
               <!-- Departments Section End -->

               <!-- Contact Section -->
               <section class="contact-form-section typo-white section-bg-img o-visible pad-top-80 pad-bottom-160"
                  data-bg="img/bg/bg-1.jpg">
                  <div class="shape-bottom" data-negative="false">
                     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none">
                        <path class="shape-fill" opacity="0.33"
                           d="M473,67.3c-203.9,88.3-263.1-34-320.3,0C66,119.1,0,59.7,0,59.7V0h1000v59.7 c0,0-62.1,26.1-94.9,29.3c-32.8,3.3-62.8-12.3-75.8-22.1C806,49.6,745.3,8.7,694.9,4.7S492.4,59,473,67.3z">
                        </path>
                        <path class="shape-fill" opacity="0.66"
                           d="M734,67.3c-45.5,0-77.2-23.2-129.1-39.1c-28.6-8.7-150.3-10.1-254,39.1 s-91.7-34.4-149.2,0C115.7,118.3,0,39.8,0,39.8V0h1000v36.5c0,0-28.2-18.5-92.1-18.5C810.2,18.1,775.7,67.3,734,67.3z">
                        </path>
                        <path class="shape-fill"
                           d="M766.1,28.9c-200-57.5-266,65.5-395.1,19.5C242,1.8,242,5.4,184.8,20.6C128,35.8,132.3,44.9,89.9,52.5C28.6,63.7,0,0,0,0 h1000c0,0-9.9,40.9-83.6,48.1S829.6,47,766.1,28.9z">
                        </path>
                     </svg>
                  </div>
                  <div class="container">
                     <div class="row">
                        <!-- col -->
                        <div class="col-xl-4 pe-xl-4 pb-5 pb-xl-0">
                           <div class="flip-box broken-top-115 verticalMove">
                              <div class="flip-box-inner imghvr-flip-3d-horz">
                                 <div class="flip-box-front">
                                    <div class="flip-box-icon margin-bottom-40">
                                       <span class="text-center flip-icon-middle ti-headphone-alt"></span>
                                    </div>
                                    <h3 class="flip-box-title margin-bottom-30">Call Us</h3>
                                    <div class="flip-content">
                                       <p><?= $siteSettings['contact_address'] ?? 'KG 541 St, Kigali, Rwanda' ?></p>
                                       <p><a
                                             href="tel:<?= str_replace(' ', '', $siteSettings['contact_phone1'] ?? '+250791619272') ?>">
                                             <?= $siteSettings['contact_phone1'] ?? '+250 791 619 272' ?>
                                          </a></p>
                                       <p><a
                                             href="mailto:<?= $siteSettings['contact_email'] ?? 'cepuok01@gmail.com' ?>">
                                             <?= $siteSettings['contact_email'] ?? 'cepuok01@gmail.com' ?>
                                          </a></p>
                                    </div>
                                 </div>
                                 <div class="flip-box-back">
                                    <h3 class="flip-box-title">Call Us</h3>
                                    <div class="flip-content">
                                       <p><?= $siteSettings['contact_address'] ?? 'KG 541 St, Kigali, Rwanda' ?></p>
                                       <p><a
                                             href="tel:<?= str_replace(' ', '', $siteSettings['contact_phone1'] ?? '+250791619272') ?>">
                                             <?= $siteSettings['contact_phone1'] ?? '+250 791 619 272' ?>
                                          </a></p>
                                       <p><a
                                             href="mailto:<?= $siteSettings['contact_email'] ?? 'cepuok01@gmail.com' ?>">
                                             <?= $siteSettings['contact_email'] ?? 'cepuok01@gmail.com' ?>
                                          </a></p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <!-- col -->
                        <div class="col-xl-8 ps-xl-4">
                           <div class="section-title-wrapper">
                              <div class="title-wrap mb-0">
                                 <div class="section-title">
                                    <span class="sub-title theme-color text-uppercase">Get In Touch</span>
                                    <h2 class="section-title margin-top-5">Don't hesitate Contact Us</h2>
                                    <span class="border-bottom"></span>
                                 </div>
                                 <div class="pad-top-15">
                                    <p class="margin-bottom-10">Feel free to Contact Us. We'd love to hear from you and
                                       answer any questions about CEP UoK fellowship, events, or how to get involved.
                                    </p>
                                 </div>
                              </div>
                              <div class="button-section margin-top-25">
                                 <a class="btn btn-default" href="<?= url('contact') ?>" title="Contact Us">Contact
                                    Us</a>
                              </div>
                           </div>
                        </div>
                        <!-- .col -->
                     </div>
                  </div>
               </section>
               <!-- Contact Form Section End -->

               <!-- Blog Section (News) -->
               <section class="blog-section pad-top-50 pad-bottom-95">
                  <div class="container">
                     <!-- Blog Wrap -->
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
                              <!--Blog Main Slider-->
                              <div class="owl-carousel blog-main-wrapper blog-style-1" data-loop="1" data-nav="0"
                                 data-dots="1" data-autoplay="0" data-autoplaypause="1" data-autoplaytime="5000"
                                 data-smartspeed="1000" data-margin="30" data-items="3" data-items-tab="2"
                                 data-items-mob="1">

                                 <?php foreach (array_slice($latestNews, 0, 6) as $news): ?>
                                    <!--Item-->
                                    <div class="item">
                                       <!--Blog Inner-->
                                       <div class="blog-inner">
                                          <div class="blog-thumb relative">
                                             <img src="<?= img_url($news['image_url']) ?>" class="img-fluid" width="768"
                                                height="600" alt="<?= htmlspecialchars($news['title']) ?>" />
                                             <div class="top-meta">
                                                <ul class="top-meta-list">
                                                   <li>
                                                      <div class="post-date">
                                                         <a href="<?= url('news') ?>">
                                                            <i class="ti-calendar"></i>
                                                            <?= date('M d, Y', strtotime($news['published_date'])) ?>
                                                         </a>
                                                      </div>
                                                   </li>
                                                </ul>
                                             </div>
                                          </div>
                                          <div class="blog-details">
                                             <div class="blog-title">
                                                <h4 class="margin-bottom-10">
                                                   <a href="<?= url('news') ?>" class="blog-name">
                                                      <?= htmlspecialchars($news['title']) ?>
                                                   </a>
                                                </h4>
                                             </div>
                                             <div class="post-desc mt-2">
                                                <div class="blog-link">
                                                   <a href="<?= url('news') ?>" class="link font-w-500">Read More</a>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <!--Blog Inner Ends-->
                                    </div>
                                    <!--Item Ends-->
                                 <?php endforeach; ?>

                              </div>
                           </div>
                        </div>
                     </div>
                     <!-- Blog Wrap -->
                  </div>
               </section>
               <!-- Blog Section End -->

               <!-- Testimonials Section (New) -->
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
                           <button class="carousel-nav prev" onclick="moveCarousel(-1)">
                              <i class="ti-angle-left"></i>
                           </button>
                           <button class="carousel-nav next" onclick="moveCarousel(1)">
                              <i class="ti-angle-right"></i>
                           </button>

                           <div class="carousel-container">
                              <div class="carousel-track" id="testimonialTrack">
                                 <?php foreach ($testimonials as $testimonial): ?>
                                    <div class="testimonial-slide">
                                       <div class="testimonial-avatar">
                                          <?php if ($testimonial['image_url']): ?>
                                             <img src="<?= img_url($testimonial['image_url']) ?>"
                                                alt="<?= htmlspecialchars($testimonial['name']) ?>">
                                          <?php else: ?>
                                             <div class="avatar-placeholder">
                                                <?= strtoupper(substr($testimonial['name'], 0, 1)) ?>
                                             </div>
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
               <!-- Testimonials Section End -->

            </div>
         </div>
      </div>
      <!-- .page-wrapper-inner -->
   </div>
   <!--page-wrapper-->

   <!-- Footer -->
   <?php include_once get_layout('footer'); ?>

   <!-- Video Modal -->
   <div id="videoModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                                background: rgba(0,0,0,0.95); z-index: 9999; align-items: center; 
                                justify-content: center;">
      <div style="position: relative; width: 90%; max-width: 1200px; aspect-ratio: 16/9;">
         <button onclick="closeVideoModal()" style="position: absolute; top: -40px; right: 0; background: white; border: none; 
                        border-radius: 50%; width: 40px; height: 40px; cursor: pointer; font-size: 20px;">
            &times;
         </button>
         <iframe id="videoIframe" style="width: 100%; height: 100%; border: none; border-radius: 10px;"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
         </iframe>
      </div>
   </div>

   <!-- jQuery -->
   <?php include_once get_layout('scripts'); ?>

   <!-- HERO CAROUSEL SCRIPTS -->
   <?php include_once get_layout('hero-slider-scripts'); ?>

   <script>
      function openVideoModal(videoId) {
         const modal = document.getElementById('videoModal');
         const iframe = document.getElementById('videoIframe');
         iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1';
         modal.style.display = 'flex';
         document.body.style.overflow = 'hidden';
      }

      function closeVideoModal() {
         const modal = document.getElementById('videoModal');
         const iframe = document.getElementById('videoIframe');
         iframe.src = '';
         modal.style.display = 'none';
         document.body.style.overflow = 'auto';
      }

      // Close modal on escape key
      document.addEventListener('keydown', function (e) {
         if (e.key === 'Escape') {
            closeVideoModal();
         }
      });

      // Close modal on background click
      document.getElementById('videoModal').addEventListener('click', function (e) {
         if (e.target === this) {
            closeVideoModal();
         }
      });
   </script>

   <script>
      // Gallery Modal Functions
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
         const modal = document.getElementById('galleryModal');
         modal.style.display = 'none';
         document.body.style.overflow = 'auto';
      }

      function navigateGallery(direction) {
         currentGalleryIndex += direction;

         if (currentGalleryIndex < 0) {
            currentGalleryIndex = galleryImages.length - 1;
         } else if (currentGalleryIndex >= galleryImages.length) {
            currentGalleryIndex = 0;
         }

         const image = galleryImages[currentGalleryIndex];
         document.getElementById('galleryModalImage').src = '<?= BASE_URL ?>' + image.image_url;
         document.getElementById('galleryModalTitle').textContent = image.title;
         document.getElementById('galleryModalCategory').textContent = image.category;
      }

      // Close on ESC key
      document.addEventListener('keydown', function (e) {
         if (e.key === 'Escape') {
            closeGalleryModal();
         }
      });

      // Close on background click
      document.getElementById('galleryModal').addEventListener('click', function (e) {
         if (e.target === this) {
            closeGalleryModal();
         }
      });

      // Arrow keys navigation
      document.addEventListener('keydown', function (e) {
         const modal = document.getElementById('galleryModal');
         if (modal.style.display === 'block') {
            if (e.key === 'ArrowLeft') navigateGallery(-1);
            if (e.key === 'ArrowRight') navigateGallery(1);
         }
      });

      // Testimonial Carousel Functions 
      (function () {
         // Wait for DOM to be fully loaded
         document.addEventListener('DOMContentLoaded', function () {
            let currentSlide = 0;
            const track = document.getElementById('testimonialTrack');
            const slides = document.querySelectorAll('.testimonial-slide');
            const totalSlides = slides.length;
            let slidesToShow = 3;

            function updateSlidesToShow() {
               if (window.innerWidth <= 768) {
                  slidesToShow = 1;
               } else if (window.innerWidth <= 992) {
                  slidesToShow = 2;
               } else {
                  slidesToShow = 3;
               }
            }

            function moveCarousel(direction) {
               updateSlidesToShow();
               const maxSlides = Math.max(0, totalSlides - slidesToShow);

               currentSlide += direction;

               if (currentSlide < 0) {
                  currentSlide = maxSlides;
               } else if (currentSlide > maxSlides) {
                  currentSlide = 0;
               }

               updateCarousel();
            }

            function updateCarousel() {
               if (!track) return;

               const slideWidth = 100 / slidesToShow;
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
                  dot.addEventListener('click', () => {
                     currentSlide = i;
                     updateCarousel();
                  });
                  dotsContainer.appendChild(dot);
               }
            }

            function updateDots() {
               const dots = document.querySelectorAll('.carousel-dot');
               dots.forEach((dot, index) => {
                  dot.classList.toggle('active', index === currentSlide);
               });
            }

            function initCarousel() {
               if (!track || slides.length === 0) {
                  console.log('Carousel not found or no slides');
                  return;
               }

               console.log('Initializing carousel with', totalSlides, 'slides');

               updateSlidesToShow();
               createDots();
               updateCarousel();

               // Set initial slide widths
               slides.forEach(slide => {
                  slide.style.flex = `0 0 calc(${100 / slidesToShow}% - ${(20 * (slidesToShow - 1)) / slidesToShow}px)`;
               });

               // Add event listeners to buttons (remove old onclick first)
               const prevBtn = document.querySelector('.carousel-nav.prev');
               const nextBtn = document.querySelector('.carousel-nav.next');

               if (prevBtn) {
                  prevBtn.onclick = null; // Remove old onclick
                  prevBtn.addEventListener('click', function (e) {
                     e.preventDefault();
                     moveCarousel(-1);
                  });
               }

               if (nextBtn) {
                  nextBtn.onclick = null; // Remove old onclick
                  nextBtn.addEventListener('click', function (e) {
                     e.preventDefault();
                     moveCarousel(1);
                  });
               }

               // Auto-play carousel (optional)
               setInterval(() => {
                  moveCarousel(1);
               }, 10000);
            }

            // Initialize when ready
            if (document.readyState === 'loading') {
               document.addEventListener('DOMContentLoaded', initCarousel);
            } else {
               initCarousel();
            }

            // Re-initialize on window resize
            window.addEventListener('resize', function () {
               updateSlidesToShow();
               createDots();
               updateCarousel();

               // Update slide widths on resize
               slides.forEach(slide => {
                  slide.style.flex = `0 0 calc(${100 / slidesToShow}% - ${(20 * (slidesToShow - 1)) / slidesToShow}px)`;
               });
            });

            // Make moveCarousel available globally (for debugging)
            window.moveCarousel = moveCarousel;
         });
      })();
   </script>

</body>

</html>