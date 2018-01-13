<?php
namespace MindaPHP;

class BufferError extends \Exception {};

class Buffer
{
	protected static $stack = array();
	protected static $data = array();
	
	protected static function error($message)
	{
		throw new BufferError($message);
	}
	
	public static function start($name)
	{
		array_push(static::$stack,$name);
		ob_start();
	}
	
	public static function end($name)
	{
		$top = array_pop(static::$stack);
		if ($top!=$name) {
			static::error("Buffer::end('$name') called, but Buffer::end('$top') expected.");
		}
		static::$data[$name] = ob_get_contents();
		ob_end_clean();
	}
	
	public static function set($name,$string)
	{
		static::$data[$name]=$string;
	}
	
	public static function get($name)
	{
		if (!isset(static::$data[$name])) return false;
		echo static::$data[$name];
		return true;
	}
	
}