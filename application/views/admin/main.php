<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta charset="utf-8" />
		<title><?php echo $title?></title>

		<meta name="description" content="" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

		<!-- bootstrap & fontawesome -->
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/bootstrap.min.css" />
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/font-awesome.css" />

		<!-- page specific plugin styles -->
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/jquery-ui.min.css" />
        <link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/select2.min.css" />
        <link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/colorbox.min.css" />

        <link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/bootstrap-select/css/bootstrap-select.css" />
        <link rel='stylesheet' href='<?php echo ASSETS_URL?>/admin/bootstrap-datepicker/css/bootstrap-datepicker.css' >
        <link rel='stylesheet' href='<?php echo ASSETS_URL?>/admin/bootstrap-datetimepicker/css/bootstrap-datetimepicker.css' >
        <link rel='stylesheet' href='<?php echo ASSETS_URL?>/admin/bootstrap-treeview/bootstrap-treeview.min.css' >
        <link rel='stylesheet' href='<?php echo ASSETS_URL?>/admin/bootstrap-fileinput/css/fileinput.css' >

		<!-- text fonts -->
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/fonts.googleapis.com.css" />

		<!-- ace styles -->
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

		<!--[if lte IE 9]>
			<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/ace-part2.min.css" class="ace-main-stylesheet" />
		<![endif]-->
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/ace-skins.min.css" />
		<link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/ace-rtl.min.css" />

		<!--[if lte IE 9]>
		  <link rel="stylesheet" href="<?php echo ASSETS_URL?>/admin/css/ace-ie.min.css" />
		<![endif]-->
		<?php if(!empty($css)):?>
        <?php foreach($css as $uri):?>
        <link href="<?php echo $uri?>" rel="stylesheet" type="text/css" />
        <?php endforeach;?>
        <?php endif;?>
        <script type="text/javascript">
             var CI_URL = "<?php echo site_url();?>";
             var ASSETS_URL="<?php echo ASSETS_URL?>";
        </script>
        
        
		<!-- ace settings handler -->
		<script src="<?php echo ASSETS_URL?>/admin/js/ace-extra.min.js"></script>
		<!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->
        
		<!--[if lte IE 8]>
		<script src="<?php echo ASSETS_URL?>/admin/js/html5shiv.min.js"></script>
		<script src="<?php echo ASSETS_URL?>/admin/js/respond.min.js"></script>
		<![endif]-->
        
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
        
        
		<script src="<?php echo ASSETS_URL?>/admin/js/bootstrap.min.js"></script>
        <script src="<?php echo ASSETS_URL?>/admin/js/jquery-ui.min.js"></script>
        <script src="<?php echo ASSETS_URL?>/admin/js/select2.min.js"></script>
        <script src="<?php echo ASSETS_URL?>/admin/bootstrap-select/js/bootstrap-select.js"></script>
        <script src="<?php echo ASSETS_URL?>/admin/bootstrap-select/js/defaults-zh_TW.js"></script>
        <script src="<?php echo ASSETS_URL?>/admin/bootstrap-treeview/bootstrap-treeview.min.js"></script>
		<script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/bootstrap-datepicker/locales/bootstrap-datepicker.zh-TW.min.js"></script>
        <script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/bootstrap-datepicker/js/jqdate.js"></script>
        
		<script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js"></script>
        <script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-TW.js"></script>
        <script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/bootstrap-datetimepicker/js/jqdatetime.js"></script>
        
        <script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/js/laydate/laydate.js"></script>
        
        <script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/bootstrap-fileinput/js/fileinput.js"></script>
        <script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/bootstrap-fileinput/js/locales/zh-TW.js"></script>        
		<script src="<?php echo ASSETS_URL?>/admin/js/jquery.validate.min.js"></script>
		<script src="<?php echo ASSETS_URL?>/admin/js/jquery.inputlimiter.min.js"></script>
        <script src="<?php echo ASSETS_URL?>/admin/js/autosize.min.js"></script>
		<script src="<?php echo ASSETS_URL?>/admin/js/jquery.colorbox.min.js"></script>
        <script src="<?php echo ASSETS_URL?>/admin/js/jquery.blockUI.js"></script>
        <script src="<?php echo ASSETS_URL?>/admin/js/custom.js"></script>
		<?php if(!empty($js)):?>
        <?php foreach($js as $uri):?>
         <script type="text/javascript" src="<?php echo $uri?>"></script>
        <?php endforeach;?>
        <?php endif;?>
        <script type="text/javascript">
        	$(function(){
				autosize($('textarea[class*=autosize]'));
				$('textarea.limited').inputlimiter({
					remText: '已經輸入%n個字',
					limitText: '最多允許%n個字'
				});
			});
        </script>
        
        <!--select2 下拉選單 -->
        <script type="text/javascript">
			$(function(){
				$('.select2').css('width','100%').select2({allowClear:true});			
			});
		</script>
                
        <!--提示系統 --->
        <script language="javascript" src="<?php echo ASSETS_URL?>/admin/SwfObjectDemo/jquery.titlealert.js"></script>
        <script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/SwfObjectDemo/swfobject.js"></script>
        <script type="text/javascript">
            var order_msg;
            var myTitle=$(document).attr("title");
            //IE正常顯示 其他瀏覽器無法執行 還會影響其他功能
            var flashvars = {
            };
            var params = {
                wmode: "transparent"
            }; 
            var attributes = {};
            swfobject.embedSWF("<?php echo ASSETS_URL?>/admin/SwfObjectDemo/sound.swf", "sound", "1", "1", "9.0.0", "expressInstall.swf", flashvars, params, attributes);
            
            $(function(){
                <?php if(!in_array($web_root_u_power,array(4,5,6))):	//股東總代代理不顯示?>
                checkOrder();
                var int=setInterval("checkOrder()",15000);	//設定一分鐘
                <?php endif;?>
            })
            function checkOrder(){
                $.ajax({
                    type: "POST",
                    url: CI_URL + 'module/Checkorder',
                    cache: false,
                    dataType:"json"
                }).done(function( data ) {  		
                    order_msg='';
                    for(i=0;i < data.length;i++){
                        order_msg+='您有' + data[i]["rowCount"] +'筆' + data[i]["rowTitle"]+'尚未處理！\n';
                        for(j=0;j < data[i]["details"].length;j++){
                            if( data[i]["details"][j]["no_sound"]=='0'){
                               <?php if($web_root_u_power!='1'):	//設計者模式 不要叫很吵?> 
                               play();
                               <?php endif;?> 
                            }
                        }
                        $('#' + data[i]["objID"]).html(data[i]["rowCount"]);
                    }
                    
                    //console.log(msg);
                    if(data.length > 0){
                        $.titleAlert("【您有"+data.length+"則新的通知】","【　　　　　　　】",{stopOnFocus:false});	//閃爍標題
                        //console.log(msg);
						<?php if($web_root_u_power!='1'):	//設計者模式 不要顯示桌面通知 很煩?> 
                        NotificationShow();//桌面通知
						<?php endif;?>
                       
                    }else{
                        $.titleAlert(myTitle,myTitle,{stopOnFocus:false});	//閃爍標題
                        $('#in_stock_span').html('0');
                    }
                    
                });
                        
                
            }
            function play() {
				/*
                var sound = document.getElementById("sound");
                if (sound) {
                    sound.SetVariable("f", '<?php echo ASSETS_URL?>/admin/SwfObjectDemo/msg2.mp3');
                    sound.GotoFrame(1);
                }
				*/
				var audio = document.createElement("audio");
				audio.src = "<?php echo ASSETS_URL?>/admin/SwfObjectDemo/msg2.mp3";
				audio.play();            
			}	
          
            //桌面通知
            function NotificationShow(){
                if (window.Notification){
                    if(Notification.permission==='granted'){
                        var options={
                            body:order_msg,
                            renotify:true,
                            noscreen:true,//手機推播
                            tag:"myNotification",
                            icon:"<?php echo ASSETS_URL?>/www/images/logo.gif",
                        }
                        var notification = new Notification('系統通知',options);
                        /*
                        //3秒後關閉
                        notification.onshow = function () {
                            setTimeout(function () {
                                notification.close();
                            },3000);
                        };
                        */
                    }else{
                        Notification.requestPermission();
                    }
                }
            }
        </script>
        <!--提示系統 --->
        
	</head>

	<body class="skin-2">
		<?php $this->load->view('admin/web_header.php')?>

		<div class="main-container ace-save-state" id="main-container">
			<script type="text/javascript">
				try{ace.settings.loadState('main-container')}catch(e){}
			</script>

			<?php $this->load->view('admin/web_sidebar.php')?>

			<div class="main-content">
				<div class="main-content-inner">
					<?php $this->load->view('admin/web_breadcrumb.php')?>

					<div class="page-content">
                    	<?php $this->load->view('admin/ace-settings.php')?>
						
                        <!-- /.ace-settings-container -->
                        
						<div class="page-header" style="display:none">
							<h1>
								jQuery UI
								<small>
									<i class="ace-icon fa fa-angle-double-right"></i>
									Restyling jQuery UI Widgets and Elements
								</small>
							</h1>
						</div>
                        <!-- /.page-header -->


						<div class="row">
							<div class="col-xs-12">
                            	<?php (isset($alert) ? $this->load->view("admin/alert") : ""); ?>
								<!-- 主要內容 開始 -->
                                <?php echo (!empty($body) ? $body : "")?>  	    
								<!-- 主要內容 結束 -->
							</div><!-- /.col -->
						</div><!-- /.row -->
					</div>
                    <!-- /.page-content -->
				</div>
			</div><!-- /.main-content -->

			<div class="footer">
				<div class="footer-inner">
					<div class="footer-content">
						<span class="bigger-120">
							<span class="blue bolder">管理平台</span>
							Application &copy; 2016
						</span>

						&nbsp; &nbsp;
						<!--<span class="action-buttons">
							<a href="#">
								<i class="ace-icon fa fa-twitter-square light-blue bigger-150"></i>
							</a>

							<a href="#">
								<i class="ace-icon fa fa-facebook-square text-primary bigger-150"></i>
							</a>

							<a href="#">
								<i class="ace-icon fa fa-rss-square orange bigger-150"></i>
							</a>
						</span>-->
					</div>
				</div>
			</div>

			<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
				<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
			</a>
		</div><!-- /.main-container -->
		
        <div id="sound"></div>
		<!-- ace scripts -->
		<script src="<?php echo ASSETS_URL?>/admin/js/ace-elements.min.js"></script>
		<script src="<?php echo ASSETS_URL?>/admin/js/ace.min.js"></script>
	</body>
</html>
