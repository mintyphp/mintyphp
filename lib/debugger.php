<?php
class Debugger
{
    private $requests;
    private $request;
    
    public function __construct($history)
    {
        if (!isset($_SESSION['debugger'])) $_SESSION['debugger'] = array();
        $this->requests = &$_SESSION['debugger'];
        $this->request = array('log'=>array(),'queries'=>array());
        array_unshift($this->requests,&$this->request);
        while (count($this->requests)>$history) array_pop($this->requests);
        $this->set('start',microtime(true));
    }

    public function set($key,$value)
    {
        $this->request[$key] = $value;
    }

    public function add($key,$value)
    {
      if (!isset($this->request[$key])) $this->request[$key] = array();
      $this->request[$key][] = $value;
    }
    
    public function get($key)
    {
      return isset($this->request[$key])?$this->request[$key]:false;
    }
    
    public function end($type)
    {
        $this->set('type',$type);
        $this->set('duration',microtime(true)-$this->get('start'));
        $this->set('memory',memory_get_peak_usage(true));
    }
    
    public function toolbar()
    {
        $this->end('ok');
        $html = '<div id="debugger-bar" style="position: fixed; width:100%; left: 0; bottom: 0; border-top: 1px solid silver; background: white;">';
        $html.= '<div style="margin:6px;">';
        $javascript = "document.getElementById(\'debugger-bar\').style.display=\'none\'; return false;";
        $html.= '<a href="#" onclick="'.$javascript.'" style="float:right;">close</a>';
        $html.= self::formatRequest($this->requests[0]);
        $html.= ' - <a href="/debugger.php">debugger</a>';
        $html.= '</div></div>';
        return $html;
    }
    
    static function formatRequest($request)
    {
        $html = date('H:i:s',$request['start']).' - ';
        $html.= strtolower($request['router']['method']).' ';
        $html.= htmlentities($request['router']['url']).' - '; 
        $html.= $request['type'].' - '; 
        $html.= round($request['duration']*1000).' ms - '; 
        $html.= round($request['memory']/1000000).' MB';
        return $html;
    }
    
    public function __toString()
    {
      return 'Debugger';
    }
    
}
