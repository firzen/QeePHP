<?PHP $this->_extends('_layouts/default_layout'); ?>
<?PHP $this->_block('title');?>代码生成工具<?PHP $this->_endblock();?>
<?PHP $this->_block('head');?>
    <script type="text/javascript" src="<?php echo $_BASE_DIR?>public/jquery.zclip.min.js"></script>
<?PHP $this->_endblock();?>
<?PHP $this->_block('contents');?>
<div>
	<h3>控制器生成器</h3>
	<p>
		控制器名称：<input id=cname name="cname"> <input id="gen" type="button" value="生成">
	</p>
	<p>
		<pre rows="" id=sourcecode name="sourcecode" cols="" style="width:700px;height: 150px;"></pre>
	</p>
	<p>
		<a class="btn btn-primary" id="copy1">复制</a>
	</p>
	<hr>
	<h3>模型生成器</h3>
	<p>
	模型名称：<input id=mname name="mname" required="required">
	</p>
	<p>
		对应表格：<input id="tname" name="tname" required="required">
	</p>
	<p>
		主键字段：<input id="pkey">
	</p>
	<p>
		<label class="span1">字段：</label>
		<label class="span1"><input id="created" type="checkbox" value="created">created</label>
		<label class="span1"><input id="updated" type="checkbox" value="updated">updated</label>
	</p>
	<p>
	<pre rows="" id=sourcecode2 name="sourcecode2" cols="" style="width:700px;height: 300px;overflow: auto;"></pre>
	</p>
	<p>
		<a class="btn btn-primary" id="copy2">复制</a>
	</p>
	<hr>
	<h3>Js换行处理器</h3>
	<p>
		<textarea rows="" cols=""  style="width:700px;height: 300px;" id=jscode></textarea>
	</p>
	<p>
		<input type="button" value="转换" id="jsgo">
	</p>
</div>

<script type="text/javascript">
$('#copy1').zclip({
	path: "<?php echo $_BASE_DIR?>public/ZeroClipboard.swf",
	afterCopy:copydone,
	copy: function(){
	    return $('#sourcecode').text();
	    }
	});
$('#copy2').zclip({
	path: "<?php echo $_BASE_DIR?>public/ZeroClipboard.swf",
	afterCopy:copydone,
	copy: function(){
	    return $('#sourcecode2').text();
	    }
	});
function copydone(){
	alert('复制成功');
}
$('#cname').keyup(function(){
	var tpl="<\?php \n"+
		"class Controller_"+$('#cname').val()+" extends Controller_Abstract{\n"+
		"\tfunction actionIndex(){\n"+
		"\t}\n"+
		"}";
	$('#sourcecode').text(tpl);
});
$('#mname,#tname,#pkey,#created,#updated').keyup(function(){
	var mname=$('#mname').val();
	var tname=$('#tname').val();
	var pkey=$('#pkey').val();
	
	var tpl="<\?php\n"+
	"/**\n"+
	" * "+mname+" 封装来自 "+tname+" 数据表的记录及领域逻辑\n"+
	" */\n"+
	"class "+mname+" extends QDB_ActiveRecord_Abstract\n"+
	"{\n"+
	"\n"+
	"    /**\n"+
	"     * 返回对象的定义\n"+
	"     *\n"+
	"     * @static\n"+
	"     *\n"+
	"     * @return array\n"+
	"     */\n"+
	"    static function __define()\n"+
	"    {\n"+
	"        return array\n"+
	"        (\n"+
	"\n"+
	"            // 用什么数据表保存对象\n"+
	"            'table_name' => '"+tname+"',\n"+
	"\n"+
	"            // 指定数据表记录字段与对象属性之间的映射关系\n"+
	"            // 没有在此处指定的属性，QeePHP 会自动设置将属性映射为对象的可读写属性\n"+
	"            'props' => array\n"+
	"            (\n"+
	"            	'"+pkey+"' => array('readonly' => true),\n"+
	"            ),\n"+
	"        	'validations' => array\n"+
	"        	(\n"+
	"        	),\n"+
	"        	'create_autofill' => array\n"+
	"        	(\n"+
	"        		//自动填充修改和创建时间\n";
	if ($('#created:checked').length){
		tpl+="        		'created'=>self::AUTOFILL_TIMESTAMP,\n";
	}
	if ($('#updated:checked').length){
		tpl+="        		'updated'=>self::AUTOFILL_TIMESTAMP\n";
	}
	tpl+="        	),\n"+
	"        	'update_autofill'=>array(\n";
	if ($('#updated:checked').length){
		tpl+="        		'updated'=>self::AUTOFILL_TIMESTAMP\n";
	}
	tpl+="        	),\n"+
	"		// 不允许通过构造函数给 "+pkey+" 属性赋值\n"+
	"        	'attr_protected' => '"+pkey+"',\n"+
	"        );\n"+
	"    }\n"+
	"    /**\n"+
	"     * 开启一个查询，查找符合条件的对象或对象集合\n"+
	"     *\n"+
	"     * @static\n"+
	"     *\n"+
	"     * @return QDB_Select\n"+
	"     */\n"+
	"    static function find()\n"+
	"    {\n"+
	"        $args = func_get_args();\n"+
	"        return QDB_ActiveRecord_Meta::instance(__CLASS__)->findByArgs($args);\n"+
	"    }\n"+
	"\n"+
	"    /**\n"+
	"     * 返回当前 ActiveRecord 类的元数据对象\n"+
	"     *\n"+
	"     * @static\n"+
	"     *\n"+
	"     * @return QDB_ActiveRecord_Meta\n"+
	"     */\n"+
	"    static function meta()\n"+
	"    {\n"+
	"        return QDB_ActiveRecord_Meta::instance(__CLASS__);\n"+
	"    }\n"+
	"\n"+
	"\n"+
	"/* ------------------ 以上是自动生成的代码，不能修改 ------------------ */\n"+
	"}\n"+
	"class "+mname+"Exception extends QException{}";
	$('#sourcecode2').text(tpl);
}).click(function(){$(this).trigger('keyup')});
$('#jsgo').click(function(){
	var v=$('#jscode').val().replace(/"/gi,"\\\"").replace(/\n/gi,"\\n\"+\n\"");
	$('#jscode').val('"'+v);
})

</script>
<?PHP $this->_endblock();?>

