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
	<h3>文档</h3>
	<ul>
		<li>快速入门：<a target="_blank" href="http://qee13.com/docs/qeephp-quickstart">http://qee13.com/docs/qeephp-quickstart</a></li>
		<li>权威开发指南：<a target="_blank" href="http://qee13.com/docs/qeephp-manual">http://qee13.com/docs/qeephp-manual</a></li>
		<li>API参考手册：<a target="_blank" href="http://qee13.com/docs/api">http://qee13.com/docs/API</a></li>
	</ul>
	<br>
	<h4>快速入门更新</h4>
	<ul>
		<li>去除YAML相关内容</li>
		<li>去除表单控件相关内容</li>
		<li>Controller命名规则简化</li>
		<li>Myapp应用简化</li>
		<li>配置简化</li>
		<li>自动建立视图文件</li>
	</ul>
</div>
<?PHP $this->_endblock();?>

