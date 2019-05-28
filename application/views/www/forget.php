<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
      <script type="text/javascript" src="<?php echo ASSETS_URL?>/www/js/forget.js"></script>
	  	<link rel="stylesheet" href="<?php echo ASSETS_URL ?>/www/css/login.css">
   </head>
   <body >
   <div class="site-wrap">  
	  <div class="row pt-5 mt-0 align-items-center">
          <div class="col-12 m-auto text-center">
            <a href="/"><h2 class="mb-3 site-logo font-weight-bold">B.G.R</h2></a>
            <span class="caption d-block">Buffett Ground Rules</span>
          </div>

          <div class="col-md-4 col-sm-8 col-12 m-auto login-body">
            <div class="mb-5 m-5">
                <span class="caption d-block mb-2 font-secondary font-weight-bold text-center">Forget Password</span>
                <h2 class="site-section-heading text-uppercase text-center font-secondary">查詢密碼</h2>
					<form class="form-horizontal member_form form" id="signForm" method="post">
                  <input type="hidden" name="forget_sms_token" id="forget_sms_token" value="<?php echo @$forget_sms_token?>">
                  <div class="form-group">
                     <label for="userId" class="control-label required">會員帳號</label>
                     <input type="text" class="form-control" name="u_id" id="u_id" maxlength="15"  placeholder="請輸入會員帳號">

                     <!-- <div class="col-sm-offset-2 col-sm-10 warning_txt"><i class="icon-close"></i>帳號已被註冊</div> -->
                  </div>
                  
                  <div class="form-group">
                     <label for="phone" class="control-label required">手機號碼</label>
                        <input type="tel" class="form-control" name="phone" name="phone" id="f_phone" maxlength="10" placeholder="請輸入手機號碼">
                  </div>
                  <div class="form-group">
                     <label for="phone" class="control-label required">輸入驗證碼</label>
                     <input type="text" class="form-control" name="forget_checknum" id="forget_checknum" maxlength="4"placeholder="請輸入驗證碼">
                     <div class="note_txt">
                        <img id="regImg" class="regImg" src="<?php echo site_url("Vcode2")?>?token=<?php echo $token?>&s_name=forget_checknum" onclick="changeChkImg('forget_checknum','regImg')" style="cursor:pointer" title="刷新驗證碼">
                     </div>
                  </div>
                  <div class="form-group">
                     <div class=" text-center">
                        <button type="button" class="btn btn-danger submit_btn" id="submitBtn">送出查詢</button>
                     </div>
                  </div>
               </form>
                

                <div class="mb-5 m-5 text-center">
                  <a href="/">返回</a>|<a href="<?php echo site_url("Manger/register") ?>">加入會員</a>
                </div>
            </div>
              
              <script>
              // Disable form submissions if there are invalid fields
              (function() {
                'use strict';
                window.addEventListener('load', function() {
                  // Get the forms we want to add validation styles to
                  var forms = document.getElementsByClassName('needs-validation');
                  // Loop over them and prevent submission
                  var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                      if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                      }
                      form.classList.add('was-validated');
                    }, false);
                  });
                }, false);
              })();
              </script>

          </div>
          </div>
   </div> 
   <footer class="site-footer">
    <div class="container">
      <div class="row mt-5 text-center">
        <div class="col-md-12">
          <p>
          Copyright &copy; <script>document.write(new Date().getFullYear());</script> All Rights Reserved
          </p>
        </div>
        
      </div>
    </div>
  </footer> 
   </body>
</html>