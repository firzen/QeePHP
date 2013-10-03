<?PHP $this->_extends('_layouts/sexy_layout'); ?>
<?PHP $this->_block('title');?>
	<?php //head title 部分 ?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('head');?>
	<?php //head 部分 ?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('contents');?>
	<table>
		<tr><th>已购买套餐</th><th>到期时间</th></tr>
		<?php foreach ($base_user_app as $v_base_user_app):?>
		<tr>
			<td><?php echo $v_base_user_app->app->appname?></td>
			<td><?php echo date('Y-m-d', $v_base_user_app->expire)?></td>
		</tr>
		<?php endforeach;?>
	</table>
<?PHP $this->_endblock();?>