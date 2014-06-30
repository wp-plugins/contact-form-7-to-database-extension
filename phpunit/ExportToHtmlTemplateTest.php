<?php

require_once('../ExportToHtmlTemplate.php');

include_once('MockQueryResultIterator.php');
include_once('WP_Mock_Functions.php');
include_once('WPDB_Mock.php');

class ExportToHtmlTemplateTest extends PHPUnit_Framework_TestCase {

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


    public function dataProvider() {
        $data = array();

        $data[] = array('no header, no footer',
                '<p>To: ${first-name} ${last-name}</p>',
                '<p>To: John Doe</p>' .
                '<p>To: Richard Roe</p>');

        $data[] = array('header, no footer',
                '{{BEFORE}}<p><b>Registered Users</b></p>{{/BEFORE}}' .
                '<p>To: ${first-name} ${last-name}</p>',
                '<p><b>Registered Users</b></p>' .
                '<p>To: John Doe</p>' .
                '<p>To: Richard Roe</p>'
        );

        $data[] = array('no header, footer',
                '<p>To: ${first-name} ${last-name}</p>' .
                '{{AFTER}}<p><b>Thank you!</b></p>{{/AFTER}}',
                '<p>To: John Doe</p>' .
                '<p>To: Richard Roe</p>' .
                '<p><b>Thank you!</b></p>'
        );

        $data[] = array('header, footer',
                '{{BEFORE}}<p><b>Registered Users</b></p>{{/BEFORE}}' .
                '<p>To: ${first-name} ${last-name}</p>' .
                '{{AFTER}}<p><b>Thank you!</b></p>{{/AFTER}}',
                '<p><b>Registered Users</b></p>' .
                '<p>To: John Doe</p>' .
                '<p>To: Richard Roe</p>' .
                '<p><b>Thank you!</b></p>'
        );

        return $data;
    }

    /**
     * @dataProvider dataProvider
     */
    public function test_export($message, $content, $expected) {
        $data = array(
                array('first-name' => 'John', 'last-name' => 'Doe'),
                array('first-name' => 'Richard', 'last-name' => 'Roe')
        );
        $this->exportSetup($data);
        $options = array();
        $options['content'] = $content;

        $exp = new ExportToHtmlTemplate();
        ob_start();
        $exp->export('Form', $options);
        $text = ob_get_contents();

        $this->assertEquals($expected, $text, $message);

    }

}