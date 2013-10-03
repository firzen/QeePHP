<html>
<head>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
	<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
	<script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
</head>
<body>
<script type="text/javascript">
$(document).ready(function() {
    $("a.toggle").toggle(function(){
        $(this).text($(this).text().replace(/隐藏/,'显示'));
        var a=$(this).parents(".summary");
        a.find(".inherited").hide();
    },function(){
        $(this).text($(this).text().replace(/显示/,'隐藏'));
        $(this).parents(".summary").find(".inherited").show();
    });
});
</script>


   <!-- 左侧栏 -->
	<div class="container">
	<div class="row">
    <div id="subpage_sidebar" class="left apidoc-classes-index span3">

      <?php $this->_control('classes', 'classes-nav', array(
          'packages' => $packages,
          'class_url' => $class_url,
          'index_url' => $index_url,
      )); ?>

    </div>

    <!-- /左侧栏 -->

    <!-- 右侧栏 -->

    <div id="col3" class="right contents span9">

      <?php $this->_block('contents'); ?><?php $this->_endblock(); ?>

    </div>

    <!-- /右侧栏 -->
    </div>
	</div>
</body>
</html>
