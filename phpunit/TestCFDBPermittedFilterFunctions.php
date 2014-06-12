<?php

include_once('../CFDBPermittedFilterFunctions.php');

class TestCFDBPermittedFilterFunctions extends PHPUnit_Framework_TestCase {

    public function tearDown() {
        CFDBPermittedFilterFunctions::getInstance()->init();
    }

    public function testSet() {
        $p = CFDBPermittedFilterFunctions::getInstance();
        $this->assertFalse($p->isFunctionPermitted('blahblah'));

        $p->setPermitAllFunctions(false);
        $this->assertFalse($p->isFunctionPermitted('blahblah'));

        $p->setPermitAllFunctions(true);
        $this->assertTrue($p->isFunctionPermitted('blahblah'));

        $p->setPermitAllFunctions(false);
        $this->assertFalse($p->isFunctionPermitted('blahblah'));
    }

    public function testAddFunction() {
        $p = CFDBPermittedFilterFunctions::getInstance();
        $this->assertFalse($p->isFunctionPermitted('blahblah'));

        $p->addPermittedFunction("blahblah");
        $this->assertTrue($p->isFunctionPermitted('blahblah'));
    }

    public function testSingleton() {
        $this->assertFalse(
                CFDBPermittedFilterFunctions::getInstance()->isFunctionPermitted('blahblah'));
        CFDBPermittedFilterFunctions::getInstance()->addPermittedFunction('blahblah');
        $this->assertTrue(
                CFDBPermittedFilterFunctions::getInstance()->isFunctionPermitted('blahblah'));

    }

    public function testRegisterFunction() {
        $this->assertFalse(
                CFDBPermittedFilterFunctions::getInstance()->isFunctionPermitted('blahblah'));
        cfdb_register_function('blahblah');
        $this->assertTrue(
                CFDBPermittedFilterFunctions::getInstance()->isFunctionPermitted('blahblah'));
    }

} 