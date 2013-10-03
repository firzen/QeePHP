<?php //布局设定 ，参考 view/_layouts下面的文件 ?>
<?PHP $this->_extends('_layouts/default_layout'); ?>
<?PHP $this->_block('title');?>
	<?php //head title 部分 ?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('head');?>
	<?php //head 部分 ?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('contents');?>
	<?php //主体部分 ?>
	<?php
		/*
		view 中默认设定的变量
		
		'_ctx'          => $context,
		'_BASE_DIR'     => $context->baseDir(),
		'_BASE_URI'     => $context->baseUri(),
		'_REQUEST_URI'  => $context->requestUri(),
		'_login_user'	=> myApp()->instance()->currentUser(),
		'_app'		=> myApp::instance(),
		*/
		
		/*
		ajax说明
		a		type=window		自动弹出facebox载入，图片展示，如果是 a，自动载入 href 网址到 facebox
				rel=id			载入href内容到 页面指定id的区域
		*		class=confirm	单击时需要确认，提示消息为 “确认title吗？”
		form	target=id		如果页面内id元素存在，自动载入form提交后结构到 页面指定id区域，文件上传失效！	
		*/
	?>
<?PHP $this->_endblock();?>

