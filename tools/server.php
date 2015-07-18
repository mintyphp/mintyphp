<?php
$dir = $_SERVER['DOCUMENT_ROOT'];
$file = realpath($dir.$_SERVER['SCRIPT_NAME']);
$GLOBALS['mindaphp_tools'] = array('/adminer.php','/conventionist.php','/configurator.php');
if (!file_exists(__DIR__.'/../config/config.php')) {
	require __DIR__.'/configurator.php'; die();
}
if (file_exists($file) && (strpos($file,$dir)===0)) return false;
if (in_array($_SERVER['SCRIPT_NAME'],$GLOBALS['mindaphp_tools'])) {
  require __DIR__.$_SERVER['SCRIPT_NAME'];
} else {
  $_SERVER['SCRIPT_NAME'] = '/index.php';
  chdir(__DIR__.'/../web');
  require 'index.php';
}