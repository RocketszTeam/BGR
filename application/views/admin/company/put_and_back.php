<form class="form-horizontal newoil-form" method="post" action="<?php echo $wallet_kind_point?>">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="widget-box widget-color-blue2">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller">放點扣點最大限額設定</h4>
                    
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-8">
                    	<?php foreach($point_sqlStr_row as $key=>$row){?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right"><?php echo $row['kind']?></label>
                            <div class="col-xs-12 col-sm-5 radio" >
                            	<input type="hidden" name="num<?php echo $key?>" value="<?php echo $row['num']?>">
								<label>
									<input type="text" class="form-control" id="com_name" name="wallet_kind_point<?php echo $key?>" value="<?php if(isset($row)) echo $row['point']?>" />
								</label>
								<label>
                                	<input type="radio" class="ace" name="wallet_kind<?php echo $key?>" value="1" <?php if($row["active"]=='1') echo ' checked'?> /><span class='lbl'>啟動</span>
                                </label>
                            	<label>
                                	<input type="radio" class="ace" name="wallet_kind<?php echo $key?>" value="2" <?php if($row["active"]=='2')  echo ' checked'?> /><span class='lbl'>關閉</span>
                                </label>
							
                            </div>
                        </div> 
                        <?php };?>
                       <!-- <div class="form-group">
                            <label for="inputWarning" class="col-sm-3 control-label no-padding-right"></label>
                            <div class="col-xs-12 col-sm-5">
                            	<p class="form-control-static text-danger">藍星尚未開放</p> 
                            </div>    
                        </div> -->
                        <div class="form-group">
                            <label for="inputWarning" class="col-sm-3 control-label no-padding-right"></label>
                            <div class="col-xs-12 col-sm-5">
                                <button type="submit" class="btn btn-primary">送出</button>
                            </div>    
                        </div> 
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


