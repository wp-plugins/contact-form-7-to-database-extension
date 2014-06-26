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
        $str = file_get_contents('TestTransforms.json');
        $data = json_decode($str, true);
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

    // todo:

    // hide metadata fields

    // show/hide

    // limit

    // order by

    // random


    // filter & search
    // Test filter on transformed values
    // t-filter?

}

class HardCodedData extends BaseTransform {

    public function getTransformedData() {
        return array(
                array('first_name' => 'Mike', 'last_name' => 'Simpson'),
                array('first_name' => 'Oya', 'last_name' => 'Simpson')
        );
    }

}


