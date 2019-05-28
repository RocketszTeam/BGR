<div id="navbar" class="navbar navbar-default ace-save-state navbar-fixed-top">
			<div class="navbar-container ace-save-state" id="navbar-container">
				<button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
					<span class="sr-only">Toggle sidebar</span>

					<span class="icon-bar"></span>

					<span class="icon-bar"></span>

					<span class="icon-bar"></span>
				</button>

				<div class="navbar-header pull-left">
					<a href="<?php echo site_url(SYSTEM_URL."/Char/index")?>" class="navbar-brand">
						<small>
							<i class="fa fa-leaf"></i>
							管理平台
						</small>
					</a>
				</div>

				<div class="navbar-buttons navbar-header pull-right" role="navigation">
					<ul class="nav ace-nav">
						<li class="grey dropdown-modal">
							<a  href="<?php echo site_url(SYSTEM_URL."/Order/bank")?>" title="匯款" data-toggle="tooltip" >
                                <i class="ace-icon fa fa-university icon-animated-vertical"></i>
								<span class="badge badge-important" id="bank_transfer">0</span>
							</a>
						</li>

						<li class="purple dropdown-modal">
							<a  href="<?php echo site_url(SYSTEM_URL."/Order/sell")?>" data-toggle="tooltip" title="拋售">
                                <i class="ace-icon fa fa-usd icon-animated-vertical"></i>
								<span class="badge badge-important" id="order_sell">0</span>
							</a>

							
						</li>

						<li class="green dropdown-modal">
							<a  href="<?php echo site_url(SYSTEM_URL."/Message/index")?>" data-toggle="tooltip" title="訊息">
								<i class="ace-icon fa fa-envelope icon-animated-vertical"></i>
								<span class="badge badge-important" id="member_talk">0</span>
							</a>

							
						</li>
						<li class="light-blue dropdown-modal">
                            <a href="<?php echo site_url()?>" target="_blank" data-toggle="tooltip" title="瀏覽網站">
                                <i class="ace-icon fa fa-globe icon-animated-vertical"></i>
                            </a>
						</li>
						<li class="red dropdown-modal">
                            <a href="<?php echo site_url(SYSTEM_URL."/Login/logout")?>" data-toggle="tooltip" title="登出">
                                <i class="ace-icon fa fa-power-off icon-animated-vertical"></i>
                                <!--登出-->
                            </a>
						</li>
					</ul>
				</div>
			</div><!-- /.navbar-container -->
		</div>