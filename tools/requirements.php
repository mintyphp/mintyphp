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
if (!file_exists('config/config.php')) {
	$config = file_get_contents('config/config.php.template');
	$questions = array(
	  array('BASE_URL','What is URL path of the application?','/',function($i){ 
		if ($i=='/') return $i; 
		$u=parse_url($i); return '/'.trim($u['path'], '/').'/'; 
	  }),
	  array('Query_HOST','What is the MySQL hostname?','localhost',null),
	  array('Query_USER','What is the MySQL username?','root',null),
	  array('Query_PASS','What is the MySQL password?','',null),
	  array('Query_NAME','What is the MySQL database?','mindaphp',null),
	  array('Query_PORT','What is the MySQL port?','3306',null),
	);
	$parameters = array();
	$c = count($questions);
	foreach ($questions as $i=>$q) {
		$n = $i+1;
		list($name,$question,$default,$filter) = $q;
		echo "[$n/$c] $question [$default] ";
		$parameters[$name] = trim(fgets(STDIN))?:$default;
	}
	$mysqli = new mysqli($parameters['Query_HOST'], $parameters['Query_USER'], $parameters['Query_PASS']);
	if ($mysqli->connect_error) {
	    echo "ERROR: MySQL connect: ($mysqli->connect_errno) $mysqli->connect_error\n";
	    exit(1);
	}
	echo "INFO: MySQL connected\n";
	$sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$parameters[Query_NAME]';";
	if (!$result = $mysqli->query($sql)) {
		echo "ERROR: MySQL database check: $mysqli->error\n";
		exit(1);
	} elseif (!$result->num_rows) {
		if ($parameters['Query_USER'] != 'root') {
			echo "ERROR: MySQL database not found: $parameters[Query_NAME]\n";
	    	exit(1);
		}
		$sql = "CREATE DATABASE `$parameters[Query_NAME]` COLLATE 'utf8_bin';";
		if (!$result = $mysqli->query($sql)) {
			echo "ERROR: MySQL database create: $mysqli->error\n";
			exit(1);
		}
		echo "INFO: MySQL database created\n";
	    $host = $parameters['Query_HOST']=='localhost'?'localhost':'%';
		$pass = base64_encode(sha1(rand() . time(true) . $parameters['Query_NAME'], true));  
		$sql = "CREATE USER '$parameters[Query_NAME]'@'$host' IDENTIFIED BY '$pass';";
		if (!$result = $mysqli->query($sql)) {
			echo "ERROR: MySQL user create: $mysqli->error\n";
			exit(1);
		}
		echo "INFO: MySQL user created\n";
	    $sql = "GRANT ALL PRIVILEGES ON `$parameters[Query_NAME]`.* TO '$parameters[Query_NAME]'@'$host';";
		if (!$result = $mysqli->query($sql)) {
			echo "ERROR: MySQL grant user: $mysqli->error\n";
			exit(1);
		}
		echo "INFO: MySQL user granted\n";
	    $parameters['Query_USER'] = $parameters['Query_NAME'];
		$parameters['Query_PASS'] = $pass;
	}
    $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$parameters[Query_NAME]' AND TABLE_NAME = 'users';";
	if (!$result = $mysqli->query($sql)) {
		echo "ERROR: MySQL table check: $mysqli->error\n";
		exit(1);
	} elseif (!$result->num_rows) {
		$sql = <<<END_OF_SQL
CREATE TABLE `$parameters[Query_NAME]`.`users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL,
  `salt` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoQuery DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
END_OF_SQL;
		if (!$mysqli->query($sql)) {
			echo "ERROR: MySQL create table: $mysqli->error\n";
			exit(1);
		}
		echo "INFO: MySQL table created\n";
	}
	$mysqli->close();
	foreach ($parameters as $key => $value) {
	  $config = str_replace('{{'.$key.'}}', $value, $config);
	}
	file_put_contents('config/config.php',$config);
}
if (!file_exists('config/config.php')) {
	echo "ERROR: Could not write 'config/config.php'\n";
	exit(1);
}
if (!file_exists('tools/adminer.php')) {
	echo "INFO: File 'adminer.php' not found, downloading...\n";
	file_put_contents('tools/adminer.php',file_get_contents('http://adminer.org/latest.php'));
}
if (!file_exists('tools/adminer.php')) {
	echo "ERROR: Could not write 'tools/adminer.php'\n";
	exit(1);
}
if (!file_exists('composer.phar')) {
	echo "INFO: File 'composer.phar' not found, downloading...\n";
	file_put_contents('composer.phar',file_get_contents('https://getcomposer.org/installer'));
	include 'composer.phar';
}
if (!file_exists('composer.phar')) {
	echo "ERROR: Could not write 'composer.phar'\n";
	exit(1);
}
exit(0);
