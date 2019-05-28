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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">玩家名稱</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control limited" id="heroes_name" name="heroes_name" value="<?php if(isset($row)) echo $row["heroes_name"]?>" placeholder="玩家名稱" maxlength="10" required />
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">顯示內容</label>
                            <div class="col-xs-12 col-sm-5">
                                <textarea id="heroes_word" name="heroes_word" class="form-control  autosize-transition limited" maxlength="10" placeholder="顯示內容" required><?php echo (isset($row) ? $row['heroes_word'] : '')?></textarea>
                            </div>
                        </div> 
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">開放時間</label>
                            <div class="col-xs-12 col-sm-4">
                             	<div class="input-daterange  input-group">
                                    <input type="text" class="form-control jqdate" name="selltime1" id="selltime1" value="<?php if(isset($row)) echo $row["selltime1"]?>" placeholder="開始日期"  />
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <input type="text" class="form-control jqdate" name="selltime2" id="selltime2" value="<?php if(isset($row)) echo $row["selltime2"]?>" placeholder="結束日期"   />
                                </div>
                            
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">排序</label>
                            <div class="col-xs-12 col-sm-4">
                             	
                                    <input type="tel" class="form-control digits" name="range" id="range" value="<?php if(isset($row)) echo $row["range"]?>" placeholder="排序" required  />
                                   
                               
                            
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

