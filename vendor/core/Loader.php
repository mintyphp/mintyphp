<?php
class Loader
{
	protected static $parentPath = null;
	protected static $paths = null;
	protected static $initialized = false;
	protected static $files = null;
	
	protected static function initialize()
	{
		if (self::$initialized) return;
		self::$initialized = true;
		self::$parentPath = dirname(dirname(__FILE__));
		self::$paths = array();
		self::$files = array(__FILE__);
	}

	public static function register($path,$namespace) {
		if (!self::$initialized) self::initialize();
		self::$paths[$namespace] = trim($path,DIRECTORY_SEPARATOR);
	}
	
	public static function load($class) {
		if (class_exists($class,false)) return; 
		if (!self::$initialized) self::initialize();
		$nsChar = '\\';
		foreach (self::$paths as $namespace => $path) {
			if (!$namespace || $namespace.$nsChar === substr($class, 0, strlen($namespace.$nsChar))) {
				
				$fileName = substr($class,strlen($namespace.$nsChar)-1);
				$fileName = str_replace($nsChar, DIRECTORY_SEPARATOR, ltrim($fileName,$nsChar));
				$fileName = self::$parentPath.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$fileName.'.php';
				
				if (file_exists($fileName)) {
				  self::$files[] = $fileName;
				  include $fileName;
  			  self::setParameters($class);
				  return true;
				} 
			}
		}
		return false;
	}
	
	
	protected static function setParameters($className) {
		$parameterClassName = 'Config\\'.$className;
		if (!class_exists($parameterClassName,false)) return;
		$parameterClass = new ReflectionClass($parameterClassName);
		$staticMembers = $parameterClass->getStaticProperties();
		foreach($staticMembers as $field => &$value) {
			if (property_exists($className, $field)) {
				$className::$$field = $value;
			}
		}
	}

	public static function getFiles() {
		if (!self::$initialized) self::initialize();
		return self::$files;
	}
}

spl_autoload_register(array('Loader', 'load'));