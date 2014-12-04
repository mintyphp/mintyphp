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
    if (static::$initialized) return;
    static::$initialized = true;
    static::start();
    static::setCsrfToken();
  }
  
  protected static function setCsrfToken()
  {
  	if (!isset($_SESSION[static::$csrfSessionKey])) {
  		$_SESSION[static::$csrfSessionKey] = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));
  	}
  }
  
  public static function start()
  {
  	if (!static::$initialized) static::initialize();
  	if (static::$started) return;
  	session_name(static::$sessionName);
  	session_start();
  	static::$started = true;
  	if (Debugger::$enabled) {
  		if (!isset($_SESSION[Debugger::$sessionKey])) {
  			$_SESSION[Debugger::$sessionKey] = array();
  		}
  		Debugger::logSession('before');
  	}
  }
  
  public static function end()
  {
  	if (!static::$initialized) static::initialize();
  	if (static::$ended) return;
  	if (!Debugger::$enabled)  session_write_close();
  	static::$ended = true;
  	if (Debugger::$enabled) Debugger::logSession('after');
  	if (Debugger::$enabled) {
  		$session = $_SESSION;
  		unset($_SESSION);
  		$_SESSION = $session;
  	}
  }
  
  public static function checkCsrfToken()
  {
  	if (!static::$initialized) static::initialize();
  	$success = false;
  	if (isset($_POST[static::$csrfSessionKey])) {
  		$success = $_POST[static::$csrfSessionKey] == $_SESSION[static::$csrfSessionKey];
  		//unset($_POST['csrf_token']);
  	}
  	return $success;
  }
  
  public static function getCsrfInput()
  {
  	if (!static::$initialized) static::initialize();
  	echo '<input type="hidden" name="'.static::$csrfSessionKey.'" value="'.$_SESSION[static::$csrfSessionKey].'"/>';
  }
  
}
