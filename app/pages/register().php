<?php
$error = false;
if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    if (!$username) {
        $error = "Username cannot be empty";
    } elseif (!$password) {
        $error = "Password cannot be empty";
    } elseif ($password != $password2) {
        $error = "Passwords must match";
    } elseif (!Auth::register($username, $password)) {
        $error = "User can not be registered";
    } else {
        Auth::login($username, $password);
        Router::redirect("admin");
    }
}
