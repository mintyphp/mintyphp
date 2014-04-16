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
		  self::$usersTable,
		  self::$usernameField,
		  self::$saltField,
		  self::$passwordField);
	  $user = DB::q1($query,$username,$password);
	  if ($user) $_SESSION['user'] = $user['users'];
	  return $user;
	}
	
	static function logout()
	{
	  if (!isset($_SESSION['user'])) return false;
	  unset($_SESSION['user']);
	  unset($_SESSION['csrf_token']);
	  return true;
	}
	
	static function register($username,$password)
	{
	  $salt = md5($username.time());
	  $query = sprintf('insert into `%s` (`%s`,`%s`,`%s`,`%s`) values (?,sha1(concat(?,?)),?,NOW())',
		  self::$usersTable,
		  self::$usernameField,
	    self::$passwordField,
		  self::$saltField,
		  self::$createdField);
	  $success = DB::q($query,$username,$salt,$password,$salt);
	  return $success;
	}
	
}