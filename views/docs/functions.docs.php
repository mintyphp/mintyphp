<?php parameterless() ?>
<h1>Functions</h1>
<p>In "lib/functions.php" you find 2 functions that you can use anywhere in your application. This file is loaded
by the front-controller, so you do not have to "require" this file.</p>
<h2>Redirect</h2>
<pre>redirect($url)</pre>
<p>This "redirect" function redirects directly to another URL.</p>
<h2>Parameterless</h2>
<pre>parameterless()</pre>
<p>By putting "&lt;?php parameterless() ?&gt;" in the first line of the action you can force a redirect to the parameterless variant
of the page.</p>
<h2>Escaped echo</h2>
<pre>e($variable)</pre>
<p>In the views one should use "&lt;?php e($variable); ?&gt;" to echo and NOT the normal "echo". This function
escapes the variable (with htmlspecialchars) to prevent Cross-Site-Scripting (XSS) attacks.</p>
<h2>CSRF token</h2>
<pre>csrf_token()</pre>
<p>Between the "&lt;form method=&quot;post&quot;&gt;" and the "&lt;/form&gt;" tag in the view one should add "&lt;?php csrf_token(); ?&gt;".
This call will echo a hidden input field to the form that will prevent Cross-Site-Request-Forgery (CSRF) attacks.
Note: this is required when sending a form with the "post" method.</p>
<h2>Debug</h2>
<pre>debug($variable)</pre>
<p>The "debug" function logs the contents of a variable to the "Logging" panel of the debugger.
If the debugger is not loaded (in production) then calls to the "debug" function are ignored.
To reduce memory usage this function limits the output:</p>
<ol>
<li>Only the first 100 characters of each string are logged.</li>
<li>Only the first 25 elements of an array are logged.</li>
<li>Only the first 10 levels of nested objects/arrays are logged.</li>
</ol>
<p>These 3 limits can be set using 3 optional parameters in the above order. 
Hence, calling "debug($variable)" is equal to calling "debug($variable,100,25,10)".</p>
