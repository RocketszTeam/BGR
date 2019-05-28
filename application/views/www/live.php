<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
   </head>
   <body>
      <?php $this -> load -> view("www/includes/header.php")?>
      <section class="page_container">
         <div class="page_content">
            <img src="<?php echo ASSETS_URL?>/www/images/video/left_img1.png" alt="" class="video_left_img">
            <div class="page_mtitle">Live Casino<b>真人視訊</b></div>
            <div class="inner_content">
            <div class="game_seo">
               <p>線上技術如蒞臨澳門賭場娛樂城投注的真實感受，真人視訊百家樂、骰寶、先搖骰寶、輪盤、龍虎、21點、三公、牛牛、番攤，真人遊戲絕對是高水準，高品味，高嚴選，讓您愛不釋手。</p>
            </div>
               <div class="viedo_list clearfix">
			   
			     <div class="video_unit">
                     <div class="v-logo dg"><img src="<?php echo ASSETS_URL?>/www/images/video/dg.png"></div>
                     <a href="<?php echo site_url("Opengame?gm=12")?>" target="_blank"><img src="<?php echo ASSETS_URL?>/www/images/video/video_pic3.png" alt="">
                     <div class="v-name">DG真人</div></a>
                  </div>
			   
                  <div class="video_unit">
                     <div class="v-logo ab"><img src="<?php echo ASSETS_URL?>/www/images/video/AB.png"></div>
                     <a href="<?php echo site_url("Opengame?gm=3")?>" target="_blank"><img src="<?php echo ASSETS_URL?>/www/images/video/video_pic1.png" alt="">
                     <div class="v-name">歐博真人</div></a>
                  </div>
				  
                  <div class="video_unit">
                     <div class="v-logo sa"><img src="<?php echo ASSETS_URL?>/www/images/video/sa.png"></div>
                     <a href="<?php echo site_url("Opengame?gm=9")?>" target="_blank"><img src="<?php echo ASSETS_URL?>/www/images/video/video_pic2.png" alt="">
                     <div class="v-name">沙龍真人</div></a>
                  </div>
                  
                  <div class="video_unit">
                     <div class="v-logo ma"><img src="<?php echo ASSETS_URL?>/www/images/video/ma.png"></div>
                     <a href="<?php echo site_url("Opengame?gm=33")?>" target="_blank"><img src="<?php echo ASSETS_URL?>/www/images/video/video_pic4.png" alt="">
                     <div class="v-name">瑪雅真人</div></a>
                  </div>
                  
                  <div class="video_unit">
                     <div class="v-logo wm"><img src="<?php echo ASSETS_URL?>/www/images/video/wm.png"></div>
                     <a href="<?php echo site_url("Opengame?gm=13")?>" target="_blank"><img src="<?php echo ASSETS_URL?>/www/images/video/video_pic5.png" alt="">
                     <div class="v-name">完美真人</div></a>
                  </div>
               </div>
            </div>
            <!--inner_content end-->
            <img src="<?php echo ASSETS_URL?>/www/images/video/right_img1.png" alt="" class="video_right_img">
         </div>
      </section>
      <?php $this -> load -> view("www/includes/footer.php")?>
   </body>
</html>