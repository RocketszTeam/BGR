
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
                            
                            <div class="col-xs-12 col-sm-12">
                             <textarea class="ckeditor"  name="word"><?php echo htmlencode($word)?></textarea>
                              
                            </div>
                        </div>
                        <div class="form-group">
                           
                            <div class="col-xs-12 col-sm-12 center">
                                <button type="submit" class="btn btn-primary">送出</button>
                                
                            </div>    
                        </div> 
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

