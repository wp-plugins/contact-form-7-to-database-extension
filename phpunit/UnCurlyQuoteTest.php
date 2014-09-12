<?php

include_once(dirname(dirname(__FILE__)) . '/ShortCodeLoader.php');

class UnCurlyQuoteTest extends PHPUnit_Framework_TestCase {

    public function testStripCurlyQuote() {
        $sl = new UnCurlyQuoteTestShortCodeLoader;
        $stripped = $sl->stripCurlyQuotes('”hello”');
        $this->assertEquals('hello', $stripped);
    }

    public function testStripCurlyQuote2() {
        $sl = new UnCurlyQuoteTestShortCodeLoader;
        $stripped = $sl->stripCurlyQuotes('”3″');
        $this->assertEquals('3', $stripped);
    }

    public function testNotStripCurlyQuoteStart() {
        $sl = new UnCurlyQuoteTestShortCodeLoader;
        $stripped = $sl->stripCurlyQuotes('”hello');
        $this->assertEquals('”hello', $stripped);
    }

    public function testNotStripCurlyQuoteEnd() {
        $sl = new UnCurlyQuoteTestShortCodeLoader;
        $stripped = $sl->stripCurlyQuotes('hello”');
        $this->assertEquals('hello”', $stripped);
    }

}

class UnCurlyQuoteTestShortCodeLoader extends ShortCodeLoader {
    public function handleShortcode($atts, $content = null) {
    }
}