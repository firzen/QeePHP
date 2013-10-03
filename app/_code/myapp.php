<?php
// 设置默认的时区
date_default_timezone_set("Etc/GMT-8");
//Myapp根目录
define('_MYAPP_DIR_', dirname(__FILE__));
/**
 * MyApp 封装了应用程序的基本MVC流程和初始化操作，并为应用程序提供一些公共服务。
 *
 * 主要完成下列任务：
 * - 初始化运行环境
 * - 提供应用程序入口
 * - 为应用程序提供公共服务
 * - 处理访问控制和用户信息在 session 中的存储
 * 
 * 执行流程解析
 * - 由入口文件实例化 Myapp
 * - 调用Dispatch 函数，处理应用信息流程
 *   - 权限判定
 *   - 转向相应的Controller
 *   - 返回View或流程控制对象
 * 
 * 使用示例
 * /---code php
 * $ret = MyApp::instance($app_config_dir)->dispatching();
 * if (is_string($ret)) echo $ret;
 * \---
 * @package mvc
 */
class MyApp
{
    /**
     * 应用程序的基本设置
     *
     * @var array
     */
    public $_app_config;
    
    /**
     * 构造函数
     *
     * @param array $app_config
     *
     * 构造应用程序对象
     */
    function __construct($app_config_dir)
    {
    	global $g_boot_time;
        QLog::log('--- STARTUP TIME --- ' . $g_boot_time, QLog::DEBUG);

        // 设置异常处理函数
        set_exception_handler(array($this, 'exception_handler'));

        #读取配置文件
        $config= require $app_config_dir.'/init.php';
        $config['app_config']['APP_DIR']=_MYAPP_DIR_;
        
        // 初始化应用程序设置
        $this->_app_config = $config['app_config'];
        
        Q::replaceIni($config);

        // 注册应用程序对象，在程序每个地方使用 Q::get_obj('app') 重新获得本对象的唯一实例
        Q::set_obj($this, 'app');
        
        //添加自动载入路径
        Q::import(_INDEX_DIR_.'/_code');
        Q::import(_INDEX_DIR_.'/_code/model');
        
    }

    /**
     * 析构函数
     */
    function __destruct()
    {
        global $g_boot_time;
        $shutdown_time = microtime(true);
        $length = $shutdown_time - $g_boot_time;
        QLog::log("--- SHUTDOWN TIME --- {$shutdown_time} ({$length})sec", QLog::DEBUG);
    }

    /**
     * 返回应用程序类的唯一实例
     *
     * @param string $app_config_dir 配置文件目录
     *
     * @return MyApp
     */
    static function instance( $app_config_dir = null)
    {
        static $instance;
        
        if (is_null($instance))
        {
            if (empty($app_config_dir))
            {
                die('INVALID CONSTRUCT APP');
            }
            $instance = new MyApp($app_config_dir);
        }
        return $instance;
    }

    /**
     * 返回应用程序基础配置的内容
     *
     * 如果没有提供 $item 参数，则返回所有配置的内容
     *
     * @param string $item
     *
     * @return mixed
     */
    function config($item = null)
    {
        if ($item)
        {
            return isset($this->_app_config[$item]) ? $this->_app_config[$item] : null;
        }
        return $this->_app_config;
    }
    
    /**
     * 根据运行时上下文对象，调用相应的控制器动作方法
     *
     * @param array $args
     *
     * @return mixed
     */
    function dispatching(array $args = array())
    {
        // 构造运行时上下文对象
        $context = QContext::instance();
        $udi = $context->requestUDI('array');
        
        // 打开 session
        if (Q::ini('runtime_session_start') ){
        	session_start();
        }
        QLog::log('session_id: ' . session_id(), QLog::DEBUG);
        
        QLog::log('REQUEST METHOD：'.$_SERVER['REQUEST_METHOD'],QLog::INFO);
        
        #IFDEF DEBUG
        QLog::log('REQUEST UDI: ' . $context->UDIString($udi), QLog::DEBUG);
        
        QLog::log(Helper_Util::getIp());
        #ENDIF
        
        // 检查是否有权限访问
        if (!$this->authorizedUDI($this->currentUserRoles(), $udi))
        {
            // 拒绝访问
            $response = $this->_on_access_denied();
        }
        
        # _REQUEST['controller']
        $controller_name = $udi[QContext::UDI_CONTROLLER];
        
        do
        {
        	// 构造控制器对象
        	try {
        		/* @var $controller Controller_Abstract */
        		$class_name = "controller_".$controller_name;
        		$controller = new $class_name($this);
        	}catch (Exception $ex){
        		$response = $this->_on_action_not_defined();
        		break;
        	}
        	// _REQUEST['action']
        	$action_name = $udi[QContext::UDI_ACTION];
        	if ($controller->existsAction($action_name))
        	{
        		// 如果指定Action存在，则调用
        		$response = $controller->execute($action_name, $args);
        	}
        	else
        	{
        		// 如果指定动作不存在，则尝试调用控制器的 _on_action_not_defined() 函数处理错误
        		$response = $controller->_on_action_not_defined($action_name);
        		if (is_null($response))
        		{
        			$response = $this->_on_action_not_defined();
        		}
        	}
        } while (false);
        
        if (is_object($response) && method_exists($response, 'execute'))
        {
            // 如果返回结果是一个对象，并且该对象有 execute() 方法，则调用
            $response = $response->execute();
        }
        elseif ($response instanceof QController_Forward)
        {
            // 如果是一个 QController_Forward 对象，则将请求进行转发
            $response = $this->dispatching($response->args);
        }

        // 其他情况则返回执行结果
        return $response;
    }

