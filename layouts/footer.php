<?php
// Fetch site settings
if (!isset($settings)) {
   require_once get_db('database');
   $db = Database::getInstance();
   $query = "SELECT setting_key, setting_value FROM site_settings";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $settingsArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
   $settings = [];
   foreach ($settingsArray as $row) {
      $settings[$row['setting_key']] = $row['setting_value'];
   }
}
?>
<!-- Footer -->
<footer id="footer" class="footer footer-1 footer-bg-img" data-bg="<?= img_url('bg/footer-bg.jpg') ?>">
   <!--Footer Widgets Columns-->
   <div class="footer-widgets">
      <div class="footer-middle-wrap footer-overlay-dark">
         <div class="color-overlay"></div>
         <div class="container">
            <div class="row">
               <!-- About CEP UoK -->
               <div class="col-lg-3 widget text-widget">
                  <div class="widget-title">
                     <h3 class="title typo-white">About CEP UoK</h3>
                  </div>
                  <div class="widget-text margin-bottom-30">
                     <p><?= $settings['footer_about'] ?? 'CEP UoK is a vibrant Christian students\' fellowship at the University of Kigali, nurturing spiritual growth, leadership development, and kingdom impact.' ?></p>
                  </div>
                  <div class="social-icons">
                     <a href="<?= $settings['social_facebook'] ?? '#' ?>" target="_blank" class="social-fb" title="Facebook">
                        <span class="ti-facebook"></span>
                     </a>
                     <a href="<?= $settings['social_twitter'] ?? '#' ?>" target="_blank" class="social-twitter" title="Twitter/X">
                        <!-- <span class="ti-twitter"></span> -->
                        <svg width="14" style="color: white;" height="14" viewBox="0 0 1200 1227" fill="currentColor">
                           <path d="M714.163 519.284L1160.89 0H1055.03L667.137 450.887L357.328 0H0L468.492 681.821L0 1226.37H105.866L515.491 750.218L842.672 1226.37H1200L714.137 519.284H714.163ZM569.165 687.828L521.697 619.934L144.011 79.6944H306.615L611.412 515.685L658.88 583.579L1055.08 1150.3H892.476L569.165 687.854V687.828Z"/>
                        </svg>
                     </a>
                     <a href="<?= $settings['social_instagram'] ?? '#' ?>" target="_blank" class="social-instagram" title="Instagram">
                        <span class="ti-instagram"></span>
                     </a>
                     <a href="<?= $settings['social_youtube'] ?? '#' ?>" target="_blank" class="social-youtube" title="YouTube">
                        <span class="ti-youtube"></span>
                     </a>
                  </div>
               </div>
               
               <!-- Quick Links -->
               <div class="col-lg-3 widget text-widget">
                  <div class="widget-title">
                     <h3 class="title typo-white">Quick Links</h3>
                  </div>
                  <div class="menu-quick-links">
                     <ul class="menu">
                        <li class="menu-item"><a href="<?= url('about') ?>">About Us</a></li>
                        <li class="menu-item"><a href="<?= url('departments') ?>">Our Departments</a></li>
                        <li class="menu-item"><a href="<?= url('news') ?>">News & Events</a></li>
                        <li class="menu-item"><a href="<?= url('gallery') ?>">Gallery</a></li>
                        <li class="menu-item"><a href="<?= url('contact') ?>">Contact Us</a></li>
                     </ul>
                  </div>
               </div>
               
               <!-- Fellowship Schedule -->
               <div class="col-lg-3 widget text-widget">
                  <div class="widget-title">
                     <h3 class="title typo-white">Fellowship Times</h3>
                  </div>
                  <ul class="footer-list-posts">
                     <li class="mb-3">
                        <div class="side-item-text">
                           <strong class="typo-white">Monday</strong>
                           <p class="text-muted mb-0">English Service</p>
                           <small>Kacyiru Campus | 11:30 AM</small>
                        </div>
                     </li>
                     <li class="mb-3">
                        <div class="side-item-text">
                           <strong class="typo-white">Wednesday</strong>
                           <p class="text-muted mb-0">Kinyarwanda Fellowship</p>
                           <small>Kacyiru Campus | 11:30 AM</small>
                        </div>
                     </li>
                     <li class="mb-3">
                        <div class="side-item-text">
                           <strong class="typo-white">Thursday</strong>
                           <p class="text-muted mb-0">Kinyarwanda Fellowship</p>
                           <small>Remera Campus | 11:30 AM</small>
                        </div>
                     </li>
                     <li class="mb-3">
                        <div class="side-item-text">
                           <strong class="typo-white">Sunday</strong>
                           <p class="text-muted mb-0">Sunday Service</p>
                           <small>Kacyiru Campus | 2:00 PM</small>
                        </div>
                     </li>
                  </ul>
               </div>
               
               <!-- Contact Info -->
               <div class="col-lg-3 widget contact-info-widget">
                  <div class="widget-title">
                     <h3 class="title typo-white">Contact Us</h3>
                  </div>
                  <div class="contact-info-list">
                     <div class="contact-item mb-3">
                        <i class="ti-location-pin typo-white mr-2"></i>
                        <span><?= $settings['contact_address'] ?? 'KG 541 St, Kigali, Rwanda' ?></span>
                     </div>
                     <div class="contact-item mb-3">
                        <i class="ti-email typo-white mr-2"></i>
                        <a href="mailto:<?= $settings['contact_email'] ?? 'cepuok01@gmail.com' ?>">
                           <?= $settings['contact_email'] ?? 'cepuok01@gmail.com' ?>
                        </a>
                     </div>
                     <div class="contact-item mb-3">
                        <i class="ti-mobile typo-white mr-2"></i>
                        <a href="tel:<?= str_replace(' ', '', $settings['contact_phone1'] ?? '+250791619272') ?>">
                           <?= $settings['contact_phone1'] ?? '+250 791 619 272' ?>
                        </a>
                     </div>
                     <?php if (isset($settings['contact_phone2'])): ?>
                     <div class="contact-item mb-3">
                        <i class="ti-mobile typo-white mr-2"></i>
                        <a href="tel:<?= str_replace(' ', '', $settings['contact_phone2']) ?>">
                           <?= $settings['contact_phone2'] ?>
                        </a>
                     </div>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   
   <!--Footer Copyright-->
   <div class="footer-copyright">
      <div class="footer-bottom-wrap pad-tb-20 typo-white">
         <div class="container">
            <div class="row">
               <div class="col-md-12 text-center">
                  <ul class="footer-bottom-items">
                     <li class="nav-item">
                        <div class="nav-item-inner">
                           <?= $settings['footer_copyright'] ?? 'Copyright © <span id="copy-year" 2025</span> CEP UoK. All rights reserved.' ?>
							<span class="designed-by">Designed by</span> <a href="https://mushyagroup.com/">
								<span class="designer-color"> <strong> MUSHYA GROUP LTD</strong></span>
							</a>
                           <br>
                           <small>Under the spiritual supervision of ADEPR Kimihurura International Service</small>
                        </div>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
      </div>
   </div>
</footer>
<!-- Footer End -->