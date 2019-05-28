
<form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction ?>">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="widget-box widget-color-blue2">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller">新增放點扣點名稱</h4>
                    
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-8">
                    	
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">名稱</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="kind" name="kind" value="" placeholder="放點扣點名稱" required="required" />
                            	
                             	
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">放點或扣點</label>
                            <div class="col-xs-12 col-sm-5">
                               <select class="form-control select2" name="type" id="type"  required>
									<option value="">請選擇</option>
									<option value=1 >放點</option>
									<option value=2 >扣點</option>
                              </select>
                              
                            </div>
                        </div>
                       
						<div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">是否限制點數</label>
                            <div class="col-xs-12 col-sm-5 radio" >
                            	
								<label>
                                	<input type="radio" class="ace" name="active" value="1"  /><span class='lbl'>啟動</span>
                                </label>
                            	<label>
                                	<input type="radio" class="ace" name="active" value="2"  /><span class='lbl'>關閉</span>
                                </label>
							
                            </div>
                        </div> 
						
						<div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">點數設定</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="point" name="point" value="" placeholder="點數" required="required" />
                            	
                             	
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

