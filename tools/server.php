<?php
// Change directory to project root

chdir(__DIR__.'/..');
session_save_path(realpath('sessions'));
$dir = $_SERVER['DOCUMENT_ROOT'];
$file = realpath($dir.$_SERVER['SCRIPT_NAME']);
if (!file_exists('config/config.php')) {
	require 'tools/configurator.php'; die();
}
if (file_exists($file) && (strpos($file,$dir)===0)) return false;
if (in_array($_SERVER['SCRIPT_NAME'],array('/adminer.php','/conventionist.php','/configurator.php','/generator.php'))) {
  require 'tools'.$_SERVER['SCRIPT_NAME'];
} else {
  $_SERVER['SCRIPT_NAME'] = '/index.php';
  chdir('web');
  require 'index.php';
}
