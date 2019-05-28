<script type="text/javascript">
	$(window).ready(function(){
		var maxHeight=200;		
		if ($('#wordArea').height()>parseInt(maxHeight)){
			$('#wordArea').css('height',maxHeight+'px');
			$('#wordArea').css('overflow','auto');	
		}
	});
</script>



<div class="col-sm-12">
    <form class="form-horizontal newoil-form" method="post" action="<?php echo $formAction?>">   
    <fieldset class="scheduler-border">
        <legend class="scheduler-border"><h2><i class="glyphicon glyphicon-edit"></i><?php echo $subtitle?></h2></legend>
        
        

        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">會員帳號</label>
            <div class="col-sm-8">
            	<p class="form-control-static">
				<?php echo tb_sql("u_id","member",$row["mem_num"]);?>
                (<?php echo tb_sql("u_name","member",$row["mem_num"]);?>)
                </p> 
            </div>
        </div>
        
        
       
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">類型</label>
            <div class="col-sm-8">
            	<p class="form-control-static"><?php echo tb_sql("kind","member_talk_kind",$row["kind"]);?></p> 
            </div>
        </div>
        
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">標題</label>
            <div class="col-sm-8">
            	<p class="form-control-static"><?php echo $row["subject"];?></p> 
            </div>
        </div>
        
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">提問內容</label>
            <div class="col-sm-8">
               <div class="form-control" style="height:200px; overflow:auto" id="wordArea">
            	<?php if(isset($row)) echo br($row["word"])?>
               </div>
            </div>
        </div>
       
        

        
        <div class="form-group">
            <label for="inputEmail3" class="col-sm-2 control-label">客服回覆</label>
            <div class="col-sm-8">
            	<textarea name="re_word"  class="form-control" rows="5" placeholder="客服回覆"><?php echo (isset($row) ? $row['re_word'] : '')?></textarea>
            </div>
        </div>        
        
        <div class="form-group ">
            <div class="col-sm-offset-2 col-sm-8">
                <button type="submit" class="btn btn-primary"><?php echo ($todo=='add'?'新增':'回覆')?></button>
                
                <a class='btn btn-danger' href='<?php echo site_url(SYSTEM_URL.'/Message/index');?>' >返回</a>
            </div>
        </div>
    </fieldset>   
    </form>
</div>