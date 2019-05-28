<script type="text/javascript">
	$(function(){
		$('.sTime').each(function(){
			laydate.render({
			  elem: this,
			  type: 'time',
			  istime: true	
			});	
		});
	});
</script>
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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">廠商名稱</label>
                            <div class="col-xs-12 col-sm-5">
                            	<p class="form-control-static"><?php echo $row["makers_name"];?></p> 
                            	
                            </div>
                        </div> 
                        
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">維修時間</label>
                            <div class="col-xs-12 col-sm-5">
                             	<div class="input-daterange  input-group">
                                    <select class="form-control " name="sDate1" id="sDate1" data-placeholder="請選擇">
                                    <option value="">請選擇</option>
                                    <?php echo inSelectOption($weekList,substr($row["selltime1"],0,1))?>
                                    </select>
                                    <span class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </span>
                                    <input type="text" class="form-control sTime" name="sTime1" id="sTime1" value="<?php if(isset($row)) echo date('H:i:s',strtotime(substr($row["selltime1"],1,6)))?>" placeholder="結束時間"   />
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <select class="form-control " name="sDate2" id="sDate2" data-placeholder="請選擇">
                                    <option value="">請選擇</option>
                                    <?php echo inSelectOption($weekList,substr($row["selltime2"],0,1))?>
                                    </select>
                                    <span class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </span>
                                    <input type="text" class="form-control sTime" name="sTime2" id="sTime2" value="<?php if(isset($row)) echo date('H:i:s',strtotime(substr($row["selltime2"],1,6)))?>" placeholder="結束時間"   />
                                </div>
                            
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

