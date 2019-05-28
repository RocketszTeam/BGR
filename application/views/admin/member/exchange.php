<script type="text/javascript">
	$(function(){
		
		jQuery.validator.addMethod("checkAgent", function( value, element ) {
			if($('#souce_agent').val()==value){
				return false;
			}else{
				return true;
			}
		},"來源代理與目標代理不得相同");
		
		$('#admin_num').addClass('checkAgent');
		
		
		$('#points_exchange').click(function(){
			if($(this).prop('checked')){
				$('#points_div').show();
			}else{
				$('#points_div').hide();
				$('#points_time1').val('');
				$('#points_time2').val('');
			}
		});

		$('#orders_exchange').click(function(){
			if($(this).prop('checked')){
				$('#orders_div').show();
			}else{
				$('#orders_div').hide();
				$('#orders_time1').val('');
				$('#orders_time2').val('');
			}
		});

		$('#sell_exchange').click(function(){
			if($(this).prop('checked')){
				$('#sell_div').show();
			}else{
				$('#sell_div').hide();
				$('#sell_time1').val('');
				$('#sell_time2').val('');
			}
		});
		$('#bank_exchange').click(function(){
			if($(this).prop('checked')){
				$('#bank_div').show();
			}else{
				$('#bank_div').hide();
				$('#bank_time1').val('');
				$('#bank_time2').val('');
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
                    		<label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">來源代理</label>
                             
                             <div class="col-xs-12 col-sm-5">
                             	<p class="form-control-static"><?php echo tb_sql('u_id','admin',$row["admin_num"])?>(<?php echo tb_sql('u_name','admin',$row["admin_num"])?>)</p>
                             	<input type="hidden" name="souce_agent" id="souce_agent" value="<?php echo $row["admin_num"]?>" />
                             </div>
                    	</div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">目標代理</label>
                            <div class="col-xs-12 col-sm-5">
                              <input type="hidden" name="souce_agent" id="souce_agent" value="<?php echo $row["admin_num"]?>" />
                              <select class="form-control select2" name="admin_num" id="admin_num" data-placeholder="請選擇" required>
                              	<option value="">請選擇</option>
                              <?php if(isset($upAccount)):?>
                              <?php foreach($upAccount as $agents):?>
                              	<option value="<?php echo $agents["num"]?>" <?php if($agents["num"]==$row["admin_num"]) echo ' selected'?>><?php echo $agents["u_id"]?>(<?php echo $agents["u_name"]?>)</option>
                              <?php endforeach;?>
                              <?php endif;?>
                              </select>
                              
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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">紅利</label>
                            <div class="col-xs-12 col-sm-2 checkbox">
                                <label>
                                    <input type="checkbox" class="ace" name="points_exchange" id="points_exchange" value="Y" />
                                    <span class="lbl">轉換紅利單據</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group" id="points_div" style="display:none;">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">紅利時間</label>
                            <div class="col-xs-12 col-sm-5">
                             	<div class="input-daterange  input-group">
                                    <input type="text" class="form-control jqdatetime" name="points_time1" id="points_time1"  placeholder="開始日期"   />
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <input type="text" class="form-control jqdatetime" name="points_time2" id="points_time2"  placeholder="結束日期"  />
                                </div>
                            </div>
                        </div> 
						
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">儲值</label>
                            <div class="col-xs-12 col-sm-2 checkbox">
                                <label>
                                    <input type="checkbox" class="ace" name="orders_exchange" id="orders_exchange" value="Y" />
                                    <span class="lbl">轉換儲值單據</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group" id="orders_div" style="display:none;">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">儲值時間</label>
                            <div class="col-xs-12 col-sm-5">
                             	<div class="input-daterange  input-group">
                                    <input type="text" class="form-control jqdatetime" name="orders_time1" id="orders_time1"  placeholder="開始日期"   />
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <input type="text" class="form-control jqdatetime" name="orders_time2" id="orders_time2"  placeholder="結束日期"  />
                                </div>
                            </div>
                        </div> 
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">拋售</label>
                            <div class="col-xs-12 col-sm-2 checkbox">
                                <label>
                                    <input type="checkbox" class="ace" name="sell_exchange" id="sell_exchange" value="Y" />
                                    <span class="lbl">轉換拋售單據</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group" id="sell_div" style="display:none;">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">拋售時間</label>
                            <div class="col-xs-12 col-sm-5">
                             	<div class="input-daterange  input-group">
                                    <input type="text" class="form-control jqdatetime" name="sell_time1" id="sell_time1"  placeholder="開始日期"   />
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <input type="text" class="form-control jqdatetime" name="sell_time2" id="sell_time2"  placeholder="結束日期"  />
                                </div>
                            </div>
                        </div> 
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">匯款</label>
                            <div class="col-xs-12 col-sm-2 checkbox">
                                <label>
                                    <input type="checkbox" class="ace" name="bank_exchange" id="bank_exchange" value="Y" />
                                    <span class="lbl">轉換匯款單據</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group" id="bank_div" style="display:none;">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">匯款時間</label>
                            <div class="col-xs-12 col-sm-5">
                             	<div class="input-daterange  input-group">
                                    <input type="text" class="form-control jqdatetime" name="bank_time1" id="bank_time1"  placeholder="開始日期"   />
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <input type="text" class="form-control jqdatetime" name="bank_time2" id="bank_time2"  placeholder="結束日期"  />
                                </div>
                            </div>
                        </div> 
                                                   
                        <div class="form-group">
                            <label for="inputWarning" class="col-sm-3 control-label no-padding-right"></label>
                            <div class="col-xs-12 col-sm-5">
                                <button type="submit" class="btn btn-primary">送出</button>
                                <a class='btn btn-danger' href='<?php echo $cancelBtn;?>' >返回</a>
                            </div>    
                        </div> 
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="page-header"></div>

<table id="simple-table" class="table table-bordered table-hover table-responsive">
	<thead>
    	<tr>
            <th>來源代理</th>
            <th>目的代理</th>
            <th>轉換紅利</th>
            <th>轉換儲值</th>
            <th>轉換拋售</th>
            <th>轉換匯款</th>
            <th>異動人員</th>
            <th>異動時間</th>
        </tr>
    </thead>
	<tbody>
    	<?php if(isset($result)):?>
        <?php foreach($result as $rowAgent):?>
    	<tr>
            <td>
            	<?php echo tb_sql('u_id','admin',$rowAgent["souce_agent"])?>
                <div class="red">
                <?php echo tb_sql('u_name','admin',$rowAgent["souce_agent"])?>
                </div>
            </td>
            <td>
            	<?php echo tb_sql('u_id','admin',$rowAgent["target_agent"])?>
                <div class="red">
                <?php echo tb_sql('u_name','admin',$rowAgent["target_agent"])?>
                </div>
            </td>
            <td>
            	<?php echo $rowAgent["points_exchange"]?>
                <?php if($rowAgent["points_time1"]):?>
                <div class="purple"><?php echo $rowAgent["points_time1"]?></div>
                <?php endif;?>
                <?php if($rowAgent["points_time2"]):?>
                <div class="purple"><?php echo $rowAgent["points_time2"]?></div>
                <?php endif;?>
            </td>
            <td>
            	<?php echo $rowAgent["orders_exchange"]?>
                <?php if($rowAgent["orders_time1"]):?>
                <div class="purple"><?php echo $rowAgent["orders_time1"]?></div>
                <?php endif;?>
                <?php if($rowAgent["orders_time2"]):?>
                <div class="purple"><?php echo $rowAgent["orders_time2"]?></div>
                <?php endif;?>
            </td>
            <td>
            	<?php echo $rowAgent["sell_exchange"]?>
                <?php if($rowAgent["sell_time1"]):?>
                <div class="purple"><?php echo $rowAgent["sell_time1"]?></div>
                <?php endif;?>
                <?php if($rowAgent["sell_time2"]):?>
                <div class="purple"><?php echo $rowAgent["sell_time2"]?></div>
                <?php endif;?>
            </td>
            <td>
            	<?php echo $rowAgent["bank_exchange"]?>
                <?php if($rowAgent["bank_time1"]):?>
                <div class="purple"><?php echo $rowAgent["bank_time1"]?></div>
                <?php endif;?>
                <?php if($rowAgent["bank_time2"]):?>
                <div class="purple"><?php echo $rowAgent["bank_time2"]?></div>
                <?php endif;?>
            </td>
            <td>
            	<?php if($rowAgent["update_admin"]):?>
					<?php echo tb_sql('u_id','admin',$rowAgent["update_admin"])?>
                    <div class="red">
                    <?php echo tb_sql('u_name','admin',$rowAgent["update_admin"])?>
                    </div>
                <?php else:?>
                	<span class="red">系統</span>
                <?php endif;?>    
            </td>
            <td><?php echo $rowAgent["buildtime"]?></td>
    	</tr>
        <?php endforeach;?>
        <?php endif;?>    
    </tbody>
</table>
<div class="text-center"><?php echo @$pagination ?></div>