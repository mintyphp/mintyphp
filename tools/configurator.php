<?php
// Change directory to project root
chdir(__DIR__ . '/../../../..');
// Use default autoload implementation
require 'vendor/mindaphp/mindaphp/src/Loader.php';
// Load the libraries
require 'config/loader.php';
// Load the config parameters
$filename = 'config/app.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = Configurator::loadCode($filename);
    $config = Configurator::parseConfig($code);
    $config = Configurator::mergePost($config, $_POST);
    echo "<pre>";
    $success = false;
    if (Configurator::testConfig($config)) {
        $code = Configurator::generateCode($config);
        Configurator::writeCode($filename, $code);
        echo "\nConfig written\n";
        $success = true;
    } else {
        echo "\nConfig not written (invalid)\n";
    }
    echo "</pre>";
    if ($success) {
        echo '<input type="button" value="OK" onClick="window.location.href=window.location.href">';
    } else {
        echo '<input type="button" value="Back" onClick="history.go(-1)">';
    }
    die();
}

$code = Configurator::loadCode($filename);
$config = Configurator::parseConfig($code);
echo Configurator::generateForm($config);

class Configurator
{
    public static function loadCode($filename)
    {
        if (!file_exists($filename)) {
            $filename .= '.template';
        }
        if (!file_exists($filename)) {
            throw new \Exception("Could not read: $filename");
        }

        return file_get_contents($filename);
    }

    public static function writeCode($filename, $code)
    {
        file_put_contents($filename, $code);
        if (!file_exists($filename)) {
            throw new \Exception("Could not write: $filename");
        }
    }

    public static function mergePost($config, $post)
    {
        $store = function ($class, $name, $value) use (&$config) {
            foreach ($config[$class] as $i => $v) {
                if ($name == $v['name']) {
                    if (gettype($v['value']) == 'boolean') {
                        $config[$class][$i]['value'] = (bool)$value;
                    } elseif (gettype($v['value']) == 'integer') {
                        $config[$class][$i]['value'] = (int)$value;
                    } elseif (gettype($v['value']) == 'double') {
                        $config[$class][$i]['value'] = (float)$value;
                    } else {
                        $config[$class][$i]['value'] = $value;
                    }
                }
            }
        };

        foreach ($config as $class => $variables) {
            foreach ($variables as $v) {
                $name = $v['name'];
                if (isset($post[$class][$name])) {
                    $store($class, $name, $post[$class][$name]);
                }
            }
        }

        return $config;
    }

    public static function generateForm($config)
    {
        $str = "<h1>Configurator</h1>\n";
        $str .= "<form method=\"POST\">\n";
        foreach ($config as $class => $variables) {
            $str .= "<fieldset>\n";
            $str .= "<legend>$class</legend>\n";
            foreach ($variables as $v) {
                $name = $v['name'];
                $comment = $v['comment'];
                if ($comment) {
                    $comment = "($comment)";
                }
                $input = $class . '[' . $v['name'] . ']';
                $str .= "$name $comment<br/>\n";
                $value = htmlentities($v['value']);
                if (gettype($v['value']) == 'boolean') {
                    $str .= '<input type="radio" name="' . $input . '" value="1"' . ($value?' checked="checked"':'') . '> true';
                    $str .= '<input type="radio" name="' . $input . '" value="0"' . ((!$value)?' checked="checked"':'') . '> false <br/>';
                } else {
                    $str .= '<input type="text" name="' . $input . '" value="' . $value . '"><br/>';
                }
            }
            $str .= "</fieldset>\n";
        }
        $str .= "<br/>\n";
        $str .= '<input type="submit" value="Test and Save"><br/>';
        $str .= "</form>\n";

        return $str;
    }

    public static function parseConfig($code)
    {
        $config = array();
        $lines = preg_split("/\r?\n/", $code);
        foreach ($lines as $line) {
            if (preg_match('/^\s*class ([a-z]+)/i', $line, $matches)) {
                $class = $matches[1];
                $config[$class] = array();
            } elseif (preg_match('/^\s*public static \$([a-z]+)\s*=(.*);\s*(\/\/(.*))?$/i', $line, $matches)) {
                $name = $matches[1];
                $value = trim($matches[2]);
                if (is_numeric($value) && strpos($value, '.') !== false) {
                    $value = (float)$value;
                } elseif (is_numeric($value)) {
                    $value = (int)$value;
                } elseif (in_array($value, array('true', 'false'))) {
                    $value = $value == 'true'?:false;
                } else {
                    $value = trim($value, '\'"');
                }

                if (isset($matches[4])) {
                    $comment = trim($matches[4]);
                } else {
                    $comment = false;
                }
                $config[$class][] = compact('name', 'value', 'comment');
            }
        }

        return $config;
    }

