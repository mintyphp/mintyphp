<?php
$error = '';
if (isset($_POST['username']))
{ if (authenticate($_POST['username'],$_POST['password'])) {
    redirect("/admin");
  }
  else $error = "Username/password not valid";
}