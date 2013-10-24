<?php include __DIR__.'/parts/header.html'; ?>
        <div class="title">
            <div class="logo">
                MindaPHP - Documentation
            </div>
        </div>
        <div class="menu">
            <p>
                <a href="/">&lt;&lt; Back</a>
            </p>
            <p>
                <a href="/docs">Overview</a><br/>
                <a href="/docs/structure">Structure</a><br/>
                <a href="/docs/router">Router</a><br/>
                <a href="/docs/database">Database</a><br/>
                <a href="/docs/functions">Functions</a><br/>
                <a href="/docs/authenticate">Authenticate</a><br/>
                <a href="/docs/api">API</a><br/>
            </p>
        </div>
        <div class="body" style="width: 600px;">
            <?php echo $body; ?>
        </div>
<?php include __DIR__.'/parts/footer.php'; ?>