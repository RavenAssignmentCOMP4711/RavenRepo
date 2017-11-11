<?php
class Fleet extends Entity {
    protected $id;
    protected $plane_id;
    protected $manufacturer;
    protected $model;
    protected $price;
    protected $seats;
    protected $reach;
    protected $cruise;
    protected $takeoff;
    protected $hourly;

    public function get($id = null) 
    {

        if ($id == null)
            return $this;

        $record = $this->loadCollectionModel('fleets')->get($id);

        if ($record == null)
            return $this; 


        $plane = json_decode(file_get_contents('http://wacky.jlparry.com/info/airplanes/'. $record->plane_id));
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
        // Fleet id cannot be modified, delete and add a new one instead
        return false;
    }

    public function setPlaneid($plane_id)
    {
        // validate the parament before accepting it.
        // if no validation needed, just delete this function
        $this->plane_id = $plane_id;
    }

    public function setModel($model) 
    {
        $this->model = $model;
    }

    public function setPrice($price) 
    {
        // validate the parament before accepting it.
        // if no validation needed, just delete this function
        $this->price = $price;
    }

    public function setSeats($seats) 
    {
        // validate the parament before accepting it.
        // if no validation needed, just delete this function
        $this->seats = $seats;
    }

    public function setReach($reach) 
    {
        // validate the parament before accepting it.
        // if no validation needed, just delete this function
        $this->reach = $reach;
    }

    public function setCruise($cruise) 
    {
        // validate the parament before accepting it.
        // if no validation needed, just delete this function
        $this->cruise = $cruise;
    }
    public function setTakeoff($takeoff) 
    {
    }
    public function setHourly($hourly) 
    {
    }
}
