<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Flights extends CSV_Model
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(APPPATH . '../data/flights.csv', 'id');
    }

    public function rules()
    {
        $config = array(
            ['field' => 'id', 'label' => 'Fleet Id', 'rules' => 'required|alpha_numeric_spaces|max_length[64]'],
            ['field' => 'fleet_id', 'label' => 'Plane Id', 'rules' => 'required|alpha_numeric_spaces|max_length[64]'],
            
        );
        return $config;
    }

    public function add($record) {
        $CI = & get_instance(); 

        if (strtolower(substr($record->id, 0, 1)) != 'r')
            return false;

        //var_dump($record->id);
        // check if fleet id valid
        if ($CI->fleets->get($record->fleet_id) == null) 
            return false;

        //var_dump($record->fleet_id);
        
        // check if fleet id valid
        if ($CI->airports->get($record->departure_airport_id) == null) 
            return false;

        //var_dump($record->departure_airport_id);
        if ($CI->airports->get($record->arrival_airport_id) == null) 
            return false;

        //var_dump($record->arrival_airport_id);

        $day = "2017-01-01";
        $departure_limit = "08:00";

        if (strtotime($day . $record->departure_time) < strtotime($day . $departure_limit)) 
            return false;

        //var_dump($record->departure_time);
        $arrival_limit = "22:00";
        if (strtotime($day . $record->arrival_time) > strtotime($day . $arrival_limit)) 
            return false;

        //var_dump($record->arrival_time);
        parent::add($record);
        return true;
    }
}
