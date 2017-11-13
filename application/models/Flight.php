<?php
class Flight extends Entity {
    protected $id;
    protected $fleet_id;
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

    public function setFleetid($fleet_id)
    {
        // only existing fleet can be used to schedule  a flight
        $record = $this->loadCollectionModel('fleets')->get($fleet_id);
        if ($record == null)
            return false;

        $this->fleet_id = $fleet_id;
        return true;
    }

    public function setDepartureairportid($airport_id) 
    {
        $record = $this->loadCollectionModel('airports')->get($airport_id);
        if ($record == null)
            return;

        $this->departure_airport_id = $airport_id;
        return true;
    }

    public function setDeparturetime($time) 
    {
        $day = "2017-01-01";
        $timelimit = "08:00";

        if (strtotime($day . $time ) < strtotime($day . $timelimit))
            return false;

        $this->departure_time = $time;
        return true;
    }


    public function setArrivaltime($time) 
    {
        $day = "2017-01-01";
        $timelimit = "22:00";

        if (strtotime($day . $time ) > strtotime($day . $timelimit))
            return false;

        $this->arrival_time = $time;
        return true;
    }

    public function setArrivalairportid($airport_id) 
    {
        $record = $this->loadCollectionModel('airports')->get($airport_id);
        if ($record == null)
            return;
        $this->arrival_airport_id = $airport_id;
        return true;
    }

    /*
    public function setPassengernum($num) 
    {
        $this->passenger_num = $num;
    }

    public function setAirhostnessnum($num) 
    {
        $this->airhostness_num = $num;
    }

    public function setAvailableseatnum($num) 
    {
        $this->available_seat_num = $num;
    }

    public function reduce_available_seat_num() 
    {
        $availble_seat_num--;
    }
     */

}
