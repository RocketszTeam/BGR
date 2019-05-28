<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>td { mso-number-format:"\@"; } </style>
</head>

<body>
            <!-- 表格 無搜尋功能 開始-->
            <table class="table table-striped table-bordered bootstrap-datatable responsive" width="100%" border="1">
                <thead>
                    <tr>
                        <!-- 表格 標題名稱 開始 -->
                        
                        <th width="8%">所屬代理</th>
                        <th width="11%">帳號</th>
                        <th width="9%">姓名</th>
                        <th width="9%">電話</th>
                        <th width="9%">註冊時間</th>
                        <th width="9%">登入時間</th>
                        <th width="10%">備註</th>
                        <!-- 表格 標題名稱 結束 -->
                    </tr>
                </thead>
                <tbody>
                    <!-- 表格 內容 開始-->
                    <?php 
					if(isset($result)):
						foreach($result as $row):
					?>
                    <tr>
                    	
                        <td>
						<?php 
						if(tb_sql("u_id","admin",$row["admin_num"])){
							echo tb_sql("u_id","admin",$row["admin_num"]);
							echo '('.tb_sql("u_name","admin",$row["admin_num"]).')';
						}
						?>
                        </td>
                        <td>
						<?php echo $row["u_id"]?>
                       
                        </td>
                        <td><?php echo $row["u_name"] ?></td>
						<td><?php echo $row["phone"] ?></td>
                        <td><?php echo $row["reg_time"] ?></td>
                        <td><?php echo $row["login_time"] ?></td>
						<td><?php echo $row["demo"] ?></td>
                        
                    </tr>
                    <?php
						endforeach;
					endif;
					?>
                    <!-- 表格 內容 結束-->
                </tbody>
            </table>

</body>
</html>