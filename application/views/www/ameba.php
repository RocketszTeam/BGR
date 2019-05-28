<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
   </head>
   <body>
      <?php $this -> load -> view("www/includes/header.php")?>
      <!--banner end-->
      <section class="page_container">
         <div class="page_content">
            <img src="images/video/left_img1.png" alt="" class="video_left_img">
            <div class="page_mtitle">Slot<b>電子遊戲</b></div>
            <div class="inner_content">
               <h1 class="game_list_title">AMEBA</h1>
               <ul class="page_tabs" role="tablist">
                  <li class="tab_last">
                     <a class=" <?php if(@$_GET['kind']=='') echo ' active'?>" href="<?php echo site_url("Slot")?>">回列表</a>
                  </li>
                  <?php if(isset($kindList)):?>
                  <?php foreach($kindList as $row):?>
                  <li class="<?php if(@$_GET['kind']==$row["num"]) echo ' active'?>" >
                     <a href="<?php echo site_url("Slot/ameba?kind=".$row["num"])?>"><?php echo $row["kind"]?></a>
                  </li>
                  <?php endforeach;?>
                  <?php endif;?>
               </ul>
               <div class="tab_container game_tab_container">
                  <?php if(isset($gameList)):?>
                  <div id="tab1" class="tab_content">
                     <div class="game_list clearfix">
                        <?php foreach($gameList as $row):?> 
                        <div class="game_unit">
                           <div class="game_img imgLiquidFill imgLiquid">
                              <img src="<?php echo UPLOADS_URL?>/games/<?php echo $row["pic1"] ?>" alt="">
                           </div>
                           <div class="game_title"><?php echo $row["game_name"]?></div>
                           <a href="<?php echo site_url("Opengame?gm=".$row["makers_num"]."&GameCode=".$row["game_code"])?>" class="game_btn">進入遊戲<i class="fa fa-caret-right"></i></a>
                           <a href="javascript:openwindow('<?php echo site_url("Fungame?gm=".$row["makers_num"]."&GameCode=".$row["game_code"])?>')" class="game_btn game_btn_go">免費試玩<i class="fa fa-caret-right"></i></a>
                        </div>
                        <?php endforeach;?> 
                     </div>
                     <!-- <div class="page_btn">
                        <a href="#"><i class="fa fa-angle-left" aria-hidden="true"></i></a>
                        <ul>
                           <li><a href="#" class="select">1</a></li>
                           <li><a href="#">2</a></li>
                           <li><a href="#">3</a></li>
                           <li><a href="#">4</a></li>
                           <li><a href="#">5</a></li>
                           <li><a href="#">6</a></li>
                           <li><a href="#">7</a></li>
                           <li><a href="#">8</a></li>
                        </ul>
                        <a href="#"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                        </div> -->
                  </div>
                  <!-- #tab1 -->
                  <?php endif;?>
               </div>
               <!-- .tab_container -->
            </div>
            <!--inner_content end-->
            <img src="images/game/right_img1.png" alt="" class="game_right_img">
         </div>
      </section>
      <?php $this -> load -> view("www/includes/footer.php")?>
   </body>
</html>