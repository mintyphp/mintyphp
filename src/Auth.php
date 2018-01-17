<?php
namespace MindaPHP;

class Auth
{
    static $usersTable    = 'users';
    static $usernameField = 'username';
    static $passwordField = 'password';
    static $createdField  = 'created';
        
    static function login($username,$password)
    {
      $query = sprintf('select * from `%s` where `%s` = ? limit 1',
        static::$usersTable,
        static::$usernameField);
      $user = DB::selectOne($query,$username);
      if ($user) {
        $table = static::$usersTable;
      	if (password_verify($password, $user[$table][static::$passwordField])) {
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
    
    static function register($username,$password)
    {
      $query = sprintf('insert into `%s` (`%s`,`%s`,`%s`) values (?,?,NOW())',
        static::$usersTable,
        static::$usernameField,
        static::$passwordField,
        static::$createdField);
      $password = password_hash($password, PASSWORD_DEFAULT);
      return DB::insert($query,$username,$password);
    }
    
    static function update($username,$password)
    {
    	$query = sprintf('update `%s` set `%s`=? where `%s`=?',
    			static::$usersTable,
    			static::$passwordField,
    			static::$usernameField);
    	$password = password_hash($password, PASSWORD_DEFAULT);
    	return DB::update($query,$password,$username);
    }
    
    static function exists($username)
    {
    	$query = sprintf('select 1 from `%s` where `%s`=?',
    			static::$usersTable,
    			static::$usernameField);
    	return DB::selectValue($query,$username);
    }
    
}

// for compatibility in PHP 5.3
if (!function_exists('password_verify')) {
    include __DIR__."/password_compat.inc";
}
