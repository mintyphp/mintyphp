<?php
// Set up redirects

use MintyPHP\Router;

Router::addRoute('', 'hello/world');
Router::addRoute('docs', 'docs/overview');
