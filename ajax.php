<?php

require 'init.php';


$length = 100;

if (!empty($_GET['l'])) {
    $length = (int)$_GET['l'];
}

$elibDb = new ElibraryDB();

$graph_organisations = [];

$organisations_db = $elibDb->getAllOrganisations();
$k = 0;
foreach ($organisations_db as $org_info) {
    $organisation = [];
    $organisation['id'] = $org_info['id'];
    $organisation['title'] = $org_info['name'];
    $organisation['city'] = $org_info['city'];
    $organisation['country'] = $org_info['country'];
    $organisation['id'] = $elibDb->generateOrganisationGraphId($organisation['id']);

    $authors = $elibDb->getOrganisationRelOrganisations($org_info['id']);
    $organisation['references'] = [];
    foreach ($authors as $author) {
        $rel_orgs = $elibDb->getAuthorOrganisations($author);
        foreach ($rel_orgs as $rel_org) {
            $organisation['references'][] = $elibDb->generateOrganisationGraphId($rel_org);
        }
    }
    $unique = array_unique($organisation['references']);
    $organisation['references'] = [];
    foreach ($unique as $item) {
        $organisation['references'][] = $item;
    }


    if ($k > $length) {
        break;
    }
    $k++;


    $graph_organisations[] = $organisation;
}

echo json_encode($graph_organisations, JSON_UNESCAPED_UNICODE);
exit;

