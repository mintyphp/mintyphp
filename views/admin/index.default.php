<?php if (!isset($_SESSION['user'])) redirect('/login'); ?>
<h1>Admin area</h1>
<p>You must be logged in to see this.</p>
<p>The session stored user object is:</p>
<pre><?php print_r($_SESSION['user']);?></pre>
