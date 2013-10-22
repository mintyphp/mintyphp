<?php parameterless() ?>
<h1>Documentation</h1>
<p>This is the documentation of the MindaPHP framework.</p>
<ul>
  <li>Philosophy</li>
  <li>Presentation/logic separation</li>
  <li>Front controller</li>
  <li>Default routing</li>
  <li>PHP templating</li>
  <li>Database abstraction layer</li>
  <li>Authentication</li>
  <li>Security</li>
</ul>

<h2>Philosophy</h2>
<p>MindaPHP aims to be a full-stack framework that is:</p>
<ol>
  <li>Easy to learn</li>
  <li>Secure by design</li>
  <li>Light-weight</li>
</ol>
<p>By design, it does:</p>
<ol>
  <li>&hellip; have one variable scope for all layers.</li>
  <li>&hellip; require you to write SQL queries (no ORM).</li>
  <li>&hellip; use PHP as a templating language.</li>
</ol>
<p>Mainly to make it easy to learn for PHP developers.</p>

<h2>Presentation/logic separation</h2>

<p>The "views" folder holds all HTML for dynamic pages, while the "actions" folder is should
hold the PHP part of these pages. The "web" and the "templates" folder hold the static files
and the templates that are used by the views. Note that every pages must have a "view" file and
can optionally have an "action" file that holds its logic.</p>

<h2>Front controller</h2>

<p>All URL's hit the "web/index.php" file. This is achieved using URL rewriting (with
"mod_rewrite" in Apache). The paradigm of routing every request through one file is called
"front controller". The file holds all configuration for your project and is thus the first
file you will edit.</p>

<h2>Default routing</h2>

<p>The view files (in the "views" folder) have an URL on which they can be reached. They may
reside in a sub-folder and their filename is constructed like this: "{name}.{template}.php".
Both the folder path and the "name" segment are part of the URL. Files with the name "index"
can be used to serve the directory URL.</p>

<p>The variable "$parameters" can be used to get access to anything provided after the URL. This
means that when you access the URL "/customers/23", the router will match the "customers" page
and set "$parameters[0]" to "23". This behavior can be turned off on a per page basis by calling
the "parameterless" function. This will guarantee a consistent URL, which is useful when using
relative links.</p>

<p>The router has a "redirect" method, that allows you to map certain URL's to other URL's. A
simple redirect that most projects have is that the "/" URL is redirected to some page in the
project. These redirects need to be called on the router from the "web/index.php"
front-controller file.</p>

<p>Note that there is a dynamic page named "404", that will be rendered when a page is not found.</p>

<h2>PHP templating</h2>

<p>The "template" folder holds all templates. Normally the action is executed and the output is
captured in the "$body" variable. A template will "echo" this variable where the action output
needs to be placed. If you do not want a template you can use the "none" template. If given the
dynamic page will be rendered directly.</p>

<h2>Database abstraction layer</h2>

<p>The "$db" variable holds your database connection. It allows you to execute SQL queries very
simple (using the "query" method). It protects you against SQL injection attacks. Note that this
method is not suited for large datasets that exceed the PHP memory limit.</p>

<h2>Authentication</h2>

<p>A basic example for registering users and logging them in is included. This example shows
some security best practises. It uses session cookies and stores sha1 hashed passwords that are
secured with a md5 salt.</p>

<h2>Security</h2>

<p>Protection mechanisms against SQL injection, Cross-Site-Scripting (XSS) and
Cross-Site-Request-Forgery (CSRF) are provided. In the views one should use the "e()" function
to escape output to protect against XSS. The forms should use the "post" method and must call
the "csrf_token()" function to protect against CSRF.</p>