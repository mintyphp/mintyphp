<?php

class Router
{
    protected $debug;

    protected $request;
    protected $actionRoot;
    protected $templateRoot;

    protected $url = null;
    protected $view = null;
    protected $action = null;
    protected $template = null;
    protected $parameters = null;

    protected function removePrefix($string,$prefix)
    {
        if (substr($string,0,strlen($prefix))==$prefix) {
            $string = substr($string,strlen($prefix));
        }

        return $string;
    }

    function __construct($debug, $request, $actionRoot, $viewRoot, $templateRoot)
    {

        $this->request = $request;
        $this->viewRoot = $viewRoot;
        $this->actionRoot = $actionRoot;
        $this->templateRoot = $templateRoot;

        $this->debug = (bool) $debug;

        $this->route();
    }

    protected function error($message)
    {
        if ($this->debug) {
            header('Content-Type: text/plain');
            echo "Error: ".$message."\n";
            debug_print_backtrace();
        }
        die();
    }

    protected function route()
    {
        $root = $this->viewRoot;
        $dir = '/';

        $request = $this->removePrefix($this->request,'/');
        $parts = explode('/',$request);
        foreach ($parts as $i=>$part) {
            $this->url = $dir.$part;
            if ($part && file_exists($root.$dir.$part) && is_dir($root.$dir.$part)) {
                $dir .= $part.'/';
                if ($i==count($parts)-1) $part = '';
                else continue;
            }
            if (!$part) $part = 'index';
            $matches = glob($root.$dir.$part.'*.php');
            if (count($matches)==0) $matches = glob($root.$dir.'index.*.php');
            else $i++;
            if (!$this->check_csrf_token()) $matches = glob($root.'/403.*.php');
            if (count($matches)==0) $matches = glob($root.'/404.*.php');
            if (count($matches)==0) $this->error('Could not find 404');
            if (count($matches)>1) $this->error('Mutiple views matched: '.implode(', ',$matches));
            list($view,$template) = $this->extractParts($matches[0],$root,$dir);
            $this->view = $this->viewRoot.$dir.$view.'.'.$template.'.php';
            $this->action = $this->actionRoot.$dir.$view.'.php';
            if (!file_exists($this->action)) $this->action = false;
            $this->template = $this->templateRoot.'/'.$template.'.php';
            $this->parameters = array();
            for ($p=$i;$p<count($parts);$p++) {
                $this->parameters[] = urldecode($parts[$p]);
            }
            break;
        }

    }

    private function check_csrf_token()
    {
        if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = rand(0, PHP_INT_MAX);
            
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            $success = isset($_POST['csrf_token']) && ($_POST['csrf_token'] == $_SESSION['csrf_token']);
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
    
    public function __toString()
    {
        return 'Router: '.$this->request;
    }
}