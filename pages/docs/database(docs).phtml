<h1>Database</h1>
<p>This class provides 4 public methods and the parameters are:</p>
<p>These functions can be statically accessed from the global "DB" class.</p>
<h2>Query</h2>
<pre>DB::q($sql,$arg1,$arg2,...)</pre>
<p>Executes SQL containing "?" symbols. Every questionmark must be matched by an extra argument. Example:</p>
<pre>
$query = 'insert into users (username,password,salt,created) values (?,sha1(concat(?,?)),?,NOW())';
$success = DB::q($query,$username,$salt,$password,$salt);
</pre>
<p>Or when you want to iterate over records:</p>
<pre>
$users = DB::q('select * from users');

&lt;?php foreach ($users as $user): ?&gt;
&lt;li&gt;&lt;?php e($user['username']); ?&gt;&lt;/li&gt;
&lt;?php endforeach; ?&gt;
</pre>
<h2>Query one</h2>
<pre>DB::q1($sql,$arg1,$arg2,...)</pre>
<p>Same as "q", but only returns the first record or false. Example:</p>
<pre>
$query = 'select * from users where username = ? and sha1(concat(salt,?)) = password limit 1';
$user = DB::q1($query,$username,$password);
</pre>
<h2>Insert id</h2>
<pre>DB::id()</pre>
<p>Returns the "insert id" from the last executed SQL query. Example:</p>
<pre>
$query = 'insert into users (username,password,salt,created) values (?,sha1(concat(?,?)),?,NOW())';
$success = DB::q($query,$username,$salt,$password,$salt);
if ($success) {
  $userId = DB::id();
} else {
  $userId = false;
}
</pre>
<h2>Raw engine access</h2>
<pre>DB::handle()</pre>
<p>Returns the handle to the (e.g. MySQLi) engine. You normally do not need this.</p>
