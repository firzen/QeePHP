<?php
// $Id$
define('ONEDAY',86400);
// 设置默认的时区
date_default_timezone_set("Etc/GMT-8");
define('SHANGHAI_DATE',date('Ymd'));
define('INDEX_DIR',dirname(dirname(__FILE__)));
define('CLI_RUNNING', true);

class CliAppException extends Exception{}

/**
 * 应用程序启动脚本
 */
global $g_boot_time;
$g_boot_time = microtime(true);

$app_config = require(INDEX_DIR. '/base/_config/boot.php');
require $app_config['QEEPHP_DIR'] . '/library/q.php';

//程序开始运行
$ret = CliApp::instance($app_config)->dispatching($argv);

if (is_string($ret)) echo $ret;

return $ret;



/**
 * CliApp 封装了应用程序的基本启动流程和初始化操作，并为应用程序提供一些公共服务。
 *
 * 主要完成下列任务：
 * - 初始化运行环境
 * - 提供应用程序入口
 * - 为应用程序提供公共服务
 * - 处理访问控制和用户信息在 session 中的存储
 */
class CliApp
{
	static $DEBUG =true;
    /**
     * 应用程序的基本设置
     *
     * @var array
     */
    public $_app_config;
    /**
     * 是否已经开启了session
     */
    protected static $_session_started =false;
    
    public static $appName;
    public static $usingSelleruserid;
    /**
     * 构造函数
     *
     * @param array $app_config
     *
     * 构造应用程序对象
     */
    function __construct(array $app_config)
    {
        global $g_boot_time;
        QLog::log('--- STARTUP TIME --- ' . $g_boot_time, QLog::DEBUG);


        // 设置异常处理函数
        set_exception_handler(array($this, 'exception_handler'));

        // 初始化应用程序设置
        $this->_app_config = $app_config;
        
        #读取配置文件
        $run_mode = strtolower($app_config['RUN_MODE']);
        $config= require $app_config['CONFIG_DIR'].'/init.php';
        Q::replaceIni($config);

        // 注册应用程序对象，在程序每个地方使用 Q::get_obj('app') 重新获得本对象的唯一实例
        Q::set_obj($this, 'app');
        
        //默认载入Base 的库及模型
        Q::import(INDEX_DIR.'/base');
        Q::import(INDEX_DIR.'/base/model');
        
        /*************增加对二次开发控的目录的检查和目录导入************/
        if(isset($config['app_config']['VERISON_DIR'])&& is_dir($config['app_config']['VERISON_DIR']))
        {
        	Q::import($config['app_config']['VERISON_DIR']);
        	Q::import($app_config['APP_DIR'].'/model');
        }
        /******************end*************************/
        // 默认库
        ibayPlatform::$ibayplatform_db=Q::ini('db_dsn_pool/default/database');
        //log 文件按时间命名  20091121.log
        Q::changeIni('log_writer_filename' , Q::ini('app_config/RUN_MODE').'-'.date('Ymd',CURRENT_TIMESTAMP).'.log');
    }

    /**
     * 析构函数
     */
    function __destruct()
    {
        // #IFDEF DBEUG
        global $g_boot_time;
        $shutdown_time = microtime(true);
        $length = $shutdown_time - $g_boot_time;
        QLog::log("--- SHUTDOWN TIME --- {$shutdown_time} ({$length})sec", QLog::DEBUG);
        // #ENDIF
    }
    function currentUser(){
    	return null;
    }

