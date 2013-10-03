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
	<h2>关于Qee13</h2>
	<p>
		提供QeePHP的文档和教程，重写了部分教程更易学上手。
	</p>
	<p>	
		感谢为Qee贡献的所有人：Daulface、Jerry、Newone、UUTAN。。。
	</p>
</div>    
<?PHP $this->_endblock();?>

