<?php 
$parameters = Router::getParameters();
if (!isset($parameters[0])) Router::redirect('/hello/form');
$name = $parameters[0];