<?php
namespace MindaPHP;

class NoPassAuth
{
    static $usersTable    = 'users';
    static $usernameField = 'username';
    static $passwordField = 'password';
    static $createdField  = 'created';
    static $tokenValidity = 300;

    static function token($username)
    {
      $query = sprintf('select * from `%s` where `%s` = ? limit 1',
        static::$usersTable,
        static::$usernameField);
      $user = DB::selectOne($query,$username);
      if ($user) {
        $table = static::$usersTable;
      	$username = $user[$table][static::$usernameField];
        $password = $user[$table][static::$passwordField];
        Token::$secret = $password;
        Token::$ttl = static::$tokenValidity;
        $token = Token::getToken(array('user'=>$username,'ip'=>$_SERVER['REMOTE_ADDR']));
      } else {
        $token = '';
      }
      return $token;
    }

    static function login($token)
    {
      $parts = explode('.',$token);
      $claims = isset($parts[1])?json_decode(base64_decode($parts[1]),true):false;
      $username = isset($claims['user'])?$claims['user']:false;
      $query = sprintf('select * from `%s` where `%s` = ? limit 1',
        static::$usersTable,
        static::$usernameField);
      $user = DB::selectOne($query,$username);
      if ($user) {
      	$table = static::$usersTable;
      	$username = $user[$table][static::$usernameField];
        $password = $user[$table][static::$passwordField];
        Token::$secret = $password;
        Token::$ttl = static::$tokenValidity;
        $claims = Token::getClaims(array('Authorization'=>'Bearer '.$token));
        if ($claims && $claims['user']==$username && $claims['ip']==$_SERVER['REMOTE_ADDR']) {
      		session_regenerate_id(true);
      		$_SESSION['user'] = $user[$table];
      	} else {
      		$user = array();
      	}
      }
      return $user;
    }
    
    static function logout()
    {
      foreach ($_SESSION as $key=>$value) {
        if ($key!='debugger') unset($_SESSION[$key]);
      }
      session_regenerate_id(true);
      return true;
    }
    
    static function register($username)
    {
      $query = sprintf('insert into `%s` (`%s`,`%s`,`%s`) values (?,?,NOW())',
        static::$usersTable,
        static::$usernameField,
        static::$passwordField,
        static::$createdField);
      $password = bin2hex(random_bytes(16));
      $password = password_hash($password, PASSWORD_DEFAULT);
      return DB::insert($query,$username,$password);
    }
    
    static function update($username)
    {
    	$query = sprintf('update `%s` set `%s`=? where `%s`=?',
    			static::$usersTable,
    			static::$passwordField,
    			static::$usernameField);
    	$password = bin2hex(random_bytes(16));
      $password = password_hash($password, PASSWORD_DEFAULT);
    	return DB::update($query,$password,$username);
    }
    
    static function exists($username)
    {
    	$query = sprintf('select `id` from `%s` where `%s`=?',
    			static::$usersTable,
    			static::$usernameField);
    	return DB::selectValue($query,$username);
    }
    
}

// for compatibility in PHP 5.3
if (!function_exists('random_bytes')) {
    include __DIR__."/random_compat.inc";
}