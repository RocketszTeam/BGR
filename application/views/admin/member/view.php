

<form class="form-horizontal newoil-form" method="post" action="">
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
                                <p class="form-control-static"><?php echo $row["u_id"];?></p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">會員等級</label>
                            <div class="col-xs-12 col-sm-5">
                            	<p class="form-control-static"><?php echo inNumberString($member_group,@$row["m_group"])?></p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">姓名</label>
                            <div class="col-xs-12 col-sm-5">
                              	<p class="form-control-static"><?php echo $row["u_name"];?></p>
                            </div>
                        </div> 
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">推廣員</label>
                            <div class="col-xs-12 col-sm-5">
                            	<p class="form-control-static"><?php echo $row["promoter"];?></p>
                            </div>
                        </div> 
                        
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">手機</label>
                            <div class="col-xs-12 col-sm-5">
                              	<p class="form-control-static"><?php echo $row["phone"];?></p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">LINE ID</label>
                            <div class="col-xs-12 col-sm-5">
                              	<p class="form-control-static"><?php echo $row["line"];?></p>
                            </div>
                        </div>                        
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">狀態</label>
                            <div class="col-xs-12 col-sm-5">
                              <p class="form-control-static"><?php echo $row["active"]=='Y' ? '啟用' : '停權';?></p>
                            
                            </div>
                        </div>  
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">備註</label>
                            <div class="col-xs-12 col-sm-5">
                              	<p class="form-control-static"><?php echo br($row["demo"]);?></p>
                            </div>
                        </div>
                           
                        <div class="form-group">
                            <label for="inputWarning" class="col-sm-3 control-label no-padding-right"></label>
                            <div class="col-xs-12 col-sm-5">
                                <!--<button type="submit" class="btn btn-primary">送出</button>-->
                                <a class='btn btn-danger' href='<?php echo $cancelBtn;?>' >返回會員</a>
                            </div>    
                        </div> 
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>



