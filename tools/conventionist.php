<?php
// Change directory to project root
chdir(__DIR__ . '/../../../..');
// Use default autoload implementation
require 'vendor/mindaphp/mindaphp/src/Loader.php';
// Load the libraries
require 'config/loader.php';
// Load the config parameters
require 'config/app.php';

echo Conventionist::html();

class Conventionist
{
    public static function html()
    {
        $errors = static::check();
        $str = "<h1>Conventionist</h1>";
        $str .= "<table cellpadding=\"4\"><tr><th>#</th><th>type</th><th>table</th><th>field</th><th>message</th></tr>";
        foreach ($errors as $i => $e) {
            $str .= "<tr><td>" . ($i + 1) .
                ".</td><td>$e[type]</td><td>$e[table]</td><td>$e[field]</td><td>$e[message]</td></tr>";
        }
        $str .= "</table>";

        return $str;
    }

    public static function text()
    {
        $errors = static::check();
        $str = "";
        foreach ($errors as $i => $e) {
            $str .= <<<END_OF_STR
#$i
  type   : $e[type]
  table  : $e[table]
  field  : $e[field]
  message: $e[message]


END_OF_STR;
        }

        return $str;
    }

    private static function check()
    {
        $tables = DB::select("SELECT TABLE_NAME,TABLE_TYPE,ENGINE,TABLE_COLLATION FROM information_schema.TABLES WHERE table_schema=DATABASE() AND table_name NOT like '%_history' AND table_name NOT like 'history'");
        $foreign_keys = DB::selectPairs("select concat(table_name, '.', column_name) as 'foreign_key', concat(referenced_table_name, '.', referenced_column_name) as 'references' from information_schema.key_column_usage where referenced_table_name is not null and table_schema=DATABASE()");
        $errors = array();
        $fieldsets = array();
        $tableNames = array();
        foreach ($tables as $i => $table) {
            $tables[$i] = $table['TABLES'];
            $tableNames[] = $table['TABLES']['TABLE_NAME'];
        }
        for ($i = 0; $i < count($tableNames); $i++) {
            $table = $tables[$i]['TABLE_NAME'];
            $fields = DB::select("SELECT COLUMN_NAME,COLUMN_KEY,EXTRA FROM information_schema.COLUMNS WHERE table_schema=DATABASE() and table_name = ?", $table);
            foreach ($fields as $j => $field) {
                $fields[$j] = $field['COLUMNS'];
            }
            // table checks:
            if (!preg_match('/^[a-z0-9_]+$/i', $table, $matches)) {
                $errors[] = array('type' => 'error', 'table' => $table, 'message' => 'invalid table name');
            }
            if (!preg_match('/^[a-z_]+$/', $table, $matches)) {
                $errors[] = array('type' => 'warning', 'table' => $table, 'message' => 'invalid table name');
            }
            if ($tables[$i]['TABLE_TYPE'] == "BASE TABLE" && $tables[$i]['ENGINE'] != "InnoDB") {
                $errors[] = array('type' => 'error', 'table' => $table, 'message' => 'type must be InnoDB');
            }
            if (!preg_match('/^utf8/i', $tables[$i]['TABLE_COLLATION'], $matches)) {
                $errors[] = array('type' => 'warning', 'table' => $table, 'message' => 'collation should be utf8');
            }
            // column checks:
            $pk = false;
            for ($j = 0; $j < count($fields); $j++) {
                $field = $fields[$j]['COLUMN_NAME'];
                if (!preg_match('/^[a-z0-9_]+$/i', $field, $matches)) {
                    $errors[] = array(
                        'type' => 'error',
                        'table' => $table,
                        'field' => $field,
                        'message' => 'invalid field name'
                    );
                }
                if (!preg_match('/^[a-z_]+$/', $field, $matches)) {
                    $errors[] = array(
                        'type' => 'warning',
                        'table' => $table,
                        'field' => $field,
                        'message' => 'invalid field name'
                    );
                }
                if ($field == 'id') {
                    $pk = true;
                    if ($fields[$j]['COLUMN_KEY'] != 'PRI') {
                        $errors[] = array(
                            'type' => 'error',
                            'table' => $table,
                            'field' => $field,
                            'message' => 'must be primary key'
                        );
                    }
                    if ($fields[$j]['EXTRA'] != 'auto_increment') {
                        $errors[] = array(
                            'type' => 'error',
                            'table' => $table,
                            'field' => $field,
                            'message' => 'must auto increment'
                        );
                    }
                } elseif (preg_match('/_id$/', $field, $matches)) {
                    if ($fields[$j]['COLUMN_KEY'] == 'PRI') {
                        $errors[] = array(
                            'type' => 'error',
                            'table' => $table,
                            'field' => $field,
                            'message' => 'may not be primary key'
                        );
                    }
                    if ($fields[$j]['COLUMN_KEY'] == '') {
                        $errors[] = array(
                            'type' => 'warning',
                            'table' => $table,
                            'field' => $field,
                            'message' => 'should have index'
                        );
                    }
                    if ($fields[$j]['EXTRA'] == 'auto_increment') {
                        $errors[] = array(
                            'type' => 'error',
                            'table' => $table,
                            'field' => $field,
                            'message' => 'may not auto increment'
                        );
                    }
                    $otherTable = static::pluralize(preg_replace('/_id$/', '', $field));
                    if (!in_array($otherTable, $tableNames)) {
                        $errors[] = array(
                            'type' => 'error',
                            'table' => $table,
                            'field' => $field,
                            'message' => "table '$otherTable' should exist"
                        );
                    }
                    if (!isset($foreign_keys["$table.$field"])) {
                        $errors[] = array(
                            'type' => 'error',
                            'table' => $table,
                            'field' => $field,
                            'message' => 'must be foreign key'
                        );
                    } elseif ($foreign_keys["$table.$field"] != "$otherTable.id") {
                        $errors[] = array(
                            'type' => 'error',
                            'table' => $table,
                            'field' => $field,
                            'message' => "must be foreign key to '$otherTable.id'"
                        );
                    }
                }
            }
            if (!$pk) {
                $errors[] = array('type' => 'error', 'table' => $table, 'field' => 'id', 'message' => 'must exist');
            }
        }

        return $errors;
    }

