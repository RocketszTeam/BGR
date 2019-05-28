<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
   </head>
  <body>
  <div class="site-wrap">
  
  <?php if (isset( $isLogin ) && !$isLogin):  //登入前?>
	 <link rel="stylesheet" href="<?php echo ASSETS_URL ?>/www/css/login.css">
	 
	 
	 <div class="row pt-5 mt-0 align-items-center">
          <div class="col-12 m-auto text-center">
            <a href="/"><h2 class="mb-3 site-logo font-weight-bold">B.G.R</h2></a>
          </div>

          <div class="col-md-4 col-sm-8 col-12 m-auto login-body">
            <div class="mb-5 m-5">
                <span class="caption d-block mb-2 font-secondary font-weight-bold text-center">Login</span>
                <h2 class="site-section-heading text-uppercase text-center font-secondary">會員登入</h2>

                <form id="LoginForm" method="post" class="needs-validation" novalidate>
                  <div class="form-group">
                    <label for="uname">帳號 / Username:</label>
                    <input type="text" class="form-control" id="login_u_id" name="login_u_id" placeholder="輸入帳號/Enter username" required>
                    <div class="valid-feedback">Valid.</div>
                    <div class="invalid-feedback">Please fill out this field.</div>
                  </div>
                  <div class="form-group">
                    <label for="pwd">密碼 / Password:</label>
                    <input type="password" class="form-control" id="login_u_password" placeholder="輸入密碼/Enter password" required>
                    <div class="valid-feedback">Valid.</div>
                    <div class="invalid-feedback">Please fill out this field.</div>
                  </div>
                  <!-- <div class="form-group form-check">
                    <label class="form-check-label">
                      <input class="form-check-input" type="checkbox" name="remember" required> I agree on blabla.
                      <div class="valid-feedback">Valid.</div>
                      <div class="invalid-feedback">Check this checkbox to continue.</div>
                    </label>
                  </div> -->
				  
                  <div class="form-group">
                  <label for="pwd">驗證碼 / Code:</label>
                  <input type="number" class="form-control" id="chknum" placeholder="驗證碼/Enter the image-code">
                  <img id="chkImg" style="cursor: pointer;" src="<?php echo site_url("Vcode2") ?>?token=<?php echo $token ?>" class="img-fluid" alt="刷新驗證碼" onclick="changeChkImg()">
                  </div>
                 
				  
                  <button type="submit" class="btn btn-danger btn-block" onclick="doLogin('LoginForm')">登入</button>
                </form>

                <div class="mb-5 m-5 text-center">
                  <a href="<?php echo site_url("Forget") ?>">忘記密碼</a>|<a href="<?php echo site_url("Manger/register") ?>">加入會員</a>
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
	
	<?php else: ?>
	
      <?php $this -> load -> view("www/includes/header.php")?>
    
	 

  <div class="site-half">
    <div class="img-bg-1" style="background-image: url('<?php echo ASSETS_URL ?>/www/images/hero_bg_2.jpg');"></div>
    <div class="container">
      <div class="row no-gutters align-items-stretch">
        <div class="col-md-5 ml-md-auto py-5">
          <span class="caption d-block mb-2 font-secondary font-weight-bold" id="index0">Outstanding Services</span>
          <h2 class="site-section-heading text-uppercase font-secondary mb-5">卓越的服務</h2>
          <p>B.G.R &mdash; Buffett Ground Rules公司概念展現巴菲特一以貫之的投資理念，包括反向分散投資策略、追求複利，以及打敗大盤的要求。這些信記錄巴菲特年輕時期利用少量資金、透過高複利累積財富的過程，以及日漸成熟的長期價值投資策略。 </p>
        </div>
      </div>
    </div>
  </div>

  <div class="site-half">
    <div class="img-bg-1 right" style="background-image: url('<?php echo ASSETS_URL ?>/www/images/hero_bg_1.jpg');"></div>
    <div class="container">
      <div class="row no-gutters align-items-stretch">
        <div class="col-md-5 mr-md-auto py-5">
          <span class="caption d-block mb-2 font-secondary font-weight-bold">Outstanding Services</span>
          <h2 class="site-section-heading text-uppercase font-secondary mb-5">選擇我們的理由</h2>
          <p>我們致力為客戶保護隱私並提供一個最安全的平台，我們在此站蒐集的資料將會為您提供最卓越的服務，我們絕對不會出賣或租賃您的個人資料給第三方，客戶所提供的個人資料均經過加密技術處理，
            並儲存在安全、非公開的作業系統，對於有機會接觸客戶的個人資料的協助夥伴也必需遵守我們訂立的隱私保密規則。 </p>  
        </div>
      </div>
    </div>
  </div>
    
  
  <div class="site-section">
    <div class="container">
      <div class="row">
        <div class="col-md-12 text-center">
          <span class="caption d-block mb-2 font-secondary font-weight-bold" id="index1">Products and Services</span>
          <h2 class="site-section-heading text-uppercase text-center font-secondary">產品與服務</h2>
        </div>
      </div>						
      <div class="row">
        <div class="col-md-12 block-13 nav-direction-white">
          <div class="nonloop-block-13 owl-carousel">
            <div class="media-image">
              <img src="<?php echo ASSETS_URL ?>/www/images/g_live.jpg" alt="Image" class="img-fluid">
              <div class="media-image-body">
                <h2 class="font-secondary text-uppercase">真人視訊</h2>
                <p>LIVE CASINO</p>
                <p><a href="<?php echo site_url("Games")?>#game_live" class="btn btn-primary text-white px-4"><span class="caption">Learn More</span></a></p>
              </div>
            </div>
            <div class="media-image">
              <img src="<?php echo ASSETS_URL ?>/www/images/g_esp.jpg" alt="Image" class="img-fluid">
              <div class="media-image-body">
                <h2 class="font-secondary text-uppercase">體育&電競</h2>
                <p>SPORT & E-SPORT</p>
                <p><a href="<?php echo site_url("Games")?>#game_sport" class="btn btn-primary text-white px-4"><span class="caption">Learn More</span></a></p>
              </div>
            </div>
            <div class="media-image">
              <img src="<?php echo ASSETS_URL ?>/www/images/g_fish.jpg" alt="Image" class="img-fluid">
              <div class="media-image-body">
                <h2 class="font-secondary text-uppercase">捕魚達人</h2>
                <p>FISHING</p>
                <p><a href="<?php echo site_url("Games")?>#game_fish" class="btn btn-primary text-white px-4"><span class="caption">Learn More</span></a></p>
              </div>
            </div>
            <div class="media-image">
              <img src="<?php echo ASSETS_URL ?>/www/images/g_slot.jpg" alt="Image" class="img-fluid">
              <div class="media-image-body">
                <h2 class="font-secondary text-uppercase">電子娛樂</h2>
                <p>SLOT GAMES</p>
                <p><a href="<?php echo site_url("Games")?>#game_slot" class="btn btn-primary text-white px-4"><span class="caption">Learn More</span></a></p>
              </div>
            </div>
            <div class="media-image">
              <img src="<?php echo ASSETS_URL ?>/www/images/g_lottery.jpg" alt="Image" class="img-fluid">
              <div class="media-image-body">
                <h2 class="font-secondary text-uppercase">彩票彩球</h2>
                <p>KENO</p>
                <p><a href="<?php echo site_url("Games")?>#game_keno" class="btn btn-primary text-white px-4"><span class="caption">Learn More</span></a></p>
              </div>
            </div>
            <div class="media-image">
              <img src="<?php echo ASSETS_URL ?>/www/images/g_slot2.jpg" alt="Image" class="img-fluid">
              <div class="media-image-body">
                <h2 class="font-secondary text-uppercase">獨家遊戲</h2>
                <p>EXCLUSIVE GAMES</p>
                <p><a href="<?php echo site_url("Games")?>" class="btn btn-primary text-white px-4"><span class="caption">Learn More</span></a></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
    <div class="site-section p-3">
      <div class="container">
        <div class="row mb-5">
          <div class="col-md-12 text-center">
            <span class="caption d-block mb-2 font-secondary font-weight-bold" id="index2">Product advantages</span>
            <h2 class="site-section-heading text-uppercase text-center font-secondary">產品優勢</h2>
          </div>
        </div>
        <div class="row border-responsive">
          <div class="col-md-6 col-lg-3 col-6 mb-4 mb-lg-0 border-right">
            <div class="text-center">
              <span class="flaticon-customer-service display-4 d-block mb-3 text-primary"></span>
              <h3 class="text-uppercase h4 mb-3">24小時貼心服務</h3>
              <p>我們的專業知識來源於廣泛的經驗，對於會員的問題從不待慢。</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3 col-6 mb-4 mb-lg-0 border-right">
            <div class="text-center">
              <span class="flaticon-group display-4 d-block mb-3 text-primary"></span>
              <h3 class="text-uppercase h4 mb-3">風控專家</h3>
              <p>專員風險評估，幫助您找到最適合自己的投資商品。</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3 col-6 mb-4 mb-lg-0 border-right">
            <div class="text-center">
              <span class="flaticon-medal display-4 d-block mb-3 text-primary"></span>
              <h3 class="text-uppercase h4 mb-3">信譽保證</h3>
              <p>我們遵循的經營理念很簡單：保證客戶滿意，我們就能贏得客戶的信任和忠誠。 </p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3 col-6 mb-4 mb-lg-0">
            <div class="text-center">
              <span class="flaticon-agreement display-4 d-block mb-3 text-primary"></span>
              <h3 class="text-uppercase h4 mb-3">合營代理</h3>
              <p>成為我們的伙伴，帶領更多會員加入B.G.R，提升您會員的等級。</p>
            </div>
          </div>
        </div>
      </div>
    </div>

      
      <!-- Footer -->
      <?php $this -> load -> view("www/includes/footer.php")?>
	  <?php endif; ?>
	  </div>
   </body>
</html>