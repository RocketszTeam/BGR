<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
      <link rel="stylesheet" href="<?php echo ASSETS_URL?>/www/css/member.css" />
      <script type="text/javascript" src="<?php echo ASSETS_URL?>/www/js/withdrawal.js"></script>
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
      <?php $this -> load -> view("www/includes/header.php")?>


      <section class="page_container">
         <div class="page_content">
            <img src="images/left_img1.png" alt="" class="left_img">
            <div class="page_mtitle">Member<b>會員中心</b></div>
            <div class="inner_content">
               <?php $this -> load -> view("www/includes/member_nav.php")?>
               <div class="member_content sell_content ">
                  <p style="margin-top:1%;">
                    已成功送出點數拍賣申請，<br>此次拍賣紀錄請至<a href="<?php echo site_url("History/sell")?>">會員中心--拍賣紀錄</a>查詢。
                 </p>
                 <button type="button" onClick="location.href='<?php echo site_url("Manger/withdrawal")?>'" class="btn btn-danger">返回</button>                  
                  
               </div>

            </div>
            <!--inner_content end-->
            <img src="images/right_img1.png" alt="" class="right_img">
         </div>
      </section>
      <!-- Footer -->
      <?php $this -> load -> view("www/includes/footer.php")?>
   </body>
</html>