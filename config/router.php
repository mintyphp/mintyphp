<?php

use MintyPHP\Router;

// Set up redirects

Router::$routes = [
    '' => 'hello/world',
    'docs' => 'docs/overview',
];
