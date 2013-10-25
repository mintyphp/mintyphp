<?php
function redirect($url,$permanent=false)
{
  global $debugger;
  if ($debugger) {
    $debugger->set('redirect',$url);
    $debugger->end('redirect');
  }
  die(header("Location: $url",true,$permanent?301:302));  
}

function parameterless()
{
  global $router;
  if ($router->getRequest()!=$router->getUrl()) {
    redirect($router->getUrl());
  }
}

function e($string)
{
  echo htmlspecialchars($string);
}

function csrf_token()
{
  echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'"/>';
}

function debug($variable,$depth=10,$strlen=80,$i=0,&$objects = array())
{
  global $debugger;
  if (!$debugger) return;
  $search = array("\0", "\a", "\b", "\f", "\n", "\r", "\t", "\v");
  $replace = array('\0', '\a', '\b', '\f', '\n', '\r', '\t', '\v');
  
  $string = '';
  
  switch(gettype($variable)) {
    case 'boolean':      $string.= $variable?'true':'false'; break;
    case 'integer':      $string.= $variable;                break;
    case 'double':       $string.= $variable;                break;
    case 'string':       
      $len = strlen($variable);
      $variable = str_replace($search,$replace,substr($variable,0,$strlen),$count);
      $variable = substr($variable,0,$strlen);
      if ($count || $len>=$strlen) $string.= 'string('.$len.'): "'.$variable.'"';
      else $string.= '"'.$variable.'"'; 
      break;
    case 'resource':     $string.= '[resource]';             break;
    case 'NULL':         $string.= "[null]";                 break;
    case 'unknown type': $string.= '[unknown]';              break;
    case 'array':          
      if ($i==$depth) $string.= 'array(..)';
      else if(empty($variable)) $string.= 'array()';
      else {
        $keys = array_keys($variable);
        $spaces = str_repeat(' ',$i*2);
        $string.= "array\n".$spaces.'(';
        foreach($keys as $key) {
          $string.= "\n".$spaces."  [$key] => ";
          $string.= debug($variable[$key],$depth,$strlen,$i+1,&$objects);
        }
        $string.="\n".$spaces.')';
      }
      break;
    case 'object':
      $id = array_search($variable,$objects,true);
      if ($id!==false)
        $string.=get_class($variable).'#'.($id+1).'(...)';
      else if($i==$depth)
        $string.=get_class($variable).'(...)';
      else {
        $id = array_push($objects,&$variable);
        $className = get_class($variable);
        $members = (array)$variable;
        $keys = array_keys($members);
        $spaces = str_repeat(' ',$i*2);
        $string.= "$className#$id\n".$spaces.'(';
        foreach($keys as $key)
        {
          $keyDisplay = strtr(trim($key),array("\0"=>':'));
          $string.= "\n".$spaces."  [$keyDisplay] => ";
          $string.= debug($members[$key],$depth,$strlen,$i+1,&$objects);
        }
        $string.= "\n".$spaces.')';
      }
      break;
  }
  
  if ($i==0) $debugger->add('log',$string);
  else return $string;
}