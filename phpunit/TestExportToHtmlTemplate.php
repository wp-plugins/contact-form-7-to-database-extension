<?php

require_once('../ExportToHtmlTemplate.php');

include_once('MockQueryResultIterator.php');
include_once('WP_Mock_Functions.php');
include_once('WPDB_Mock.php');

class TestExportToHtmlTemplate extends PHPUnit_Framework_TestCase {

    var $bufferOutput = false;

    public function tearDown() {
        if ($this->bufferOutput) {
            ob_flush();
            ob_end_clean();
            $this->bufferOutput = false;
        }
    }

    public function exportSetup($data) {
        date_default_timezone_set('America/New_York');
        $mock = new MockQueryResultIterator($data);
        CFDBQueryResultIteratorFactory::getInstance()->setQueryResultsIteratorMock($mock);

        global $wpdb;
        $wpdb = new WPDB_Mock;

        $fields = array();
        foreach (array_keys($data[0]) as $key) {
            $fields[] = (object)array('field_name' => $key);
        }
        $wpdb->getResultReturnVal = $fields;
        $this->bufferOutput = true;
    }

    public function test_export_no_header_no_footer() {
        $data = array(
                array('first-name' => 'John', 'last-name' => 'Doe'),
                array('first-name' => 'Richard', 'last-name' => 'Roe')
        );
        $this->exportSetup($data);
        $options = array();
        $options['content'] =
                '<p>To: ${first-name} ${last-name}</p>';

        $exp = new ExportToHtmlTemplate();
        ob_start();
        $exp->export('Form', $options);
        $text = ob_get_contents();

        $expected =
                '<p>To: John Doe</p>' .
                '<p>To: Richard Roe</p>';
        $this->assertEquals($expected, $text);
    }

    public function test_export_header_no_footer() {
        $data = array(
                array('first-name' => 'John', 'last-name' => 'Doe'),
                array('first-name' => 'Richard', 'last-name' => 'Roe')
        );
        $this->exportSetup($data);
        $options = array();
        $options['content'] =
                '{{HEADER}}<p><b>Registered Users</b></p>{{/HEADER}}' .
                '<p>To: ${first-name} ${last-name}</p>';


        $exp = new ExportToHtmlTemplate();
        ob_start();
        $exp->export('Form', $options);
        $text = ob_get_contents();

        $expected =
                '<p><b>Registered Users</b></p>' .
                '<p>To: John Doe</p>' .
                '<p>To: Richard Roe</p>';
        $this->assertEquals($expected, $text);
    }

    public function test_export_no_header_footer() {
        $data = array(
                array('first-name' => 'John', 'last-name' => 'Doe'),
                array('first-name' => 'Richard', 'last-name' => 'Roe')
        );
        $this->exportSetup($data);
        $options = array();
        $options['content'] =
                '<p>To: ${first-name} ${last-name}</p>' .
                '{{FOOTER}}<p><b>Thank you!</b></p>{{/FOOTER}}';


        $exp = new ExportToHtmlTemplate();
        ob_start();
        $exp->export('Form', $options);
        $text = ob_get_contents();

        $expected =
                '<p>To: John Doe</p>' .
                '<p>To: Richard Roe</p>' .
                '<p><b>Thank you!</b></p>';
        $this->assertEquals($expected, $text);
    }

    public function test_export_header_footer() {
        $data = array(
                array('first-name' => 'John', 'last-name' => 'Doe'),
                array('first-name' => 'Richard', 'last-name' => 'Roe')
        );
        $this->exportSetup($data);
        $options = array();
        $options['content'] =
                '{{HEADER}}<p><b>Registered Users</b></p>{{/HEADER}}' .
                '<p>To: ${first-name} ${last-name}</p>' .
                '{{FOOTER}}<p><b>Thank you!</b></p>{{/FOOTER}}';


        $exp = new ExportToHtmlTemplate();
        ob_start();
        $exp->export('Form', $options);
        $text = ob_get_contents();

        $expected =
                '<p><b>Registered Users</b></p>' .
                '<p>To: John Doe</p>' .
                '<p>To: Richard Roe</p>' .
                '<p><b>Thank you!</b></p>';
        $this->assertEquals($expected, $text);
    }

}