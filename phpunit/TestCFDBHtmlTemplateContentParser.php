<?php

require_once('../CFDBHtmlTemplateContentParser.php');

class TestCFDBHtmlTemplateContentParser extends PHPUnit_Framework_TestCase {

    public function test_parseHeaderTemplateFooter_no_header_no_footer() {
        $parser = new CFDBHtmlTemplateContentParser();
        $content = 'Name: ${fname} ${lname}';

        $header = null;
        $template = null;
        $footer = null;
        list($header, $template, $footer) = $parser->parseHeaderTemplateFooter($content);

        $this->assertEquals(null, $header);
        $this->assertEquals($content, $template);
        $this->assertEquals(null, $footer);
    }

    public function test_parseHeaderTemplateFooter_header_no_footer() {
        $parser = new CFDBHtmlTemplateContentParser();
        $content = '{{HEADER}}This is my header{{/HEADER}}Name: ${fname} ${lname}';

        $header = null;
        $template = null;
        $footer = null;
        list($header, $template, $footer) = $parser->parseHeaderTemplateFooter($content);

        $this->assertEquals('This is my header', $header);
        $this->assertEquals('Name: ${fname} ${lname}', $template);
        $this->assertEquals(null, $footer);
    }

    public function test_parseHeaderTemplateFooter_no_header_footer() {
        $parser = new CFDBHtmlTemplateContentParser();
        $content = 'Name: ${fname} ${lname}{{FOOTER}}This is my footer{{/FOOTER}}';

        $header = null;
        $template = null;
        $footer = null;
        list($header, $template, $footer) = $parser->parseHeaderTemplateFooter($content);

        $this->assertEquals(null, $header);
        $this->assertEquals('Name: ${fname} ${lname}', $template);
        $this->assertEquals('This is my footer', $footer);
    }

    public function test_parseHeaderTemplateFooter_header_footer() {
        $parser = new CFDBHtmlTemplateContentParser();
        $content = '{{HEADER}}This is my header{{/HEADER}}Name: ${fname} ${lname}{{FOOTER}}This is my footer{{/FOOTER}}';

        $header = null;
        $template = null;
        $footer = null;
        list($header, $template, $footer) = $parser->parseHeaderTemplateFooter($content);

        $this->assertEquals('This is my header', $header);
        $this->assertEquals('Name: ${fname} ${lname}', $template);
        $this->assertEquals('This is my footer', $footer);
    }

} 