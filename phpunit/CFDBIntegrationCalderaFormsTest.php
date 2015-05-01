<?php

include_once(dirname(dirname(__FILE__)) . '/CFDBIntegrationCalderaForms.php');

/**
 * mock WP function
 * @return string
 */
function get_home_path() {
    return '/var/www/htdocs/';
}

class CFDBIntegrationCalderaFormsTest extends PHPUnit_Framework_TestCase {

    public function testSubmission() {

        $form_ser = file_get_contents('CFDBIntegrationCalderaFormsTest/form.dat');
        $referrer_ser = file_get_contents('CFDBIntegrationCalderaFormsTest/referrer.dat');
        $processed_data_ser = file_get_contents('CFDBIntegrationCalderaFormsTest/processed_data.dat');

        $form = unserialize($form_ser);
        $referrer = unserialize($referrer_ser);
        global $processed_data;
        $processed_data = unserialize($processed_data_ser);

        $caldera = new CFDBIntegrationCalderaForms(null);
        $data = $caldera->convertData($form);

        $this->assertEquals("Caldera Form 1", $data->title);
        $this->assertEquals('click', $data->posted_data['mybutton']);
        $this->assertEquals('good,bad,ugly', $data->posted_data['mycheckbox']);
        $this->assertEquals('red', $data->posted_data['mydropdown']);
        $this->assertEquals('no_one@nowhere.com', $data->posted_data['email']);
        $this->assertEquals('my hidden value', $data->posted_data['myhidden']);
        $this->assertEquals("line1\nline2\nline3", $data->posted_data['text']);
        $this->assertEquals('(123)456-7890', $data->posted_data['phone']);
        $this->assertEquals('far', $data->posted_data['howfar']);
        $this->assertEquals('VA', $data->posted_data['state']);
        $this->assertEquals('hello', $data->posted_data['line']);
        $this->assertEquals('#786161', $data->posted_data['color']);
        $this->assertEquals('73', $data->posted_data['range']);

        $this->assertEquals('Screen-Shot.png', $data->posted_data['file']);
        $this->assertEquals('/var/www/htdocs//wp-content/uploads/2015/05/Screen-Shot.png', $data->uploaded_files['file']);

    }

    public function test_getUrlWithoutSchemeHostAndPort_1() {
        $caldera = new CFDBIntegrationCalderaForms(null);
        $this->assertEquals('/wp-content/uploads/2015/05/Screen-Shot.png',
                $caldera->getUrlWithoutSchemeHostAndPort('http://www.mysite.com/wp-content/uploads/2015/05/Screen-Shot.png'));
    }

    public function test_getUrlWithoutSchemeHostAndPort_2() {
        $caldera = new CFDBIntegrationCalderaForms(null);
        $this->assertEquals('/wp-content/uploads/2015/05/Screen-Shot.png',
                $caldera->getUrlWithoutSchemeHostAndPort('https://www.mysite.com/wp-content/uploads/2015/05/Screen-Shot.png'));
    }

    public function test_getUrlWithoutSchemeHostAndPort_3() {
        $caldera = new CFDBIntegrationCalderaForms(null);
        $this->assertEquals('/wp-content/uploads/2015/05/Screen-Shot.png',
                $caldera->getUrlWithoutSchemeHostAndPort('https://www.mysite.com:8080/wp-content/uploads/2015/05/Screen-Shot.png'));
    }

}