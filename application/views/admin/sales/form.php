<form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction ?>" enctype="multipart/form-data">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="widget-box widget-color-blue2">
                <div class="widget-header">
                    <h4 class="widget-title lighter smaller"><?php echo $subtitle?></h4>
                    
                </div>
                <div class="widget-body">
                    <div class="widget-main padding-8">
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">分類</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="kind" id="kind" data-placeholder="請選擇" required>
                                <?php if(isset($row_group)):?>
                                <?php foreach($row_group as $rowg):?>
                                <option value="<?php echo $rowg["num"]?>" <?php if(isset($row) && $rowg["num"]==@$row["kind"]) echo ' selected'?>><?php echo $rowg["kind"]?></option> 
                                <?php endforeach;?>
                                <?php endif;?>		
                              </select>
                              
                            </div>
                         </div>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">主題</label>
                            <div class="col-xs-12 col-sm-5">
                            	<input type="text" class="form-control" id="subject" name="subject" value="<?php if(isset($row)) echo $row["subject"]?>" placeholder="主題" required />
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">發布日期</label>
                            <div class="col-xs-12 col-sm-1">
                            	<input type="text" class="form-control jqdate" id="buildtime" name="buildtime" value="<?php echo (@$row["buildtime"]!="" ? $row["buildtime"] : date('Y-m-d'))?>" placeholder="發布日期" required />
                               
                            </div>
                        </div> 
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">開放時間</label>
                            <div class="col-xs-12 col-sm-4">
                             	<div class="input-daterange  input-group">
                                    <input type="text" class="form-control jqdate" name="selltime1" id="selltime1" value="<?php if(isset($row)) echo $row["selltime1"]?>" placeholder="開始日期"  />
                                    <span class="input-group-addon">
                                        <i class="fa fa-exchange"></i>
                                    </span>
                                    <input type="text" class="form-control jqdate" name="selltime2" id="selltime2" value="<?php if(isset($row)) echo $row["selltime2"]?>" placeholder="結束日期"   />
                                </div>
                            
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">內容</label>
                            <div class="col-xs-12 col-sm-6">
                            	<textarea class="ckeditor" id="word"  name="word"><?php if(isset($row)) echo htmlencode($row["word"])?></textarea>
                            </div>
                        </div> 
                       
                        <?php for($i=0;$i<$picMax;$i++):?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">圖片</label>
                            <div class="col-xs-12 col-sm-6">
                            	<input class="form-control file"  name="upload[]" id="upload" type="file" />
                                
							   <?php if(isset($row) && !empty($row["pic".($i+1)])){?>
                               <span class="help-block">
                                   <a class='btn btn-info btn-white'  href="<?php echo UPLOADS_URL.'/active/'.$row["pic".($i+1)]?>" data-rel="colorbox" data-geo="">瀏覽已上傳圖片</a>
                                   <div class="checkbox-inline text-danger">
                                      <label>
                                          <input type="checkbox" class="ace" name="delpic<?php echo $i?>"  value="Y">
                                          <span class="lbl">刪除此張圖片</span>
                                      </label>
                                  </div>
                               </span>  
                               <?php } ?> 
                                
                            </div>
                        </div> 
                        <?php endfor;?>
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
</form>
<script>
$(function(){
	CKEDITOR.disableAutoInline = true;
	/*
	CKEDITOR.timestamp= new Date();
	CKEDITOR.replace( 'word', {
		height: 470,
		width: 610,
	} );
	*/
	//var editor = CKEDITOR.replace( 'word' );
	//CKFinder.setupCKEditor( editor );
    //$('#word').replace("");
});
</script>
