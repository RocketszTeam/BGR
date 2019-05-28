<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta charset="utf-8" />
		<title><?php echo $title?></title>

		<meta name="description" content="User login page" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

		<!-- bootstrap & fontawesome -->
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/bootstrap.min.css" />
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/font-awesome.css" />

		<!-- text fonts -->
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/fonts.googleapis.com.css" />

		<!-- ace styles -->
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/ace.min.css" />

		<!--[if lte IE 9]>
			<link rel="stylesheet" href="<?php echo ASSETS_URL?>/assets/ace/css/ace-part2.min.css" />
		<![endif]-->
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/ace-rtl.min.css" />

		<!--[if lte IE 9]>
		  <link rel="stylesheet" href="<?php echo ASSETS_URL?>/ace/css/ace-ie.min.css" />
		<![endif]-->

		<!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

		<!--[if lte IE 8]>
		<script src="<?php echo ASSETS_URL?>/ace/js/html5shiv.min.js"></script>
		<script src="<?php echo ASSETS_URL?>/ace/js/respond.min.js"></script>
		<![endif]-->
        <script type="text/javascript">
		function changeChkImg(){
			$.ajax({
				type: "POST",
				url:  "<?php echo site_url(SYSTEM_URL."/Login/refresh_token")?>",
				cache: false,
				async:false,
				dataType:"json"
			}).done(function( msg ) { 
				var now = new Date();
				$('#chkImg').attr("src","<?php echo  site_url("Vcode2");?>?token=" + msg.token  + "&now=" + now.getTime());							
			});
		}
		
		</script>
	</head>

	<body class="login-layout">
		<div class="main-container">
			<div class="main-content">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<div class="login-container">
							<div class="center">
								<h1>
									<i class="ace-icon fa fa-leaf green"></i>
									<span class="red">管理平台</span>
									<!--<span class="white" id="id-text2">Application</span>-->
								</h1>
								<!--<h4 class="blue" id="id-company-text">&copy; Company Name</h4>-->
							</div>

							<div class="space-6"></div>

							<div class="position-relative">
								<div id="login-box" class="login-box visible widget-box no-border">
									<div class="widget-body">
										<div class="widget-main">
											<h4 class="header blue lighter bigger">
												<i class="ace-icon fa fa-coffee green"></i>
												請輸入您的帳號密碼
											</h4>

											<div class="space-6"></div>

											<form id="loginForm" method="post" autocomplete="off" action="<?php echo  site_url(SYSTEM_URL."/Login/adminLogin")?>">
												<fieldset>
                                                	
													<label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="text" id="u_id" name="u_id" class="form-control" placeholder="帳號"  />
															<i class="ace-icon fa fa-user"></i>
														</span>
													</label>

													<label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="password" id="u_password" name="u_password" class="form-control" placeholder="密碼" />
															<i class="ace-icon fa fa-lock"></i>
														</span>
													</label>
													
                                                    <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="text" id="chknum" name="chknum" maxlength="4" class="form-control" placeholder="驗證碼" />
															<i class="ace-icon fa fa-eye"></i>
														</span>
													</label>
                                                    <label class="block clearfix">
														<span class="block input-icon text-center">
															<img id="chkImg" src="<?php echo site_url("Vcode2")?>?token=<?php echo $token?>" height="52"  style="cursor:pointer;vertical-align:bottom" onClick="changeChkImg();" title="點擊更換驗證碼">
														</span>
													</label>
                                                    
													<div class="space"></div>

													<div class="clearfix">
														<button type="submit" class="width-35 pull-right btn btn-sm btn-primary">
															<i class="ace-icon fa fa-key"></i>
															<span class="bigger-110">登入</span>
														</button>
														<?php if(isset($DesignBtn)):?>
                                                        <?php if($DesignBtn=='DesignMode'):?>
														<button type="button" onClick="$('#DesignLogin').submit();" class="pull-left btn btn-sm btn-danger">
															<i class="ace-icon fa fa-key"></i>
															<span class="bigger-110">設計者模式登入</span>
														</button>
														<?php endif;?>
                                                        <?php endif;?>
													</div>
													<div class="space-4"></div>
                                                    
												</fieldset>
											</form>
											<?php if(isset($DesignBtn)):?>
                                            <?php if($DesignBtn=='DesignMode'):?>
                                            <form  action="<?php echo site_url(SYSTEM_URL.'/Login/DesignLogin');?>" id="DesignLogin" method="post">
                                            <input type="hidden" name="designLogin" value="DesignLogin" />
                                            </form>
                                            <?php endif;?>
                                            <?php endif;?>
											<div class="space-6"></div>

											
										</div><!-- /.widget-main -->

										
									</div><!-- /.widget-body -->
								</div><!-- /.login-box -->
								<?php if(@$_POST['sys_num']!="" && @$_POST['formAction']!=""):?>
								<div id="forgot-box" class="forgot-box widget-box no-border">
									<div class="widget-body">
										<div class="widget-main">
											<h4 class="header red lighter bigger">
												<i class="ace-icon fa fa-key"></i>
												系統管理者認證
											</h4>

											<div class="space-6"></div>
											<p>
												請輸入您的簡訊認證碼
											</p>

											<form id="smsForm" method="post" autocomplete="off" action="<?php echo @$_POST['formAction']?>">
                                            	<input type="hidden" name="sys_num" id="sys_num" value="<?php echo @$_POST['sys_num']?>"/>
												<fieldset>
													<label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="text" name="sms_code" id="sms_code" maxlength="4" class="form-control" placeholder="簡訊驗證碼" />
															<i class="ace-icon fa fa-envelope"></i>
														</span>
													</label>

													<div class="clearfix">
														<button type="submit" class="width-35 pull-right btn btn-sm btn-danger">
															<i class="ace-icon fa fa-lightbulb-o"></i>
															<span class="bigger-110">送出驗證</span>
														</button>
													</div>
												</fieldset>
											</form>
										</div><!-- /.widget-main -->

										<div class="toolbar center">
											<a href="<?php site_url(SYSTEM_URL."/Login")?>" data-target="#login-box" class="back-to-login-link">
												返回登入
												<i class="ace-icon fa fa-arrow-right"></i>
											</a>
										</div>
									</div><!-- /.widget-body -->
								</div><!-- /.forgot-box -->
								<?php endif;?>
								<!-- /.signup-box -->
							</div><!-- /.position-relative -->

							
						</div>
					</div><!-- /.col -->
				</div><!-- /.row -->
			</div><!-- /.main-content -->
		</div><!-- /.main-container -->

		<!-- basic scripts -->

		<!--[if !IE]> -->
		<script src="<?php echo ASSETS_URL?>/admin/js/jquery-2.1.4.min.js"></script>

		<!-- <![endif]-->

		<!--[if IE]>
		<script src="<?php echo ASSETS_URL?>/admin/js/jquery-1.11.3.min.js"></script>
		<![endif]-->
		<script type="text/javascript">
			if('ontouchstart' in document.documentElement) document.write("<script src='<?php echo ASSETS_URL?>/admin/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="<?php echo ASSETS_URL?>/admin/js/jquery.validate.min.js"></script>
		<!-- inline scripts related to this page -->
		<script type="text/javascript">
			<?php if(@$_POST['sys_num']!="" && @$_POST['formAction']!=""):?>
			jQuery(function($) {
				$('#login-box').removeClass('visible');
				$('#forgot-box').addClass('visible');
			});
			<?php endif;?>
			
			
			jQuery(function($) {
				
				//複寫
				 $.validator.setDefaults({
					errorElement: 'sapn',
					errorClass: 'help-block',
					highlight: function (e) {
						$(e).closest('label.block').removeClass('has-success').addClass('has-error');
					},
					success: function (e) {
						$(e).closest('label.block').removeClass('has-error').addClass('has-success');
						$(e).remove();
					},
					errorPlacement: function (error, element) {
						if(element.is('input[type=checkbox]') || element.is('input[type=radio]')) {
							var controls = element.closest('div[class*="col-"]');
							if(controls.find(':checkbox,:radio').length > 1) controls.append(error);
							else error.insertAfter(element.nextAll('.lbl:eq(0)').eq(0));
						}
						else if(element.is('.select2')) {
							error.insertAfter(element.siblings('[class*="select2-container"]:eq(0)'));
						}
						else if(element.is('.chosen-select')) {
							error.insertAfter(element.siblings('[class*="chosen-container"]:eq(0)'));
						}
						else error.insertAfter(element.parent());
					},
				 });
				
				$('#loginForm').validate({
					rules: {
						'u_id' : {
							required :true
						},
						'u_password' : {
							required :true
						},
						'chknum' : {
							required :true,
							maxlength : 4
						}
					},
					messages:{
						'u_id' : '帳號為必填'	,
						'u_password' : '密碼為必填',
						'chknum' : {
							required : '驗證碼必填',
							maxlength : '驗證碼最多4碼'
						}
					}
				});
				
				$('#smsForm').validate({
					rules: {
						'sms_code' : {
							required :true,
							maxlength : 4
						}
					},
					messages:{
						'sms_code' : {
							required : '簡訊驗證碼必填',
							maxlength : '驗證碼最多4碼'
						}
					}
				});
			 	
			});
		</script>
	</body>
</html>
