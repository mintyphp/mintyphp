<?php
// Use default autoload implementation
require '../vendor/mindaphp/Loader.php';
// Load the libraries
require '../config/loader.php';
// Load the config parameters
require '../config/config.php';
// Load the routes
require '../config/router.php';
// Register shortcut functions
function e() {	echo call_user_func_array('htmlspecialchars',func_get_args()); }
function d() { return call_user_func_array('Debugger::debug',func_get_args()); }

// Start the session
Session::start();

// Load the action into body
ob_start();
if (Router::getAction()) {
	extract(Router::getParameters());
	require Router::getAction();
}
if (ob_get_contents()) {
  ob_end_flush();
  trigger_error('MindaPHP action "'.Router::getAction().'" should not send output.', E_USER_WARNING);
}
else {
	ob_end_clean();
}

// End the session
Session::end();

if (Router::getTemplate()) {
  ob_start();
  require Router::getView();
  // Show developer toolbar
  if (Debugger::$enabled) Debugger::toolbar();
  Router::setContent(ob_get_contents());
  ob_end_clean();
  // Load body into template
  require Router::getTemplate();
} else { // Handle the 'none' template case
  require Router::getView();
}
