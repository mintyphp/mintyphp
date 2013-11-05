<?php
class RouterError extends Exception {};

class Router
{
    protected $debugger;
    
    protected $request;
    protected $script;
    protected $actionRoot;
    protected $templateRoot;
    
    protected $allowGet;

    protected $url = null;
    protected $view = null;
    protected $action = null;
    protected $template = null;
    protected $parameters = null;
    
    function __construct($debugger, $actionRoot, $viewRoot, $templateRoot, $allowGet = false)
    {
        $this->debugger = $debugger;
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->request = $_SERVER['REQUEST_URI'];
        $this->script = $_SERVER['SCRIPT_NAME'];
        $this->viewRoot = $viewRoot;
        $this->actionRoot = $actionRoot;
        $this->templateRoot = $templateRoot;
        $this->allowGet = $allowGet;
        $this->route();
    }

    protected function error($message)
    {
        throw new RouterError($message);
    }

    protected function removePrefix($string,$prefix)
    {
      if (substr($string,0,strlen($prefix))==$prefix) {
        $string = substr($string,strlen($prefix));
      }
    
      return $string;
    }
    
    protected function route()
    {
        $root = $this->viewRoot;
        $dir = '/';

        $request = $this->removePrefix($this->request,$this->script?:'');
        $getOk = strpos($request, '?')===false;
        $parts = explode('/',ltrim($request,'/'));
        foreach ($parts as $i=>$part) {
            $this->url = $dir.$part;
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
            $csrfOk = $this->check_csrf_token($this->method);
            if (!$csrfOk) { $matches = glob($root.'/403.*.php'); $i=count($parts); }
            if (!$this->allowGet && !$getOk) { $matches = glob($root.'/405.*.php'); $i=count($parts); }
            if (count($matches)==0) { $matches = glob($root.'/404.*.php'); $i=count($parts); }
            if (count($matches)==0) $this->error('Could not find 404');
            if (count($matches)>1) $this->error('Mutiple views matched: '.implode(', ',$matches));
            list($view,$template) = $this->extractParts($matches[0],$root,$dir);
            $this->view = $this->viewRoot.$dir.$view.'.'.$template.'.php';
            $this->action = $this->actionRoot.$dir.$view.'.php';
            if (!file_exists($this->action)) $this->action = false;
            $this->template = $template!='none'?$this->templateRoot.'/'.$template.'.php':false;
            $this->parameters = array();
            for ($p=$i;$p<count($parts);$p++) {
                if (strpos($parts[$p],'?')!==false) break;
                $this->parameters[] = urldecode($parts[$p]);
            }
            if ($this->debugger) {
              $method = $this->method;
              $redirect = $request!=$_SERVER['REQUEST_URI']?$request:false;
              $request = $_SERVER['REQUEST_URI'];
              $url = $this->url;
              $viewFile = $this->view;
              $actionFile = $this->action;
              $templateFile = $this->template;
              $parameters = array();
              $parameters['url'] = $this->parameters;
              $parameters['get'] = $_GET;
              $parameters['post'] = $_POST;
              $this->debugger->set('router',compact('method','csrfOk','request','redirect','url','dir','view','template','viewFile','actionFile','templateFile','parameters'));
            }
            break;
        }

    }

    protected function check_csrf_token($method)
    {
        if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = rand(0, PHP_INT_MAX);
            
        if ($method=='POST') {
            if (isset($_POST['csrf_token'])) {
                $success = $_POST['csrf_token'] == $_SESSION['csrf_token'];
                //unset($_POST['csrf_token']);
            } else {
                $success = false;
            }
        } else {
            $success = true;
        }

        return $success;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function redirect($request,$location)
    {
        if (rtrim($this->request,'/') == rtrim($request,'/')) {
          $this->request = $location;
          $this->route();
        }
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getAction()
    {
      return $this->action;
    }

    protected function extractParts($match,$root,$dir)
    {
      $match = $this->removePrefix($match,$root.$dir);
      $parts = explode('.',$match);
      array_pop($parts);
      $template = array_pop($parts);
      $action = implode('.',$parts);
      if (!$action) $this->error('Could not extract action from filename: '.$match);
      if (!$template) $this->error('Could not extract template from filename: '.$match);
      return array($action,$template);
    }

    public function getView()
    {
      return $this->view;
    }
    
    public function getTemplate()
    {
        return $this->template;
    }

    public function getParameters()
    {
    	if (!$this->parameters) return array();
    	else return $this->parameters;
    }

}