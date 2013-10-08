<?php
// $Id$

/**
 * @file
 * 定义 Behavior_Tree 类
 *
 * @ingroup behavior
 *
 * @{
 */

/**
 * Behavior_Tree 使用改进型先根遍历算法存储树状结构
 */
class Model_Behavior_Tree extends QDB_ActiveRecord_Behavior_Abstract
{

    /**
     * 设置
     *
     * @var array
     */
    protected $_settings = array
    (
        //! 存储左值的属性
        'prop_left'      => 'left_value',
        //! 存储右值的属性
        'prop_right'     => 'right_value',
        //! 父对象 ID 属性
        'prop_parent_id' => 'parent_id',
        //! 父对象映射为对象的什么属性
        'parent_node_mapping' => 'parent',
        //! 子对象映射为对象的什么属性
        'child_nodes_mapping' => 'children',
        //! 从根到本节点的路径 @return QCol
        'func_get_parent_path' => 'getParentPath',
        //! 当前节点下的直接和间接从属节点
        'func_get_sub_tree' => 'getSubTree',
        //! 当前节点下的直接和间接从属节点的 id @return array
        'func_get_sub_tree_ids' => 'getSubTreeIds',
        //! 直接或间接从属于当前节点的节点数目 @return int
        'func_get_sub_tree_count' => 'getSubTreeCount',
        //! 与当前节点同级的节点  @return QCol
        'func_get_sibling_nodes' => 'getSiblingNodes',
    );
    protected $primary_key;

    /**
     * 绑定插件
     */
    function bind()
    {
        $this->primary_key=reset($this->_meta->idname);
        $config = array(
            'target_key' => $this->_settings['prop_parent_id'],
            'assoc_params' => '',
            'assoc_class' => $this->_meta->class_name,
        );
        $this->_meta->addAssoc($this->_settings['child_nodes_mapping'], QDB::HAS_MANY, $config);
//        $config = array(
//            'source_key' => $this->_settings['prop_parent_id'],
//            'target_key' => $this->primary_key,
//            'assoc_params' => '',
//            'assoc_class' => $this->_meta->class_name,
//        );
//        $this->_meta->addAssoc($this->_settings['parent_mapping'], QDB::BELONGS_TO, $config);

//        $this->_addEventHandler(self::BEFORE_CREATE,  array($this, '_before_create'));
//        $this->_addEventHandler(self::AFTER_DESTROY,  array($this, '_after_destroy'));

        $this->_setPropGetter($this->_settings['parent_node_mapping'],array($this,'getParentNode'));
        $this->_addDynamicMethod($this->_settings['func_get_parent_path'],array($this,'getParentPath'));
        $this->_addDynamicMethod($this->_settings['func_get_sub_tree'],array($this,'getSubTree'));
        $this->_addDynamicMethod($this->_settings['func_get_sub_tree_ids'],array($this,'getSubTreeIds'));
        $this->_addDynamicMethod($this->_settings['func_get_sibling_nodes'],array($this,'getSiblingNodes'));
        $this->_addDynamicMethod('getBottomNodes',array($this,'getBottomNodes'));
        
    }

