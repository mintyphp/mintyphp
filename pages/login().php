<?php

use MintyPHP\Auth;
use MintyPHP\Router;

$error = '';

if (isset($_POST['username'])) {
    /** @var string $username */
    $username = $_POST['username'];
    /** @var string $password */
    $password = $_POST['password'];
    if (Auth::login($username, $password)) {
        Router::redirect("admin");
    } else {
        $error = "Username/password not valid";
    }
}
