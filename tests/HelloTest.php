<?php

namespace MintyPHP\Tests;

use MintyPHP\Hello;

class HelloTest extends \PHPUnit\Framework\TestCase
{
    public function testGetGreeting()
    {
        $this->assertEquals('Hello MintyPHP!', Hello::getGreeting());
    }
}
