<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>

      <script type="text/javascript">
         $(function(){
           $('.payment').click(function(){
             $('#down-alert').hide();
             if($(this).attr('data-payment')=='Credit'){
               $("#down-alert").fadeToggle();
               $('#depositForm').prop('target','_blank');
             }else{
               $('#depositForm').prop('target','_self');
             }
           });
           
         });
      </script>
   </head>
   <body>
   <div class="site-wrap">
      <?php $this -> load -> view("www/includes/header.php")?>
      <section class="page_container">
         <div class="page_content">
            <img src="images/left_img1.png" alt="" class="left_img">
            <div class="page_mtitle">Member<b>會員中心</b></div>
            <div class="inner_content">
               <?php $this -> load -> view("www/includes/member_nav.php")?>
               <div class="member_content">
                  <div class="tab_container">
                     <div id="tab1" class="tab_content">
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
                        <!--page btn end-->
                     </div>
                  </div>
                  <!-- .tab_container -->
               </div>
            </div>
            <!--inner_content end-->
            <img src="images/right_img1.png" alt="" class="right_img">
         </div>
      </section>
      <!-- Footer -->
      <?php $this -> load -> view("www/includes/footer.php")?>
	  </div>
   </body>
</html>