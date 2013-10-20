<?php parameterless() ?>
<h1>Functions</h1>
<p>In "lib/functions.php" you find 4 global functions that you can use anywhere in your application.</p>
<h2>Redirect</h2>
<pre>redirect($url)</pre>
<p>This "redirect" function redirects directly to another URL.</p>
<h2>Parameterless</h2>
<pre>parameterless()</pre>
<p>By putting "&lt;?php parameterless() ?&gt;" in the first line of the action you can force a redirect to the parameterless variant
of the page.</p>
<h2>Authenticate</h2>
<pre>authenticate($username,$password)</pre>
<p>Call this function to authenticate a user, example:</p>
<pre>
if (authenticate($username, $password)) {
  redirect("/admin");
} else {
  echo "Username/password not valid";
}
</pre>
<h2>Register</h2>
<pre>register($username,$password)</pre>
<p>Call this function to register a new user.</p>
<pre>
if (register($username, $password)) {
  authenticate($username, $password);
  redirect("/admin");
} else { 
  echo "User can not be registered";
}
</pre>