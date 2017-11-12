<?php
class Fleets extends CSV_Model 
{
    /**
     * Constructor
     */ 
    public function __construct()
    {
        parent::__construct(APPPATH . '../data/fleets.csv', 'id');
    }

    /** 
     * provide form validation rules
     */
    public function rules()
    {
        $config = array(
            ['field' => 'id', 'label' => 'Fleet Id', 'rules' => 'required|alpha_numeric_spaces|max_length[64]'],
            ['field' => 'plane_id', 'label' => 'Plane Id', 'rules' => 'required|alpha_numeric_spaces|max_length[64]'],
        );
        return $config;
    }
}
