<?php


class ElibraryDB
{
    var $db;
    var $organisations = 'organisations';
    var $publications = 'publications';
    var $authors = 'authors';
    var $keywords = 'keywords';
    var $authors_to_organisations = 'authors_to_organisations';
    var $publications_to_organisations = 'publications_to_organisations';
    var $publications_to_authors = 'publications_to_authors';
    var $publications_to_keywords = 'publications_to_keywords';
    var $publications_to_publications = 'publications_to_publications';


    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function getRow($table, $id)
    {
        $query = 'SELECT * FROM ?n WHERE `id`=?s';

        return $this->db->getRow($query, $table, $id);
    }


    function getAllOrganisations($limit = 0)
    {
        $query = 'SELECT * FROM ?n';

        if (!empty($limit)) {
            $query .= ' LIMIT ?i';
        }

        return $this->db->getAll($query, $this->organisations, $limit);
    }

    function getAllAuthors($limit = 0)
    {
        $query = "SELECT {$this->authors}.id, fio, post, name FROM {$this->authors}, {$this->authors_to_organisations}, {$this->organisations} WHERE {$this->authors}.id={$this->authors_to_organisations}.authorid AND {$this->authors_to_organisations}.orgsid={$this->organisations}.id ";

        if (!empty($limit)) {
            $query .= ' LIMIT ?i';
        }

        return $this->db->getAll($query, $limit);
    }


    function getOrganisationMain($id)
    {
        $query = 'SELECT `id`, `city`, `country`, `type` FROM ?n WHERE `id`=?s';
        return $this->db->getRow($query, $this->organisations, $id);
    }

    function generateOrganisationGraphId($id)
    {
        $info = $this->getRow($this->organisations, $id);

        return shortMd5($info['country']) . '.' . shortMd5($info['city']) . '.' . $info['id'];
    }


    function generateAuthorGraphId($id)
    {
        $info = $this->getRow($this->authors, $id);

        return shortMd5($info['country']) . '.' . shortMd5($info['city']) . '.' . $info['id'];
    }

    function getAllOrganisationsIds()
    {
        $query = 'SELECT `id` FROM ?n';
        return $this->db->getCol($query, $this->organisations);
    }


    function getOrganisationRelOrganisationsByAuthors($id = 5051)
    {
        $query = 'SELECT `authorid` FROM `authors_to_organisations` WHERE `authors_to_organisations`.`orgsid`=?s';

        $authors_ids = $this->db->getCol($query, $id);

        return $authors_ids;
    }


    function getAuthorOrganisations($id = 781679)
    {
        $query = 'SELECT `orgsid` FROM `authors_to_organisations` WHERE `authors_to_organisations`.`authorid`=?s';

        $authors_ids = $this->db->getCol($query, $id);

        return $authors_ids;
    }


    function getOrganisationRelsByPublicationsCoAuthors($id = 5051)
    {
        $query = 'SELECT DISTINCT `authors_to_organisations`.orgsid FROM `authors_to_organisations`, `publications_to_organisations`, `publications_to_authors` WHERE authors_to_organisations.orgsid<>?s AND `publications_to_organisations`.orgsid=?s AND publications_to_organisations.publicationid=publications_to_authors.publicationid AND publications_to_authors.authorid=authors_to_organisations.authorid';
        $authors_ids = $this->db->getCol($query, $id, $id);
        return $authors_ids;
    }


    function getAuthorRelByPublications($id = 21817)
    {
        $query = 'SELECT `authorid` FROM `publications_to_authors` WHERE `publications_to_authors`.`publicationid`=?s';

        $authors_ids = $this->db->getCol($query, $id);

        return $authors_ids;
    }

    function getAuthorRublics($id)
    {
        $query = 'SELECT DISTINCT `publications`.`rubric` FROM `publications_to_authors`, `publications` WHERE publications_to_authors.publicationid=publications.id AND publications_to_authors.authorid=?s';
        $authors_ids = $this->db->getCol($query, $id);
        return $authors_ids;
    }

    function getAuthorMostPopularRublic($id)
    {
        $query = 'SELECT `publications`.`rubric` FROM `publications_to_authors`, `publications` WHERE publications_to_authors.publicationid=publications.id AND publications_to_authors.authorid=?s GROUP BY rubric';
        $authors_ids = $this->db->getAll($query, $id);
        return $authors_ids;
    }

    function getPublicationRelByRubrics($rubric)
    {
        $query = 'SELECT DISTINCT id, title, rubric FROM `publications` WHERE publications.rubric=?s';
        $authors_ids = $this->db->getAll($query, $rubric);
        return $authors_ids;
    }


    function updateRublics()
    {
        $query = 'SELECT id, `rubric` FROM `publications`';

        $publications = $this->db->getAll($query);

        foreach ($publications as $publication) {
            $publication['rubric'] = str_replace(' ', '_', $publication['rubric']);

            save($publication, 'publications');
        }

        return count($publications);

    }


    function getAll($table, $limit = 0)
    {
        $query = 'select * from ?n';
        $limit = (int)$limit;

        if (!empty($limit)) {
            $query .= ' LIMIT ' . $limit;
        }

        return $this->db->getAll($query, $table);
    }
}


?>