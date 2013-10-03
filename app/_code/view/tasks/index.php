<?PHP $this->_extends('_layouts/default_layout'); ?>

<?PHP $this->_block('title');?>我的任务<?PHP $this->_endblock();?>

<?PHP $this->_block('contents');?>
<div class="tasks">

  <h1>我的任务</h1>

  <?php foreach ($tasks as $task): ?>

  <h2><a href="<?php echo url('tasks/edit', array('task_id' => $task->id())); ?>"><?php echo h($task->subject); ?></a></h2>

  <p class="meta">
    <?php if ($task->is_completed): ?>
    <em>已经在 <?php echo date('m-d H:i', $task->completed_at); ?> 完成该任务</em>
    <?php else: ?>
    <strong>添加日期：<?php echo date('m-d H:i', $task->created); ?></strong>
    <?php endif; ?>
    , <a href="<?php echo url('tasks/delete', array('task_id' => $task->id())); ?>" onclick="return confirm('您确定要删除该任务吗?');">删除</a>
  </p>

  <?php if ($task->description): ?>
  <p class="description">
    <?php echo nl2br(h($task->description)); ?>
  </p>
  <?php endif; ?>

  <hr />

  <?php endforeach; ?>
</div>
<?php echo Q::control('pagination','pagination-task-index',array('pagination'=>$pagination))?>
    
<?PHP $this->_endblock();?>

