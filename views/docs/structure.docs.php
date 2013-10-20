<?php parameterless() ?>
<h1>Structure</h1>
<pre>
.
├── actions                         Folder containing PHP for dynamic pages
│   ├── hello
│   │   ├── form.php                Advanced "hello world" example (form)
│   │   └── index.php               Advanced "hello world" example (output)
│   ├── login.php                   Authorization example: logging in
│   └── register.php                Authorization example: registration
├── lib                             
│   ├── authenticate.php            Authentication functions
│   ├── db_mysqli.php               Database abstraction layer ($db)
│   ├── functions.php               Helper functions
│   └── router.php                  Default routing engine ($router)
├── templates                       Folder containing templates
│   ├── default.php
│   ├── docs.php                    Template used by the page you look at
│   ├── error.php
│   └── parts
│       ├── footer.php
│       └── header.html
├── views                           Folder containing HTML for dynamic pages
│   ├── 404.error.php               Page served when page is not found
│   ├── admin
│   │   └── index.default.php       Authorization example: protected page
│   ├── docs
│   │   ├── authenticate.docs.php
│   │   ├── database.docs.php
│   │   ├── functions.docs.php
│   │   ├── overview.docs.php
│   │   ├── reference.docs.php
│   │   ├── router.docs.php
│   │   └── structure.docs.php      This is the page you are looking at
│   ├── hello
│   │   ├── form.default.php        Advanced "hello world" example (form)
│   │   ├── index.default.php       Advanced "hello world" example (output)
│   │   └── world.default.php       Simple "hello world" example
│   ├── login.default.php           Authorization example: logging in
│   ├── logout.default.php          Authorization example: logging out
│   └── register.default.php        Authorization example: registration
└── web                             Folder containing static files
    ├── css
    │   └── default.css
    ├── db.sql                      Authorization example: database layout
    └── index.php                   Front-controller file
</pre>