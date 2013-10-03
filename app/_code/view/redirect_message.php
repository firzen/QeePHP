<?php $this->_extends('_layouts/default_layout'); ?>
<?php $this->_block('contents'); ?>
<div class="alert">
	<h3 ><?php echo $message_caption; ?></h3>
	
	<p style="color:#333333; font-size: 14px;  font-weight:700; ">
	  <?php echo nl2br(h($message_body)); ?>
	</p>
	<p>
	  <a href="<?php echo $redirect_url; ?>">如果您的浏览器没有自动跳转，请点击这里</a>
	</p>
	
	<?php if (!empty($redirect_url)):?>
		<script type="text/javascript">
		setTimeout("window.location.href ='<?php echo $redirect_url; ?>';", <?php echo $redirect_delay * 1000; ?>);
		</script>
	<?php endif;?>
</div>

<?php echo $hidden_script; ?>


<?php $this->_endblock(); ?>

