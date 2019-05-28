<script type="text/javascript">	
	//商品下拉分類
	function ajaxKind(nation,defaultValue){			
		$.ajax({
			type: "POST",
			url: CI_URL + "module/kind/ajax_kind",
			cache: false,
			data: { 
					ojbID: "kind",        		//下拉控制項的id
					table: "member_service_kind",   		//資料表名稱
					pleaseSelect: true,  		//是否需要有「請選擇」
					required:true,				//是否需要為判斷必輸入值
					nation: nation,				//語系			
					hasSearch:true,				//下拉關鍵字查詢	 		
					defValue: defaultValue		//預設要被選取的value值
				  }
		}).done(function( htmlData ) { 
			$('#kindDiv').html(htmlData);
			$('.selectpicker').selectpicker('refresh');//重新特效抓取
			$.get('<?php echo ASSETS_URL?>/admin/js/selectpicker-event.js');
		});
	}
</script>

<div class="col-sm-12">
    <form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction?>">   
    <fieldset class="scheduler-border">
        <legend class="scheduler-border"><h2><i class=" glyphicon glyphicon-plus-sign"></i><?php echo $subtitle?></h2></legend>
        
        

      
        
        
       
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">類型</label>
            <div class="col-sm-8">
                <span id="kindDiv"></span>
                <script type="text/javascript">
                    $(window).ready(function() {	
                        ajaxKind('TW','');
                    });
                </script>
            </div>         
        </div>

        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">會員帳號</label>
            <div class="col-sm-8">
            	<select class="form-control selectpicker" name="mem_num" id="mem_num" data-live-search="true" data-show-subtext="true" data-size="10" required>
                	<option value="">請選擇</option>
                    <?php if(isset($memberList)):?>
                    	<?php foreach($memberList as $row):?>
                        <optgroup label="<?php echo $row["u_id"]?>(<?php echo $row["u_name"]?>)">
                        	<?php if(count($row["member_data"]) > 0):?>
                            	<?php foreach($row["member_data"] as $row2):?>
                            	<option value="<?php echo $row2["num"]?>" data-subtext="<?php echo $row2["u_name"]?>"><?php echo $row2["u_id"]?></option>
                                <?php endforeach;?>
                            <?php endif;?>
                        </optgroup> 
                        <?php endforeach;?>   
                    <?php endif;?>
                </select>
            </div>
        </div>

        
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">標題</label>
            <div class="col-sm-8">
            	<input type="text" name="subject" id="subject" class="form-control" placeholder="標題" required="required" />
            </div>
        </div>
        
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">內容</label>
            <div class="col-sm-8">
            	<textarea name="word"  class="form-control" placeholder="內容" rows="5" required></textarea>
            </div>
        </div>        
       
        

        
        
        <div class="form-group ">
            <div class="col-sm-offset-2 col-sm-8">
                <button type="submit" class="btn btn-primary">新增</button>
                
               <!-- <a class='btn btn-danger' href='<?php echo site_url(SYSTEM_URL.'/Message/index');?>' >返回</a>-->
            </div>
        </div>
    </fieldset>   
    </form>
</div>