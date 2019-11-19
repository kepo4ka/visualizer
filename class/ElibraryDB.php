<?php


class ElibraryDB
{
    var $db;
    var $organisations = 'organisations';
    var $publications = 'publications';
    var $authors = 'authors';
    var $keywords = 'keywords';
    var $authors_to_organsations = 'authors_to_organsations';
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
        $query = "SELECT {$this->authors}.id, fio, post, name FROM {$this->authors}, {$this->authors_to_organsations}, {$this->organisations} WHERE {$this->authors}.id={$this->authors_to_organsations}.authorid AND {$this->authors_to_organsations}.orgsid={$this->organisations}.id ";

        if (!empty($limit)) {
            $query .= ' LIMIT ?i';
        }

        return $this->db->getAll($query, $this->authors, $limit);
    }


    function getOrganisationMain($id)
    {
        $query = 'SELECT `id`, `city`, `country`, `type` FROM ?n WHERE `id`=?s';
        return $this->db->getRow($query, $this->organisations, $id);
    }

    function generateOrganisationGraphId($id)
    {
        $info = $this->getRow($this->organisations, $id);

        return $info['country'] . '.' . $info['city'] . '.' . $info['id'];
    }

    function generateAuthorGraphId($id)
    {
        $info = $this->getRow($this->authors, $id);

        return $info['country'] . '.' . $info['city'] . '.' . $info['id'];
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
}


?>