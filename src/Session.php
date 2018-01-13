<?php
namespace MindaPHP;

class SessionError extends \Exception {};

class Session
{
  public static $sessionName        = 'mindaphp';
  public static $csrfSessionKey     = 'csrf_token';
  public static $enabled            = true;
	public static $csrfLength         = 16;

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
    if (!static::$enabled) return;
    if (isset($_SESSION[static::$csrfSessionKey])) return;

    $buffer = random_bytes(static::$csrfLength);

  	$_SESSION[static::$csrfSessionKey] = bin2hex($buffer);
  }

  public static function start()
  {
  	if (!static::$initialized) static::initialize();
  	if (static::$started) return;
    if (static::$enabled || Debugger::$enabled) {
      if (!ini_get('session.cookie_httponly')) {
        ini_set('session.cookie_httponly',1);
      }
      if (!ini_get('session.cookie_secure') && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off') {
        ini_set('session.cookie_secure',1);
      }
      session_name(static::$sessionName);
    	session_start();
      if (!static::$enabled) {
        foreach ($_SESSION as $k=>$v) {
          if ($k != Debugger::$sessionKey) unset($_SESSION[$k]);
        }
      }
    }
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
  	static::$ended = true;
  	if (static::$enabled && !Debugger::$enabled) session_write_close();
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
    if (!static::$enabled) return true;
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
    if (!static::$enabled) return;
    static::setCsrfToken();
  	echo '<input type="hidden" name="'.static::$csrfSessionKey.'" value="'.$_SESSION[static::$csrfSessionKey].'"/>';
  }

}

// for compatibility in PHP 5.3
if (!function_exists('random_bytes')) {
    include __DIR__."/random_compat.inc";
}