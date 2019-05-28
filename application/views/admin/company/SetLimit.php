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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">最低限額=>超級大小、超級單雙、五行</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="digits form-control" id="lower_min_amount" name="lower_min_amount" value="<?php echo $SetLimit["lower_min_amount"]?>"  required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">最低限額=>1~5星、猜大小</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="digits form-control" id="lower_min_amount_static" name="lower_min_amount_static" value="<?php echo $SetLimit["lower_min_amount_static"]?>"  required="required" />
                            </div>
                        </div>
                        <?php for($i=1;$i<=5;$i++):?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">最高限額=><?php echo $i?>星</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="digits form-control" id="upper_s<?php echo $i?>" name="upper_s<?php echo $i?>" value="<?php echo $SetLimit["upper_s".$i]?>"  required="required" />
                            </div>
                        </div>
						<?php endfor;?>                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">最高限額=>猜大小</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="digits form-control" id="upper_bs" name="upper_bs" value="<?php echo $SetLimit["upper_bs"]?>"  required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">最高限額=>超級單雙</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="digits form-control" id="upper_SPoe" name="upper_SPoe" value="<?php echo $SetLimit["upper_SPoe"]?>"  required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">最高限額=>超級大小</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="digits form-control" id="upper_SPbs" name="upper_SPbs" value="<?php echo $SetLimit["upper_SPbs"]?>"  required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">最高限額=>五行</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="digits form-control" id="upper_five" name="upper_five" value="<?php echo $SetLimit["upper_five"]?>"  required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">單場限額=>猜大小</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="digits form-control" id="game_bs" name="game_bs" value="<?php echo $SetLimit["game_bs"]?>"  required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">單場限額=>超級單雙</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="digits form-control" id="game_SPoe" name="game_SPoe" value="<?php echo $SetLimit["game_SPoe"]?>"  required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">單場限額=>超級大小</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="digits form-control" id="game_SPbs" name="game_SPbs" value="<?php echo $SetLimit["game_SPbs"]?>"  required="required" />
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

