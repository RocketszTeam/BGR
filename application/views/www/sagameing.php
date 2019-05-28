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
   var mobile_reg=/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;
	$(function(){
		if(mobile_reg.test(navigator.userAgent)){	//手機板
			$('#mobile').attr('value','true');
		}else{	//電腦版
			$('#mobile').attr('value','false');
		}
		$('#gameForm').submit();
	});	
	
</script>
</head>

<body>
<form id="gameForm" method="post" action="<?php echo $formAction?>">
<input name="username" type="hidden" value="<?php echo @$_GET['u_id']?>" />
<input name="token" type="hidden" value="<?php echo @$_GET['token']?>" />
<input type="hidden" name="lang" value="zh_TW" />
<input type="hidden" name="mobile" id="mobile"  />
<!--<input type="hidden" name="h5web" value="true" />-->
<input type="hidden" name="lobby" value="<?php echo @$lobby?>" />
</form>
</body>
</html>