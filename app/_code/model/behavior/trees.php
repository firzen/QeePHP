<?php
// $Id: tree.php 2009-06-27 13:13:13 emudy $

/**
 * 定义Model_Behavior_Trees类
 *
 * @ Behavior
 *
 */
 
 /**
  * Behavior_Trees 实现节点添加、更新、删除前后对节点树结构的一些调整
  *
  * Trees 行为插件支持下列选项设置
  *
  * - scheme_prop：节点操作方案。决定着移动、删除操作对节点树结构的影响
  *
  *   选项可设为 true, false，支持用户自定义动态设置该选项。其中选项为 true 时，操作具有连带性，移动节点时，
  *   连带移动节点及其下所有子孙节点，那么删除则央及子孙节点。
  *
  *   如果要使用自定义的加密方法，可以指定 scheme_prop 设置为一个回调函数，例如：
  *
  *    @code php
  *    'scheme' => array('MyClass', 'callbackfunc');
  *    @endcode
  *
  *   选项默认为 false。
  *   建议用动态设置吧，这样操作比较灵活。同时因为移动和删除共用该属性。当然用户也可以根据自己的需要为删除设置一个选项。
  *
  * - cat_id_prop：指示模型中用来保存节点ID，默认为 cat_id
  *
  * - cat_namd_prop：指示模型中用来保存节点的名称，默认为 name
  *
  * - parent_id_prop：指示模型中用来保存节点的父节点ID，默认为 parent_id
  *
  * - left_prop：指示模型中用来保存节点的左值，默认为 left_value
  *
  * - right_prop：指示模型中用来保存节点的右值，默认为 right_value
  *
  * - created_prop：批示模型中用来保存节点的创建时间，默认为 created
  *
  * - update_prop：指示模型中用来保存节点的更新时间，默认为 updated
  *
  * - error_messages：用于操作在出现异常时保存的提示信息，该项只是简单的在操作中设置了一下，有待完善
  *
  * @version 
  *
  */
class Model_Behavior_Trees extends QDB_ActiveRecord_Behavior_Abstract
{
	/**
	 * 根节点名称
	 */
	private $_rootNodeName = '_#_ROOT_NODE_#_';

	/**
	 * Trees 设置选项信息
	 *
	 * @var array 
	 */
	protected $_settings = array (
	
		/**
		 * 设置移动（删除）的方案，单一移动（删除）节点或者连带移动（删除）子孙节点
		 *
		 * 值为 false 时单一移动（删除）节点，直接子节点上移一级，即接到移动节点的父节点下
		 * 值为 true 时连带移动（删除）子孙节点
		 *
		 * @var boolen
		 */
		'scheme_prop' 		=> false,
		
		/**
		 * 根节点删除选项，默认为 false。为 false 不允许删除根节点
		 */
		'remove_prop'		=> false,
		
		'cat_id_prop' 		=> 'cat_id',
		'cat_name_prop' 	=> 'name',
		'parent_id_prop' 	=> 'parent_id',
		'left_prop' 		=> 'left_value',
		'right_prop' 		=> 'right_value',
		'created_prop' 		=> 'created',
		'updated_prop' 		=> 'updated',
		
		/**
		 * 出现不符合要求时的消息中。有待完善
		 *
		 */
		'error_messages'	=> array(),
	);
		
	/**
	 * 绑定插件
	 */
	function bind()
	{
		$this->_addEventHandler(self::BEFORE_CREATE,	array($this, '_befault_save'));
		$this->_addEventHandler(self::AFTER_DESTROY,  	array($this, '_after_destroy'));
		$this->_addEventHandler(self::BEFORE_UPDATE,  	array($this, '_before_update'));
		
		$this->_addDynamicMethod('getParentNode',     	array($this, 'getParentNode'));	
		$this->_addDynamicMethod('getSubNodes',     	array($this, 'getSubNodes'));
		$this->_addDynamicMethod('getSubTree',			array($this, 'getSubTree'));
		$this->_addDynamicMethod('getPath',				array($this, 'getPath'));
		$this->_addDynamicMethod('getCurrentLevelNodes',	array($this, 'getCurrentLevelNodes'));
		$this->_addDynamicMethod('getAllNodes',			array($this, 'getAllNodes'));
		$this->_addDynamicMethod('getAllTopNodes',		array($this, 'getAllTopNodes'));
		$this->_addDynamicMethod('calcAllChildCount',	array($this, 'calcAllChildCount'));
		
		$this->_addDynamicMethod('checkScheme',     		array($this, 'checkScheme'));
	} 
	
