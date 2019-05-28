<div class="breadcrumbs ace-save-state breadcrumbs-fixed" id="breadcrumbs">
						<ul class="breadcrumb">
							<li>
								<i class="ace-icon fa fa-home home-icon"></i>
								<a href="<?php echo site_url(SYSTEM_URL."/Char/index")?>">首頁</a>
							</li>
							<?php
                            if(isset($breadcrumb)):
                                foreach($breadcrumb as $row):
                            ?>
							<li>
								<?php if($row["url"]!=NULL):?>
                                    <a href="<?php echo site_url(SYSTEM_URL."/".$row["url"])?>"><?php echo $row["title"]?></a>
                                <?php else:?>
                                    <a href="javascript:void(0)"><?php echo $row["title"]?></a>
                                <?php endif;?>
							</li>
							<?php
                                endforeach;
                            endif;
                            ?>
						</ul><!-- /.breadcrumb -->

					</div>