<?php
namespace MindaPHP\Tests;

use MindaPHP\DB;

class DBTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass()
    {
        DB::$username = 'mindaphp_test';
        DB::$password = 'mindaphp_test';
        DB::$database = 'mindaphp_test';
    }

    public function testDropPostsBefore()
    {
        $result = DB::query('DROP TABLE IF EXISTS `posts`;');
        $this->assertNotFalse($result, 'drop posts failed');
    }

    public function testDropUsersBefore()
    {
        $result = DB::query('DROP TABLE IF EXISTS `users`;');
        $this->assertNotFalse($result, 'drop users failed');
    }

    public function testCreateUsers()
    {
        $result = DB::query('CREATE TABLE `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(255) COLLATE utf8_bin NOT NULL,
            `password` varchar(255) COLLATE utf8_bin NOT NULL,
            `salt` varchar(255) COLLATE utf8_bin NOT NULL,
            `created` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;');
        $this->assertNotFalse($result, 'create users failed');
    }

    public function testCreatePosts()
    {
        $result = DB::query('CREATE TABLE `posts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `slug` varchar(255) COLLATE utf8_bin NOT NULL,
            `tags` varchar(255) COLLATE utf8_bin NOT NULL,
            `title` text COLLATE utf8_bin NOT NULL,
            `content` mediumtext COLLATE utf8_bin NOT NULL,
            `created` datetime NOT NULL,
            `published` datetime DEFAULT NULL,
            `user_id` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;');
        $this->assertNotFalse($result, 'create posts failed');
    }

    public function testInsertUsers()
    {
        $result = DB::insert("INSERT INTO `users` (`id`, `username`, `password`, `salt`, `created`) VALUES (NULL, 'test1', 'c32ac6310706acdadea74c901c3f08fe06c44c61', 'd7e8541887cb9b3461d7364e4e7c8b7d', '2014-05-28 22:58:22');");
        $this->assertNotFalse($result, 'insert user failed 1');
        $this->assertEquals(1, $result);
        $result = DB::insert("INSERT INTO `users` (`id`, `username`, `password`, `salt`, `created`) VALUES (NULL, 'test2', 'c32ac6310706acdadea74c901c3f08fe06c44c61', 'd7e8541887cb9b3461d7364e4e7c8b7d', '2014-05-28 22:58:22');");
        $this->assertNotFalse($result, 'insert user failed 2');
        $this->assertEquals(2, $result);
    }

    public function testInsertPosts()
    {
        $result = DB::insert("INSERT INTO `posts` (`id`, `slug`, `tags`, `title`, `content`, `created`, `published`, `user_id`) VALUES (NULL, '2014-08-test1', '', 'test', 'test', '0000-00-00 00:00:00', NULL, 1);");
        $this->assertNotFalse($result, 'insert post failed 1');
        $this->assertEquals(1, $result);
        $result = DB::insert("INSERT INTO `posts` (`id`, `slug`, `tags`, `title`, `content`, `created`, `published`, `user_id`) VALUES (NULL, '2014-08-test2', '', 'test', 'test', '0000-00-00 00:00:00', NULL, 1);");
        $this->assertNotFalse($result, 'insert post failed 2');
        $this->assertEquals(2, $result);
    }

    public function testSelectPosts()
    {
        $result = DB::select("SELECT * FROM `posts`;");
        $this->assertEquals(2, count($result));
        $this->assertEquals('posts', array_keys($result[0])[0]);
        $this->assertEquals('id', array_keys($result[0]['posts'])[0]);
        $result = DB::select("SELECT * FROM `posts`, `users` WHERE posts.user_id = users.id and users.username = 'test1';");
        $this->assertEquals(2, count($result));
        $this->assertEquals(array('posts', 'users'), array_keys($result[0]));
        $this->assertEquals('id', array_keys($result[0]['posts'])[0]);
        $this->assertEquals('test1', $result[0]['users']['username']);
        $this->setExpectedException('MindaPHP\DBError');
        $result = DB::select("some bogus query;");
    }

    public function testSelectOne()
    {
        $result = DB::selectOne("SELECT * FROM `posts` limit 1;");
        $this->assertEquals('posts', array_keys($result)[0]);
        $this->assertEquals('id', array_keys($result['posts'])[0]);
        $result = DB::selectOne("SELECT * FROM `posts` WHERE slug like 'm%' limit 1;");
        $this->assertEquals(array(), $result);
        $this->setExpectedException('MindaPHP\DBError');
        $result = DB::selectOne("some bogus query;");
    }

    public function testSelectValues()
    {
        $result = DB::selectValues("SELECT username FROM `users`;");
        $this->assertEquals(array('test1', 'test2'), $result);
        $result = DB::selectValues("SELECT username FROM `users` WHERE username like 'm%' limit 1;");
        $this->assertEquals(array(), $result);
        $this->setExpectedException('MindaPHP\DBError');
        $result = DB::selectValues("some bogus query;");
    }

    public function testSelectValue()
    {
        $result = DB::selectValue("SELECT username FROM `users` limit 1;");
        $this->assertEquals('test1', $result);
        $result = DB::selectValue("SELECT username FROM `users` WHERE username like 'm%' limit 1;");
        $this->assertEquals(false, $result);
        $this->setExpectedException('MindaPHP\DBError');
        $result = DB::selectValue("some bogus query;");
    }

    public function testQuery()
    {
        $result = DB::query("SELECT * FROM `posts` limit 1;");
        $this->assertEquals(true, $result);
        $this->setExpectedException('MindaPHP\DBError');
        $result = DB::query("some bogus query;");
    }

    public function testDeletePosts()
    {
        $result = DB::delete('DELETE FROM `posts`;');
        $this->assertNotFalse($result, 'delete posts failed');
        $this->assertEquals(2, $result);
    }

    public function testDeleteUsers()
    {
        $result = DB::delete('DELETE FROM `users`;');
        $this->assertNotFalse($result, 'delete users failed');
        $this->assertEquals(2, $result);
    }

    public function testDropPosts()
    {
        $result = DB::query('DROP TABLE `posts`;');
        $this->assertNotFalse($result, 'drop posts failed');
    }

    public function testDropUsers()
    {
        $result = DB::query('DROP TABLE `users`;');
        $this->assertNotFalse($result, 'drop users failed');
    }

}
