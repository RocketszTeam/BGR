<script src="<?php echo ASSETS_URL?>/admin/js/jquery.nestable.min.js"></script>

<script type="text/javascript">

	$(function(){
		$('#nestable').nestable({maxDepth:1, group:0});
		$('#nestable').on('change', function() {	//拖曳排序後觸發
			dropSort();
		});
		getTree();
	});
    function getTree() {
		var data;
		
		$.ajax({
			type: "POST",
			url: CI_URL + "<?php echo SYSTEM_URL?>/item/load_view",
			async:false,
			dataType: "json"
		}).done(function( htmlData ) {  
			$('#tree').treeview({enableLinks:true,showTags:true,data: htmlData,levels:1});
		});
    }
	function dropSort(){
		var sortStr='';
		$('#nestable .dd-item').each(function(){
			sortStr+= (sortStr!='' ? ',' : '') + $(this).attr('data-id');
		});
		//console.log(sortStr);
		$.ajax({
			type: "POST",
			url:	'<?php echo site_url(SYSTEM_URL."/item/AjaxRange")?>',
			cache: false,
			async:false,
			data: { 
					sortNum: sortStr	//新排序
				  }
		}).done(function( htmlData ) {  			
			getTree();
		});				
	}
</script>

<div class="col-xs-12 col-lg-6">
    <div class="widget-box widget-color-green2">
        <div class="widget-header">
            <h4 class="widget-title lighter smaller">權限列表</h4>
        </div>
        <div class="widget-body">
            <div class="widget-main padding-8">
            		<h4 class="widget-title lighter smaller">
            	
                	<a href="<?php echo site_url(SYSTEM_URL.'/item/myRange');?>" class="btn btn-danger">顯示主分類</a>
                	</h4>
                <div id="tree"></div>                                                
            </div>
        </div>                                            
    </div>
</div>

<div class="col-xs-12 col-lg-6">
    <div class="widget-box widget-color-blue2">
        <div class="widget-header">
            <h4 class="widget-title lighter smaller"><?php echo $subtitle?></h4>
        </div>
        <div class="widget-body">
            <div class="widget-main padding-8">
                <div class="dd" id="nestable">
                    <ol class="dd-list">
                    	<?php if(isset($rowAll) && count($rowAll) > 0):?>
                        <?php foreach($rowAll as $row):?>
                        <li class="dd-item" data-id="<?php echo $row["num"]?>">
                            <div class="dd-handle"><?php echo $row["title"]?></div>
                        </li>
                        <?php endforeach;?>
                        <?php endif;?>  
                    </ol>
                </div>                                                    
            </div>
        </div>                                            
    </div>
</div>
    
                                        
                                        