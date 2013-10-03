<?PHP $this->_extends('_layouts/sexy_layout'); ?>
<?PHP $this->_block('title');?>
	<?php echo '绑定eBay账号'?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('head');?>
<script>
	function checkEbayAccount()
	{
		if($("#ebay_account").val() == '')
		{
			alert('请选择Ebay账号');
			return false;
		}
		return true;
	}
</script>

<?PHP $this->_endblock();?>
<?PHP $this->_block('contents');?>
<form action="" method="post" name="bata">
	<input type="hidden" name="appkeyid" value="<?php echo $baseuserapp->appkeyid?>">
	<div class="lb_1">
		<h3>给应用绑定eBay账号</h3>
		<table class="table table-striped table-bordered table-condensed"  id="lb_1">
			<tr>
				<td>应用名: </td>
				<td><?php echo $baseuserapp->app->appname?></td>
			</tr>
			
			<tr>
				<td>eBay账号</td>
				<td>
					<?php 
						Q::control('dropdownlistwithempty', 'ebay_account', array(
							'items' => $baseebayuser,
							'value' => request('ebay_account'),
							'style' => 'width:150px',
						))->display();
					?>
                    &nbsp;&nbsp;
                    <?php if(isset($error['account_exist'])):?>
                    	<font color="#FF0000"><?php echo $error['account_exist'];?></font>
                    <?php endif;?>
				</td>
			</tr>
			
			<tr>
				<td>有效期</td>
				<td><?php echo date("Y-m-d", $baseuserapp->expire)?></td>
			</tr>
			
			<tr>
				<td colspan="2">
					<div style="float:right;padding-right:200px;"><input class="btn" type="submit" value="确定" onclick="return checkEbayAccount()"></div>
				</td>
			</tr>
		</table>
	</div>
</form>
<?PHP $this->_endblock();?>