<?php
namespace MindaPHP;

class RouterError extends \Exception {};

class Router
{
  protected static $method = null;
  protected static $original = null;
  protected static $request = null;
  protected static $script = null;
  
  public static $baseUrl = '/';
  public static $pageRoot = '../pages/';
  public static $templateRoot = '../templates/';
  public static $allowGet = false;
  
  protected static $url = null;
  protected static $view = null;
  protected static $action = null;
  protected static $template = null;
  protected static $parameters = null;
  
  protected static $routes = array();
  
  public static $initialized = false;
  
  protected static function initialize()
  {
    if (static::$initialized) return;
    static::$initialized = true;
    static::$method = $_SERVER['REQUEST_METHOD'];
    static::$request = $_SERVER['REQUEST_URI'];
    static::$script = $_SERVER['SCRIPT_NAME'];
    static::applyRoutes();
    static::route();
  }

  protected static function error($message)
  {
  	if (Debugger::$enabled) {
  		Debugger::set('status',500);
  	}
    throw new RouterError($message);
  }

  protected static function removePrefix($string,$prefix)
  {
    if (substr($string,0,strlen($prefix))==$prefix) {
      $string = substr($string,strlen($prefix));
    }
  
    return $string;
  }
  
  public static function redirect($url,$permanent=false)
  {
  	if (!static::$initialized) static::initialize();
    $url = static::$baseUrl . $url;
    $status = $permanent?301:302;
  	if (Debugger::$enabled) {
  		Debugger::set('redirect',$url);
  		Debugger::set('status',$status);
  		Debugger::end('redirect');
  	}
  	die(header("Location: $url",true,$permanent?301:302));
  }
  
  protected static function route()
  {
    $root = static::$pageRoot;
    $dir = '';
    $redirect = false;
    $status = 200;
    
    $request = static::removePrefix(static::$request,static::$script?:'');
    $request = static::removePrefix($request,static::$baseUrl);
    if (static::$original===null) static::$original = $request;
    
    $questionMarkPosition = strpos($request,'?');
    $hasGet = $questionMarkPosition!==false;
    if ($hasGet) $request = substr($request,0,$questionMarkPosition);
    
    $parts = explode('/',$request);
    for ($i=count($parts);$i>=0;$i--) {
    	if ($i==0) $dir = ''; 
    	else $dir = implode('/',array_slice($parts, 0, $i)).'/';
    	if (file_exists($root.$dir) && is_dir($root.$dir)) {
    		$parameters = array_slice($parts, $i, count($parts)-$i);
    		
    		if (count($parameters)) { 
    			$part = array_shift($parameters);  
    			$matches = glob($root.$dir.$part.'(*).phtml');
    			if (count($matches)==0) {
    				array_unshift($parameters,$part);
    				$matches = glob($root.$dir.'index(*).phtml');
    			}
    		} else { 
    			$matches = glob($root.$dir.'index(*).phtml');
    		}
    		
    		$csrfOk = static::$method=='GET'?true:Session::checkCsrfToken();
    		if (!$csrfOk) {
    			$status = 403; $matches = glob($root.'error/forbidden(*).phtml'); $dir=''; $i=count($parts);
    		}
    		if (!static::$allowGet && $hasGet) {
    			$status = 405; $matches = glob($root.'error/method_not_allowed(*).phtml'); $dir=''; $i=count($parts);
    		}
    		if (count($matches)==0) {
    			$status = 404; $matches = glob($root.'error/not_found(*).phtml'); $dir=''; $i=count($parts);
    		}
    		if (count($matches)==0) static::error('Could not find 404');
    		if (count($matches)>1) static::error('Mutiple views matched: '.implode(', ',$matches));
    		list($view,$template) = static::extractParts($matches[0],$root,$dir);
    		static::$url = $dir.$view;
    		static::$view = static::$pageRoot.$dir.$view.'('.$template.').phtml';
    		static::$template = $template!='none'?static::$templateRoot.$template:false;
    		$matches = glob($root.$dir.$view.'().php');
    		if (count($matches)==0) $matches = glob($root.$dir.$view.'($*).php');
    		if (count($matches)==0) static::$action = false;
    		if (count($matches)>1) static::error('Mutiple actions matched: '.implode(', ',$matches));
    		static::$parameters = array();
    		if (count($matches)==1) {
    			static::$action = $matches[0];
    			$parameterNames = static::extractParameterNames($matches[0],$root,$dir,$view);
    			if (count($parameters)>count($parameterNames)) {
    				$redirect = static::$url.'/'.implode('/',array_slice($parameters, 0, count($parameterNames)));
    			}
    			$parameters = array_map('urldecode', $parameters);
    			if (count($parameters)<count($parameterNames)) {
    				for ($i=count($parameters); $i<count($parameterNames); $i++) {
    					array_push($parameters,null);
    				}
    			}
    			if (!$redirect && count($parameterNames)){
    				static::$parameters = array_combine($parameterNames, $parameters);
    			}
    		}
    		break;
    	}
    }
    if (Debugger::$enabled) {
    	$method = static::$method;
    	$request = '/'.static::$original;
    	$url = '/'.static::$url;
    	$viewFile = static::$view;
    	$actionFile = static::$action;
    	$templateFile = static::$template;
    	$parameters = array();
    	$parameters['url'] = static::$parameters;
    	$parameters['get'] = $_GET;
    	$parameters['post'] = $_POST;
    	Debugger::set('router',compact('method','csrfOk','request','url','dir','view','template','viewFile','actionFile','templateFile','parameters'));
    	Debugger::set('status',$status);
    }
    if ($redirect) static::redirect($redirect);
  }

