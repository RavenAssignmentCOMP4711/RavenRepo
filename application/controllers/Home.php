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
        $this->load->helper('form');
        $this->load->library('table');
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
         $this->data['airport_list'] = ''. $counter . " airports in operation";

        // flight booking
        //$this->load->model('airports');
        //$this->load->model('flights');
        //$this->data['airports'] = $this->airports->all();
        //$this->data['flights'] = $this->flights->all();


        // show error messages
        
        $error_message = validation_errors(); 
        $this->data['error_message'] = $error_message;    

        // show search form
        $search_for = array(
            'from' => '',
            'to'   => '',
            'departure_date' => '',
            'departure_time' => '',
            'time_range' => '30'
        ); 

        //var_dump($this->session->userdata['search_result']);
        
        $this->data['search_result'] = '';
        $search_result = empty($this->session->userdata['search_result']) ? null : $this->session->userdata['search_result'];
        //var_dump($search_result);
        if ($search_result != null) {
        $this->table->set_heading('#', 'Sumary', 'Details');

        // Show the search result in table
        $num = 1;
        foreach ($search_result as $record) 
        {
            foreach ($record as $segment)
            {
                $sumary = '';
                $details = '';
                $flight;
                foreach ($segment as $flight_id)
                {
                    //$this->data['search_result'] .= ('<div>' . $flight_id . '</div>');
                    $flight = $this->flights->get($flight_id); 
                    $sumary .= ($flight->departure_airport_id . '-->');
                    $params = array(
                        'flight_id' => $flight->id,
                        'departure_airport' => $flight->departure_airport_id,
                        'arrival_airport' => $flight->arrival_airport_id,
                        'departure_time' => $flight->departure_time,
                        'arrival_time' => $flight->arrival_time
                    );
                    $details .= $this->parser->parse('/template/flight_widget',$params,true);
                }
                $sumary .= $flight->arrival_airport_id;
                $this->table->add_row($num++, $sumary, $details);
            }

        }
        $template = array(
            'table_open' => '<table border="1" class="table">'
        );
        $this->table->set_template($template);
        $this->data['search_result'] .= $this->table->generate();

        } 

        //var_dump($this->session->userdata('search_for'));
        $search_for =empty($this->session->userdata('search_for')) ? $search_for :  array_merge($search_for, $this->session->userdata('search_for'));

        $this->session->set_userdata('search_for', $search_for);

        $this->data['error_message'] = validation_errors('<div class="error">', '</div>');

        $search_for = (Object)$search_for;

        $form = form_open('/home/search', array('id' => 'search_form', 'class' => 'form-horizontal', 'method' => 'post'));

        $field_block = 'form_components/bs_inline_field_block';

        $airports = $this->airports->all();

        $options = array(
            '--" disabled="disabled' => '** Please departure airport**',
        );
        foreach ($airports as $airport)
        {
            $options[$airport->id] = $airport->id;
        }

        $selected = $search_for->from; 

        // Departure airport
        $field_data = array(
            'block_class' => 'col-md-6',
            'field_class' => 'col-md-12', 
            'the_label' => form_label('From', 'from' ,['class' =>'form-label col-md-4']),
            'the_field' => form_dropdown('from', $options, $selected, array('id' => 'from', 'class' => 'form-control'))
        );

        $form .= $this->parser->parse($field_block, $field_data, true);

        // Destination 
        $selected = $search_for->to; 
        $options['--" disabled="disabled'] = '** Please destination airport**';
        $field_data = array(
            'block_class' => 'col-md-6',
            'field_class' => 'col-md-12', 
            'the_label' => form_label('To', 'to', ['class' =>'form-label col-md-4']),
            'the_field' => form_dropdown('to', $options, $selected, array('id' => 'to', 'class' => 'form-control col-md-12'))
        );

        $form .= $this->parser->parse($field_block, $field_data, true);

        // Departure date 
        $field_data = array(
            'block_class' => 'col-md-6',
            'field_class' => 'col-md-12', 
            'the_label' => form_label('Departure date', 'departure_date', array('class' =>'form-label col-md-12')),
            'the_field' => form_input(array(
                'id' => 'departure_date', 
                'name' => 'departure_date', 
                'type' => 'date', 
                'value' => $search_for->departure_date,
                'class' => 'form-control'))
        );

        $form .= $this->parser->parse($field_block, $field_data, true);

        // Departure time
        $field_data = array(
            'block_class' => 'col-md-3',
            'field_class' => 'col-md-12', 
            'the_label' => form_label('time', 'departure_time', array('class' =>'form-label col-md-12')),
            'the_field' => form_input(array(
                'id' => 'departure_time', 
                'name' => 'departure_time', 
                'type' => 'time', 
                'value' => $search_for->departure_time, 
                'class' => 'form-control'))
        );

        $form .= $this->parser->parse($field_block, $field_data, true);

        // Search range
        $options = array(
            'placeholder' => 'Result within',
            '15' => '15 minutes',
            '30' => '30 minutes',
            '45' => '45 minutes',
            '60' => 'one hour',
        );

        $selected = $search_for->time_range;

        $field_data = array(
            'block_class' => 'col-md-3',
            'field_class' => 'col-md-12', 
            'the_label' => form_label('Time range within', 'time_range', array('class' =>'form-label col-md-12')),
            'the_field' => form_dropdown('time_range', $options, $selected, array(
                'id' => 'time_range', 
                'class' => 'form-control'))
        );

        $form .= $this->parser->parse($field_block, $field_data, true);

        $form .= form_submit(null, 'Search', ['class' =>'btn btn-success col-md-3']); 
        $form .= form_close();
        $this->data['search_form'] = $form;



        // render the page
        $this->render();
        
    }


    public function search()
    {
        $this->load->library('form_validation');

        $rules = array(
            ['field' => 'from', 'label' => 'From', 'rules' => 'required'],
            ['field' => 'to', 'label' => 'To', 'rules' => 'required'],
            ['field' => 'departure_date', 'label' => 'Departure date', 'rules' => 'required'],
        );

        $this->form_validation->set_rules($rules);

        $post = $this->input->post();
        //var_dump($post);

        $search_for = empty($this->session->userdata('search_for')) ? array() : (array)($this->session->userdata('search_for'));

        $search_for = (Object)array_merge($search_for, $post);

        // save searching condition back to session data 
        $this->session->set_userdata('search_for', (array)$search_for);

        //var_dump($post);
        if (!$this->form_validation->run())
            return $this->index();

        $airports = $this->airports->all();
        //var_dump($airports);

        $graph = array();

        $visited = array();
        foreach ($airports as $row)
        {
            foreach ($airports as $col)
                $graph[$row->id][$col->id] = $row->id == $col->id ? 1 : null;
            $visited[$row->id] = false;
        } 

        $flights = $this->flights->all();
        foreach ($flights as $flight) {
            $row = $flight->departure_airport_id;
            $col = $flight->arrival_airport_id;
            $graph[$row][$col] []= $flight->id;
        }

        //var_dump($graph);


        $path = array();
        find_paths($graph, $search_for->from, $search_for->to, $visited, $path);

        //var_dump($path);

        $options = array();
        $temp = array();
        $count = 0;

        // revert the order from path and get flights info back from graph 
        for ($i = sizeof($path) -1; $i >= 0; --$i)
        {
            //array_push($temp, $path[$i]); 
            if ($path[$i] != $search_for->to) 
            {
                array_push($temp, $graph[$path[$i]][$path[$i-1]]);
                continue;
            } 
            $options[$count++] = $temp;
            $temp = array();
        }

        
        //var_dump($options);

        // expands the result if multipy flights exist between two airport  
        $results = array();
        $count = 0; 
        foreach ($options as $option)
        {
            for ($i = 0; $i < sizeof($option); ++$i) 
            {
                $result = join_array($result, $option[$i]); 
            } 
            $results[$count++] = $result;
        }
     //   var_dump($results);

        $this->session->set_userdata('search_result', $results);
        return $this->index();

    }

