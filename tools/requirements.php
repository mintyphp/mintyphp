<?php
if (!defined('PHP_VERSION_ID')) {
	$version = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
if (PHP_VERSION_ID < 50300) {
	echo "ERROR: PHP 5.3 or higher required\n";
	exit(1);
}
if (!function_exists('mysqli_connect')) {
	echo "ERROR: MySQLi extension not found\n";
	exit(1);
}
if (!file_exists('tools/latest.php')) {
	echo "INFO: Adminer not found, downloading...\n";
	file_put_contents('tools/latest.php',file_get_contents('http://adminer.org/latest.php'));
}
if (!file_exists('tools/latest.php')) {
	echo "ERROR: Could not write 'tools/latest.php'\n";
	exit(1);
}
if (!file_exists('composer.phar')) {
	echo "INFO: Composer not found, downloading...\n";
	file_put_contents('composer.phar',file_get_contents('https://getcomposer.org/installer'));
}
if (!file_exists('composer.phar')) {
	echo "ERROR: Could not write 'composer.phar'\n";
	exit(1);
}
if (!file_exists('phpunit.phar')) {
	echo "INFO: PHPUnit not found, downloading...\n";
	file_put_contents('phpunit.phar',file_get_contents('https://phar.phpunit.de/phpunit.phar'));
}
if (!file_exists('phpunit.phar')) {
	echo "ERROR: Could not write 'phpunit.phar'\n";
	exit(1);
}
exit(0);
