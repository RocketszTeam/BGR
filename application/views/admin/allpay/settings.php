<form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction ?>" enctype="multipart/form-data">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="widget-box widget-color-blue2">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller"><?php echo $subtitle?></h4>
                    
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-8">
       
                        <?php if(isset($result)):?>
                        <?php foreach($result as $row):?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right"><?php echo inNumberString($member_group,$row["m_group"])?></label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="hidden"  name="m_group[]" value="<?php echo $row["m_group"]?>"  />
                            	<input type="text" class="form-control digits" name="amount[]" value="<?php echo $row["amount"]?>" placeholder="金額設定" required />
                            </div>
                        </div>
                        <?php endforeach;?> 
                        <?php endif;?>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-sm-3 control-label no-padding-right"></label>
                            <div class="col-xs-12 col-sm-5">
                                <button type="submit" class="btn btn-primary">送出</button>
                            </div>    
                        </div> 
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

