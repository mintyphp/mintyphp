<?php
// Load the authenticate functions
require '../lib/authenticate.php';

$error = '';
if (isset($_POST['username']))
{ if (login($_POST['username'],$_POST['password'])) {
    redirect("/admin");
  }
  else $error = "Username/password not valid";
}