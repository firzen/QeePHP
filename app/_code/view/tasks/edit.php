<?PHP $this->_extends('_layouts/default_layout'); ?>
<?PHP $this->_block('contents');?>
<div>
	<form action="" method="post">
	<input type="hidden" name="task_id" value="<?php echo $task->id()?>">
	<?php if (!empty($errors)):?>
	<div class="alert">
		<?php echo $errors?>
	</div>
	<?php endif;?>
	<fieldset>
	<legend>编辑任务 - <?php echo $task->id()?></legend>
	<p>
		<label>任务主题</label>
		<input name="subject" value="<?php echo $task->subject?>">
	</p>
	<p>
		<label>任务描述</label>
		<textarea name="description" style="width:400px;height: 100px;"><?php echo $task->description?></textarea>
	</p>
	<p>
		<label>
		<input type="checkbox" name="is_completed" value="true">
		完成
		</label>
	</p>
	<p>
		<input type="submit" value="提交">
	</p>
	</fieldset>
	</form>
</div>
<?PHP $this->_endblock();?>

