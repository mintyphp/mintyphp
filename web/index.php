<?php
// Load the router
require '../lib/router.php';
// Load the database abstraction layer 
require '../lib/database.php';
// Load the helper functions
require '../lib/functions.php';

// Debug on or off
$debug = true;

// Connect to the database
$db = new Database($debug, 'localhost', 'mindaphp', 'mindaphp', 'mindaphp');

// Start the session
session_start('mindaphp');

// Load the front controller
$router = new Router($debug, $_SERVER['REQUEST_URI'], '../actions', '../templates');

// Set up redirects
$router->redirect('/','/hello/world');
$router->redirect('/docs','/docs/overview');

// Set the parameters
$parameters = $router->getParameters();

// Handle the 'none' template case 
if ($router->getTemplate()=='none') die(require $router->getAction());

// Load the action into body
ob_start();
require $router->getAction();
$body = ob_get_contents();
ob_end_clean();
  
// Load body into template
require $router->getTemplate();