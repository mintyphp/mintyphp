<?php
function redirect($url)
{
  die(header("Location: $url"));
}

function parameterless()
{
  global $router;
  if ($router->getRequest()!=$router->getUrl()) {
    redirect($router->getUrl());
  }
}