  /**
   * 撤销绑定
   */
	function unbind()
	{
		parent::unbind();
	}
	
	/**
	 * 创建节点前调用该函数。
	 * 根据需要创建根节点、更新左右值等
	 *
	 * @param QDB_ActiveRecord_Abstract $obj
	 */
	function _befault_save(QDB_ActiveRecord_Abstract $obj)
	{
		$lft = $this->_settings['left_prop'];
		$rgt = $this->_settings['right_prop'];
		$prn = $this->_settings['parent_id_prop'];
		
		$dbo = $this->_meta->table->getConn();
		$tbName = $this->_meta->table->getFullTableName();
		
		/**
		 * 父节点ID
		 */
		$parent_id = $obj->{$prn};
		
		if($parent_id)
		{
			/**
			 * 查询父节点信息
			 */
			$parent = $this->getParentNode($obj);
			
			/**
			 * 不存在节点则抛出异常
			 */
			if(!$parent->id())
			{
				throw new QDB_ActiveRecord_ValidateFailedException(array($prn=>'不存在该父节点！'), $obj);
			}
		}
		else
		{
			/**
			 * 检查是否存在根节点
			 */
			$parent = $this->getParentNode($obj, 
					array($this->_settings['cat_name_prop'] => $this->_rootNodeName)
			);
			
			/**
			 *如果不存在根节点，则先创建根节点_#_ROOT_NODE_#_
			 */
			if(!$parent->id())
			{				
				$node = array(
					$this->_settings['cat_name_prop'] => $this->_rootNodeName,
					$this->_settings['parent_id_prop'] => -1,
					$this->_settings['left_prop'] => 1,
					$this->_settings['right_prop'] => 2,
					$this->_settings['created_prop'] => CURRENT_TIMESTAMP,
					$this->_settings['updated_prop'] => CURRENT_TIMESTAMP,
				);
				
				/**
				 * 插入节点的根
				 */
				$this->_meta->table->insert($node);
				
				$parent->{$lft} = $node[$lft];
				$parent->{$rgt} = $node[$rgt];	
			}
				
			/**
			 * 确保父节点为0
			 */
			$parent_id = 0;
		}
		//dump($parent);exit;
		
		/**
		 * 更新节点左右值
		 */
		$sql = "UPDATE {$tbName} 
						SET {$lft} = {$lft} + 2 
						WHERE {$lft} >=" . $parent->{$rgt};
		$dbo->execute($sql);
		$sql = "UPDATE {$tbName} 
						SET {$rgt} = {$rgt} + 2 
						WHERE {$rgt} >=" . $parent->{$rgt};
		$dbo->execute($sql);
		
		$obj->{$prn} = $parent_id;
		$obj->{$lft} = $parent->{$rgt};
		$obj->{$rgt} = $parent->{$rgt} + 1;
	}
	
