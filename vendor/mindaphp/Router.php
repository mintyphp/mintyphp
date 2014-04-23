<?php
namespace MindaPHP;

class RouterError extends \Exception {};

class Router
{
  protected static $method = null;
  protected static $request = null;
  protected static $script = null;
  
  public static $viewRoot = '../views';
  public static $actionRoot = '../actions';
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
  
  public static function parameterless()
  {
  	if (!static::$initialized) static::initialize();
    if (static::getRequest()!=static::getUrl()) {
  		static::redirect(static::getUrl());
  	}
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
    $root = static::$viewRoot;
    $dir = '/';

    $request = static::removePrefix(static::$request,static::$script?:'');
    $getOk = strpos($request, '?')===false;
    $parts = explode('/',ltrim($request,'/'));
    foreach ($parts as $i=>$part) {
      static::$url = $dir.$part;
      if (strpos($part,'?')!==false) {
        $part = substr($part,0,strpos($part,'?'));
        $i=count($parts);
      } else if ($part && file_exists($root.$dir.$part) && is_dir($root.$dir.$part)) {
        $dir .= $part.'/';
        if ($i==count($parts)-1) $part = '';
        else continue;
      } 
      if (!$part) $part = 'index';
      $matches = glob($root.$dir.$part.'.*.php');
      if (count($matches)==0) $matches = glob($root.$dir.'index.*.php');
      else $i++;
      $csrfOk = static::$method=='GET'?true:Session::checkCsrfToken();
      if (!$csrfOk) { $matches = glob($root.'/403.*.php'); $dir='/'; $i=count($parts); }
      if (!static::$allowGet && !$getOk) { $matches = glob($root.'/405.*.php'); $dir='/'; $i=count($parts); }
      if (count($matches)==0) { $matches = glob($root.'/404.*.php'); $dir='/'; $i=count($parts); }
      if (count($matches)==0) static::error('Could not find 404');
      if (count($matches)>1) static::error('Mutiple views matched: '.implode(', ',$matches));
      list($view,$template) = static::extractParts($matches[0],$root,$dir);
      static::$view = static::$viewRoot.$dir.$view.'.'.$template.'.php';
      static::$action = static::$actionRoot.$dir.$view.'.php';
      if (!file_exists(static::$action)) static::$action = false;
      static::$template = $template!='none'?static::$templateRoot.'/'.$template.'.php':false;
      static::$parameters = array();
      for ($p=$i;$p<count($parts);$p++) {
        if (strpos($parts[$p],'?')!==false) break;
        static::$parameters[] = urldecode($parts[$p]);
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
        $parameters['url'] = static::$parameters;
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
    $parts = explode('.',$match);
    array_pop($parts);
    $template = array_pop($parts);
    $action = implode('.',$parts);
    if (!$action) static::error('Could not extract action from filename: '.$match);
    if (!$template) static::error('Could not extract template from filename: '.$match);
    return array($action,$template);
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