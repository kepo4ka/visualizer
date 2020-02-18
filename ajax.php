<?php

require_once __DIR__ . '/init.php';

$length = 15;

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
$primary_field = 'id';
$references_field = 'references';


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


function clearEmptyReferences($info, $primary_field, $references_field)
{
    $names = [];

    foreach ($info as $item) {
        $names[] = $item[$primary_field];
    }
    unset($item);

    $new_info = [];


    foreach ($info as $key => &$item) {
        $item['name'] = $item['id'];
        unset($item['id']);
        $item['imports'] = $item['references'];
        unset($item['references']);
        $item['size'] = rand(100, 1000);
    }
    unset($item);


    foreach ($info as $key => $item) {
        $temp = $item;
        $temp['imports'] = [];

        foreach ($item['imports'] as $key1 => $item1) {
            if (in_array($item1, $names)) {
                $temp['imports'][] = $item1;
            }
        }

        if (empty($temp['imports'])) {
            continue;
        }


        $new_info[] = $temp;
    }

    return $new_info;


    return $info;
}

$info = clearEmptyReferences($info, $primary_field, $references_field);

echo json_encode($info, JSON_UNESCAPED_UNICODE);
exit;

