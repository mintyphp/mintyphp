<?php

namespace MintyPHP;

class Hello
{
    public static string $name = 'MintyPHP';

    public static function getGreeting(): string
    {
        return 'Hello ' . static::$name . '!';
    }
}
