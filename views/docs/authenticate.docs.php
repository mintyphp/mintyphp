<h1>Authenticate</h1>
<p>In "vendor/core/auth.php" you find 3 functions that you can use anywhere.</p>
<h2>Login</h2>
<pre>Auth::login($username,$password)</pre>
<p>Call this function to authenticate a user, example:</p>
<pre>
if (Auth::login($username, $password)) {
  Router::redirect("/admin");
} else {
  $error = "Username/password not valid";
}
</pre>
<h2>Logout</h2>
<pre>Auth::logout()</pre>
<p>Call this function to de-authenticate a user, example:</p>
<pre>
Auth::logout();
Router::redirect("/login");
</pre>
<h2>Register</h2>
<pre>Auth::register($username,$password)</pre>
<p>Call this function to register a new user, example:</p>
<pre>
if (Auth::register($username, $password)) {
  Auth::login($username, $password);
  Router::redirect("/admin");
} else { 
  $error = "User can not be registered";
}
</pre>
