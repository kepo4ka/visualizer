<?php

require 'init.php';



$elibDb = new ElibraryDB();

$graph_organisations = [];

$organisations_db = $elibDb->getAllOrganisations();
$k = 0;
foreach ($organisations_db as $org_info) {
    $organisation = [];
    $organisation['id'] = $org_info['id'];
    $organisation['title'] = $org_info['name'];

    $authors = $elibDb->getOrganisationRelOrganisations($org_info['id']);
    $organisation['references'] = [];
    foreach ($authors as $author) {
        $rel_orgs = $elibDb->getAuthorOrganisations($author);
        foreach ($rel_orgs as $rel_org) {

            $organisation['references'][] = $rel_org;
        }
    }
    $unique = array_unique($organisation['references']);
    $organisation['references'] = [];
    foreach ($unique as $item) {
        $organisation['references'][] = $item;
    }

    if ($k > 100) {
        break;
    }
    $k++;


    $graph_organisations[] = $organisation;
}

echo json_encode($graph_organisations, JSON_UNESCAPED_UNICODE);
exit;

