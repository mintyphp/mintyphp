<?php
if (NoPassAuth::login($token)) {
  Router::redirect("admin");
}
Router::redirect("nopass/login");