<?php

class Database
{
    protected $debug;
    protected $mysqli;
    public $queryCount;
    public $queryDuration;
    
    public function __construct($debug, $host, $username, $password, $database)
    {
        $this->debug = (bool) $debug;
        $reflect  = new ReflectionClass('mysqli');
        $this->mysqli = $reflect->newInstanceArgs(array_slice(func_get_args(), 1));
        if (mysqli_connect_errno()) {
            $this->error(mysqli_connect_error());
        }
    }

    private function error($message, $die = true)
    {
        if ($this->debug) {
            header('Content-Type: text/plain');
            echo "Error: ".$message."\n";
            debug_print_backtrace();
        }
        if ($this->debug || $die) die();
        return false;
    }
    
    public function q1($query)
    {
        $args = func_get_args();
        if (func_num_args() > 1) {
            array_splice($args,1,0,array(str_repeat('s', count($args)-1)));
        }
        return call_user_func_array(array($this, 'qt1'), $args);
    }
    
    private function qt1($query)
    {
        $result = call_user_func_array(array($this, 'qt'), func_get_args());
        if (isset($result[0])) return $result[0];
        return $result;
    }
    
    public function q($query)
    {
        $args = func_get_args();
        if (func_num_args() > 1) {
            array_splice($args,1,0,array(str_repeat('s', count($args)-1)));
        }
        return call_user_func_array(array($this, 'qt'), $args);
    }
   
    private function qt($query)
    {
        if ($this->debug) $start = microtime(true);
      
        $query = $this->mysqli->prepare($query);
        if (!$query) {
            return $this->error($this->mysqli->error,false);
        }
        if (func_num_args() > 1) {
            $args = array_slice(func_get_args(), 1);
            call_user_func_array(array($query, 'bind_param'),&$args);
        }
        $query->execute();
        if ($query->errno) {
            return $this->error(mysqli_error($this->mysqli),false);
        }
        if ($query->affected_rows > -1) {
            return $query->affected_rows;
        }
        $params = array();
        $meta = $query->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }
        call_user_func_array(array($query, 'bind_result'), $params);

        $result = array();
        while ($query->fetch()) {
            $r = array();
            foreach ($row as $key => $val) {
                $r[$key] = $val;
            }
            $result[] = $r;
        }

        $query->close(); 
        
        if ($this->debug) {
            $this->queryCount++;
            $this->queryDuration += round((microtime(true)-$start)*1000,3);
        }
        
        return $result;
    }
 
    public function id()
    {
        return $this->mysqli->insert_id;
    }
    
    public function handle()
    {
        return $this->mysqli;
    }
    
    public function __toString()
    {
        return 'Database';
    }
}
