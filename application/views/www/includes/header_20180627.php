<!-- Header -->
<header id="header">
      <div class="inner">         
            <div class="row">
            	<div class="col-lg-3 text-center" style="margin:auto;">
				          <div class="topLogo"><a href="<?php echo site_url()?>"><img src="<?php echo ASSETS_URL?>/www/images/logo.png" alt="BOMA" class="img-fluid"></a></div>
              	</div>

            <!---會員登入---->
              <?php if(isset($isLogin) && !$isLogin):  //登入前?>
              <?php if(!$this->agent->is_mobile()): //電腦版登入?>
              <div class="col-lg-9 d-none d-md-block" style="margin:auto;">               
                		
                    <div class="loginBox">
                      <form  id="LoginForm" autocomplete="off"  method="post">
                        <div class="row">                                                  
                        <div class="col">
                              <input type="hidden" name="rtn" id="rtn" value="<?php echo $this->input->get('rtn')?>" />
                              <input id="login_u_id" name="login_u_id" class="clearVal" type="text" placeholder="帳號" maxlength="20">
                        </div>
                        <div class="col">
                              <div class="input-group">                             
                              <input id="login_u_password" name="login_u_password"  class="clearVal" type="password" placeholder="密碼" maxlength="20">
                              <div class="input-group-append">
                                  <a class="button btn-warning btn-forgot" href="<?php echo site_url("Forget")?>"><i class="fas fa-question"></i></a>
                              </div>
                              </div>
                        </div>   				

                        <div class="col">
                              <div class="vcode_box"><img id="chkImg" src="<?php echo site_url("Vcode2")?>?token=<?php echo $token?>"  class="img-fluid" alt="刷新驗證碼" onclick="changeChkImg()"></div>
                              <input id="chknum" name="chknum" type="tel" class="clearVal" placeholder="驗證碼" maxlength="4">
                        </div>
                          
                        <div class="col">
                            <div class="sign_btn">
                                  <a href="#" onclick="$('#LoginForm').submit();" class="button btn-warning">登入</a>
                                  <!-- <a class="button signin btn-warning" href="member.php">登入</a> -->
                                  <a class="button btn-danger" value="會員註冊" onclick="location.href='<?php echo site_url("Manger/register")?>'">免費註冊</a>                                   
							</div>    
						</div>
						
                      </div>
					  
                    </form>  
                  </div>                                  
              </div>
              <?php endif;?>
                <!-- 會員登入之後 取代帳號登入 -->
                <?php else: //登入後?>
                <div class="col-lg-8 loginBox  form-inline"　style="margin:auto;">                  
                    <div class="login_in"><?php echo $user_info["u_name"]?>，歡迎光臨～</div>
                    <div class="login_dot">剩餘點數：<?php echo number_format($user_info['WalletTotal'],0)?>點</div>
                    <div class="signin">
						<a class="button" onclick="location.href='<?php echo site_url("Manger/account")?>'">會員中心</a>
						<a class="button button2" onclick="location.href='<?php echo site_url("Index/logout")?>'" >登出</a>
					</div>                  
                </div>
                <?php endif;?>
            </div>
            <?php if($this->agent->is_mobile() && isset($isLogin) && !$isLogin): //手機板登入按鈕 未登入才顯示?>  
            <!-- 手機板會員登入按鈕 -->
            <div class="d-md-none m_login">
              <button class="button" data-toggle="modal" data-target="#m_login">會員登入</button>
            </div>                
            <?php endif;?>
      </div>

<?php $this -> load -> view("www/includes/marquee.php")?> 
<?php $this -> load -> view("www/includes/nav.php")?>                 
<?php $this -> load -> view("www/includes/banner.php")?>                 
                
  <!-- 手機板會員登入視窗 -->
<?php if($this->agent->is_mobile()): //手機板登入?>                 
<div class="modal fade" id="m_login">
    <div class="modal-dialog">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">會員登入</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
          <div class="loginBox">
                    <form id="LoginForm" autocomplete="off"  method="post">                                                                         
                            
                              <input type="hidden" name="rtn" id="rtn" value="<?php echo $this->input->get('rtn')?>" />
                              <input id="login_u_id" name="login_u_id" class="clearVal" type="text" placeholder="帳號" maxlength="20">
                            
                            
                              <div class="input-group">                             
                              <input id="login_u_password" name="login_u_password"  class="clearVal" type="password" placeholder="密碼" maxlength="20">
                              <div class="input-group-append">
                                  <a class="button btn-warning btn-forgot" href="<?php echo site_url("Forget")?>"><i class="fas fa-question"></i></a>
                              </div>
                              </div>                             
                                    
                      
                      <div>
                          
                          <div class="vcode_box"><img id="chkImg" src="<?php echo site_url("Vcode2")?>?token=<?php echo $token?>"  class="img-fluid" alt="刷新驗證碼" onclick="changeChkImg()"></div>
                          <input id="chknum" name="chknum" type="tel" class="clearVal" placeholder="驗證碼" maxlength="4">
                         
                      </div>
                      
                      
                         <div class="sign_btn">
                          <a href="#" onclick="$('#LoginForm').submit();" class="btn btn-warning">　登入　</a>						
                          <a class="btn btn-danger" value="會員註冊" onclick="location.href='<?php echo site_url("Manger/register")?>'">免費註冊</a>                                   
                        </div>
					</form>

            </div>          
        </div>        
        
      </div>
    </div>
</div>
<?php endif;?>
</header>