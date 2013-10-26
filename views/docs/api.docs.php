<?php parameterless() ?>
<h1>API</h1>
<p>This is a reference of all global variables and functions:</p>
<pre>
Type    Variable/Function             File
==========================================================
object  $debugger                     web/index.php
object  $router                       web/index.php
        $router-&gt;redirect($url,$loc)  lib/router.php
string  $router-&gt;getParameters()      lib/router.php
string  $router-&gt;getTemplate()        lib/router.php
string  $router-&gt;getAction()          lib/router.php
string  $router-&gt;getView()            lib/router.php
string  $router-&gt;getUrl()             lib/router.php
string  $body                         web/index.php
array   $parameters                   web/index.php
object  $db                           web/index.php
array   $db-&gt;q($sql,&hellip;)                lib/database.php
array   $db-&gt;q1($sql,&hellip;)               lib/database.php
integer $db-&gt;id()                     lib/database.php
object  $db-&gt;handle()                 lib/database.php
        redirect($url)                lib/functions.php
        parameterless()               lib/functions.php
        e($variable)                  lib/functions.php
        csrf_token()                  lib/functions.php
        debug($variable)              lib/functions.php
bool    login($username,$password)    lib/authenticate.php
bool    logout()                      lib/authenticate.php
bool    register($username,$password) lib/authenticate.php
</pre>
