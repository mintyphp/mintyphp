<?php
class DBError extends \Exception {};

class DB
{
	public static $host=null;
	public static $username=null;
	public static $password=null;
	public static $database=null;
	public static $port=null;
	public static $socket=null;
	
  protected static $mysqli = null;
  
  protected static function connect()
  {
  	if (Router::getPhase()!='action') {
  		self::error('Database can only be used in MindaPHP action');
  	}
    if (!self::$mysqli) {
      $reflect = new ReflectionClass('mysqli');
      $args = array(self::$host,self::$username,self::$password,self::$database,self::$port,self::$socket);
      while (isset($args[count($args)-1]) && $args[count($args)-1] !== null) array_pop($args);
      self::$mysqli = $reflect->newInstanceArgs($args);
      if (mysqli_connect_errno()) self::error(mysqli_connect_error());
    }
  }
    
  protected static function error($message)
  {
    throw new DBError($message);
  }
   
  public static function qv($query)
  {
  	$result = forward_static_call_array('DB::q1', func_get_args());
    while (is_array($result)) {
      $key = array_shift(array_keys($result));
      $result = $result[$key];
    }
    return $result;
  }
    
  public static function q1($query)
  {
    $args = func_get_args();
    if (func_num_args() > 1) {
      array_splice($args,1,0,array(str_repeat('s', count($args)-1)));
    }
    return forward_static_call_array('DB::qt1', $args);
  }
    
  private static function qt1($query)
  {
    $result = forward_static_call_array('DB::qt', func_get_args());
    if (isset($result[0])) return $result[0];
    return $result;
  }
    
  public static function q($query)
  {
    $args = func_get_args();
    if (func_num_args() > 1) {
      array_splice($args,1,0,array(str_repeat('s', count($args)-1)));
    }
    return forward_static_call_array('DB::qt', $args);
  }
   
  private static function qt($query)
  {
    if (!Debugger::$enabled) {
      return forward_static_call_array('DB::_qt', func_get_args());
    }
    $time = microtime(true);
    $result = forward_static_call_array('DB::_qt', func_get_args());
    $duration = microtime(true)-$time;
    $arguments = func_get_args();
    if (strtoupper(substr(trim($query), 0, 6))=='SELECT') {
      $arguments[0] = 'explain '.$query;
      $explain = forward_static_call_array('DB::_qt', $arguments);
    } else {
      $explain = false;
    }
    $arguments = array_slice(func_get_args(),2);
    $equery = self::$mysqli->real_escape_string($query);
    Debugger::add('queries',compact('duration','query','equery','arguments','result','explain'));
    return $result;
  }
    
  private static function _qt($query)
  {
    self::connect();
  	$query = self::$mysqli->prepare($query);
    if (!$query) {
      return self::error(self::$mysqli->error,false);
    }
    if (func_num_args() > 1) {
      $args = array_slice(func_get_args(), 1);
      call_user_func_array(array($query, 'bind_param'),&$args);
    }
    $query->execute();
    if ($query->errno) {
      return self::error(mysqli_error(self::$mysqli),false);
    }
    if ($query->affected_rows > -1) {
      return $query->affected_rows;
    }
    $params = array();
    $meta = $query->result_metadata();
    while ($field = $meta->fetch_field()) {
      if (!$field->table && strpos($field->name, '.')) {
        $parts = explode('.', $field->name, 2);
        $params[] = &$row[$parts[0]][$parts[1]];
      } else {
        if (!isset($row[$field->table])) $row[$field->table] = array();
        $params[] = &$row[$field->table][$field->name];
      }
    }
    call_user_func_array(array($query, 'bind_result'), $params);

    $result = array();
    while ($query->fetch()) {
      $result[] = json_decode(json_encode($row),true);
    }

    $query->close(); 

    return $result;
  }
 
  public static function id()
  {
    return self::$mysqli->insert_id;
  }
    
  // Undocumented
  public static function handle()
  {
    self::connect();
    return self::$mysqli;
  }
  
  // Undocumented
  public static function options()
  {
    return call_user_func_array(array(self::$mysqli, 'options'), func_get_args());
  }
   
  // Undocumented
  public static function close()
  {
    return self::$mysqli->close();
  }

}
