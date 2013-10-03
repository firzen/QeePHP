<?php
// $Id: q.php 2628 2009-07-17 08:40:15Z jerry $

/**
 * 定义 QeePHP 核心类，并初始化框架基本设置
 *
 * @link http://qeephp.com/
 * @copyright Copyright (c) 2006-2009 Qeeyuan Inc. {@link http://www.qeeyuan.com}
 * @license New BSD License {@link http://qeephp.com/license/}
 * @version $Id: q.php 2628 2009-07-17 08:40:15Z jerry $
 * @package core
 */

/**
 * QeePHP 框架基本库所在路径
 */
define('Q_DIR', dirname(__FILE__));

/**
 * DIRECTORY_SEPARATOR 的简写
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * CURRENT_TIMESTAMP 定义为当前时间，减少框架调用 time() 的次数
 */
define('CURRENT_TIMESTAMP', time());

global $G_CLASS_FILES;
if (empty($G_CLASS_FILES))
{
    require Q_DIR . '/_config/qeephp_class_files.php';
}

/**
 * 类 Q 是 QeePHP 框架的核心类，提供了框架运行所需的基本服务
 *
 * 类 Q 提供 QeePHP 框架的基本服务，包括：
 * 
 * @author YuLei Liao <liaoyulei@qeeyuan.com>
 * @version $Id: q.php 2628 2009-07-17 08:40:15Z jerry $
 * @package core
 */
class Q
{
    /**
     * 指示应用程序运行模式
     */
    // 开发运行模式
    const RUN_MODE_DEVEL  = 'devel';
    // 生产部署模式
    const RUN_MODE_DEPLOY = 'deploy';
    // 测试模式
    const RUN_MODE_TEST   = 'test';

    /**
     * 对象注册表
     *
     * @var array
     */
    private static $_objects = array();

    /**
     * 类搜索路径
     *
     * @var array
     */
    private static $_class_path = array();

    /**
     * 类搜索路径的选项
     *
     * @var array
     */
    private static $_class_path_options = array();

    /**
     * 应用程序设置
     *
     * @var array
     */
    private static $_ini = array();

    /**
     * 返回 QeePHP 版本号
     *
     * @return string QeePHP 版本号
     */
    static function version()
    {
        return '13.1';
    }

    /**
     * 获取指定的设置内容
     *
     * $option 参数指定要获取的设置名。
     * 如果设置中找不到指定的选项，则返回由 $default 参数指定的值。
     *
     * @code php
     * $option_value = Q::ini('my_option');
     * @endcode
     *
     * 对于层次化的设置信息，可以通过在 $option 中使用“/”符号来指定。
     *
     * 例如有一个名为 option_group 的设置项，其中包含三个子项目。
     * 现在要查询其中的 my_option 设置项的内容。
     *
     * @code php
     * // +--- option_group
     * //   +-- my_option  = this is my_option
     * //   +-- my_option2 = this is my_option2
     * //   \-- my_option3 = this is my_option3
     *
     * // 查询 option_group 设置组里面的 my_option 项
     * // 将会显示 this is my_option
     * echo Q::ini('option_group/my_option');
     * @endcode
     *
     * 要读取更深层次的设置项，可以使用更多的“/”符号，但太多层次会导致读取速度变慢。
     *
     * 如果要获得所有设置项的内容，将 $option 参数指定为 '/' 即可：
     *
     * @code php
     * // 获取所有设置项的内容
     * $all = Q::ini('/');
     * @endcode
     *
     * @param string $option 要获取设置项的名称
     * @param mixed $default 当设置不存在时要返回的设置默认值
     *
     * @return mixed 返回设置项的值
     */
    static function ini($option, $default = null)
    {
        if ($option == '/') return self::$_ini;

        if (strpos($option, '/') === false)
        {
            return array_key_exists($option, self::$_ini)
                ? self::$_ini[$option]
                : $default;
        }

        $parts = explode('/', $option);
        $pos =& self::$_ini;
        foreach ($parts as $part)
        {
            if (!isset($pos[$part])) return $default;
            $pos =& $pos[$part];
        }
        return $pos;
    }
    static function loadDefaultIni(){
    	self::$_ini=require dirname(__FILE__).'/_config/default_config.php';
    }

