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
	  array('DB_HOST','What is the MySQL hostname?','localhost',null),
	  array('DB_USER','What is the MySQL username?','root',null),
	  array('DB_PASS','What is the MySQL password?','',null),
	  array('DB_NAME','What is the MySQL database?','mindaphp',null),
	  array('DB_PORT','What is the MySQL port?','3306',null),
	);
	$parameters = array();
	$c = count($questions);
	foreach ($questions as $i=>$q) {
		$n = $i+1;
		list($name,$question,$default,$filter) = $q;
		echo "[$n/$c] $question [$default] ";
		$parameters[$name] = trim(fgets(STDIN))?:$default;
	}
	$mysqli = new mysqli($parameters['DB_HOST'], $parameters['DB_USER'], $parameters['DB_PASS']);
	if ($mysqli->connect_error) {
	    echo "ERROR: MySQL connect: ($mysqli->connect_errno) $mysqli->connect_error\n";
	    exit(1);
	}
	echo "INFO: MySQL connected\n";
	$sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$parameters[DB_NAME]';";
	if (!$result = $mysqli->query($sql)) {
		echo "ERROR: MySQL database check: $mysqli->error\n";
		exit(1);
	} elseif (!$result->num_rows) {
		if ($parameters['DB_USER'] != 'root') {
			echo "ERROR: MySQL database not found: $parameters[DB_NAME]\n";
	    	exit(1);
		}
		$sql = "CREATE DATABASE `$parameters[DB_NAME]` COLLATE 'utf8_bin';";
		if (!$result = $mysqli->query($sql)) {
			echo "ERROR: MySQL database create: $mysqli->error\n";
			exit(1);
		}
		echo "INFO: MySQL database created\n";
	    $host = $parameters['DB_HOST']=='localhost'?'localhost':'%';
		$pass = base64_encode(sha1(rand() . time(true) . $parameters['DB_NAME'], true));  
		$sql = "CREATE USER '$parameters[DB_NAME]'@'$host' IDENTIFIED BY '$pass';";
		if (!$result = $mysqli->query($sql)) {
			echo "ERROR: MySQL user create: $mysqli->error\n";
			exit(1);
		}
		echo "INFO: MySQL user created\n";
	    $sql = "GRANT ALL PRIVILEGES ON `$parameters[DB_NAME]`.* TO '$parameters[DB_NAME]'@'$host';";
		if (!$result = $mysqli->query($sql)) {
			echo "ERROR: MySQL grant user: $mysqli->error\n";
			exit(1);
		}
		echo "INFO: MySQL user granted\n";
	    $parameters['DB_USER'] = $parameters['DB_NAME'];
		$parameters['DB_PASS'] = $pass;
	}
    $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$parameters[DB_NAME]' AND TABLE_NAME = 'users';";
	if (!$result = $mysqli->query($sql)) {
		echo "ERROR: MySQL table check: $mysqli->error\n";
		exit(1);
	} elseif (!$result->num_rows) {
		$sql = <<<END_OF_SQL
CREATE TABLE `$parameters[DB_NAME]`.`users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL,
  `salt` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
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
exit(0);