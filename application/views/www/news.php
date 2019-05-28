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

            <!--  news link  -->
              <?php foreach($newsList as $row):?>
                    <div class="row mb-2 news-link">
                      <div class="col-2 text-center">
                          <?php echo date('Y.m.d',strtotime($row["buildtime"]))?>
                      </div>
                      <div class="col-10">
                          <a href="<?php echo site_url("News/detail") . "?num=" . $row["num"] ?>" data-toggle="modal" data-target="#news"><?php echo $row["subject"]?></a>
                      </div>
                    </div>  
                <?php endforeach;?>
            <!--  news link end -->

          </div>
        </div>

      </div>
    </div>
	  
<!----news model----->
<div class="modal fade" id="news">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <?php if(isset($row)):?>
            <!-- Modal Header -->
            <div class="modal-header bg-light">
              <h4 class="modal-title">NEWS</h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
      
            <!-- Modal body -->
            <div class="modal-body">
                <h5><?php echo $row["subject"]?></h5>
                <p>發布日期：<?php echo date('Y.m.d',strtotime($row["buildtime"]))?></p>
                 <?php echo $row["word"]?>
            </div>
      
            <!-- Modal footer -->
            <div class="modal-footer bg-light">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        <?php endif;?>
      </div>
    </div>
</div>
<!----news model end----->	  
	  

      <?php $this -> load -> view("www/includes/footer.php")?>
	  </div>
   </body>
</html>