    public static function generateCode($config)
    {
        $export = function ($v) {
            if (gettype($v['value']) == 'boolean') {
                return $v['value']?'true':'false';
            } else {
                return var_export($v['value'], true);
            }
        };
        $code = "<?php\n";
        $code .= "namespace MindaPHP\Config;\n";
        foreach ($config as $class => $variables) {
            $nameChars = $valueChars = 0;
            foreach ($variables as $v) {
                $nameChars = max($nameChars, strlen($v['name']));
                $valueChars = max($valueChars, strlen($export($v)));
            }
            $code .= "\nclass $class\n{\n";
            foreach ($variables as $v) {
                $name = sprintf("%-${nameChars}s", $v['name']);
                $value = sprintf("%-${valueChars}s", $export($v));
                $code .= "\tpublic static \$$name = $value;";
                if ($v['comment']) {
                    $code .= " // $v[comment]";
                }
                $code .= "\n";
            }
            $code .= "}\n";
        }

        return $code;
    }

    public function testConfig(&$config)
    {
        $parameters = array();
        foreach ($config as $class => &$variables) {
            foreach ($variables as &$v) {
                $parameters[$class . '_' . $v['name']] = &$v['value'];
            }
        }

        $mysqli = new mysqli($parameters['DB_host'], $parameters['DB_username'], $parameters['DB_password']);
        if ($mysqli->connect_error) {
            echo "ERROR: MySQL connect: ($mysqli->connect_errno) $mysqli->connect_error\n";

            return false;
        }
        echo "INFO: MySQL connected\n";
        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$parameters[DB_database]';";
        if (!$result = $mysqli->query($sql)) {
            echo "ERROR: MySQL database check: $mysqli->error\n";

            return false;
        } elseif ($result->num_rows) {
            echo "INFO: MySQL database exists\n";
        } else {
            if ($parameters['DB_username'] != 'root') {
                echo "ERROR: MySQL database not found: $parameters[DB_database]\n";

                return false;
            }
            $sql = "CREATE DATABASE `$parameters[DB_database]` COLLATE 'utf8_bin';";
            if (!$result = $mysqli->query($sql)) {
                echo "ERROR: MySQL database create: $mysqli->error\n";

                return false;
            }
            echo "INFO: MySQL database created\n";
            $host = $parameters['DB_host'] == 'localhost'?'localhost':'%';
            $pass = base64_encode(sha1(rand() . time(true) . $parameters['DB_database'], true));
            $sql = "CREATE USER '$parameters[DB_database]'@'$host' IDENTIFIED BY '$pass';";
            if (!$result = $mysqli->query($sql)) {
                echo "ERROR: MySQL user create: $mysqli->error\n";

                return false;
            }
            echo "INFO: MySQL user created\n";
            $sql = "GRANT ALL PRIVILEGES ON `$parameters[DB_database]`.* TO '$parameters[DB_database]'@'$host';";
            if (!$result = $mysqli->query($sql)) {
                echo "ERROR: MySQL grant user: $mysqli->error\n";

                return false;
            }
            echo "INFO: MySQL user granted\n";
            $parameters['DB_username'] = $parameters['DB_database'];
            $parameters['DB_password'] = $pass;
        }
        $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$parameters[DB_database]' AND TABLE_NAME = 'users';";
        if (!$result = $mysqli->query($sql)) {
            echo "ERROR: MySQL users table check: $mysqli->error\n";

            return false;
        } elseif (!$result->num_rows) {
            $sql = "CREATE TABLE `$parameters[DB_database]`.`users` (";
            $sql .= "`id` int(11) NOT NULL AUTO_INCREMENT,";
            $sql .= "`username` varchar(255) COLLATE utf8_bin NOT NULL,";
            $sql .= "`password` varchar(255) COLLATE utf8_bin NOT NULL,";
            $sql .= "`created` datetime NOT NULL,";
            $sql .= "PRIMARY KEY (`id`),";
            $sql .= "UNIQUE KEY `username` (`username`)";
            $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
            if (!$mysqli->query($sql)) {
                echo "ERROR: MySQL create users table: $mysqli->error\n";

                return false;
            }
            echo "INFO: MySQL users table created\n";
        } else {
            echo "INFO: MySQL users table exists\n";
        }
        if ($mysqli->close()) {
            echo "INFO: MySQL disconnected\n";
        }

        return true;
    }
}
