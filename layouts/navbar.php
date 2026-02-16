<!--Sticky part-->
<div class="sticky-outer">
   <div class="sticky-head">
      <!--Navbar-->
      <nav class="navbar nav-shadow">
         <div class="basic-container clearfix">
            <div class="">
               <!--Overlay Menu Switch-->
               <ul class="nav navbar-items pull-left">
                  <li class="list-item">
                     <a href="<?= url() ?>" class="logo-general"><img src="<?= img_url('logo-only.png') ?>"
                           class="img-fluid changeable-light" width="166" height="50" alt="Logo" /></a>
                     <a href="<?= url() ?>" class="logo-sticky"><img src="<?= img_url('logo-only.png') ?>"
                           class="img-fluid changeable-dark" width="166" height="50" alt="Logo" /></a>
                  </li>
               </ul>
               <!-- Menu -->
               <ul class="nav navbar-items pull-right">
                  <!--List Item-->
                  <li class="list-item">
                     <ul class="nav navbar-main menu-white">
                        <li><a href="<?= url() ?>">Home</a></li>
                        <li class="dropdown dropdown-sub"><a href="#">About</a>
                           <ul class="dropdown-menu">
                              <li class="dropdown"><a href="<?= url('about-cep') ?>">CEP INFO</a>
                                 <ul class="dropdown-menu child-dropdown-menu">
                                    <li><a href="<?= url(path: 'about-cep') ?>">Who We Are</a></li>
                                    <li><a href="<?= url(path: 'history') ?>">History</a></li>
                                 </ul>
                              </li>
                              <li class="dropdown"><a href="<?= url(path: 'gallery-photo') ?>">Gallery</a>
                                 <ul class="dropdown-menu child-dropdown-menu">
                                    <li><a href="<?= url(path: 'gallery-photo') ?>">Photo Gallery</a></li>
                                    <li><a href="<?= url(path: 'gallery-video') ?>">Video Gallery</a></li>
                                 </ul>
                              </li>
                              <li><a href="<?= url(path: 'leadership') ?>">Leadership</a></li>
                              <li> <a href="<?= url(path: 'local-church') ?>">Local church</a> </li>
                           </ul>
                        </li>
                        <li class="dropdown dropdown-sub"><a href="<?= url(path: 'departments') ?>">Departments</a>
                           <ul class="dropdown-menu">
                              <li class="dropdown"> <a href="<?= url(path: 'evangelism') ?>">Evangelism</a>
                                 <ul class="dropdown-menu">
                                    <li><a href="<?= url(path: 'evangelism-outreach') ?>">Outreach</a></li>
                                    <li><a href="<?= url(path: 'evangelism-sermons') ?>">Sermons</a></li>
                                    <li><a href="<?= url(path: 'evangelism-seminars') ?>">Seminars</a></li>
                                    <li><a href="<?= url(path: 'events') ?>">Conference and events</a></li>
                                 </ul>
                              </li>
                              <li class="dropdown"> <a href="<?= url(path: 'social-welfare') ?>">Social Affairs</a>
                                 <ul class="dropdown-menu">
                                    <li><a href="<?= url(path: 'social-welfare') ?>">Social Welfare</a>
                                    </li>
                                    <li><a href="<?= url(path: 'social-community') ?>">Community Work</a>
                                    </li>
                                    <li><a href="<?= url(path: 'social-families') ?>">Families</a>
                                    </li>
                                 </ul>
                              </li>
                              <li> <a href="<?= url(path: 'choir') ?>">Choir</a> </li>
                              <li> <a href="<?= url(path: 'protocol') ?>">Protocol</a> </li>
                              <li> <a href="<?= url(path: 'team-media') ?>">Media Team</a> </li>
                              <li> <a href="<?= url(path: 'team-worship') ?>">Worship Team</a> </li>
                           </ul>
                        </li>
                        <li class="dropdown mega-dropdown dropdown-sub relative"><a
                              href="#">Fellowship</a>
                           <ul class="dropdown-menu mega-dropdown-menu dropdown-col-2">
                              <li class="mega-dropdown-col">
                                 <a href="#" class="text-uppercase theme-color">Day Campus Fellowship</a>
                                 <ul class="mega-child-dropdown-menu">
                                    <li><a href="<?= url(path: 'service-day-english') ?>">English Service</a></li>
                                    <li><a href="<?= url(path: 'service-day-kinyarwanda') ?>">Kinyarwanda Service</a></li>
                                    <li><a href="<?= url(path: 'service-day-prayers') ?>">Intercessory Prayers</a></li>
                                 </ul>
                              </li>
                              <li class="mega-dropdown-col">
                                 <a href="#" class="text-uppercase theme-color">Weekend Services</a>
                                 <ul class="mega-child-dropdown-menu">
                                    <li><a href="<?= url(path: 'service-weekend-fellowship') ?>">Weekend Fellowship</a></li>
                                    <li><a href="<?= url(path: 'service-weekend-prayers') ?>">Weekend Prayer Session</a></li>
                                 </ul>
                              </li>
                           </ul>
                        </li>
                        <li class="dropdown dropdown-sub"><a href="<?= url(path: 'news') ?>">News</a>
                           <ul class="dropdown-menu">
                              <li><a href="<?= url(path: 'events') ?>">Events</a></li>
                              <li><a href="<?= url(path: 'outreach') ?>">Outreach</a></li>
                              <li><a href="<?= url(path: 'news') ?>">News & Announcements</a></li>
                           </ul>
                        </li>
                        <li><a href="<?= url(path: 'contact') ?>">Contact Us</a></li>
                     </ul>
                  </li>
                  <!--List Item End-->
                  <!--List Item-->
                  <li class="list-item">
                     <div class="header-navbar-text-1">
                        <a href="<?= url(path: 'membership') ?>" class="h-donate-btn">Membership</a>
                     </div>
                  </li>
                  <!--List Item End-->
               </ul>
               <!-- Menu -->
            </div>
         </div>
      </nav>
   </div>
   <!--sticky-head-->
</div>
<!--sticky-outer-->