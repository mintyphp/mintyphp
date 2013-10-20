<?php
$error = '';
if (isset($_POST['username']))
{ if (authenticate($_POST['username'],$_POST['password'])) {
    redirect("/admin");
  }
  else $error = "Username/password not valid";
}
?>
<h1>Login</h1>
<form method="post">
Username<br/>
<input name="username"/><br/>
Password<br/>
<input type="password" name="password"/><br/>
<br/>
<input type="submit"/><br/>
<?php echo $error; ?>
</form>

<p><a href="/register">Register</a></p>