<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
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
   <div class="site-wrap">
      <?php $this -> load -> view("www/includes/header.php")?>
      <section class="page_container">
         <div class="page_content">
            <img src="images/left_img1.png" alt="" class="left_img">
            <div class="page_mtitle">Member<b>會員中心</b></div>
            <div class="inner_content">
               <?php $this -> load -> view("www/includes/member_nav.php")?>
               <div class="member_content sell_content ">
                  <form class="form-horizontal member_form" method="post" id="withdrawalForm">
                     <div class="form-group">
                        <label for="price" class="col-md-3 control-label">拍賣點數來源</label>
                        <div class="col-md-9">
                           <select class="form-control" id="sel1">
                              <option value="0"> 電子錢包 ............... <?php echo number_format($user_info['WalletTotal'],0)?></option>
                           </select>
                        </div>
                     </div>
                     <div class="form-group">
                        <label for="deposit_pwd" class="col-md-3 control-label">拍賣點數</label>
                        <div class="col-md-9">
                           <input type="number" name="amount" id="amount" class="form-control" maxlength="10" placeholder="請輸入拍賣點數">
                           <input type="hidden" id="tmpAmount" value="<?php echo $user_info[ 'WalletTotal' ] ?>"/>
                        </div>
                     </div>
                     <div class="form-group">
                        <div class="page_form_btn">
                           <button type="button" class="btn btn-default submit_btn" id="withdrawalBTN" >確認拍賣</button>
                        </div>
                     </div>
                  </form>
                  <div class="member_note">
                     <div class="note_title"><i class="icon-notice"></i>點數拍賣注意事項：</div>
                     <ol class="note_list">
                        <li>為加速您的服務，申請點數拍賣時，務必先關閉您的遊戲。</li>
                        <li>若無您欲轉移之遊戲館，請洽詢線上客服。</li>
                        <li>【黃金俱樂部】因網路因素轉移點數會較慢，還請耐心等候。</li>
                        <li>過於頻繁的轉移動作，系統將自動濾除。</li>
                     </ol>
                  </div>
               </div>
               <!----幣商廣告區-------------------------->
               <div class="col-md-6 col-12">
                  <ul class="bitcone">
                     <?php if(isset($BitCoin)):?>
                     <?php foreach($BitCoin as $row):?>
                     <li><a href="<?php echo ($row["url"] ? $row["url"] : 'javascript:void(0)') ?>" <?php if($row["url"]) echo ' target="_blank"'?>>
                        <img src="<?php echo UPLOADS_URL?>/coinman/<?php echo $row["pic"]?>" width="300" alt=""/>
                        </a>
                     </li>
                     <?php endforeach;?>
                     <?php endif;?>
                  </ul>
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