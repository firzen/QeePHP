<?php
/**
 * @defgroup core 框架核心
 * 
 * 框架核心是所有 QeePHP 组件依赖的基础服务，不管应用程序以何种方式使用 QeePHP，都需要载入框架核心文件 q.php。
 * 
 * 框架核心载入后，将完成下列工作：
 * - 定义名为 Q_VERSION 的常量，指示 QeePHP 的版本号；
 * - 定义名为 Q_DIR 的常量，指示 QeePHP 框架文件的实际存储位置；
 * - 定义名为 DS 的常量。该常量是 PHP 内置常量 DIRECTORY_SEPARATOR 的所写名；
 * - 调用 spl_autoload_register() 注册一个自动类载入服务，以便能够自动加载需要的 QeePHP 类文件；
 * - 载入 QeePHP 框架的默认设置。
 * 
 * 上述工作完成后，应用程序即可使用 QeePHP 框架提供的各项服务。
 * 
 * 示例：
 * @code
 * require 'qeephp/q.php';
 * @endcode
 */
