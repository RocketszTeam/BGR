<div class="page-header">
    <a href="<?php echo site_url(SYSTEM_URL."/gateway/create")?>" class="btn btn-primary">新增功能</a>
</div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 padding-5">
            <table id="simple-table" class="table table-bordered table-hover table-responsive">
                <thead>
                <tr>
                    <th>金流名稱</th>
                    <th>CVS & ATM開放層級</th>
                    <th>信用卡開放層級</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $row):?>
                        <tr>
                            <td><?= $row['gatewayName'];?></td>
                            <td>
                                <?php foreach (memberGroup() as $k => $v):?>
                                    <?php if(pow(2, $k) & $row['atmncvs']):?>
                                        <span class="label <?=memberLabel($k);?>"><?=$v;?></span>
                                    <?php endif;?>
                                <?php endforeach;?>
                            </td>
                            <td>
                                <?php foreach (memberGroup() as $k => $v):?>
                                    <?php if(pow(2, $k) & $row['credit']):?>
                                        <span class="label <?=memberLabel($k);?>"><?=$v;?></span>
                                    <?php endif;?>
                                <?php endforeach;?>
                            </td>
                            <td>
                                <a href="<?php echo site_url($editBTN.$row["uid"]) ?>" class="btn btn-xs btn-info" title="修改">
                                    <i class="ace-icon fa fa-pencil bigger-120"></i>
                                </a>
                                <button class="btn btn-xs btn-danger" data-toggle="modal" title="刪除" data-target="#dialog-confirm" data-action="<?php echo site_url($delBTN.$row["uid"])?>">
                                    <i class="ace-icon fa fa-trash-o bigger-120"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach;?>
                </tbody>
            </table>