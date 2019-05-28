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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">網站名稱</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="com_name" name="com_name" value="<?php if(isset($row)) echo $row["com_name"]?>" placeholder="網站名稱" required="required" />
                            </div>
                        </div>
                        
                       <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">網站標題</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="com_title" name="com_title" value="<?php if(isset($row)) echo $row["com_title"]?>" placeholder="網站標題" required="required" />
                            </div>
                       </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">網站關鍵字</label>
                            <div class="col-xs-12 col-sm-5">
                            	<textarea id="com_keywords" name="com_keywords" class="form-control  autosize-transition" placeholder="網頁關鍵字"><?php echo (isset($row) ? $row['com_keywords'] : '')?></textarea>
                              
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">網站描述</label>
                            <div class="col-xs-12 col-sm-5">
                            	<textarea id="com_description" name="com_description" class="form-control  autosize-transition" placeholder="網頁關鍵字"><?php echo (isset($row) ? $row['com_description'] : '')?></textarea>
                              
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">紅利占成</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control digits" min="20" max="150" id="point_percent" name="point_percent" value="<?php if(isset($row)) echo $row["point_percent"]?>" placeholder="紅利占成" required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">返水設定</label>
                            <div class="col-xs-12 col-sm-5 radio">
                            	<label>
                                	<input type="radio" class="ace" name="rake_mode" value="1" <?php if(@$row["rake_mode"]=='1') echo ' checked'?> /><span class='lbl'>開啟</span>
                                </label>
                            	<label>
                                	<input type="radio" class="ace" name="rake_mode" value="0" <?php if(@$row["rake_mode"]=='0')  echo ' checked'?> /><span class='lbl'>關閉</span>
                                </label>
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">返水上限</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control digits"  id="rake_winLimit" name="rake_winLimit" value="<?php if(isset($row)) echo $row["rake_winLimit"]?>" placeholder="返水上限" required="required" />
                            </div>
                        </div>
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


<!--

<div class="page-header"></div>


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
-->

