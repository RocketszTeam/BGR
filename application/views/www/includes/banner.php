<div class="slide-one-item home-slider owl-carousel">

 <?php if(isset($bannerList)):?>
          <?php foreach($bannerList as $keys => $row):?>
			<div class="site-blocks-cover inner-page overlay <?php if($keys==0) echo ' active'?>" style="background-image: url(<?php echo UPLOADS_URL?>/banner/<?php echo $row["pic"]?>);" data-aos="fade" data-stellar-background-ratio="0.5">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-md-6" data-aos="fade">
                <h1 class="font-secondary font-weight-bold text-uppercase"><?php echo $row["subject"]?></h1>
              </div>
            </div>
          </div>
        </div>  
          <?php endforeach;?>
        <?php endif;?>

  </div>


      
        
 