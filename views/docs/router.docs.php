<?php parameterless() ?>
<h1>Router</h1>
<h2>Constructor</h2>
<pre>new Router($debug, $request, $actionRoot, $viewRoot, $templateRoot)</pre>
<p>The front-controller executes this constructor specfying whether or not to run in debug mode. The other
parameters are typically "$_SERVER['REQUEST_URI']", "../actions", "../views", and "../templates". The instance is stored
in the global "$router" variable.</p>
<h2>Redirect</h2>
<pre>$router-&gt;redirect($request, $location)</pre>
<p>This should also be called from the front-controller (in "web/index.php"). Typically this is used to redirect
the empty requested URL "/" to a specific location. This should not be confused with the global "redirect" function
that redirects directly (not conditionally as this one) to another URL.
<h2>Request</h2>
<pre>$router-&gt;getRequest()</pre>
<p>With this call you can read the requested URL. Normally this is the same as "$_SERVER['REQUEST_URI']".</p>
<h2>URL</h2>
<pre>$router-&gt;getUrl()</pre>
<p>With this call you can find the effective routed URL. This does not contain parameters and/or trailing slashes.
It does also show the redirected target, not the entered URL.</p>
<h2>View</h2>
<pre>$router-&gt;getView()</pre>
<p>This gets the path to the view file that is loaded. For example on this page it returns: <?php var_dump($router->getView()); ?>.
<h2>Action</h2>
<pre>$router-&gt;getAction()</pre>
<p>This gets the path to the action file that is loaded. For example on this page it returns: <?php var_dump($router->getAction()); ?>.
<h2>Template</h2>
<pre>$router-&gt;getTemplate()</pre>
<p>This gets the path to the template file that is loaded. For example on this page it returns: <?php var_dump($router->getTemplate()); ?>.
<h2>Parameters</h2>
<pre>$router-&gt;getParameters()</pre>
<p>This gets the parameters effective parameters that can also be accessed by using the shortcut "$parameters". See
also the "parameterless" function in the "Functions" section of the documentation.</p>