    /**
     * 将用户数据保存到 session 中
     *
     * @param mixed $user
     * @param mixed $roles
     */
    function changeCurrentUser($user, $roles)
    {
        $user['roles'] = implode(',', Q::normalize($roles));
        $_SESSION[Q::ini('acl_session_key')] = $user;
    }
    /**
     * 获得当前用户对应的 User 模型对象实例
     *
     * @return User
     */
    function currentUserObject()
    {
    	$data = $this->currentUser();
    	if ($data===null){
    		throw new MyAppException('当前访问用户没有对应的 User 对象'); 
    	}
    	$user = User::find('user_id = ?', $data['user_id'])->getOne();
    	if (!$user->isNewRecord())
    	{
    		return $user;
    	}
    	else
    	{
    		throw new MyAppException('当前访问用户没有对应的 User 对象');
    	}
    }
    /**
     * 获取保存在 session 中的用户数据
     * @param $data_key 属性
     * @return array|null 不存在时返回null
     */
    static function currentUser($data_key =null)
    {
        $key = Q::ini('acl_session_key');
        if (!isset($_SESSION[$key])){
            return null;
        }
        if (!is_null($data_key)){
            return $_SESSION[$key][$data_key];
        }
        return $_SESSION[$key];
    }

    /**
     * 获取 session 中用户信息包含的角色
     *
     * @return array
     */
    static function currentUserRoles()
    {
        $user = self::currentUser();
        return isset($user['roles']) ? Q::normalize($user['roles']) : array();
    }
    /**
     * 从 session 中清除用户数据
     */
    static function cleanCurrentUser()
    {
        unset($_SESSION[Q::ini('acl_session_key')]);
    }

    /**
     * 检查指定角色是否有权限访问特定的控制器和动作
     *
     * @param array $roles
     * @param string|array $udi
     *
     * @return boolean
     */
    function authorizedUDI($roles, $udi)
    {
        $roles = Q::normalize($roles);
        #获得控制器的权限设置
        $acl = Q::ini('acl_global'); #config/acl.yaml.php

        if (!is_array($acl))
        {
            $controller_acl= Q::ini('acl_default');
        }

        if (isset($acl[$udi[QContext::UDI_CONTROLLER]]))
        {
            $controller_acl= (array)$acl[$udi[QContext::UDI_CONTROLLER]];
        }else{
            $acl = array_change_key_case($acl, CASE_LOWER);
            $controller_acl= isset($acl[QACL::ALL_CONTROLLERS]) ? (array)$acl[QACL::ALL_CONTROLLERS] : Q::ini('acl_default');
        }
        
        #权限检查
        $acl = Q::singleton('QACL');
        $action_name = strtolower($udi[QContext::UDI_ACTION]);
        if (isset($controller_acl['actions'][$action_name]))
        {
            // 如果动作的 ACT 检验通过，则忽略控制器的 ACT
            return $acl->rolesBasedCheck($roles, $controller_acl['actions'][$action_name]);
        }

        if (isset($controller_acl['actions'][QACL::ALL_ACTIONS]))
        {
            // 如果为所有动作指定了默认 ACT，则使用该 ACT 进行检查
            return $acl->rolesBasedCheck($roles, $controller_acl['actions'][QACL::ALL_ACTIONS]);
        }

        // 否则检查是否可以访问指定控制器
        return $acl->rolesBasedCheck($roles, $controller_acl);
    }


    /**
     * 访问被拒绝时的错误处理函数
     */
    protected function _on_access_denied()
    {
        $message = "";
        require(_MYAPP_DIR_ . '/view/403.php');
        exit;
    }
    /**
     * 访问出现exception错误处理函数
     */
    protected function _on_access_exception(Exception $exception)
    {
        $message = "";
        require(_MYAPP_DIR_ . '/view/exception.php');
        exit;
    }
    /**
     * 视图调用未定义的控制器或动作时的错误处理函数
     */
    protected function _on_action_not_defined()
    {
        require(_MYAPP_DIR_ . '/view/404.php');
        exit;
    }

    /**
     * 默认的异常处理
     */
    function exception_handler(Exception $ex)
    {
        $this->_on_access_exception($ex);
        exit;
    }
}

class MyAppException extends QException{}
