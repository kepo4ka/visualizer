<?php
require_once __DIR__ . '/../init.php';

class CovidDB
{
    var $db;

    var $countries = 'countries';
    var $cities = 'cities';
    var $airports = 'airports';
    var $destinations = 'destinations';
    var $visa = 'visa';
    var $covid = 'covid';
    var $length = 100;


    function __construct($length = 100)
    {
        global $db;
        $this->db = $db;
        $this->length = $length;
    }


    function getAirports()
    {

        $query = "select id, name, city_id, city_name from airports LIMIT {$this->length}";
        $airports = $this->db->getAll($query);


        foreach ($airports as &$airport) {
            $airport['title'] = $airport['name'];
            $airport['title1'] = $airport['city_name'];

            $query = "select countries.name, countries.continent FROM cities, countries, airports
            where airports.id={$airport['id']} AND 
            airports.city_id=cities.id AND
            cities.country_iso=countries.iso";

            $airport['country'] = $this->db->getRow($query);

            if (empty($airport['country']))
            {
                \Helper\Helper::echoVarDumpPre($query);
            }

            $airport['rubric'] = "{$airport['country']['continent']}.{$airport['country']['name']}.{$airport['city_name']}";
//            $airport['rubric_md5'] = ' .' . splitMd5($airport['rubric']);
//            $airport['name'] = $airport['rubric_md5'] . $airport['name'];

            $query = "select `air_to` from {$this->destinations} WHERE air_from={$airport['id']}";
            $airport['destinations'] = $this->db->getCol($query);
            $airport['imports'] = $airport['destinations'];
        }
        unset($airport);

        $airports = clearEmptyReferences($airports, PRIMARY_FIELD, REFEREFCES_FIELD);



        return $airports;


    }


}


?>