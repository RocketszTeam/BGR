<script type="text/JavaScript">
    $(function(){
        $('.activeSwitch').click(function(){
            $.ajax({
                type: "POST",
                url: "<?php echo site_url(SYSTEM_URL."/payment/enableChange")?>",
                cache: false,
                data: { uid: $(this).val(), enable: ($(this).prop('checked') ? 1 : 0) }
            });
        });

    });
</script>
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="page-header">
                <a href="<?php echo site_url(SYSTEM_URL."/Payment/create/".$groupID.'/atm')?>" class="btn btn-danger">新增ATM</a>
                <a href="<?php echo site_url(SYSTEM_URL."/Payment/create/".$groupID.'/cvs')?>" class="btn btn-success">新增CVS</a>
                <!--<a href="<?php echo site_url(SYSTEM_URL."/Payment/create/".$groupID.'/atmncvs')?>" class="btn btn-info">新增CVS & ATM</a>-->
                <a href="<?php echo site_url(SYSTEM_URL."/Payment/create/".$groupID.'/credit')?>" class="btn btn-warning">新增信用卡</a>
                <a href="<?php echo site_url(SYSTEM_URL."/Payment/create/".$groupID.'/atm?patchFlag=1')?>" class="btn btn-danger">批次新增ATM</a>
                <a href="<?php echo site_url(SYSTEM_URL."/Payment/create/".$groupID.'/cvs?patchFlag=1')?>" class="btn btn-success">批次新增CVS</a>
                <!--<a href="<?php echo site_url(SYSTEM_URL."/Payment/create/".$groupID.'/atmncvs?patchFlag=1')?>" class="btn btn-info">批次新增CVS & ATM</a>-->
                <a href="<?php echo site_url(SYSTEM_URL."/Payment/create/".$groupID.'/credit?patchFlag=1')?>" class="btn btn-warning">批次新增信用卡</a>
            </div>
            <ul class="nav nav-tabs">
                <?php foreach(memberGroup() as $key=>$val):?>
                    <?php
                    $hrefTag=site_url(SYSTEM_URL."/Payment/index/".$key);
                    $activeTag = "";
                        if($key == $groupID){
                            $activeTag = 'active';
                            $hrefTag="#";
                        }
                    ?>
                    <li role="presentation" class="<?=$activeTag;?>"><a href="<?=$hrefTag;?>"><?=memberGroup($key)?></a></li>
                <?php endforeach;?>
            </ul>
            <table id="simple-table" class="table table-bordered table-hover table-responsive">
                <thead>
                    <tr>
                        <td>金流名稱</td>
                        <td>項目</td>
                        <td>商家代號</td>
                        <td>HashKey / 交易密碼</td>
                        <td>累計金額</td>
                        <td>使用狀態</td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                <?php if(isset($row)):?>
                    <?php foreach ($row as $key => $val):?>
                    <tr>
                        <td><?=$val['gatewayName'];?></td>
                        <td><?=paymentType($val['paymentType']);?></td>
                        <td><?=$val['merchant'];?></td>
                        <td><?=$val['HashKey'];?></td>
                        <td class="text-right">$<?=number_format($val['counting']);?></td>
                        <td>
                            <label>
                                <input name="switch-field-1" class="ace ace-switch  activeSwitch" type="checkbox" value="<?=$val["uid"]?>" <?php if ($val["enable"]) echo ' checked' ?> />
                                <span class="lbl"></span>
                            </label>
                        </td>
                        <td>
                            <div class="hidden-sm hidden-xs btn-group">

                                <a href="<?php echo site_url($editBTN.$val["uid"])?>" class="btn btn-xs btn-info" data-rel="tooltip"  title="修改">
                                    <i class="ace-icon fa fa-pencil bigger-120"></i>
                                </a>
                                <button class="btn btn-xs btn-danger" data-toggle="modal" title="刪除" data-target="#dialog-confirm" data-action="<?php echo site_url($delBTN.$val["uid"])?>">
                                    <i class="ace-icon fa fa-trash-o bigger-120"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                <?php endif;?>
                </tbody>
            </table>
        </div>
    </div>





</form>

<div class="page-header"></div>