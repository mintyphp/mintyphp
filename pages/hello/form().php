<?php 
if (isset($_POST['name'])) Router::redirect('/hello/'.urlencode($_POST['name']));