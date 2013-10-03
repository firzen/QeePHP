<?php echo '<?php'; ?>

// $Id$

/**
 * <?php echo $class_name; ?> 控制器
 */
class <?php echo $class_name; ?> extends Controller_Abstract
{

	function actionIndex()
	{
        // 为 $this->_view 指定的值将会传递数据到视图中
		# $this->_view['text'] = 'Hello!';
		# 访问控制在 config/acl.yaml.php
		# app.yaml.php 用 Q::ini('appini/设置名') 来读取，例如 Q::ini('appini/app_title');
		# $this->_redirectMessage
		# $this->_redirect
		# $this->_404
		# $this->_login_user 访问 保存在session的用户数组
		# $this->_context 访问上下文资料
		# $this->_context->isPOST()
		# $this->_context->isAJAX()
		# $this->_app 访问myApp
	}
}


