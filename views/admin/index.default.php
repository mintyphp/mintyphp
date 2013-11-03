<h1>Admin area</h1>
<p>You must be logged in to see this.</p>
<p>The session stored user object is:</p>
<pre><?php print_r($user);?></pre>
<p>All registered users:</p>
<table>
  <thead>
    <tr>
      <th>Username</th>
      <th>Created</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $user): ?>
      <tr><td><?php e($user['users']['username']);?></td><td><?php e($user['users']['created']);?></td></tr>
    <?php endforeach; ?>
  </tbody>
</table>
