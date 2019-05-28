<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="<?php echo @$com_keywords?>" />
<meta name="Description" Content="<?php echo @$com_description?>"/>
<meta name="COPYRIGHT" content="Copyright (c) by <?php echo @$com_name?>">
<title><?php echo $com_title?></title>
<script src="<?php echo ASSETS_URL?>/www/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/js/jquery.blockUI.js"></script>
<style type="text/css">
body {
    margin: 0;
}
</style>

<script type="text/javascript">
   var CI_URL = "<?php echo site_url();?>";
   var ASSETS_URL="<?php echo ASSETS_URL?>";
	$(function(){
		var windowHight=window.screen.availHeight-100;
		
		$('#gameFrame').attr('height',windowHight);
		<?php if(@$_GET['gm']==3 && @$is_mobile=='Y'):?>
		
		parent.document.getElementById("gameFrame").height=document.body.scrollHeight-20
		<?php endif;?>
		<?php if(@$_GET['GameCode']==''):?>
		//$('#gameFrame').attr('height',windowHight);
		<?php else:?>
		
		var iHeight=window.screen.availHeight / 2; //視窗的高度;
		//$('#gameFrame').attr('height',iHeight);
		<?php endif;?>
		//$('#gameFrame').attr('marginheight',windowHight);
		//parent.document.getElementById("gameFrame").noResize=false;
		/*
		window.document.body.onbeforeunload = function(){

			return '★★ 確定離開此頁面？？ ★★';

		}	
		*/	
	});	
	$(document).ready(function(){
		$.blockUI({ message: '<img src="' + ASSETS_URL + '/www/images/009.gif" />' });
	});
	$(window).on('load', function(){
		//$.unblockUI;
		window.parent.$.unblockUI(); 
	});	
</script>
</head>

<body>

<iframe id="gameFrame" src="<?php echo $link?>" frameborder="0" width="100%"  scrolling="auto" marginheight="0" marginwidth="0"></iframe>
</body>
</html>