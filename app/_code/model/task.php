<?php
/**
 * Task 封装来自 task 数据表的记录及领域逻辑
 */
class Task extends QDB_ActiveRecord_Abstract
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
            'table_name' => 'tasks',

            // 指定数据表记录字段与对象属性之间的映射关系
            // 没有在此处指定的属性，QeePHP 会自动设置将属性映射为对象的可读写属性
            'props' => array
            (
            	'is_completed' => array('readonly' => true),
            	'completed_at' => array('readonly' => true),
            	'owner' => array(QDB::BELONGS_TO => 'User', 'source_key' => 'owner_id'),
            ),
        	'validations' => array
        	(
        		'subject' => array
        		(
        			array('not_empty', '任务主题不能为空'),
        			array('max_length', 180, '任务主题不能超过 60 个汉字或 180 个字符'),
        		),
        	),
        	'create_autofill' => array
        	(
        		// 新建任务的 is_completed 状态总是为 false
        		'is_completed' => false,
        		// 新建任务的 completed_at 值总是为 null
        		'completed_at' => null,
        		//自动填充修改和创建时间
        		'created'=>self::AUTOFILL_TIMESTAMP,
        		'updated'=>self::AUTOFILL_TIMESTAMP
        	),
        	'update_autofill'=>array(
        		'updated'=>self::AUTOFILL_TIMESTAMP
        	),
        	'attr_accessible' => 'subject, description',
        );
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
     * 明确修改任务的状态
     * @param boolean $completed
     * @return Task
     */
    function completed($completed){
    	$completed = (bool)$completed;
    	if ($completed && !$this->is_completed)
    	{
    		// 如果任务状态从“未完成”变成“已完成”，则保存完成任务的时刻
    		// changePropForce() 可以强制改变一个只读属性的值
    		$this->changePropForce('completed_at', time());
    	}
    	elseif (!$completed)
    	{
    		// 如果任务状态设置为“未完成”，则清理掉 completed_at 记录的时间
    		$this->changePropForce('completed_at', null);
    	}
    	$this->changePropForce('is_completed', $completed);
    	return $this;
    }
}

