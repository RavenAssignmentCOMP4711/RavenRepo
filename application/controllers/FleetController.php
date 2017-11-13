<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FleetController extends Application 
{
    /**
     *The FleetController controller is used to load Fleet view, get table data from 
     *Fleet model, and display the Fleet page
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * connect fleet view and load all of fleet infomation for model
     */
    function index() 
    {
        // get current user role
        $role = $this->session->userdata('userrole');
       
        // clear cached adding/editing fleet data
        $this->session->unset_userdata('fleet');

        $this->data['title'] = 'Raven Air Fleet ('. ($role == '' ? ROLE_GUEST : $role) . ')';
        $this->data['pagebody'] = 'fleet/show';
        $fleets = $this->fleets->all();
        $this->load->library('table');

        $this->table->set_heading('Fleet ID', 'Plane ID');
        foreach($fleets as $fleet) 
        {
            // if user is admin, link to edit page, else link to show page
            $url = ($this->is_admin() ? '/fleet/edit/' : '/fleet/') . $fleet->id;
            $show_link_data = array(
                'display' => $fleet->id,
                'url' => $url 
            );
            $show_link = $this->parser->parse('template/_link', $show_link_data, true);

            // only show delete link when user is the admin
            $delete_link_data = array(
                'a_class' => 'btn btn-danger',
                'gly_class' => 'glyphicon glyphicon-trash',
                'url' => '/fleet/delete/'. $fleet->id 
            );
            $delete_link = $this->is_admin() ? $this->parser->parse('template/buttons/glyphbutton', $delete_link_data, true) : '';

            // add a row to the table with the data 
            $this->table->add_row($delete_link . $show_link, $fleet->plane_id);
        }
        $template = array(
            'table_open' => '<table border="1" cellpadding="2" cellspacing="1" class="table">'
        );
        $this->table->set_template($template);
        $this->data['thetable'] = $this->table->generate();

        // only showing the add button when user is the admin 
        $add_button_data = array(
            'a_class' => 'btn btn-success',
            'gly_class' => 'glyphicon glyphicon-plus',
            'url' => '/fleet/add'
        );

        $this->data['nav_link'] = $this->is_admin() ? $this->parser->parse('template/buttons/glyphbutton', $add_button_data, true) : null; 
        $this->render();
    }


    /**
     * Remove a fleet from the database.
     */
    function delete($id) 
    {
        $role = $this->session->userdata('userrole');
        if ($role == ROLE_ADMIN) {
            $fleet = $this->fleets->get($id);  
            if ($fleet != NULL) {
                $this->fleets->delete($id);
            }
        }
        redirect(base_url('/fleet'));
    }

    /**
     * shows the adding form to add new fleet 
     */
    public function add()
    {
        // only admin can access this page
        if (!$this->is_admin()) {
            $this->data['title'] = "Unauthorized access";
            $this->data['pagebody'] = "errors/page403.php";
            $this->render();
        }
        
        $this->load->helper('form');
        $fleet = $this->session->userdata('fleet') !== null ? (Object)($this->session->userdata('fleet')) : $this->fleets->create();

        $this->data['title'] = "Add fleet";
        $this->data['pagebody'] = "fleet/add";

        // loading the adding form
        $this->data['theform'] = $this->generate_form(['fleet' =>$fleet, 'url' => '/fleet/submit_add' ]);

        $this->render();
    }

    /**
     * shows the adding form to add new fleet 
     */
    public function edit($id)
    {
        // only admin can access this page
        if (!$this->is_admin()) {
            $this->data['title'] = "Unauthorized access";
            $this->data['pagebody'] = "errors/page403.php";
            $this->render();
        }


        $this->load->helper('form');

        $fleet = $this->session->userdata('fleet') !== null ? (Object)($this->session->userdata('fleet')) : $this->fleets->get($id);

        $this->data['title'] = "Edit fleet";
        $this->data['pagebody'] = "fleet/edit";
        $this->data['theform'] = $this->generate_form(['fleet' =>$fleet, 'url' => '/fleet/submit_edit' ]);
        $this->render();
    }



    /**
     * when click one of the fleet, the detail infomation will shows up.
     */
    function show($id) 
    {
        //$this->session->unset_userdata('fleet'); // This is required to avoid having data auto filled with old session data...
        $role = $this->session->userdata('userrole');
        $this->data['title'] = 'Raven Air Fleet ('. ($role == '' ? ROLE_GUEST : $role) . ') ';
        $this->data['pagebody'] = 'fleet/show';
        $this->load->library('table');
        $this->data['jsonbutton'] = '';

        $fleet = $this->fleet->get($id);

        foreach ((array)$fleet as $key=>$value)
        {
            $this->table->add_row(
                ucfirst(str_replace(['*', '_'] , ' ', $key)), 
                $value);
        }

        $template = array(
            'table_open' => '<table border="1" cellpadding="2" cellspacing="1" class="table">'
        );
        $this->table->set_template($template);
        $this->data['thetable'] = $this->table->generate();
        $nav_link_data = array(
            'display' => 'Back',
            'url' => '/fleet',
            'classes' => 'btn btn-primary'
        ); 
        $this->data['nav_link'] = $this->parser->parse('template/_link', $nav_link_data, true); 

        $this->render();

    }

    // handle form submission
    public function submit_add()
    {
        // setup for validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules($this->fleets->rules());

        $input = $this->input->post();
        //var_dump($input);
        // retrieve & update data transfer buffer
        $fleet = (array) $this->session->userdata('fleet');
        $fleet = array_merge($fleet, $this->input->post());
        $fleet = (object) $fleet;  // convert back to object
        $this->session->set_userdata('fleet', $fleet);

        // validate away
        if ($this->form_validation->run())
        {
            $this->fleets->add($fleet);

            //echo "data updated";
            //$this->session->unset_userdata('fleet');
            //redirect(base_url('/fleet'));
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
        $this->form_validation->set_rules($this->fleets->rules());

        $input = $this->input->post();
        // retrieve & update data transfer buffer
        $fleet = (array) $this->session->userdata('fleet');
        $fleet = array_merge($fleet, $this->input->post());
        $fleet = (object) $fleet;  // convert back to object
        $this->session->set_userdata('fleet', $fleet);

        // validate away
        if ($this->form_validation->run())
        {
            $this->fleets->update($fleet);
            $this->session->unset_userdata('fleet');
            redirect(base_url('/fleet'));
        } else {
            // Redirect to the form editing
            $this->edit($fleet->id); 
        }

    }

    /**
     * A function to generate the fleet form
     */
    private function generate_form($data)
    {
        $this->load->helper('form');

        $fleet = $data['fleet'];

        $form = form_open($data['url'], ['id' => 'new_fleet_form', 'class' => 'form-horizontal', 'method' => 'post']); 
        $field_block = 'form_components/bs_field_block';
        $field_data = array(
            'form_error' => form_error('id'),
            'the_label' => form_label('ID', 'id', ['class' => 'form-label col-md-2']),
            'the_field' => form_input(['id' => 'id', 'name' => 'id', 'placeholder' => 'ID: Rxxxxx', 'value' => $fleet->id, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);

        $planes = json_decode(file_get_contents('http://wacky.jlparry.com/info/airplanes'));

        $options = array(
            'place_holder' => 'Select a plane'
        );
        foreach ($planes as $plane)
        {
            $options[$plane->id] = $plane->id;
        }

        $selected = isset($fleet->plane_id) ? $fleet->plane_id : null; 

        $field_data = array(
            'form_error' => form_error('plane_id'),
            'the_label' => form_label('Plane id', 'plane_id' ,['class' =>'form-label col-md-2']),
            'the_field' => form_dropdown('plane_id', $options, $selected, ['id' => 'plane_list', 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);

        // add a fieldset tor wrap the fields cannot be modified
        $form .= form_fieldset('Plane details', ['id' => 'plane_details', 'disabled' => '']);

        $fleetEntity = $this->fleet->get($fleet->id);

        $field_data = array(
            'form_error' => form_error('manufacturer'),
            'the_label' => form_label('Manufacturer', 'manufacturer' ,['class' =>'form-label col-md-2']),
            'the_field' => form_input(['id' => 'manufacturer', 'name' => 'manufacturer', 'value' => $fleetEntity->manufacturer, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);
        $field_data = array(
            'form_error' => form_error('model'),
            'the_label' => form_label('Model', 'model', ['class' =>'form-label col-md-2']),
            'the_field' => form_input(['id' => 'model', 'name' => 'model', 'value' => $fleetEntity->model, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);

        $field_data = array(
            'form_error' => form_error('price'),
            'the_label' => form_label('Price', 'price', ['class' =>'form-label col-md-2']),
            'the_field' => form_input(['id' => 'price', 'name' => 'price', 'value' => $fleetEntity->price, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);

        $field_data = array(
            'form_error' => form_error('seats'),
            'the_label' => form_label('Seats', 'seats', ['class' =>'form-label col-md-2']),
            'the_field' => form_input(['id' => 'seats', 'name' => 'seats', 'value' => $fleetEntity->seats, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);

        $field_data = array(
            'form_error' => form_error('reach'),
            'the_label' => form_label('Reach', 'reach', ['class' =>'form-label col-md-2']),
            'the_field' => form_input(['id' => 'reach', 'name' => 'reach', 'value' => $fleetEntity->reach, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);

        $field_data = array(
            'form_error' => form_error('cruise'),
            'the_label' => form_label('Cruise', 'cruise', ['class' =>'form-label col-md-2']),
            'the_field' => form_input(['id' => 'cruise', 'name' => 'cruise', 'value' => $fleetEntity->cruise, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);

        $field_data = array(
            'form_error' => form_error('takeoff'),
            'the_label' => form_label('Takeoff', 'takeoff', ['class' =>'form-label col-md-2']),
            'the_field' => form_input(['id' => 'takeoff', 'name' => 'takeoff', 'value' => $fleetEntity->takeoff, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);

        $field_data = array(
            'form_error' => form_error('hourly'),
            'the_label' => form_label('Hourly', 'hourly', ['class' =>'form-label col-md-2']),
            'the_field' => form_input(['id' => 'hourly', 'name' => 'hourly', 'value' => $fleetEntity->hourly, 'class' => 'form-control'])
        );
        $form .= $this->parser->parse($field_block, $field_data, true);

        // close form_fieldset 
        $form .= form_fieldset_close();

        // form buttons
        $form.= form_submit(null,'submit',['class' => 'btn btn-warning col-md-2 col-md-offset-2']);
        $cancel_button_data = array(
            'classes' => 'btn btn-info col-md-2 col-md-offset-2', 
            'url'=>'/fleet',
            'display'=>'Cancel'
        );
        $form .= $this->parser->parse('template/_link', $cancel_button_data, true);

        // close form
        $form .= form_close();

        return $form;
    }
}
