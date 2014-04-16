<?php
$files = 0;
$updated = 0;
$source = "https://raw.github.com/mevdschee/MindaPHP/master/";
$paths = array('web/index.php','web/debugger/index.php','vendor/mindaphp/*.php');
foreach ($paths as $path) {
  foreach (glob($path) as $filename) {
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
      if ($size && preg_match('/<\?php/', $data)) {
        $updated++;
        file_put_contents($filename, $data);
        $version = substr($hash, 0, 10);
        echo "$filename ($version)\n";
      } else {
        echo "$filename (ERROR)\n";
      }
    }
  }
}
echo "$files checked $updated updated\n";
