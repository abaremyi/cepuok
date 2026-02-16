

<p class="rs-p-wp-fix"></p>
<rs-module-wrap id="rev_slider_1_1_wrapper" data-alias="cep-home-slider" data-source="gallery" 
   style="visibility:hidden;background:#000000;padding:0;margin:0px auto;margin-top:0;margin-bottom:0;">
   <rs-module id="rev_slider_1_1" style="" data-version="6.5.31">
      <rs-slides>
         <?php 
         $slideIndex = 1;
         foreach ($sliders as $slider): 
            $slideKey = "rs-" . $slideIndex;
         ?>
         <rs-slide style="position: absolute;" data-key="<?= $slideKey ?>" 
            data-title="<?= htmlspecialchars($slider['title']) ?>" 
            data-thumb="rs-plugin/assets/zmain-slider-<?= $slideIndex ?>-1536x864-100x100.jpg" 
            data-anim="adpr:false;e:slidingoverlay;ms:2000;" 
            data-in="o:1;x:(100%);" 
            data-out="a:false;">
            
            <img src="rs-plugin/assets/dummy.png" 
                 alt="<?= htmlspecialchars($slider['title']) ?>" 
                 title="<?= htmlspecialchars($slider['title']) ?>" 
                 width="1536" height="864" 
                 class="rev-slidebg tp-rs-img rs-lazyload" 
                 data-lazyload="rs-plugin/assets/zmain-slider-<?= $slideIndex ?>-1536x864.jpg"
                 data-parallax="5" data-no-retina>
            
            <!-- Title -->
            <h1 id="slider-<?= $slideIndex ?>-slide-<?= $slideIndex ?>-layer-2" 
                class="rs-layer Concept-Title" 
                data-type="text" 
                data-color="#ffffff||rgba(255, 255, 255, 1)||rgba(255, 255, 255, 1)||rgba(255, 255, 255, 1)" 
                data-rsp_ch="on" 
                data-xy="x:c;y:m;yo:10px,-26px,-10px,-33px;"
                data-text="w:normal,nowrap,nowrap,normal;s:50,48,45,30;l:52,55,50,40;ls:1px;fw:700;a:center;" 
                data-dim="w:754px,699px,auto,400px;" 
                data-padding="b:10;" 
                data-frame_0="sX:2;sY:2;" 
                data-frame_0_mask="u:t;" 
                data-frame_1="e:power2.out;st:2110;sp:1270;sR:2110;"
                data-frame_1_mask="u:t;" 
                data-frame_999="x:left;e:power3.in;st:w;sp:1000;sR:5620;" 
                data-frame_999_reverse="x:true;" 
                style="z-index:10;font-family:'Poppins';text-transform:uppercase;">
               <?= htmlspecialchars($slider['title']) ?>
            </h1>
            
            <!-- Subtitle -->
            <rs-layer id="slider-<?= $slideIndex ?>-slide-<?= $slideIndex ?>-layer-4" 
                      class="Concept-SubTitle" 
                      data-type="text" 
                      data-rsp_ch="on" 
                      data-xy="x:c;y:m;yo:-42px,-83px,-64px,-71px;" 
                      data-text="w:normal,nowrap,nowrap,nowrap;s:21,20,20,15;l:21,25,20,20;fw:700;a:center,left,left,left;"
                      data-dim="w:408px,auto,auto,auto;" 
                      data-padding="b:10;" 
                      data-frame_0="o:1;" 
                      data-frame_0_chars="d:5;y:100%;o:0;rZ:-35deg;" 
                      data-frame_0_mask="u:t;" 
                      data-frame_1="st:640;sp:1200;sR:640;" 
                      data-frame_1_chars="e:power4.inOut;d:10;rZ:0deg;"
                      data-frame_1_mask="u:t;" 
                      data-frame_999="x:left;e:power3.in;st:w;sp:1000;sR:4960;" 
                      data-frame_999_reverse="x:true;" 
                      style="z-index:11;font-family:'Open Sans';text-transform:uppercase;">
               <?= htmlspecialchars($slider['subtitle']) ?>
            </rs-layer>
            
            <!-- Description -->
            <rs-layer id="slider-<?= $slideIndex ?>-slide-<?= $slideIndex ?>-layer-14" 
                      data-type="text" 
                      data-rsp_ch="on" 
                      data-xy="x:c;y:m,t,t,t;yo:78px,286px,271px,233px;" 
                      data-text="w:normal;s:18,18,16,15;l:31,30,30,27;a:center;" 
                      data-dim="w:806px,805px,689px,388px;h:auto,auto,auto,89px;"
                      data-frame_0="y:100%;" 
                      data-frame_0_mask="u:t;" 
                      data-frame_1="st:2680;sp:1360;sR:2680;" 
                      data-frame_1_mask="u:t;" 
                      data-frame_999="o:0;st:w;sR:4960;" 
                      style="z-index:9;font-family:'Open Sans';">
               <?= htmlspecialchars($slider['description']) ?>
            </rs-layer>
            
            <!-- Button 1 -->
            <?php if ($slider['button1_text']): ?>
            <a id="slider-<?= $slideIndex ?>-slide-<?= $slideIndex ?>-layer-16" 
               class="rs-layer res-slide-btn pop rev-btn" 
               href="<?= url($slider['button1_link']) ?>" 
               target="_self" 
               data-type="button" 
               data-color="rgba(255,255,255,1)" 
               data-xy="x:c;xo:-80px;y:m;yo:162px,156px,128px,112px;" 
               data-text="s:16,16,14,14;l:17;fw:500;a:center;"
               data-rsp_bd="off" 
               data-padding="t:15,15,10,10;r:35,35,25,25;b:15,15,10,10;l:35,35,25,25;" 
               data-border="bor:3px,3px,3px,3px;" 
               data-frame_0="y:100%;" 
               data-frame_1="e:power4.inOut;st:3160;sp:1200;sR:3160;" 
               data-frame_999="o:0;st:w;sR:4640;"
               data-frame_hover="bgc:#000;boc:#000;bor:3px,3px,3px,3px;bos:solid;oX:50;oY:50;sp:0;" 
               style="z-index:8;font-family:'Poppins';cursor:pointer;">
               <?= htmlspecialchars($slider['button1_text']) ?>
            </a>
            <?php endif; ?>
            
            <!-- Button 2 -->
            <?php if ($slider['button2_text']): ?>
            <a id="slider-<?= $slideIndex ?>-slide-<?= $slideIndex ?>-layer-17" 
               class="rs-layer res-slide-btn pop rev-btn" 
               href="<?= url($slider['button2_link']) ?>" 
               target="_self" 
               data-type="button" 
               data-color="rgba(255,255,255,1)" 
               data-xy="x:c;xo:80px;y:m;yo:162px,156px,128px,112px;" 
               data-text="s:16,16,14,14;l:17;fw:500;a:center;"
               data-rsp_bd="off" 
               data-padding="t:15,15,10,10;r:35,35,25,25;b:15,15,10,10;l:35,35,25,25;" 
               data-border="bor:3px,3px,3px,3px;" 
               data-frame_0="y:100%;" 
               data-frame_1="e:power4.inOut;st:3360;sp:1200;sR:3360;" 
               data-frame_999="o:0;st:w;sR:4440;"
               data-frame_hover="bgc:#e74c3c;boc:#e74c3c;bor:3px,3px,3px,3px;bos:solid;oX:50;oY:50;sp:0;" 
               style="z-index:7;font-family:'Poppins';cursor:pointer;background-color:#e74c3c;border-color:#e74c3c;">
               <?= htmlspecialchars($slider['button2_text']) ?>
            </a>
            <?php endif; ?>
            
         </rs-slide>
         <?php 
            $slideIndex++;
         endforeach; 
         ?>
      </rs-slides>
   </rs-module>
</rs-module-wrap>