//    public function searchFlights()
//    {
//        $data = $this->input->post();
//        $departure = $data['departureAirport'];
//        $arrival = $data['destinationAirport'];
//        $this->load->model('flights');
//        $flights = $this->flights->toArray();
//        $matches = array();
//
//        foreach($flights as $flight) {
//
//            if($flight['departure_airport_id'] == $departure) {
//                if($flight['arrival_airport_id'] == $arrival) {
//                    array_push($matches, array($flight)); //1
//                } else {
//                    foreach($flights as $flight2) {
//                        if($flight2['departure_airport_id'] == $flight['arrival_airport_id']) {
//                            if($flight2['arrival_airport_id'] == $arrival) {
//                                array_push($matches, array($flight, $flight2)); //2
//                            } else {
//                                foreach($flights as $flight3) {
//                                    if($flight3['departure_airport_id'] == $flight2['arrival_airport_id']) {
//                                        if($flight3['arrival_airport_id'] == $arrival) {
//                                            array_push($matches, array($flight, $flight2, $flight3)); //3
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }
//        header('Content-Type: application/json');
//        echo json_encode($matches);
//    }
//
}

function is_adjacent(&$g, $u, $v)
{
    return $u != $v && $g[$u][$v] != null;
}

function get_all_adjacent(&$g, $u) {
    $adjacent = array();

    foreach ($g[$u] as $key => $value) 
        if ($u != $key && $g[$u][$key] != null)
           array_push($adjacent, $key); 
    return $adjacent;
}

function find_paths($g, $u, $v, &$visited, &$path)
{
    $direct = false;
    $indirect = false;
    $visited[$u] = true;
    if ($u == $v)
        return false;
    foreach(get_all_adjacent($g, $u) as $neighbour) {
        if (!$visited[$neighbour] && find_paths($g, $neighbour, $v, $visited, $path)) 
        {
            array_push($path, $u);
            $indirect = true;
        }
        if ($neighbour == $v ) { 
            array_push($path, $v);
            array_push($path, $u);
            $direct = true;
        }
    }

    return $direct || $indirect;
}

function join_array(&$arr1, &$arr2) {
    $result = array();
    $size1 = sizeof($arr1);
    $size2 = sizeof($arr2);

    if ($size1 == 0) { 
        for ($i = 0; $i < $size2; ++$i)
        {
        $result[$i] = (array)$arr2[$i];
        }
    }

    for ($i = 0; $i < $size1 * $size2; ++$i) 
    {
        $temp = array(); 
        $temp = array_merge($temp, $arr1[$i % $size1]); 
        array_push($temp, $arr2[$i % $size2]); 
        $result[$i] = $temp;
    }

    //var_dump($result);
    return $result;
} 
