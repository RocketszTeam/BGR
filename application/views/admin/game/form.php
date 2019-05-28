<script type="text/javascript">
    function ajaxKind(makers_num,kindVal){
        $.ajax({
            type: "POST",
            url: CI_URL + '<?php echo SYSTEM_URL?>/Games/ajaxKind',
            cache: false,
            data: { 
                    makers_num: makers_num,
                    kindVal: kindVal
                  }
        }).done(function( msg ) {           
            $('#kind').html(msg);
            $('.select2').select2({allowClear:true});
        }); 
    }
</script>
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
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">遊戲廠商</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="makers_num" id="makers_num" data-placeholder="請選擇" onChange="ajaxKind(this.value,'')" required>
                                <?php if(isset($row_group)):?>
                                <?php foreach($row_group as $rowg):?>
                                <option value="<?php echo $rowg["num"]?>" <?php if(isset($row) && $rowg["num"]==@$row["makers_num"]) echo ' selected'?>><?php echo $rowg["makers_name"]?></option> 
                                <?php endforeach;?>
                                <?php endif;?>      
                              </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">遊戲分類</label>
                            <div class="col-xs-12 col-sm-5">
                                <select class="form-control select2" name="kind" id="kind" data-placeholder="請選擇">
                                </select>
                            </div>
                        </div>
                        <script type="text/javascript">
                            $(function(){
                                ajaxKind($('#makers_num').val(),'<?php echo @$row["kind"]?>');
                            });
                        </script>
                        
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">遊戲名稱</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="game_name" name="game_name" value="<?php if(isset($row)) echo $row["game_name"]?>" placeholder="遊戲名稱" required />
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">遊戲代碼</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="text" class="form-control" id="game_code" name="game_code" value="<?php if(isset($row)) echo $row["game_code"]?>" placeholder="遊戲代碼" required />
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">支援裝置</label>
                            <div class="col-xs-12 col-sm-5 radio">
                                <?php echo inInputCR($deviceArr,'radio','device',(isset($row) ? $row["device"] : 1))?>
                            </div>
                        </div> 
                         
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">狀態</label>
                            <div class="col-xs-12 col-sm-5">
                              <select class="form-control select2" name="active" id="active" data-placeholder="請選擇" required>
                                <option value="Y" <?php if(@$row["active"]=='Y') echo ' selected'?>>開放</option>
                                <option value="N" <?php if(@$row["active"]=='N') echo ' selected'?>>關閉</option>
                              </select>
                            
                            </div>
                        </div> 
                       
                        <?php for($i=0;$i<$picMax;$i++):?>
                        <div class="form-group">
                            <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">圖片</label>
                            <div class="col-xs-12 col-sm-5">
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
                                <a class='btn btn-danger' href='<?php echo $cancelBtn.'?find2='.@$_REQUEST['find2'].'&find3='.@$_REQUEST['find3'].'&per_page='.@@$_REQUEST['per_page']  ;  ?>' >取消</a>
                            </div>    
                        </div> 
                        <input type="hidden" id='find2' name='find2' value="<?php echo @$_REQUEST['find2'];?>">
                        <input type="hidden" id='find3' name='find3' value="<?php echo @$_REQUEST['find3'];?>">
                        <input type="hidden" id='per_page' name='per_page' value="<?php echo @$_REQUEST['per_page'];?>">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

