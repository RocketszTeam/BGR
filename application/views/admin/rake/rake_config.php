<script type="text/javascript">
	$(function(){
		$('#m_group').change(function(){
			console.log(CI_URL + '<?php echo $changeUrl?>' + $(this).val());
			location.href=CI_URL + '<?php echo $changeUrl?>' + $(this).val();	
		});
	});
</script>

<form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction ?>">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="widget-box widget-color-blue2">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller"><?php echo $subtitle?></h4>
                    
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-8">
                    	
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">會員群組</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="m_group" id="m_group" data-placeholder="請選擇" required>
                                <?php echo inSelectOption($member_group,$row['m_group'])?>
                              </select>
                              
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">真人返水</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control number" id="live_rake" name="live_rake" value="<?php if(isset($row)) echo $row["live_rake"]?>"  placeholder="真人返水" required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">體育返水</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control number" id="sport_rake" name="sport_rake" value="<?php if(isset($row)) echo $row["sport_rake"]?>"  placeholder="體育返水" required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">電子返水</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control number" id="egame_rake" name="egame_rake" value="<?php if(isset($row)) echo $row["egame_rake"]?>"  placeholder="電子返水" required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">彩票返水</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control number" id="lottery_rake" name="lottery_rake" value="<?php if(isset($row)) echo $row["lottery_rake"]?>"  placeholder="彩票返水" required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">電競返水</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control number" id="esport_rake" name="esport_rake" value="<?php if(isset($row)) echo $row["esport_rake"]?>"  placeholder="電競返水" required="required" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-sm-3 control-label no-padding-right"></label>
                            <div class="col-xs-12 col-sm-5">
                                <button type="submit" class="btn btn-primary">更新</button>
                                
                            </div>    
                        </div> 
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

