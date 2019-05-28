
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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">遊戲廠商</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="gamemaker_num" id="gamemaker_num" data-placeholder="請選擇" required>
                              	<option value="">請選擇</option>
                                <?php if(isset($makers_data)):?>
                                <?php foreach($makers_data as $rowMenu):?>
                                <option value="<?php echo $rowMenu["num"]?>"><?php echo $rowMenu["makers_name"]?></option>
                                <?php endforeach;?>
                                <?php endif;?>		
                              </select>
                              
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">會員帳號</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="mem_num" id="mem_num" data-placeholder="請選擇" required>
                                <option value="">請選擇</option>
                                <?php if(isset($memberList)):?>
                                    <?php foreach($memberList as $row):?>
                                    <optgroup label="<?php echo $row["u_id"]?>(<?php echo $row["u_name"]?>)">
                                        <?php if(count($row["member_data"]) > 0):?>
                                            <?php foreach($row["member_data"] as $row2):?>
                                            <option value="<?php echo $row2["num"]?>" data-subtext="<?php echo $row2["u_name"]?>"><?php echo $row2["u_id"]?>(<?php echo $row2["u_name"]?>)</option>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                    </optgroup> 
                                    <?php endforeach;?>   
                                <?php endif;?>
                              </select>
                              
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">遊戲密碼</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="u_password" name="u_password"  placeholder="遊戲密碼" required="required" />
                            	
                             	
                            </div>
                        </div>
                    
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">強制建立遊戲帳號</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="checkbox" class="pull-left" style="float: left;margin-top:13px;" id="forceadd" name="forceadd" value="1" />
                            </div>
                        </div>
                        

                        
                      
                        <div class="form-group">
                            <label for="inputWarning" class="col-sm-3 control-label no-padding-right"></label>
                            <div class="col-xs-12 col-sm-5">
                                <button type="submit" class="btn btn-primary">新增</button>
                                
                            </div>    
                        </div> 
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

