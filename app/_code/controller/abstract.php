<?php

/**
 * 应用程序的公共控制器基础类
 *
 * 可以在这个类中添加方法来完成应用程序控制器共享的功能。
 */
abstract class Controller_Abstract extends QController_Abstract
{
    /**
     * 控制器动作要渲染的数据
     *
     * @var array
     */
    protected $_view = array();

    /**
     * 控制器要使用的视图类
     *
     * @var string
     */
    protected $_view_class = 'View';

    /**
     * 控制器要使用的视图
     *
     * @var string
     */
    protected $_viewname = null;

    /**
     * 控制器所属的应用程序
     *
     * @var MyApp
     */
    protected $_app;

    /**
     * 登陆用户信息，等价于$this->_app->currentUser();
     */
    protected $_login_user;
    /**
     * 构造函数
     */
    function __construct($app)
    {
        parent::__construct();
        $this->_app = $app;
    }

    /**
     * 执行指定的动作
     *
     * @return mixed
     */
    function execute($action_name, array $args = array())
    {
        $action_method = "action{$action_name}";

        // 执行指定的动作方法
        $this->_before_execute();

        #IFDEF DBEUG
        QLog::log('EXECUTE ACTION: '. get_class($this) . '::' . $action_method . '()', QLog::DEBUG);
        #ENDIF

       	$response = call_user_func_array(array($this, $action_method), $args);
        $this->_after_execute($response);

        //未指定 使用版本 就使用新版
        $_login_user=$this->_app->currentUser();
        if (is_null($response) && is_array($this->_view))
        {
   			// 设定一些默认变量
	       	$this->_view['_app']=$this->_app;
	       	$this->_view['_login_user']=$_login_user;
	       	
            // 如果动作没有返回值，并且 $this->view 不为 null，
            $config = array('view_dir' => $this->_getViewDir(),'view_dir_base' => $this->_getViewDirBase());
            $response = new $this->_view_class($config);
            $response->setViewname($this->_getViewName())
                     ->assign($this->_view);
        }elseif ($response instanceof $this->_view_class ){
        	$this->_view['_app']=$this->_app;
	       	$this->_view['_login_user']=$_login_user;
	       	$response->assign($this->_view);
        }
        
        $this->_after_all($response);
        return $response;
    }
    protected function _after_all(&$response){
    }

    /**
     * 指定的控制器动作未定义时调用
     *
     * @param string $action_name
     */
    function _on_action_not_defined($action_name)
    {
    }

    /**
     * 执行控制器动作之前调用
     */
    protected function _before_execute()
    {
    	$this->_login_user=$this->_app->currentUser();
    }

    /**
     * 执行控制器动作之后调用
     *
     * @param mixed $response
     */
    protected function _after_execute(& $response)
    {
    }

    /**
     * 准备视图目录
     *
     * @return array
     */
    protected function _getViewDir()
    {
        $dir = _MYAPP_DIR_ . '/view';
        return $dir;
    }

    /**
     * 准备视图目录
     *
     * @return array
     */
    protected function _getViewDirBase()
    {

        $dir = Q::ini('app_config/APP_DIR') . '/view';

        if ($this->_context->namespace)
        {
            $dir .= "/{$this->_context->namespace}";
        }
        return $dir;
    }
    
    /**
     * 确定要使用的视图
     *
     * @return string
     */
    protected function _getViewName()
    {
        if ($this->_viewname === false)
        {
            return false;
        }
        $viewname = empty($this->_viewname) ? $this->_context->action_name : $this->_viewname;
        return strtolower("{$this->_context->controller_name}/{$viewname}");
    }

    /**
     * 显示一个提示页面，然后重定向浏览器到新地址
     *
     * @param string $caption
     * @param string $message
     * @param string $url
     * @param int $delay
     * @param string $script
     *
     * @return QView_Render_PHP
     */
    protected function _redirectMessage($caption, $message, $url, $delay = 5, $script = '')
    {
        $config = array('view_dir' => $this->_getViewDir(),'view_dir_base' => $this->_getViewDirBase());
        $response = new $this->_view_class($config);
        $response->setViewname('redirect_message');
        $response->assign(array(
            'message_caption'   => $caption,
            'message_body'      => $message,
            'redirect_url'      => $url,
            'redirect_delay'    => $delay,
            'hidden_script'     => $script,
        ));
        return $response;
    }
    protected function _redirect($url, $delay = 0, $js = 0)
    {
    	if ($js){
    		exit('<script type="text/javascript">window.location="'.$url.'";</script>');
    	}else {
    		$qr=new QView_Redirect($url, $delay);
    		return $qr;
    	}
    }
}

