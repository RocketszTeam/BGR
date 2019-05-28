<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
   </head>
   <body>
   <div class="site-wrap">
      <?php $this -> load -> view("www/includes/header.php")?>
	  
	   <div class="site-section">
      <div class="container">

        <div class="row mb-5">
            <div class="col-md-12 text-center">
              <span class="caption d-block mb-2 font-secondary font-weight-bold">Notice message</span>
              <h2 class="site-section-heading text-uppercase text-center font-secondary">最新公告</h2>
            </div>
        </div>

        <div class="row mb-5">
          <div class="col-12 mb-5">

            <?php if(isset($row)):?>

            <div class="inner_content">
               <div class="news_header">
                  <div class="news_detail_title"><?php echo $row["subject"]?></div>
                  <div>
                     <span class="active_tag org_tag">最新消息</span>
                     <span class="news_detail_date"><?php echo date('Y.m.d',strtotime($row["buildtime"]))?></span>
                  </div>
               </div>
               <div class="news_detail_txt">
                 <?php echo $row["word"]?>
               </div>
            </div>
            <?php endif;?>

          </div>
        </div>

      </div>
    </div>
	
	
      <?php $this -> load -> view("www/includes/footer.php")?>
	  </div>
   </body>
</html>
