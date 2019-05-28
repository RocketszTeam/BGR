

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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">群組</label>
                            <div class="col-xs-12 col-sm-5">
                            	<p class="form-control-static"><?php echo $power_name;?></p>
                              
                            </div>
                         </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">帳號</label>
                            <div class="col-xs-12 col-sm-5">
                            	
                                <p class="form-control-static"><?php echo $row["u_id"];?></p> 
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">姓名</label>
                            <div class="col-xs-12 col-sm-5">
                            	 <p class="form-control-static"><?php echo $row["u_name"];?></p> 
                              
                            </div>
                        </div> 
                       
                       
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
                                <button type="submit" class="btn btn-primary">變更密碼</button>
                                <input name="todo" type="hidden" value="pws" />
                            </div>    
                        </div> 
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