    /**
     * 修改指定设置的内容
     *
     * 当 $option 参数是字符串时，$option 指定了要修改的设置项。
     * $data 则是要为该设置项指定的新数据。
     *
     * @code php
     * // 修改一个设置项
     * Q::changeIni('option_group/my_option2', 'new value');
     * @endcode
     *
     * 如果 $option 是一个数组，则假定要修改多个设置项。
     * 那么 $option 则是一个由设置项名称和设置值组成的名值对，或者是一个嵌套数组。
     *
     * @code php
     * // 假设已有的设置为
     * // +--- option_1 = old value
     * // +--- option_group
     * //   +-- option1 = old value
     * //   +-- option2 = old value
     * //   \-- option3 = old value
     *
     * // 修改多个设置项
     * $arr = array(
     *      'option_1' => 'value 1',
     *      'option_2' => 'value 2',
     *      'option_group/option2' => 'new value',
     * );
     * Q::changeIni($arr);
     *
     * // 修改后
     * // +--- option_1 = value 1
     * // +--- option_2 = value 2
     * // +--- option_group
     * //   +-- option1 = old value
     * //   +-- option2 = new value
     * //   \-- option3 = old value
     * @endcode
     *
     * 上述代码展示了 Q::changeIni() 的一个重要特性：保持已有设置的层次结构。
     *
     * 因此如果要完全替换某个设置项和其子项目，应该使用 Q::replaceIni() 方法。
     *
     * @param string|array $option 要修改的设置项名称，或包含多个设置项目的数组
     * @param mixed $data 指定设置项的新值
     */
    static function changeIni($option, $data = null)
    {
        if (is_array($option))
        {
            foreach ($option as $key => $value)
            {
                self::changeIni($key, $value);
            }
            return;
        }

        if (!is_array($data))
        {
            if (strpos($option, '/') === false)
            {
                self::$_ini[$option] = $data;
                return;
            }

            $parts = explode('/', $option);
            $max = count($parts) - 1;
            $pos =& self::$_ini;
            for ($i = 0; $i <= $max; $i ++)
            {
                $part = $parts[$i];
                if ($i < $max)
                {
                    if (!isset($pos[$part]))
                    {
                        $pos[$part] = array();
                    }
                    $pos =& $pos[$part];
                }
                else
                {
                    $pos[$part] = $data;
                }
            }
        }
        else
        {
            foreach ($data as $key => $value)
            {
                self::changeIni($option . '/' . $key, $value);
            }
        }
    }

    /**
     * 替换已有的设置值
     *
     * Q::replaceIni() 表面上看和 Q::changeIni() 类似。
     * 但是 Q::replaceIni() 不会保持已有设置的层次结构，
     * 而是直接替换到指定的设置项及其子项目。
     *
     * @code php
     * // 假设已有的设置为
     * // +--- option_1 = old value
     * // +--- option_group
     * //   +-- option1 = old value
     * //   +-- option2 = old value
     * //   \-- option3 = old value
     *
     * // 替换多个设置项
     * $arr = array(
     *      'option_1' => 'value 1',
     *      'option_2' => 'value 2',
     *      'option_group/option2' => 'new value',
     * );
     * Q::replaceIni($arr);
     *
     * // 修改后
     * // +--- option_1 = value 1
     * // +--- option_2 = value 2
     * // +--- option_group
     * //   +-- option2 = new value
     * @endcode
     *
     * 从上述代码的执行结果可以看出 Q::replaceIni() 和 Q::changeIni() 的重要区别。
     *
     * 不过由于 Q::replaceIni() 速度比 Q::changeIni() 快很多，
     * 因此应该尽量使用 Q::replaceIni() 来代替 Q::changeIni()。
     *
     * @param string|array $option 要修改的设置项名称，或包含多个设置项目的数组
     * @param mixed $data 指定设置项的新值
     */
    static function replaceIni($option, $data = null)
    {
        if (is_array($option))
        {
            self::$_ini = array_merge(self::$_ini, $option);
        }
        else
        {
            self::$_ini[$option] = $data;
        }
    }

