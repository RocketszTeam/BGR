<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
   </head>
  <body style="background-image: url('<?php echo ASSETS_URL ?>/www/images/bg.jpg');">
  <div class="site-wrap">
      <?php $this -> load -> view("www/includes/header.php")?>
	  
	  
	  <div class="site-section">
      <div class="container">

        <div class="site-section">
        <div class="container">
            <div class="row mb-5">            
            
            <div class="col-md-9" id="focus-info">
			<?php if(isset($row)):?>
                <div class="row mb-5">
                    <div class="col-12 mb-1"><h2 class="text-uppercase"><?php echo $row["subject"]?></h2></div>
					<?php if($row["pic1"]!=''):?>
                    <div class="col-md-12 mb-1">
                        <img src="<?php echo UPLOADS_URL.'/active/'.$row["pic1"]?>" alt="image" class="img-fluid">
                    </div>
					<?php endif;?>
				  
                    <div class="col-md-12 mt-1">
                        <div>
                            <h4>活動說明</h4>
                            <?php echo $row["word"]?>
                        </div>
                    </div>
                </div>
				<?php endif;?>
                <a href="<?php echo site_url('Active')?>"><i class="fas fa-angle-double-left"></i> Baack</a>
            </div>

		 
		 

            <div class="col-md-3 focus-body">
                <span class="caption d-block mb-2 font-secondary font-weight-bold">FOCUS</span>
                <h2 class="site-section-heading text-uppercase font-secondary">焦點情報</h2>
                <ul>
                    <?php if(isset($activeList)):?>
                    <?php foreach($activeList as $keys=>$row):?>
                        <li class="focus-title">
                            <!-- <a href="<?php echo site_url("Active/detail/".$row["num"])?>"> -->
                            <a href="<?php echo site_url("Active/detail") . "?num=" . $row["num"] ?>">
                            <?php echo $row["subject"]?>
                            </a>
                        </li>
                    <?php endforeach;?>
                    <?php endif;?>         

                </ul>
                    <a href="<?php echo site_url('Active')?>"><i class="fas fa-angle-double-left"></i> Baack</a>
            </div>

            </div>
        </div>
        </div>
         

        </div>

      </div>
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  	       <!-- Footer -->
      <?php $this -> load -> view("www/includes/footer.php")?>
	  </div>
   </body>
</html>