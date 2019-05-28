<!-- PAGE CONTENT BEGINS -->


<div class="row">
    <div class="col-xs-12 col-sm-5">
    	<div class="form-group">
            <div class="btn-group">
                <button type="button" class="btn  btn-lg btn-info" onClick="location.href='<?php echo site_url(SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$att) ?>'">返回會員</button>
            </div>
        </div>
        <h4 class="inline text-danger">
			<?php echo tb_sql("u_name","member",$mem_num)?>【<?php echo tb_sql("u_id","member",$mem_num)?>】
        </h4>
    </div>
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
            <th>會員</th>
            <th>IP</th>
            <th>登入日期</th>
           
        </tr>
    </thead>
    <tbody>
		<?php 
        if(isset($result)):
            foreach($result as $row):
        ?>
        <tr>
            <td>
            	<?php echo tb_sql("u_id","member",$row["mem_num"])?>
                <div class="text-danger"><?php echo tb_sql("u_name","member",$row["mem_num"])?></div> 
            </td>
            <td><?php echo $row["login_ip"]?></td>
            
            <td>
                <div class="text-danger">
                	<?php echo $row["buildtime"]?>
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