	/**
	 * 更新前的动作
	 *
	 * 函数功能有：
	 *		
	 *		- 移动节点
	 *
	 * 		- 根据插件的行为选项 scheme_prop 决定是单一移动一个节点还是连带移动其所有子孙节点。
	 *		
	 *			- scheme_prop = false(0) 单一移动节点，其直接子节点将上移到移支节点的父节点下
	 *			- scheme_prop = true(1)  边带移动子孙节点，节点及其下所有子孙节点一起移动到目标节点
	 *
	 *			- scheme_prop默认为false
	 *
	 *		- 更部分节点的左右值。
	 *		
	 * 
	 * @param QDB_ActiveRecord_Abstract $obj
	 */
	function _before_update(QDB_ActiveRecord_Abstract $obj)
	{
		$this->_settings['scheme_prop'] = $this->checkScheme();
		$lft = $this->_settings['left_prop'];
		$rgt = $this->_settings['right_prop'];
		$cid = $this->_settings['cat_id_prop'];
		$prn = $this->_settings['parent_id_prop'];
		$dbo = $this->_meta->table->getConn();
		$tbName = $this->_meta->table->getFullTableName();
		
		$node = $this->_meta->find(array($cid =>$obj->{$cid}))->query();
		
		// 当前节点的父对象ID
		$src_parent_id = $node->{$prn};
		
		// 目标节点对象ID
		$tar_parent_id = $obj->{$prn};
		
		/**
		 * 如果用户尝试将节点挂在自己本身下，抛出异常
		 */
		if($obj->{$prn} == $obj->{$cid})
		{
			throw new QDB_ActiveRecord_ValidateFailedException(array($prn=>'节点不能以自己为挂点！傻蛋'), $obj);
		}
		
		/**
		 * 节点挂点不变
		 */
		if( $src_parent_id == $tar_parent_id )
		{
		}
		
		/**
		 * 移动节点
		 * 
		 * 程序将根据插件的行为设置选项决定如何移动节点
		 *
		 * - scheme_prop: 
		 *		
		 * 		= false(0) 时单一移动节点到目标节点，支持任何方向的移动，包括向子孙节点方向的移动
		 *
		 *		= true(1) 时连带移动子孙节点，除了不允许向子孙节点移动外，可以向任何方面移动
		 *		
		 *	scheme_prop默认为false
		 */
		else
		{
			/**
			 * 如果目标为根，则根ID为1
			 */
			if($obj->{$prn} == 0)
			{
				$obj->{$prn} = 1;
			}
			
			/**
			 * 取得目标节点信息
			 */
			$target = $this->getParentNode($obj);
					
			/**
			 * 单一移动节点，将其直接子节点上移到其父节点下
			 */
			if(!$this->_settings['scheme_prop'])
			{
				/**
				 * 步进值
				 */
				$step = 2;
				
				/**
				 * 移动节点到目标节点的距离
				 */
				$instance = $target->{$rgt} - $node->{$lft};
				
				/**
				 * 先更新目标右值，和左右值大于目标右值的节点
				 */
				$sql = "UPDATE {$tbName} 
							SET {$lft} = {$lft} + 2  
							WHERE {$lft} >= " . $target->{$rgt};
				$dbo->execute($sql);
				$sql = "UPDATE {$tbName} 
								SET {$rgt} = {$rgt} + 2 
								WHERE {$rgt} >=" . $target->{$rgt};
				$dbo->execute($sql);
				
				/**
				 * 当前移动节点的左右值
				 */
				if($instance > 0)
				{
					if($target->{$lft} > $obj->{$lft} && $target->{$rgt} < $obj->{$rgt})
					{
						$curr_src_lv = $node->{$lft};
						$curr_src_rv = $node->{$rgt} + $step;
					}else{
						$curr_src_lv = $node->{$lft};
						$curr_src_rv = $node->{$rgt};
					}
				}else{
					$curr_src_lv = $node->{$lft} + $step;
					$curr_src_rv = $node->{$rgt} + $step;
				}
				
				/**
				 * 节点向左右值比自己左右值低的节点移动时
				 * 当更新目标节点左右值后，移动节点到目标节点的距离将增加$step
				 * 则：$instance = -(abs($instance) + $step)
				 */
				if( $instance < 0 || ($target->{$lft} > $obj->{$lft} && $target->{$rgt} < $obj->{$rgt}) ){
					$instance -= 1;
				}
		
				/**
				 * 更新移动节点及其子孙节点的左右值
				 */
				$sql = "UPDATE {$tbName} 
							SET {$lft} = {$lft} - 1,
							{$rgt} = {$rgt} - 1  
							WHERE {$lft} >= " . $curr_src_lv . "
							AND {$rgt} <= " . $curr_src_rv;
				$dbo->execute($sql);	
				$sql = "UPDATE {$tbName} 
								SET {$prn} = " . $node->{$prn} . "
								WHERE {$prn} = " .$obj->{$cid};
				$dbo->execute($sql);
				
				/**
				 * 再一次更新左右值大于移动节点右值的节点的左右值
				 */
				$sql = "UPDATE {$tbName} 
							SET {$lft} = {$lft} - {$step}  
							WHERE {$lft} > " . $curr_src_rv;
				$dbo->execute($sql);
				$sql = "UPDATE {$tbName} 
								SET {$rgt} = {$rgt} - {$step} 
								WHERE {$rgt} > " . $curr_src_rv;
				$dbo->execute($sql);
				
				/**
				 * 更新节点后的目标节点左右值
				 */
				if($instance > 0 )
				{
					if($target->{$lft} > $obj->{$lft} && $target->{$rgt} < $obj->{$rgt})
					{
						$curr_src_lv = $curr_src_lv + $instance;
						$curr_src_rv = $curr_src_lv + 1;
					}else{
						$curr_src_lv = $curr_src_lv + $instance - $step;
						$curr_src_rv = $curr_src_lv + 1;
					}
				}
				else
				{
					$curr_src_lv = $curr_src_lv + $instance - 1;
					$curr_src_rv = $curr_src_lv + 1;
				}
				
				$obj->{$prn} = $obj->{$prn} == 1 ? 0 : $obj->{$prn};
				$obj->{$lft} = $curr_src_lv;
				$obj->{$rgt} = $curr_src_rv;
			}
			
			/**
			 * 连带移动节点时，其子节点一同移动
			 */
			else
			{
				/**
				 * 连带移动的情况下，节点不能移动到自己的子孙节点下，否则抛出异常
				 */
				if( $target->{$lft} > $obj->{$lft} && $target->{$rgt} < $obj->{$rgt} )
				{
					throw new QDB_ActiveRecord_ValidateFailedException(array($prn=>'节点不能移动到子孙节点下！'), $obj);
				}
				
				/**
				 * 移动节点总数
				 */
				$counts = $this->calcAllChildCount($obj);
				
				/**
				 * 步进值
				 */
				$step = ($counts + 1) * 2;
				
				/**
				 * 移动节点到目标节点的距离
				 */
				$instance = $target->{$rgt} - $node->{$lft};
								
				/**
				 * 先更新目标右值，和左右值大于目标右值的节点
				 */
				$sql = "UPDATE {$tbName} 
							SET {$lft} = {$lft} + {$step}  
							WHERE {$lft} >= " . $target->{$rgt};
				$dbo->execute($sql);
				$sql = "UPDATE {$tbName} 
								SET {$rgt} = {$rgt} + {$step} 
								WHERE {$rgt} >=" . $target->{$rgt};
				$dbo->execute($sql);
				
				/**
				 * 当前移动节点的左右值
				 */
				if($instance > 0)
				{
					$curr_src_lv = $node->{$lft};
					$curr_src_rv = $node->{$rgt};
				}else{
					$curr_src_lv = $node->{$lft} + $step;
					$curr_src_rv = $node->{$rgt} + $step;
				}
			
				/**
				 * 节点向左右值比自己左右值低的节点移动时
				 * 当更新目标节点左右值后，移动节点到目标节点的距离将增加$step
				 * 则：$instance = -(abs($instance) + $step)
				 */
				if($instance < 0) $instance -= $step;
		
				/**
				 * 更新移动节点及其子孙节点的左右值
				 */
				$sql = "UPDATE {$tbName} 
							SET {$lft} = {$lft} + {$instance},
							{$rgt} = {$rgt} + {$instance}  
							WHERE {$lft} >= " . $curr_src_lv . "
							AND {$rgt} <= " . $curr_src_rv;
				$dbo->execute($sql);	
		
				/**
				 * 再一次更新左右值大于移动节点右值的节点的左右值
				 */
				$sql = "UPDATE {$tbName} 
							SET {$lft} = {$lft} - {$step}  
							WHERE {$lft} >= " . $curr_src_rv;
				$dbo->execute($sql);
				$sql = "UPDATE {$tbName} 
								SET {$rgt} = {$rgt} - {$step} 
								WHERE {$rgt} >=" . $curr_src_rv;
				$dbo->execute($sql);
			
				/**
				 * 更新节点后的目标节点左右值
				 */
				if($instance > 0)
				{				
					$curr_src_lv = $curr_src_lv + $instance - $step;
					$curr_src_rv = $curr_src_rv + $instance - $step;
				}else{
					$curr_src_lv = $curr_src_lv + $instance;
					$curr_src_rv = $curr_src_rv + $instance;	
				}	
				$obj->{$prn} = $obj->{$prn} == 1 ? 0 : $obj->{$prn};
				$obj->{$lft} = $curr_src_lv;
				$obj->{$rgt} = $curr_src_rv;
			}
		}
	}
	
