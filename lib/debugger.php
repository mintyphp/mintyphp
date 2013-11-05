<?php
class Debugger
{
    private $requests;
    private $request;
    private $shutdown;
    
    public function __construct($history)
    {
        if (!isset($_SESSION['debugger'])) $_SESSION['debugger'] = array();
        $this->requests = &$_SESSION['debugger'];
        $this->request = array('log'=>array(),'queries'=>array(),'session'=>array());
        $this->logSession('before');
        array_unshift($this->requests,&$this->request);
        while (count($this->requests)>$history) array_pop($this->requests);
        $this->set('start',microtime(true));
        $this->set('user',get_current_user());
        register_shutdown_function(array($this,'end'),'abort');
    }
    
    private function logSession($title)
    {
        $session = array();
        foreach ($_SESSION as $k=>$v) {
            if ($k=='debugger') $v=true;
            $session[$k] = $v;
        }
        $data = debug($session);
        $data = substr($data, strpos($data,"\n"));
        $this->request['session'][$title] = trim($data);
        if (is_object($this)) array_pop($this->request['log']);
    }

    public function set($key,$value)
    {
        $this->request[$key] = $value;
    }

    public function add($key,$value)
    {
        if (!isset($this->request[$key])) {
            $this->request[$key] = array();
        }
        $this->request[$key][] = $value;
    }
    
    public function get($key)
    {
        return isset($this->request[$key])?$this->request[$key]:false;
    }
    
    public function end($type)
    {
        if ($this->get('type')) return;
        $this->set('type',$type);
        $this->set('duration',microtime(true)-$this->get('start'));
        $this->set('memory',memory_get_peak_usage(true));
        $this->set('files',get_included_files());
        $this->logSession('after');
    }
    
    public function toolbar()
    {
        $this->end('ok');
        $html = '<div id="debugger-bar" style="position: fixed; width:100%; left: 0; bottom: 0; border-top: 1px solid silver; background: white;">';
        $html.= '<div style="margin:6px;">';
        $javascript = "document.getElementById('debugger-bar').style.display='none'; return false;";
        $html.= '<a href="#" onclick="'.$javascript.'" style="float:right;">close</a>';
        $request = $this->request;
        $parts = array();
        $parts[] = date('H:i:s',$request['start']);
        $parts[] = strtolower($request['router']['method']).' '.htmlentities($request['router']['url']);
        if (!isset($request['type'])) {
          $parts[] ='???';
        } else {
          $parts[] = $request['type'];
          $parts[] = round($request['duration']*1000).' ms ';
          $parts[] = round($request['memory']/1000000).' MB';
        }
        $html.= implode(' - ',$parts).' - <a href="/debugger.php">debugger</a>';
        $html.= '</div></div>';
        echo $html;
    }
    
}
