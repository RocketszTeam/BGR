<style type="text/css">
    .mobile-submenu .btn { 
        color: black !important; 
        background: white !important;
    }
    tr.border_bottom {
        /*border: 2px solid #FEE188;*/
        box-shadow:inset 0 0 5px red;
    }
</style>
<script type="text/JavaScript">
    $(function(){
        $('.activeSwitch').click(function(){
            $.ajax({
                type: "POST",
                url: "<?php echo site_url(SYSTEM_URL."/Member/keyChange")?>",
                cache: false,
                data: { num: $(this).val(), value: ($(this).prop('checked') ? 'Y' : 'N') }
            }).done(function( htmlData ) {  
                $.unblockUI();
                if(htmlData != "OK"){
                    $('#message').text(htmlData);
                    //$('#alrtMsg').show();
                    //$('#message').modal({ keyboard: false ,backdrop: 'static'});
                    $('#message').modal('show');
                    console.log("SHOW");
                }
                //console.log(htmlData);
            });;
        });
        
        $('#smsBTN').click(function(){
            $.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/admin/images/loadingbar/009.gif" >',baseZ: 2000});
            $.ajax({
                type: "POST",
                url: CI_URL + "<?php echo SYSTEM_URL?>/Member/member_sms",
                cache: false,
                async:false,
                dataType:"json",
                data: { 
                        mem_num : $('#mem_num').val(),
                        sms_type : $('#sms_type').val(),
                        subject : $('#subject').val(),
                        sms_body : $('#sms_body').val(),
                      }
            }).done(function( htmlData ) {  
                $.unblockUI();
                $('#alrtTitle').text(htmlData.title);
                $('#alertBody').text(htmlData.Msg)
                if(htmlData.RntCode==1){
                    $('#alrtMsg').removeClass('alert-danger').addClass('alert-success');
                    $(".DelVal").prop('value','');
                    
                    
                }else{
                    $('#alrtMsg').removeClass('alert-success').addClass('alert-danger');
                }
                
                $('#alrtMsg').show();
                //$('.selectpicker').selectpicker('refresh');//重新特效抓取
            });
            
            
        });
        
        $('.show_modal').click(function(){
            $('#alrtMsg').hide();
            $(".DelVal").prop('value','');
            $('#mem_num').attr('value',$(this).attr('data-num'));
            $('#show_id').html($(this).attr('data-name'));
            $('#member_sms').modal({ keyboard: false ,backdrop: 'static'});
            $('#member_sms').modal('show');
        });
        
        //$('.mobile-submenu').collapse()
        
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
                                <?php if(isset($upAccount)):?>
                                <div class="form-group">

                <?php

                if (($web_root_u_power != 6) and ($web_root_u_power != 4) and ($web_root_u_power != 5)){
                    ?>

                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">代理</label>
                                    <div class="col-xs-12 col-sm-5">


                                      <select class="form-control select2" name="find1" id="find1" data-placeholder="請選擇">
                                        <option value="">請選擇</option>
                                        <?php if(isset($upAccount)):?>
                                        <?php foreach($upAccount as $row):?>
                                        <option value="<?php echo $row["num"]?>" <?php if($row["num"]==@$_REQUEST["find1"]) echo ' selected'?>><?php echo $row["u_id"]?>(<?php echo $row["u_name"]?>)</option>
                                        <?php endforeach;?>
                                        <?php endif;?>      
                                      </select>

<?              }       ?>
                                      <?php if (@$_REQUEST["find1"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."代理=【".tb_sql("u_name","admin",@$_REQUEST["find1"])."】";} ?>
                                    </div>
                                 </div>
                                 <?php endif;?>
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">帳號</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find2" name="find2" value="<?php echo @$_REQUEST["find2"]?>" placeholder="帳號" />
                                      <?php if (@$_REQUEST["find2"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."帳號=【".@$_REQUEST["find2"]."】";} ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">姓名</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find3" name="find3" value="<?php echo @$_REQUEST["find3"]?>" placeholder="姓名" />
                                      <?php if (@$_REQUEST["find3"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."姓名=【".@$_REQUEST["find3"]."】";} ?>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">IP</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find4" name="find4" value="<?php echo @$_REQUEST["find4"]?>" placeholder="IP" />
                                      <?php if (@$_REQUEST["find4"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."IP=【".@$_REQUEST["find4"]."】";} ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">電話</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find5" name="find5" value="<?php echo @$_REQUEST["find5"]?>" placeholder="電話" />
                                      <?php if (@$_REQUEST["find5"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."電話=【".@$_REQUEST["find5"]."】";} ?>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">狀態</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <select class="form-control select2" name="find6" id="find6" data-placeholder="請選擇">
                                        <option value="">請選擇</option>
                                        <option value="Y" <?php if (@$_REQUEST["find6"]=="Y"){?> selected="selected" <?php } ?>>啟用</option>
                                        <option value="N" <?php if (@$_REQUEST["find6"]=="N"){?> selected="selected" <?php } ?>>停權</option>
                                      </select>
                                      <?php if (@$_REQUEST["find4"]=="Y"){$find_msg.=($find_msg!="" ? "、" : "")."狀態=【啟用】";} ?>
                                      <?php if (@$_REQUEST["find4"]=="N"){$find_msg.=($find_msg!="" ? "、" : "")."狀態=【停權】";} ?>
                                    </div>
                                 </div>     
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">登入日期</label>
                                    <div class="col-xs-12 col-sm-5">
                                        <div class="input-daterange input-group">
                                            <input type="text" class="input-sm form-control jqdatetime" name="find7" id="find7" value="<?php echo @$_REQUEST["find7"]?>" />
                                            <span class="input-group-addon">
                                                <i class="fa fa-exchange"></i>
                                            </span>
                                            <input type="text" class="input-sm form-control jqdatetime" name="find8" id="find8" value="<?php echo @$_REQUEST["find8"]?>"  />
                                            <?php if (@$_REQUEST["find7"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."登入日期-起=【".@$_REQUEST["find7"]."】";} ?> 
                                            <?php if (@$_REQUEST["find8"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."登入日期-訖=【".@$_REQUEST["find8"]."】";} ?> 
                                        </div>
                                    </div>
                                </div>   
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">註冊日期</label>
                                    <div class="col-xs-12 col-sm-5">
                                        <div class="input-daterange  input-group">
                                            <input type="text" class="input-sm form-control jqdatetime" name="find9" id="find9" value="<?php echo @$_REQUEST["find9"]?>" />
                                            <span class="input-group-addon">
                                                <i class="fa fa-exchange"></i>
                                            </span>
                                            <input type="text" class="input-sm form-control jqdatetime" name="find10" id="find10" value="<?php echo @$_REQUEST["find10"]?>"   />
                                            <?php if (@$_REQUEST["find9"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."註冊日期-起=【".@$_REQUEST["find9"]."】";} ?> 
                                            <?php if (@$_REQUEST["find10"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."註冊日期-訖=【".@$_REQUEST["find10"]."】";} ?> 
                                        </div>
                                    </div>
                                </div>  
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">儲值狀態</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <select class="form-control select2" name="find11" id="find11" data-placeholder="請選擇">
                                        <option value="">請選擇</option>
                                        <option value="Y" <?php if (@$_REQUEST["find11"]=="Y"){?> selected="selected" <?php } ?>>7天內有儲值</option>
                                        <option value="N" <?php if (@$_REQUEST["find11"]=="N"){?> selected="selected" <?php } ?>>7天內未儲值</option>
                                        <option value="W" <?php if (@$_REQUEST["find11"]=="W"){?> selected="selected" <?php } ?>>未曾儲值</option>
                                      </select>
                                      <?php if (@$_REQUEST["find11"]=="Y"){$find_msg.=($find_msg!="" ? "、" : "")."儲值狀態=【7天內有儲值】";} ?>
                                      <?php if (@$_REQUEST["find11"]=="N"){$find_msg.=($find_msg!="" ? "、" : "")."儲值狀態=【7天內未儲值】";} ?>
                                      <?php if (@$_REQUEST["find11"]=="W"){$find_msg.=($find_msg!="" ? "、" : "")."儲值狀態=【未曾儲值】";} ?>
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


<table id="simple-table" class="table table-bordered table-hover table-responsive">
    <thead>
        <tr>
            <!--<th class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" />
                    <span class="lbl"></span>
                </label>
            </th>-->
            <th>代理</th>
            <th>帳號</th>
            <th class="hidden-480">姓名</th>
            <th>儲值/拋售</th>
            <th class="hidden-480">周拋售次數<a href="#" data-toggle="tooltip" title="每周一 00點起"><i class="glyphicon glyphicon-question-sign"></i></a></th>            
            <th class="hidden-480">登入/註冊時間</th>
            <th class="hidden-480">登入IP</th>
            <th>狀態</th>
            <th>管理</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if(isset($result)):
            foreach($result as $row):
        ?>
        <tr>
            <td>
                <?php echo tb_sql("u_id","admin",$row["admin_num"])?>
                <div class="text-danger"><?php echo tb_sql("u_name","admin",$row["admin_num"])?></div> 
            </td>
            <td>
                <?php if($view_auth){ ?>

                <a href="javascript:void(0)" data-msg-title="備註" data-msg-content="<?php echo $row["demo"]?>" data-rel="tooltip" title="查看會員備註" data-toggle="info">
                    <?php echo $row["u_id"]?>
                </a>
                <?php }else{ ?>
                    <?php echo $row["u_id"]?>

                <?php } ?>
                <?php
                    $depositCheck=depositCheck($row["num"]);
                    switch ($depositCheck){
                        case 'Y':
                            echo '<span class="label label-success arrowed arrowed-right">7天內有儲值</span>';
                            break;
                        case 'N':
                            echo '<span class="label label-danger arrowed arrowed-right">7天內未儲值</span>';
                            break;
                        default:
                            echo '<span class="label label-warning arrowed arrowed-right">未曾儲值</span>';
                            break;
                    }
                ?>
                <div class="text-success"><?php echo inNumberString($member_group,$row["m_group"])?></div>
                <div class="hidden-md hidden-lg text-danger">
                    <?php echo $row["u_name"]?>
                </div>
            </td>
            <td class="hidden-480">

                <?php
                    $scoreClass='';
                    if(memAtmCount($row["num"]) < 40){
                        $scoreClass='text-danger';
                    }elseif(memAtmCount($row["num"]) >= 40){
                        $scoreClass='text-success';
                    }
                ?>


            <?php echo $row["u_name"]?>
                
                <div class="<?php echo $scoreClass?>" data-rel="tooltip" title="ATM繳費評分"><?php echo memAtmCount($row["num"])?> % </div> 

                
            </td>
            <td>
                <?php
                    $saveClass='';
                    if(getAllSave($row["num"]) < 0){
                        $saveClass='text-danger';
                    }elseif(getAllSave($row["num"]) > 0){
                        $saveClass='text-success';
                    }
                    $sellClass='';
                    if(getAllSell($row["num"]) > 0){
                        $sellClass='text-danger';
                    }
                ?>
                <div class="<?php echo $saveClass?>" data-rel="tooltip" title="儲值"><?php echo number_format(getAllSave($row["num"]),0)?></div>
                <div class="<?php echo $sellClass?>" data-rel="tooltip" title="拋售"><?php echo number_format(getAllSell($row["num"]),0)?></div> 
            </td>


              <td class="hidden-480">
                <?php
                    //拋售提領次數        
                    $wbt = getWeekSell($row["num"],$bt);
                    if($wbt >= 2){
                        echo '<span class="badge badge-success">';
                    } else {
                        echo '<span class="badge">';
                    }
                    
                    echo $wbt.'</span>';            
                ?>
            </td>
            

            <td class="hidden-480">
                <?php echo $row["login_time"]?>
                <div class="text-danger"><?php echo $row["reg_time"]?></div>
            </td>
            <td class="hidden-480">

                <?php if($view_auth){ ?>

                <a href="<?php echo site_url(SYSTEM_URL."/Member/login_list/".$row["num"]."?per_page=".$nowpage.$att) ?>" data-rel="tooltip" title="登入歷程"><?php echo $row["login_ip"]?></a>
                <?php }else{ ?>
                    <?php echo $row["login_ip"]?>
                <?php } ?>

            </td>
            <td>
            
                <label>
                    <input name="switch-field-1" class="ace ace-switch  activeSwitch" type="checkbox" value="<?php echo $row["num"]?>" <?php if ($row["active"]=="Y") echo ' checked' ?> <?php if(!$edit_auth) echo ' disabled' ?> />
                    <span class="lbl"></span>
                </label>
                
            </td>
            <td>
                <div class="hidden-sm hidden-xs btn-group">
                    <a  class="btn btn-xs show_modal" data-num="<?php echo $row["num"]?>" data-name="<?php echo $row["u_id"].'('.$row["u_name"].')'?>" data-rel="tooltip" title="訊息發送">
                        <i class="ace-icon fa fa-envelope bigger-120"></i>
                    </a>    
                    <a href="<?php echo site_url(SYSTEM_URL."/Member/agents_exchange/".$row["num"]."?per_page=".$nowpage.$att) ?>" class="btn btn-xs btn-yellow" data-rel="tooltip" title="代理換線">
                        <i class="ace-icon fa fa-exchange bigger-120"></i>
                    </a>
                    <a href="<?php echo site_url(SYSTEM_URL."/Member/bank/".$row["num"]."?per_page=".$nowpage.$att) ?>" class="btn btn-xs btn-pink" data-rel="tooltip" title="銀行資料">
                        <i class="ace-icon fa fa-university bigger-120"></i>
                    </a>
                    <a href="<?php echo site_url(SYSTEM_URL."/Member/wallet_list/".$row["num"]."?per_page=".$nowpage.$att) ?>" class="btn btn-xs btn-purple" data-rel="tooltip" title="點數管理">
                        <i class="ace-icon fa fa-credit-card bigger-120"></i>
                    </a>
                    <?php if($view_auth){ ?>
                    <a href="<?php echo site_url(SYSTEM_URL."/Member/games_account/".$row["num"]."?per_page=".$nowpage.$att) ?>" class="btn btn-xs btn-warning" data-rel="tooltip" title="遊戲帳號">
                        <i class="ace-icon fa fa-gamepad bigger-120"></i>
                    </a>
                    <?php } ?>
                    <?php if($edit_auth){ ?>
                    <a href="<?php echo site_url($editBTN.$row["num"]."?per_page=".$nowpage.$att)?>" class="btn btn-xs btn-info" data-rel="tooltip"  title="修改">
                        <i class="ace-icon fa fa-pencil bigger-120"></i>
                    </a>
                    <?php } ?>       
                    <?php if($view_auth){ ?>
                    <a href="<?php echo site_url($viewBTN.$row["num"]."?per_page=".$nowpage.$att)?>" class="btn btn-xs" data-rel="tooltip"  title="瀏覽">
                        <i class="ace-icon fa fa-search bigger-120"></i>
                    </a>
                    <?php } ?>                
                    
                </div>
                <div class="hidden-md hidden-lg">
                    <div class="inline pos-rel">

                        <button class="btn btn-minier btn-primary" data-toggle="collapse" data-target="#mobile-submenu<?php echo $row["num"]?>">
                            <i class="ace-icon fa fa-cog icon-only bigger-110"></i>
                        </button>
                        <div id="mobile-submenu<?php echo $row["num"]?>" class="collapse mobile-submenu">
                            <div class="btn-group-vertical">
                                    <a data-num="<?php echo $row["num"]?>" data-name="<?php echo $row["u_id"].'('.$row["u_name"].')'?>" class=" btn btn-minier btn-default show_modal tooltip-success" data-rel="tooltip" title="訊息發送">
                                        <span class="red">
                                            <i class="ace-icon fa fa-envelope bigger-120"></i>
                                        </span>
                                    </a>

                                    <a href="<?php echo site_url(SYSTEM_URL."/Member/agents_exchange/".$row["num"]."?per_page=".$nowpage.$att) ?>" class="btn btn-minier btn-default tooltip-success" data-rel="tooltip" title="代理換線">
                                        <span class="purple">
                                            <i class="ace-icon fa fa-exchange bigger-120"></i>
                                        </span>
                                    </a>

                                    <a href="<?php echo site_url(SYSTEM_URL."/Member/bank/".$row["num"]."?per_page=".$nowpage.$att) ?>" class="btn btn-minier btn-default tooltip-success" data-rel="tooltip" title="銀行資料">
                                        <span class="inverse">
                                            <i class="ace-icon fa fa-university fa-inversebigger-120"></i>
                                        </span>
                                    </a>

                                    <a href="<?php echo site_url(SYSTEM_URL."/Member/wallet_list/".$row["num"]."?per_page=".$nowpage.$att) ?>" class="btn btn-minier btn-default tooltip-success" data-rel="tooltip" title="點數管理">
                                        <span class="purple">
                                            <i class="ace-icon fa fa-credit-card bigger-120"></i>
                                        </span>
                                    </a>

                                    <?php if($view_auth){ ?>
                                    <a href="<?php echo site_url(SYSTEM_URL."/Member/games_account/".$row["num"]."?per_page=".$nowpage.$att) ?>" class="btn btn-xs btn-warning" data-rel="tooltip" title="遊戲帳號">
                                        <i class="ace-icon fa fa-gamepad bigger-120"></i>
                                    </a>
                                    <?php } ?>
                                    <?php if($edit_auth){ ?>
                                    <a href="<?php echo site_url($editBTN.$row["num"]."?per_page=".$nowpage.$att)?>" class="btn btn-minier btn-default tooltip-success" data-rel="tooltip" title="修改">
                                        <span class="green">
                                            <i class="ace-icon fa fa-pencil bigger-120"></i>
                                        </span>
                                    </a>
                                    <?php } ?>      


                                    <?php if($view_auth){ ?>
                                    <a href="<?php echo site_url($viewBTN.$row["num"]."?per_page=".$nowpage.$att)?>" class="btn btn-xs" data-rel="tooltip"  title="瀏覽">
                                        <i class="ace-icon fa fa-search bigger-120"></i>
                                    </a>
                                    <?php } ?>    
                                                                     
                            </div>
                        </div>

                        
                    </div>
                </div>
                                                
           </td>                                      
        </tr>
        <?php endforeach;
        endif;
        ?>
    </tbody>
</table> 
<div  class="text-center">
	<a href="<?php echo site_url(SYSTEM_URL."/Member/excel".($att!="" ? "?p=1".$att : ""))?>" class="btn btn-minier btn-primary" target="_blank">匯出Excel</a>
</div>
<div class="text-center">
    查詢資料共有 <span class="text-danger"><?php echo $total_rows?></span> 筆 <br />
    <?php echo @$pagination ?>
    <!--<ul class="pagination">
        <li><a href="#"><i class="ace-icon fa fa-angle-double-left"></i></a></li>
        <li><a href="#">1</a></li>
        <li class="active"><a href="#">2</a></li>
        <li><a href="#"><i class="ace-icon fa fa-angle-double-right"></i></a></li>
    </ul>-->
</div>

<!--會員訊息 -->
<div class="modal fade  in" tabindex="-1" role="dialog" id="member_sms">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>-->
                <h4 class='smaller'><i class='ace-icon fa fa-envelope red'></i> 訊息發送</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger fade in" id="alrtMsg" style="display:none;">
                    <strong id="alrtTitle">請求失敗！</strong>&nbsp;&nbsp;<span id="alertBody">XXXX</span>
                </div>
                <form class="form-horizontal newoil-form" method="post">
                    <input type="hidden" class="DelVal" id="mem_num" name="mem_num" />
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">會員</label>
                        <div class="col-xs-12 col-sm-10">
                          <p class="form-control-static" id="show_id"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">類型</label>
                        <div class="col-xs-12 col-sm-10">
                            <select class="form-control  select2" name="sms_type" id="sms_type" data-placeholder="請選擇">
                                <!--<option value="">請選擇</option>-->
                                <!--<option value="1">訊息</option>-->
                                <option value="2">簡訊</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">標題</label>
                        <div class="col-xs-12 col-sm-10">
                            <input name="subject" id="subject" class="form-control DelVal" placeholder="標題"  />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">內容</label>
                        <div class="col-xs-12 col-sm-10">
                            <textarea id="sms_body" name="sms_body" class="form-control limited autosize-transition DelVal" maxlength="70" placeholder="內容"></textarea>
                        </div>
                    </div>
                                    
                </form>   
            </div>
            <div class="modal-footer center">
                <button type="button" id="smsBTN" class="btn btn-lg btn-white btn-pink btn-round"><i class="ace-icon fa fa-check bigger-110"></i>&nbsp;送出</button>
                <button data-dismiss="modal" type="button"  class=" btn-white btn-lg btn btn-default btn-round">
                    <i class='ace-icon fa fa-times bigger-110'></i>&nbsp; 關閉
                </button>
            </div>
       </div>
   </div>
</div>        
<!--會員訊息 -->