	/**
	 * 数据库删除节点记录后调用该函数
	 *
	 * 函数功能有：
	 *
	 * 		- 根据插件的行为选项决定是单一删除一个节点还是连带删除节点及其所有子孙节点。
	 *		
	 *				- scheme_prop = false(0) 单一删除节点
	 *				- scheme_prop = true(1)  边带删除子孙节点
	 *
	 *				- scheme_prop默认为false
	 *
	 *		- 删除节点后更新节点的左右值。
	 *		
	 * 
	 * @param QDB_ActiveRecord_Abstract $obj
	 */
	function _after_destroy(QDB_ActiveRecord_Abstract $obj)
	{
		$this->_settings['scheme_prop'] = $this->checkScheme();
  		$lft = $this->_settings['left_prop'];
		$rgt = $this->_settings['right_prop'];
		$cid = $this->_settings['cat_id_prop'];
		$prn = $this->_settings['parent_id_prop'];
		
		$dbo = $this->_meta->table->getConn();
		$tbName = $this->_meta->table->getFullTableName();
		
		/**
		 * 单一删除节点
		 * 当删除节点时，节点下的直接子节点将自动上移到该节点的父节点下，其他节点挂点不变
		 * 
		 * @var $this->_settings['scheme_prop'] = false
		 */
		if ( !$this->_settings['scheme_prop'] )
		{
			/**
			 * 更新要删除节点下所有子孙节点的左右值
			 */
			$sql = "UPDATE {$tbName} 
							SET {$lft} = {$lft} - 1,
							{$rgt} = {$rgt} - 1 
							WHERE {$lft} >= " . $obj->{$lft} . "
							AND {$rgt} <= " . $obj->{$rgt};
			$dbo->execute($sql);
			$sql = "UPDATE {$tbName} 
							SET {$prn} = " . $obj->{$prn} . "
							WHERE {$prn} = " . $obj->{$cid};
			$dbo->execute($sql);
			
			/**
			 * 根据要删除的节点左右值，更新其他节点的左右值
			 */
			$sql = "UPDATE {$tbName} 
							SET {$lft} = {$lft} - 2 
							WHERE {$lft} >" . $obj->{$rgt};
			$dbo->execute($sql);
			$sql = "UPDATE {$tbName} 
							SET {$rgt} = {$rgt} - 2 
							WHERE {$rgt} >" . $obj->{$rgt};
			$dbo->execute($sql);
		}
		
		/**
		 * 连带删除子孙节点
		 * 当删除一个节点时，同时也删除该节点下的所有子孙节点
		 */
		 else
		 {
		 	/**
			 * 节点的子孙节点总数
			 */
			$counts = $this->calcAllChildCount($obj);
			
			/**
			 * 步进值
			 * 
			 * @var $step 更新节点左右值的变动数
			 */
			$step = ($counts + 1) * 2;
			
			/**
			 * 删除节点下的所有子孙节点
			 */
			$sql = "DELETE FROM {$tbName} 
							WHERE {$lft} > " . $obj->{$lft} . " 
							AND {$rgt} < " . $obj->{$rgt};
			$dbo->execute($sql);
			
			/**
			 * 根据删除节点的左右值更新部分节点的左右值
			 */
			$sql = "UPDATE {$tbName} 
							SET {$lft} = {$lft} - {$step}  
							WHERE {$lft} >= " . $obj->{$rgt};
			$dbo->execute($sql);
			$sql = "UPDATE {$tbName} 
							SET {$rgt} = {$rgt} - {$step} 
							WHERE {$rgt} >" . $obj->{$rgt};
			$dbo->execute($sql);
		}
	}
	
