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
        global $db;
        $limit = (int)$limit;

        $graph_items = [];
//        $items_db = $this->elibDb->getAllAuthors(300);


        $query = 'select authors.id, authors.post, authors.fio from authors, authors_to_organisations WHERE authors_to_organisations.orgsid=?s AND authors_to_organisations.authorid=authors.id';
        if (!empty($limit)) {
            $query .= " LIMIT $limit";
        }

        $items_db = $db->getAll($query, 5051);

        foreach ($items_db as $current_item) {

            $new_item = [];
            $new_item['id'] = $current_item['id'];
            $new_item['fio'] = $current_item['fio'];
            $new_item['post'] = $current_item['post'];
            $new_item['title'] = $current_item['fio'];


            $temp_rubrics = $this->elibDb->getAuthorRubrics($new_item['id']);

            if (empty($temp_rubrics)) {
                continue;
            }

            $new_item['rubric'] = $temp_rubrics[0]['rubric'];
            $new_item['rubric_md5'] = splitMd5($new_item['rubric']);
            $new_item['id'] = $new_item['rubric_md5'] . $new_item['id'];


            $q = 'select distinct publications_to_authors.authorid FROM publications, publications_to_authors WHERE  publications.id=publications_to_authors.publicationid AND publications.rubric=?s';

            $rel_authors = $db->getCol($q, $new_item['rubric']);


            foreach ($rel_authors as $key => $rel_author) {
                $rel_authors[$key] = $new_item['rubric_md5'] . $rel_authors[$key];
            }
            $new_item['references'] = $rel_authors;

            $graph_items[] = $new_item;
        }
        return $graph_items;
    }


    function PublicationsRelByPopolarRubris($group = false, $limit = 0)
    {
        $graph_items = [];
        $items_db = $this->elibDb->getAll('publications', 1000);


        foreach ($items_db as $current_item) {

            $new_item = [];
            $new_item['id'] = $current_item['id'];
            $new_item['title'] = $current_item['title'];
            $new_item['rubric'] = $current_item['rubric'];

            if (empty($new_item['rubric'])) {
                continue;
            }

//            $new_item['rublic'] = $this->elibDb->getAuthorMostPopularRublic($new_item['id']);


            if ($group) {
                $new_item['id'] = splitMd5($new_item['rubric']) . $new_item['id'];
            }


            $rel_publications = $this->elibDb->getPublicationRelByRubrics($new_item['rubric']);

            $new_item['references'] = [];


            foreach ($rel_publications as $rel_item) {
                if ($group) {
                    $rel_organisation['id'] = splitMd5($rel_item['rubric']) . $rel_item['id'];
                }
                $new_item['references'][] = $rel_organisation['id'];
            }

            $graph_items[] = $new_item;
        }
        return $graph_items;
    }
}


?>