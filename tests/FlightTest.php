<?php
use PHPUnit\Framework\TestCase;

class FlightTest extends TestCase
{
    private $CI;
    public function setUp()
    {
        $this->CI=&get_instance();
    }

    /**
     * @dataProvider setterDataProvider
     */
    public function testSetters($property, $input, $expected)
    {
        $flight = $this->CI->flight;
        //var_dump($flight);

        if ($property == 'fleet_id') 
        {
            $test_record = array(
                'id' => $input,
                'plane_id' => 'baron'
            );
            $this->CI->fleets->add($test_record);

        }
        $flight->$property = $input;
        $this->assertEquals($expected, $flight->$property == $input);

        if ($property == 'fleet_id') 
        {
            $this->CI->fleets->delete($input);
        }
    }
    /**
     * data provider for test fleet setters
     */
    public function setterDataProvider()
    {
        $data = json_decode(file_get_contents("data/flight_setters.json"),true);
        $testData = array();
        foreach ($data as $record)
        {
            $testData[$record['case']] = array_slice($record, 1);
        }
        return $testData;
    }
} 
