<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
   </head>
   <body>
      <?php $this -> load -> view("www/includes/header.php")?>
      <section class="page_container esport_container">
         <div class="page_content">
            <div class="page_mtitle">Member<b>會員中心</b></div>
            <div class="inner_content">

            <?php $this -> load -> view("www/includes/member_nav.php")?>

            <div class="member_content">
			   
            <table class="table table-striped accounts">
					<thead>
							  <tr>
								<th class="green-bg">時     間</th>
								<th class="green-bg">類     型</th>
								<th class="green-bg">金     額 </th> 
							  </tr>
							  
					</thead>
					 <tbody>
						<?php if(isset($result)):?>
                        <?php foreach($result as $row):?>
                        <tr>
                             <td><?php echo $row["buildtime"]?></td>
                             <td>
                                  <p><?php echo tb_sql("kind","wallet_kind",$row["kind"])?></p>
                                  <p><?php if($row['kind'] == 3 || $row['kind']==4):	//轉出轉入遊戲?></p>
                                      <p><?php echo ($row['kind']==3 ?'轉入' :'轉出').tb_sql("makers_name","game_makers",$row['makers_num'])?></p>
                                      <p class="text-danger">剩餘點數：<?php echo number_format($row["makers_balance"],2,'.',',')?></p>
                                  <?php endif;?>
                             </td>
                             <td> <p class="<?php echo ($row["points"] < 0 ?  'text-danger' : 'text-success')?>"> <?php echo number_format($row["points"],0)?> </p></td> 
                       </tr>
                       <?php endforeach;?>
                       <?php endif;?>
					</tbody>
				</table>

            <div class="text-center">                       
				<?php echo @$pagination ?>
            </div>             




            </div>


            </div>
            <!--inner_content end-->
         </div>
      </section>
      <?php $this -> load -> view("www/includes/footer.php")?>
   </body>
</html>