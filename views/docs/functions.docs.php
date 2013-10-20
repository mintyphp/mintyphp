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