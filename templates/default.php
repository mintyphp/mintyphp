<!DOCTYPE html>
<html>
  <head>
    <title>MindaPHP</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/default.css" rel="stylesheet">
  </head>
  <body>
    <a href="https://github.com/mevdschee/MindaPHP"><img style="position: absolute; top: 0; right: 0; border: 0;" src="/img/forkme_right_red_aa0000.png" alt="Fork me on GitHub"></a>
    <div class="title">
      <div class="logo">
        MindaPHP
      </div>
    </div>
    <div class="menu">
    <p>
      <a href="/hello/world">Hello world</a><br/>
      <a href="/hello">Hello you</a><br/>
      <a href="/admin">Admin area</a><br/>
      <a href="/docs">Documentation</a><br/>
      <a href="/dead_link">Dead link</a><br/>
    </p>
    <p>
      <?php $username = isset($_SESSION['user'])?$_SESSION['user']['username']:false ?>
      <?php if ($username): ?>
        <a href="/logout">Logout "<?php echo $username; ?>"</a>
      <?php else: ?>
        <a href="/login">Login</a>
      <?php endif; ?>
    </p>
  </div>
  <div class="body">
    <?php echo Router::getContent(); ?>
  </div>
</body>
</html>
