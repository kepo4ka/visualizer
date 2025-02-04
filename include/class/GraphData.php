<?php


use Helper\DB;

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


    function PublicationsByPublications($search_array = [], $limit = 0)
    {
        global $db;

        $graph_items = [];
        $orgsid = 5051;
        $query = 'SELECT publicationid FROM publications_to_organisations where publications_to_organisations.orgsid=?s LIMIT ?i';

        return $db->query($query, $orgsid, $limit);
    }


    function AuthorsRelByPublications($group = false, $limit = 0)
    {
        global $db;
        $limit = (int)$limit;

        $graph_items = [];
//        $items_db = $this->elibDb->getAllAuthors(300);


        // получить список авторов указанной организации
        $org_id = 5051;
        $query = 'select authors.id, authors.post, authors.fio from authors, authors_to_organisations WHERE authors_to_organisations.orgsid=?s AND authors_to_organisations.authorid=authors.id';
        if (!empty($limit)) {
            $query .= " LIMIT $limit";

        }
        $items_db = $db->getAll($query, $org_id);


        foreach ($items_db as $current_item) {

            $new_item = [];
            $new_item['name'] = $current_item['id'];
            $new_item['fio'] = $current_item['fio'];
            $new_item['post'] = $current_item['post'];
            $new_item['title'] = $current_item['fio'];
            $new_item['title1'] = $new_item['post'];


            $temp_rubrics = $this->elibDb->getAuthorRubrics($current_item['id']);

            if (empty($temp_rubrics)) {
                continue;
            }

            $new_item['rubric'] = $temp_rubrics[0]['rubric'];
            $new_item['rubric_md5'] = ' .' . splitMd5($new_item['rubric']);
            $new_item['name'] = $new_item['rubric_md5'] . $new_item['name'];

            $q = 'select distinct publications_to_authors.authorid FROM publications, publications_to_authors WHERE publications_to_authors.authorid<>?s AND publications.id=publications_to_authors.publicationid AND publications.rubric=?s';

            $rel_authors = $db->getCol($q, $current_item['id'], $new_item['rubric']);


            foreach ($rel_authors as $key => $rel_author) {
                $rel_authors[$key] = $new_item['rubric_md5'] . $rel_authors[$key];
                $new_item['imports'][] = $rel_authors[$key];
            }


            $graph_items[] = $new_item;
        }

        return $graph_items;
    }


    function PublicationsRelByRubris($orgsid = 5051, $limit = 0)
    {
        global $db;

        $query = "select publications.id,
            title,
            year,
            language,
            rubric,
            in_rinc
            from publications, publications_to_organisations 
            where publications.id=publications_to_organisations.publicationid 
            and 
            publications.rubric<>''
            AND 
              publications_to_organisations.orgsid=$orgsid";

        if (!empty($limit)) {
            $query .= " limit $limit";
        }

        $publications = $db->getAll($query);


        foreach ($publications as &$publication) {
            $publication['name'] = $publication['id'];
            $publication['title1'] = $publication['title'];

            $query = "select end_publ_id FROM 
              publications_to_publications, publications_to_organisations
              WHERE 
                  publications_to_publications.origin_publ_id={$publication['id']}
              AND 
                  publications_to_publications.end_publ_id=publications_to_organisations.publicationid
              AND
                  publications_to_organisations.orgsid={$orgsid}
              ";

            $publication['imports'] = $db->getCol($query);
        }
        unset($publication);

        $publications = clearEmptyReferences($publications, PRIMARY_FIELD, REFEREFCES_FIELD);


        foreach ($publications as &$publication) {
            $publication['rubric_md5'] = ' .' . splitMd5($publication['rubric']);
            $publication['name'] = $publication['rubric_md5'] . $publication['name'];

            foreach ($publication['imports'] as &$rel_publication) {
                $query = "select rubric from publications 
                    where publications.id={$rel_publication}";
                $rel_publication_info = $db->getRow($query);

                if (empty($rel_publication_info)) {
                    unset($rel_publication);
                    continue;
                }

                $rubric_md5 = ' .' . splitMd5($rel_publication_info['rubric']);
                $rel_publication = $rubric_md5 . $rel_publication;

            }
            unset($rel_publication);
        }
        unset($publication);

        return $publications;
    }


    function PublicationsRelByPopolarRubris()
    {
        $graph_items = [];

        $orgsid = 5051;

        $query = "select publications.id,
            year,
            language,
            rubric,
            in_rinc,
            from publications, publications_to_organisations 
            where publications.id=publications_to_organisations.publicationid 
            AND 
              publications_to_organisations.orgsid=$orgsid";


        $items_db = $this->elibDb->getAll('publications', 1000);


        foreach ($items_db as $current_item) {
            $new_item = [];
            $new_item['name'] = $current_item['id'];
            $new_item['title'] = $current_item['title'];
            $new_item['rubric'] = $current_item['rubric'];

            if (empty($new_item['rubric'])) {
                continue;
            }

//            $new_item['rublic'] = $this->elibDb->getAuthorMostPopularRublic($new_item['id']);


            if ($group) {
                $new_item['name'] = splitMd5($new_item['rubric']) . $new_item['name'];
            }


            $rel_publications = $this->elibDb->getPublicationRelByRubrics($new_item['rubric']);

            $new_item['imports'] = [];


            foreach ($rel_publications as $rel_item) {
                if ($group) {
                    $rel_organisation['id'] = splitMd5($rel_item['rubric']) . $rel_item['id'];
                }
                $new_item['imports'][] = $rel_organisation['id'];
            }

            $graph_items[] = $new_item;
        }
        return $graph_items;
    }


    function getPublicationFullInfo($id)
    {
        $publication = DB::getById('publications', $id);

        $publication['rubric'] = preg_replace('/_/', ' ', $publication['rubric']);

        $authors_ids = DB::getByColAll('publications_to_authors', 'publicationid', $id);
        $relations_ids = DB::getByColAll('publications_to_publications', 'origin_publ_id', $id);
        $keywords_ids = DB::getByColAll('publications_to_keywords', 'publicationid', $id);

        foreach ($authors_ids as $author_id) {
            $author = DB::getById('authors', $author_id['authorid']);
            unset($author['id']);
            $publication['authors'][] = $author;
        }

        foreach ($relations_ids as $relations_id) {
            $relation = DB::getById('publications', $relations_id['end_publ_id']);
            $filtered = [];

            $filtered['title'] = $relation['title'];
            $filtered['type'] = $relation['type'];
            $filtered['year'] = $relation['year'];
            $filtered['rubric'] = preg_replace('/_/', ' ', $relation['rubric']);


            $publication['relations'][] = $filtered;
        }

        $publication['keywords'] = '';

        foreach ($keywords_ids as $keyword_id) {
            $keyword = DB::getById('keywords', $keyword_id['keywordid']);

            if (empty($keyword)) {
                continue;
            }
            $publication['keywords'] .= $keyword['name'] . ', ';
        }

       if ($keywords_ids > 1 && !empty($publication['keywords'])) {
            $publication['keywords'] = substr($publication['keywords'], 0, -2);
       }

        return $publication;
    }

}


?>