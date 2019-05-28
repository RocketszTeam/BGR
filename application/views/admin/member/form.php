<script type="text/javascript">
	var pass_reg=/^(?!([^a-zA-Z]+|\D+)$)[a-zA-Z0-9]{6,13}$/;
	$(function(){
	   jQuery.validator.addMethod("checkMyuID", function( value, element ) {
		  var str = value;
		  var result = false;
		  $.ajax({
			  type: "POST",
			  url: CI_URL + "<?php echo SYSTEM_URL?>/Member/ajax_chk_id",
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
		 	  
		  return result;
	   }, "此帳號已存在");	
	   
	   jQuery.validator.addMethod("checkFormat", function( value, element ) {
		  var reg=/^[a-zA-Z0-9]{4,12}$/;
		  return reg.test(value);	  
	   }, "帳號格式為4~12碼英文或數字混合");	

		jQuery.validator.addMethod("PasswordFormat", function( value, element ) {
		  var str = value;
		  var result = false;
		  return (pass_reg.test(value));
		}, '密碼請輸入6~13碼英文數字混合');	
	   $('#u_password').addClass('PasswordFormat');
	   	
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
                            	<input type="text" class="form-control checkMyuID checkFormat" id="u_id" name="u_id"  placeholder="帳號" required />
                             	<?php else:?>
                                <p class="form-control-static"><?php echo $row["u_id"];?></p>
                                <?php endif;?>
                                <input name="nation" id="nation" type="hidden" value="<?php echo $nation;?>" />
                            </div>
                        </div>
                        <?php if($todo=='add'):?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">密碼</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="password" class="form-control PasswordFormat" id="u_password" name="u_password"  placeholder="密碼" required="required" />
                              
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">確認密碼</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="password" class="form-control" equalTo="#u_password" id="u_password2" name="u_password2"  placeholder="確認密碼" required="required" />
                             
                            </div>
                        </div> 														
                        <?php endif;?>	
					
						<?php if($todo=="edit"){?>	<!--"創建會員"不可以修改會員等級，"修改會員"可以修改會員等級-->
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">會員等級</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="m_group" id="m_group" data-placeholder="請選擇" required>
                              	<?php echo inSelectOption($member_group,@$row["m_group"])?>
                              </select>
                              
                            </div>
                        </div>
						<?php } ?>
					
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">姓名</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="u_name" name="u_name" value="<?php if(isset($row)) echo $row["u_name"]?>" placeholder="姓名" required />
                              
                            </div>
                        </div> 
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">推廣員</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="promoter" name="promoter" value="<?php if(isset($row)) echo $row["promoter"]?>" placeholder="推廣員" />
                              
                            </div>
                        </div> 
                        
                        
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">手機</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="phone" name="phone" value="<?php if(isset($row)) echo $row["phone"]?>" placeholder="手機" required />
                              
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">LINE ID</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="line" name="line" value="<?php if(isset($row)) echo $row["line"]?>" placeholder="LINE ID" required />
                              
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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">驗證</label>
                            <div class="col-xs-12 col-sm-5 radio">
                            	<label>
                                	<input type="radio" class="ace" name="is_vaid" value="0" <?php if(@$row["is_vaid"]=='0' || !isset($row)) echo ' checked'?> /><span class='lbl'>未驗證</span>
                                </label>
                            	<label>
                                	<input type="radio" class="ace" name="is_vaid" value="1" <?php if(@$row["is_vaid"]=='1')  echo ' checked'?> /><span class='lbl'>已驗證</span>
                                </label>
                            </div>
                        </div> 
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">備註</label>
                            <div class="col-xs-12 col-sm-5">
                            	<textarea id="demo" name="demo" class="form-control  autosize-transition" placeholder="備註"><?php echo (isset($row) ? $row['demo'] : '')?></textarea>
                              
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
                                	<input type="password" class="form-control PasswordFormat" id="u_password" name="u_password"  placeholder="新密碼" required="required" />
                                  
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