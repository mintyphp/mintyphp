<?php
function redirect($url)
{
  global $debugger;
  if ($debugger) $debugger->add('log','redirect: '.$url);
  die(header("Location: $url"));  
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