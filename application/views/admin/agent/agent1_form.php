<script type="text/javascript">

	$(function(){
	   jQuery.validator.addMethod("checkMyuID", function( value, element ) {
		  var str = value;
		  var result = false;
		  if(value.toLowerCase()!='www'){
			  $.ajax({
				  type: "POST",
				  url: CI_URL + "<?php echo SYSTEM_URL?>/Agents/ajax_chk_id",
				  cache: false,
				  async:false,
				  data: { u_id: str}
			  }).done(function( msg ) { 	
				 if(msg.trim()=='Y'){
					 result=true;
				 }else{
					result = false; 
				 }
			  });
		  }
		 	  
		  return result;
	   }, "此帳號已存在");	
	   
	   $(".down-alert").hide();
	   $( "#percent" ).on('input',function(e){
		   $(".down-alert").hide();				   
		   if( $(this).val() < <?php echo $min_percent ?>){
				$(".down-alert").html("<b>有總代或代理的分潤高於您的設定值，儲存後將會下壓。<br />請於設定完成後，手動「補帳」各遊戲報表。</b>").fadeToggle();
		   }
	   });		   
	   	
	});

</script>

<form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction ?>">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="widget-box widget-color-blue2">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller"><?php echo $subtitle?></h4>
                    
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-8">
                        
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">帳號</label>
                            <div class="col-xs-12 col-sm-5">
                            	<?php if($todo=='add'):?>
                            	<input type="text" class="form-control checkMyuID" id="u_id" name="u_id"  placeholder="帳號" required />
                             	<?php else:?>
                                <p class="form-control-static"><?php echo $row["u_id"];?></p>
                                <?php endif;?>
                            </div>
                        </div>
                        <?php if($todo=='add'):?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">密碼</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="password" class="form-control" id="u_password" name="u_password"  placeholder="密碼" required="required" />
                              
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">確認密碼</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="password" class="form-control" equalTo="#u_password" id="u_password2" name="u_password2"  placeholder="確認密碼" required="required" />
                             
                            </div>
                        </div> 
                        <?php endif;?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">姓名</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="u_name" name="u_name" value="<?php if(isset($row)) echo $row["u_name"]?>" placeholder="姓名" required />
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">分潤設定</label>
                            <div class="col-xs-12 col-sm-5">
                            	<!--
                            	<input type="text" class="form-control digits" id="percent" max="100" name="percent" value="<?php if(isset($row)) echo $row["percent"]?>" placeholder="分潤設定" required />
                                <p class="form-control-static text-danger">每月只能修改一次</p>
                                <div class="down-alert text-danger"></div>                                
                                -->
                            	<?php if($todo!='add'):?>
                                	<?php 
										//可修改起始時間
										date_default_timezone_set("Asia/Taipei");
										$sTime=strtotime(date('Y-m-01 00:00:00'));
										$eTime=strtotime(date('Y-m-01 12:00:00'));
										$now=time();	//現在時間
										if($sTime <=$now && $now < $eTime){	//可修改日期
									?>
                                    <input type="text" class="form-control digits" id="percent" max="100" name="percent" value="<?php if(isset($row)) echo $row["percent"]?>" placeholder="分潤設定" required />
                                    <p class="form-control-static text-danger">可修改日期：每個月1號 00:00:00 ~ 12:00:00</p>
                                    <div class="down-alert text-danger"></div>                                    
                                    <?php 
										}else{
									?>
                                	<input type="hidden" id="percent" name="percent" value="<?php if(isset($row)) echo $row["percent"]?>"  />
                                	<p class="form-control-static"><?php echo $row["percent"];?>%</p>
                                    <p class="form-control-static text-danger">可修改日期：每個月1號 00:00:00 ~ 12:00:00</p>
                                    <?php 
										}
									?>
                                <?php else:?>
                            	<input type="text" class="form-control digits" id="percent" max="100" name="percent" value="<?php if(isset($row)) echo $row["percent"]?>" placeholder="分潤設定" required />
                                <p class="form-control-static text-danger">每月只能修改一次</p>
                                <?php endif;?> 
                                                                
                            </div>
                        </div> 
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">狀態</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="active" id="active" data-placeholder="請選擇" required>
                                <option value="">請選擇</option>
                                <option value="Y" <?php if(@$row["active"]=='Y') echo ' selected'?>>啟用</option>
                                <option value="N" <?php if(@$row["active"]=='N') echo ' selected'?>>停權</option>
                              </select>
                            
                            </div>
                         </div>     
                        <div class="form-group">
                            <label for="inputWarning" class="col-sm-3 control-label no-padding-right"></label>
                            <div class="col-xs-12 col-sm-5">
                                <button type="submit" class="btn btn-primary">送出</button>
                                <a class='btn btn-danger' href='<?php echo $cancelBtn;?>' >取消</a>
                            </div>    
                        </div> 
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="page-header"></div>

<?php if($todo=='edit'):?>
<form class="form-horizontal newoil-form" method="post" action="<?php echo $pwdAction?>">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="widget-box widget-color-blue2">
                    <div class="widget-header">
                        <h4 class="widget-title lighter smaller">修改密碼</h4>
                       
                    </div>
                    <div class="widget-body">
                        <div class="widget-main padding-8">
                        	
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">新密碼</label>
                                <div class="col-xs-12 col-sm-5">
                                	<input type="password" class="form-control" id="u_password" name="u_password"  placeholder="新密碼" required="required" />
                                  
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">確認密碼</label>
                                <div class="col-xs-12 col-sm-5">
                                	<input type="password" class="form-control" equalTo="#u_password" id="u_password2" name="u_password2"  placeholder="確認密碼" required="required" />
                                 
                                </div>
                            </div> 
                            
                            <div class="form-group">
                            <label for="inputWarning" class="col-sm-3 control-label no-padding-right"></label>
                            <div class="col-xs-12 col-sm-5">
                                <button type="submit" class="btn btn-primary">修改密碼</button>
                               
                            </div>    
                        </div>
                            
                            
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php endif;?>