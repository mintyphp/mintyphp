<?php
// Load the router
require '../lib/router.php';
// Load the database abstraction layer
require '../lib/database.php';
// Load the helper functions
require '../lib/functions.php';
// Load the helper functions
require '../lib/debugger.php';

// Start the session
session_start('mindaphp');

// Debugger on or off
$debugger = false;
$debugger = new Debugger(&$_SESSION,10);

// Load the front controller
$router = new Router($debugger, $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'], '../actions', '../views', '../templates');

// Connect to the database
$db = new Database($debugger, 'localhost', 'mindaphp', 'mindaphp', 'mindaphp');

// Set up redirects
$router->redirect('/','/hello/world');
$router->redirect('/docs','/docs/overview');

// Set the parameters
$parameters = $router->getParameters();

// Handle the 'none' template case
if ($router->getTemplate()=='none') {
    @include $router->getAction();
    require $router->getView();
    exit();
}

// Load the action into body
ob_start();
@include $router->getAction();
require $router->getView();
$body = ob_get_contents();
ob_end_clean();

// Load body into template
require $router->getTemplate();

// Show developer toolbar
if ($debugger) $debugger->toolbar(get_defined_vars());
