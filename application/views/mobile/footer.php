<div class="m_ft d-md-none">
	<div class="row">
		<div class="col">
			<a href="<?php echo site_url("Manger/deposit")?>#cha">
			<img src="<?php echo ASSETS_URL?>/www/images/mobile/fi3.png" class="img-fluid">
			<div>快速<br/>儲值</div>
			</a>
		</div>
		<div class="col">
			<a href="<?php echo site_url("Manger/transfer")?>#cha">
			<img src="<?php echo ASSETS_URL?>/www/images/mobile/fi4.png" class="img-fluid">
			<div>點數<br/>轉移</div>
			</a>
		</div>
		<div class="col">
			<a href="<?php echo site_url("Manger/withdrawal")?>#cha">
			<img src="<?php echo ASSETS_URL?>/www/images/mobile/fi5.png" class="img-fluid">
			<div>點數<br/>轉出</div>
			</a>
		</div>
		<div class="col">
			<a href="javascript:openwindow('http://kefu.ziyun.com.cn/vclient/chat/?websiteid=135433' + window.document.title)">
			<img src="<?php echo ASSETS_URL?>/www/images/mobile/fi2.png" class="img-fluid">
			<div>客服<br/>中心</div>
			</a>
		</div>		
		
		<!----登入前---->
		<?php if(isset($isLogin) && !$isLogin):  //登入前?>
		<div class="col">
			<a href="<?php echo site_url("Login")?>">
			<img src="<?php echo ASSETS_URL?>/www/images/mobile/fi6.png" class="img-fluid">
			<div>會員<br/>登入</div>
			</a>
		</div>
		<?php else: //登入後?>
		<!----登入後---->		
		<div class="col">
			<a href="<?php echo site_url("Manger/account")?>#cha">
			<img src="<?php echo ASSETS_URL?>/www/images/mobile/fi6.png" class="img-fluid">
			<div>會員<br/>中心</div>
			</a>
		</div>
		<div class="col">
			<a href="<?php echo site_url("Index/logout")?>">
			<img src="<?php echo ASSETS_URL?>/www/images/mobile/fi7.png" class="img-fluid">
			<div>會員<br/>登出</div>
			</a>
		</div>
		
		<?php endif;?>
	</div>
</div>