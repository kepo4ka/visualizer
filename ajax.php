<?php

require_once __DIR__ . '/init.php';

$length = 20;

if (!empty($_GET['l'])) {
    $length = (int)$_GET['l'];
}


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
$primary_field = 'name';
$references_field = 'imports';


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
//        $info = redisGet($key);

        if (empty($info)) {
//            echo 'work SLOW...\n';

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




$info = $graph_data->clearEmptyReferences($info, $primary_field, $references_field);

echo json_encode($info, JSON_UNESCAPED_UNICODE);
exit;

