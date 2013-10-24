<?php
class Debugger
{
    private $requests;
    private $request;
    
    public function __construct(&$storage,$history)
    {
        if (!isset($storage['debugger'])) $storage['debugger'] = array();
        $this->requests = &$storage['debugger'];
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
      return $this->request[$key];
    }
    
    public function toolbar()
    {
        $this->set('duration',microtime(true)-$this->get('start'));
?>
<script src="/js/jquery-1.10.2.min.js"></script>
<div id="debugbar" style="position: fixed; width:100%; bottom: 0;">
<div style="background: silver; border: 1px solid gray;">
<a onclick="$('#router').toggle(); return false;" href="#">router</a>
<a onclick="$('#log').toggle(); return false;" href="#">log</a>
<a onclick="$('#queries').toggle(); return false;" href="#">db</a>
<?php e($this->request['router']['url']); ?> - 
<?php echo sprintf('%.2f',$this->request['duration']*1000); ?> ms - 
<?php echo sprintf('%.2f',memory_get_peak_usage()/1000000); ?> MB
<a style="float:right;" onclick="$('#debugbar').toggle(); return false;" href="#">X</a>
</div>
</div>
<div id="router" style="display:none; overflow: scroll; background: white; border: 1px solid silver; padding: 20px; position: fixed; top: 20; right: 20; bottom: 40; left: 20;">
<?php foreach ($this->requests as $i=>$request): ?>
<?php echo "req -$i"?><br/>
<?php foreach ($request['router'] as $k=>$v): ?>
<?php echo "$k=>$v"; ?><br/>
<?php endforeach; ?>
<?php endforeach; ?>
</div>
<div id="log" style="display:none; overflow: scroll; background: white; border: 1px solid silver; padding: 20px; position: fixed; top: 20; right: 20; bottom: 40; left: 20;">
<?php foreach ($this->requests as $i=>$request): ?>
<?php echo "-----------------"?><br/>
<?php foreach ($request['log'] as $k=>$v): ?>
<?php echo e($v); ?><br/>
<?php endforeach; ?>
<?php endforeach; ?>
</div>
<div id="queries" style="display:none; overflow: scroll; background: white; border: 1px solid silver; padding: 20px; position: fixed; top: 20; right: 20; bottom: 40; left: 20;">
<?php foreach ($this->requests as $i=>$request): ?>
<?php echo "req -$i"?><br/>
<?php foreach ($request['queries'] as $j=>$query): ?>
<?php echo "-"?><br/>
<?php foreach ($query as $k=>$v): ?>
<?php echo "$k=>$v"; ?><br/>
<?php endforeach; ?>
<?php endforeach; ?>
<?php endforeach; ?>
</div>
<?php
    }
    
    public function __toString()
    {
      return 'Debugger';
    }
    
}
