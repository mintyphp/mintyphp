<?php
namespace MindaPHP;

class CacheError extends \Exception {};

class Cache
{
	public static $prefix='mindaphp';
	public static $servers='localhost';
	public static $compressTreshold=20000;
	public static $compressSavings=0.2;
	
	protected static $memcache = null;
	
	protected static function initialize()
    {
    	if (!static::$memcache) {
    		static::$memcache = new \Memcache();
    		$servers = explode(',',static::$servers);
    		$servers = array_map(function($server){
    			$server = explode(':',trim($server));
    			if (count($server)==1) $server[1]='11211';
    			return $server;
    		}, $servers);
    		foreach ($servers as $server) {
    			static::$memcache->addServer($server[0], $server[1]);
    		}
    		static::$memcache->setCompressThreshold(static::$compressTreshold,static::$compressSavings);
    	}
    }
    
    protected static function variable($var) {
    	$type = gettype($var);
    	switch($type) {
    		case 'boolean': $result = $var?'TRUE':'FALSE'; break;
			case 'integer': $result = $var; break;
			case 'NULL': $result = $var; break;
    		case 'string': $result = '(string:'.strlen($var).')'; break;
			case 'array': $result = '(array:'.count($var).')'; break;
			default: $result = '('.$type.')';
    	}
    	return $result;
    }
    
    public static function add($key,$var,$expire=0)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$res = static::$memcache->add(static::$prefix.$key,$var,0,$expire);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$command = 'add';
    		$arguments = array($key,static::variable($var));
    		if ($expire) $arguments[]=$expire;
    		$result = static::variable($res);
    		Debugger::add('cache',compact('duration','command','arguments','result'));
    	}    	
    	return $res;
    }
    
    public static function decrement($key,$value=1)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$res = static::$memcache->decrement(static::$prefix.$key,$value);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$command = 'decrement';
    		$arguments = array($key);
    		if ($value>1) $arguments[]=$value;
    		$result = static::variable($res);
    		Debugger::add('cache',compact('duration','command','arguments','result'));
    	}
    	return $res;
    }
    
    public static function delete($key)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$res = static::$memcache->delete(static::$prefix.$key,0);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$command = 'delete';
    		$arguments = array($key);
    		$result = static::variable($res);
    		Debugger::add('cache',compact('duration','command','arguments','result'));
    	}
    	return $res;
    }
    
    public static function get($key)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$res = static::$memcache->get(static::$prefix.$key);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$command = 'get';
    		$arguments = array($key);
    		$result = static::variable($res);
    		Debugger::add('cache',compact('duration','command','arguments','result'));
    	}
    	return $res;
    }
    
    public static function increment($key,$value=1)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$res = static::$memcache->increment(static::$prefix.$key,$value);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$command = 'increment';
    		$arguments = array($key);
    		if ($value>1) $arguments[]=$value;
    		$result = static::variable($res);
    		Debugger::add('cache',compact('duration','command','arguments','result'));
    	}
    	return $res;
    }
    
    public static function replace($key,$var,$expire=0)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$res = static::$memcache->replace(static::$prefix.$key,$var,0,$expire);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$command = 'replace';
    		$arguments = array($key,static::variable($var));
    		if ($expire) $arguments[]=$expire;
    		$result = static::variable($res);
    		Debugger::add('cache',compact('duration','command','arguments','result'));
    	}
    	return $res;
     
    }
    
    public static function set($key,$var,$expire=0)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$res = static::$memcache->set(static::$prefix.$key,$var,0,$expire);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$command = 'set';
    		$arguments = array($key,static::variable($var));
    		if ($expire) $arguments[]=$expire;
    		$result = static::variable($res);
    		Debugger::add('cache',compact('duration','command','arguments','result'));
    	}
    	return $res;
    }
    
}
