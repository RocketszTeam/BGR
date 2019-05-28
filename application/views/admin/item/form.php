
<form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction ?>">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="widget-box widget-color-blue2">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller"><?php echo $subtitle?></h4>
                    
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-8">
                    	<?php if(@$root > 0):?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">上層選單</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="root" id="root" data-placeholder="請選擇" required>
                                <?php if(isset($rootMenu)):?>
                                <?php foreach($rootMenu as $rowMenu):?>
                                <option value="<?php echo $rowMenu["num"]?>" <?php if($rowMenu["num"]==@$root) echo ' selected'?>><?php echo $rowMenu["title"]?></option>
                                <?php endforeach;?>
                                <?php endif;?>		
                              </select>
                              
                            </div>
                        </div>
                        <?php endif;?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">選項名稱</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="title" name="title" value="<?php if(isset($row)) echo $row["title"]?>" placeholder="選項名稱" required="required" />
                            	
                             	
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">選項連結</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="url" name="url" value="<?php if(isset($row)) echo $row["url"]?>" placeholder="選項連結" <?php if(@$root > 0) echo 'required'?> />
                              
                            </div>
                        </div>
                        <?php if(@$root==0):?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">選項圖示</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="icon" name="icon" value="<?php if(isset($row)) echo $row["icon"]?>" placeholder="選項圖示" />
                             
                            </div>
                        </div> 
                        <?php endif;?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">相關連結</label>
                            <div class="col-xs-12 col-sm-5">
                                <!--<input type="text" class="form-control" id="other_url" name="other_url" value="<?php if(isset($row)) echo $row["other_url"]?>" placeholder="相關連結"  />-->
                                <textarea id="other_url" name="other_url" class="form-control  autosize-transition" placeholder="相關連結"><?php if(isset($row)) echo $row["other_url"]?></textarea>
                              
                            </div>
                        </div>                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">是否顯示</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="isShow" id="isShow"  required>
                                  <option value="Y" <?php if(@$row["isShow"]=='Y') echo ' selected'?>>顯示</option>
                                  <option value="N" <?php if(@$row["isShow"]=='N') echo ' selected'?>>不顯示</option>
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

