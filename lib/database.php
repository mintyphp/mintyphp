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
    
    public function qv($query)
    {
      $result = call_user_func_array(array($this, 'q1'), func_get_args());
      while (is_array($result)) {
        $key = array_shift(array_keys($result));
        $result = $result[$key];
      }
      return $result;
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
        if (strtoupper(substr(trim($query), 0, 6))=='SELECT') {
          $arguments[0] = 'explain '.$query;
          $explain = call_user_func_array(array($this, '_qt'), $arguments);
        } else {
          $explain = false;
        }
        $arguments = array_slice(func_get_args(),2);
        $equery = $this->mysqli->real_escape_string($query);
        $this->debugger->add('queries',compact('duration','query','equery','arguments','result','explain'));
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
            if (!isset($row[$field->table])) $row[$field->table] = array();
            $params[] = &$row[$field->table][$field->name];
        }
        call_user_func_array(array($query, 'bind_result'), $params);

        $result = array();
        while ($query->fetch()) {
            $result[] = json_decode(json_encode($row),true);
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

    // Undocumented
    public function options()
    {
        return call_user_func_array(array($this->mysqli, 'options'), func_get_args());
    }
    
    // Undocumented
    public function close()
    {
      return $this->mysqli->close();
    }

}
