<?php

include_once('../CFDBQueryResultIteratorFactory.php');
include_once('../ExportToCsvUtf8.php');
include_once('../ExportToJson.php');
include_once('../trans/BaseTransform.php');

include_once('MockQueryResultIterator.php');
include_once('WP_Mock_Functions.php');
include_once('WPDB_Mock.php');


$wpdb = null; // mock global

class TestTransforms extends PHPUnit_Framework_TestCase {

    public function tearDown() {
        CFDBQueryResultIteratorFactory::getInstance()->clearMock();
        $wpdb = null;
        try {
            ob_flush();
            ob_end_clean();
        } catch (Exception $e) {
        }
    }

    public function setUp() {
        $data = array(
                array('submit_time' => '1401303038.5193', 'Submitted' => '2014-05-28 14:50:38 -04:00', 'name' => 'b1', 'age' => '2000', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1', 'misc' => 'x1'),
                array('submit_time' => '1401303030.4485', 'Submitted' => '2014-05-28 14:50:30 -04:00', 'name' => 'a2', 'age' => '30', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1', 'misc' => 'X11'),
                array('submit_time' => '1401303022.9142', 'Submitted' => '2014-05-28 14:50:22 -04:00', 'name' => 'a', 'age' => '9', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1', 'misc' => 'X101'),
                array('submit_time' => '1401303016.1247', 'Submitted' => '2014-05-28 14:50:16 -04:00', 'name' => 'p1', 'age' => '20', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1', 'misc' => 'x2'),
                array('submit_time' => '1401303009.4765', 'Submitted' => '2014-05-28 14:50:09 -04:00', 'name' => 'p2', 'age' => '20.001', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1', 'misc' => 'x6'),
                array('submit_time' => '1401302992.7106', 'Submitted' => '2014-05-28 14:49:52 -04:00', 'name' => 'j', 'age' => '1500', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1', 'misc' => 'X8'),
                array('submit_time' => '1401302985.4975', 'Submitted' => '2014-05-28 14:49:45 -04:00', 'name' => 'd', 'age' => '.99999', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1', 'misc' => 'x123'),
                array('submit_time' => '1401302974.9052', 'Submitted' => '2014-05-28 14:49:34 -04:00', 'name' => 'm', 'age' => '900', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1', 'misc' => 'x12'),
        );
        $mock = new MockQueryResultIterator($data);
        CFDBQueryResultIteratorFactory::getInstance()->setQueryResultsIteratorMock($mock);

        global $wpdb;
        $wpdb = new WPDB_Mock;

        $fields = array();
        foreach (array_keys($data[0]) as $key) {
            $fields[] = (object)array('field_name' => $key);
        }
        $wpdb->getResultReturnVal = $fields;
    }

    public function test_simple() {
        $options = array();
        $exp = new ExportToCsvUtf8();
        ob_start();
        $exp->export('Ages', $options);
        $text = ob_get_contents();
        $this->assertTrue(strlen($text) > 20);
        $this->assertTrue(strpos($text, 'msimpson') > 0);
    }

    public function test_transform() {
        $options = array();
        $options['trans'] = 'name=strtoupper(name)';

        $exp = new ExportToCsvUtf8();
        ob_start();
        $exp->export('Ages', $options);
        $text = ob_get_contents();

        $this->assertTrue(strlen($text) > 20);
        $this->assertTrue(strpos($text, 'msimpson') > 0);
        $this->assertTrue(strpos($text, 'B1') > 0);
        $this->assertTrue(strpos($text, 'P2') > 0);
    }

    public function testLexicalSortClass() {
        $options = array();
        $options['trans'] = 'SortByField(misc)';

        $exp = new ExportToJson();
        ob_start();
        $exp->export('Ages', $options);
        $text = ob_get_contents();

        $stuff = json_decode($text);
        $idx = 0;
        $this->assertTrue(is_array($stuff));
        $this->assertEquals('X101', $stuff[$idx++]->misc);
        $this->assertEquals('X11', $stuff[$idx++]->misc);
        $this->assertEquals('X8', $stuff[$idx++]->misc);
        $this->assertEquals('x1', $stuff[$idx++]->misc);
        $this->assertEquals('x12', $stuff[$idx++]->misc);
        $this->assertEquals('x123', $stuff[$idx++]->misc);
        $this->assertEquals('x2', $stuff[$idx++]->misc);
        $this->assertEquals('x6', $stuff[$idx++]->misc);
    }

    public function testNaturalSortClass() {
        $options = array();
        $options['trans'] = 'NaturalSortByField(misc)';

        $exp = new ExportToJson();
        ob_start();
        $exp->export('Ages', $options);
        $text = ob_get_contents();

        $stuff = json_decode($text);
        $this->assertTrue(is_array($stuff));
        $idx = 0;
        $this->assertEquals('X8', $stuff[$idx++]->misc);
        $this->assertEquals('X11', $stuff[$idx++]->misc);
        $this->assertEquals('X101', $stuff[$idx++]->misc);
        $this->assertEquals('x1', $stuff[$idx++]->misc);
        $this->assertEquals('x2', $stuff[$idx++]->misc);
        $this->assertEquals('x6', $stuff[$idx++]->misc);
        $this->assertEquals('x12', $stuff[$idx++]->misc);
        $this->assertEquals('x123', $stuff[$idx++]->misc);
    }

    public function testTransformThenSort() {
        $options = array();
        $options['trans'] = 'misc=strtoupper(misc)&&NaturalSortByField(misc)';

        $exp = new ExportToJson();
        ob_start();
        $exp->export('Ages', $options);
        $text = ob_get_contents();

        $stuff = json_decode($text);
        $this->assertTrue(is_array($stuff));
        $idx = 0;
        $this->assertEquals('X1', $stuff[$idx++]->misc);
        $this->assertEquals('X2', $stuff[$idx++]->misc);
        $this->assertEquals('X6', $stuff[$idx++]->misc);
        $this->assertEquals('X8', $stuff[$idx++]->misc);
        $this->assertEquals('X11', $stuff[$idx++]->misc);
        $this->assertEquals('X12', $stuff[$idx++]->misc);
        $this->assertEquals('X101', $stuff[$idx++]->misc);
        $this->assertEquals('X123', $stuff[$idx++]->misc);
    }

    public function testSimpleStat() {
        $options = array();
        $options['trans'] = 'HardCodedData';

        $exp = new ExportToJson();
        ob_start();
        $exp->export('Ages', $options);
        $text = ob_get_contents();

        $stuff = json_decode($text);
        $this->assertTrue(is_array($stuff));
        $this->assertEquals('Mike', $stuff[0]->first_name);
        $this->assertEquals('Oya', $stuff[1]->first_name);
    }

}

class HardCodedData extends BaseTransform {

    public function getTransformedData() {
        return array(
                array('first_name' => 'Mike', 'last_name' => 'Simpson'),
                array('first_name' => 'Oya', 'last_name' => 'Simpson')
        );
    }

}