    /**
     * 载入指定类的定义文件，如果载入失败抛出异常
     *
     * @code php
     * Q::loadClass('Table_Posts');
     * @endcode
     * 
     * @param string $class_name 要载入的类
     *
     * @return string|boolean 成功返回类名，失败返回 false
     */
    static function loadClass($class_name)//, $dirs = null, $throw = true)
    {
        if (class_exists($class_name, false) || interface_exists($class_name, false))
        {
            return $class_name;
        }
        self::autoload($class_name);
        
        //成功返回类名，失败返回 false
        if (class_exists($class_name,false) || interface_exists($class_name,false)) {
        	return $class_name;
        }
        return false;
    }

    /**
     * 添加一个类搜索路径
     *
     * 如果要使用 Q::loadClass() 载入非 QeePHP 的类，需要通过 Q::import() 添加类类搜索路径。
     *
     * 要注意，Q::import() 添加的路径和类名称有关系。
     *
     * 例如类的名称为 Vendor_Smarty_Adapter，那么该类的定义文件存储结构就是 vendor/smarty/adapter.php。
     * 因此在用 Q::import() 添加 Vendor_Smarty_Adapter 类的搜索路径时，
     * 只能添加 vendor/smarty/adapter.php 的父目录。
     *
     * @code php
     * Q::import('/www/app');
     * Q::loadClass('Vendor_Smarty_Adapter');
     * // 实际载入的文件是 /www/app/vendor/smarty/adapter.php
     * @endcode
     *
     * 由于 QeePHP 的规范是文件名全小写，因此要载入文件名存在大小写区分的第三方库时，
     * 应该指定 import() 方法的第二个参数。
     *
     * @code php
     * Q::import('/www/app/vendor', true);
     * Q::loadClass('Zend_Mail');
     * // 实际载入的文件是 /www/app/vendor/Zend/Mail.php
     * @endcode
     *
     * @param string $dir 要添加的搜索路径
     * @param boolean $case_sensitive 在该路径中查找类文件时是否区分文件名大小写
     */
    static function import($dir, $case_sensitive = false)
    {
        $real_dir = realpath($dir);
        if ($real_dir)
        {
            $dir = rtrim($real_dir, '/\\');
            if (!isset(self::$_class_path[$dir]))
            {
                self::$_class_path[$dir] = $dir;
                self::$_class_path_options[$dir] = $case_sensitive;
            }
        }
    }

    /**
     * 返回指定 class 的唯一实例
     *
     * @param string $class_name
     *
     * @return object
     */
    static function singleton($class_name)
    {
        if (self::has_obj($class_name))
        {
            return self::get_obj($class_name);
        }
        if (method_exists($class_name, 'instance'))
        {
            $args = func_get_args();
            array_shift($args);
            $obj = call_user_func_array(array($class_name, 'instance'), $args);
        }
        else
        {
            $obj = new $class_name();
        }
        return self::set_obj($obj, $class_name);
    }

    /**
     * 用指定名字设置对象
     *
     * @param object $obj
     * @param string $name
     *
     * @return object
     */
    static function set_obj($obj, $name = null)
    {
        if (!is_object($obj))
        {
            // LC_MSG: Type mismatch. $obj expected is object, actual is "%s".
            throw new QException(Q::__('Type mismatch. $obj expected is object, actual is "%s".',
                    gettype($obj)));
        }

        if (empty($name)) $name = get_class($obj);
        $name = strtolower($name);
        return self::$_objects[$name] = $obj;
    }

    /**
     * 取得指定名字的对象
     *
     * @param string $name
     *
     * @return object
     */
    static function get_obj($name)
    {
        $name = strtolower($name);
        if (isset(self::$_objects[$name]))
        {
            return self::$_objects[$name];
        }
        // LC_MSG: No object is set by name "%s".
        throw new QException(Q::__('No object is set by name "%s".', $name));
    }

