<script type="text/javascript">
	function keyChange(number,value){
		$.ajax({
			type: "POST",
			url: CI_URL + '<?php echo SYSTEM_URL?>/Rake/keyChange',
			cache: false,
			data: { 
					order_no: number,
					value: value
				  }
		}).done(function( msg ) {  			
			$('#tr' + number).remove();
		});	
	}
	
	function AllChange(value){
		$.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/admin/images/loadingbar/009.gif" >',baseZ: 2000});
		$.ajax({
			type: "POST",
			url: CI_URL + '<?php echo SYSTEM_URL?>/Rake/AllChange',
			cache: false,
			data:{value: value}
		}).done(function( msg ) {  			
			$.unblockUI();
			$('#simple-table tbody tr').remove();
			modalMsg('處理完畢');
		});	
	}
	
</script>


<!-- PAGE CONTENT BEGINS -->
<?php if($openFind=='Y'):	//啟用搜尋才顯示?>
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
                                <div class="form-group" style="display:none">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">訂單編號</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find1" name="find1" value="<?php echo @$_REQUEST["find1"]?>" placeholder="訂單編號" />
                                      <?php if (@$_REQUEST["find1"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."訂單編號=【".@$_REQUEST["find1"]."】";} ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">會員帳號</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find4" name="find4" value="<?php echo @$_REQUEST["find4"]?>" placeholder="會員帳號" />
                                      <?php if (@$_REQUEST["find4"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."會員帳號=【".@$_REQUEST["find4"]."】";} ?>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">日期區間</label>
                                    <div class="col-xs-12 col-sm-5">
                                        <div class="input-daterange  input-group">
                                            <input type="text" class="input-sm form-control jqdate" name="find7" id="find7" value="<?php echo @$_REQUEST["find7"]?>" />
                                            <span class="input-group-addon">
                                                <i class="fa fa-exchange"></i>
                                            </span>
                                            <input type="text" class="input-sm form-control jqdate" name="find8" id="find8" value="<?php echo @$_REQUEST["find8"]?>"  />
                                            <?php if (@$_REQUEST["find7"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."日期區間-起=【".@$_REQUEST["find7"]."】";} ?> 
                                            <?php if (@$_REQUEST["find8"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."日期區間-訖=【".@$_REQUEST["find8"]."】";} ?> 
                                        </div>
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
    if(@$find_msg!=""){	//列出搜尋條件
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
<div class="tabbable">
    <ul class="nav nav-tabs" id="myTab">
        <li class="<?php echo (@$keyin1=='0' ? ' active' : '')?>">
            <a href="<?php echo site_url(SYSTEM_URL."/Rake/index/0")?>">
                等待處理
            </a>
        </li>
        <li class="<?php echo (@$keyin1=='1' ? ' active' : '')?>">
            <a  href="<?php echo site_url(SYSTEM_URL."/Rake/index/1")?>">
                處理完畢
            </a>
        </li>
        <li class="<?php echo (@$keyin1=='2' ? ' active' : '')?>">
            <a  href="<?php echo site_url(SYSTEM_URL."/Rake/index/2")?>">
                拒絕發放
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="home" class="tab-pane fade in active">
        	<?php if(@$keyin1==0):?>
            <div class="row">
                <div class="col-xs-12 col-sm-5">
                    <div class="form-group">
                        <div class="btn-group">
                            <button type="button" class="btn  btn-info" onClick="AllChange(1)">一鍵發放</button>
							<button type="button" class="btn  btn-danger" onClick="AllChange(2)">一鍵拒絕</button>
                        </div>
                    </div>
                    
                </div>
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
                        <th>日期</th>
                        <th>會員</th>
                        <th>點數</th>
                        <?php if(@$keyin1=='0'):?>
                        <th>處理情況</th>
                        <?php else:?>
                        <th>處理人員</th>
                        <th>異動時間</th>
                        <?php endif;?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(isset($result)):
                        foreach($result as $row):
                    ?>
                    <tr id="tr<?php echo $row["num"]?>">
                        <td>
                            <?php echo tb_sql("u_id","admin",$row["admin_num"])?>
                            <div class="text-danger"><?php echo tb_sql("u_name","admin",$row["admin_num"])?></div> 
                        </td>
                        <td>
                            <div class="text-danger"><?php echo date('Y-m-d',strtotime($row["buildtime"]))?></div>
                        </td>
                        <td>
                            <?php echo $row["u_id"]?>
                            <div class="text-danger"><?php echo tb_sql("u_name","member",$row["mem_num"])?></div>
                        </td>
                        <td>
                        <?php echo number_format($row["rake"],0)?>
                        </td>
                        <?php if(@$keyin1=='0'):?>
                        <td>
                        <?php if(@$keyin1=='0' && !in_array($web_root_u_power,array(4,5,6))):?>
                       		<select class="select2" onchange="keyChange('<?php echo $row["num"]?>',this.value)" data-allow-clear="false"> 
                       		<?php echo inSelectOption($rakeKeyin1,$row["keyin1"])?>
                            </select>
                        <?php else:?>
                        	<?php echo inNumberString($rakeKeyin1,$row["keyin1"])?>
                        <?php endif;?>
                        </td>

                        <?php else:?>
                        <td>
                        	<?php 
							if($row["update_admin"]!="" && $row["update_admin"] > 0){
								echo tb_sql("u_id","admin",$row["update_admin"]).'<br>('.tb_sql("u_name","admin",$row["update_admin"]).')';
							}elseif($row["update_admin"]!="" && $row["update_admin"]==0){
								echo '<span class="text-danger">[系統]</span>';	
							}
							?>
                        </td>
                        <td><div class="text-danger"><?php echo $row["updatetime"]?></div></td>
                        <?php endif;?>
                                                     
                    </tr>
                    <?php endforeach;
                    endif;
                    ?>
                </tbody>
            </table> 
            
            <div class="text-center">總共有：<?php echo $total_rows?>筆</div>
            <div class="text-center">
                <?php echo @$pagination ?>
                <!--<ul class="pagination">
                    <li><a href="#"><i class="ace-icon fa fa-angle-double-left"></i></a></li>
                    <li><a href="#">1</a></li>
                    <li class="active"><a href="#">2</a></li>
                    <li><a href="#"><i class="ace-icon fa fa-angle-double-right"></i></a></li>
                </ul>-->
            </div>
            
        </div>

    </div>
</div>


<div class="modal fade in" tabindex="-1" role="dialog" id="report_info">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header center h4 red" id="report_info_header">
                
            </div>
            <div class="modal-body">
            	<div class="row">
                	<input type="hidden" id="mem_num" />
                    <div class="col-xs-12 col-sm-8">
                        <div class="form-group">
                            <div class="input-daterange  input-group">
                                <input type="text" class="input-sm form-control jqdatetime" name="sDate" id="sDate"  placeholder="開始日期"  />
                                <span class="input-group-addon">
                                    <i class="fa fa-exchange"></i>
                                </span>
                                <input type="text" class="input-sm form-control jqdatetime" name="eDate" id="eDate"  placeholder="結束日期"   />
                            </div>
                        </div>
                	</div>
                    <div class="col-xs-12 col-sm-3">
                    	<div class="form-group">
                        	<div class="btn-group">
                            	<button type="button" class="btn btn-white btn-sm btn-primary" onClick="upDateReport()">更新日期</button>
                            </div>
                        </div>
                    </div>
                </div>
            
                
                <table class="table table-bordered table-hover">
                    <tr>
                        <th class="text-center">廠商</th>
                        <th class="text-center">投注</th>
                        <th class="text-center">洗碼</th>
                        <th class="text-center">輸贏</th>
                    </tr>
                    <tbody id="report_body">
                    
                    </tbody>
                    
                    <tr>
                    	<td class="text-center red">總計</td>
                        <td class="text-center formatNumber" id="total_betAmount"></td>
                        <td class="text-center formatNumber" id="total_validAmount"></td>
                        <td class="text-center number-info" id="total_winOrLoss"></td>
                    </tr>    
                </table>
                	       
            </div>
            <div class="modal-footer center">
                <button data-dismiss="modal" type="button"  class=" btn-white btn-lg btn btn-default btn-round">
                    <i class='ace-icon fa fa-times bigger-110'></i>&nbsp; 關閉
                </button>
            </div>
       </div>
    </div>
</div>



