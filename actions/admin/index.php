<?php 
if (!isset($_SESSION['user'])) redirect('/login');
$user = $_SESSION['user'];
$users = $db->q('select * from users');