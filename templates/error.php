<?php
  Buffer::set('html','<p style="color:red;">ERROR: '.Buffer::get('html').'</p>');
  require __DIR__.'/default.php';
?>