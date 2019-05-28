    
    <?php if(isset($isLogin) && !$isLogin):?>
    	<script type="text/javascript" src="<?php echo ASSETS_URL?>/www/js/login.js"></script>
    <?php endif ?>
    <!-- 刷新驗證馬用 -->
    <script type="text/javascript">
            function changeChkImg(s_name,obj){
				s_name = s_name || "";
				obj = obj || "chkImg";
                $.ajax({
                    type: "POST",
                    url:  "<?php echo site_url("Manger/refresh_token")?>",
                    cache: false,
                    async:false,
                    dataType:"json"
                }).done(function( msg ) { 
                    var now = new Date();
                    $('.' + obj).attr("src","<?php echo  site_url("Vcode2");?>?token=" + msg.token  + "&now=" + now.getTime() + "&s_name=" + s_name);							
                });
            }
    </script> 
    		 
        <!-- Header -->
			<header id="header">
      <div class="inner">         
            <div class="row">
            	<div class="col-lg-4 text-center">
				          <div class="topLogo"><a href="<?php echo site_url()?>"><img src="<?php echo ASSETS_URL?>/www/images/logo.png" alt="BOMA"></a></div>
              </div>
              <div class="col-lg-8 d-none d-md-block">                
                		<!---會員登入---->
                    <?php if(isset($isLogin) && !$isLogin):  //登入前?>
                    <div class="loginBox row">
                      <form class="col" id="LoginForm" autocomplete="off"  method="post">
                            <input type="hidden" name="rtn" id="rtn" value="<?php echo $this->input->get('rtn')?>" />                                                       
                            <input id="login_u_id" name="login_u_id" class="clearVal" type="text" placeholder="帳號" maxlength="20">
                            <div class="input-group">                             
                              <input id="login_u_password" name="login_u_password"  class="clearVal" type="password" placeholder="密碼" maxlength="20">
                              <div class="input-group-append">
                                  <a class="button btn-warning btn-forgot" href="<?php echo site_url("Forget")?>"><i class="fas fa-question"></i></a>
                              </div>
                            </div>   				
                          
                      </form>

                      <div class="col">
                          <form class="form-inline">  
                          <div class="vcode_box"><img src="<?php echo ASSETS_URL?>/www/images/027.gif"  class="img-fluid chkImg" alt="刷新驗證碼" onclick="changeChkImg()"></div>
                            <input id="chknum" name="chknum" type="tel" class="clearVal" placeholder="驗證碼" maxlength="4">
                          </form>
                      </div>
                      
                      <div class="col">
          							 <div class="sign_btn">
                          <a href="#" onclick="$('#LoginForm').submit();" class="button signin btn-warning">登入 <i class="fa fa-sign-in" aria-hidden="true"></i></a>
                          <!-- <a class="button signin btn-warning" href="member.php">登入</a> -->
                          <a class="button signup btn-danger" value="會員註冊" onclick="location.href='<?php echo site_url("Manger/register")?>'">免費註冊</a>                                   
          							</div>
                    </div>
                  </div>                                  
              </div>
                <!-- 會員登入之後 取代帳號登入 -->
                <?php else: //登入後?>
                <div class="col-lg-8 loginBox  form-inline d-none d-md-block">
                  <div class="login_in">Rocketsz，您好～</div>
                  <div class="login_dot">目前點數：1,000點</div>
                  <div class="signin"><a class="button" onclick="location.href='<?php echo site_url("Manger/account")?>'">會員中心</a></div>
                  <div class="signin"><a class="button" onclick="location.href='<?php echo site_url("Index/logout")?>'" type="button">登出</a></div>
                </div>
                <?php endif;?>
            <!-- 手機板會員登入 -->
                <div class="d-md-none m_login">
                  <button class="button" data-toggle="modal" data-target="#m_login">會員登入</button>
                </div>
                
          </div>

