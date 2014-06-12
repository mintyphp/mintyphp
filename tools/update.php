<?php
$files = 0;
$updated = 0;
$source = "https://raw.github.com/mevdschee/MindaPHP/master/";
$paths = array(
  '.htaccess',
  'web/.htaccess',
  'web/index.php',
  'web/debugger/index.php',
  'vendor/mindaphp/*.php',
  'tools/requirements.php',
  'tools/server.php',
  'tools/update.php'
);
foreach ($paths as $path) {
  foreach (glob($path) as $filename) {
  	echo '.';
    $files++;
    $data = @file_get_contents($source.$filename);
    if ($data===false) {
      echo "Error loading URL ($source$filename)\n";
      continue;
    }
    $size = strlen($data);
    $hash = sha1($data);
    $old = sha1(file_get_contents($filename));
    if ($old!=$hash) {
      if ((preg_match('/\.php$/', $filename) && $size && preg_match('/<\?php/', $data)) ||
          (preg_match('/\.htaccess$/', $filename) && $size)) {
      	$updated++;
        file_put_contents($filename, $data);
        $version = substr($hash, 0, 10);
        echo "\n$filename ($version)\n";
      } else {      
        echo "\n$filename (ERROR)\n";
      }
    }
  }
}
echo "$files checked $updated updated\n";