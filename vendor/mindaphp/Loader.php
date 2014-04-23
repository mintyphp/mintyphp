<?php
namespace MindaPHP;

class Loader
{
	protected static $parentPath = null;
	protected static $paths = null;
	protected static $nsChar = '\\';
	protected static $initialized = false;
	protected static $files = null;
	
	protected static function initialize()
	{
		if (static::$initialized) return;
		static::$initialized = true;
		static::$parentPath = __FILE__;
		for ($i=substr_count(get_class(), static::$nsChar);$i>=0;$i--) {
			static::$parentPath = dirname(static::$parentPath);
		}
		static::$paths = array();
		static::$files = array(__FILE__);
	}

	public static function register($path,$namespace) {
		if (!static::$initialized) static::initialize();
		static::$paths[$namespace] = trim($path,DIRECTORY_SEPARATOR);
	}
	
	public static function load($class) {
		if (class_exists($class,false)) return true; 
		if (!static::$initialized) static::initialize();
		foreach (static::$paths as $namespace => $path) {
			if (!$namespace || $namespace.static::$nsChar === substr($class, 0, strlen($namespace.static::$nsChar))) {
				
				$fileName = substr($class,strlen($namespace.static::$nsChar)-1);
				$fileName = str_replace(static::$nsChar, DIRECTORY_SEPARATOR, ltrim($fileName,static::$nsChar));
				$fileName = static::$parentPath.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$fileName.'.php';
				
				if (file_exists($fileName)) {
				  static::$files[] = $fileName;
				  include $fileName;
				  return true;
				} 
			}
		}
		return false;
	}

	public static function loadCore($class) {
		if (class_exists(__NAMESPACE__.static::$nsChar.$class,false)) return true; 
		if (!static::$initialized) static::initialize();
		if (substr($class,0,strlen(__NAMESPACE__))==__NAMESPACE__) {
			$class = substr($class,strlen(__NAMESPACE__)+strlen(static::$nsChar));
		}
		if (strpos($class,static::$nsChar)===false) {
			$fileName = dirname(__FILE__).DIRECTORY_SEPARATOR.$class.'.php';
			if (file_exists($fileName)) {
				static::$files[] = $fileName;
				include $fileName;
				class_alias(__NAMESPACE__.static::$nsChar.$class,$class);
				static::setParameters($class);
				return true;
			}
		}
		return false;
	}
	
	protected static function setParameters($className) {
		$parameterClassName = __NAMESPACE__.static::$nsChar.'Config'.static::$nsChar.$className;
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
		if (!static::$initialized) static::initialize();
		return static::$files;
	}
}

class_alias(__NAMESPACE__.'\\Loader','Loader');
spl_autoload_register(array('Loader', 'loadCore'));
spl_autoload_register(array('Loader', 'load'));