  //code from Paul Osman: http://blog.eval.ca/2007/03/03/php-pluralize-method/
  //who translated it from Ruby from the Rails Inflector class
    private static function pluralize($string)
    {
        $plural = array(
            array( '/(quiz)$/i',               "$1zes"   ),
            array( '/^(ox)$/i',                "$1en"    ),
            array( '/([m|l])ouse$/i',          "$1ice"   ),
            array( '/(matr|vert|ind)ix|ex$/i', "$1ices"  ),
            array( '/(x|ch|ss|sh)$/i',         "$1es"    ),
            array( '/([^aeiouy]|qu)y$/i',      "$1ies"   ),
            array( '/([^aeiouy]|qu)ies$/i',    "$1y"     ),
            array( '/(hive)$/i',               "$1s"     ),
            array( '/(?:([^f])fe|([lr])f)$/i', "$1$2ves" ),
            array( '/sis$/i',                  "ses"     ),
            array( '/([ti])um$/i',             "$1a"     ),
            array( '/(buffal|tomat)o$/i',      "$1oes"   ),
            array( '/(bu)s$/i',                "$1ses"   ),
            array( '/(alias|status)$/i',       "$1es"    ),
            array( '/(octop|vir)us$/i',        "$1i"     ),
            array( '/(ax|test)is$/i',          "$1es"    ),
            array( '/s$/i',                    "s"       ),
            array( '/$/',                      "s"       )
        );
        $irregular = array(
            array( 'move',   'moves'    ),
            array( 'sex',    'sexes'    ),
            array( 'child',  'children' ),
            array( 'man',    'men'      ),
            array( 'person', 'people'   )
        );
        $uncountable = array(
            'sheep',
            'fish',
            'series',
            'species',
            'money',
            'rice',
            'information',
            'equipment'
        );
      // save some time in the case that singular and plural are the same
        if (in_array(strtolower($string), $uncountable)) {
            return $string;
        }
      // check for irregular singular forms
        foreach ($irregular as $noun) {
            if (strtolower($string) == $noun[0]) {
                    return $noun[1];
            }
        }
      // check for matches using regular expressions
        foreach ($plural as $pattern) {
            if (preg_match($pattern[0], $string)) {
                    return preg_replace($pattern[0], $pattern[1], $string);
            }
        }

        return $string;
    }
}
