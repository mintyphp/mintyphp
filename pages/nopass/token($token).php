<?php

use MintyPHP\NoPassAuth;
use MintyPHP\Router;

if (NoPassAuth::login($token)) {
  Router::redirect("admin");
}
Router::redirect("nopass/login");
