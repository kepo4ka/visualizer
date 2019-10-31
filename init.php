<?php
require_once 'lib/safemysql.class.php';

require_once 'config.php';

require_once 'functions.php';
require_once 'class/ElibraryDB.php';
require_once 'class/ElibraryDB.php';

$user = 'root';
$pass = '';
$db_name = 'elibrary';
$host = 'localhost';

$db = new SafeMysql(array('host' => $host, 'user' => $user, 'pass' => $pass, 'db' => $db_name, 'charset' => 'utf8'));

?>