<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
      <script type="text/javascript" src="<?php echo ASSETS_URL?>/www/js/account.js"></script>
   </head>
   <body>
      <?php $this -> load -> view("www/includes/header.php")?>
      <section class="site-wrap">
         <div class="page_content">
            <div class="page_mtitle">Member<b>會員中心</b></div>
            <div class="inner_content">

            <?php $this -> load -> view("www/includes/member_nav.php")?>

            <div class="member_content">
			   

               <ul id="tabsss" class="nav nav-tabs page_tabs">
                  <li>
                     <a href="#tab1" data-toggle="tab" class="active">基本設定</a>
                  </li>
                  <li>
                     <a href="#tab2" data-toggle="tab">變更密碼</a>
                  </li>
                  <li>
                     <a href="#tab3" data-toggle="tab">銀行設定</a>
                  </li>
                  
               </ul>

               <div id="TabsssContent" class="tab-content">
                  <div class="tab-pane fade active show" id="tab1">
                     <div id="tab1" class="tab_content">
                                       <form class="form-horizontal member_form">
                                          <div class="member_txt">基本資料</div>
                                          <div class="form-group">
                                             <label for="name" class="control-label">會員姓名</label>
                                             <input type="text" class="form-control" id="" value="<?php echo $rowMember["u_name"]?>" disabled="">
                                          </div>
                                          <div class="form-group">
                                             <label for="userId" class="control-label">會員帳號</label>
                                             <input type="text" class="form-control" id="" value="<?php echo $user_info['u_id']?>" disabled="">
                                          </div>
                                          <div class="form-group">
                                             <label for="userId" class="control-label">LINEID</label>
                                             <input type="text" class="form-control" id="" value="<?php echo $rowMember["line"]?>" disabled="">
                                          </div>
                                          <div class="form-group">
                                             <label for="phone" class="control-label">手 機</label>
                                             <input type="text" class="form-control" id="" value="<?php echo $rowMember["phone"]?>" disabled="">
                                          </div>
                                          <div class="form-group mb-3">
                                             <label for="pwd" class="control-label">電子錢包</label>
                                             <input type="text" class="form-control" id="" value="<?php echo number_format($user_info['WalletTotal'],0)?>" disabled="">
                                             <button type="submit" class="btn btn-danger btn-block">前往儲值</button>
                                          </div>

                                          <div class="alert alert-danger">
                                             <strong>Notice.</strong>帳戶修改注意，如遇不可抗拒因素，需變更手機號碼，請洽詢客服人員！
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
                                             <label for="pwd" class="control-label">原始密碼</label>
                                             <input type="password" class="form-control" name="u_password" id="u_password" maxlength="15" placeholder="請輸入原始密碼">
                                             <!-- <div class="note_txt">(請輸入原始密碼)</div> -->
                                          </div>
                                          <div class="form-group">
                                             <label for="pwd" class="control-label">修改密碼</label>
                                             <input type="password" class="form-control" name="new_password" id="new_password" maxlength="15" placeholder="請輸入新密碼">
                                             <!-- <div class="note_txt">(請輸入新密碼)</div> -->
                                          </div>
                                          <div class="form-group">
                                             <label for="check_pwd" class="control-label">再次確認密碼</label>
                                             <input type="password" class="form-control" name="new_password2" id="new_password2" maxlength="15" placeholder="請再次輸入密碼">
                                             <!-- <div class="note_txt">(請再次輸入密碼)</div> -->
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
                                             <label class="control-label dotted_icon">銀行名稱</label>

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
                                          <div class="form-group">
                                             <label class="control-label dotted_icon">分行名稱</label>
                                                <?php if(@$user_info["bank_num"]==""):?>
                                                <input name="bank_branch" class="form-control"  placeholder="請填入分行名稱">
                                                <?php else:?>
                                                <?php echo $user_info["bank_branch"]?>
                                                <?php endif;?>
                                          </div>
                                          <div class="form-group">
                                             <label for="bankId" class="control-label dotted_icon">銀行帳號</label>
                                                <?php if(@$user_info["bank_num"]==""):?>
                                                <input name="bank_account" class="form-control"  placeholder="請填入銀行帳號">
                                                <?php else:?>
                                                <?php echo $user_info["bank_account"]?>
                                                <?php endif;?>
                                          </div>
                                          <div class="form-group">
                                             <label for="bankName" class="control-label dotted_icon">銀行戶名</label>
                                                <?php if(@$user_info["bank_num"]==""):?>
                                                <input name="account_name" class="form-control"  placeholder="必須與會員名稱及身分證一致">
                                                <?php else:?>
                                                <?php echo $user_info["account_name"]?>
                                                <?php endif;?>
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


            </div>
            <!--inner_content end-->
         </div>
      </section>
      <?php $this -> load -> view("www/includes/footer.php")?>
   </body>
</html>