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
      	if (password_verify($password, $user['users'][static::$passwordField])) {
      		session_regenerate_id(true);
      		$_SESSION['user'] = $user['users'];
      	} else {
      		$user = array();
      	}
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
      $query = sprintf('insert into `%s` (`%s`,`%s`,`%s`) values (?,?,NOW())',
        static::$usersTable,
        static::$usernameField,
        static::$passwordField,
        static::$createdField);
      $password = password_hash($password, PASSWORD_DEFAULT);
      return DB::insert($query,$username,$password)!==false;
    }
    
}
