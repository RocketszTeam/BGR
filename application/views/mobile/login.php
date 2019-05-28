<!DOCTYPE html>
<head>
    <?php $this->load->view("www/includes/head.php")?>   
</head>
<body>  
	<?php $this->load->view("www/includes/menu.php")?>
	<!-- END nav -->
	<?php $this->load->view("www/includes/banner.php")?>

	
	<div class="row text-center bg-dark IndexLink">
			<div class="col"> <a href="<?php echo site_url("Guide?kind=1")?>">關於玖天</a></div> 
			<div class="col"> <a href="<?php echo site_url("Manger/deposit")?>#cha">儲值中心</a></div> 
			<div class="col"> <a class="btn btn-warning btn-pd-min" href="<?php echo site_url("Slot/qt")?>">免費試玩</a></div> 
	</div>
    <section class="probootstrap-section-half d-md-flex px-md-5 mobBox" id="section-about">
		
      <div class="container text-center" >
          <div class="mt-3">
            <form id="LoginForm2" autocomplete="off"  method="post">
			

				  <input type="hidden" name="rtn" id="rtn" value="<?php echo $this->input->get('rtn')?>" /> 
				  <div class="form-group">               
					<div class="input-group">
						<input type="text" class="form-control login_m" placeholder="帳號" id="login_u_id" name="login_u_id" maxlength="20" tabindex="1">
					</div>
					<div class="input-group">
					  <input type="password" class="form-control login_m" placeholder="密碼" name="login_u_password" id="login_u_password" maxlength="20" tabindex="2">
					  <div class="input-group-btn">
					  <a  class="btn btn-warning btn-forgot" href="<?php echo site_url("Forget")?>" style="height:40px !important;"><i class="fa fa-question"></i></a>
					  
					  </div>
					</div>
				   
					<div class="input-group">
					  <input type="tel" class="form-control login_m" placeholder="驗證碼" name="chknum" id="chknum" maxlength="4" tabindex="3">
					  <div class=" ">
						<img src="<?php echo site_url("Vcode2")?>?token=<?php echo $token?>" id="chkImg" width="118" height="40" style="cursor:pointer" onclick="changeChkImg()"/>
					  </div>
					</div>
				  </div>


					  <button type="submit" class="button btn btn-warning btn-pd-min">登入</button>
					  <a class="button btn btn-danger btn-pd-min" href="<?php echo site_url("Manger/register")?>">加入會員</a>

		 
          </form>

		</div>
	</div>

    </section>

    
    <?php $this->load->view("www/includes/footer.php")?>

	</body>
</html>