    /**
     * 返回应用程序类的唯一实例
     *
     * @param array $app_config
     *
     * @return CliApp
     */
    static function instance( $app_config = null)
    {
        static $instance;
        if (is_null($instance))
        {
            if (empty($app_config))
            {
                die('INVALID CONSTRUCT APP');
            }
            $instance = new CliApp($app_config);
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
        // 获得请求对应的 UDI controller namespace module action
        $t=!empty($args[1])?$args[1]:'default@base';
		$ti=strpos($t,'@');
        if($ti){
            $controller=substr($t,0,$ti);
            $module=substr($t,$ti+1);
        }else{
            $controller=$t;
            $module=null; 
        }

        $udi =array(
        	'controller'=> $controller,
        	'action'=>!empty($args[2])?$args[2]:'index',
        	'namespace'=>null,
        	'module'=>$module,
        );
		$udiAttr='';
		if(isset($args[3])){
			$udiAttr.='-'.(strlen($args[3])<=8?$args[3]:substr($args[3],0,8));
			$udiAttr=str_replace(array('\\','/',':'),'-',$udiAttr);
		}

        //载入 各个应用 
        $modules=Q::ini('modules');
        if(isset($modules[$udi[QContext::UDI_MODULE]])){
			
            self::$appName=$module_name=$udi[QContext::UDI_MODULE];
            $module_conf=$modules[$udi[QContext::UDI_MODULE]];
            Q::import( Q::ini('app_config/MODULE_DIR').'/'.$module_name.'/_code');
			
        }else{
			// 无module 选择 , 
            self::$appName='';
			
			//使用 全局 模型
			foreach($modules as $module_name=>$v){
				Q::import( Q::ini('app_config/MODULE_DIR').'/'.$module_name.'/_code');
			}
        }
        Q::changeIni('module_name',self::$appName);
        
        
        //log 文件按时间命名  20091121.log
        Q::changeIni('log_writer_filename' , 'cli.'.$udi['controller'].'-'.$udi['action'].$udiAttr.'-'.Q::ini('app_config/RUN_MODE').'-'.date('Ymd',CURRENT_TIMESTAMP).'.log');
        
        #IFDEF DEBUG
        QLog::log('REQUEST UDI: ' . implode('/',$args), QLog::DEBUG);
        #ENDIF

       	#载入controller
        $class_name = 'controller_';
        $controller_name = $udi[QContext::UDI_CONTROLLER];
        $class_name .= $controller_name;

        if(strlen(Q::ini('app_config/VERISON_DIR'))&&is_dir(Q::ini('app_config/VERISON_DIR')))
        {
        	if (strpos($class_name, '\\') === false)
        	{
        		$filename = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
        	}
        	else
        	{
        		$filename = str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
        		$filename = ltrim($filename, '\\');
        	}
        	$l_filename = strtolower($filename);
        	//分别查找verison目录下的base及modules目下是否有相对应的action，如果有的话调用对应的
        	 
        	if ($udi[QContext::UDI_MODULE]==''||$udi[QContext::UDI_MODULE]=='base'){
        		if(is_file(Q::ini('app_config/VERISON_DIR') .DS.'base'.DS.'v'. $l_filename))
        		{
        			$class_name = "vcontroller_".$controller_name;
        			//$class_name = $this->_app_config['VERISON_NAME']."_controller_".$controller_name;
        		}
        	}
        	 
        	//
        	if (strlen($udi[QContext::UDI_MODULE])>0&&$udi[QContext::UDI_MODULE]!=''&&$udi[QContext::UDI_MODULE]!='base'&&$udi[QContext::UDI_MODULE]!='default'){
        		if(is_file(Q::ini('app_config/VERISON_DIR') .DS.'modules'.DS.$udi[QContext::UDI_MODULE].DS.'_code'.DS.'v'. $l_filename))
        		{
        			$class_name = "vcontroller_".$controller_name;
        			//$class_name = $this->_app_config['VERISON_NAME']."_controller_".$controller_name;
        		}
        	}
        }
        
        if ($udi[QContext::UDI_MODULE]==''||$udi[QContext::UDI_MODULE]=='base'||$udi[QContext::UDI_MODULE]=='default'){
        	if(isset($l_filename)&&is_file(Q::ini('app_config/VERISON_DIR') .DS.'base'.DS.'v'. $l_filename))
        	{
        		Q::import( Q::ini('app_config/VERISON_DIR').'/'.'base/');
        	}
        }
        if (strlen($udi[QContext::UDI_MODULE])>0&&$udi[QContext::UDI_MODULE]!=''&&$udi[QContext::UDI_MODULE]!='base'
        		&&$udi[QContext::UDI_MODULE]!='default'){
        	if(isset($l_filename)&&is_file(Q::ini('app_config/VERISON_DIR') .DS.'modules'.DS.$udi[QContext::UDI_MODULE].DS.'_code'.DS.'v'. $l_filename))
        	{
        		Q::import( Q::ini('app_config/VERISON_DIR').'/modules/'.$udi[QContext::UDI_MODULE].'/_code/');
        	}
        }
        
        $controller = new $class_name($this);
        $action_name = $udi[QContext::UDI_ACTION];
        if ($controller->existsAction($action_name))
        {
            // 如果指定动作存在，则调用
            $response = $controller->execute($action_name, $args);
        }
        else
        {
        	throw new CliAppException('Action Not Defined!');
        }

        // 其他情况则返回执行结果
        return $response;
    }


	/**
	 * 默认的异常处理
	 */
	function exception_handler(Exception $ex)
	{
		#record exception in daily log and exception log
		Qlog::log($ex);
		#show exception error page
		echo $ex->getMessage();
	}
    /**
     * 载入配置文件内容
     *
     * @param array $app_config
     *
     * @return array
     */
    static function loadConfigFiles(array $app_config)
    {
        $run_mode = strtolower($app_config['RUN_MODE']);
        $config= require $app_config['CONFIG_DIR'].'/init.php';
        return $config;
    }
}

/**
 * 多语言处理函数
 * 一次全部载入! 
 */
function __t(){
    global $language_lines;
    $args = func_get_args();
    $msg = array_shift($args);
    $language = strtolower(Q::ini('app_config/LANGUAGE'));
    
    if(!is_array($language_lines)){
        $language_lines=array();
        $lang_input=@include(Q::ini('app_config/APP_DIR'). '/_lang/'.$language.'.php');
		if(is_array($lang_input))
			$language_lines+=$lang_input;
        if(Q::ini('module_name')){
	        $lang_input=@include(Q::ini('app_config/MODULE_DIR') . "/".Q::ini('module_name')."/_lang/".$language.'.php');
            if(is_array($lang_input))
                $language_lines+=$lang_input;
		}
    }
    if (isset($language_lines[$msg]))
    {
        $msg = $language_lines[$msg];
    }
    array_unshift($args, $msg);
    return call_user_func_array('sprintf', $args);
}

/**
 * ebay 翻译函数
 *
 * @return unknown
 */
function _EL ()
{
    $args = func_get_args();
    $msg = array_shift($args);
    $messages = require  Q::ini('app_config/ROOT_DIR') . '/_common/_lang/ebay.php';
    if (isset($messages[$msg])) {
        $msg = $messages[$msg];
    }
    array_unshift($args, $msg);
    return call_user_func_array('sprintf', $args);
}