	/**
	 * 取得指定节点的父节点
	 *
	 * @param QDB_ActiveRecord_Abstract $obj
	 *
	 * @param $args array
	 */
	function getParentNode(QDB_ActiveRecord_Abstract $obj, $args = null)
	{
		$cat_id = $this->_settings['parent_id_prop'];
		if($args === null)$args = array( reset($this->_meta->idname) => $obj->{$cat_id} );
		return $this->_meta->find($args)->query();
	}
	
	/**
	 * 取得指定节点的所有直接子节点
	 *
	 * @param QDB_ActiveRecord_Abstract $obj
	 *
	 * @return 
	 */
	function getSubNodes(QDB_ActiveRecord_Abstract $obj)
	{
		$prn = $this->_settings['parent_id_prop'];
		$cat_id = $this->_settings['cat_id_prop'];
		return $this->_meta->find(array($prn => $obj->{$cat_id}))->getAll();
	}
	
	/**
     * 返回指定节点为根的整个子节点树
     *
     * @param QDB_ActiveRecord_Abstract $obj
     *
     * @return array
     */
    function getSubTree(QDB_ActiveRecord_Abstract $obj)
    {
    	$lft = $this->_settings['left_prop'];
    	$rgt = $this->_settings['right_prop'];
    	return $this->_meta->find("{$lft} BETWEEN ? AND ?", $obj->{$lft}, $obj->{$rgt})
        			->all()->order("{$lft} ASC")
        			->query();
    }
    
