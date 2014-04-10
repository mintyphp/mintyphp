<h1>API</h1>
<p>This is a reference of all global variables and functions.</p>
<pre>
Type    Function                            Location        Purpose          File
================================================================================================
        e($variable)                        Template/View   Output           web/index.php
        d($variable)                        Everywhere      Debugging        web/index.php
bool    Loader::register($path,$namespace)  Loader Config   Loading classes  vendor/core/Loader.php
array   DB::q($sql,...)                     Action          Database query   vendor/core/DB.php
array   DB::q1($sql,...)                    Action          Database query   vendor/core/DB.php
integer DB::id()                            Action          Database inserts vendor/core/DB.php
        Router::addForward($url,$loc)       Router Config   Redirection      vendor/core/Router.php
        Router::redirect($url)              View            Redirection      vendor/core/Router.php
        Router::parameterless()             Action          Redirection      vendor/core/Router.php
array   Router::getParameters()             Action          User input       vendor/core/Router.php
string  Router::getContent()                Template        Create template  vendor/core/Router.php
bool    Auth::login($username,$password)    Action          Logging in       vendor/core/Auth.php
bool    Auth::logout()                      Action          Logging out      vendor/core/Auth.php
bool    Auth::register($username,$password) Action          Adding users     vendor/core/Auth.php
string  Session::getCsrfInput()             Template/View   Form security    vendor/core/Session.php

</pre>
