<?php
// Load the router
require '../lib/router.php';
// Load the database abstraction layer
require '../lib/database.php';
// Load the helper functions
require '../lib/functions.php';

// Start the session
session_start('mindaphp');

// Debugger on or off
$debugger = true;

// Load the debugger
if ($debugger) {
  require '../lib/debugger.php';
  $debugger = new Debugger(10);
}

// Load the front controller
$router = new Router($debugger, '../actions', '../views', '../templates');

// Connect to the database
$db = new Database($debugger, 'localhost', 'mindaphp', 'mindaphp', 'mindaphp');

// Set up redirects
$router->redirect('/','/hello/world');
$router->redirect('/docs','/docs/overview');

// Set the parameters
$parameters = $router->getParameters();

// Handle the 'none' template case
if (!$router->getTemplate()) {
    @include $router->getAction();
    require $router->getView();
    exit();
}

// Load the action into body
ob_start();
if ($router->getAction()) require $router->getAction();
require $router->getView();
// Show developer toolbar
if ($debugger) $debugger->toolbar();
$body = ob_get_contents();
ob_end_clean();

// Load body into template
require $router->getTemplate();