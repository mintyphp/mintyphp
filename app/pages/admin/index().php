<?php
if (!isset($_SESSION['user'])) {
    Router::redirect('login');
}
$user = $_SESSION['user'];
$users = DB::select('select * from users');
