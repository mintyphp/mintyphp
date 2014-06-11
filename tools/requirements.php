<?php
if (!file_exists('config/config.php')) {
	$config = file_get_contents('config/config.php.template');
	$c = 6;
	// {{BASE_URL}}
	printf("[1/$c] What is URL path of the application? [/] ");
	$url = parse_url(trim(fgets(STDIN)));
	if (!trim($url['path'], '/')) $url = '/';
	else $url = '/'.trim($url['path'], '/').'/';
	$config = str_replace('{{BASE_URL}}', $url, $config);
	// {{DB_HOST}}
	printf("[2/$c] What is the MySQL hostname? [localhost] ");
	$config = str_replace('{{DB_HOST}}', trim(fgets(STDIN))?:'localhost', $config);
	// {{DB_USER}}
	printf("[3/$c] What is the MySQL username? [root] ");
	$config = str_replace('{{DB_USER}}', trim(fgets(STDIN))?:'root', $config);
	// {{DB_PASS}}
	printf("[4/$c] What is the MySQL password? [] ");
	$config = str_replace('{{DB_PASS}}', trim(fgets(STDIN))?:'', $config);
	// {{DB_NAME}}
	printf("[5/$c] What is the MySQL database? [mindaphp] ");
	$config = str_replace('{{DB_NAME}}', trim(fgets(STDIN))?:'mindaphp', $config);
	// {{DB_PORT}}
	printf("[6/$c] What is the MySQL port? [3306] ");
	$config = str_replace('{{DB_PORT}}', trim(fgets(STDIN))?:'3306', $config);
	file_put_contents('config/config.php',$config);
}
if (!file_exists('config/config.php')) {
	echo "ERROR: Could not write 'config/config.php'\n";
	exit(1);
}
if (!file_exists('tools/adminer.php')) {
	file_put_contents('tools/adminer.php',file_get_contents('http://adminer.org/latest.php'));
}
if (!file_exists('tools/adminer.php')) {
	echo "ERROR: Could not write 'tools/adminer.php'\n";
	exit(1);
}
exit(0);