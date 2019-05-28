<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
        <script type="text/javascript" src="<?php echo ASSETS_URL?>/www/js/transfer.js"></script>
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
                    <form class="form-horizontal member_form transfer_form transferForm" id="transferForm">
                        <div class="form-group">
                            <label class="col-md-4 control-label">轉出</label>
                            <div class="col-md-8">
                                 <select name="makers_num_A" id="makers_num_A" class="form-control" required> 
                                    <option value="">請選擇從哪轉出</option>
                                    <option value="0">　　 電子錢包 ............... <?php echo number_format($user_info['WalletTotal'],0)?></option>
                                    <?php if(isset($makers_data2)):?>
                                    <?php foreach($makers_data2 as $row):?>
                                    <option value="<?php echo $row["num"]?>" class="balance_div" data-makersnum="<?php echo $row["num"]?>">　　 <?php echo $row["makers_name"]?></option>
                                    <?php endforeach;?>
                                    <?php endif;?>
                                  </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">轉入目標</label>
                            <div class="col-md-8">
                                <select name="makers_num_B" id="makers_num_B" class="form-control" required> 
                                    <option value="">請選擇轉入位置</option>
                                    <?php if(isset($makers_data)):?>
                                    <?php foreach($makers_data as $row):?>
                                    <option value="<?php echo $row["num"]?>" class="balance_div" data-makersnum="<?php echo $row["num"]?>">　　 <?php echo $row["makers_name"]?></option>
                                    <?php endforeach;?>
                                    <?php endif;?>
                                    <option value="0">　　 電子錢包 ............... <?php echo number_format($user_info['WalletTotal'],0)?></option>
                                  </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="price" class="col-md-4 control-label">轉移點數</label>
                            <div class="col-md-8">
                                <input type="number" class="form-control" id="amount" name="amount" placeholder="請輸入轉移點數">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="page_form_btn">
                                <button type="button" id="transferBTN" class="btn btn-default submit_btn">確認轉移</button>
                            </div>
                        </div>
                    </form>
                    <div class="member_note trans_note">
                        <div class="note_title"><i class="icon-notice"></i>點數轉移注意事項：</div>
                        <ol class="note_list">
                            <li>為加速您的服務，申請點數轉移時，務必先關閉您的遊戲。</li>
                            <li>若無您欲轉移之遊戲館，請洽詢線上客服。</li>
                            <li>【黃金俱樂部】因網路因素轉移點數會較慢，還請耐心等候。</li>
                            <li>過於頻繁的轉移動作，系統將自動濾除。</li>
                        </ol>
                    </div>
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