<?php

use MintyPHP\Router;

if (isset($_POST['name'])) {
    /** @var string $name */
    $name = $_POST['name'];
    Router::redirect('hello/' . urlencode($name));
}
