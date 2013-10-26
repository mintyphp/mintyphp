<h1>Login</h1>
<form method="post">
Username<br/>
<input name="username"/><br/>
Password<br/>
<input type="password" name="password"/><br/>
<br/>
<input type="submit"/><br/>
<?php e($error); ?>
<?php csrf_token(); ?>
</form>

<p><a href="/register">Register</a></p>