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
		if (self::$initialized) return;
		self::$initialized = true;
		self::$parentPath = __FILE__;
		for ($i=substr_count(get_class(), self::$nsChar);$i>=0;$i--) {
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
		if (class_exists($class,false)) return true; 
		if (!self::$initialized) self::initialize();
		foreach (self::$paths as $namespace => $path) {
			if (!$namespace || $namespace.self::$nsChar === substr($class, 0, strlen($namespace.self::$nsChar))) {
				
				$fileName = substr($class,strlen($namespace.self::$nsChar)-1);
				$fileName = str_replace(self::$nsChar, DIRECTORY_SEPARATOR, ltrim($fileName,self::$nsChar));
				$fileName = self::$parentPath.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$fileName.'.php';
				
				if (file_exists($fileName)) {
				  self::$files[] = $fileName;
				  include $fileName;
				  return true;
				} 
			}
		}
		return false;
	}

	public static function loadCore($class) {
		if (class_exists(__NAMESPACE__.self::$nsChar.$class,false)) return true; 
		if (!self::$initialized) self::initialize();
		if (substr($class,0,strlen(__NAMESPACE__))==__NAMESPACE__) {
			$class = substr($class,strlen(__NAMESPACE__)+strlen(self::$nsChar));
		}
		if (strpos($class,self::$nsChar)===false) {
			$fileName = dirname(__FILE__).DIRECTORY_SEPARATOR.$class.'.php';
			if (file_exists($fileName)) {
				self::$files[] = $fileName;
				include $fileName;
				class_alias(__NAMESPACE__.self::$nsChar.$class,$class);
				self::setParameters($class);
				return true;
			}
		}
		return false;
	}
	
	protected static function setParameters($className) {
		$parameterClassName = __NAMESPACE__.self::$nsChar.'Config'.self::$nsChar.$className;
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

class_alias(__NAMESPACE__.'\\Loader','Loader');
spl_autoload_register(array('Loader', 'loadCore'));
spl_autoload_register(array('Loader', 'load'));