<?php
use PHPUnit\Framework\TestCase;
class FleetTest extends TestCase
{
    private $CI;
    public function setUp()
    {
        $this->CI = &get_instance();
    }
    /**
     * @dataProvider setterDataProvider
     */
    public function testSetters($property, $input, $expected)
    {
        $fleet = $this->CI->fleet;
        $fleet->$property = $input;
        $this->assertEquals($expected, $fleet->$property == $input);
    }
    /**
     * data provider for test fleet setters
     */
    public function setterDataProvider()
    {
        $data = json_decode(file_get_contents("data/fleet_setters.json"),true);
        $testData = array();
        foreach ($data as $record)
        {
            $testData[$record['case']] = array_slice($record, 1);
        }
        return $testData;
    }
}
