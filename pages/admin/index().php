<?php

use MintyPHP\DB;
use MintyPHP\Router;

if (!isset($_SESSION['user'])) Router::redirect('login');
$user = $_SESSION['user'];
$users = DB::select('select * from users');
