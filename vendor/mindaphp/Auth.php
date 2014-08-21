<?php
namespace MindaPHP;

class Auth
{
	static $usersTable    = 'users';
	static $usernameField = 'username';
	static $saltField     = 'salt';
	static $passwordField = 'password';
	static $createdField  = 'created';
		
	static function login($username,$password)
	{
	  $query = sprintf('select * from `%s` where `%s` = ? and sha1(concat(`%s`,?)) = `%s` limit 1',
		  static::$usersTable,
		  static::$usernameField,
		  static::$saltField,
		  static::$passwordField);
	  $user = DB::selectOne($query,$username,$password);
	  if ($user) {
	  	session_regenerate_id(true);
	  	$_SESSION['user'] = $user['users'];
	  }
	  return $user;
	}
	
	static function logout()
	{
	  if (!isset($_SESSION['user'])) return false;
	  unset($_SESSION['user']);
	  unset($_SESSION['csrf_token']);
	  session_regenerate_id(true);
	  return true;
	}
	
	static function register($username,$password)
	{
	  $salt = md5($username.time());
	  $query = sprintf('insert into `%s` (`%s`,`%s`,`%s`,`%s`) values (?,sha1(concat(?,?)),?,NOW())',
		  static::$usersTable,
		  static::$usernameField,
	      static::$passwordField,
		  static::$saltField,
		  static::$createdField);
	  return DB::insert($query,$username,$salt,$password,$salt)!==false;
	}
	
}