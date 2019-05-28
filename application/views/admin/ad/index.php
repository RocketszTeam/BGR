<script type="text/javascript">
	$(function(){
		$('.activeSwitch').click(function(){
			$.ajax({
				type: "POST",
				url: "<?php echo site_url(SYSTEM_URL."/Ad/keyChange")?>",
				cache: false,
				data: { num: $(this).val(), value: ($(this).prop('checked') ? 'Y' : 'N') }
			});
		});
	});
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
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">顯示位置</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <select class="form-control select2" name="find2" id="find2" data-placeholder="請選擇">
                                        <option value="">請選擇</option>
                                        <?php if(isset($row_group)):?>
                                        <?php foreach($row_group as $row):?>
                                        <option value="<?php echo $row["num"]?>" <?php if($row["num"]==@$_REQUEST["find2"]) echo ' selected'?>><?php echo $row["kind"]?></option>
                                        <?php endforeach;?>
                                        <?php endif;?>		
                                      </select>
                                      <?php if (@$_REQUEST["find1"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."顯示位置=【".tb_sql("kind","banner_kind",@$_REQUEST["find2"])."】";} ?>
                                    </div>
                                 </div>
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">標題</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find3" name="find3" value="<?php echo @$_REQUEST["find3"]?>" placeholder="標題" />
                                      <?php if (@$_REQUEST["find2"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."標題=【".@$_REQUEST["find3"]."】";} ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">顯示</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <select class="form-control select2" name="find4" id="find4" data-placeholder="請選擇">
                                        <option value="">請選擇</option>
                                        <option value="Y" <?php if (@$_REQUEST["find4"]=="Y"){?> selected="selected" <?php } ?>>顯示</option>
                                        <option value="N" <?php if (@$_REQUEST["find4"]=="N"){?> selected="selected" <?php } ?>>關閉</option>
                                      </select>
                                      <?php if (@$_REQUEST["find4"]=="Y"){$find_msg.=($find_msg!="" ? "、" : "")."狀態=【啟用】";} ?>
                                      <?php if (@$_REQUEST["find4"]=="N"){$find_msg.=($find_msg!="" ? "、" : "")."狀態=【停權】";} ?>
                                    </div>
                                 </div>
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">日期區間</label>
                                    <div class="col-xs-12 col-sm-5">
                                        <div class="input-daterange  input-group">
                                            <input type="text" class="input-sm form-control jqdate" name="find5" id="find5" value="<?php echo @$_REQUEST["find5"]?>" />
                                            <span class="input-group-addon">
                                                <i class="fa fa-exchange"></i>
                                            </span>
                                            <input type="text" class="input-sm form-control jqdate" name="find6" id="find6" value="<?php echo @$_REQUEST["find6"]?>"  />
                                            <?php if (@$_REQUEST["find7"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."日期區間-起=【".@$_REQUEST["find5"]."】";} ?> 
                                            <?php if (@$_REQUEST["find8"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."日期區間-訖=【".@$_REQUEST["find6"]."】";} ?> 
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

<table id="simple-table" class="table table-bordered table-hover table-responsive">
    <thead>
        <tr>
            <!--<th class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" />
                    <span class="lbl"></span>
                </label>
            </th>-->
            
            <th>顯示位置</th>
            <th>標題</th>
            <th>開放時間</th>
            <th>發布日期</th>
            <th>圖片上傳</th>
            <th>是否顯示</th>
            <th>管理</th>
        </tr>
    </thead>
    <tbody>
		<?php 
        if(isset($result)):
            foreach($result as $row):
        ?>
        <tr>
            <td><?php echo tb_sql("kind","banner_kind",$row["kind"])?></td>
            <td><?php echo $row["subject"]?></td>
            <td>
            	<?php if($row["selltime1"]!=NULL):?>
            	<div class="text-info"><?php echo $row["selltime1"]?></div>
                <?php endif;?>
            	<?php if($row["selltime2"]!=NULL):?>
            	<div class="text-info"><?php echo $row["selltime2"]?></div>
                <?php endif;?>
            </td>
            <td><?php echo $row["buildtime"]?></td>
            <td>
            	<?php if($row["pic"]!=''):?>
            	<a href="<?php echo UPLOADS_URL.'/banner/'.$row["pic"]?>" data-rel="colorbox" class="btn btn-xs btn-info"  title="<?php echo $row["subject"]?>">
                	<i class="ace-icon fa fa-search icon-on-right bigger-110"></i>
                </a>
                <?php else:?>
                -
                <?php endif?>
            </td>
            <td>
            	<label>
                	<input name="switch-field-1" class="ace ace-switch  activeSwitch" type="checkbox" value="<?php echo $row["num"]?>" <?php if ($row["view"]=="Y") echo ' checked' ?> />
                    <span class="lbl"></span>
                </label>
                
            </td>
            <td>
            	
                <div class="hidden-sm hidden-xs btn-group">
                    <a href="<?php echo site_url($editBTN.$row["num"])?>" class="btn btn-xs btn-info" title="修改">
                        <i class="ace-icon fa fa-pencil bigger-120"></i>
                    </a>
                    
                    <button class="btn btn-xs btn-danger" data-toggle="modal" title="刪除" data-target="#dialog-confirm" data-action="<?php echo site_url($delBTN.$row["num"])?>">
                        <i class="ace-icon fa fa-trash-o bigger-120"></i>
                    </button>
                    
                </div>
                <div class="hidden-md hidden-lg">
                    <div class="inline pos-rel">
                        <button class="btn btn-minier btn-primary dropdown-toggle" data-toggle="dropdown" data-position="auto">
                            <i class="ace-icon fa fa-cog icon-only bigger-110"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close">
                            <li>
                                <a href="<?php echo site_url($editBTN.$row["num"]) ?>" class="tooltip-success" data-rel="tooltip" title="修改">
                                    <span class="green">
                                        <i class="ace-icon fa fa-pencil bigger-120"></i>
                                    </span>
                                </a>
                            </li>
							
                            <li>
                            	<a href="#" class="tooltip-error" data-rel="tooltip" data-toggle="modal" title="刪除" data-target="#dialog-confirm" data-action="<?php echo site_url($delBTN.$row["num"])?>">
                                    <span class="red">
                                        <i class="ace-icon fa fa-trash-o bigger-120"></i>
                                    </span>
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </div>
                                                          
           </td>                                      
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


