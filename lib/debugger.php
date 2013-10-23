<?php
class Debugger
{
    private $requests;
    private $request;
    
    public function __construct(&$storage,$history)
    {
        $this->requests = &$storage;
        $this->request = array();
        $this->requests[] = array_unshift($this->requests,&$this->request);
        while (count($this->requests)>$history) array_pop($this->requests);
    }
    
    public function log($message)
    {
        $this->request['log'][] = $message;        
    }

    public function toolbar($globals)
    {
        $this->log('variables: '.implode(',',array_keys($globals)));
        
        echo '<div style="position: fixed; bottom:0;">';
        foreach ($this->requests as $i=>$request)
        { echo "$i:log<br/>";
          echo '<pre>'.implode("\n",$this->requests[$i]['log']).'</pre>';
        }
        echo '</div>';
    }
    
    public function __toString()
    {
      return 'Debugger';
    }
    
}
