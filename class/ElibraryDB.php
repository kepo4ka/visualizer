<?php


class ElibraryDB
{
    var $db;
    var $organisations = 'organisations';
    var $publications = 'publications';
    var $authors = 'authors';
    var $authors_to_publications = 'authors_to_publications';


    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function getAllOrganisations()
    {
        $query = 'SELECT * FROM ?n';
        return $this->db->getAll($query, $this->organisations);
    }

    function getAllOrganisationsIds()
    {
        $query = 'SELECT `id` FROM ?n';
        return $this->db->getCol($query, $this->organisations);
    }


    function getOrganisationRelOrganisations($id = 5051)
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


}


?>