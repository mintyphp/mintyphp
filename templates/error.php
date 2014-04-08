<?php
  Router::setContent('<p style="color:red;">ERROR: '.Router::getContent().'</p>');
  require __DIR__.'/default.php';
?>