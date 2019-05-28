<script src="<?php echo ASSETS_URL?>/admin/js/jquery.nestable.min.js"></script>
<script type="text/javascript">
	$(function(){
		countItem();
		$('.dd').nestable({maxDepth:0, group:0});
		$('.dd').nestable('collapseAll');
		$.validator.addMethod("checkGroup",function(value, element){
			var str=value;
			var result=false;
			$.ajax({
				type: "POST",
				url: CI_URL + "<?php echo SYSTEM_URL?>/Group/ajax_chk_u_power",
				cache: false,
				async:false,
				data: { u_power: str}
			}).done(function(msg){
				if(msg.trim()=='Y'){
					result=true;
				}else{
					result = false; 
				}
			});
			return result;
		},"此群組已經存在");	
	});
	function chkbox_set(num,obj){	//自動勾選上一層
		if($(obj).prop('checked')){
			$('.mainbox' + num).prop('checked',true);
		}
		var chile_len=$('.nodebox' + num + '[type="checkbox"]:checked').length;
		if(chile_len==0){
			$('.mainbox' + num).prop('checked',false);
		}
		countItem();
	}
	
	function chkbox_set_sub(num){	//自動勾選下一層
	
		if($('.mainbox' + num).prop('checked')){
			$('.nodebox' + num).prop('checked',true);
		}else{
			$('.nodebox' + num).prop('checked',false);
		}
		//當主選項被選取 展開下層選單 取消選取 收和選單
		var par=$('.mainbox' + num).parents('.dd-item');
		if($(par).hasClass('dd-collapsed')){
			$(par).find('[data-action="expand"]').trigger('click');
		}else{
			$(par).find('[data-action="collapse"]').trigger('click');
		}
		countItem();
	}
	function countItem(){
		var ckList='';
		$('.chkItem').each(function(){
			if($(this).prop('checked')){
				ckList+=(ckList!="" ? "," : "") + $(this).val();	
			}
		});	
		$('#power_list').prop('value',ckList);
		//console.log(ckList);
	}
</script>

<form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction?>">
	<input name="power_list" id="power_list" type="hidden"  />
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="widget-box widget-color-blue2">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller"><?php echo $subtitle?></h4>
                    
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-8">
                    	<div class="row">
                        	<div class="col-sm-2"></div>
                            <div class="col-sm-4">
                            	<div class="widget-box widget-color-green2">
                                	<div class="widget-header">
                                    	<h4 class="widget-title lighter smaller">權限設定</h4>
                                    </div>
                                    <div class="widget-body">
                                    	<div class="widget-main padding-8">
                                            <div class="dd">
                                                <?php
                                                if(isset($result) && $result["power_list"]!=""){
                                                    $power_list=explode(',',$result["power_list"]);	
                                                }
                                                ?>
                                            
                                                <ol class="dd-list dd-nodrag">
                                                <?php if(isset($ItemList) && count($ItemList) > 0):?>
                                                    <?php foreach($ItemList as $row):?>
                                                    <li class="dd-item item-orange dd-nodrag">
                                                        <div class="dd-handle dd-nodrag">
                                                            <label class="pos-rel">
                                                                <input type="checkbox" class="ace mainbox<?php echo $row["num"]?> chkItem" value="<?php echo $row["num"]?>" onClick="chkbox_set_sub('<?php echo $row["num"]?>')" <?php if(isset($power_list)){if(in_array($row["num"],$power_list)) echo ' checked';}?> />
                                                                <span class="lbl"></span>
                                                            </label>
                                                        
                                                            <?php echo $row["title"]?>
                                                            
                                                        </div>
                                                        <?php if(count($row["Nodes"]) > 0):?>
                                                        <ol class="dd-list dd-nodrag">
                                                        	<?php foreach($row["Nodes"] as $row2):?>
                                                            <li class="dd-item item-red dd-nodrag">
                                                                <div class="dd-handle dd-nodrag">
                                                                    <label class="pos-rel">
                                                                        <input type="checkbox" class="ace nodebox<?php echo $row["num"]?> chkItem" onClick="chkbox_set('<?php echo $row["num"]?>',this)" value="<?php echo $row2["num"]?>" <?php if(isset($power_list)){if(in_array($row2["num"],$power_list)) echo ' checked';}?> />
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                    <?php echo $row2["title"]?>
                                                                </div>
                                                            </li>
                                                            <?php endforeach;?>
                                                        </ol>   
                                                        <?php endif;?>                                                             
                                                    </li>
                                                    <?php endforeach;?>
                                                <?php endif;?>    
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                 
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="inputWarning" class="col-sm-3 control-label no-padding-right">群組代碼</label>
                                    <div class="col-xs-12 col-sm-5">
                                    	<?php if($todo=='add'):?>
                                        <input type="text" class="form-control checkGroup digits" min="1" id="u_power" name="u_power" value="<?php if(isset($result)) echo $result["u_power"]?>" placeholder="群組代碼" required />
                                        <span class="help-block">數字越小權限越大</span>
                                        <?php else:?>
                                        <p class="form-control-static"><?php echo $result["u_power"];?></p>
                                        <?php endif;?>
                                    </div>
                                </div>
                                <div class="form-group">
                                	<label for="inputWarning" class="col-sm-3 control-label no-padding-right">群組名稱</label>
                                    <div class="col-xs-12 col-sm-5">
                                    	<input type="text" class="form-control" id="power_name" name="power_name" value="<?php if(isset($result)) echo $result["power_name"]?>" placeholder="群組名稱" required="required">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="inputWarning" class="col-sm-3 control-label no-padding-right"></label>
                                    <div class="col-xs-12 col-sm-5">
                                        <button type="submit" class="btn btn-primary">送出</button>
                                        <a class='btn btn-danger' href='<?php echo $cancelBtn;?>' >取消</a>
                                    </div>    
                                </div> 
                            </div>
                        
                        </div>
                    
                    
                            
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>