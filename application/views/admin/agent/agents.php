
<table id="simple-table" class="table table-bordered table-hover table-responsive">
    <thead>
        <tr>
            <!--<th class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" />
                    <span class="lbl"></span>
                </label>
            </th>-->
            <th>申請日期</th>
            <th>來源網站</th>
            <th>聯絡姓名</th>
            <th>聯絡電話</th>
            <th>申請留言</th>
            <th>管理</th>
        </tr>
    </thead>
    <tbody>
		<?php 
        if(isset($result)):
            foreach($result as $row):
        ?>
        <tr>
        	<td><?php echo $row["buildtime"]?></td>
            <td><?php echo tb_sql("u_id","admin",$row["admin_num"])?><br><?php echo tb_sql("u_name","admin",$row["admin_num"])?></td>
            <td><?php echo $row["u_name"]?></td>
            <td>
				<?php echo $row["phone"] ?>
				<?php if($row["other_type"]!=""):?>
                <div>
                其他聯絡方式：<?php echo $row["other_type"]?>，<?php echo $row["other_text"]?>
                </div>
                <?php endif;?>
            </td>
            <td>
            	<a class="btn btn-xs btn-success" href="javascript:void(0)" data-msg-title="申請留言" data-msg-content="<?php echo $row["word"]?>" data-rel="tooltip" title="查看詳細" data-toggle="info">
                	<i class="ace-icon fa fa-eye bigger-120"></i>
                </a>
            </td>
            <td>
            	
                <div class="hidden-sm hidden-xs btn-group">
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
