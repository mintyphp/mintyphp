<?php
namespace MindaPHP;

class RouterError extends \Exception {};

class Router
{
  protected static $method = null;
  protected static $request = null;
  protected static $script = null;
  
  public static $pageRoot = '../pages';
  public static $templateRoot = '../templates';
  public static $allowGet = false;
  
  protected static $url = null;
  protected static $view = null;
  protected static $action = null;
  protected static $content = null;
  protected static $template = null;
  protected static $parameters = null;
  
  protected static $routes = array();
  
  protected static $initialized = false;
  protected static $phase = 'init';
  
  protected static function initialize()
  {
    if (static::$initialized) return;
    static::$initialized = true;
    static::$method = $_SERVER['REQUEST_METHOD'];
    static::$request = $_SERVER['REQUEST_URI'];
    static::$script = $_SERVER['SCRIPT_NAME'];
    static::route();
    static::applyRoutes();
  }

  protected static function error($message)
  {
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
    if (Debugger::$enabled) {
  		Debugger::set('redirect',$url);
  		Debugger::end('redirect');
  	}
  	die(header("Location: $url",true,$permanent?301:302));
  }
  
  protected static function route()
  {
    $root = static::$pageRoot;
    $dir = '/';

    $request = static::removePrefix(static::$request,static::$script?:'');
    $questionMarkPosition = strpos($request,'?');
    $hasGet = $questionMarkPosition===false;
    if (!$hasGet) $request = substr($request,0,$questionMarkPosition);
    $parts = explode('/',ltrim($request,'/'));
    foreach ($parts as $i=>$part) {
      static::$url = $dir.$part;
      if ($part && file_exists($root.$dir.$part) && is_dir($root.$dir.$part)) {
        $dir .= $part.'/';
        if ($i==count($parts)-1) $part = '';
        else continue;
      } 
      if (!$part) $part = 'index';
      $matches = glob($root.$dir.$part.'(*).phtml');
      if (count($matches)==0) $matches = glob($root.$dir.'index(*).phtml');
      else $i++;
      $csrfOk = static::$method=='GET'?true:Session::checkCsrfToken();
      if (!$csrfOk) { $matches = glob($root.'/403(*).phtml'); $dir='/'; $i=count($parts); }
      if (!static::$allowGet && !$hasGet) { $matches = glob($root.'/405(*).phtml'); $dir='/'; $i=count($parts); }
      if (count($matches)==0) { $matches = glob($root.'/404(*).phtml'); $dir='/'; $i=count($parts); }
      if (count($matches)==0) static::error('Could not find 404');
      if (count($matches)>1) static::error('Mutiple views matched: '.implode(', ',$matches));
      list($view,$template) = static::extractParts($matches[0],$root,$dir);
      static::$view = static::$pageRoot.$dir.$view.'('.$template.').phtml';
      static::$template = $template!='none'?static::$templateRoot.'/'.$template.'.php':false;
      $matches = glob($root.$dir.$view.'().php');
      if (count($matches)==0) $matches = glob($root.$dir.$view.'($*).php');
      if (count($matches)==0) static::$action = false;
      if (count($matches)>1) static::error('Mutiple actions matched: '.implode(', ',$matches));
      if (count($matches)==1) {
      	static::$action = $matches[0];
      	$parameterNames = static::extractParameterNames($matches[0],$root,$dir,$view);
        $parameters = array_slice($parts, $i, count($parts)-$i);
        if (count($parameters)>count($parameterNames)) static::redirect(static::getUrl());
        $parameters = array_map('urldecode', $parameters);
        if (count($parameters)<count($parameterNames)) { 
          for ($i=count($parameters); $i<count($parameterNames); $i++) { 
        	array_push($parameters,null);
          }
        }
        if (count($parameterNames)){
          static::$parameters = array_combine($parameterNames, $parameters);
        }
      }      
      if (Debugger::$enabled) {
        $method = static::$method;
        $routed = $request!=$_SERVER['REQUEST_URI']?$request:false;
        $request = $_SERVER['REQUEST_URI'];
        $url = static::$url;
        $viewFile = static::$view;
        $actionFile = static::$action;
        $templateFile = static::$template;
        $parameters = array();
        $parameters['url'] = static::$parameters===null?:array();
        $parameters['get'] = $_GET;
        $parameters['post'] = $_POST;
        Debugger::set('router',compact('method','csrfOk','request','routed','url','dir','view','template','viewFile','actionFile','templateFile','parameters'));
      }
      break;
    }
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
	  	if (rtrim(static::$request,'/') == rtrim($sourcePath,'/')) {
	  		static::$request = $destinationPath;
	  		static::route();
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
    static::$phase = 'action';
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
    static::$phase = 'view';
    return static::$view;
  }
  
  public static function setContent($content)
  {
  	if (!static::$initialized) static::initialize();
  	static::$content = $content;
  }
  
  public static function getContent()
  {
  	if (!static::$initialized) static::initialize();
  	return static::$content;
  }
  
  public static function getTemplate()
  {
    if (!static::$initialized) static::initialize();
    static::$phase = 'view';
    return static::$template;
  }

  public static function getParameters()
  {
  	if (!static::$initialized) static::initialize();
    if (!static::$parameters) return array();
  	else return static::$parameters;
  }
  
  public static function getPhase()
  {
  	return static::$phase;
  }
}