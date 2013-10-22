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