<?php include_once("marquee.php")?> 
             <!--Web Nav --------------------------------->
           </div>
          <section class="wrapper navBox">
           <div class="inner"> 
                <nav class="d-none d-md-block w_nav">
                <ul class="nav nav-pills nav-fill">
                  <li class="nav-item w_item">
                    <a class="nav-link" href="<?php echo site_url()?>">首頁</a>
                  </li>
				          <li class="nav-item">
                    <a class="nav-link" href="<?php echo site_url("Live")?>">真人娛樂</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="<?php echo site_url("Sport")?>">體育博彩</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="<?php echo site_url("Slot")?>">電子遊戲</a>
                  </li>
				          <li class="nav-item">
                    <a class="nav-link" href="<?php echo site_url("Keno")?>">彩票彩球</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="<?php echo site_url("Openav")?>">成人影城</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="<?php echo site_url("Active")?>">優惠活動</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="<?php echo site_url("Guide?kind=7")?>">合營代理</a>
                  </li>
                  <!--<li class="nav-item">
                    <a class="nav-link" href="mob.php">APP下載</a>
                  </li>-->
                </ul>
                </nav>
            
                <!-- Mobel Nav -->
                <div class="menu d-md-none"><a href="#menu"><i class="fas fa-bars"></i> 選單</i></a></div>
          </div>
        </section>          
			</header>

	<!-- Mob Nav -->
                <nav id="menu">
                    <ul class="links d-md-none">
                        <li><a href="<?php echo site_url()?>">首頁</a></li>
                        <li><a href="<?php echo site_url("Live")?>">真人娛樂</a></li>
                        <li><a href="<?php echo site_url("Sport")?>">體育博彩</a></li>
                        <li><a href="<?php echo site_url("Slot")?>">電子遊戲</a></li>
                        <li><a href="<?php echo site_url("Keno")?>">彩票彩球</a></li>
                        <li><a href="<?php echo site_url("Openav")?>">成人影城</a></li>                 
                        <li><a href="<?php echo site_url("Active")?>">優惠活動</a></li>
                        <li><a href="<?php echo site_url("Guide?kind=7")?>">合營代理</a></li>                        
                        <!-- <li><a href="mob.php">APP下載</a></li> -->
                    </ul>
                </nav>        
          
                
			<!-- Banner -->
      				<!--<section id="banner" data-video="assets/images/banner">-->
<div>
          <div id="banner" class="carousel slide" data-ride="carousel">

              <!-- Indicators -->
              <ul class="carousel-indicators">
              <?php if(isset($bannerList)):?>
                  <?php foreach($bannerList as $keys => $row):?>
                  <li data-target="#banner" data-slide-to="<?php echo $keys?>" class="<?php if($keys==0) echo 'active'?>"></li>
                  <?php endforeach;?>
                  <?php endif;?>
              </ul>            
              
              <!-- The slideshow -->
              <div class="carousel-inner">
                <a class="nav-link" href="<?php echo site_url("Active")?>">
              <?php if(isset($bannerList)):?>
                  <?php foreach($bannerList as $keys => $row):?>
                  <div class="carousel-item <?php if($keys==0) echo ' active'?>">
                      <img src="<?php echo UPLOADS_URL?>/banner/<?php echo $row["pic"]?>" class="img-fluid">
                  </div>
                  <?php endforeach;?>
                  <?php endif;?>
                </a>
              </div>
              
              <!-- Left and right controls -->
              <a class="carousel-control-prev" href="#banner" data-slide="prev">
                <span class="carousel-control-prev-icon"></span>
              </a>
              <a class="carousel-control-next" href="#banner" data-slide="next">
                <span class="carousel-control-next-icon"></span>
              </a>
            </div>
          </div> 
</div>
                
                
                
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
              <form id="form-inline" name="mLoginForm" autocomplete="off"  method="post">
                <input type="hidden" name="rtn" id="mrtn" value="<?php echo $this->input->get('rtn')?>" />                                            
                <input id="m_login_u_id" name="login_u_id" type="text" class="form-control login clearVal" placeholder="帳號" maxlength="20">                              
                <input id="m_login_u_password" name="login_u_password" type="password" class="form-control login clearVal" placeholder="密碼"  maxlength="20">
                <div class="vcode_box"><img src="<?php echo site_url("Vcode2")?>?token=<?php echo $token?>"  class="img-fluid chkImg" alt="刷新驗證碼" onclick="changeChkImg()"></div>
                <input id="m_chknum" name="chknum" type="tel" class="form-control login" placeholder="驗證碼">
              </form> 
              <div class="sign_btn">
                  <a class="button signin" href="#" onclick="$('#mLoginForm').submit();">登入</a>
                  <a class="button signup" value="會員註冊" href="<?php echo site_url("Manger/register")?>">免費註冊</a>                                   
              </div>
          </div>
        </div>        
        
      </div>
    </div>
  </div>