<!DOCTYPE html>
<html lang="en">

<?php
// CEP UOK WEBSITE - HOMEPAGE
// Include paths configuration
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";

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
               <!-- Events Section -->
               <section class="events-section pad-tb-0 broken-top-50 pt-sm-5 pt-xl-0 pad-bottom-md-none">
                  <div class="container">
                     <!-- Row -->
                     <div class="row">
                        <!--Events Main Slider-->
                        <div class="owl-carousel events-main-wrapper events-style-1" data-loop="1" data-nav="0"
                           data-dots="1" data-autoplay="0" data-autoplaypause="1" data-autoplaytime="5000"
                           data-smartspeed="1000" data-margin="30" data-items="2" data-items-tab="1" data-items-mob="1">
                           <!--Item-->
                           <div class="item">
                              <!--Events Inner-->
                              <div class="events-inner">
                                 <div class="events-item">
                                    <div class="media">
                                       <div class="event-date me-4">Jan 3<span class="event-time">8.00
                                             am</span> </div>
                                       <div class="media-body">
                                          <!-- Ministries Content -->
                                          <div class="event-content">
                                             <div class="event-title">
                                                <h5><a href="event-details.html">Our Sponsorship Meetup
                                                      Will Be Held Again</a></h5>
                                             </div>
                                             <div class="read-more"><a href="event-details.html">Event
                                                   Details</a></div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <!--Events Inner Ends-->
                           </div>
                           <!--Item Ends-->
                           <!--Item-->
                           <div class="item">
                              <!--Events Inner-->
                              <div class="events-inner">
                                 <div class="events-item">
                                    <div class="media">
                                       <div class="event-date me-4">Feb 12<span class="event-time">7.00
                                             am</span> </div>
                                       <div class="media-body">
                                          <!-- Ministries Content -->
                                          <div class="event-content">
                                             <div class="event-title">
                                                <h5><a href="event-details.html">Event: Reflect The
                                                      Community And Serving</a></h5>
                                             </div>
                                             <div class="read-more"><a href="event-details.html">Event
                                                   Details</a></div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <!--Events Inner Ends-->
                           </div>
                           <!--Item Ends-->
                           <!--Item-->
                           <div class="item">
                              <!--Events Inner-->
                              <div class="events-inner">
                                 <div class="events-item">
                                    <div class="media">
                                       <div class="event-date me-4">Mar 3<span class="event-time">6.00
                                             am</span> </div>
                                       <div class="media-body">
                                          <!-- Ministries Content -->
                                          <div class="event-content">
                                             <div class="event-title">
                                                <h5><a href="event-details.html">New Families During
                                                      National Adoption Month</a></h5>
                                             </div>
                                             <div class="read-more"><a href="event-details.html">Event
                                                   Details</a></div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <!--Events Inner Ends-->
                           </div>
                           <!--Item Ends-->
                           <!--Item-->
                           <div class="item">
                              <!--Events Inner-->
                              <div class="events-inner">
                                 <div class="events-item">
                                    <div class="media">
                                       <div class="event-date me-4">Apr 18<span class="event-time">12.00
                                             pm</span> </div>
                                       <div class="media-body">
                                          <!-- Ministries Content -->
                                          <div class="event-content">
                                             <div class="event-title">
                                                <h5><a href="event-details.html">Event: Lord is
                                                      Sufficient for all of our needs</a></h5>
                                             </div>
                                             <div class="read-more"><a href="event-details.html">Event
                                                   Details</a></div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <!--Events Inner Ends-->
                           </div>
                           <!--Item Ends-->
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
                                       <div class="about-img bf-pattern mb-5 mb-xl-0"> <img
                                             src="<?= img_url('about/about-1.jpeg') ?>" class="" alt="about-img">
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
                              <div class="section-title"> <span class="sub-title theme-color text-uppercase">About
                                    Us</span>
                                 <h2 class="section-title margin-top-5">Building Christ-Centered Leaders at the University of Kigali</h2> <span class="border-bottom"></span>
                              </div>
                              <div class="pad-top-15">
                                 <p class="margin-bottom-20">CEP–UoK (Communauté des Étudiants Pentecôtistes à l'Université de Kigali) is a Christian students' fellowship that brings together university students who desire to grow spiritually, live out their faith, and serve God within the academic environment.</p>
                                 <p class="styled-text">To raise Christ-centered leaders who honor God, uphold biblical values, and positively influence the Church, the University, and society.</p>
                              </div>
                           </div>
                           <div class="row">
                              <!-- Feature Box -->
                              <div class="col-md-6">
                                 <div class="feature-box-wrap f-box-style-1 mb-md-0 mb-sm-4 relative">
                                    <div class="feature-box-details">
                                       <div class="feature-icon margin-bottom-25"> <span
                                             class="ti-heart b-radius-50 d-block"></span> </div>
                                       <div class="feature-content">
                                          <div class="feature-title relative margin-bottom-15">
                                             <h4>Spiritual Growth</h4>
                                          </div>
                                          <p class="mb-0">We nurture students through prayer, worship, biblical teaching, and discipleship, creating a supportive community of faith.</p>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <!-- Feature Box End -->
                              <!-- Feature Box -->
                              <div class="col-md-6">
                                 <div class="feature-box-wrap f-box-style-1 relative">
                                    <div class="feature-box-details">
                                       <div class="feature-icon margin-bottom-25"> <span
                                             class="ti-book b-radius-50 d-block"></span> </div>
                                       <div class="feature-content">
                                          <div class="feature-title relative margin-bottom-15">
                                             <h4>Unity & Service</h4>
                                          </div>
                                          <p class="mb-0">We foster unity among believers and impact the university community through evangelism, outreach, and acts of love.</p>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <!-- Feature Box End -->
                           </div>
                           <div class="button-section margin-top-35"> <a class="btn btn-default" href="<?= url(path: 'about') ?>"
                                 title="Learn More">Learn More</a> </div>
                        </div>
                        <!-- Col -->
                     </div>
                     <!-- Row -->
                  </div>
                  <!-- Container -->
               </section>
               <!-- About Section End -->
               <!-- Get a Quote Section -->
               <section id="get-quote-section" class="get-quote-section section-bg-img" data-bg="images/bg/bg-1.jpg">
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
                                       <div class="video-icon">
                                          <a class="popup-youtube box-shadow1"
                                             href="https://www.youtube.com/watch?v=e1l1-LWTcBs"> <i
                                                class="ti-heart"></i> </a>
                                       </div>
                                    </div>
                                 </div>
                                 <!-- video details End-->
                              </div>
                              <!-- video wrap end -->
                              <div class="title-wrap mb-0">
                                 <div class="section-title typo-white margin-bottom-40">
                                    <h2 class="title mb-3">“Pray! And listen to God! You can do this alone,
                                       but find somebody to do it with you”</h2> <span class="dancing-text">Real Story
                                       Cross Journey from Anna
                                       Hampton</span>
                                 </div>
                                 <div class="get-quote-btn"> <a class="btn btn-default" href="donate-now.html"
                                       title="Donate Online">Donate Online</a>
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
               <!-- Ministries Section -->
               <section id="ministries-section" class="ministries-section pad-top-95 pad-bottom-70">
                  <div class="container">
                     <!-- Row -->
                     <div class="row">
                        <div class="offset-md-2 col-md-8">
                           <div class="title-wrap text-center">
                              <div class="section-title"> <span
                                    class="sub-title theme-color text-uppercase">Ministries</span>
                                 <h2 class="section-title margin-top-5">Our Ministries</h2> <span
                                    class="border-bottom center"></span>
                              </div>
                           </div>
                        </div>
                        <!--Ministries Main Slider-->
                        <div class="owl-carousel ministries-main-wrapper" data-loop="1" data-nav="1" data-dots="0"
                           data-autoplay="0" data-autoplaypause="1" data-autoplaytime="5000" data-smartspeed="1000"
                           data-margin="30" data-items="3" data-items-tab="2" data-items-mob="1">
                           <!--Item-->
                           <div class="item">
                              <div class="ministries-box-style-2">
                                 <!-- Ministries Inner -->
                                 <div class="ministries-inner">
                                    <div class="ministries-thumb"> <img class="img-fluid squared w-100"
                                          src="images/ministries/childrens_ministry.jpg" width="360" height="240"
                                          alt="Agricultural Processing"> </div>
                                    <!-- Ministries Content -->
                                    <div class="ministries-content pad-30">
                                       <div class="ministries-title margin-bottom-15">
                                          <h4><a href="childrens-ministry.html" class="ministries-link">Children's
                                                Ministry</a></h4>
                                       </div>
                                       <div class="ministries-desc">
                                          <p>Children’s ministry is the most important ministry in our
                                             church. This ministry helps kids learn about the Lord</p>
                                       </div>
                                       <div class="ministries-link margin-top-20"> <a target="_blank"
                                             href="childrens-ministry.html" class="link">Read More</a>
                                       </div>
                                    </div>
                                 </div>
                                 <!-- Ministries Inner Ends -->
                              </div>
                           </div>
                           <!--Item Ends-->
                           <!--Item-->
                           <div class="item">
                              <div class="ministries-box-style-2">
                                 <!-- Ministries Inner -->
                                 <div class="ministries-inner">
                                    <div class="ministries-thumb"> <img class="img-fluid squared w-100"
                                          src="images/ministries/women_ministry.jpg" width="360" height="240"
                                          alt="Agricultural Processing"> </div>
                                    <!-- Ministries Content -->
                                    <div class="ministries-content pad-30">
                                       <div class="ministries-title margin-bottom-15">
                                          <h4><a href="womens-ministry.html" class="ministries-link">Womens Ministry</a>
                                          </h4>
                                       </div>
                                       <div class="ministries-desc">
                                          <p>A Women’s ministry includes hosting Bible studies, services,
                                             support group, and gathering events. The goal of this
                                             women’s</p>
                                       </div>
                                       <div class="ministries-link margin-top-20"> <a target="_blank"
                                             href="womens-ministry.html" class="link">Read More</a>
                                       </div>
                                    </div>
                                 </div>
                                 <!-- Ministries Inner Ends -->
                              </div>
                           </div>
                           <!--Item Ends-->
                           <!--Item-->
                           <div class="item">
                              <div class="ministries-box-style-2">
                                 <!-- Ministries Inner -->
                                 <div class="ministries-inner">
                                    <div class="ministries-thumb"> <img class="img-fluid squared w-100"
                                          src="images/ministries/global_ministry.jpg" width="360" height="240"
                                          alt="Agricultural Processing"> </div>
                                    <!-- Ministries Content -->
                                    <div class="ministries-content pad-30">
                                       <div class="ministries-title margin-bottom-15">
                                          <h4><a href="global-ministry.html" class="ministries-link">Global Ministry</a>
                                          </h4>
                                       </div>
                                       <div class="ministries-desc">
                                          <p>We are very grateful for our part in the work of Global
                                             Ministries and the opportunity to be</p>
                                       </div>
                                       <div class="ministries-link margin-top-20"> <a target="_blank"
                                             href="global-ministry.html" class="link">Read More</a>
                                       </div>
                                    </div>
                                 </div>
                                 <!-- Ministries Inner Ends -->
                              </div>
                           </div>
                           <!--Item Ends-->
                           <!--Item-->
                           <div class="item">
                              <div class="ministries-box-style-2">
                                 <!-- Ministries Inner -->
                                 <div class="ministries-inner">
                                    <div class="ministries-thumb"> <img class="img-fluid squared w-100"
                                          src="images/ministries/music_ministry.jpg" width="360" height="240"
                                          alt="Agricultural Processing"> </div>
                                    <!-- Ministries Content -->
                                    <div class="ministries-content pad-30">
                                       <div class="ministries-title margin-bottom-15">
                                          <h4><a href="music-ministry.html" class="ministries-link">Music
                                                Ministry</a></h4>
                                       </div>
                                       <div class="ministries-desc">
                                          <p>Music is something we get exposed to from childhood through
                                             adulthood. From the pattering sounds from raindrops</p>
                                       </div>
                                       <div class="ministries-link margin-top-20"> <a target="_blank"
                                             href="music-ministry.html" class="link">Read More</a> </div>
                                    </div>
                                 </div>
                                 <!-- Ministries Inner Ends -->
                              </div>
                           </div>
                           <!--Item Ends-->
                        </div>
                        <!--Ministries Owl Slider-->
                     </div>
                     <!-- Row -->
                  </div>
                  <!-- Container -->
               </section>
               <!-- Ministries Section End -->
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
                                    <div class="flip-box-icon margin-bottom-40"><span
                                          class="text-center flip-icon-middle ti-headphone-alt"></span>
                                    </div>
                                    <h3 class="flip-box-title margin-bottom-30">Call Us</h3>
                                    <div class="flip-content">
                                       <p>684 West College St. Sun City, USA.</p>
                                       <p><a href="tel:+8(123)985789">+8 (123) 985 789</a></p>
                                       <p><a
                                             href="https://zozothemes.com/cdn-cgi/l/email-protection#e79d82808289848f9295848fa78a868e8bc984888a"><span
                                                class="__cf_email__"
                                                data-cfemail="b9c3dcdedcd7dad1cccbdad1f9d4d8d0d597dad6d4">[email&#160;protected]</span></a>
                                       </p>
                                    </div>
                                 </div>
                                 <div class="flip-box-back">
                                    <h3 class="flip-box-title">Call Us</h3>
                                    <div class="flip-content">
                                       <p>684 West College St. Sun City, USA.</p>
                                       <p><a href="tel:+8(123)985789">+8 (123) 985 789</a></p>
                                       <p><a
                                             href="https://zozothemes.com/cdn-cgi/l/email-protection#a8d2cdcfcdc6cbc0dddacbc0e8c5c9c1c486cbc7c5"><span
                                                class="__cf_email__"
                                                data-cfemail="4f352a282a212c273a3d2c270f222e2623612c2022">[email&#160;protected]</span></a>
                                       </p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <!-- col -->
                        <div class="col-xl-8 ps-xl-4">
                           <div class="section-title-wrapper">
                              <div class="title-wrap mb-0">
                                 <div class="section-title"> <span class="sub-title theme-color text-uppercase">Get In
                                       Touch</span>
                                    <h2 class="section-title margin-top-5">Don't hesitate Contact Us</h2>
                                    <span class="border-bottom"></span>
                                 </div>
                                 <div class="pad-top-15">
                                    <p class="margin-bottom-10">Feel free to Contact Us. Zegen Church WP
                                       Theme comes with sermons, ministries, events, testimonies, staff
                                       members, church locations shortcodes to enhance your website.</p>
                                 </div>
                              </div>
                              <div class="button-section margin-top-25"> <a class="btn btn-default"
                                    href="contact-us.html" title="Learn More">Contact Us</a> </div>
                           </div>
                        </div>
                        <!-- .col -->
                     </div>
                  </div>
               </section>
               <!-- Contact Form Section End -->
               <!-- Blog Section -->
               <section class="blog-section pad-top-50 pad-bottom-95">
                  <div class="container">
                     <!-- Blog Wrap -->
                     <div class="row">
                        <div class="col-md-12">
                           <div class="title-wrap text-center">
                              <div class="section-title"> <span class="sub-title theme-color text-uppercase">Our
                                    Blog</span>
                                 <h2 class="section-title margin-top-5">Latest Posts</h2> <span
                                    class="border-bottom center"></span>
                              </div>
                           </div>
                           <div class="row">
                              <!--Blog Main Slider-->
                              <div class="owl-carousel blog-main-wrapper blog-style-1" data-loop="1" data-nav="0"
                                 data-dots="1" data-autoplay="0" data-autoplaypause="1" data-autoplaytime="5000"
                                 data-smartspeed="1000" data-margin="30" data-items="3" data-items-tab="2"
                                 data-items-mob="1">
                                 <!--Item-->
                                 <div class="item">
                                    <!--Blog Inner-->
                                    <div class="blog-inner">
                                       <div class="blog-thumb relative"> <img src="images/blog/blog-grid/blog-grid1.jpg"
                                             class="img-fluid" width="768" height="600" alt="blog-img" />
                                          <div class="top-meta">
                                             <ul class="top-meta-list">
                                                <li>
                                                   <div class="post-date"><a href="blog-single.html"><i
                                                            class="ti-calendar"></i> Oct 21,
                                                         2019</a></div>
                                                </li>
                                             </ul>
                                          </div>
                                       </div>
                                       <div class="blog-details">
                                          <div class="blog-title">
                                             <h4 class="margin-bottom-10"><a href="blog-single.html"
                                                   class="blog-name">Giving Back – Uganda Training
                                                   Centers</a></h4>
                                          </div>
                                          <div class="post-desc mt-2">
                                             <div class="blog-link"> <a target="_blank" href="blog-single.html"
                                                   class="link font-w-500">Read
                                                   More</a> </div>
                                          </div>
                                       </div>
                                    </div>
                                    <!--Blog Inner Ends-->
                                 </div>
                                 <!--Item Ends-->
                                 <!--Item-->
                                 <div class="item">
                                    <!--Blog Inner-->
                                    <div class="blog-inner">
                                       <div class="blog-thumb relative"> <img src="images/blog/blog-grid/blog-grid2.jpg"
                                             class="img-fluid" width="768" height="600" alt="blog-img" />
                                          <div class="top-meta">
                                             <ul class="top-meta-list">
                                                <li>
                                                   <div class="post-date"><a href="blog-single.html"><i
                                                            class="ti-calendar"></i> Oct 21,
                                                         2019</a></div>
                                                </li>
                                             </ul>
                                          </div>
                                       </div>
                                       <div class="blog-details">
                                          <div class="blog-title">
                                             <h4 class="margin-bottom-10"><a href="blog-single.html"
                                                   class="blog-name">Spirit Of The Lord Is, From The
                                                   New Life</a></h4>
                                          </div>
                                          <div class="post-desc mt-2">
                                             <div class="blog-link"> <a target="_blank" href="blog-single.html"
                                                   class="link font-w-500">Read
                                                   More</a> </div>
                                          </div>
                                       </div>
                                    </div>
                                    <!--Blog Inner Ends-->
                                 </div>
                                 <!--Item Ends-->
                                 <!--Item-->
                                 <div class="item">
                                    <!--Blog Inner-->
                                    <div class="blog-inner">
                                       <div class="blog-thumb relative"> <img src="images/blog/blog-grid/blog-grid3.jpg"
                                             class="img-fluid" width="768" height="600" alt="blog-img" />
                                          <div class="top-meta">
                                             <ul class="top-meta-list">
                                                <li>
                                                   <div class="post-date"><a href="blog-single.html"><i
                                                            class="ti-calendar"></i> Oct 20,
                                                         2019</a></div>
                                                </li>
                                             </ul>
                                          </div>
                                       </div>
                                       <div class="blog-details">
                                          <div class="blog-title">
                                             <h4 class="margin-bottom-10"><a href="blog-single.html"
                                                   class="blog-name">Help End the Water Crisis For
                                                   Families</a></h4>
                                          </div>
                                          <div class="post-desc mt-2">
                                             <div class="blog-link"> <a target="_blank" href="blog-single.html"
                                                   class="link font-w-500">Read
                                                   More</a> </div>
                                          </div>
                                       </div>
                                    </div>
                                    <!--Blog Inner Ends-->
                                 </div>
                                 <!--Item Ends-->
                                 <!--Item-->
                                 <div class="item">
                                    <!--Blog Inner-->
                                    <div class="blog-inner">
                                       <div class="blog-thumb relative"> <img src="images/blog/blog-grid/blog-grid4.jpg"
                                             class="img-fluid" width="768" height="600" alt="blog-img" />
                                          <div class="top-meta">
                                             <ul class="top-meta-list">
                                                <li>
                                                   <div class="post-date"><a href="blog-single.html"><i
                                                            class="ti-calendar"></i> Oct 19,
                                                         2019</a></div>
                                                </li>
                                             </ul>
                                          </div>
                                       </div>
                                       <div class="blog-details">
                                          <div class="blog-title">
                                             <h4 class="margin-bottom-10"><a href="blog-single.html"
                                                   class="blog-name">Pray for Help to Save Mattia's
                                                   Life</a></h4>
                                          </div>
                                          <div class="post-desc mt-2">
                                             <div class="blog-link"> <a target="_blank" href="blog-single.html"
                                                   class="link font-w-500">Read
                                                   More</a> </div>
                                          </div>
                                       </div>
                                    </div>
                                    <!--Blog Inner Ends-->
                                 </div>
                                 <!--Item Ends-->
                              </div>
                           </div>
                        </div>
                     </div>
                     <!-- Blog Wrap -->
                  </div>
               </section>
               <!-- Blog Section End -->
            </div>
         </div>
      </div>
      <!-- .page-wrapper-inner -->
   </div>
   <!--page-wrapper-->
   <!-- Footer -->
   <?php include_once get_layout('footer'); ?>
   



   

   <!-- jQuery -->
   <?php include_once get_layout('scripts'); ?>

   <!-- HERO CAROUSEL SCRIPTS -->
   <?php include_once get_layout('hero-slider-scripts'); ?>


</body>

</html>