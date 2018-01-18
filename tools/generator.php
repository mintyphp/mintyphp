<?php
// Change directory to project root
chdir(__DIR__.'/../../../..');
// Use default autoload implementation
require 'vendor/mindaphp/mindaphp/src/Loader.php';
// Load the libraries
require 'config/loader.php';
// Load the config parameters
require 'config/app.php';

use MindaPHP\DB;

$tables = isset($_POST['tables'])?$_POST['tables']:false;

if (!$tables) {
    $entities = DB::select('SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA` = ?;', DB::$database);
    echo '<form method="post">';
    foreach ($entities as $entity) {
        $table = $entity['TABLES']['TABLE_NAME'];
        echo '<input type="checkbox" name="tables[]" value="'.$table.'">'.$table.'<br/>';
    }
    echo '<input type="submit" value="OK">';
    echo '</form>';
    var_dump($entities);
} else {
    foreach ($tables as $table) {
        $path = 'admin2';
        $pages = array(
            'index().php',
            'index(admin).phtml',
            'add().php',
            'add(admin).phtml',
            'edit($id).php',
            'edit(admin).phtml',
            'delete($id).php',
            'delete(admin).phtml',
            'view($id).php',
            'view(admin).phtml',
        );
        $humanize = function ($v) {
            return str_replace('_', ' ', $v);
        };
        $singularize = function ($v) {
            return rtrim($v, 's');
        };

        $fields = DB::select("SELECT * FROM information_schema.COLUMNS WHERE table_schema=DATABASE() and extra != 'auto_increment' and table_name = ?", $table);
        $belongsTo = DB::select("select * from information_schema.KEY_COLUMN_USAGE where referenced_table_name is not null and table_schema=DATABASE() AND table_name = ?", $table);
        $hasMany = DB::select("select * from information_schema.KEY_COLUMN_USAGE where referenced_table_name is not null and table_schema=DATABASE() AND referenced_table_name = ?", $table);
        $hasAndBelongsToMany = DB::select("select * from information_schema.KEY_COLUMN_USAGE a, information_schema.KEY_COLUMN_USAGE b where a.referenced_table_name is not null and b.referenced_table_name is not null and a.table_schema=DATABASE() and b.table_schema=DATABASE() and a.table_name = b.table_name and a.CONSTRAINT_NAME != b.CONSTRAINT_NAME and a.referenced_table_name = ?", $table);

        $findDisplayField = function ($table) {
            $field = DB::selectValue("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_schema=DATABASE() and extra != 'auto_increment' and table_name = ? and COLUMN_NAME = 'name' limit 1", $table);
            if ($field) {
                return $field;
            }
            $field = DB::selectValue("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_schema=DATABASE() and extra != 'auto_increment' and table_name = ? and COLUMN_KEY = 'UNI' limit 1 ", $table);
            if ($field) {
                return $field;
            }
            $field = DB::selectValue("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_schema=DATABASE() and extra != 'auto_increment' and table_name = ? limit 1", $table);

            return $field;
        };
        $findBelongsTo = function ($name) use ($belongsTo) {
            foreach ($belongsTo as $relation) {
                if ($relation['KEY_COLUMN_USAGE']['COLUMN_NAME'] == $name) {
                    return $relation;
                }
            }

            return false;
        };

        echo $table.'<br/><pre>';
        var_dump($fields[0], $belongsTo, $hasMany, $hasAndBelongsToMany);
        echo '</pre>';

        $dir = 'pages/'.$path.'/'.$table;
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($pages as $page) {
            ob_start();
            include "skel/pages/$page";
            file_put_contents("$dir/$page", ob_get_clean());
        }
    }
}
