<?php if (isset($_POST['name'])) redirect('/hello/'.urlencode($_POST['name'])); ?>
<h1>Advanced "hello world"</h1>
<?php if (isset($parameters[0])): ?>
<p>Hello <?php echo $parameters[0]; ?></p>
<p><a href="/hello">Back</a></p>
<?php else: ?>
<p>Hello</p>
<form method="post">
Name<br/>
<input name="name"/><br/>
<br/>
<input type="submit"/>
</form>
<?php endif; ?>