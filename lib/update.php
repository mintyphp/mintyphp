<?php
foreach (glob("*.php") as $filename) {
  if ($filename=='update.php') continue;
  $url = "https://raw.github.com/mevdschee/MindaPHP/master/lib/$filename";
  echo "$url\n";
}
