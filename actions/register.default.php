<?php
$error = false;
if (isset($_POST['username']))
{ $username = $_POST['username'];
  $password = $_POST['password'];
  $password2 = $_POST['password2'];
  if ($password!=$password2) 
  { $error = "Passwords must match"; 
  } 
  elseif (!register($username, $password))
  { $error = "User can not be registered";
  }
  else
  { authenticate($username, $password);
    redirect("/admin");
  }
}
?>
<h1>Register</h1>
<form method="post">
Username<br/>
<input name="username"/><br/>
Password<br/>
<input type="password" name="password"/><br/>
Password (again)<br/>
<input type="password" name="password2"/><br/>
<br/>
<input type="submit"/><br/>
<?php echo $error; ?>
</form>