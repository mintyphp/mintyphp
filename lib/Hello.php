<?php
namespace MintyPHP;

class Hello
{
    public static $name = 'MintyPHP';

    public static function getGreeting()
    {
        return 'Hello ' . static::$name . '!';
    }
}
