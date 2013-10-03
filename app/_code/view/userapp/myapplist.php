<?PHP $this->_extends('_layouts/sexy_layout'); ?>
<?PHP $this->_block('title');?>
	<?php echo '我购买的应用列表'?>
<?PHP $this->_endblock();?>
<?PHP $this->_block('head');?>
<script type="text/javascript">
	function selectTerm()
	{
		document.myapplist.action = "<?php echo url('userapp/myapplist')?>";
		document.myapplist.submit();
	}
</script>
<?PHP $this->_endblock();?>
<?PHP $this->_block('contents');?>

    	<div >
        <h3>我购买的应用 </h3>
            <form action="" method="post" name="myapplist" class="form-inline">
                 <div class="search_b">
        			<div class="list">
        				应用列表
        				<?php 
        					Q::control('dropdownlistwithempty', 'baseapplist', array(
        						'items' => $base_app,
        						'value' => request('baseapplist'),
        						'onchange' => 'selectTerm()',
        						'style' => 'width:150px'
        					))->display();
        				?>
                    	&nbsp;&nbsp;&nbsp;&nbsp;
        				Ebay账号
        				<?php 
        					Q::control('dropdownlistwithempty', 'ebay_account', array(
        						'items' => $baseebayuser,
        						'value' => request('ebay_account'),
        						'onchange' => 'selectTerm()',
        						'style' => 'width:150px'
        					))->display();
        				?>
						<a href="<?php echo url('app/list')?>" target="_blank" style="float:right;margin-right:150px;" class="btn btn-primary"> 购 买 应 用 </a>
        			</div>
                </div>
				
             </form>
			 
        </div>

<br/>
<div class="lb_1">
<table class="table table-bordered table-condensed table-hover table-striped">
	<tr>
		<th>应用名</th><th>绑定ebay账号</th><th>有效期</th><th>操作</th>
	</tr>
	
	<?php foreach ($baseuserapp as $v_baseuserapp): ?>
	<tr <?php if($v_baseuserapp->using == 0) echo "class=greyback";?> >
		<td >
			<?php echo $v_baseuserapp->app->appname;?>
        </td>
		<td>
			<?php 
				$base_ebay_user = Base_Ebay_User::find('ebay_user_id = ?', $v_baseuserapp->ebay_user_id)->setColumns('selleruserid')->getOne(); 
				echo $base_ebay_user->selleruserid;
			?>
		</td>
		<td>
        	<?php 
				//echo date("Y-m-d", $v_baseuserapp->expire)
				if($v_baseuserapp->expire < time())
				{
					echo '已过期';
				}
				else
				{
					echo date("Y-m-d", $v_baseuserapp->expire);
				}
			?>
        </td>
		<td>
			<a style="color:#00c; text-decoration:none;" href="<?php echo url('app/buyapp',array('is_buy'=>'false','id'=>$v_baseuserapp->id, 'appid'=>$v_baseuserapp->app->appid,'ebayuseraccount'=>$v_baseuserapp->ebay_user_id)) ?>" target="_blank">续费</a>&nbsp;&nbsp;
			<?php 
				if($v_baseuserapp->using != 0)
				{
					if(!$v_baseuserapp->ebay_user_id)
					{
						$id = $v_baseuserapp->id;
						echo "<a style='color:#00c; text-decoration:none;' href=index.php?controller=userapp&action=bindselleruseridtoapp&id=$id>绑定账号</a>";
					}
				}
			?>
		</td>
	</tr>
	<?php endforeach;?>
</table>
</div>
<?php
$this->_control('pagination', 'my-pagination', array(
    'pagination' => $pagination,
));
?>
<div style=" clear:both"></div>
<?PHP $this->_endblock();?>