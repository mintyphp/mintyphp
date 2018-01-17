<?php
// Change directory to project root
chdir(__DIR__ . '/../../../..');
session_save_path(sys_get_temp_dir());
$dir = $_SERVER['DOCUMENT_ROOT'];
$file = realpath($dir . $_SERVER['SCRIPT_NAME']);
if (!file_exists('config/app.php')) {
    require 'vendor/mindaphp/mindaphp/tools/configurator.php';
    die();
}
if (file_exists($file) && (strpos($file, $dir) === 0)) {
    return false;
}
$tools = array('/adminer.php', '/conventionist.php', '/configurator.php', '/generator.php');
if (in_array($_SERVER['SCRIPT_NAME'], $tools)) {
    require 'vendor/mindaphp/mindaphp/tools' . $_SERVER['SCRIPT_NAME'];
} else {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    chdir('webroot');
    require 'index.php';
}
