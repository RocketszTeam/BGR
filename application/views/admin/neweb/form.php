<form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction ?>" enctype="multipart/form-data">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="widget-box widget-color-blue2">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller"><?php echo $subtitle?></h4>
                    
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-8">
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">會員群組</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="m_group" id="m_group" data-placeholder="請選擇" required>
                              	<?php echo inSelectOption($member_group,@$row["m_group"])?>	
                              </select>
                              
                            </div>
                         </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">商店編號</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="merchantnumber" name="merchantnumber" value="<?php if(isset($row)) echo $row["merchantnumber"]?>" placeholder="商店編號" required />
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">交易密碼</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="code" name="code" value="<?php if(isset($row)) echo $row["code"]?>" placeholder="交易密碼" required />
                            </div>
                        </div> 
                        <?php if($todo!='add'):?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">目前收款金額</label>
                            <div class="col-xs-12 col-sm-5">
                            	<p class="form-control-static red"><?php echo number_format($row["total"],0);?></p>
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">最後收款金額</label>
                            <div class="col-xs-12 col-sm-5">
                            	<p class="form-control-static red"><?php echo number_format($row["last_amount"],0);?></p>
                            </div>
                        </div>
                        
                        <?php endif;?>
                         
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">下次收款金額</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control digits" name="next_amount" id="next_amount" value="<?php if(isset($row)) echo $row["next_amount"]?>" placeholder="下次收款金額" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">狀態</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="active" id="active" data-placeholder="請選擇" required>
                                <option value="Y" <?php if(@$row["active"]=='Y') echo ' selected'?>>開放</option>
                                <option value="N" <?php if(@$row["active"]=='N') echo ' selected'?>>關閉</option>
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

