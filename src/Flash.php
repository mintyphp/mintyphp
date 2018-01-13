<?php
namespace MindaPHP;

class Flash
{
	static $flashSessionKey = 'flash';
	
	public static function set($type,$message)
	{
		if (!isset($_SESSION[static::$flashSessionKey])) $_SESSION[static::$flashSessionKey] = array();
		$_SESSION[static::$flashSessionKey][$type] = $message;
	}
	
	public static function get()
	{
		if (isset($_SESSION[static::$flashSessionKey])) {
			$flash = $_SESSION[static::$flashSessionKey];
			unset($_SESSION[static::$flashSessionKey]);
		} else {
			$flash = array();
		}
		return $flash;	
	}
	
}