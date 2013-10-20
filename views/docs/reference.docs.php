<?php parameterless() ?>
<h1>Reference</h1>
<p>This is an reference of all global variables and functions:</p>
<pre>
bool      $debug

          $router-&gt;redirect($url,$location);
string    $router-&gt;getParameters();
string    $router-&gt;getTemplate()
string    $router-&gt;getAction();
string    $router-&gt;getView();
string    $router-&gt;getUrl();
        
string    $body
        
array     $parameters
        
array     $db-&gt;q($sql,&hellip;);
array     $db-&gt;q1($sql,&hellip;);
integer   $db-&gt;id();
object    $db-&gt;handle();
        
          function redirect($url)
          function parameterless()
          
bool      function authenticate($username,$password)
bool      function register($username,$password)
</pre>