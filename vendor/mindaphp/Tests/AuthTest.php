<?php
namespace MindaPHP\Tests;

use MindaPHP\Auth;

class AuthTest extends \PHPUnit\Framework\TestCase
{
    public static $db;

    public static function setUpBeforeClass()
    {
        DBTest::setUpBeforeClass();
        self::$db = new DBTest();
        self::$db->testDropUsersBefore();
        self::$db->testCreateUsers();
    }

    public function testRegister()
    {
        $registered = Auth::register('test', 'test');
        $this->assertNotFalse($registered, 'user not registered');
    }

    public function testLogin()
    {
        try {
            var_dump(Auth::login('test', 'test'));
            $session_regenerated = false;
        } catch (\Exception $e) {
            $session_regenerated = $e->getMessage() == "session_regenerate_id(): Cannot regenerate session id - headers already sent";
        }
        $this->assertTrue($session_regenerated, 'session not regenerated');
    }

    public function testLogout()
    {
        $_SESSION['user'] = array('id' => 1, 'username' => 'test');
        $_SESSION['csrf_token'] = md5(time());
        try {
            Auth::logout();
            $session_regenerated = false;
        } catch (\Exception $e) {
            $session_regenerated = $e->getMessage() == "session_regenerate_id(): Cannot regenerate session id - headers already sent";
        }
        $this->assertTrue($session_regenerated, 'session not regenerated');
        $this->assertFalse(isset($_SESSION['user']), 'user not unset');
        $this->assertFalse(isset($_SESSION['csrf_token']), 'csrf token not unset');
    }

    public static function tearDownAfterClass()
    {
        self::$db->testDropUsers();
    }
}
