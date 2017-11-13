<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Application
{
    function __construct()
    {
        parent::__construct();
    }

    
    /**
    *The home controller is used to load home view
    *and display the home page
    */
    function index() 
    {                               
        $this->data['pagebody'] = 'home';
        $role = $this->session->userdata('userrole');
        $this->data['title'] = 'Raven Airline ('. ($role == '' ? ROLE_GUEST : $role) . ')';
        $this->data['fleet_count'] = $this->fleets->size();
        $this->data['flight_count'] = $this->flights->size();
        $this->data['airport_count'] = $this->airports->size();
        
        $airports = $this->airports->toArray();
        $airport_list = '';
        $counter = 0;
        foreach($airports as $port) 
        {  if($port['id'] == "YXS")
           $airport_list = $airport_list . '<b>'.$port['id'] . 
                " - Base Airport" .'</b><br>';
            else 
                $airport_list = $airport_list . $port['id'] . 
                    " - Destination " .$counter.'<br>';
            $counter++;
        }
         $this->data['airport_list'] = $airport_list;

         // flight booking
        $this->load->model('airports');
        $this->load->model('flights');
        $this->data['airports'] = $this->airports->all();
        $this->data['flights'] = $this->flights->all();
        
        $this->render();
    }

    public function searchFlights()
    {
        $data = $this->input->post();
        $departure = $data['departureAirport'];
        $arrival = $data['destinationAirport'];
        $this->load->model('flights');
        $flights = $this->flights->toArray();
        $matches = array();

        foreach($flights as $flight) {

            if($flight['departure_airport_id'] == $departure) {
                if($flight['arrival_airport_id'] == $arrival) {
                    array_push($matches, array($flight)); //1
                } else {
                    foreach($flights as $flight2) {
                        if($flight2['departure_airport_id'] == $flight['arrival_airport_id']) {
                            if($flight2['arrival_airport_id'] == $arrival) {
                                array_push($matches, array($flight, $flight2)); //2
                            } else {
                                foreach($flights as $flight3) {
                                    if($flight3['departure_airport_id'] == $flight2['arrival_airport_id']) {
                                        if($flight3['arrival_airport_id'] == $arrival) {
                                            array_push($matches, array($flight, $flight2, $flight3)); //3
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($matches);
    }
    
    function show_404(){
        $this->load->view("/errors/cli/error_404");
    }
}
