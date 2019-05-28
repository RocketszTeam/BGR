
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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">金流名稱</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="gatewayName" name="gatewayName" value="<?php if(isset($row)) echo $row["gatewayName"]?>" placeholder="金流名稱" required="required" />
                            	
                             	
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">串接網址</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="gatewayUrl" name="gatewayUrl" value="<?php if(isset($row)) echo $row["gatewayUrl"]?>" placeholder="串接網址"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">ATM & CVS開放層級</label>
                            <div class="col-xs-12 col-sm-5">
                                <?php foreach (memberGroup() as $k => $v):?>
                                    <div class="form-check inline">
                                        <input class="form-check-input" type="checkbox" id="atmncvs<?=$k;?>" name="atmncvs[]" value="<?=pow(2, $k);?>"
                                        <?php
                                            if(isset($row)){
                                                if(pow(2, $k) & $row['atmncvs']){
                                                    echo 'checked';
                                                }
                                            }
                                        ?>>
                                        <label class="form-check-label" for="atmncvs<?=$k;?>"><?=memberGroup($k);?></label>
                                    </div>
                                <?php endforeach;?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">信用卡開放層級</label>
                            <div class="col-xs-12 col-sm-5">
                                <?php foreach (memberGroup() as $k => $v):?>
                                    <div class="form-check inline">
                                        <input class="form-check-input" type="checkbox" id="credit<?=$k;?>" name="credit[]" value="<?=pow(2, $k);?>"
                                            <?php
                                            if(isset($row)){
                                                if(pow(2, $k) & $row['credit']){
                                                    echo 'checked';
                                                }
                                            }
                                            ?>>
                                        <label class="form-check-label" for="credit<?=$k;?>"><?=memberGroup($k);?></label>
                                    </div>
                                <?php endforeach;?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">Libraries名稱</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="libs" name="libs" value="<?php if(isset($row)) echo $row["libs"]?>" placeholder="Libraries名稱"/>

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

