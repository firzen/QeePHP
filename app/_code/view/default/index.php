<?php //布局设定 ，参考 view/_layouts下面的文件 ?>
<?PHP $this->_extends('_layouts/default_layout'); ?>
<?PHP $this->_block('title');?>
    <?php //head title 部分 ?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('head');?>
    <?php //head 部分 ?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('contents');?>
<div>
	<img alt="" src="public/img/orm.png">
</div>

<?PHP $this->_endblock();?>

