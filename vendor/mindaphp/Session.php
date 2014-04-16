<?php
namespace MindaPHP;

class SessionError extends \Exception {};

class Session
{
  public static $sessionName        = 'mindaphp';
  public static $csrfSessionKey     = 'csrf_token';
  
  protected static $initialized = false;
  protected static $started = false;
  protected static $ended = false;

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
  	if (Debugger::$enabled) {
  		if (!isset($_SESSION[Debugger::$sessionKey])) {
  			$_SESSION[Debugger::$sessionKey] = array();
  		}
  		Debugger::logSession('before');
  	}
  }
  
  public static function end()
  {
  	if (!self::$initialized) self::initialize();
  	if (self::$ended) return;
  	if (!Debugger::$enabled)  session_write_close();
  	self::$ended = true;
  	if (Debugger::$enabled) Debugger::logSession('after');
  	if (Debugger::$enabled) {
  		$session = $_SESSION;
  		unset($_SESSION);
  		$_SESSION = $session;
  	}
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