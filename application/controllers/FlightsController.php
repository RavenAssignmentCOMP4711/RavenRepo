<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FlightsController extends Application 
{
    /**
    *The FlightsController controller is used to load Flights view, get table data from 
    *Flights model, and display the Flights page
    */

    function __construct()
    {
        parent::__construct();
    }

    /**
    * connect flight view and load all of flights information for model
    */

    function index()
    {
        // This is the view we want shown
        $role = $this->session->userdata('userrole');
        $this->data['title'] = 'Raven Air Flights ('. ($role == '' ? ROLE_GUEST : $role) . ')';
        $this->data['pagebody'] = 'flights';
        
        // Building the list of flights to pass to our view
        $this->session->unset_userdata('flights');
        $flights = $this->flights->all();
        
        $this->load->library('table');
        $this->table->set_heading('Flight ID', 'Fleet',	'Departure Airport', 'Departure Time', 'Arrival Airport', 'Arrival Time');
        
        
        foreach($flights as $flight) 
        {
           
            $url = ($this->is_admin() ? '/flight/edit/' : '/flights/') . $flight->id;
            $show_link_data = array(
                'display' => $flight->id,
                'url' => $url 
            );
            $show_link = $this->parser->parse('template/_link', $show_link_data, true);
            
            $delete_link_data = array(
                'a_class' => 'btn btn-danger',
                'gly_class' => 'glyphicon glyphicon-trash',
                'url' => '/flight/delete/'. $flight->id 
            );
            $delete_link = $this->is_admin() ? $this->parser->parse('template/buttons/glyphbutton', $delete_link_data, true) : '';
            $this->table->add_row($delete_link.$show_link, $flight->fleet_id, $flight->departure_airport_id,$flight->departure_time,$flight->arrival_airport_id, $flight->arrival_time);
            // add a row to the table with the data 
           // $this->table->add_row($delete_link . $show_link, $fleet->plane_id);
        
        }
        $template = array(
            'table_open' => '<table border="1" cellpadding="2" cellspacing="1" class="table">'
        );
        $this->table->set_template($template);
        $this->data['thetable'] = $this->table->generate();
        
        //$this->data['jsonbutton'] = '<a class="btn btn-default" href="/info/flights" target="_blank"> Show JSON </a>';
       
        // only showing the add button when user is the admin 
        $add_button_data = array(
            'a_class' => 'btn btn-success',
            'gly_class' => 'glyphicon glyphicon-plus',
            'url' => '/flight/add'
        );

        $this->data['nav_link'] = $this->is_admin() ? $this->parser->parse('template/buttons/glyphbutton', $add_button_data, true) : null; 
        $this->render();
    }


    public function edit($id)
    {
        // only admin can access this page
        if (!$this->is_admin()) {
            $this->data['title'] = "Unauthorized access";
            $this->data['pagebody'] = "errors/page403.php";
            $this->render();
        }


        $this->load->helper('form');

        $flight = $this->session->userdata('flights') !== null ? (Object)($this->session->userdata('flights')) : $this->flights->get($id);

        $this->data['title'] = "Edit flight";
        $this->data['pagebody'] = "flight/edit";
        $this->data['theform'] = $this->generate_form(['flight' =>$flight, 'url' => '/flight/submit_edit' ]);
        $this->render();
    }

    /**
    * when click one of the flight, the detail information will shows up.
    */
    function show_flights($id) 
    {
        // Geting the particular flight's details to pass to our view
        $flight = $this->flights->get($id);
        
        $role = $this->session->userdata('userrole');
        $this->data['title'] = 'Raven Air Flights ('. ($role == '' ? ROLE_GUEST : $role) . ') ' . $flight->id;
        
        $this->data['pagebody'] = 'flight';

        $this->load->library('table');  
        
       foreach($flight as $key=>$value) 
        {  
           $key = str_replace("_"," ",$key);
            if ($key != 'key'){ // Avoid adding the key name 'key' as a row...
                $this->table->add_row(ucwords($key), $value);
            }
        }
        $template = array(
            'table_open' => '<table border="1" cellpadding="2" cellspacing="1" class="table">'
        );
        $this->table->set_template($template);
        $this->data['thetable'] = $this->table->generate();
        $this->data['jsonbutton'] = '<a class="btn btn-default" href="/info/flights/' . $id . '" target="_blank"> Show JSON </a>';
        $this->render();
        
    }

    function delete($id) 
    {
        $role = $this->session->userdata('userrole');
        if ($role == ROLE_ADMIN) {
            $flight = $this->flights->get($id);  
            if ($flight != NULL) {
                $this->flights->delete($id);
            }
        }
        redirect(base_url('/flights'));
    }

    public function add()
    {
        // only admin can access this page
        if (!$this->is_admin()) {
            $this->data['title'] = "Unauthorized access";
            $this->data['pagebody'] = "errors/page403.php";
            $this->render();
        }
        
        $this->load->helper('form');
        $flight = $this->session->userdata('flights') !== null ? (Object)($this->session->userdata('flights')) : $this->flights->create();

        $this->data['title'] = "Add flight";
        $this->data['pagebody'] = "flight/add";

        // loading the adding form
        $this->data['theform'] = $this->generate_form(['flight' =>$flight, 'url' => '/flight/submit_add' ]);

        $this->render();
    }

     // handle form submission
    public function submit_add()
    {

        // setup for validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules($this->flights->rules());


        $input = $this->input->post();
        //var_dump($input);
        // retrieve & update data transfer buffer
        $flight = (array) $this->session->userdata('flights');
        $flight = array_merge($flight, $this->input->post());
        $flight = (object) $flight;  // convert back to object
        $this->session->set_userdata('flights', $flight);

        // validate away
        if ($this->form_validation->run())
        {
            $this->flights->add($flight);
            $this->index();
        } else {
            //error_log("Validation failed: " . validation_errors());
            $this->add(); 
        }

    }

    /**
     * Accption edit fleet form submission
     */
    public function submit_edit()
    {
        // setup for validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules($this->flights->rules());

        $input = $this->input->post();
        // retrieve & update data transfer buffer
        $flight = (array) $this->session->userdata('flights');
        $flight = array_merge($flight, $this->input->post());
        $this->session->set_userdata('flights', $flight);

        // validate away
        if ($this->form_validation->run())
        {
            $this->flights->update($flight);
            $this->session->unset_userdata('flight');
            redirect(base_url('/flights'));
        } else {
            // Redirect to the form editing
            $this->edit($flights->id); 
        }

    }

     /**
     * A function to generate the fleet form
     */
     private function generate_form($data)
     {
         $this->load->helper('form');
 
         $flight = $data['flight'];
 
         $form = form_open($data['url'], ['id' => 'new_flight_form', 'class' => 'form-horizontal', 'method' => 'post']); 
         $field_block = 'form_components/bs_field_block';
         $field_data = array(
             'form_error' => form_error('id'),
             'the_label' => form_label('ID', 'id', ['class' => 'form-label col-md-2']),
             'the_field' => form_input(['id' => 'id', 'name' => 'id', 'placeholder' => 'ID: RIxxx', 'value' => $flight->id, 'class' => 'form-control'])
         );
         $form .= $this->parser->parse($field_block, $field_data, true);
 
         $planes = $this->fleets->all();
         $airports = $this->airports->all();

         //choose a flight
         $options = array(
             'place_holder' => 'Select a plane'
         );
         foreach ($planes as $plane)
         {
             $options[$plane->id] = $plane->id;
         }

         $selected = isset($flight->fleet_id) ? $flight->fleet_id : null; 
 
         $field_data = array(
             'form_error' => form_error('fleet_id'),
             'the_label' => form_label('Plane id', 'fleet_id' ,['class' =>'form-label col-md-2']),
             'the_field' => form_dropdown('fleet_id', $options,$selected,['id' => 'plane_list', 'class' => 'form-control'])
         );

         $form .= $this->parser->parse($field_block, $field_data, true);

         //choose a departure airport
         $options2 = array(
            'place_holder' => 'Select a departure airport'
        );

        foreach ($airports as $airport)
        {
            $options2[$airport->id] = $airport->id;
        }

        $selected2 = isset($flight->departure_airport_id) ? $flight->departure_airport_id : null; 
        
        $field_data = array(
            'form_error' => form_error('departure_airport_id'),
            'the_label' => form_label('departure airport', 'departure_airport_id' ,['class' =>'form-label col-md-2']),
            'the_field' => form_dropdown('departure_airport_id',$options2,$selected2,['id' => 'departure_airports_list', 'class' => 'form-control'])
        );
       
        $form .= $this->parser->parse($field_block, $field_data, true);
       
        //add departure_time
        $field_data = array(
            'form_error' => form_error('departure_time'),
            'the_label' => form_label('departure time', 'departure_time', ['class' => 'form-label col-md-2']),
            'the_field' => form_input(['id' => 'departure_time', 'name' => 'departure_time', 'type' => 'time', 'placeholder' => 'hh:mm', 'value' => $flight->departure_time, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);



         //choose a arrive airport
         $options3 = array(
            'place_holder' => 'Select an arrival airport'
        );

        foreach ($airports as $airport)
        {
            $options3[$airport->id] = $airport->id;
        }

        $selected3 = isset($flight->arrival_airport_id) ? $flight->arrival_airport_id : null; 
        
        $field_data = array(
            'form_error' => form_error('arrival_airport_id'),
            'the_label' => form_label('arrive airport', 'arrival_airport_id' ,['class' =>'form-label col-md-2']),
            'the_field' => form_dropdown('arrival_airport_id',$options3,$selected3,['id' => 'arrive_airports_list', 'class' => 'form-control'])
        );
       
        $form .= $this->parser->parse($field_block, $field_data, true);
       
        //add arrive_time
        $field_data = array(
            'form_error' => form_error('arrival_time'),
            'the_label' => form_label('arrival time', 'arrival_time', ['class' => 'form-label col-md-2']),
            'the_field' => form_input(['id' => 'arrival_time', 'name' => 'arrival_time', 'type' => 'time', 'placeholder' => 'hh:mm', 'value' => $flight->arrival_time, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);


         
          // form buttons
          //submit
        $form.= form_submit(null,'submit',['class' => 'btn btn-warning col-md-2 col-md-offset-2']);

        $cancel_button_data = array(
            'classes' => 'btn btn-info col-md-2 col-md-offset-2', 
            'url'=>'/flights',
            'display'=>'Cancel'
        );
        $form .= $this->parser->parse('template/_link', $cancel_button_data, true);


         // close form
         $form .= form_close();
 
         return $form;
     }

}
