<?php //布局设定 ，参考 view/_layouts下面的文件 ?>
<?PHP $this->_extends('_layouts/default_layout'); ?>

<?PHP $this->_block('contents');?>
<form name="form_user" id="form_user" 
    action="<?php echo url('users/register'); ?>" method="post">

  <fieldset>
    <p>
      <label for="username">用户名</label>
      <input type="text" name="username" id="username" />
      <?php if (@count($errors['username'])){echo implode(',', $errors['username']);}?>
    </p>

    <p>
      <label for="password">密码</label>
      <input type="password" name="password" id="password" />
      <?php if (@count($errors['password'])){echo implode(',', $errors['password']);}?>
    </p>
	<p>
	  <label for="imgcode">验证码</label>
	  <input type="text" name="imgcode" >
	  <img alt="" src="<?php echo url('users/imgcode')?>">
	  <?php if (@count($errors['imgcode'])){echo implode(',', $errors['imgcode']);}?>
	</p>
    <p>
      <input type="submit" name="Submit" value="提交" />
    </p>

  </fieldset>

</form>
<?PHP $this->_endblock();?>

