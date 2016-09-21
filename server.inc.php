<?php
$db_host ="localhost";
$db_name = "dc_katalog";
$db_user = "root";
$db_pass = "";

$db = new PDO('mysql:host='.$db_host.';dbname='.$db_name.';charset=utf8;',$db_user,$db_pass);
$db->exec("set names utf8");
?>