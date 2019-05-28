
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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">
                                <?php if(!empty($patchFlag)){
                                    echo '批次處理';
                                }else{
                                    echo memberGroup($groupID);
                                }
                                ?>
                            </label>
                            <label class="col-xs-12 col-sm-5 control-label" style="text-align: left;"><?=paymentType($paymentType);?></label>
                            <input type="hidden" name="m_group" value="<?=$groupID?>"/>
                            <input type="hidden" name="paymentType" value="<?=$paymentType?>"/>
                            <?php if(!empty($patchFlag)):?>
                            <input type="hidden" name="patchFlag" value="<?=$patchFlag?>"/>
                            <?php endif ?>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">金流</label>
                            <div class="col-xs-12 col-sm-5">
                                <select class="form-control" id="libs" name="libs">
                                    <?php foreach ($getewayList as $key => $val):?>
                                    <?php
                                        $selected = '';
                                        if(isset($row))
                                            if($row['libs'] == $val['libs'])$selected = 'selected';
                                    ?>
                                    <option value="<?=$val['libs'];?>" <?=$selected;?>><?=$val['gatewayName'];?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">狀態</label>
                            <div class="col-xs-12 col-sm-5">
                                <?php
                                    $checkedOpt = ['停用', '啟用'];
                                ?>
                                <?php foreach($checkedOpt as $key => $val):?>
                                    <?php
                                        $checked = '';
                                        if(isset($row)){
                                            if($row['enable'] == $key)$checked = 'checked';
                                        }else{
                                            if($key == 0)$checked = 'checked';
                                        }
                                    ?>
                                <label class="radio-inline">
                                    <input type="radio" name="enable" id="enable<?=$key;?>" value="<?=$key;?>" <?=$checked;?>><?=$val;?>
                                </label>
                                <?php endforeach;?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">商家代號</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="merchant" name="merchant" value="<?php if(isset($row)) echo $row["merchant"]?>" placeholder="Merchant / web / Customer_no"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">HashKey / 交易密碼</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="HashKey" name="HashKey" value="<?php if(isset($row)) echo $row["HashKey"]?>" placeholder="HashKey / code / 交易密碼"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">HashIV</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="HashKey" name="HashIV" value="<?php if(isset($row)) echo $row["HashIV"]?>" placeholder="HashIV"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">驗證碼</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="validate" name="validate" value="<?php if(isset($row)) echo $row["validate"]?>" placeholder="validate"/>

                            </div>
                        </div>
                        <?php if(isset($row)):?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">目前累計金額</label>
                            <label class="col-xs-12 col-sm-5 control-label" style="text-align: left;"><?=$row["counting"];?></label>
                        </div>
                        <?php endif;?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">收款截止金額</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="amount" name="amount" value="<?php if(isset($row)) echo $row["amount"]?>" placeholder="收款截止金額"/>
                                <div class="text-danger">*金額設為0表示持續收款</div>
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

