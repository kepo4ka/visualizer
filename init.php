<?php
require_once __DIR__ . '/lib/safemysql.class.php';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/predis/redis.php';

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/class/ElibraryDB.php';
require_once __DIR__ . '/class/GraphData.php';

$user = 'root';
$pass = '';
$db_name = 'elibrary';
$host = 'localhost';

$db = new SafeMysql(array('host' => $host, 'user' => $user, 'pass' => $pass, 'db' => $db_name, 'charset' => 'utf8'));

?>