<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Qee13 - <?php $this->_block('title'); ?> <?php $this->_endblock(); ?> QeePHP 文档 下载</title>
	<meta name="keywords" content="PHP,QEEPHP,下载,文档,交流">
	<link href="<?php echo $_BASE_DIR?>public/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link rel="stylesheet" href="<?php echo $_BASE_DIR?>public/css/todc-bootstrap.css"/>
	<link rel="stylesheet" href="<?php echo $_BASE_DIR?>public/bootstrap/css/bootstrap-responsive.min.css"/>
	<link rel="stylesheet" href="<?php echo $_BASE_DIR?>public/css/base.css"/>
	<script type="text/javascript" src="<?php echo $_BASE_DIR?>public/jquery-1.8.0.min.js"></script>
	<?php $this->_block('head'); ?><?php $this->_endblock(); ?>
</head>
<body>
<div class=" header">
	<div class="container">
		<div class="row">
			<div class="span9">
				<a href="<?php echo url('default/index')?>"><h1>QeePHP</h1></a>
				<p>领域驱动设计框架</p>
			</div>
			<div class="span3">
				<div style="margin-top: 50px;text-align: right;">
					<?php if (isset($_login_user['username'])):?>
						欢迎您，<?php echo $_login_user['username']?>&nbsp;
						<a href="<?php echo url('users/changePassword')?>">修改密码</a>
						<a href="<?php echo url('users/logout')?>">登出</a>&nbsp;
					<?php else:?>
						<a href="<?php echo url('users/login')?>">登录</a> &nbsp;
						<a href="<?php echo url('users/register')?>">注册</a>
					<?php endif;?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="container">
	<div class="row">
		<div class="span2 ">
			<div  id="menu">
			<ul class="level-one">
				<li><a href="<?php echo url('default/about')?>">关于Qee13</a></li>
				<!-- <li><a href="<?php echo url('default/news')?>">新闻</a></li> -->
				
				<li><a href="<?php echo url('default/docs')?>">文档</a></li>
				<li><a href="<?php echo url('tools/buildController')?>">代码生成器</a></li>
				
				<li><a href="<?php echo url('default/download')?>">下载</a></li>
				<li><a href="http://qee13.uservoice.com/forums/218047-general">交流</a></li>
				
			</ul>
			</div>
		</div>
		<div class="span10">
			<?php $this->_block('contents'); ?>
			<?php $this->_endblock(); ?>
		</div>
	</div>
	<hr />
	Copyright @ 2013 Tim13
</div>
<!-- Debug Message -->
<div class="container debug">
	<?php if (in_array( $_SERVER['SERVER_NAME'],array('localhost','127.0.0.1'))):?>
	<div class="debug alert">
		<h3>Debug</h3>
		<h4>$_GET</h4>
		<?php dump($_GET)?>
		<h4>$_POST</h4>
		<?php dump($_POST)?>
		<h4>$_SESSION</h4>
		<?php dump($_SESSION)?>
		<h4>$_COOKIE</h4>
		<?php dump($_COOKIE)?>
	</div>
	<?php endif;?>
</div>
<script type="text/javascript" src="http://js.tongji.linezing.com/3293358/tongji.js"></script><noscript><a href="http://www.linezing.com"><img src="http://img.tongji.linezing.com/3293358/tongji.gif"/></a></noscript>
</body>
</html>
