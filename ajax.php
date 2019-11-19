<?php

require_once __DIR__ . '/init.php';

$length = 100;

if (!empty($_GET['l'])) {
    $length = (int)$_GET['l'];
}


if (!empty($_GET['type'])) {
    $type = preg_replace('/[^\w]/m', '', $_GET['type']);
} else {
    $type = 'OrganisationsRelByPublicationsCoAuthors';
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




}

echo json_encode($info, JSON_UNESCAPED_UNICODE);
exit;

