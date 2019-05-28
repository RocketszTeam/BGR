<!DOCTYPE HTML>
<html>
    <head>
        <?php $this -> load -> view("www/includes/head.php")?>
        <link rel="stylesheet" href="<?php echo ASSETS_URL?>/www/css/games.css" />

    </head>
    <body>
    <?php $this -> load -> view("www/includes/header.php")?>


        <!-- Main -->
    <div id="main" class="">    
	<div class="inner">
		<div class="title"><span class="top-title">SA老虎機</span></div>
		<ul class="nav nav-tabs">
		   <li class="<?php if(@$_GET['kind']=='') echo ' active'?>"><a href="<?php echo site_url(uri_string())?>">全部</a></li>
		   <?php if(isset($kindList)):?> 
		   <?php foreach($kindList as $row):?>
		   <li class="<?php if(@$_GET['kind']==$row["num"]) echo ' active'?>"><a href="<?php echo site_url("Slot/qt?kind=".$row["num"])?>"><?php echo $row["kind"]?></a></li>
		   <?php endforeach;?> 
		   <?php endif;?>
		</ul>

		<div class="tab-content">
		  <div id="G1" class="tab-pane fade in active">
				<div class="row" style="margin:5px;">  
					<?php if(isset($gameList)):?>
					<?php foreach($gameList as $row):?> 
					<div class="col-md-3 col-sm-3 col-xs-6">
						<div class="cle-g-box"><a href="<?php echo site_url("Opengame?gm=".$row["makers_num"]."&GameCode=".$row["game_code"])?>" target="_blank" title="<?php echo $row["game_name"]?>"><img src="<?php echo UPLOADS_URL?>/games/<?php echo $row["pic1"] ?>" class="img-responsive" width="100%"></a>
							<div class="cgb-info"><h3><?php echo $row["game_name"]?></h3><a href="<?php echo site_url("Opengame?gm=".$row["makers_num"]."&GameCode=".$row["game_code"])?>" target="_blank" title="<?php echo $row["game_name"]?>" class="game-in">進入遊戲</a></div>
						</div>
					</div>
					<?php endforeach;?> 
					<?php endif;?>
			   </div>
		  </div>
			
		</div>
    </div>            
    </div>

        <!-- Footer -->
        <?php $this -> load -> view("www/includes/footer.php")?>

    </body>
</html>