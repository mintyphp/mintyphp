<?php

use MintyPHP\Router;
use MintyPHP\DB;

if (!isset($_SESSION['user'])) Router::redirect('login');
$user = $_SESSION['user'];
$users = DB::select('select * from users');
