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
<!--Topbar-->
<div class="topbar relative">
   <div class="basic-container clearfix">
      <ul class="nav topbar-items pull-left">
         <li class="nav-item">
            <ul class="nav header-info">
               <li>
                  <div class="header-address typo-white">
                     <span class="ti-location-pin"></span>
                     <?= $settings['contact_address'] ?? 'KG 541 St, Kigali, Rwanda' ?>
                  </div>
               </li>
               <li>
                  <div class="header-phone typo-white">
                     <span class="ti-mobile"></span>
                     <?= $settings['contact_phone1'] ?? '+250 791 619 272' ?>
                  </div>
               </li>
            </ul>
         </li>
      </ul>
      <ul class="nav topbar-items pull-right">
         <li class="nav-item">
            <div class="social-icons typo-white">
               <a href="<?= $settings['social_facebook'] ?? '#' ?>" target="_blank" class="social-fb" 
                  title="Facebook">
                  <span class="ti-facebook"></span>
               </a>
               <a href="<?= $settings['social_twitter'] ?? '#' ?>" target="_blank" class="social-twitter"
                  title="Twitter/X">
                  <svg width="14" style="color: white;" height="14" viewBox="0 0 1200 1227" fill="currentColor">
                     <path d="M714.163 519.284L1160.89 0H1055.03L667.137 450.887L357.328 0H0L468.492 681.821L0 1226.37H105.866L515.491 750.218L842.672 1226.37H1200L714.137 519.284H714.163ZM569.165 687.828L521.697 619.934L144.011 79.6944H306.615L611.412 515.685L658.88 583.579L1055.08 1150.3H892.476L569.165 687.854V687.828Z"/>
                  </svg>
               </a>
               <a href="<?= $settings['social_instagram'] ?? '#' ?>" target="_blank" class="social-instagram"
                  title="Instagram">
                  <span class="ti-instagram"></span>
               </a>
               <a href="<?= $settings['social_youtube'] ?? '#' ?>" target="_blank" class="social-youtube"
                  title="YouTube">
                  <span class="ti-youtube"></span>
               </a>
            </div>
         </li>
         <li><a href="#" class="full-view-switch text-center"><i class="ti-search typo-white"></i></a></li>
      </ul>
   </div>
   
   <!--Search-->
   <div class="full-view-wrapper hide">
      <a href="#" class="close full-view-close"></a>
      <form class="navbar-form search-form" role="search" action="<?= url('search') ?>" method="GET">
         <div class="input-group">
            <input class="form-control" placeholder="Search CEP UoK..." name="q" type="text">
         </div>
      </form>
   </div>
</div>
<!--Topbar-->