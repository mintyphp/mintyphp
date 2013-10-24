<?php include __DIR__.'/parts/header.html'; ?>
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
            <?php echo $body; ?>
        </div>
<?php include __DIR__.'/parts/footer.php'; ?>