  public static function getUrl()
  {
    if (!static::$initialized) static::initialize();
    return static::$url;
  }

  public static function addRoute($sourcePath,$destinationPath)
  {
  	static::$routes[$destinationPath] = $sourcePath;
  }
  
  protected static function applyRoutes()
  {
  	if (!static::$initialized) static::initialize();
  	foreach (static::$routes as $destinationPath => $sourcePath) {
  		if (rtrim(static::$request,'/') == rtrim(static::$baseUrl . $sourcePath,'/')) {
	  		static::$request = static::$baseUrl . $destinationPath;
	  		break;
	  	}
  	}
  }

  public static function getRequest()
  {
    if (!static::$initialized) static::initialize();
    return static::$request;
  }

  public static function getAction()
  {
    if (!static::$initialized) static::initialize();
    return static::$action;
  }

  protected static function extractParts($match,$root,$dir)
  {
    if (!static::$initialized) static::initialize();
    $match = static::removePrefix($match,$root.$dir);
    $parts = preg_split('/\(|\)/', $match);
    array_pop($parts);
    $template = array_pop($parts);
    if (!$template) static::error('Could not extract template from filename: '.$match);
    $action = array_pop($parts);
    if (!$action) static::error('Could not extract action from filename: '.$match);
    return array($action,$template);
  }

  protected static function extractParameterNames($match,$root,$dir,$view)
  {
  	if (!static::$initialized) static::initialize();
  	$match = static::removePrefix($match,$root.$dir.$view);
  	$parts = preg_split('/\(|\)/', $match);
  	array_pop($parts);
  	$parameterNames = array_pop($parts);
  	$parts = preg_match_all('/,?(\$([^,\)]+))+/', $parameterNames, $matches);
  	return $matches[2];
  }
  
  public static function getView()
  {
    if (!static::$initialized) static::initialize();
    return static::$view;
  }
  
  public static function getTemplateView()
  {
    if (!static::$initialized) static::initialize();
    $filename = static::$template.'.phtml';
    return file_exists($filename)?$filename:false;
  }

  public static function getTemplateAction()
  {
  	if (!static::$initialized) static::initialize();
    $filename = static::$template.'.php';
    return file_exists($filename)?$filename:false;
  }
  
  
  public static function getParameters()
  {
  	if (!static::$initialized) static::initialize();
    if (!static::$parameters) return array();
  	else return static::$parameters;
  }
  
  public static function getBaseUrl()
  {
  	$url = static::$baseUrl;
  	if (substr($url,0,4)!='http') {
  	  if (substr($url,0,2)!='//') $url = '//'.$_SERVER['SERVER_NAME'].$url;
  	  $s = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')?'s':'';
  	  $url = "http$s:$url";
  	}
  	return $url;
  }
}
