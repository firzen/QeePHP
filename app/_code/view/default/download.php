<?php //布局设定 ，参考 view/_layouts下面的文件 ?>
<?PHP $this->_extends('_layouts/default_layout'); ?>
<?PHP $this->_block('title');?>下载<?PHP $this->_endblock();?>
<?PHP $this->_block('head');?>
    <?php //head 部分 ?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('contents');?>
<div>
	<h3>新版下载地址</h3>
	
	<p>
		<a target="_blank" href="https://github.com/firzen/QeePHP">https://github.com/firzen/QeePHP</a>
	</p>
	<p>
		<a target="_blank" href="https://code.google.com/p/qee13/">https://code.google.com/p/qee13/</a>
	</p>
	<p>
		203.208.46.177	qee13.googlecode.com
	</p>
	<h3>原版下载地址</h3>
	<p>
		<a target="_blank" href="https://code.google.com/p/qeephp/">https://code.google.com/p/qeephp/</a>
	</p>
	<h4>Host</h4>
</div>
    
<?PHP $this->_endblock();?>

