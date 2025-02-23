<?php

use MintyPHP\Firewall;
use MintyPHP\Session;
use MintyPHP\Debugger;
use MintyPHP\Analyzer;
use MintyPHP\Router;
use MintyPHP\DB;
use MintyPHP\Buffer;
use MintyPHP\I18n;

// Change directory to project root
chdir(__DIR__ . '/..');
// Load the libraries
require 'vendor/autoload.php';
// Load the config parameters
require 'config/config.php';
// Load the routes
require 'config/router.php';
// Register shortcut functions
function e($string)
{
    echo htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
}
function d()
{
    return call_user_func_array('MintyPHP\\Debugger::debug', func_get_args());
}
function t()
{
    $arguments = func_get_args();
    $arguments[0] = I18n::translate($arguments[0]);
    return call_user_func_array('sprintf', $arguments);
}

// Start the firewall
Firewall::start();

// Start the session
Session::start();

// Analyze the PHP code
if (Debugger::$enabled) {
    Analyzer::execute();
}

// Load the action into body
ob_start();
if (Router::getTemplateAction()) {
    require Router::getTemplateAction();
}
if (ob_get_contents()) {
    ob_end_flush();
    trigger_error('MintyPHP template action"' . Router::getTemplateAction() . '" should not send output. Error raised ', E_USER_WARNING);
} else {
    ob_end_clean();
}

ob_start();
if (Router::getAction()) {
    extract(Router::getParameters());
    require Router::getAction();
}
if (ob_get_contents()) {
    ob_end_flush();
    trigger_error('MintyPHP action "' . Router::getAction() . '" should not send output. Error raised ', E_USER_WARNING);
} else {
    ob_end_clean();
}

// End the session
Session::end();

// Close the database connection
DB::close();

if (Router::getTemplateView()) {
    Buffer::start('html');
    if (Router::getView()) {
        require Router::getView();
    }

    // Show developer toolbar
    if (Debugger::$enabled) {
        Debugger::toolbar();
    }

    Buffer::end('html');
    // Load body into template
    require Router::getTemplateView();
} else { // Handle the 'none' template case
    if (Router::getView()) {
        require Router::getView();
    }
}
