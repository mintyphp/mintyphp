<?php
function authenticate($username,$password)
{
  global $db;
  $query = 'select * from `users` where `username` = ? and sha1(concat(`salt`,?)) = `password` limit 1';
  $user = $db->q1($query,$username,$password);
  if ($user) $_SESSION['user'] = $user;
  return $user;
}

function register($username,$password)
{
  global $db;
  $salt = md5($username.time());
  $query = 'insert into users (username,password,salt,created) values (?,sha1(concat(?,?)),?,NOW())';
  $success = $db->q($query,$username,$salt,$password,$salt);
  return $success;
}

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