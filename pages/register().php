<?php

use MintyPHP\Auth;
use MintyPHP\Router;

$error = false;
if (isset($_POST['username'])) {
  /** @var string $username */
  $username = $_POST['username'];
  /** @var string $password */
  $password = $_POST['password'];
  /** @var string $password2 */
  $password2 = $_POST['password2'];
  if (!$username) {
    $error = "Username cannot be empty";
  } elseif (!$password) {
    $error = "Password cannot be empty";
  } elseif ($password != $password2) {
    $error = "Passwords must match";
  } elseif (Auth::exists($username)) {
    $error = "Username is already taken";
  } elseif (!Auth::register($username, $password)) {
    $error = "User can not be registered";
  } else {
    Auth::login($username, $password);
    Router::redirect("admin");
  }
}
