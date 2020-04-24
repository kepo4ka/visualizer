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
    var $length;


    function __construct($length = 0)
    {
        global $db;
        $this->db = $db;
        $this->length = $length;
    }


    function getAirports()
    {
        $query = "select id, name, city_id, city_name from airports";

        if (!empty($this->length)) {
            $query .= " LIMIT {$this->length}";
        }


        $airports = $this->db->getAll($query);


        foreach ($airports as &$airport) {
            $airport['title'] = $airport['name'];

            $airport['name'] = $airport['id'];

            $query = "select countries.restriction_type, countries.name, countries.continent FROM cities, countries, airports
            where airports.id={$airport['id']} AND 
            airports.city_id=cities.id AND
            cities.country_iso=countries.iso
            AND 1";

            $airport['country'] = $this->db->getRow($query);
            $airport['restriction_type'] = $airport['country']['restriction_type'];

            $airport['title1'] = $airport['city_name'];

            $airport['rubric'] = "{$airport['country']['continent']}.{$airport['country']['name']}.{$airport['city_name']}";
//            $airport['rubric_md5'] = ' .' . splitMd5($airport['rubric']);
//            $airport['name'] = $airport['rubric_md5'] . $airport['name'];

            $query = "select `air_from` from {$this->destinations} WHERE air_to={$airport['id']}";
            $airport['destinations'] = $this->db->getCol($query);
            $airport['imports'] = $airport['destinations'];
            unset($airport['destinations']);
            unset($airport['country']);
        }
        unset($airport);


        $airports = clearEmptyReferences($airports, PRIMARY_FIELD, REFEREFCES_FIELD);

        foreach ($airports as &$airport) {
            $airport['rubric_md5'] = ' .' . splitMd5($airport['rubric']);
            $airport['name'] = $airport['rubric_md5'] . $airport['name'];

            foreach ($airport['imports'] as &$destination) {

                $query = "select countries.name as country_name, cities.name as city_name, countries.continent FROM cities, countries, airports
                    where airports.id={$destination} AND 
                    airports.city_id=cities.id AND
                    cities.country_iso=countries.iso";

//                $query .= " AND countries.restriction_type='non-global_restriction'";


                $country_info = $this->db->getRow($query);

                if (empty($country_info)) {
                    unset($destination);
                    continue;
                }

                $rubric = "{$country_info['continent']}.{$country_info['country_name']}.{$country_info['city_name']}";
                $rubric_md5 = ' .' . splitMd5($rubric);
                $destination = $rubric_md5 . $destination;
            }
            unset($destination);
//            unset($airport['city_name']);
//            unset($airport['city_id']);
        }
        unset($airport);

        foreach ($airports as $airport) {
            if (empty($airport['imports'])) {
                $airport['imports'][] = $airport['name'];
            }
        }
        return $airports;
    }


    function getAirportFullInfo($id)
    {
        $query = "select 
        airports.name as airport_name,
        cities.name as city_name, 
        cities.cpi as city_cpi,
        cities.safety as city_safety,
        cities.timezone as city_timezone,
        cities.population as city_population,
        cities.population as city_population,
        countries.name as country_name,
        countries.continent as country_continent,
        countries.continent as country_continent,
        countries.alternate_names as country_alter_name,
        countries.restriction_type,
        countries.restriction_text,
        countries.restriction_update_time,
        countries.phone,
        countries.capital,
        countries.language,
        countries.language1,
        countries.language2,
        countries.population as country_population,
        countries.population as country_population,
        countries.currency_code as country_currency,
        countries.currency_name as country_currency_name,
        countries.area as country_area
        from countries, airports, cities
        where airports.id=$id
        and airports.city_id=cities.id AND 
        cities.country_iso = countries.iso
        ";


        $airport = $this->db->getRow($query);

        $query = "select airports.name, airports.city_name from destinations, airports WHERE air_to={$id} AND destinations.air_from=airports.id";
        $airport['destinations'] = $this->db->getAll($query);

        return $airport;
    }


}


?>