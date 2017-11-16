<?php
defined('BASEPATH') OR exit('No direct script access allowed');
defined('SECONDES_IN_MINUTE') OR define('SECONDES_IN_MINUTE', 60);
defined('SAFE_TRANSFER_TIME') OR define('SAFE_TRANSFER_TIME', 90);

class FlightsFinder 
{
    private $CI;
    private $map;
    private $visited;
    private $paths;

    public function __construct()
    {
        //Get CI for later access to other models
        $this->CI = &get_instance();

        $airports = $this->CI->airports->all();
        $flights = $this->CI->flights->all();
        $graph = array();
        foreach ($airports as $row)
        {
            foreach ($airports as $col)
                $graph[$row->id][$col->id] = $row->id == $col->id ? 1 : null;
        } 
        foreach ($flights as $flight) {
            $row = $flight->departure_airport_id;
            $col = $flight->arrival_airport_id;
            $graph[$row][$col] []= $flight->id;
        }
        $this->map = $graph;
    }

    public function search($from, $to, $conditions)
    {
        $this->paths = array();
        
        $history = array();

        $this->find_paths_($from, $to, $conditions, $history);

        /*
        foreach ($this->paths as $flight) {
            var_dump($flight);
        }
         */

        return $this->paths;
    }

    private function find_paths_($u, $v, $conditions, $history)
    {
        
        // reach the end of the route
        if ($u == $v) {
            array_push($this->paths, $history);
            return;
        }

        // gete route history
        $airport_history = array();
        
        foreach ($history as $flight) {
            array_push($airport_history, $flight->departure_airport_id);
            array_push($airport_history, $flight->arrival_airport_id);
        }


        // if neighbour is not in the history, recursively call find_paths_ 
        foreach($this->get_adjacent_($u) as $neighbour)
        {
            // skip the neighbour if already in history (visited)
            if (in_array($neighbour, $airport_history))
                continue;

            // send the request all the available neighbour 
            foreach ($this->map[$u][$neighbour] as $id)
            {
                $flight = $this->CI->flights->get($id);

                // skip the flight if not meeting the requirement
                if (!$this->check_($id, $conditions))
                    continue;

                // make a copy of current history
                $updated_history = $history;

                // put the flight into the updated_history
                array_push($updated_history, $flight);
                
                // update the requirement for next candidate
                $new_conditions = array(
                    'departure_time' => (Object) array(
                        'what' => 'departure_time',
                        'expr' => '<=',
                        'departure_date' => $conditions['departure_time']->departure_date,
                        'departure_time' => $flight->arrival_time, 
                    )
                );

                // send the message for next node 
                $this->find_paths_($neighbour, $v, $new_conditions, $updated_history);
            }
        }
    }

    private function get_adjacent_($u)
    {
        $g = $this->map;
        $adjacent = array();
        foreach ($g[$u] as $key => $value) 
            if ($u != $key && $g[$u][$key] != null)
                array_push($adjacent, $key); 
        return $adjacent;
    }

    /*
    private function filter_($flight_ids, &$conditions) 
    {
       $passed = array(); 
       foreach ($flight_ids as $id) 
       {
           if ($this->check_($id, &$conditions)
              array_push($passed, $flight); 
       }
       return $passed;
    }
     */

    private function check_($flight_id, &$conditions) 
    {
        $flight = $this->CI->flights->get($flight_id);
        if ($flight == null)
            return false;
        foreach ($conditions as $condition)
        {
            switch ($condition->what)
            {
            case 'departure_time':
                $day = $condition->departure_date;
                $offer_time = strtotime($day . $flight->departure_time);
                $required_time = strtotime($day. $condition->departure_time); 
                if ($condition->expr == 'range')
                    return abs($offer_time - $required_time) > $condition->range; 

                if ($offer_time < $required_time)
                    return false;
                break;
            default:
                break;
            } 
        }
        return true;
    }
}

