
<?php
use PHPUnit\Framework\TestCase;

class FleetsTest extends TestCase
{
    private $CI;
    public function setUp()
    {
        $this->CI=&get_instance();
    }

    /**
     * @dataProvider addFleetsDataProvider
     */
    public function testAddFleets($id, $plane_id, $expected)
    {
        $record = array(
            'id' => $id,
            'plane_id' =>$plane_id
        );
        $this->CI->fleets->add($record);
        $result = $this->CI->fleets->get($id);
        $this->assertEquals($expected, $result != null && $result->id == $id);
        /*
        if ($expected == true) {
            $this->assertEquals($expected, $result->id == $id && $result->plane_id == $plane_id);
            $this->CI->fleets->fleets->delete($id);
        }
         */
        $this->CI->fleets->delete($id);
    }

    public  function addFleetsDataProvider()
    {
        $data = json_decode(file_get_contents("data/fleets_crud_test.json"),true);
        $testData = array();
        foreach ($data as $record)
        {
            $testData[$record['case']] = array_slice($record, 1);
        }
        return $testData;
    }
} 
