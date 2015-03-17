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
    
    public static function add($key,$var,$expire=0)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$result = static::$memcache->add(static::$prefix.$key,$var,0,$expire);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$method = 'add';
    		$arguments = compact('key','var','expire');
    		Debugger::add('cache',compact('duration','method','arguments','result'));
    	}    	
    	return $result;
    }
    
    public static function decrement($key,$value=1)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$result = static::$memcache->decrement(static::$prefix.$key,$value);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$method = 'decrement';
    		$arguments = compact('key','value');
    		Debugger::add('cache',compact('duration','method','arguments','result'));
    	}
    	return $result;
    }
    
    public static function delete($key)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$result = static::$memcache->delete(static::$prefix.$key,0);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$method = 'delete';
    		$arguments = compact('key');
    		Debugger::add('cache',compact('duration','method','arguments','result'));
    	}
    	return $result;
    }
    
    public static function get($key)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$result = static::$memcache->get(static::$prefix.$key);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$method = 'get';
    		$arguments = compact('key');
    		Debugger::add('cache',compact('duration','method','arguments','result'));
    	}
    	return $result;
    }
    
    public static function increment($key,$value=1)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$result = static::$memcache->increment(static::$prefix.$key,$value);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$method = 'increment';
    		$arguments = compact('key','value');
    		Debugger::add('cache',compact('duration','method','arguments','result'));
    	}
    	return $result;
    }
    
    public static function replace($key,$var,$expire=0)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$result = static::$memcache->replace(static::$prefix.$key,$var,0,$expire);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$method = 'replace';
    		$arguments = compact('key','var','expire');
    		Debugger::add('cache',compact('duration','method','arguments','result'));
    	}
    	return $result;
     
    }
    
    public static function set($key,$var,$expire=0)
    {
    	if (Debugger::$enabled) $time = microtime(true);
    	if (!static::$memcache) static::initialize();
    	$result = static::$memcache->set(static::$prefix.$key,$var,0,$expire);
    	if (Debugger::$enabled) {
    		$duration = microtime(true)-$time;
    		$method = 'set';
    		$arguments = compact('key','var','expire');
    		Debugger::add('cache',compact('duration','method','arguments','result'));
    	}
    	return $result;
    }
    
}