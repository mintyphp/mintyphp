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
            if (count($matches)==0) $matches = glob($root.'/404.*.php');
            if (count($matches)==0) $this->error('Could not find 404');
            if (count($matches)>1) $this->error('Mutiple actions matched: '.implode(', ',$matches));
            $this->view = $matches[0];
            $this->action = $this->extractAction($matches[0],$root,$dir);
            $this->template = $this->extractTemplate($matches[0],$root,$dir);
            $this->parameters = array();
            for ($p=$i;$p<count($parts);$p++) {
                $this->parameters[] = urldecode($parts[$p]);
            }
            break;
        }
        
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
       
    protected function extractAction($match,$root,$dir)
    {
      $match = $this->removePrefix($match,$root);
      if (!preg_match('/(.*)\.[^\.]+\.php$/', $match, $matches)) $this->error('Could not action from filename: '.$match);
      $root = $this->actionRoot;
      
      $matches = glob($root.$matches[1].'.php');
      if (count($matches)==0) return false;
      if (count($matches)>1) $this->error('Mutiple actions matched: '.implode(', ',$matches));
      return $matches[0];
    }
    
    public function getView()
    {
      return $this->view;
    }
        
    protected function extractTemplate($match,$root,$dir)
    {
        $match = $this->removePrefix($match,$root.$dir);
        if (!preg_match('/.*\.([^\.]+)\.php$/', $match, $matches)) $this->error('Could not extract template from filename: '.$match);
        $root = $this->templateRoot;
        $dir = '/';
        if ($matches[1]=='none') return 'none';
        $matches = glob($root.$dir.$matches[1].'*.php');
        if (count($matches)==0) $this->error('Could not find template');
        if (count($matches)>1) $this->error('Mutiple templates matched: '.implode(', ',$matches));
        return $matches[0];
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