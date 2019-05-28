<script type="text/javascript">
	function keyChange(number,value){
		$.ajax({
			type: "POST",
			url: CI_URL + '<?php echo SYSTEM_URL?>/order/bank_keyChange',
			cache: false,
			data: { 
					order_no: number,
					value: value
				  }
		}).done(function( msg ) {  			
			$('#tr' + number).remove();
		});	
	}
	
	function toCloseSound(tb,column,value){
		$.ajax({
			type: "POST",
			url: CI_URL + '<?php echo SYSTEM_URL?>/Order/ajaxCloseSound',
			cache: false,
			data: { 
					tb: tb,
					column: column,
					value: value
				  }
		}).done(function( msg ) {  			
			$('#soundBTN').hide();
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
                                <div class="form-group">
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
                                            <input type="text" class="input-sm form-control jqdatetime" name="find7" id="find7" value="<?php echo @$_REQUEST["find7"]?>" />
                                            <span class="input-group-addon">
                                                <i class="fa fa-exchange"></i>
                                            </span>
                                            <input type="text" class="input-sm form-control jqdatetime" name="find8" id="find8" value="<?php echo @$_REQUEST["find8"]?>"  />
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
            <a href="<?php echo site_url(SYSTEM_URL."/Order/bank/0")?>">
                未收款
            </a>
        </li>
        <li class="<?php echo (@$keyin1=='1' ? ' active' : '')?>">
            <a  href="<?php echo site_url(SYSTEM_URL."/Order/bank/1")?>">
                已收款
            </a>
        </li>
        <li class="<?php echo (@$keyin1=='2' ? ' active' : '')?>">
            <a  href="<?php echo site_url(SYSTEM_URL."/Order/bank/2")?>">
                已放棄
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="home" class="tab-pane fade in active">
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
                        <th>訂單編號/日期</th>
                        <th>匯款會員</th>
                        <th>匯款金額</th>
                        <th>公司帳戶</th>
                        <?php if(@$keyin1=='0'):?>
                        <th>收款情況</th>
                        <th>管理</th>
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
                    <tr id="tr<?php echo $row["order_no"]?>">
                        <td>
                            <?php echo tb_sql("u_id","admin",$row["admin_num"])?>
                            <div class="text-danger"><?php echo tb_sql("u_name","admin",$row["admin_num"])?></div> 
                        </td>
                        <td>
                            <div class="text-info"><?php echo $row["order_no"]?></div>
                            <div class="text-danger"><?php echo $row["buildtime"]?></div>
                            
                        </td>
                        <td>
                            <a href="<?php echo  site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>">
                                <?php echo tb_sql("u_id","member",$row["mem_num"])?>
                            </a>    
                            <div class="text-danger"><?php echo tb_sql("u_name","member",$row["mem_num"])?></div>
                        </td>
                        <td>
                        <?php echo number_format($row["amount"],0)?>
                        </td>
                        <td>
                        	<div class="text-success"><?php echo $row["bank_name"]?></div>
                            <div class="text-success">帳號：<?php echo $row["bank_account"]?></div>
                            <div class="text-success">戶名：<?php echo $row["account_name"]?></div>
                        </td>
                        <?php if(@$keyin1=='0'):?>
                        <td>
                        <?php if(@$keyin1=='0' && !in_array($web_root_u_power,array(4,5,6))):?>
                       		<select class="select2" onchange="keyChange('<?php echo $row["order_no"]?>',this.value)" data-allow-clear="false"> 
                       		<?php echo inSelectOption($bankKeyin2,$row["keyin2"])?>
                            </select>
                        <?php else:?>
                        	<?php echo inNumberString($bankKeyin2,$row["keyin2"])?>
                        <?php endif;?>
                        </td>
                        <td>
                        	<div class="btn-group">
                                <?php if($row["keyin2"]==0 && $row["no_sound"]==0 && !in_array($web_root_u_power,array(4,5,6))):?>
                                <button type="button" class="btn btn-danger" id="soundBTN" onClick="toCloseSound('member_bank_transfer','order_no','<?php echo $row["order_no"]?>')">靜音</button>
                                <?php endif;?>
                            </div>
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




