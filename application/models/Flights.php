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

    // TODO: add validation rules here !!!
    public function rules()
    {
        $config = array(
            ['field' => 'id', 'label' => 'Fleet Id', 'rules' => 'required|alpha_numeric_spaces|max_length[64]'],
            ['field' => 'plane_id', 'label' => 'Plane Id', 'rules' => 'required|alpha_numeric_spaces|max_length[64]'],
        );
        return $config;
    }
}
