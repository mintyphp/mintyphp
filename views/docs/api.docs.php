<h1>API</h1>
<p>This is a reference of all global variables and functions.</p>
<pre>
Type    Function                            Location        Purpose          Level
=====================================================================================================
        e($variable)                        Template/View   Output           Public
        d($variable)                        Everywhere      Debugging        Public
array   DB::q($sql,...)                     Action          Database query   Public
array   DB::q1($sql,...)                    Action          Database query   Public
string  DB::qv($sql,...)                    Action          Database query   Public
bool    Auth::login($username,$password)    Action          Logging in       Public
bool    Auth::logout()                      Action          Logging out      Public
bool    Auth::register($username,$password) Action          Adding users     Public
        Router::addRoute($req,$loc)         Router Config   Routing          Public
        Router::redirect($url)              Action          Redirection      Public
array   Router::getParameters()             Action          User input       Public
string  Session::getCsrfInput()             Template/View   Form security    Public
=====================================================================================================
bool    Loader::register($path,$namespace)  Loader Config   Loading classes  Advanced
        Router::parameterless()             Action          Redirection      Medium
string  Router::getContent()                Template        Create template  Medium
integer DB::id()                            Action          Database inserts Medium
</pre>
