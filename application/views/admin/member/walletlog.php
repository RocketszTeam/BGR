<!-- PAGE CONTENT BEGINS -->
<?php if ($openFind == 'Y'):    //啟用搜尋才顯示?>
	<div class="page-header">
		<form class="form-horizontal" method="get" action="<?php echo $s_action ?>">
			<div class="row">
				<div class="col-xs-12 col-sm-12">
					<div id="accordion" class="accordion-style1 panel-group">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title ">
									<a class="accordion-toggle collapsed" data-toggle="collapse"
									   data-parent="#accordion" href="#collapseOne">
										<i class="ace-icon fa fa-angle-down bigger-110"
										   data-icon-hide="ace-icon fa fa-angle-down"
										   data-icon-show="ace-icon fa fa-angle-right"></i>
										&nbsp;篩選條件
									</a>
								</h4>
							</div>
							<div class="panel-collapse collapse" id="collapseOne">
								<div class="panel-body">


									<div class="form-group">
										<label for="inputWarning"
										       class="col-xs-12 col-sm-3 control-label no-padding-right">狀態</label>

										<div class="col-xs-12 col-sm-5">
											<select class="form-control select2" name="find3" id="find3"
											        data-placeholder="請選擇">
												<option value="">請選擇</option>
												<option
													value="1" <?php if (@$_REQUEST[ "find3" ] == '1') echo ' selected' ?>>
													成功
												</option>
												<option
													value="0" <?php if (@$_REQUEST[ "find3" ] == '0') echo ' selected' ?>>
													失敗
												</option>

											</select>
											<?php if (@$_REQUEST[ "find3" ] != "") {
												$find_msg .= ( $find_msg != "" ? "、" : "" ) . "狀態=【" . ( @$_REQUEST[ "find3" ] == 0 ? '失敗' : '成功' ) . "】";
											} ?>
										</div>
									</div>

									<div class="form-group">
										<label for="inputWarning"
										       class="col-xs-12 col-sm-3 control-label no-padding-right">帳號</label>

										<div class="col-xs-12 col-sm-5">
											<input type="text" class="form-control" id="find4" name="find4"
											       value="<?php echo @$_REQUEST[ "find4" ] ?>" placeholder="帳號"/>
											<?php if (@$_REQUEST[ "find4" ] != "") {
												$find_msg .= ( $find_msg != "" ? "、" : "" ) . "帳號=【" . @$_REQUEST[ "find4" ] . "】";
											} ?>
										</div>
									</div>
									<div class="form-group">
										<label for="inputWarning"
										       class="col-xs-12 col-sm-3 control-label no-padding-right">日期</label>

										<div class="col-xs-12 col-sm-5">
											<div class="input-daterange  input-group">
												<input type="text" class="input-sm form-control jqdate" name="find7"
												       id="find7" value="<?php echo @$_REQUEST[ "find7" ] ?>"/>
                                            <span class="input-group-addon">
                                                <i class="fa fa-exchange"></i>
                                            </span>
												<input type="text" class="input-sm form-control jqdate" name="find8"
												       id="find8" value="<?php echo @$_REQUEST[ "find8" ] ?>"/>
												<?php if (@$_REQUEST[ "find7" ] != "") {
													$find_msg .= ( $find_msg != "" ? "、" : "" ) . "日期-起=【" . @$_REQUEST[ "find7" ] . "】";
												} ?>
												<?php if (@$_REQUEST[ "find8" ] != "") {
													$find_msg .= ( $find_msg != "" ? "、" : "" ) . "日期-訖=【" . @$_REQUEST[ "find8" ] . "】";
												} ?>
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
		if (@$find_msg != "") {    //列出搜尋條件
			$find_arr = explode('、', $find_msg);
			?>
			<div class="widget-box widget-color-green">
				<div class="widget-body">
					<div class="widget-main padding-8">
						<ul class="list-unstyled spaced">
							<?php foreach ($find_arr as $find_str): ?>
								<li><i class="ace-icon fa fa-search bigger-110 red"></i><?php echo $find_str ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

			<?php
		}
		?>
	</div>

<?php endif; ?>


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
		<th>來源</th>
		<th>目的</th>
		<th>點數</th>
		<th>狀態</th>
		<th>備註</th>
		<th>操作日期/更新日期</th>

	</tr>
	</thead>
	<tbody>
	<?php
	if (isset( $result )):
		foreach ($result as $row):
			?>
			<tr>
				<td>
					<a href="<?php echo site_url(SYSTEM_URL . "/Member/index?find2=" . tb_sql("u_id", "member", $row[ "mem_num" ])) ?>"><?php echo tb_sql("u_id", "member", $row[ "mem_num" ]) ?></a>

					<div class="text-danger"><?php echo tb_sql("u_name", "member", $row[ "mem_num" ]) ?></div>
				</td>
				<td class="purple">
					<?php echo( $row[ "source" ] == '0' ? '電子錢包' : tb_sql('makers_name', 'game_makers', $row[ 'source' ]) ) ?>
				</td>
				<td class="pink">
					<?php echo( $row[ "target" ] == '0' ? '電子錢包' : tb_sql('makers_name', 'game_makers', $row[ 'target' ]) ) ?>
				</td>
				<td class="text-danger"><?php echo number_format($row[ 'points' ], 2) ?></td>
				<td><span
						class="<?php echo $row[ 'status' ] == 0 ? 'red' : 'green' ?>"><?php echo $row[ 'status' ] == 0 ? '失敗' : '成功' ?></span>
				</td>
				<td>
					<?php if ($row[ 'word' ]) echo br($row[ 'word' ]); else echo '-'; ?>
				</td>

				<td>

					<div class="text-danger">
						<?php echo $row[ "buildtime" ] ?>
					</div>
					<div class="text-info">
						<?php echo $row[ "upTime" ] ?>
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



