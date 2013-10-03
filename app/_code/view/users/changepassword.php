<?php //布局设定 ，参考 view/_layouts下面的文件 ?>
<?PHP $this->_extends('_layouts/default_layout'); ?>
<?PHP $this->_block('title');?>
    <?php //head title 部分 ?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('head');?>
    <?php //head 部分 ?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('contents');?>
<div>
<form name="form_user" id="form_user" action="<?php echo url('users/changePassword'); ?>" method="post">
  <fieldset>
    <p>
      <label for="oldpass">旧密码</label>
      <input type="password" name="oldpass" id="oldpass" />
      <?php if (@count($errors['oldpass'])){echo implode(',', $errors['oldpass']);}?>
    </p>

    <p>
      <label for="newpass">新密码</label>
      <input type="password" name="newpass" id="newpass" />
      <?php if (@count($errors['newpass'])){echo implode(',', $errors['newpass']);}?>
    </p>
    <p>
      <label for="newpass_repeat">再次输入新密码</label>
      <input type="password" name="newpass_repeat" id="newpass_repeat" />
      <?php if (@count($errors['newpass_repeat'])){echo implode(',', $errors['newpass_repeat']);}?>
    </p>
    <p>
      <input type="submit" name="Submit" value="提交" />
    </p>
    </fieldset>
    </form>
</div>
    
<?PHP $this->_endblock();?>

