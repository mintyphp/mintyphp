<?php
$dir = $_SERVER['DOCUMENT_ROOT'];
$file = realpath($dir.$_SERVER['SCRIPT_NAME']);
if (file_exists($file) && (strpos($file,$dir)===0)) return false;
if ($_SERVER['SCRIPT_NAME']=='/adminer.php') {
  include 'adminer.php';
} else {
  $_SERVER['SCRIPT_NAME'] = '/index.php';
  chdir('../web');
  require 'index.php';
}
