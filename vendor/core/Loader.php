<?php
class Loader
{
	protected static $parentPath = null;
	protected static $paths = null;
	protected static $nsChar = '\\';
	protected static $initialized = false;
	protected static $files = null;
	
	protected static function initialize()
	{
		if (self::$initialized) return;
		self::$initialized = true;
		self::$parentPath = dirname(dirname(__FILE__));
		for ($i=substr_count(get_class(), self::$nsChar);$i>1;$i--) {
			self::$parentPath = dirname(self::$parentPath);
		}
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
		foreach (self::$paths as $namespace => $path) {
			if (!$namespace || $namespace.self::$nsChar === substr($class, 0, strlen($namespace.self::$nsChar))) {
				
				$fileName = substr($class,strlen($namespace.self::$nsChar)-1);
				$fileName = str_replace(self::$nsChar, DIRECTORY_SEPARATOR, ltrim($fileName,self::$nsChar));
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
		$parameterClass = new \ReflectionClass($parameterClassName);
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