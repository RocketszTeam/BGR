<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
   </head>
   <body>
      <?php $this -> load -> view("www/includes/header.php")?>
      <section class="page_container sport_container">
         <div class="page_content">
            <div class="page_mtitle"> Sports <b>運動&電競賽事</b></div>
            <div class="inner_content">
            <div class="game_seo">
               <p>體育賽事提供ＭＬＢ美國職棒、ＮＢＡ美國職籃、日本職棒、CPBL中華職棒、足球五大聯賽各項國際體育賽事，以及刺激的賽狗、賽馬等多樣玩法。</p>
               <p>電子競技e-Sport提供英雄聯盟ＬＯＬ、絕地求生吃雞、ＣＳ、星際爭霸、爐石戰記、等各國電競職業聯賽，賽事零時差直播，開盤即時，系統穩定，快來為您喜愛的隊伍加油。
               </p>
               </div>
               <div class="sport_list clearfix">
                  <div class="sport_unit">
                     <a href="<?php echo site_url('Opengame?gm=8')?>" target="_blank">
                        <div class="s-logo sp"><img src="<?php echo ASSETS_URL?>/www/images/sport/sport-logo1.png"></div>
                        <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/sport/sport_pic1.jpg" alt="">
                        <div class="sport_info">
                           <div class="sport_title">SUPER</div>
                           <div class="sport_txt">體育是亞洲最熱門體育投注平台之一，賽事豐富齊全，深受玩家喜愛</div>
                        </div>
                     </a>
                  </div>
				  <!--<a href="javascript:void(0);" onclick="javascript:alert('即將上線，敬請期待');">-->
                 <!-- <div class="sport_unit">
                     <a href="<?php echo site_url('Opengame?gm=21')?>" target="_blank">             
                        <div class="s-logo cp"><img src="<?php echo ASSETS_URL?>/www/images/sport/sport-logo2.png"></div>
                        <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/sport/sport_pic2.jpg" alt="">
                        <div class="sport_info">
                           <div class="sport_title">鑫寶體育</div>
                           <div class="sport_txt">體育是亞洲最熱門體育投注平台之一，賽事豐富齊全，深受玩家喜愛</div>
                        </div>
                     </a>
                  </div>-->
				  <!--電競-->
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
                 <!-- <div class="sport_unit">
                    <a href="<?php echo site_url('Opengame?gm=36')?>" target="_blank">
                        <div class="s-logo cp"><img src="<?php echo ASSETS_URL?>/www/images/esport/esport-logo2.png"></div>
                        <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/esport/esport_pic2.jpg" alt="">
                        <div class="sport_info">
                           <div class="sport_title">皇朝電競</div>
                           <div class="sport_txt">亞洲最熱門平台之一，豐富齊全，深受玩家喜愛</div>
                        </div>
                     </a>
                  </div>-->
				  
				  <!--電競結束-->
				  <!--<div class="sport_unit">
                     <a href="<?php echo site_url('#')?>" target="_blank">
                        <div class="s-logo cp"><img src="<?php echo ASSETS_URL?>/www/images/sport/penhfun-XL2.png"></div>
                        <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/sport/penhfun-XL1.png" alt="">
                        <div class="sport_info">
                           <div class="sport_title">體球王</div>
                           <div class="sport_txt">體育是亞洲最熱門體育投注平台之一，賽事豐富齊全，深受玩家喜愛</div>
                        </div>
                     </a>
                  </div>-->
                  <!-- <div class="sport_unit">
                     <a href="<?php echo site_url('Opengame?gm=8')?>" target="_blank">
                        <div class="s-logo cl"><img src="<?php echo ASSETS_URL?>/www/images/sport/sport-logo3.png"></div>
                        <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/sport/sport_pic3.jpg" alt="">
                        <div class="sport_info">
                           <div class="sport_title">希爾體育</div>
                           <div class="sport_txt">體育是亞洲最熱門體育投注平台之一，賽事豐富齊全，深受玩家喜愛</div>
                        </div>
                     </a>
                  </div> -->
               </div>
            </div>
            <!--inner_content end-->
         </div>
      </section>
      <?php $this -> load -> view("www/includes/footer.php")?>
   </body>
</html>