    /**
     * 返回根节点到指定节点路径上的所有节点
     *
     * 返回的结果不包括“_#_ROOT_NODE_#_”根节点各个节点同级别的其他节点。
     * 结果集是一个二维数组，可以用 array_to_tree() 函数转换为层次结构（树型）。
     *
     * @param QDB_ActiveRecord_Abstract $obj
     *
     * @return array
     */
    function getPath(QDB_ActiveRecord_Abstract $obj)
    {
    	$lft = $this->_settings['left_prop'];
    	$rgt = $this->_settings['right_prop'];
    	
        $rowset = $this->_meta->find("{$lft} < ? AND {$rgt} > ? and {$this->_settings['parent_id_prop']} >= 0", $obj->{$lft},$obj->{$rgt})
        			->all()->order("{$lft} ASC")
        			->query();
        			
        if (is_array($rowset))
        {
            array_shift($rowset);
        }
        return $rowset;
    }

     /**
     * 获取指定节点同级别的所有节点
     *
     * @param QDB_ActiveRecord_Abstract $obj
     *
     * @return array
     */
    function getCurrentLevelNodes(QDB_ActiveRecord_Abstract $obj)
    {
        $prn = $this->_settings['parent_id_prop'];
        $lft = $this->_settings['left_prop'];
        return $this->_meta->find("{$prn} = ?", $obj->{$prn})
        			->all()->order("{$lft} ASC")
        			->query();
    }
    
    /**
     * 取得所有节点
     *
     * @param QDB_ActiveRecord_Abstract $obj
     *
     * @return array
     */
    function getAllNodes(QDB_ActiveRecord_Abstract $obj)
    {
    	$lft = $this->_settings['left_prop'];
        return $this->_meta->find("{$lft} = ?", $obj->{$lft})
        			->all()->order("{$lft} ASC")
        			->query();
    }

    /**
     * 获取所有顶级节点（即 _#_ROOT_NODE_#_ 的直接子节点）
     *
     * @param QDB_ActiveRecord_Abstract $obj
     *
     * @return array
     */
    function getAllTopNodes(QDB_ActiveRecord_Abstract $obj)
    {
        $prn = $this->_settings['parent_id_prop'];
        $lft = $this->_settings['left_prop'];
        return $this->_meta->find("{$prn} = ?", 0)
        			->all()->order("{$lft} ASC")
        			->query();
    }
		
	/**
	 * 计算指定节点下的所有子孙节点总数
	 *
	 * @param QDB_ActiveRecord_Abstract $obj
	 *
	 * @return int
	 */
	function calcAllChildCount(QDB_ActiveRecord_Abstract $obj)
	{
		$lft = $this->_settings['left_prop'];
		$rgt = $this->_settings['right_prop'];
		return intval(($obj->{$rgt} - $obj->{$lft} - 1) / 2);
	}
	
