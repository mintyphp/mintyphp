<?php
namespace MindaPHP;

class Hello
{
    public static $name = 'MindaPHP';

    public static function getGreeting()
    {
        return 'Hello ' . static::$name . '!';
    }
}
