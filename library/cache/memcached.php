<?php
// $Id: memcached.php 1987 2009-01-08 18:03:35Z dualface $

/**
 * 定义 QCache_Memcached 类
 *
 * @link http://qeephp.com/
 * @copyright Copyright (c) 2006-2009 Qeeyuan Inc. {@link http://www.qeeyuan.com}
 * @license New BSD License {@link http://qeephp.com/license/}
 * @version $Id: memcached.php 1987 2009-01-08 18:03:35Z dualface $
 * @package cache
 */

/**
 * QCache_Memcached 使用 memcache 扩展来缓存数据
 *
 * @author YuLei Liao <liaoyulei@qeeyuan.com>
 * @version $Id: memcached.php 1987 2009-01-08 18:03:35Z dualface $
 * @package cache
 */
class QCache_Memcached
{
    /**
     * memcached连接句柄
     *
     * @var resource
     */
    protected $_conn;

    /**
     * 默认的缓存服务器
     *
     * @var array
     */
    protected $_default_server = array(
        /**
         * 缓存服务器地址或主机名
         */
        'host' => '10.127.95.32',

        /**
         * 缓存服务器端口
         */
        'port' => '11211',
    );

    /**
     * 默认的缓存策略
     *
     * @var array
     */
    protected $_default_policy = array(
        /**
         * 缓存服务器配置，参看$_default_server
         * 允许多个缓存服务器
         */
        'servers' => array(),

        /**
         * 是否压缩缓存数据
         */
        'compressed' => false,

        /**
         * 缓存有效时间
         *
         * 如果设置为 0 表示缓存永不过期
         */
        'life_time' => 900,

        /**
         * 是否使用持久连接
         */
        'persistent' => true,
    );

    /**
     * 构造函数
     *
     * @param 缓存策略 $policy
     */
    function __construct(array $policy = null)
    {
        if (!extension_loaded('memcache'))
        {
            throw new QCache_Exception('The memcache extension must be loaded before use!');
        }

        if(is_array($policy))
        {
            $this->_default_policy = array_merge($this->_default_policy, $policy);
        }

        if (empty($this->_default_policy['servers']))
        {
            $this->_default_policy['servers'][] = $this->_default_server;
        }

        $this->_conn = new Memcache();
        foreach ($this->_default_policy['servers'] as $server)
        {
            $result = $this->_conn->addServer($server['host'], $server['port'], $this->_default_policy['persistent']);
            if (!$result)
            {
                throw new QCache_Exception(sprintf('Connect memcached server [%s:%s] failed!', $server['host'], $server['port']));
            }
        }
    }

    /**
     * 写入缓存
     *
     * @param string $id
     * @param mixed $data
     * @param array $policy
     * @return boolean
     */
    function set($id, $data, array $policy = null)
    {
        $compressed = isset($policy['compressed']) ? $policy['compressed'] : $this->_default_policy['compressed'];
        $life_time = isset($policy['life_time']) ? $policy['life_time'] : $this->_default_policy['life_time'];

        $this->_conn->set($id, $data, $compressed ? MEMCACHE_COMPRESSED : 0, $life_time);
    }

    /**
     * 读取缓存，失败或缓存撒失效时返回 false
     *
     * @param string $id
     *
     * @return mixed
     */
    function get($id)
    {
        return $this->_conn->get($id);
    }

    /**
     * 删除指定的缓存
     *
     * @param string $id
     * @return boolean
     */
    function remove($id)
    {
        return $this->_conn->delete($id);
    }

    /**
     * 清除所有的缓存数据
     *
     * @return boolean
     */
    function clean()
    {
        return $this->_conn->flush();
    }
}

