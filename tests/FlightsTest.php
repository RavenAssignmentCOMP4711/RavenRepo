
<?php
use PHPUnit\Framework\TestCase;

class FlighttsTest extends TestCase
{
    private $CI;
    public function setUp()
    {
        $this->CI=&get_instance();
    }

    /**
     * @dataProvider addFlightsDataProvider
     */
    public function testAddFlights($id, $fleet_id, $departure_airport_id, $arrival_airport_id, $departure_time, $arrival_time, $expected)
    {
        $fleet_record = array(
            'id' => $fleet_id,
            'plane_id' => 'baron'
        ); 
        $this->CI->fleets->add((Object)$fleet_record);
        //var_dump($this->CI->fleets->get($fleet_id));
        
        $flight_record = array(
            'id' => $id,
            'fleet_id' => $fleet_id,
            'departure_airport_id' => $departure_airport_id,
            'arrival_airport_id' => $arrival_airport_id,
            'departure_time' => $departure_time,
            'arrival_time' => $arrival_time
        );
        $this->CI->flights->add((Object)$flight_record);

        $result = $this->CI->flights->get($id);

        $this->assertEquals($expected, $result != null && $result->id == $id);

        $this->CI->flights->delete($id);
        $this->CI->fleets->delete($fleet_id);
    }

    public  function addFlightsDataProvider()
    {
        $data = json_decode(file_get_contents("data/flights_crud_test.json"),true);
        $testData = array();
        foreach ($data as $record)
        {
            $testData[$record['case']] = array_slice($record, 1);
        }
        return $testData;
    }
} 
