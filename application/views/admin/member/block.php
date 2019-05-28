
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
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">IP</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find1" name="find1" value="<?php echo @$_REQUEST["find1"]?>" placeholder="IP" />
                                      <?php if (@$_REQUEST["find1"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."IP=【".@$_REQUEST["find1"]."】";} ?>
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
<div class="form-group">
	<a href="<?php echo site_url($addBTN)?>" class="btn btn-primary">新增黑名單</a>
</div>
<table id="simple-table" class="table table-bordered table-hover table-responsive">
    <thead>
        <tr>
            <!--<th class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" />
                    <span class="lbl"></span>
                </label>
            </th>-->
            
            <th>登入IP</th>
            <th>管理</th>
        </tr>
    </thead>
    <tbody>
		<?php 
        if(isset($result)):
            foreach($result as $row):
        ?>
        <tr>
            <td><?php echo $row["block"] ?></td>
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


