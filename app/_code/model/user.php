<?php
/**
 * User 封装来自 user 数据表的记录及领域逻辑
 */
class User extends QDB_ActiveRecord_Abstract
{

    /**
     * 返回对象的定义
     *
     * @static
     *
     * @return array
     */
    static function __define()
    {
        return array
        (

            // 用什么数据表保存对象
            'table_name' => 'users',

            // 指定数据表记录字段与对象属性之间的映射关系
            // 没有在此处指定的属性，QeePHP 会自动设置将属性映射为对象的可读写属性
            'props' => array
            (
            	'password' => array('setter' => '_password_setter'),
            ),
        );
    }
    /**
     * password Setter 修改模型password值自动加密
     * @param string $val
     */
    function _password_setter($val){
    	$this->_props['password']=self::passwordEncode($val);
    	$this->willChanged('password');
    }
    /**
     * 密码加密实现函数
     * @param string $val
     * @return string
     */
    static function passwordEncode($val){
    	return md5($val);
    }
    /**
     * 开启一个查询，查找符合条件的对象或对象集合
     *
     * @static
     *
     * @return QDB_Select
     */
    static function find()
    {
        $args = func_get_args();
        return QDB_ActiveRecord_Meta::instance(__CLASS__)->findByArgs($args);
    }

    /**
     * 返回当前 ActiveRecord 类的元数据对象
     *
     * @static
     *
     * @return QDB_ActiveRecord_Meta
     */
    static function meta()
    {
        return QDB_ActiveRecord_Meta::instance(__CLASS__);
    }


/* ------------------ 以上是自动生成的代码，不能修改 ------------------ */
    /**
     * 验证用户名密码是否正确
     * @param string $username
     * @param string $password
     * @return User
     */
    static function validateLogin($username,$password){
    	$user=self::find('username =?',$username )->getOne();
    	if ($user->isNewRecord()) {
    		return false;
    	}
    	if (!$user->checkPassword($password)){
    		return false;
    	}
    	return $user;
    }
    /**
     * 检查指定的密码是否与当前用户的密码相符
     * @param string $password
     * @return boolean
     */
    function checkPassword($password){
    	return self::passwordEncode($password) == $this->password;
    }
    /**
     * 修改当前用户的密码
     * @param string $password
     */
    function changePassword($password){
    	$this->password=$password;
    	$this->save();
    }
    /**
     * 创建任务
     * @param string $subject
     * @param string $description
     * @return Task
     */
    function createTask($subject,$description){
    	$task=new Task(array(
    		'subject'=>$subject,
    		'description'=>$description
    		));
    	$task->owner_id=$this->user_id;
    	return $task;
    }
}

class UserException extends QException{}