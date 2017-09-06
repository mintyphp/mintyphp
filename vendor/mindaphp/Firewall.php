<?php
namespace MindaPHP;

class Firewall
{
  public static $concurrency=10;
  public static $spinLockSeconds=0.15;
  public static $intervalSeconds=300;
  public static $cachePrefix='fw_concurrency_';
  public static $reverseProxy=false;
  
  protected static $key=false;
  
  protected static function getClientIp()
  {
  	if (static::$reverseProxy && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  		$ip = array_pop(explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']));
  	}
  	else{
  		$ip = $_SERVER['REMOTE_ADDR'];
  	}
  	return $ip;     
  }
  
  protected static function getKey()
  {
  	if (!static::$key) {
  		static::$key = static::$cachePrefix.'_'.static::getClientIp();
  	}
  	return static::$key;
  }
  
  public static function start()
  {
  	header_remove('X-Powered-By');
  	$key = static::getKey();
  	$start = microtime(true);
  	Cache::add($key,0,static::$intervalSeconds);
  	register_shutdown_function('Firewall::end');
  	while (Cache::increment($key)>static::$concurrency) {
  		Cache::decrement($key);
  		if (!static::$spinLockSeconds || microtime(true)-$start>static::$intervalSeconds) { 
  			http_response_code(429); 
  			die('429: Too Many Requests'); 
  		}
  		usleep(static::$spinLockSeconds*1000000);
  	}
  }
  
  public static function end()
  {
  	Cache::decrement(static::getKey());
  }

}
