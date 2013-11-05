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

function debug($variable,$strlen=100,$width=25,$depth=10,$i=0,&$objects = array())
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
    case 'resource':     $string.= '[resource]';             break;
    case 'NULL':         $string.= "null";                   break;
    case 'unknown type': $string.= '???';                    break;
    case 'string':       
      $len = strlen($variable);
      $variable = str_replace($search,$replace,substr($variable,0,$strlen),$count);
      $variable = substr($variable,0,$strlen);
      if ($len<$strlen) $string.= '"'.$variable.'"';
      else $string.= 'string('.$len.'): "'.$variable.'"...';
      break;
    case 'array':          
      $len = count($variable);
      if ($i==$depth) $string.= 'array('.$len.') {...}';
      elseif(!$len) $string.= 'array(0) {}';
      else {
        $keys = array_keys($variable);
        $spaces = str_repeat(' ',$i*2);
        $string.= "array($len)\n".$spaces.'{';
        $count=0;
        foreach($keys as $key) {
          if ($count==$width) {
            $string.= "\n".$spaces."  ...";
            break;
          }
          $string.= "\n".$spaces."  [$key] => ";
          $string.= debug($variable[$key],$strlen,$width,$depth,$i+1,&$objects);
          $count++;
        }
        $string.="\n".$spaces.'}';
      }
      break;
    case 'object':
      $id = array_search($variable,$objects,true);
      if ($id!==false)
        $string.=get_class($variable).'#'.($id+1).' {...}';
      else if($i==$depth)
        $string.=get_class($variable).' {...}';
      else {
        $id = array_push($objects,&$variable);
        $array = (array)$variable;
        $spaces = str_repeat(' ',$i*2);
        $string.= get_class($variable)."#$id\n".$spaces.'{';
        $properties = array_keys($array);
        foreach($properties as $property) {
          $name = str_replace("\0",':',trim($property));
          $string.= "\n".$spaces."  [$name] => ";
          $string.= debug($array[$property],$strlen,$width,$depth,$i+1,&$objects);
        }
        $string.= "\n".$spaces.'}';
      }
      break;
  }
  
  if ($i>0) return $string;
  
  $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
  do $caller = array_shift($backtrace); while ($caller && !isset($caller['file']));
  if ($caller) $string = $caller['file'].':'.$caller['line']."\n".$string;
  
  if (is_object($debugger)) $debugger->add('log',$string);
  return $string;
}