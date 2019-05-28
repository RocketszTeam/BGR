<!--UI、日期元件-->
<script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/js/jqdate/ui/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="<?php echo ASSETS_URL?>/admin/js/jqdate/jqdate.js"></script>
<!--end-->

<div class="col-sm-12">
  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist" style="display:<?php echo (@$openFind!='Y' ? 'none' : '')?>">
    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">內容</a></li>
    <li role="presentation"><a href="#search" aria-controls="search" role="tab" data-toggle="tab">條件搜尋</a></li>
  </ul>


  <!-- Tab panes -->
  <div class="tab-content">
  
    <div role="tabpanel" class="tab-pane" id="search"><br>
    <form class="form-horizontal" method="post" action="<?php echo $s_action ?>">
        
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">訂單編號</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="find1" name="find1" value="<?php echo @$_REQUEST["find1"]?>" placeholder="訂單編號">
                <?php if (@$_REQUEST["find1"]!=""){$find_msg.="訂單編號=【".@$_REQUEST["find1"]."】";} ?>
            </div>
        </div>
        
        
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">收款情況</label>
            <div class="col-sm-8">
                <select class="form-control selectpicker" name="find2" id="find2" data-live-search="true">
                <option value="">請選擇</option>
                <?php echo inSelectOption2($orderKeyin2,@$_REQUEST["find2"])?>
                </select>
                
                <?php if (@$_REQUEST["find2"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."收款情況=【".returnKeyin2(@$_REQUEST["find2"])."】";} ?>
            </div>
        </div>

        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">繳費方式</label>
            <div class="col-sm-8">
                <select class="form-control selectpicker" name="find3" id="find3" data-live-search="true">
                <option value="">請選擇</option>
                <?php echo inSelectOption($paymentType,@$_REQUEST["find3"])?>
                </select>
                
                <?php if (@$_REQUEST["find3"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."繳費方式=【".inNumberString($paymentType,@$_REQUEST["find3"])."】";} ?>
            </div>
        </div>

         
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">會員帳號</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="find4" name="find4" value="<?php echo @$_REQUEST["find4"]?>" placeholder="會員帳號">
                <?php if (@$_REQUEST["find4"]!=""){$find_msg.="會員帳號=【".@$_REQUEST["find4"]."】";} ?>
            </div>
        </div>
        
        
        
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">日期區間</label>
            <div class="col-sm-8">
                <input class="form-control jqdate"  name="find8" type="text" value="<?php echo @$_REQUEST["find8"]?>" placeholder="開始日期" style="float:left; margin-right:30px;"/>
            	<input class="form-control jqdate"  name="find9" type="text" value="<?php echo @$_REQUEST["find9"]?>" placeholder="結束日期"  style="float:left;"/>   
     <?php if (@$_REQUEST["find8"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."發佈日期-起=【".@$_REQUEST["find8"]."】";} ?> 
     <?php if (@$_REQUEST["find9"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."發佈日期-訖=【".@$_REQUEST["find9"]."】";} ?>
            </div>
        </div> 
        
        <div class="form-group">
        <div class="col-sm-offset-2 col-sm-8">
        <a class='btn btn-default' href='<?php echo $_SERVER['PHP_SELF'] ?>' >查詢全部資料</a>
        <button type="submit" class="btn btn-primary">依上列條件搜尋</button>
               
        </div>
        </div>
                </form>
        </div>
  
    <div role="tabpanel" class="tab-pane active" id="home"><br>

			<?php
            if(!empty($find_msg)){
            ?>
            <div class="page-header">
            <h5>搜尋絛件：<?php echo $find_msg?></h5>
            </div>
            <?php
            }
            ?>
           

            <!-- 表格 無搜尋功能 開始-->
            <table class="table table-striped table-bordered bootstrap-datatable responsive" width="100%">
                <thead>
                    <tr>
                            <!-- 表格 標題名稱 開始 -->
                        
                        <th width="9%">訊息類型</th>
                        
                        <th width="15%">會員帳號</th>
                        <th width="24%">標題</th>
                        <th width="9%">已讀取</th>
                        <th width="13%">建立時間</th>
                        <th width="15%">更新時間</th>
                        <th width="15%">經手人</th>
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
                    	<td><?php echo tb_sql("kind","member_service_kind",$row["kind"])?></td>
                        
                        <td>                          
                            <div class="text-success"><?php echo tb_sql("u_id","member",$row["mem_num"])?></div>
                            <div class="text-danger"><?php echo tb_sql("u_name","member",$row["mem_num"])?></div>
						</td>
                        <td><?php echo $row["subject"]?></td>
                        <td><div class="text-<?php echo ($row['is_read']==1 ? 'success' : 'danger')?>">
						<?php echo ($row['is_read']==1 ? '是' : '否')?>
                        </div>
                        </td>
                        <td><?php echo $row["buildtime"]?></td>
                        <td><?php echo $row["updatetime"]?></td>
                        <td >
                        	<?php 
							if($row["admin_num"]!="" && $web_root_num > 0){
								echo tb_sql("u_id","admin",$row["admin_num"]).'<br>('.tb_sql("u_name","admin",$row["admin_num"]).')';
							}elseif($row["admin_num"]!="" && $web_root_num==0){
								echo '<span class="text-danger">[系統]</span>';	
							}
								 
							?>
                        </td>
                        
                    </tr>
                    <?php
						endforeach;
					endif;
					?>
                    <!-- 表格 內容 結束-->
                </tbody>
            </table>
       
            
            <div class="text-right">
                查詢資料共有 <?php echo (isset($total_rows) ? $total_rows : 0) ?> 筆<br>        
            
                <?php echo $pagination ?>
            </div>	
            
            
    </div>
    
  </div>
</div>



