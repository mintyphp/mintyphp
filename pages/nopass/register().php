<?php

use MintyPHP\NoPassAuth;

$error = false;
if (isset($_POST['username'])) {
  /** @var string $username */
  $username = $_POST['username'];
  if (!$username) {
    $error = "Username cannot be empty";
  } elseif (!NoPassAuth::register($username)) {
    $error = "User can not be registered";
  } else {
    include 'login().php';
  }
}
