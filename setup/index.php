<?php
#Detect browser language for the installation proccess
$langbase = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
$langext = strtoupper(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 3, 2));
define('LANG',$langbase."_".$langext);
require('install.php');
?>
