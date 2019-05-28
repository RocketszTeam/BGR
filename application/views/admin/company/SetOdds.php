<form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction ?>">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="widget-box widget-color-blue2">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller"><?php echo $subtitle?></h4>
                    
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-8">
                    	<div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">1星(中1)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="s1_z1" name="s1_z1" value="<?php echo $SetOdds["s1_z1"]?>"  required="required" />
                                </div>
                            </div>
                            <?php for($i=1;$i<=2;$i++):?>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">2星(中<?php echo $i?>)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="s2_z<?php echo $i?>" name="s2_z<?php echo $i?>" value="<?php echo $SetOdds["s2_z".$i]?>"  required="required" />
                                </div>
                            </div>
                            <?php endfor;?>
                            <?php for($i=2;$i<=3;$i++):?>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">3星(中<?php echo $i?>)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="s3_z<?php echo $i?>" name="s3_z<?php echo $i?>" value="<?php echo $SetOdds["s3_z".$i]?>"  required="required" />
                                </div>
                            </div>
                            <?php endfor;?>
                            <?php for($i=2;$i<=4;$i++):?>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">4星(中<?php echo $i?>)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="s4_z<?php echo $i?>" name="s4_z<?php echo $i?>" value="<?php echo $SetOdds["s4_z".$i]?>"  required="required" />
                                </div>
                            </div>
                            <?php endfor;?>
                            <?php for($i=3;$i<=5;$i++):?>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">5星(中<?php echo $i?>)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="s5_z<?php echo $i?>" name="s5_z<?php echo $i?>" value="<?php echo $SetOdds["s5_z".$i]?>"  required="required" />
                                </div>
                            </div>
                            <?php endfor;?>
                    	</div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">猜大小(大)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="bs_b" name="bs_b" value="<?php echo $SetOdds["bs_b"]?>"  required="required" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">猜大小(小)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="bs_s" name="bs_s" value="<?php echo $SetOdds["bs_s"]?>"  required="required" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">超級單雙(單)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="SPoe_o" name="SPoe_o" value="<?php echo $SetOdds["SPoe_o"]?>"  required="required" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">超級單雙(雙)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="SPoe_e" name="SPoe_e" value="<?php echo $SetOdds["SPoe_e"]?>"  required="required" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">超級大小(大)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="SPbs_b" name="SPbs_b" value="<?php echo $SetOdds["SPbs_b"]?>"  required="required" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">超級大小(小)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="SPbs_s" name="SPbs_s" value="<?php echo $SetOdds["SPbs_s"]?>"  required="required" />
                                </div>
                            </div>
                            <?php for($i=1;$i<=5;$i++):?>
                            <div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">五行(<?php echo $fiveArray[$i]?>)</label>
                                <div class="col-xs-12 col-sm-5">
                                    <input type="text" class="number form-control" id="five_f<?php echo $i?>" name="five_f<?php echo $i?>" value="<?php echo $SetOdds["five_f".$i]?>"  required="required" />
                                </div>
                            </div>
                            <?php endfor;?>
                        </div>
                    </div>
                </div>
                
                <div class="widget-footer">
                	<div class="widget-main padding-8">
                        <div class="form-group">
                            <!--<label for="inputWarning" class="col-sm-1 control-label no-padding-right"></label>-->
                            <div class="col-xs-12 col-sm-12 center">
                                <button type="submit" class="btn btn-primary">送出儲存</button>
                            </div>    
                        </div> 
                    </div>    
                </div>
            </div>
        </div>
    </div>
</form>

