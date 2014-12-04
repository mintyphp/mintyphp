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
      $query = sprintf('select * from `%s` where `%s` = ? and sha2(concat(`%s`,?),512) = `%s` limit 1',
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
      $salt = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));
      $query = sprintf('insert into `%s` (`%s`,`%s`,`%s`,`%s`) values (?,sha2(concat(?,?),512),?,NOW())',
          static::$usersTable,
          static::$usernameField,
          static::$passwordField,
          static::$saltField,
          static::$createdField);
      return DB::insert($query,$username,$salt,$password,$salt)!==false;
    }
    
}
