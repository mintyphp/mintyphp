<?php 
// Load the authenticate functions
require '../lib/authenticate.php';

logout();
redirect('/login');