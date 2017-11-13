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

    public function get($id = null) {

        if ($id == null)
            return $this;

        $record = $this->loadCollectionModel('fleets')->get($id);

        if ($record == null)
            return $this;


        $plane = json_decode(file_get_contents('http://wacky.jlparry.com/info/airplanes/' . $record->plane_id));
        if ($plane == null)
            return $this;

        foreach ($plane as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    public function setId($id) {
        // Fleet id cannot be modified, delete and add a new one instead
        return false;
    }

    public function setPlaneid($plane_id) {
        // validate the parament before accepting it.
        // if no validation needed, just delete this function
        $plane = json_decode(file_get_contents('http://wacky.jlparry.com/info/airplanes/' . $plane_id));
        if ($plane == null)
            return false;
        $this->plane_id = $plane_id;

        // also update other properties 
        foreach ($plane as $key => $value) {
            $this->$key = $value;
        }

        return true;
    }

    public function setModel($model) {
        // this property cannot be directly changed.
        return false;
    }

    public function setPrice($price) {
        // this property cannot be directly changed.
        return false;
    }

    public function setSeats($seats) {
        // this property cannot be directly changed.
        return false;
    }

    public function setReach($reach) {
        // this property cannot be directly changed.
        return false;
    }

    public function setCruise($cruise) {
        // this property cannot be directly changed.
        return false;
    }

    public function setTakeoff($takeoff) {
        // this property cannot be directly changed.
        return false;
    }

    public function setHourly($hourly) {
        // this property cannot be directly changed.
        return false;
    }

}
