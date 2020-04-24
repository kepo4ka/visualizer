<?php
require_once __DIR__ . '/init.php';

require_once __DIR__ . '/class/ElibraryDB.php';
require_once __DIR__ . '/class/GraphData.php';

$user = 'root';
$pass = '';
$db_name = 'elibrary';
$host = 'localhost';

$db = new SafeMysql(array('host' => $host, 'user' => $user, 'pass' => $pass, 'db' => $db_name, 'charset' => 'utf8'));


if (!empty($_GET['type'])) {
    $type = preg_replace('/[^\w]/m', '', $_GET['type']);
} else {
    $type = 'AuthorsRelByPublications';
}


if (empty($type)) {
    echo 'error';
    exit;
}


$graph_data = new GraphData();
$info = [];


switch ($type) {
    case 'OrganisationsRelByAuthors':
        $info = $graph_data->OrganisationsRelByAuthors($length);
        break;

    case 'OrganisationsRelByPublicationsCoAuthors':
        $info = $graph_data->OrganisationsRelByPublicationsCoAuthors(true, $length);
        break;

    case 'AuthorsRelByPublications':

//        $info = $graph_data->elibDb->updateRubrics();

        $start = microtime(true);

        $key = "AuthorsRelByPublications:$length";


        $info = redisGet($key);

        if (empty($info)) {
            $info = $graph_data->AuthorsRelByPublications(true, $length);
            redisSet($key, $info);

        }
        //getTime($start);
        break;
    case 'PublicationsRelByPopolarRubris':
        $info = $graph_data->PublicationsRelByPopolarRubris(true, $length);
//      $elib = new ElibraryDB();
//        $info = $elib->updateRublics();
        break;

}


$info = clearEmptyReferences($info, PRIMARY_FIELD, REFEREFCES_FIELD);

foreach ($info as &$item)
{
    unset($item['fio']);
    unset($item['post']);
}
unset($item);

echo json_encode($info, JSON_UNESCAPED_UNICODE);
exit;