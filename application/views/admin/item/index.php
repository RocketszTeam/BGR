<script type="text/JavaScript">
	$(function(){
		$('.activeSwitch').click(function(){
			$.ajax({
				type: "POST",
				url: "<?php echo site_url(SYSTEM_URL."/item/AjaxActive")?>",
				cache: false,
				data: { num: $(this).val(), active: ($(this).prop('checked') ? 'Y' : 'N') }
			});
		});
	});
</script>
<div class="page-header">
	<?php if(isset($item)){?>
		<a href="<?php echo site_url(SYSTEM_URL."/item/index/".(empty($item["root"]) ? "" : $item["root"])) ?>" class="btn btn-default">回上層</a>
	<?php }?>
	<a href="<?php echo site_url(SYSTEM_URL."/item/create/".(empty($item["num"]) ? "" : $item["num"]))?>" class="btn btn-primary">新增功能</a>
</div>


<?php if($openFind=='Y'):	//啟用搜尋才顯示?>
<div class="page-header">
    <form class="form-horizontal" method="post" action="<?php echo $s_action ?>">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="widget-box widget-color-blue2 collapsed">
                    <div class="widget-header">
                        <h4 class="widget-title lighter smaller">篩選條件</h4>
                        <div class="widget-toolbar">
                            <a href="#" data-action="collapse"><i class="ace-icon fa fa-chevron-down"></i></a>
                        </div>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main padding-8">
                        	
                            
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
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">狀態</label>
                                <div class="col-xs-12 col-sm-5">
                                  <select class="form-control selectpicker" name="find4" id="find4">
                            	  	<option value="">請選擇</option>
                                    <option value="Y" <?php if (@$_REQUEST["find4"]=="Y"){?> selected="selected" <?php } ?>>啟用</option>
                                    <option value="N" <?php if (@$_REQUEST["find4"]=="N"){?> selected="selected" <?php } ?>>停權</option>
                                  </select>
                                  <?php if (@$_REQUEST["find4"]=="Y"){$find_msg.=($find_msg!="" ? "、" : "")."狀態=【啟用】";} ?>
                                  <?php if (@$_REQUEST["find4"]=="N"){$find_msg.=($find_msg!="" ? "、" : "")."狀態=【停權】";} ?>
                                </div>
                             </div>     
                            <!--<div class="form-group">
                                <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">日期區間</label>
                                <div class="col-xs-12 col-sm-5">
                                    <div class="input-daterange jqdate input-group">
                                        <input type="text" class="input-sm form-control" name="start" />
                                        <span class="input-group-addon">
                                            <i class="fa fa-exchange"></i>
                                        </span>
    
                                        <input type="text" class="input-sm form-control" name="end"  />
                                    </div>
                                </div>
                             </div>-->     
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
            
            <th>選項名稱</th>
            <th>選項路徑</th>
            <?php if(!isset($item)):?>
            <th>子項目</th>
            <?php endif;?>         
            <th>顯示</th>
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
				<?php if($row["icon"]!=""):?>
                <i class="<?php echo $row["icon"]?>"></i>
                <?php endif;?>
				<?php echo $row["title"]?>
            </td>
            <td><?php echo ($row["url"]!=NULL ? $row["url"] : "-")?></td>
            <?php if(!isset($item)):?>
            <td>
            	<div class="btn-group">
                    <a href="<?php echo site_url(SYSTEM_URL."/item/index/".$row["num"]) ?>" class="btn btn-xs btn-success" title="查看詳細" data-rel="tooltip">
                        <i class="ace-icon fa fa-eye bigger-120"></i>
                    </a>
                </div>     
            </td>
            <?php endif;?> 
            <td>
            	<label>
                	<input name="switch-field-1" class="ace ace-switch ace-switch-5 activeSwitch" type="checkbox" value="<?php echo $row["num"]?>" <?php if ($row["isShow"]=="Y") echo ' checked' ?> />
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
