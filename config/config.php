<?php
namespace MindaPHP\Config;

class Router
{
  public static $viewRoot     = '../views';
  public static $actionRoot   = '../actions';
  public static $templateRoot = '../templates';
}

class Session
{
  public static $sessionName = 'mindaphp';
}

class DB
{
  public static $host     = 'localhost';
  public static $username = 'mindaphp';
  public static $password = 'mindaphp';
  public static $database = 'mindaphp';
  public static $port     = 3306;
}

class Auth
{
  public static $usersTable    = 'users';    // table that holds the user data
  public static $usernameField = 'username'; // type varchar, has unique index
  public static $saltField     = 'salt';     // type varchar(32), holds md5 in hex
  public static $passwordField = 'password'; // type varchar(40), holds sha1 in hex
  public static $createdField  = 'created';  // type datetime, optional
}

class Debugger
{
  public static $enabled = true;
}
