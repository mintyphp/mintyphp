<h1>Router</h1>
<p>The instance is stored in the global "Router" class.</p>
<h2>Redirect</h2>
<pre>Router::setRedirect($request, $location)</pre>
<p>This should also be called from the front-controller (in "web/index.php"). Typically this is used to redirect
the empty requested URL "/" to a specific location. This should not be confused with the global "redirect" function
that redirects directly (not conditionally as this one) to another URL.
<h2>Request</h2>
<pre>Router::getRequest()</pre>
<p>With this call you can read the requested URL. Normally this is the same as "$_SERVER['REQUEST_URI']".</p>
<h2>URL</h2>
<pre>Router::getUrl()</pre>
<p>With this call you can find the effective routed URL. This does not contain parameters and/or trailing slashes.
It does also show the redirected target, not the entered URL.</p>
<h2>View</h2>
<pre>Router::getView()</pre>
<p>This gets the path to the view file that is loaded. For example on this page it returns: <?php var_dump(Router::getView()); ?>.
<h2>Action</h2>
<pre>Router::getAction()</pre>
<p>This gets the path to the action file that is loaded. For example on this page it returns: <?php var_dump(Router::getAction()); ?>.
<h2>Template</h2>
<pre>Router::getTemplate()</pre>
<p>This gets the path to the template file that is loaded. For example on this page it returns: <?php var_dump(Router::getTemplate()); ?>.
<h2>Parameters</h2>
<pre>Router::getParameters()</pre>
<p>This gets the parameters effective parameters that can also be accessed by using the shortcut "$parameters". See
also the "parameterless" function in the "Functions" section of the documentation.</p>
<h2>Redirect</h2>
<pre>Router::redirect($url)</pre>
<p>This "redirect" function redirects directly to another URL.</p>
<h2>Parameterless</h2>
<pre>Router::parameterless()</pre>
<p>By putting "&lt;?php Router::parameterless() ?&gt;" in the first line of the action you can force a redirect to the parameterless variant
of the page.</p>
<h2>CSRF token</h2>
<pre>Session::getCsrfInput()</pre>
<p>Between the "&lt;form method=&quot;post&quot;&gt;" and the "&lt;/form&gt;" tag in the view one should add "&lt;?php Session::getCsrfInput(); ?&gt;".
This call will echo a hidden input field to the form that will prevent Cross-Site-Request-Forgery (CSRF) attacks.
Note: this is required when sending a form with the "post" method.</p>