    /**
     * 撤销绑定
     */
    function unbind()
    {
    	parent::unbind();
        $this->_meta->removeAssoc($this->_settings['child_nodes_mapping']);
        $this->_meta->removeAssoc($this->_settings['parent_node_mapping']);
    }
	function getBottomNodes(QDB_ActiveRecord_Abstract $obj,$limit=0){
		$select=$this->_meta->find('right_value-left_value  =1 and left_value >? and right_value<?',$obj->left_value,$obj->right_value);
		if ($limit){
			$select->top($limit);
		}
		return $select->getAll();
	}
    /**
     * 在数据库中创建 ActiveRecord 对象前调用
     *
     * @param QDB_ActiveRecord_Abstract $obj
     */
    function _before_create(QDB_ActiveRecord_Abstract $obj)
    {
//        /**
//         * 创建一个新节点时，需要更新其他节点的左值和右值
//         */
//        $rgt_pn = $this->settings['right_prop'];
//        $lft_pn = $this->settings['left'];
//        $pid_pn = $this->settings['prop_parent_id'];
//
//        $parent = $this->getParentNode($obj);
//        if ($parent->id())
//        {
//            /**
//             * 设定当前对象的左值和右值
//             */
//            $rgt = $parent->{$rgt_pn};
//            $obj->{$lft_pn} = $rgt;
//            $obj->{$rgt_pn} = $rgt + 1;
//
//            /**
//             * 根据父节点的左值和右值更新其他节点的左值和右值
//             */
//            $row = array($lft_pn => new QDB_Expr("[{$lft_pn}] + 2"));
//            $this->_meta->updateWhere($row, "[{$lft_pn}] >= ?", $rgt);
//
//            $row = array($rgt_pn => new QDB_Expr("[{$rgt_pn}] + 2"));
//            $this->_meta->updateWhere($row, "[{$rgt_pn}] >= ?", $rgt);
//        }
//        else
//        {
//            $obj->{$pid_pn} = 0;
//            $obj->{$lft_pn} = 1;
//            $obj->{$rgt_pn} = 2;
//        }
    }

    /**
     * 在数据库中删除记录后调用
     *
     * @param QDB_ActiveRecord_Abstract $obj
     */
    function _after_destroy(QDB_ActiveRecord_Abstract $obj)
    {
//        $rgt_pn = $this->_settings['right_prop'];
//        $lft_pn = $this->_settings['left_prop'];
//
//        /**
//         * 更新其他节点的左值和右值
//         */
//        $row = array($lft_pn => new QDB_Expr("[{$lft_pn}] - 2"));
//        $this->_meta->updateWhere($row, "[{$lft_pn}] > ?", $obj->{$lft_pn});
//
//        $row = array($rgt_pn => new QDB_Expr("[{$rgt_pn}] - 2"));
//        $this->_meta->updateWhere($row, "[{$rgt_pn}] > ?", $obj->{$rgt_pn});
    }
    function getParentNode(QDB_ActiveRecord_Abstract $obj){
        return $this->_meta->find('['.$this->primary_key.']=?',$obj->{$this->_settings['prop_parent_id']})->query();
    }
    function getSiblingNodes(QDB_ActiveRecord_Abstract $obj){
        return $this->_meta->find("[{$this->_settings['prop_parent_id']}] =?",$obj->{$this->_settings['prop_parent_id']})->getAll();
    }
    function getParentPath (QDB_ActiveRecord_Abstract $obj){
        return $this->_meta->find("[{$this->_settings['prop_left']}] <? And [{$this->_settings['prop_right']}] >?",$obj->{$this->_settings['prop_left']},$obj->{$this->_settings['prop_right']})->getAll();
    }
    function getSubTree(QDB_ActiveRecord_Abstract $obj){
        return $this->_meta->find("[{$this->_settings['prop_left']}] > ? and [{$this->_settings['prop_right']}] <?",$obj->{$this->_settings['prop_left']},$obj->{$this->_settings['prop_right']})->getAll();
    }
    function getSubTreeIds(QDB_ActiveRecord_Abstract $obj){
        return Helper_Array::getCols($this->_meta->find("[{$this->_settings['prop_left']}] > ? and [{$this->_settings['prop_right']}] <?",$obj->{$this->_settings['prop_left']},$obj->{$this->_settings['prop_right']})->getAll(),$this->primary_key);
    }
    function getSubTreeCount(QDB_ActiveRecord_Abstract $obj){
        return $this->_meta->find("[{$this->_settings['prop_left']}] > ? and [{$this->_settings['prop_right']}] <?",$obj->{$this->_settings['prop_left']},$obj->{$this->_settings['prop_right']})->getCount();
    }
}
