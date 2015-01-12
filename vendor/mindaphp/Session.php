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
  	if (isset($_SESSION[static::$csrfSessionKey])) return;
  	
  	$strlen = function($binary_string) {
  		if (function_exists('mb_strlen')) {
  			return mb_strlen($binary_string, '8bit');
  		}
  		return strlen($binary_string);
  	};
  	
  	$raw_csrf_len = 16; 
  	$buffer = '';
  	$buffer_valid = false;
  	if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
  		$buffer = mcrypt_create_iv($raw_csrf_len, MCRYPT_DEV_URANDOM);
  		if ($buffer) {
  			$buffer_valid = true;
  		}
  	}
  	if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
  		$buffer = openssl_random_pseudo_bytes($raw_csrf_len);
  		if ($buffer) {
  			$buffer_valid = true;
  		}
  	}
  	if (!$buffer_valid && @is_readable('/dev/urandom')) {
  		$f = fopen('/dev/urandom', 'r');
  		$read = $strlen($buffer);
  		while ($read < $raw_csrf_len) {
  			$buffer .= fread($f, $raw_csrf_len - $read);
  			$read = $strlen($buffer);
  		}
  		fclose($f);
  		if ($read >= $raw_csrf_len) {
  			$buffer_valid = true;
  		}
  	}
  	if (!$buffer_valid || $strlen($buffer) < $raw_csrf_len) {
  		$bl = $strlen($buffer);
  		for ($i = 0; $i < $raw_csrf_len; $i++) {
  			if ($i < $bl) {
  				$buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
  			} else {
  				$buffer .= chr(mt_rand(0, 255));
  			}
  		}
  	}
  	
  	$_SESSION[static::$csrfSessionKey] = bin2hex($buffer);
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
  	static::setCsrfToken();
  	echo '<input type="hidden" name="'.static::$csrfSessionKey.'" value="'.$_SESSION[static::$csrfSessionKey].'"/>';
  }
  
}
