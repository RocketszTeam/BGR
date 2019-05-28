			<div id="sidebar" class="sidebar responsive ace-save-state sidebar-fixed">
				<script type="text/javascript">
					try{ace.settings.loadState('sidebar')}catch(e){}
					$(function(){
						$('.submenu .active').parents('li').addClass('active').addClass('open');
						
						//解析度小於768 改變選單樣式					
						if(window.screen.width <=768){
							$('#sidebar-collapse').trigger('click');	//觸發JS改變選單樣式
						}
					});
				</script>
                
				<div class="sidebar-shortcuts" id="sidebar-shortcuts" style="display:none;">
					<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
						<button class="btn btn-success">
							<i class="ace-icon fa fa-signal"></i>
						</button>

						<button class="btn btn-info">
							<i class="ace-icon fa fa-pencil"></i>
						</button>

						<button class="btn btn-warning">
							<i class="ace-icon fa fa-users"></i>
						</button>

						<button class="btn btn-danger">
							<i class="ace-icon fa fa-cogs"></i>
						</button>
					</div>

					<div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
						<span class="btn btn-success"></span>

						<span class="btn btn-info"></span>

						<span class="btn btn-warning"></span>

						<span class="btn btn-danger"></span>
					</div>
				</div>
                <!-- /.sidebar-shortcuts -->
				
				<ul class="nav nav-list">                    
                <?php
                if(isset($menu)):
					if(count($menu) > 0):
						foreach($menu as $row):					
				?>
                <li class="">
                  <a href="javascript:vold(0)" class="dropdown-toggle">
                      <i class="<?php echo $row["icon"]?>"></i>
                      <span class="menu-text"><?php echo $row["title"]?></span>

                      <b class="arrow fa fa-angle-down"></b>
                  </a>

                  <b class="arrow"></b>
                <?php
                		if(count($row["Child"]) > 0):
				?>    
                  <ul class="submenu">
                <?php
                			foreach($row["Child"] as $row2):
				?>    
                      <li class="<?php if(false !== ($rst = strpos($this -> uri -> uri_string(), $row2["url"]))) echo ' active'?>" style="display:<?php if($row2["isShow"]!='Y') echo 'none'?>">
                          <a href="<?php echo site_url(SYSTEM_URL."/".$row2["url"])?>">
                              <i class="menu-icon fa fa-caret-right"></i>
                              <?php echo $row2["title"]?>
                          </a>

                          <b class="arrow"></b>
                      </li>
                      
                <?php
							endforeach;
				?>        
                  </ul>
                <?php
				endif;
				?>
              </li>         
				<?php
						endforeach;
					endif;
				endif;
				?>
            </ul>
                <!-- /.nav-list -->

				<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
					<i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
				</div>
			</div>