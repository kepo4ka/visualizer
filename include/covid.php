<?php
require_once __DIR__ . '/init.php';


$user = 'root';
$pass = '';
$db_name = 'coronovirus';
$host = 'localhost';
$db = new SafeMysql(array('host' => $host, 'user' => $user, 'pass' => $pass, 'db' => $db_name, 'charset' => 'utf8'));

require_once __DIR__ . '/class/CovidDB.php';


if (!empty($_GET['type'])) {
    $type = preg_replace('/[^\w]/m', '', $_GET['type']);
} else {
    $type = 'airports';
}

$covid = new CovidDB($length);


switch ($type)
{
    case 'airports':

        $key = "covid_airports:$length";
        $info = redisGet($key);
        if (empty($info))
        {
            $info = $covid->getAirports();
            redisSet($key, $info);
        }

        break;
}


//$info = clearEmptyReferences($info, PRIMARY_FIELD, REFEREFCES_FIELD);


echo json_encode($info, JSON_UNESCAPED_UNICODE);
exit;

?>