<?php $this->_extends('_layouts/index_layout'); ?>

<?php $this->_block('contents'); ?>

<div class="apidoc-index container">

  <h1>QeePHP API 参考手册</h1>

  <p>API 参考手册提供了 QeePHP 所有对象和方法的参考信息和用法示例。是日常使用必备的参考文档。</p>
<div class="row">
	<div class=" bs-docs-sidebar span3">
	  	<ul id="tabs-packages " class="nav nav-list bs-docs-sidenav affix">
	    <?php foreach($packages as $package): ?>
	    <li><a href="#package-<?php echo $package->name; ?>"><?php echo $package->name; ?></a></li>
	    <?php endforeach; ?>
	
	  </ul>
	</div>
	<div class="span9">
  <?php foreach($packages as $package): ?>

  <div class="package" id="package-<?php echo $package->name; ?>">

    <h2>包 - <?php echo h($package->name); ?></h2>

    <div class="package-classes">
      <?php $i = 0;  $classes = $package->classes; ksort($classes); foreach($classes as $i => $class): $i++; ?>
      <tr>
        <?php if ($i == 1): ?>
          <?php echo Command_API::formatting($package->description); ?>

        <?php endif; ?>

          <h4 class="class-name">
            <a href="<?php echo Command_API::classUrl($class, $class_url); ?>"><?php echo h($class->name); ?></a>
          </h4>
          <p class="class-summary">
            <?php echo h($class->summary); ?>
          </p>
      <?php endforeach; ?>

    </div>
  </div>

  <?php endforeach; ?>
	</div>
</div>

<script type="text/javascript">$("#tabs-packages").tabs();</script>

<?php $this->_endblock(); ?>

