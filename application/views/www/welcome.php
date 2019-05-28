<!DOCTYPE HTML>
<html>
    <head>
        <?php $this -> load -> view("www/includes/head.php")?>
        <link rel="stylesheet" href="<?php echo ASSETS_URL?>/www/css/games.css" />
        <!-- 獎金池-->
        <link rel="stylesheet" href="<?php echo ASSETS_URL?>/www/css/counter.css"> 
        <script src="<?php echo ASSETS_URL?>/www/js/counter.js" type="text/javascript" charset="utf-8"></script>
    </head>
    <body>
    <?php $this -> load -> view("www/includes/header.php")?>


        <!-- Main -->
            <div id="main" class="">    

				<div class="inner">
                <div class="games_title"><h2>註冊成功Welcoe to BOMA～</h2></div>
                <p style="color:white; font-size:24px;">感謝您註冊會員，歡迎您～</p>
				</div>
                
            </div>

        <!-- Footer -->
        <?php $this -> load -> view("www/includes/footer.php")?>

    </body>
</html>