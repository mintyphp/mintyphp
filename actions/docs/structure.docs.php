<?php parameterless() ?>
<h1>Structure</h1>
<pre>
.
├── actions                         Folder containing dynamic pages
│   ├── 404.error.php               Page served when page is not found
│   ├── admin
│   │   └── index.default.php       Authorization example: protected page
│   ├── docs
│   │   ├── database.docs.php
│   │   ├── functions.docs.php
│   │   ├── overview.docs.php
│   │   ├── reference.docs.php
│   │   ├── router.docs.php
│   │   └── structure.docs.php      This is the page you are looking at
│   ├── hello
│   │   ├── index.default.php       Advanced "hello world" example
│   │   └── world.default.php       Simple "hello world" example
│   ├── login.default.php           Authorization example: logging in
│   ├── logout.default.php          Authorization example: logging out
│   └── register.default.php        Authorization example: registration
├── lib                             
│   ├── db_mysqli.php               Database abstraction layer ($db)
│   ├── functions.php               All helper functions
│   └── router.php                  Default routing engine ($router)
├── templates                       Folder containing templates
│   ├── default.php
│   ├── docs.php                    Template used by the page you look at
│   ├── error.php
│   └── parts
│       ├── footer.php
│       └── header.html
└── web                             Folder containing static files
    ├── css
    │   └── default.css
    ├── db.sql                      Authorization example: database layout
    └── index.php                   Front-controller file
</pre>