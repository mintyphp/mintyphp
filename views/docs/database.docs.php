<?php parameterless() ?>
<h1>Database</h1>
<p>In the file "lib/database.php" you find the "Database" class. The front controller instantiates this
class into the "$db" variable. The class provides 4 public methods and a constructor:</p>
<h2>Constructor</h2>
<pre>new Database($debug, $host, $username, $password, $database)</pre>
<p>The front-controller executes this constructor specfying whether or not to run in debug mode. The other
parameters are inherited from the MySQLi constructor. This means that also "port" and "socket" can be specified
as extra arguments if needed. The instance is stored in the global "$db" variable.</p>
<h2>Query</h2>
<pre>$db-&gt;q($sql,$arg1,$arg2,...)</pre>
<p>Executes SQL containing "?" symbols. Every questionmark must be matched by an extra argument. Example:</p>
<pre>
$query = 'insert into users (username,password,salt,created) values (?,sha1(concat(?,?)),?,NOW())';
$success = $db-&gt;q($query,$username,$salt,$password,$salt);
</pre>
<p>Or when you want to iterate over records:</p>
<pre>
$users = $db-&gt;q('select * from users');

&lt;?php foreach ($users as $user): ?&gt;
&lt;li&gt;&lt;?php e($user['username']); ?&gt;&lt;/li&gt;
&lt;?php endforeach; ?&gt;
</pre>
<h2>Query one</h2>
<pre>$db-&gt;q1($sql,$arg1,$arg2,...)</pre>
<p>Same as "q", but only returns the first record or false. Example:</p>
<pre>
$query = 'select * from users where username = ? and sha1(concat(salt,?)) = password limit 1';
$user = $db-&gt;q1($query,$username,$password);
</pre>
<h2>Insert id</h2>
<pre>$db-&gt;id()</pre>
<p>Returns the "insert id" from the last executed SQL query. Example:</p>
<pre>
$query = 'insert into users (username,password,salt,created) values (?,sha1(concat(?,?)),?,NOW())';
$success = $db-&gt;q($query,$username,$salt,$password,$salt);
if ($success) {
  $userId = $db-&gt;id();
} else {
  $userId = false;
}
</pre>
<h2>Raw engine access</h2>
<pre>$db-&gt;handle()</pre>
<p>Returns the handle to the (e.g. MySQLi) engine. You normally do not need this.</p>
