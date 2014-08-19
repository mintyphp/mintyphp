<?php
if (!isset($_SESSION['user'])) Router::redirect('login');
$user = $_SESSION['user'];
$users = Query::records('select * from users');