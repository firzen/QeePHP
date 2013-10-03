<?php
class Controller_Tasks extends Controller_Abstract{
	function actionIndex()
	{
		$user=$this->_app->currentUserObject();
		$tasks=Task::find('owner_id=?',$user->id())
			//分页
			->limitPage(request('page',1),5)
			->fetchPagination($this->_view['pagination'])
			//获取数据
			->getAll();
		$this->_view['tasks']=$tasks;
	}
	function actionCreate(){
		if (request_is_post()){
			// 通过应用程序对象获得当前用户对象
			$user = $this->_app->currentUserObject();
	
			// 通过用户对象创建任务
			try {
				$task = $user->createTask(request('subject'), request('description'));

				// 保存并重定向浏览器
				$task->save();
				return $this->_redirect(url('tasks/index'));
				
			}catch (Exception $ex){
				$this->_view['errors']=$ex->getMessage();
			}
		}
	}
	function actionEdit(){
		$user=$this->_app->currentUserObject();
		// 查询指定 ID，并且其所有者是当前用户的任务（禁止修改他人的任务），注意getOne会一直返回Task对象，如果不存在会返回新的Task对象
		$task=Task::find('task_id =? and owner_id =?',request('task_id'),$user->id())->getOne();
		
		// 如果任务的不存在，视图修改的任务不存在或者不是当前用户创建的
		if ($task->isNewRecord()){
			return $this->_redirectMessage('对不起', '任务不存在或已被删除', url('tasks/index'));
		}
		if (request_is_post()){
			// 直接通过表单赋值
			$task->changeProps($_POST);
			$task->subject=post('subject');
			$task->save();
			// 根据是否选中“已完成”检查框来设置任务的状态
	        $task->completed(request('is_completed',false));
	
	        // 保存并重定向浏览器
	        $task->save();
			return $this->_redirect(url('tasks/index'));
		}
		$this->_view['task']=$task;
	}
	function actionDelete()
	{
		// destroyWhere() 方法的参数和 find() 方法完全一致
		$user=$this->_app->currentUserObject();
		Task::meta()->destroyWhere('task_id = ? AND [owner.user_id] = ?',request('task_id'),$user->id());
	
		return $this->_redirectMessage(
			'删除成功',
			'您已经成功删除了一个任务',
			url('tasks/index')
		);
	}
}