    /**
     * 确定指定的对象是否存在
     *
     * @param string $name
     *
     * @return bool
     */
    static function has_obj($name)
    {
        return isset(self::$_objects[strtolower($name)]);
    }
    

    /**
     * 读取指定的缓存内容，如果内容不存在或已经失效，则返回 false
     *
     * 在操作缓存数据时，必须指定缓存的 ID。每一个缓存内容都有一个唯一的 ID。
     * 例如数据 A 的缓存 ID 是 data-a，而数据 B 的缓存 ID 是 data-b。
     *
     * 在大量使用缓存时，应该采用一定的规则来确定缓存 ID。下面是一个推荐的方案：
     *
     * <ul>
     *   <li>首先按照缓存数据的性质确定前缀，例如 page、db 等；</li>
     *   <li>然后按照数据的唯一索引来确定后缀，并和前缀一起组合成完整的缓存 ID。</li>
     * </ul>
     *
     * 按照这个规则，缓存 ID 看上去类似 page.news.1、db.members.userid。
     *
     * Q::cache() 可以指定 $policy 参数来覆盖缓存数据本身带有的策略。
     * 具体哪些策略可以使用，请参考不同缓存服务的文档。
     *
     * $backend_class 用于指定要使用的缓存服务对象类名称。例如 QCache_File、QCache_APC 等。
     *
     * @code php
     * $data = Q::cache($cache_id);
     * if ($data === false)
     * {
     *     $data = ....
     *     Q::writeCache($cache_id, $data);
     * }
     * @endcode
     *
     * @param string $id 缓存的 ID
     * @param array $policy 缓存策略
     * @param string $backend_class 要使用的缓存服务
     *
     * @return mixed 成功返回缓存内容，失败返回 false
     */
    static function cache($id, array $policy = null, $backend_class = null)
    {
        static $obj = null;

        if (is_null($backend_class))
        {
            if (is_null($obj))
            {
            	$ini=Q::ini('app_config/CONFIG_CACHE_SETTINGS');
            	$params=array();
            	if (isset($ini[self::ini('runtime_cache_backend')])){
            		$params=$ini[self::ini('runtime_cache_backend')];
            	}
                $obj = self::singleton(self::ini('runtime_cache_backend'),$params);
            }
            return $obj->get($id, $policy);
        }
        else
        {
            $cache = self::singleton($backend_class);
            return $cache->get($id, $policy);
        }
    }

    /**
     * 将变量内容写入缓存，失败抛出异常
     *
     * $data 参数指定要缓存的内容。如果 $data 参数不是一个字符串，则必须将缓存策略 serialize 设置为 true。
     * $policy 参数指定了内容的缓存策略，如果没有提供该参数，则使用缓存服务的默认策略。
     *
     * 其他参数同 Q::cache()。
     *
     * @param string $id 缓存的 ID
     * @param mixed $data 要缓存的数据
     * @param array $policy 缓存策略
     * @param string $backend_class 要使用的缓存服务
     */
    static function writeCache($id, $data, array $policy = null, $backend_class = null)
    {
        static $obj = null;

        if (is_null($backend_class))
        {
            if (is_null($obj))
            {
                $obj = self::singleton(self::ini('runtime_cache_backend'));
            }
            $obj->set($id, $data, $policy);
        }
        else
        {
            $cache = self::singleton($backend_class);
            $cache->set($id, $data, $policy);
        }
    }

    /**
     * 删除指定的缓存内容
     *
     * 通常，失效的缓存数据无需清理。但有时候，希望在某些操作完成后立即清除缓存。
     * 例如更新数据库记录后，希望删除该记录的缓存文件，以便在下一次读取缓存时重新生成缓存文件。
     *
     * @code php
     * Q::cleanCache($cache_id);
     * @endcode
     *
     * @param string $id 缓存的 ID
     * @param array $policy 缓存策略
     * @param string $backend_class 要使用的缓存服务
     */
    static function cleanCache($id, array $policy = null, $backend_class = null)
    {
        static $obj = null;

        if (is_null($backend_class))
        {
            if (is_null($obj))
            {
                $obj = self::singleton(self::ini('runtime_cache_backend'));
            }
            $obj->remove($id, $policy);
        }
        else
        {
            $cache = self::singleton($backend_class);
            $cache->remove($id, $policy);
        }
    }

