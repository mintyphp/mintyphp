<?php 
if (isset($_POST['name'])) redirect('/hello/'.urlencode($_POST['name']));