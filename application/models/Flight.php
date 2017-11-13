<?php
class Flight extends Entity {
    protected $id;
    protected $plane_id;
    protected $departure_airport_id;
    protected $departure_time;
    protected $arrival_airport_id;
    protected $arrival_time;
    protected $passenger_num;
    protected $availble_seat_num;
    protected $airhostness_num;

    public function get($id = null) 
    {

        if ($id == null)
            return $this;

        $record = $this->loadCollectionModel('flights')->get($id);

        if ($record == null)
            return $this; 


        $plane = $this->flights->all();
        if ($plane == null)
            return $this;

        foreach ($plane as $key =>$value)
        {
            $this->$key = $value;
        }

        return $this;
    }

    public function setId($id)
    {
        // Flight id cannot be modified, delete and add a new one instead
        return false;
    }

    public function setPlaneid($plane_id)
    {
        // validate the parament before accepting it.
        // if no validation needed, just delete this function
        $this->plane_id = $plane_id;
    }

    public function set_departure_airport_id($airport_id) 
    {
        $this->departure_airport_id = $airport_id;
    }

    public function set_departure_time($time) 
    {
        $this->departure_time = $time;
    }


    public function set_arrival_time($time) 
    {
        $this->arrival_time = $time;
    }

    public function set_arrival_airport_id($airport_id) 
    {
        $this->arrival_airport_id = $airport_id;
    }

    public function set_passenger_num($num) 
    {
        $this->passenger_num = $num;
    }

    public function set_airhostness_num($num) 
    {
        $this->airhostness_num = $num;
    }

    public function set_available_seat_num($num) 
    {
        $this->available_seat_num = $num;
    }

    public function reduce_available_seat_num() 
    {
        $availble_seat_num--;
    }



}
