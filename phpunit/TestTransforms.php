<?php

include_once('../CFDBQueryResultIteratorFactory.php');
include_once('../ExportToCsvUtf8.php');

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
                array('submit_time' => '1401303038.5193', 'Submitted' => '2014-05-28 14:50:38 -04:00', 'name' => 'b1', 'age' => '2000', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1'),
                array('submit_time' => '1401303030.4485', 'Submitted' => '2014-05-28 14:50:30 -04:00', 'name' => 'a2', 'age' => '30', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1'),
                array('submit_time' => '1401303022.9142', 'Submitted' => '2014-05-28 14:50:22 -04:00', 'name' => 'a', 'age' => '9', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1'),
                array('submit_time' => '1401303016.1247', 'Submitted' => '2014-05-28 14:50:16 -04:00', 'name' => 'p2', 'age' => '20', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1'),
                array('submit_time' => '1401303009.4765', 'Submitted' => '2014-05-28 14:50:09 -04:00', 'name' => 'p1', 'age' => '20.001', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1'),
                array('submit_time' => '1401302992.7106', 'Submitted' => '2014-05-28 14:49:52 -04:00', 'name' => 'j', 'age' => '1500', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1'),
                array('submit_time' => '1401302985.4975', 'Submitted' => '2014-05-28 14:49:45 -04:00', 'name' => 'd', 'age' => '.99999', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1'),
                array('submit_time' => '1401302974.9052', 'Submitted' => '2014-05-28 14:49:34 -04:00', 'name' => 'm', 'age' => '900', 'Submitted Login' => 'msimpson', 'Submitted From' => '192.168.1.1'),
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

        ///echo $text;
        $this->assertTrue(strlen($text) > 20);
        $this->assertTrue(strpos($text, 'msimpson') > 0);
        $this->assertTrue(strpos($text, 'B1') > 0);
        $this->assertTrue(strpos($text, 'P2') > 0);
    }


}


