<?PHP $this->_extends('_layouts/default_layout'); ?>
<?PHP $this->_block('contents');?>
<div>
	<form action="" method="post">
	<?php if (!empty($errors)):?>
	<div class="alert">
		<?php echo $errors?>
	</div>
	<?php endif;?>
	<fieldset>
	<legend>添加任务</legend>
	<p>
		<label>任务主题</label>
		<input name="subject">
	</p>
	<p>
		<label>任务描述</label>
		<textarea name="description" style="width:400px;height: 100px;"></textarea>
	</p>
	<p>
		<input type="submit" value="提交">
	</p>
	</fieldset>
	</form>
</div>
<?PHP $this->_endblock();?>

