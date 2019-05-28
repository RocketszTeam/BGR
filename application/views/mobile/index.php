<!DOCTYPE html>
<head>
    <?php $this->load->view("www/includes/head.php")?>   
</head>
<body>  
	<?php $this->load->view("www/includes/menu.php")?>
	<!-- END nav -->
	<?php $this->load->view("www/includes/banner.php")?>

	
	<div class="row text-center bg-dark IndexLink  m-0">
			<div class="col"> <a href="<?php echo site_url("Guide?kind=1")?>">關於玖天</a></div> 
			<div class="col"> <a href="<?php echo site_url("Manger/deposit")?>#cha">儲值中心</a></div> 
			<div class="col"> <a class="btn btn-warning btn-pd-min" href="<?php echo site_url("Slot/qt")?>">免費試玩</a></div> 
	</div>
    <section class="probootstrap-section-half d-md-flex px-md-5 mobBox" id="section-about">
		
      <div class="container text-center" >
	  <!-----真人------>
          <div class="row probootstrap-animate" data-animate-effect="fadeIn">
		  <div class="col-4 p-0 m-0">
			  <a href="<?php echo site_url("Live")?>">
					  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/live.png">
			  </a>
		  </div>
            <div class="col-8 row p-0 m-0" style=" margin-left: 0px;">
				 <div class="col-6 m_game">
				  <a href="<?php echo site_url("Opengame?gm=12")?>" target="_blank">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/live_dg.png"> DG真人
				  </a>
				  </div>
				  <div class="col-6 m_game">
				  <a href="<?php echo site_url("Opengame?gm=9")?>" target="_blank">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/live_sa.png"> SA真人
				  </a>
				  </div>
				  <div class="col-6 m_game">
				  <a href="<?php echo site_url("Opengame?gm=13")?>" target="_blank">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/live_wm.png"> 完美真人
				  </a>
				  </div>
				  <div class="col-6 m_game">
				  <a href="<?php echo site_url("Opengame?gm=3")?>" target="_blank">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/live_ab.png"> 歐博真人
				  </a>
				  </div>
            </div>			
        </div>
		<!-----體育------>
		<div class="row probootstrap-animate" data-animate-effect="fadeIn">
		  <div class="col-4 p-0 m-0">
			  <a href="<?php echo site_url("Sport")?>">
					  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/sport.png">
			  </a>
		  </div>
            <div class="col-8 row p-0 m-0" style=" margin-left: 0px;">
				 <div class="col-12 m_game">
				  <a href="<?php echo site_url("Opengame?gm=8")?>" target="_blank">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/sport_sp.png"> SUPER體育
				  </a>
				  </div>
				  <div class="col-12 m_game sky">
				  <a href="<?php echo site_url("Opengame?gm=21")?>" target="_blank">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/sport_ssb.png"> 鑫寶體育
				  </a>
				  </div>
            </div>			
        </div>
		
		<!-----電子------>
		<div class="row probootstrap-animate" data-animate-effect="fadeIn">
		  <div class="col-4 p-0 m-0">
			  <a href="<?php echo site_url("Slot")?>">
					  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/slot.png">
			  </a>
		  </div>
			<div class="col-8 row p-0 m-0" style=" margin-left: 0px;">
				 <div class="col-6 m_game">
				  <a href="<?php echo site_url("Slot/ameba")?>">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/solt_Ameba.png"> AMEBA
				  </a>
				  </div>
				  <div class="col-6 m_game">
				  <a href="<?php echo site_url("Opengame?gm=6&GameCode=4003")?>">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/solt_GG.png"> 全民捕魚
				  </a>
				  </div>
				  
				  <div class="col-12 m_game">
				  <a href="<?php echo site_url("Slot/pg")?>">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/solt_PG.png"> PG電子
				  </a>
				  </div>
				  
				  <!--<div class="col-6 m_game">
				  <a href="<?php echo site_url("Slot/qt")?>">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/solt_QT.png"> QT電子
				  </a>
				  </div>-->
				  
				  <div class="col-12 m_game">
				  <a href="<?php echo site_url("Opengame?gm=22")?>">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/solt_7PK.png"> 7PK
				  </a>
				  </div>
			</div>
			
        </div>
		
		<!-----電競------>
		
		<div class="row probootstrap-animate" data-animate-effect="fadeIn">
		  <div class="col-4 p-0 m-0">
			  <a href="<?php echo site_url("Esport")?>">
					  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/esp.png">
			  </a>
		  </div>
			<div class="col-8 row p-0 m-0" style=" margin-left: 0px;">
				 <div class="col-12 m_game">
				  <a href="<?php echo site_url("Opengame?gm=35")?>">
						<img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/esp_avia.png"> 泛亞電競
				  </a>
				  </div>
				  <div class="col-12 m_game">
				  <a href="javascript:alert('敬請期待！')">
						<img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/esp_hc.png"> 皇朝電競
				  </a>
				  </div>
			</div>	  
			
        </div>		
		
		
			
		<!-----彩票------>
          <div class="row probootstrap-animate" data-animate-effect="fadeIn">
		  <div class="col-4 p-0 m-0">
			  <a href="<?php echo site_url("Keno")?>">
					  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/keno.png">
			  </a>
		  </div>
            <div class="col-8 row p-0 m-0" style=" margin-left: 0px;">
				 <div class="col-6 m_game">
				  <a href="<?php echo site_url("Opengame?gm=28")?>">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/keno_1.png"> BINGO
				  </a>
				  </div>
				  <div class="col-6 m_game">
				  <a href="<?php echo site_url("Opengame?gm=20")?>">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/keno_2.png"> 六合彩
				  </a>
				  </div>
				  <div class="col-6 m_game">
				  <a href="<?php echo site_url("Opengame?gm=26")?>">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/keno_3.png"> 北京賽車
				  </a>
				  </div>
				  <div class="col-6 m_game">
				  <a href="<?php echo site_url("Opengame?gm=31")?>">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/keno_4.png"> 北京賽車
				  </a>
				  </div>
				  <div class="col-12 m_game">
                      <a href="<?php echo site_url("Opengame?gm=41")?>">
						  <img class="img-fluid" src="<?php echo ASSETS_URL?>/www/images/mobile/keno_5.png"> 彩播 LOTTERY VIDEO
				  </a>
				  </div>
            </div>			
        </div>

		

      </div>
    </section>

    
    <?php $this->load->view("www/includes/footer.php")?>

	</body>
</html>