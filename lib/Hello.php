<?php

namespace MintyPHP;

class Hello
{
    public static $name = 'MintyPHP';

    public static function getGreeting(): string
    {
        return 'Hello ' . static::$name . '!';
    }
}
