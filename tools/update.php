<?php
// Change directory to project root
chdir(__DIR__.'/..');
$files = 0;
$created = 0;
$updated = 0;
$url = "https://github.com/mevdschee/MindaPHP/archive/master.zip";
$zipDir = 'MindaPHP-master/';
$archive = 'tools/master.zip';
$path = realpath('.');
$prefixes = array(
  'web/index.php',
  'web/debugger/',
  'vendor/mindaphp/core/',
  'tools/'
);

echo "Downloading: $url\n";
if (!copy($url,$archive)) die("Error loading URL ($url)\n");
echo "Unzipping: $archive\n";

$zip = new ZipArchive;

if ($zip->open($archive)!==true) die("Error opening archive ($archive)\n");
	 
for($i = 0; $i < $zip->numFiles; $i++) {

	$filename = substr($zip->getNameIndex($i),strlen($zipDir));
	
	$match = false;
	foreach ($prefixes as $prefix) {
		if (substr($filename,0,strlen($prefix))===$prefix) {
			$match = true;
			break;
		}
	}
	if (!$match) continue;
	
	$files++;
	if (file_exists("$path/$filename")) {
		$old = sha1(file_get_contents("$path/$filename"));
	} else {
		$old = false;
		$created++;
	}
	
	$dir = pathinfo($filename,PATHINFO_DIRNAME);
	
	if (substr($filename,-1)=='/') $success = file_exists("$path/$dir") || mkdir("$path/$dir",0755,true);
	else $success = copy("zip://".$archive."#".$zipDir.$filename, "$path/$filename");
	
	if (!$success) {
		echo "$filename (ERROR)\n";
	}
	
	$new = sha1(file_get_contents("$path/$filename"));
	
	if ($old!=$new) {
		$version = substr($new, 0, 10);
		if ($old) $updated++;
		echo "$filename ($version)\n";
	}
	
}
	
$zip->close();
echo "$files checked $updated updated $created created\n";
