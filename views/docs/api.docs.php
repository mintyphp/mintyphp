<h1>API</h1>
<p>This is a reference of all global variables and functions.</p>
<pre>
Type    Function                            Location        Purpose          File
================================================================================================
        e($variable)                        Template/View   Output           web/index.php
        d($variable)                        Everywhere      Debugging        web/index.php
bool    Loader::register($path,$namespace)  Loader Config   Loading classes  lib/core/Loader.php
array   DB::q($sql,...)                     Action          Database query   lib/core/DB.php
array   DB::q1($sql,...)                    Action          Database query   lib/core/DB.php
integer DB::id()                            Action          Database inserts lib/core/DB.php
        Router::addForward($url,$loc)       Router Config   Redirection      lib/core/Router.php
        Router::redirect($url)              View            Redirection      lib/core/Router.php
        Router::parameterless()             Action          Redirection      lib/core/Router.php
array   Router::getParameters()             Action          User input       lib/core/Router.php
string  Router::getContent()                Template        Create template  lib/core/Router.php
bool    Auth::login($username,$password)    Action          Logging in       lib/core/Auth.php
bool    Auth::logout()                      Action          Logging out      lib/core/Auth.php
bool    Auth::register($username,$password) Action          Adding users     lib/core/Auth.php
string  Session::getCsrfInput()             Template/View   Form security    lib/core/Session.php

</pre>
