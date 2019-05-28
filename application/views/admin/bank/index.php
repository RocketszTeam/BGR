<script type="text/JavaScript">
	$(function(){
		$('.activeSwitch').click(function(){
			$.ajax({
				type: "POST",
				url: "<?php echo site_url(SYSTEM_URL."/Bank/keyChange")?>",
				cache: false,
				data: { num: $(this).val(), value: ($(this).prop('checked') ? 'Y' : 'N') }
			});
		});
		
	});
</script>
<!-- PAGE CONTENT BEGINS -->

<table id="simple-table" class="table table-bordered table-hover table-responsive">
    <thead>
        <tr>
            <!--<th class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" />
                    <span class="lbl"></span>
                </label>
            </th>-->
            <th>銀行名稱</th>
            <th>銀行帳號</th>
            <th>銀行帳號</th>
           
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
        	<td><?php echo tb_sql('bank_code','bank_list',$row["bank_num"]).tb_sql('bank_name','bank_list',$row["bank_num"])?></td>
            <td><?php echo $row["bank_account"]?></td>
            <td><?php echo $row["account_name"]?></td>
            <td>
            	<label>
                	<input name="switch-field-1" class="ace ace-switch  activeSwitch" type="checkbox" value="<?php echo $row["num"]?>" <?php if ($row["active"]=="Y") echo ' checked' ?> />
                    <span class="lbl"></span>
                </label>
                
            </td>
            <td>
                <div class="hidden-sm hidden-xs btn-group">

                    <a href="<?php echo site_url($editBTN.$row["num"])?>" class="btn btn-xs btn-info" data-rel="tooltip"  title="修改">
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




