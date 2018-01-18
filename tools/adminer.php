<?php
// Change directory to project root
chdir(__DIR__.'/../../../..');
// Use default autoload implementation
require 'vendor/mindaphp/mindaphp/src/Loader.php';
// Load the config parameters
require 'config/app.php';

// database auto-login credentials
$_GET["username"] = "";
// bypass database selection bug
$_GET["db"] = MindaPHP\Config\DB::$database;

// Adminer Extension
function adminer_object()
{

    class AdminerSoftware extends Adminer
    {

        public function credentials()
        {
            return array(\MindaPHP\Config\DB::$host . ':' .\MindaPHP\Config\DB::$port, \MindaPHP\Config\DB::$username, \MindaPHP\Config\DB::$password);
        }

        public function database()
        {
            return \MindaPHP\Config\DB::$database;
        }

        public function navigation($missing)
        {
            parent::navigation($missing);
            echo '<p class="links"><a href="/conventionist.php">Conventionist</a></p>';
        }
    }

    return new AdminerSoftware;
}

include 'vendor/mindaphp/mindaphp/tools/latest.php';
