<div class="site-mobile-menu">
      <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close mt-3">
          <span class="icon-close2 js-menu-toggle"></span>
        </div>
      </div>
      <div class="site-mobile-menu-body"></div>
    </div> <!-- .site-mobile-menu -->
    
    
    <div class="site-navbar-wrap js-site-navbar bg-white">
      
      <div class="container">
        <div class="site-navbar bg-light">
          <div class="row align-items-center">
            <div class="col-2">
              <h2 class="mb-0 site-logo"><a href="index.html" class="font-weight-bold">B.G.R</a></h2>
            </div>
            <div class="col-10">
              <nav class="site-navigation text-right" role="navigation">
                <div class="container">
                  <div class="d-inline-block d-lg-none ml-md-0 mr-auto py-3"><a href="#" class="site-menu-toggle js-menu-toggle text-black"><span class="icon-menu h3"></span></a></div>

                  <ul class="site-menu js-clone-nav d-none d-lg-block">
                    <li class="active"><a href="index.html">首頁</a></li>
                    <li class="has-children">
                      <a href="<?php echo site_url("Games")?>">服務項目</a>
                      <ul class="dropdown arrow-top">
                        <li><a href="<?php echo site_url("Games")?>#game_live">真人視訊</a></li>
                        <li><a href="<?php echo site_url("Games")?>#game_fish">捕魚達人</a></li>
                        <li><a href="<?php echo site_url("Games")?>#game_slot">電子娛樂</a></li>
                        <li><a href="<?php echo site_url("Games")?>#game_sport">體育&電競</a></li>
                        <li><a href="<?php echo site_url("Games")?>#game_keno">彩票&競速</a></li>
                      </ul>
                    </li>
                    <li><a href="<?php echo site_url("News")?>">最新消息</a></li>
                    <li><a href="<?php echo site_url("active")?>">優惠情報</a></li>
                    <li><a href="<?php echo site_url("about")?>">關於我們</a></li>
                    <li><a href="<?php echo site_url("reseller")?>">合營代理</a></li>
                    <li><a href="http://line.naver.jp/ti/p/UHxpyJFKMj">聯絡我們</a></li>
                    <li><a href="<?php echo site_url("Manger/account") ?>"><span class="d-inline-block p-3 bg-primary text-white btn btn-primary">會員中心</span></a></li>
					<li><a href="<?php echo site_url("Index/logout") ?>"><span class="d-inline-block p-3 bg-primary text-white btn btn-primary">登出</span></a></li>
                  </ul>
                </div>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php $this -> load -> view("www/includes/banner.php")?>