	/**
	 * 返回指定操作方案
	 */
	function checkScheme()
	{
		$scheme = $this->_settings['scheme_prop'];
		if(is_array($scheme))
		{
			return call_user_func($scheme);
		}
		else
		{
			return $scheme;
		}
	}
}

/**
 * 示例：（移动节点）
 *
 * Controller 中添加 Edit 方法：
 *
 * @code php
 *	function actionEdit()
 *	{
 *	    // 查询指定 ID，并且其所有者是当前用户的任务（禁止修改他人的任务）
 *	    $cat = Category::find('cat_id = ?',$this->_context->cat_id)->query();
 *	 
 *	    if (!$cat->id())
 *	    {
 *	        // 如果任务的 ID 无效，视图修改的任务不存在或者不是当前用户创建的
 *	        return $this->_redirect(url('categories/index'));
 *	    }
 *	 
 *	    // 构造表单对象
 *	    $form = new Form_Category(url('categories/edit'));
 *		   
 *	    // 添加一个隐藏字段到表单
 *	    $form->add(QForm::ELEMENT, 'cat_id', array('_ui' => 'hidden'));
 *	 
 *	    if ($this->_context->isPOST() && $form->validate($_POST))
 *	    {
 *	        // changeProps() 方法可以批量修改 cat 对象的属性，但不会修改只读属性的值
 *	        try
 *	        {
 *		        $cat->changeProps($form->values());
 *		        
 *		        // 设置移动方案
 *  	        $cat->setScheme($this->_context->scheme_prop);
 *		        
 *		        // 保存并重定向浏览器
 *		        $cat->save();
 *  	       	return $this->_redirect(url('categories/index'));
 *	       	}
 *	       	catch (QDB_ActiveRecord_ValidateFailedException $ex)
 *			{
 *  			$form['name']->invalidate($ex);
 *			}
 *	    }
 *	    elseif (!$this->_context->isPOST())
 *	    {
 *	        // 如果不是 POST 提交，则把对象值导入表单
 *	        $form->import($cat);
 *	    }
 *	 	$default_options = array('cat_id' => 0, 'name' => '根分类');
 *		
 *		$cats = Helper_Array::sortByCol(
 *							Category::find('left_value > ?', 1)
 *							->asArray()
 *							->getAll(), 
 *							'left_value', SORT_ASC);
 *			
 *		array_unshift($cats, $default_options);
 *			
 *		$parent = Helper_Array::toHashMap($cats, 'cat_id', 'name');
 *			
 *		$form['parent_id']->items = $parent;
 *		$form->add(QForm::ELEMENT, 'scheme_prop',array('_ui'=>'radiogroup','_label'=>'方案','items'=>array(0=>'单一',1=>'连带')));
 *	    
 *	    $this->_view['form'] = $form;
 *	    
 *	    // 重用 create 动作的视图
 *	    $this->_viewname = 'create';
 *	}
 * @endcode
 *
 *
 * Model (Category) 中设置插件，添加代码：
 *
 * @code php
 *  
 *	 // 操作方案
 *	static private $_scheme;
 *	
 *	// 设置操作方案
 *	function setScheme($scheme)
 *	{
 *		self::$_scheme = $scheme;
 *	}
 *	
 *	//返回操作方案
 *	static function getScheme()
 *	{
 *		return self::$_scheme;
 *	}
 *
 * 	static function __define()
 *   {
 *       return array
 *       (
 *  		// 指定该 ActiveRecord 要使用的行为插件
 *  		'behaviors' => 'trees',
 *
 *  		// 指定行为插件的配置
 *   		'behaviors_settings' => array
 *    		(
 *      		# '插件名' => array('选项' => 设置),
 *     			'trees' => array (
 *					 // 自定义方法，根据用户的选择动态设置，可以直接设置为 true 或 false
 *              	'scheme_prop' => array('Category','getScheme'),
 *
 *					'left_prop' => 'left_value',
 *					'right_prop' => 'right_value',
 *				),
 *     		),
 *       ......
 *		),
 *	}
 *
 * @endcode
 *
 * yaml配置：
 *
 * @code txt
 *  name:
 *    _ui: textbox
 *	  _label: "分类"
 *  parent_id: 
 *	  _ui: dropdownlist
 *	  _label: "所属"
 *
 * @endcode
 *
 */