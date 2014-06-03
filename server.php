<?php
$file = $_SERVER['DOCUMENT_ROOT'].$_SERVER['SCRIPT_NAME'];
if (file_exists($file)) return false;
$_SERVER['SCRIPT_NAME'] = '/index.php';
chdir('web');
require 'index.php';