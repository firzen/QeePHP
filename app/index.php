<?php 
/**
 * 定义主目录，即index.php入口文件所在目录
 */
define('_INDEX_DIR_', dirname(__FILE__));
global $g_boot_time;
$g_boot_time = microtime(true);


//强制错误显示
error_reporting(E_ALL | E_STRICT);

//载入QeePHP
require _INDEX_DIR_.'/../library/q.php';

//载入应用对象
require _INDEX_DIR_.'/_code/myapp.php';

//进入 MVC
$ret = MyApp::instance(_INDEX_DIR_.'/_code/_config')->dispatching();

if (is_string($ret)) echo $ret;

return $ret;
