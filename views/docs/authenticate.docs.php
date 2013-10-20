<?php parameterless() ?>
<h1>Authenticate</h1>
<p>In "lib/authenticate.php" you find 3 functions that you can use anywhere after adding the following:</p>
<pre>require '../lib/authenticate.php';</pre>
<h2>Login</h2>
<pre>login($username,$password)</pre>
<p>Call this function to authenticate a user, example:</p>
<pre>
if (login($username, $password)) {
  redirect("/admin");
} else {
  echo "Username/password not valid";
}
</pre>
<h2>Logout</h2>
<pre>logout()</pre>
<p>Call this function to de-authenticate a user, example:</p>
<pre>
logout();
redirect("/login");
</pre>
<h2>Register</h2>
<pre>register($username,$password)</pre>
<p>Call this function to register a new user, example:</p>
<pre>
if (register($username, $password)) {
  login($username, $password);
  redirect("/admin");
} else { 
  echo "User can not be registered";
}
</pre>