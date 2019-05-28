<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <script type="text/javascript" src="<?php echo ASSETS_URL?>/www/js/register.js"></script>
	<link rel="stylesheet" href="<?php echo ASSETS_URL ?>/www/css/login.css">
   </head>
   <body >
   
	   <div class="site-wrap">
        <div class="row pt-5 mt-0 align-items-center">
          <div class="col-12 m-auto text-center">
            <a href="/"><h2 class="mb-3 site-logo font-weight-bold">B.G.R</h2></a>
          </div>

          <div class="col-md-4 col-sm-8 col-12 m-auto login-body">
            <div class="mb-5 m-5">
                <span class="caption d-block mb-2 font-secondary font-weight-bold text-center">Join Member</span>
                <h2 class="site-section-heading text-uppercase text-center font-secondary">會員註冊</h2>

                <form class="form-horizontal member_form form" id="signForm" method="post">
                  <input type="hidden" name="sms_token" id="sms_token" value="<?php echo @$sms_token?>">
                  <div class="form-group">
                     <label for="name" class="control-label required">會員姓名</label>
                        <input type="text" class="form-control" id="u_name" name="u_name" maxlength="5" placeholder="請輸入真實姓名，以免影響您的權益">
                  </div>
                  <div class="form-group">
                     <label for="userId" class="control-label required">會員帳號</label>
                        <input type="text" class="form-control" name="u_id" id="u_id" maxlength="15"  placeholder="6~13碼英文數字組合">
                     <!-- <div class="col-sm-offset-2 col-sm-10 warning_txt"><i class="icon-close"></i>帳號已被註冊</div> -->
                  </div>
                  <div class="form-group">
                     <label for="password" class="control-label required">設定密碼</label>
                     <input type="password" class="form-control" name="u_password" id="u_password" maxlength="15" placeholder="6~13碼英文數字組合">
                     <!-- <div class="col-sm-offset-2 col-sm-10 warning_txt"><i class="icon-close"></i>密碼輸入錯誤</div> -->
                  </div>
                  <div class="form-group">
                     <label for="u_password2" class="control-label required">確認密碼</label>
                     <input type="password" class="form-control" name="u_password2" id="u_password2" maxlength="15"  placeholder="再次輸入密碼">
                  </div>
                  <div class="form-group">
                     <label for="phone" class="control-label required">手機號碼</label>
                        <input type="text" class="form-control" name="phone" id="phone" maxlength="10"placeholder="請輸入您的手機" >
                        <button class="code_btn btn-danger btn-block" id="smsBTN">獲取驗證碼</button>
                  </div>
                  <div class="form-group">
                     <label for="code" class="control-label required">輸入簡訊驗證碼</label>
                     <input type="text" class="form-control" name="sms_code" id="sms_code" maxlength="4" placeholder="請輸入簡訊驗證碼">
                     <!-- <div class="col-sm-offset-2 col-sm-10 warning_txt"><i class="icon-close"></i>驗證碼輸入錯誤</div> -->
                  </div>
                  <div class="form-group">
                     <label for="name" class="control-label required">LINE ID</label>
                     <input type="text" class="form-control" id="line" name="line" maxlength="20" placeholder="請輸入LINE ID">

                  </div>
                  <div class="form-group">
                     <label for="phone" class=" control-label required">驗證碼</label>
                     <input type="text" class="form-control" name="checknum" id="checknum" maxlength="4" placeholder="請輸入圖形驗證碼">
                     <img id="regImg" src="<?php echo site_url("Vcode2")?>?token=<?php echo $token?>&s_name=reg_checknum" onclick="changeChkImg('reg_checknum','regImg')" style="cursor:pointer;" title="刷新驗證碼">
                  </div>
                  <div class="form-check">
                     <label class="form-check-labe"style="background: transparent;">
                     <input type="checkbox" class="form-check-input agree" id="squaredThree">
                     <span class="link-color1" style="font-size:.8em;">我已年滿18歲</span>
                     </label>
                  </div>
                  <div class="form-group">
                     <div class="mb-5 m-5 text-center">
                         <button type="reset" class="btn btn-danger reset_btn">清除</button>|<button type="submit" class="btn btn-danger submit_btn" id="submitBtn">送出</button>
                     </div>
                  </div>
               </form>
				
				
				
				
                
                <div class="mb-5 m-5 text-center">
                  <a href="/">返回</a>｜<a href="/">登入會員</a>
                </div>
            </div>
              
<!--              <script>-->
<!--              // Disable form submissions if there are invalid fields-->
<!--              (function() {-->
<!--                'use strict';-->
<!--                window.addEventListener('load', function() {-->
<!--                  // Get the forms we want to add validation styles to-->
<!--                  var forms = document.getElementsByClassName('needs-validation');-->
<!--                  // Loop over them and prevent submission-->
<!--                  var validation = Array.prototype.filter.call(forms, function(form) {-->
<!--                    form.addEventListener('submit', function(event) {-->
<!--                      if (form.checkValidity() === false) {-->
<!--                        event.preventDefault();-->
<!--                        event.stopPropagation();-->
<!--                      }-->
<!--                      form.classList.add('was-validated');-->
<!--                    }, false);-->
<!--                  });-->
<!--                }, false);-->
<!--              })();-->
<!--              </script>-->

          </div>
          </div>
      </div>
 
  
   <footer class="site-footer">
    <div class="container">
      <div class="row mt-5 text-center">
        <div class="col-md-12">
          <p><?php $this -> load -> view("www/includes/body_js.php")?>
          Copyright &copy; <script>document.write(new Date().getFullYear());</script> All Rights Reserved
          </p>
        </div>
        
      </div>
    </div>
  </footer> 
	  
   </body>
</html>