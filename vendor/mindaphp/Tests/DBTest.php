<?php
namespace MindaPHP\Tests;

use MindaPHP\DB;

class DBTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{	
		DB::$username = 'mindaphp_test';
		DB::$password = 'mindaphp_test';
		DB::$database = 'mindaphp_test';
	}
	
	public function testDropPosts()
	{
		$result = DB::query('DROP TABLE IF EXISTS `posts`;');
		$this->assertNotFalse($result, 'drop posts failed: '.DB::$error);
	}

	public function testDropUsers()
	{
		$result = DB::query('DROP TABLE IF EXISTS `users`;');
		$this->assertNotFalse($result, 'drop users failed: '.DB::$error);
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
		$this->assertNotFalse($result, 'create users failed: '.DB::$error);
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
		$this->assertNotFalse($result, 'create posts failed: '.DB::$error);
	}

	public function testInsertUsers()
	{
		$result = DB::insert("INSERT INTO `users` (`id`, `username`, `password`, `salt`, `created`) VALUES (NULL, 'test1', 'c32ac6310706acdadea74c901c3f08fe06c44c61', 'd7e8541887cb9b3461d7364e4e7c8b7d', '2014-05-28 22:58:22');");
		$this->assertNotFalse($result, 'insert user failed 1: '.DB::$error);
		$result = DB::insert("INSERT INTO `users` (`id`, `username`, `password`, `salt`, `created`) VALUES (NULL, 'test2', 'c32ac6310706acdadea74c901c3f08fe06c44c61', 'd7e8541887cb9b3461d7364e4e7c8b7d', '2014-05-28 22:58:22');");
		$this->assertNotFalse($result, 'insert user failed 2: '.DB::$error);
	}
	
	public function testInsertPosts()
	{
		$result = DB::insert("INSERT INTO `posts` (`id`, `slug`, `tags`, `title`, `content`, `created`, `published`, `user_id`) VALUES (NULL, '2014-08-test1', '', 'test', 'test', '0000-00-00 00:00:00', NULL, 1);");
		$this->assertNotFalse($result, 'insert post failed 1: '.DB::$error);
		$result = DB::insert("INSERT INTO `posts` (`id`, `slug`, `tags`, `title`, `content`, `created`, `published`, `user_id`) VALUES (NULL, '2014-08-test2', '', 'test', 'test', '0000-00-00 00:00:00', NULL, 1);");
		$this->assertNotFalse($result, 'insert post failed 2: '.DB::$error);
	}	
	
	public function testDeletePosts()
	{
		$result = DB::delete('DELETE FROM `posts`;');
		$this->assertNotFalse($result, 'delete posts failed: '.DB::$error);
	}
	
	public function testDeleteUsers()
	{
		$result = DB::delete('DELETE FROM `users`;');
		$this->assertNotFalse($result, 'delete users failed: '.DB::$error);
	}
		
	
	protected function tearDown()
	{
		
	}
	
}