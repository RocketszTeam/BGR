<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
   </head>
  <body>
  <div class="site-wrap">
      <?php $this -> load -> view("www/includes/header.php")?>


  <div class="site-section">
      <div class="container">

        <div class="row">
            <div class="col-md-12 text-center">
              <span class="caption d-block mb-2 font-secondary font-weight-bold">FOCUS</span>
              <h2 class="site-section-heading text-uppercase text-center font-secondary">焦點情報</h2>
            </div>
        </div>

        <div class="row mb-5">

          <!----優惠BANNER----->
		<?php if(isset($activeList)):?>
			<?php foreach($activeList as $keys=>$row):?>
					
          <div class="col-md-6 col-lg-4 mb-5">
            <div class="media-image">
              <img src="<?php echo UPLOADS_URL?>/active/<?php echo $row["pic1"]?>" alt="Image" class="img-fluid">
              <div class="media-image-body">
                <h2 class="font-secondary text-uppercase"><?php echo $row["subject"]?></h2>
                <p><a href="<?php echo site_url("Active/detail") . "?num=" . $row["num"] ?>">Read More</a></p>
              </div>
            </div>
          </div>
			<?php endforeach;?>
		<?php endif;?>
          <!----優惠BANNER END----->

        </div>

      </div>
    </div>
	
	
		       <!-- Footer -->
      <?php $this -> load -> view("www/includes/footer.php")?>
	  </div>
   </body>
</html>

	