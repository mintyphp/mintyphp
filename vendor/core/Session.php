<?php
class SessionError extends Exception {};

class Session
{
  public static $sessionName        = 'mindaphp';
  public static $csrfSessionKey     = 'csrf_token';
  
  protected static $initialized = false;
  protected static $started = false;
  
  protected static function initialize()
  {
    if (self::$initialized) return;
    self::$initialized = true;
    self::start();
    self::setCsrfToken();
  }
  
  protected static function setCsrfToken()
  {
  	if (!isset($_SESSION[self::$csrfSessionKey])) {
  		$_SESSION[self::$csrfSessionKey] = rand(0, PHP_INT_MAX);
  	}
  }
  
  public static function start()
  {
  	if (!self::$initialized) self::initialize();
  	if (self::$started) return;
  	session_name(self::$sessionName);
  	session_start();
  	self::$started = true;
  }
  
  public static function end()
  {
  	if (!self::$initialized) self::initialize();
  	session_write_close();
  }
  
  public static function checkCsrfToken()
  {
  	if (!self::$initialized) self::initialize();
  	$success = false;
  	if (isset($_POST[self::$csrfSessionKey])) {
  		$success = $_POST[self::$csrfSessionKey] == $_SESSION[self::$csrfSessionKey];
  		//unset($_POST['csrf_token']);
  	}
  	return $success;
  }
  
  public static function getCsrfInput()
  {
  	if (!self::$initialized) self::initialize();
  	echo '<input type="hidden" name="'.self::$csrfSessionKey.'" value="'.$_SESSION[self::$csrfSessionKey].'"/>';
  }
  
}