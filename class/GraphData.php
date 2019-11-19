<?php


class GraphData
{
    var $elibDb;

    function __construct()
    {
        $this->elibDb = new ElibraryDB();
    }

    function OrganisationsRelByAuthors($length = 100)
    {
        $graph_organisations = [];
        $organisations_db = $this->elibDb->getAllOrganisations();
        $k = 0;

        foreach ($organisations_db as $org_info) {
            $organisation = [];
            $organisation['id'] = $org_info['id'];
            $organisation['title'] = $org_info['name'];
            $organisation['city'] = $org_info['city'];
            $organisation['country'] = $org_info['country'];
            $organisation['id'] = $this->elibDb->generateOrganisationGraphId($organisation['id']);

            $authors = $this->elibDb->getOrganisationRelOrganisationsByAuthors($org_info['id']);
            $organisation['references'] = [];
            foreach ($authors as $author) {
                $rel_orgs = $this->elibDb->getAuthorOrganisations($author);
                foreach ($rel_orgs as $rel_org) {
                    $organisation['references'][] = $this->elibDb->generateOrganisationGraphId($rel_org);
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

        return $graph_organisations;
    }


    function OrganisationsRelByPublicationsCoAuthors($group = false, $limit = 0)
    {
        $graph_organisations = [];
        $organisations_db = $this->elibDb->getAllOrganisations($limit);


        foreach ($organisations_db as $org_info) {
            $organisation = [];
            $organisation['id'] = $org_info['id'];
            $organisation['title'] = $org_info['name'];
            $organisation['city'] = $org_info['city'];
            $organisation['country'] = $org_info['country'];

            if ($group) {
                $organisation['id'] = $this->elibDb->generateOrganisationGraphId($organisation['id']);
            }

            $rel_organisations = $this->elibDb->getOrganisationRelsByPublicationsCoAuthors($org_info['id']);

            $organisation['references'] = [];

            foreach ($rel_organisations as $rel_organisation) {
                if ($group) {
                    $rel_organisation = $this->elibDb->generateOrganisationGraphId($rel_organisation);
                }
                $organisation['references'][] = $rel_organisation;
            }

            $graph_organisations[] = $organisation;
        }

        return $graph_organisations;
    }


    function AuthorsRelByPublications($group = false, $limit = 0)
    {
        $graph_items = [];
        $items_db = $this->elibDb->getAllAuthors($limit);


        foreach ($items_db as $current_item) {
            $new_item = [];
            $new_item['id'] = $current_item['id'];
            $new_item['fio'] = $current_item['fio'];
            $new_item['post'] = $current_item['post'];
            $new_item['name'] = $current_item['name'];

            if ($group) {
                $new_item['id'] =  $new_item['name'] . '.' . $new_item['post'] . '.' . $new_item['id'];
            }

            $rel_organisations = $this->elibDb->getAuthorRelByPublications($current_item['id']);

            $new_item['references'] = [];

            foreach ($rel_organisations as $rel_organisation) {
                if ($group) {
                    $rel_organisation['id'] =  $rel_organisation['name'] . '.' . $rel_organisation['post'] . '.' . $rel_organisation['id'];
                }
                $new_item['references'][] = $rel_organisation;
            }

            $graph_items[] = $new_item;
        }

        return $graph_items;
    }


}


?>