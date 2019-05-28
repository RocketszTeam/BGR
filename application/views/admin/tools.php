<script type="text/javascript">
    $(function(){
        $('.activeSwitch').click(function(){
            $.ajax({
                type: "POST",
                url: "<?php echo site_url(SYSTEM_URL."/Games/keyChange")?>",
                cache: false,
                data: { num: $(this).val(), value: ($(this).prop('checked') ? 'Y' : 'N') }
            });
        });
    });
</script>
<!-- PAGE CONTENT BEGINS -->
<?php if($openFind=='Y'):   //啟用搜尋才顯示?>
    <div class="page-header">
        <form class="form-horizontal" method="post" action="<?php echo $s_action ?>">
            <div class="row">
                <div class="col-xs-12 col-sm-12">
                    <div id="accordion" class="accordion-style1 panel-group">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title ">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                        <i class="ace-icon fa fa-angle-down bigger-110" data-icon-hide="ace-icon fa fa-angle-down" data-icon-show="ace-icon fa fa-angle-right"></i>
                                        &nbsp;篩選條件
                                    </a>
                                </h4>
                            </div>
                            <div class="panel-collapse collapse" id="collapseOne">
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">遊戲廠商</label>
                                        <div class="col-xs-12 col-sm-5">
                                            <select class="form-control select2" name="find2" id="find2" data-placeholder="請選擇">
                                                <option value="">請選擇</option>
                                                <?php if(isset($row_group)):?>
                                                    <?php foreach($row_group as $row):?>
                                                        <option value="<?php echo $row["makers_num"]?>" <?php if($row["makers_num"]==@$_REQUEST["find2"]) echo ' selected'?>><?php echo $row["makers_name"]?></option>
                                                    <?php endforeach;?>
                                                <?php endif;?>
                                            </select>
                                            <?php if (@$_REQUEST["find2"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."遊戲廠商=【".
                                                tb_sql("makers_name","game_makers",$result[0]["makers_num"])
                                                ."】";} ?>


                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">遊戲名稱</label>
                                        <div class="col-xs-12 col-sm-5">
                                            <input type="text" class="form-control" id="find3" name="find3" value="<?php echo @$_REQUEST["find3"]?>" placeholder="標題" />
                                            <?php if (@$_REQUEST["find3"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."遊戲名稱=【".@$_REQUEST["find3"]."】";} ?>
                                        </div>
                                    </div>


                                    <div class="text-center">
                                        <a href="<?php echo site_url(uri_string()) ?>" class="btn btn-yellow btn-sm">
                                            <span class="ace-icon fa fa-times icon-on-right bigger-110"></span>
                                            清除篩選
                                        </a>
                                        <button type="submit" class="btn btn-purple btn-sm">
                                            <span class="ace-icon fa fa-search icon-on-right bigger-110"></span>
                                            條件篩選
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </form>
        <?php
        if(@$find_msg!=""){ //列出搜尋條件
            $find_arr=explode('、',$find_msg);
            ?>
            <div class="widget-box widget-color-green">
                <div class="widget-body">
                    <div class="widget-main padding-8">
                        <ul class="list-unstyled spaced">
                            <?php foreach($find_arr as $find_str):?>
                                <li><i class="ace-icon fa fa-search bigger-110 red"></i><?php echo $find_str?></li>
                            <?php endforeach;?>
                        </ul>
                    </div>
                </div>
            </div>

            <?php
        }
        ?>
    </div>

<?php endif;?>
<form class="form-horizontal" method="post" action="<?php echo $s_action ?>">

    <?php //var_dump($this->_ci_cached_vars);
    ?>

    <div class="form-group">
        <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">遊戲廠商</label>
        <div class="col-xs-12 col-sm-5">
            <select class="form-control select2" name="find2" id="find2" data-placeholder="請選擇">
                <option value="">請選擇</option>
                <?php if(isset($game_makers)):?>
                    <?php foreach($game_makers as $row):?>
                        <option value="<?php echo $row["num"]?>" <?php if($row["num"]==@$_REQUEST["find2"]) echo ' selected'?>><?php echo $row["makers_name"]?></option>
                    <?php endforeach;?>
                <?php endif;?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">帳號</label>

        <div class="col-xs-12 col-sm-5">
            <input type="text" class="form-control" id="find1" name="find1" value="<?php echo @$_REQUEST[ "find1" ] ?>"
                   placeholder="標題"/>
            <?php if (@$_REQUEST[ "find1" ] != "") {
                $find_msg .= ( $find_msg != "" ? "、" : "" ) . "帳號=【" . @$_REQUEST[ "find1" ] . "】";
            } ?>
        </div>
    </div>
    <div class="form-group text-center">
        <button name="submit" class="btn btn-sm btn-primary">查詢</button>
    </div>
</form>
<table id="simple-table" class="table table-bordered table-hover table-responsive">
    <thead>
    <tr>
        <!--<th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace" />
                <span class="lbl"></span>
            </label>
        </th>-->

        <th>遊戲廠商</th>
        <th>使用者名稱</th>
        <th>餘額</th>
        <th>管理</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if(isset($member)):
        foreach($member as $row):
            //print_r($row);
            ?>
            <tr  class="<?php echo $row[ "balance" ] > 0 ? 'has-bonus' : '' ?>" style="background-color:<?php echo $row[ "balance" ] > 0 ? '#FFB7DD' : '' ?>">
                <td><?php echo tb_sql('makers_name','game_makers',$row['gamemaker_num']); ?></td>
                <td><?php echo $row["u_id"]?></td>
                <td><span id='bal_<?php echo $row["mem_num"].'_'.$row['gamemaker_num'];?>'><?php echo $row["balance"] ?></span></td>
                <td><button onclick='$(this).account_withdraw(<?php echo $row["mem_num"].','.$row['gamemaker_num']; ?>)'>轉回電子錢包</button>
                </td>

            </tr>
        <?php endforeach;
    endif;
    ?>
    <tr>
        <td colspan=3>
            <?php echo $links ;?>
        </td>
        <td>
            <?php if(!empty($member)):?>
                <button id="balAllReturnBtn">全部轉回電子錢包</button>
            <?php endif;?>
        </td>
    </tr>
    </tbody>
</table>

<div class="text-center">
    <?php echo @$pagination ?>
    <!--<ul class="pagination">
        <li><a href="#"><i class="ace-icon fa fa-angle-double-left"></i></a></li>
        <li><a href="#">1</a></li>
        <li class="active"><a href="#">2</a></li>
        <li><a href="#"><i class="ace-icon fa fa-angle-double-right"></i></a></li>
    </ul>-->
</div>
<script>
    $( document ).ready(function() {
        $('#balAllReturnBtn').click(function(){
            $("table#simple-table button[id!='balAllReturnBtn']").each(function (i){
                setTimeout(btnClick, i * 300, $(this));
            });
        });
        function btnClick(obj){
            obj.click();
        }

        $.fn.account_withdraw = function(mem_num,gamemaker_num) {
            //alert('hello world');
            // alert (mem_num);
            // alert (gamemaker_num);

            $.ajax({
                url: '<?php echo $s_balance;?>',
                cache: false,
                dataType: 'json',
                type:'POST',
                data: { action: 'withdraw',
                    mem_num : mem_num,
                    gamemaker_num : gamemaker_num
                },
                error: function(xhr) {
                    console.log(xhr.responseText); },
                success: function(response) {
                    $('#bal_'+mem_num+'_'+gamemaker_num).html(response);
                    $('#bal_'+mem_num+'_'+gamemaker_num).parent().parent().removeAttr('style');
                    // alert(response);

                }
            })

        };


    });
</script>

