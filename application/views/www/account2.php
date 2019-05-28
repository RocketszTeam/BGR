<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>

      <script type="text/javascript" src="<?php echo ASSETS_URL?>/www/js/account.js"></script>
      
   </head>
   <body >
   <div class="site-wrap">
      <?php $this -> load -> view("www/includes/header.php")?>
      <section class="page_container">
         <div class="page_content">
            <img src="images/left_img1.png" alt="" class="left_img">
            <div class="page_mtitle">Member<b>會員中心</b></div>
            <div class="inner_content">
               <?php $this -> load -> view("www/includes/member_nav.php")?>
               <div class="member_content">
			   

<ul id="tabsss" class="nav nav-tabs page_tabs">
	<li >
		<a href="#tab1" data-toggle="tab" class="active">
			 基本設定
		</a>
	</li>
	<li>
		<a href="#tab2" data-toggle="tab">
			變更密碼
		</a>
	</li>
	<li>
		<a href="#tab3" data-toggle="tab">
			銀行設定
		</a>
	</li>
	
</ul>



<div id="TabsssContent" class="tab-content">
	<div class="tab-pane fade active show" id="tab1">
		<div id="tab1" class="tab_content">
                        <form class="form-horizontal member_form">
                           <div class="member_txt">會員基本設定</div>
                           <div class="form-group">
                              <label for="name" class="col-sm-2 control-label">會員姓名</label>
                              <div class="col-sm-6">
                                 <input type="text" class="form-control" id="" value="<?php echo $rowMember["u_name"]?>" disabled="">
                              </div>
                           </div>
                           <div class="form-group">
                              <label for="userId" class="col-sm-2 control-label">會員帳號</label>
                              <div class="col-sm-6">
                                 <input type="text" class="form-control" id="" value="<?php echo $user_info['u_id']?>" disabled="">
                              </div>
                           </div>
                           <div class="form-group">
                              <label for="userId" class="col-sm-2 control-label">LINEID</label>
                              <div class="col-sm-6">
                                 <input type="text" class="form-control" id="" value="<?php echo $rowMember["line"]?>" disabled="">
                              </div>
                           </div>
                           <div class="form-group">
                              <label for="phone" class="col-sm-2 control-label">手 機</label>
                              <div class="col-sm-6">
                                 <input type="text" class="form-control" id="" value="<?php echo $rowMember["phone"]?>" disabled="">
                              </div>
                           </div>
                           <div class="form-group">
                              <label for="pwd" class="col-sm-2 control-label">電子錢包</label>
                              <div class="col-sm-4">
                                 <?php echo number_format($user_info['WalletTotal'],0)?>
                              </div>
                              <div class="col-sm-4 note_txt">
                                 <button type="submit" class="btn ">前往儲值</button>
                              </div>
                           </div>
                           <div class="member_note">
                              <span><i class="icon-notice"></i>帳戶修改注意</span>如遇不可抗拒因素，需變更手機號碼，請洽詢客服人員！
                           </div>
                           <div class="form-group">
                              <div class="page_form_btn">
                                 <button type="reset" class="btn btn-default reset_btn">清除</button>
                                 <button type="submit" class="btn btn-default submit_btn ">送出</button>
                              </div>
                           </div>
                        </form>
                     </div>
                     <!-- #tab1 -->
	</div>
	<div class="tab-pane fade" id="tab2">
		<div id="tab2" class="tab_content">
                        <div class="member_txt">變更密碼</div>
                        <form class="form-horizontal member_form accountForm"  method="post">
                           <div class="form-group">
                              <label for="pwd" class="col-sm-2 control-label">原始密碼</label>
                              <div class="col-sm-6">
                                 <input type="password" class="form-control" name="u_password" id="u_password" maxlength="15" placeholder="">
                              </div>
                              <div class="col-sm-4 note_txt">(請輸入原始密碼)</div>
                           </div>
                           <div class="form-group">
                              <label for="pwd" class="col-sm-2 control-label">修改密碼</label>
                              <div class="col-sm-6">
                                 <input type="password" class="form-control" name="new_password" id="new_password" maxlength="15" placeholder="">
                              </div>
                              <div class="col-sm-4 note_txt">(請輸入新密碼)</div>
                           </div>
                           <div class="form-group">
                              <label for="check_pwd" class="col-sm-2 control-label">再次確認密碼</label>
                              <div class="col-sm-6">
                                 <input type="password" class="form-control" name="new_password2" id="new_password2" maxlength="15" placeholder="">
                              </div>
                              <div class="col-sm-4 note_txt">(請再次輸入密碼)</div>
                           </div>
                           <div class="form-group">
                              <div class="page_form_btn">
                                 <button type="reset" class="btn btn-default reset_btn">清除</button>
                                 <button type="submit" class="btn btn-default submit_btn accountBTN">送出</button>
                              </div>
                           </div>
                        </form>
                     </div>
                     <!-- #tab2 -->
	</div>
	<div class="tab-pane fade" id="tab3">
		 <div id="tab3" class="tab_content">
                        <div class="member_txt">轉入帳號</div>
                        <form class="form-horizontal banksetting_form bankForm" method="post">
                           <div class="form-group">
                              <label class="col-md-2 control-label dotted_icon">銀行名稱</label>
                              <div class="col-md-7">
                                 <?php if(@$user_info["bank_num"]==""):?>
                                 <select name="bank_num" id="bank_num" class="form-control">
                                    <option value="">請選擇</option>
                                    <?php if(isset($bankList)):?>
                                    <?php foreach($bankList as $row):?>
                                    <option value="<?php echo $row["num"]?>"><?php echo $row["bank_code"].$row["bank_name"]?></option>
                                    <?php endforeach;?>
                                    <?php endif;?>
                                 </select>
                                 <?php else:?>
                                 <?php echo tb_sql("bank_code","bank_list",$user_info["bank_num"])?>
                                 <?php echo tb_sql("bank_name","bank_list",$user_info["bank_num"])?>
                                 <?php endif;?>
                              </div>
                           </div>
                           <div class="form-group">
                              <label class="col-md-2 control-label dotted_icon">分行名稱</label>
                              <div class="col-md-7">
                                 <?php if(@$user_info["bank_num"]==""):?>
                                 <input name="bank_branch" class="form-control"  placeholder="請填入分行名稱">
                                 <?php else:?>
                                 <?php echo $user_info["bank_branch"]?>
                                 <?php endif;?>
                              </div>
                           </div>
                           <div class="form-group">
                              <label for="bankId" class="col-md-2 control-label dotted_icon">銀行帳號</label>
                              <div class="col-md-7">
                                 <?php if(@$user_info["bank_num"]==""):?>
                                 <input name="bank_account" class="form-control"  placeholder="請填入銀行帳號">
                                 <?php else:?>
                                 <?php echo $user_info["bank_account"]?>
                                 <?php endif;?>
                              </div>
                           </div>
                           <div class="form-group">
                              <label for="bankName" class="col-md-2 control-label dotted_icon">銀行戶名</label>
                              <div class="col-md-7">
                                 <?php if(@$user_info["bank_num"]==""):?>
                                 <input name="account_name" class="form-control"  placeholder="必須與會員名稱及身分證一致">
                                 <?php else:?>
                                 <?php echo $user_info["account_name"]?>
                                 <?php endif;?>
                              </div>
                           </div>
                           <div class="form-group">
                              <div class="page_form_btn">
                                 <button type="reset" class="btn btn-default reset_btn">清除</button>
                                 <button type="submit" class="btn btn-default submit_btn bankBTN">確認儲存</button>
                              </div>
                           </div>
                        </form>
                     </div>
                     <!-- #tab3 -->
	</div>

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