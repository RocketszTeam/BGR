<?php
if(!empty($alert)){
?>
<div class="alert alert-<?php echo $alert['type']; ?> fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
    	<i class="ace-icon fa fa-times"></i>
    </button>
    <strong><?php echo $alert['title']; ?></strong><?php echo $alert['content']; ?>
</div>
<?php
}
?>