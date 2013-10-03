<?php $this->_extends('_layouts/chm_layout'); ?>

<?php $this->_block('contents'); ?>

<div class="guide-section">

  <div class="guide-header">
    <span class="nav">
      <a href="http://qee13.com/app/?action=docs">文档索引</a>
      &raquo;
      <a href="<?php echo $book->url($book); ?>"><?php echo h($book->subject); ?></a>
      &raquo;
      <a href="<?php echo $book->url($section->chapter); ?>"><?php echo h($section->chapter->subject); ?></a>
      &raquo;
      <?php echo h($section->subject); ?>
    </span>
  </div>

  <div class="guide-section-details formatted">
    <?php
    	$string=$section->contents;
    	$start=strpos($string, '$Id:');
    	if ($start){
			$string=substr($string,0, $start);
		} 
    	echo Command_Book::formatting($string); 
	    echo '$Id: '.$section->filename.'.texy ';
	    echo date('Y-m-d H:i:s',$section->last_modified).'Z Tim13 $';
    ?>

  </div>

  <div class="guide-footer">

    <table border="0" width="100%">
      <tr>
        <td align="left" width="200">
          <?php if ($section->prev()): ?>
          &laquo;
          <a href="<?php echo $book->url($section->prev()); ?>"><?php echo h($section->prev()->subject); ?></a>
          <?php endif; ?>

        </td>

        <td align="center">
          本章：<a href="<?php echo $book->url($section->chapter); ?>"><?php echo h($section->chapter->subject); ?></a>
          <br />
          <a href="<?php echo $book->url($book); ?>">返回索引页</a>
        </td>

        <td align="right" width="200">
          <?php if ($section->next()): ?>
          <a href="<?php echo $book->url($section->next()); ?>"><?php echo h($section->next()->subject); ?></a> 
          &raquo;
          <?php endif; ?>
        </td>
      </tr>
    </table>

  </div>

</div>

<?php $this->_endblock(); ?>

