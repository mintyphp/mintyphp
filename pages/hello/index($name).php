<?php

/**
 * @var string|null $name
 */

use MintyPHP\Router;

if (!$name) Router::redirect('hello/form');