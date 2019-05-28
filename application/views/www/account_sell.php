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
			   
                           <table class="table table-striped history member_table member_record">
                           <thead>
                              <tr>
                                 <th>時    間</th>
                                 <th>金    額</th>
                                 <th>處理結果</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php if (isset( $result )): ?>
                              <?php foreach ($result as $row): ?>
                              <tr>
                                 <td data-th="時間"><?php echo $row["buildtime"]?></td>
                                 <td data-th="金額">
                                    <?php echo number_format($row["amount"],0)?>         
                                 </td>
                                 <td data-th="處理結果">
                                    <p class="<?php echo ($row["keyin1"]==1  ? 'text-success' : 'text-danger')?>"> <?php echo inNumberString($sellKeyin1,$row["keyin1"])?> </p>
                                 </td>
                              </tr>
                              <?php endforeach; ?>
                              <?php endif; ?>
                           </tbody>
                        </table>
                        <!--page btn start-->
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