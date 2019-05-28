<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
   </head>
   <body>
      <?php $this -> load -> view("www/includes/header.php")?>
      <section class="page_container lottery_container">
         <div class="page_content">
            <div class="page_mtitle">Lottery Game<b>彩票遊戲</b></div>
            <div class="inner_content">
               <div class="game_seo">
                  <p>提供專業的北京PK10、極速賽車等系列<spen style="display:none;">彩票開獎分析、公式探討與開獎歷史，助你預測最好結果奪得大獎</spen>，「高賠率、節奏快」是賽車彩票的最大特色，只要學會穩定控制風險，就有機會以小博大穩定獲利！</p>
               </div>
               <div class="lottery_list clearfix">
                  <div class="lottery_unit">
                     <div class="lottery_img">
                        <img src="<?php echo ASSETS_URL?>/www/images/lottery/lottery_pic1.png" alt="">
                     </div>
                     <div class="lottery_txt">
                        <div class="lottery_title">六合彩</div>
                        <a href="<?php echo site_url("Opengame?gm=20")?>" target="_blank" class="lottery_btn">進入遊戲<i class="fa fa-caret-right"></i></a>
                     </div>
                  </div>
                  <div class="lottery_unit">
                     <div class="lottery_img">
                        <img src="<?php echo ASSETS_URL?>/www/images/lottery/lottery_pic2.png" alt="">
                     </div>
                     <div class="lottery_txt">
                        <div class="lottery_title">BINGO</div>
                        <a href="<?php echo site_url("Opengame?gm=28")?>" target="_blank" class="lottery_btn">進入遊戲<i class="fa fa-caret-right"></i></a>
                     </div>
                  </div>
                  <div class="lottery_unit">
                     <div class="lottery_img">
                        <img src="<?php echo ASSETS_URL?>/www/images/lottery/lottery_pic3.png" alt="">
                     </div>
                     <div class="lottery_txt">
                        <div class="lottery_title">北京賽車9K</div>
                        <a href="<?php echo site_url("Opengame?gm=26")?>" target="_blank" class="lottery_btn">進入遊戲<i class="fa fa-caret-right"></i></a>
                     </div>
                  </div>
                  <!--
                  <div class="lottery_unit">
                     <div class="lottery_img">
                        <img src="<?php echo ASSETS_URL?>/www/images/lottery/lottery_pic4.png" alt="">
                     </div>
                     <div class="lottery_txt">
                        <div class="lottery_title">北京賽車PN</div>
                        <a href="<?php echo site_url("Opengame?gm=31")?>" target="_blank" class="lottery_btn">進入遊戲<i class="fa fa-caret-right"></i></a>
                     </div>
                  </div>
                  -->
				  <!--
                  <div class="lottery_unit">
                     <div class="lottery_img">
                        <img src="<?php //echo ASSETS_URL?>/www/images/lottery/lottery_pic5.png" alt="">
                     </div>
                     <div class="lottery_txt">
                        <div class="lottery_title">彩播</div>
                        <a href="<?php //echo site_url("Opengame?gm=41")?>" target="_blank" class="lottery_btn">進入遊戲<i class="fa fa-caret-right"></i></a>
                     </div>
                  </div>
				  -->
               </div>
            </div>
            <!--inner_content end-->
         </div>
      </section>
      <?php $this -> load -> view("www/includes/footer.php")?>
   </body>
</html>