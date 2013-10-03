<?php
/**
 * 转换 HTML 特殊字符，等同于 htmlspecialchars()
 *
 * @param string $text
 *
 * @return string
 */
function h($text)
{
    return htmlspecialchars($text);
}


/**
 * QDebug::dump() 的简写，用于输出一个变量的内容
 *
 * @param mixed $vars 要输出的变量
 * @param string $label 输出变量时显示的标签
 * @param boolean $return 是否返回输出内容
 *
 * @return string
 */
function dump($vars, $label = null, $return = false)
{
    return QDebug::dump($vars, $label, $return);
}

/**
 * QContext::url() 方法的简写，用于构造一个 URL 地址
 *
 * url() 方法的参数比较复杂，请参考 QContext::url() 方法的详细说明。
 *
 * @param string $udi UDI 字符串
 * @param array|string $params 附加参数数组
 * @param string $route_name 路由名
 * @param array $opts 控制如何生成 URL 的选项
 *
 * @return string 生成的 URL 地址
 */
function url($udi, $params = null, $route_name = null, array $opts = null)
{
    return QContext::instance()->url($udi, $params, $route_name, $opts);
}

/**
 * 设置对象的自动载入
 */
Q::registerAutoload();

/**
 * Ajax 相关的函数
 */
 /**
 * 当没有找到 PHP 内置的 JSON 扩展时，使用 PEAR::Service_JSON 来处理 JSON 的构造和解析
 *
 * 强烈推荐所有 PHP 用户安装 JSON 扩展，获得最好的性能表现。
 */

if (!function_exists('json_encode')) {
    /**
     * 将变量转换为 JSON 字符串
     *
     * @param mixed $value
     *
     * @return string
     */
    function json_encode($value)
    {
        static $instance = array();
        if (!isset($instance[0])) {
            require_once(Q_DIR . '/helper/json.php');
            $instance[0] =new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        }
        return $instance[0]->encode($value);
    }
}

if (!function_exists('json_decode')) {
    /**
     * 将 JSON 字符串转换为变量
     *
     * @param string $jsonString
     *
     * @return mixed
     */
    function json_decode($jsonString)
    {
        static $instance = array();
        if (!isset($instance[0])) {
            require_once(Q_DIR . '/helper/json.php');
            $instance[0] =new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        }
        return $instance[0]->decode($jsonString);
    }
}


/**
 * 自定义函数库-请求处理系列
 */

function val($arr, $name, $default = null)
{
    return isset($arr[$name]) ? $arr[$name] : $default;
}
/**
 * 检查$_POST,$_GET,$_REQUEST数组元素内容，不存在则返回 $default默认值
 * @param $name 
 * @param $default 默认值
 * @return mixed
 */
function request($name, $default = null,$auto_trim=true)
{
    if (isset($_POST[$name])){
        return post($name,$default,$auto_trim);
    }elseif (isset($_GET[$name])){
    	return get($name,$default,$auto_trim);
    }elseif (isset($_REQUEST[$name])) {
        return $auto_trim && !is_array($_REQUEST[$name])?trim($_REQUEST[$name]):$_REQUEST[$name];
    }else {
    	return $default;
    }
}
/**
 * 返回$_GET数组元素内容，不存在则返回 $default默认值
 * @param $name _GET[$name]
 * @param $default 默认值
 * @return mixed
 */
function get($name, $default = null,$auto_trim=true)
{
    $ret= isset($_GET[$name]) ? $_GET[$name]: $default;
    if ($auto_trim && !is_array($ret)){
    	$ret=trim($ret);
    }
    return $ret;
}
/**
 * 返回$_POST数组元素内容，不存在则返回 $default默认值
 * @param $name _POST[$name]
 * @param $default 默认值
 * @return mixed
 */
function post($name, $default = null,$auto_trim=true)
{
	
    $ret= isset($_POST[$name]) ? $_POST[$name] : $default;
    if ($auto_trim && !is_array($ret)){
    	$ret=trim($ret);
    }
    return $ret;
}

function cookie($name, $default = null)
{
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
}

function session($name, $default = null)
{
    return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
}

function server($name, $default = null)
{
    return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
}

function env($name, $default = null)
{
    return isset($_ENV[$name]) ? $_ENV[$name] : $default;
}
/**
 * 是否是 POST 请求
 * @return bool
 */
function request_is_post()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}
/**
 * 是否 AJAX 请求
 * @return bool
 */
function request_is_ajax()
{
    return strtolower(get_header('X_REQUESTED_WITH')) == 'xmlhttprequest';
}

function request_is_flash()
{
    return strtolower(get_header('USER_AGENT')) == 'shockwave flash';
}
function get_header($header)
{
    $name = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
    return server($name, '');
}
function get_domain_address($ssl=false){
	return 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
}
function server_is_ip(){
	return QValidator::validate_is_ipv4($_SERVER['SERVER_NAME']);
}
function server_is_local(){
	return in_array(@$_SERVER['SERVER_NAME'],array('localhost','127.0.0.1'));
}