<?php
$error = '';
if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $token = NoPassAuth::token($username);
    if ($token) {
        if (!Cache::get('NoPassAuth_mailto_'.$username)) {
            Cache::set('NoPassAuth_mailto_'.$username, '1', NoPassAuth::$tokenValidity);
            mail($username, 'Login to '.Router::getBaseUrl(), 'Click here: '.Router::getBaseUrl()."nopass/token/$token");
        }
        Router::redirect("nopass/sent");
    }
    $error = 'Not found';
}
