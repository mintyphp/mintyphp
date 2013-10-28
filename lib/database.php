<?php
class DatabaseError extends Exception {};

class Database
{
    protected $debugger;
    protected $mysqli;
    protected $arguments;
    
    public function __construct($debugger, $host, $username, $password, $database)
    {
        $this->debugger = $debugger;
        $this->mysqli = null;
        $this->arguments = array_slice(func_get_args(), 1);
    }

    private function connect()
    {
      if (!$this->mysqli) {
        $reflect  = new ReflectionClass('mysqli');
        $this->mysqli = $reflect->newInstanceArgs($this->arguments);
        if (mysqli_connect_errno()) $this->error(mysqli_connect_error());
      }
    }
    
    protected function error($message)
    {
      throw new DatabaseError($message);
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
        if (!$this->debugger) {
            return call_user_func_array(array($this, '_qt'), func_get_args());
        }
        $time = microtime(true);
        $result = call_user_func_array(array($this, '_qt'), func_get_args());
        $duration = microtime(true)-$time;
        $arguments = func_get_args();
        $arguments[0] = 'explain '.$query;
        $explain = call_user_func_array(array($this, '_qt'), $arguments);
        $arguments = array_slice(func_get_args(),2);
        $rows = $this->mysqli->affected_rows;
        $equery = $this->mysqli->real_escape_string($query);
        $this->debugger->add('queries',compact('duration','query','equery','arguments','rows','result','explain'));
        return $result;
    }
    
    private function _qt($query)
    {
        $this->connect();
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
        
        return $result;
    }
 
    public function id()
    {
        return $this->mysqli->insert_id;
    }
    
    public function handle()
    {
        $this->connect();
        return $this->mysqli;
    }
    
    public function __toString()
    {
        return 'Database';
    }
}
