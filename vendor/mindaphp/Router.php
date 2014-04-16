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
  
  protected static $forwards = array();
  
  protected static $initialized = false;
  protected static $phase = 'init';
  
  protected static function initialize()
  {
    if (self::$initialized) return;
    self::$initialized = true;
    self::$method = $_SERVER['REQUEST_METHOD'];
    self::$request = $_SERVER['REQUEST_URI'];
    self::$script = $_SERVER['SCRIPT_NAME'];
    self::route();
    self::forward();
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
  	if (!self::$initialized) self::initialize();
    if (self::getRequest()!=self::getUrl()) {
  		self::redirect(self::getUrl());
  	}
  }
  
  public static function redirect($url,$permanent=false)
  {
  	if (!self::$initialized) self::initialize();
    if (Debugger::$enabled) {
  		Debugger::set('redirect',$url);
  		Debugger::end('redirect');
  	}
  	die(header("Location: $url",true,$permanent?301:302));
  }
  
  protected static function route()
  {
    $root = self::$viewRoot;
    $dir = '/';

    $request = self::removePrefix(self::$request,self::$script?:'');
    $getOk = strpos($request, '?')===false;
    $parts = explode('/',ltrim($request,'/'));
    foreach ($parts as $i=>$part) {
      self::$url = $dir.$part;
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
      $csrfOk = self::$method=='GET'?true:Session::checkCsrfToken();
      if (!$csrfOk) { $matches = glob($root.'/403.*.php'); $dir='/'; $i=count($parts); }
      if (!self::$allowGet && !$getOk) { $matches = glob($root.'/405.*.php'); $dir='/'; $i=count($parts); }
      if (count($matches)==0) { $matches = glob($root.'/404.*.php'); $dir='/'; $i=count($parts); }
      if (count($matches)==0) self::error('Could not find 404');
      if (count($matches)>1) self::error('Mutiple views matched: '.implode(', ',$matches));
      list($view,$template) = self::extractParts($matches[0],$root,$dir);
      self::$view = self::$viewRoot.$dir.$view.'.'.$template.'.php';
      self::$action = self::$actionRoot.$dir.$view.'.php';
      if (!file_exists(self::$action)) self::$action = false;
      self::$template = $template!='none'?self::$templateRoot.'/'.$template.'.php':false;
      self::$parameters = array();
      for ($p=$i;$p<count($parts);$p++) {
        if (strpos($parts[$p],'?')!==false) break;
        self::$parameters[] = urldecode($parts[$p]);
      }
      if (Debugger::$enabled) {
        $method = self::$method;
        $redirect = $request!=$_SERVER['REQUEST_URI']?$request:false;
        $request = $_SERVER['REQUEST_URI'];
        $url = self::$url;
        $viewFile = self::$view;
        $actionFile = self::$action;
        $templateFile = self::$template;
        $parameters = array();
        $parameters['url'] = self::$parameters;
        $parameters['get'] = $_GET;
        $parameters['post'] = $_POST;
        Debugger::set('router',compact('method','csrfOk','request','redirect','url','dir','view','template','viewFile','actionFile','templateFile','parameters'));
      }
      break;
    }
  }

  public static function getUrl()
  {
    if (!self::$initialized) self::initialize();
    return self::$url;
  }

  public static function addForward($sourcePath,$destinationPath)
  {
  	self::$forwards[$destinationPath] = $sourcePath;
  }
  
  protected static function forward()
  {
  	if (!self::$initialized) self::initialize();
  	foreach (self::$forwards as $destinationPath => $sourcePath) {
	  	if (rtrim(self::$request,'/') == rtrim($sourcePath,'/')) {
	  		self::$request = $destinationPath;
	  		self::route();
	  		break;
	  	}
  	}
  }

  public static function getRequest()
  {
    if (!self::$initialized) self::initialize();
    return self::$request;
  }

  public static function getAction()
  {
    if (!self::$initialized) self::initialize();
    self::$phase = 'action';
    return self::$action;
  }

  protected static function extractParts($match,$root,$dir)
  {
    if (!self::$initialized) self::initialize();
    $match = self::removePrefix($match,$root.$dir);
    $parts = explode('.',$match);
    array_pop($parts);
    $template = array_pop($parts);
    $action = implode('.',$parts);
    if (!$action) self::error('Could not extract action from filename: '.$match);
    if (!$template) self::error('Could not extract template from filename: '.$match);
    return array($action,$template);
  }

  public static function getView()
  {
    if (!self::$initialized) self::initialize();
    self::$phase = 'view';
    return self::$view;
  }
  
  public static function setContent($content)
  {
  	if (!self::$initialized) self::initialize();
  	self::$content = $content;
  }
  
  public static function getContent()
  {
  	if (!self::$initialized) self::initialize();
  	return self::$content;
  }
  
  public static function getTemplate()
  {
    if (!self::$initialized) self::initialize();
    self::$phase = 'view';
    return self::$template;
  }

  public static function getParameters()
  {
  	if (!self::$initialized) self::initialize();
    if (!self::$parameters) return array();
  	else return self::$parameters;
  }
  
  public static function getPhase()
  {
  	return self::$phase;
  }
}