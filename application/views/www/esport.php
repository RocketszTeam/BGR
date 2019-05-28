<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
   </head>
   <body>
      <?php $this -> load -> view("www/includes/header.php")?>
      <section class="page_container esport_container">
         <div class="page_content">
            <div class="page_mtitle">ESport<b>電子競技</b></div>
            <div class="inner_content">
               <div class="sport_list clearfix">
                  <div class="sport_unit">
                    <a href="<?php echo site_url('Opengame?gm=35')?>" target="_blank">
                        <div class="s-logo sp"><img src="<?php echo ASSETS_URL?>/www/images/esport/esport-logo1.png"></div>
                        <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/esport/esport_pic1.jpg" alt="">
                        <div class="sport_info">
                           <div class="sport_title">泛亞電競</div>
                           <div class="sport_txt">亞洲最熱門平台之一，豐富齊全，深受玩家喜愛</div>
                        </div>
                     </a>
                  </div>
                  <div class="sport_unit">
                    <a href="<?php echo site_url('Opengame?gm=36')?>" target="_blank">
                        <div class="s-logo cp"><img src="<?php echo ASSETS_URL?>/www/images/esport/esport-logo2.png"></div>
                        <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/esport/esport_pic2.jpg" alt="">
                        <div class="sport_info">
                           <div class="sport_title">皇朝電競</div>
                           <div class="sport_txt">亞洲最熱門平台之一，豐富齊全，深受玩家喜愛</div>
                        </div>
                     </a>
                  </div>
               </div>
            </div>
            <!--inner_content end-->
         </div>
      </section>
      <?php $this -> load -> view("www/includes/footer.php")?>
   </body>
</html>