    /**
     * 对字符串或数组进行格式化，返回格式化后的数组
     *
     * $input 参数如果是字符串，则首先以“,”为分隔符，将字符串转换为一个数组。
     * 接下来对数组中每一个项目使用 trim() 方法去掉首尾的空白字符。最后过滤掉空字符串项目。
     *
     * 该方法的主要用途是将诸如：“item1, item2, item3” 这样的字符串转换为数组。
     *
     * @code php
     * $input = 'item1, item2, item3';
     * $output = Q::normalize($input);
     * // $output 现在是一个数组，结果如下：
     * // $output = array(
     * //   'item1',
     * //   'item2',
     * //   'item3',
     * // );
     *
     * $input = 'item1|item2|item3';
     * // 指定使用什么字符作为分割符
     * $output = Q::normalize($input, '|');
     * @endcode
     *
     * @param array|string $input 要格式化的字符串或数组
     * @param string $delimiter 按照什么字符进行分割
     *
     * @return array 格式化结果
     */
    static function normalize($input, $delimiter = ',')
    {
        if (!is_array($input))
        {
            $input = explode($delimiter, $input);
        }
        $input = array_map('trim', $input);
        return array_filter($input, 'strlen');
    }

    /**
     * 创建一个用户界面控件对象
     *
     * 使用 Q::control() 方法，可以很容易的创建指定类型的用户界面控件对象。
     *
     * @param string $type 用户界面控件对象的类型
     * @param string $id 控件ID
     * @param array $attrs 要传递给控件的附加属性
     *
     *
     * @return QUI_Control_Abstract 创建的用户界面控件对象
     */
    static function control($type, $id = null, $attrs = array())
    {
        $id = empty($id) ? strtolower($type) : strtolower($id);
        $class_name = "Control_{$type}";
        return new $class_name($id, $attrs);
    }

    /**
     * 用于 QeePHP 的类自动载入，不需要由开发者调用
     *
     * @param string $class_name
     */
    static function autoload($class_name)
    {
        global $G_CLASS_FILES;
        $class_name_l = strtolower($class_name);
        if (isset($G_CLASS_FILES[$class_name_l]))
        {
            require Q_DIR . DS . $G_CLASS_FILES[$class_name_l];
            return $class_name_l;
        }
        
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
        foreach (self::$_class_path as $dir)
        {
            $path = $dir .DS. ((self::$_class_path_options[$dir]) ? $filename : $l_filename);
        	if (is_file($path))
            {
                require($path);
                break;
            }
        }
    }

    /**
     * 注册或取消注册一个自动类载入方法
     *
     * 该方法参考 Zend Framework。
     *
     * @param string $class 提供自动载入服务的类
     * @param boolean $enabled 启用或禁用该服务
     */
    static function registerAutoload($class = 'Q', $enabled = true)
    {
        if (!function_exists('spl_autoload_register'))
        {
            require_once Q_DIR . '/qexception.php';
            throw new QException('spl_autoload does not exist in this PHP installation');
        }

        if ($enabled === true)
        {
            spl_autoload_register(array($class, 'autoload'));
        }
        else
        {
            spl_autoload_unregister(array($class, 'autoload'));
        }
    }
	 /**
	 * QeePHP 内部使用的多语言翻译函数
	 *
	 * @return $msg
	 */
	static function __()
	{
	    $args = func_get_args();
	    $msg = array_shift($args);
	    $language = strtolower(Q::ini('error_language'));
	    $messages = array();// Q::loadFile('lc_messages.php', Q_DIR . '/_lang/' . $language, false);
	    if (isset($messages[$msg]))
	    {
	        $msg = $messages[$msg];
	    }
	    array_unshift($args, $msg);
	    return call_user_func_array('sprintf', $args);
	}
}
Q::loadDefaultIni();

require 'q.func.php';