<?php

include_once('../CFDBTransformParser.php');

class TestCFDBTransformParser extends PHPUnit_Framework_TestCase
{

    public function  test_parse_1_1()
    {
        $p = new CFDBTransformParser;
        $p->parse('last_name=funct');
        $e = $p->getExpressionTree();

        $this->assertEquals(1, count($e));
        $this->assertEquals("last_name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct", $e[0][2]);
    }

    public function  test_parse_1_2()
    {
        $p = new CFDBTransformParser;
        $p->parse('last_name=funct()');
        $e = $p->getExpressionTree();

        $this->assertEquals(1, count($e));
        $this->assertEquals("last_name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct", $e[0][2]);
    }


    // 1 arg

    public function  test_parse_2_1()
    {
        $p = new CFDBTransformParser;
        $p->parse('last_name=strtoupper(last_name)');
        $e = $p->getExpressionTree();

        $this->assertEquals(1, count($e));
        $this->assertEquals("last_name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("strtoupper", $e[0][2]);
        $this->assertEquals("last_name", $e[0][3]);
    }

    // multiple args

    public function  test_parse_2_2()
    {
        $p = new CFDBTransformParser;
        $p->parse('name=funct(first_name,last_name)');
        $e = $p->getExpressionTree();

        $this->assertEquals(1, count($e));
        $this->assertEquals("name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct", $e[0][2]);
        $this->assertEquals("first_name", $e[0][3]);
        $this->assertEquals("last_name", $e[0][4]);
    }

    public function  test_parse_2_3()
    {
        $p = new CFDBTransformParser;
        $p->parse('name=funct(first_name, last_name)');
        $e = $p->getExpressionTree();

        $this->assertEquals(1, count($e));
        $this->assertEquals("name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct", $e[0][2]);
        $this->assertEquals("first_name", $e[0][3]);
        $this->assertEquals("last_name", $e[0][4]);
    }

    public function  test_parse_2_4()
    {
        $p = new CFDBTransformParser;
        $p->parse('name=funct(first_name,   middle_name,      last_name)');
        $e = $p->getExpressionTree();

        $this->assertEquals(1, count($e));
        $this->assertEquals("name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct", $e[0][2]);
        $this->assertEquals("first_name", $e[0][3]);
        $this->assertEquals("middle_name", $e[0][4]);
        $this->assertEquals("last_name", $e[0][5]);
    }

    // multiples
    public function  test_parse_3_1()
    {
        $p = new CFDBTransformParser;
        $p->parse('first_name=funct1&&last_name=funct2');
        $e = $p->getExpressionTree();

        $this->assertEquals(2, count($e));
        $this->assertEquals("first_name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct1", $e[0][2]);

        $this->assertEquals("last_name", $e[1][0]);
        $this->assertEquals("=", $e[1][1]);
        $this->assertEquals("funct2", $e[1][2]);
    }

    public function  test_parse_3_2()
    {
        $p = new CFDBTransformParser;
        $p->parse('first_name=funct1()&&last_name=funct2()');
        $e = $p->getExpressionTree();

        $this->assertEquals(2, count($e));
        $this->assertEquals("first_name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct1", $e[0][2]);

        $this->assertEquals("last_name", $e[1][0]);
        $this->assertEquals("=", $e[1][1]);
        $this->assertEquals("funct2", $e[1][2]);
    }

    public function  test_parse_4_1()
    {
        $p = new CFDBTransformParser;
        $p->parse('first_name=funct1(x_name)&&last_name=funct2()');
        $e = $p->getExpressionTree();

        $this->assertEquals(2, count($e));
        $this->assertEquals("first_name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct1", $e[0][2]);
        $this->assertEquals("x_name", $e[0][3]);

        $this->assertEquals("last_name", $e[1][0]);
        $this->assertEquals("=", $e[1][1]);
        $this->assertEquals("funct2", $e[1][2]);
    }

    public function test_parse_4_2()
    {
        $p = new CFDBTransformParser;
        $p->parse('first_name=funct1()&&last_name=funct2(x_name)');
        $e = $p->getExpressionTree();

        $this->assertEquals(2, count($e));
        $this->assertEquals("first_name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct1", $e[0][2]);

        $this->assertEquals("last_name", $e[1][0]);
        $this->assertEquals("=", $e[1][1]);
        $this->assertEquals("funct2", $e[1][2]);
        $this->assertEquals("x_name", $e[1][3]);
    }

    public function test_parse_4_3()
    {
        $p = new CFDBTransformParser;
        $p->parse('first_name=funct1(y_name)&&last_name=funct2(x_name)');
        $e = $p->getExpressionTree();

        $this->assertEquals(2, count($e));
        $this->assertEquals("first_name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct1", $e[0][2]);
        $this->assertEquals("y_name", $e[0][3]);

        $this->assertEquals("last_name", $e[1][0]);
        $this->assertEquals("=", $e[1][1]);
        $this->assertEquals("funct2", $e[1][2]);
        $this->assertEquals("x_name", $e[1][3]);
    }

    public function test_parse_4_4()
    {
        $p = new CFDBTransformParser;
        $p->parse('first_name=funct1(y1_name,y2_name)&&last_name=funct2(x1_name,x2_name)');
        $e = $p->getExpressionTree();

        $this->assertEquals(2, count($e));
        $this->assertEquals("first_name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct1", $e[0][2]);
        $this->assertEquals("y1_name", $e[0][3]);
        $this->assertEquals("y2_name", $e[0][4]);

        $this->assertEquals("last_name", $e[1][0]);
        $this->assertEquals("=", $e[1][1]);
        $this->assertEquals("funct2", $e[1][2]);
        $this->assertEquals("x1_name", $e[1][3]);
        $this->assertEquals("x2_name", $e[1][4]);
    }

    public function test_parse_4_5()
    {
        $p = new CFDBTransformParser;
        $p->parse('  first_name=funct1(y1_name,  y2_name) &&  last_name=funct2( x1_name,  x2_name)');
        $e = $p->getExpressionTree();

        $this->assertEquals(2, count($e));
        $this->assertEquals("first_name", $e[0][0]);
        $this->assertEquals("=", $e[0][1]);
        $this->assertEquals("funct1", $e[0][2]);
        $this->assertEquals("y1_name", $e[0][3]);
        $this->assertEquals("y2_name", $e[0][4]);

        $this->assertEquals("last_name", $e[1][0]);
        $this->assertEquals("=", $e[1][1]);
        $this->assertEquals("funct2", $e[1][2]);
        $this->assertEquals("x1_name", $e[1][3]);
        $this->assertEquals("x2_name", $e[1][4]);
    }

    public function test_parse_5_1()
    {
        $p = new CFDBTransformParser;
        $p->parse('xxxx');
        $e = $p->getExpressionTree();
        $this->assertEquals(1, count($e));
        $this->assertEquals(1, count($e[0]));
        $this->assertEquals("xxxx", $e[0][0]);
    }

    public function test_parse_5_2()
    {
        $p = new CFDBTransformParser;
        $p->parse('xxxx&&yyyy');
        $e = $p->getExpressionTree();
        $this->assertEquals(2, count($e));

        $this->assertEquals(1, count($e[0]));
        $this->assertEquals("xxxx", $e[0][0]);

        $this->assertEquals(1, count($e[1]));
        $this->assertEquals("yyyy", $e[1][0]);
    }

    public function test_parse_5_3()
    {
        $p = new CFDBTransformParser;
        $p->parse('xxxx&&first_name=funct1(y1_name,  y2_name)&&yyy&&last_name=funct2( x1_name,  x2_name)');
        $e = $p->getExpressionTree();

        $this->assertEquals(4, count($e));

        $this->assertEquals(1, count($e[0]));
        $this->assertEquals("xxxx", $e[0][0]);

        $this->assertEquals("first_name", $e[1][0]);
        $this->assertEquals("=",          $e[1][1]);
        $this->assertEquals("funct1",     $e[1][2]);
        $this->assertEquals("y1_name",    $e[1][3]);
        $this->assertEquals("y2_name",    $e[1][4]);

        $this->assertEquals(1,     count($e[2]));
        $this->assertEquals("yyy", $e[2][0]);

        $this->assertEquals("last_name", $e[3][0]);
        $this->assertEquals("=",         $e[3][1]);
        $this->assertEquals("funct2",    $e[3][2]);
        $this->assertEquals("x1_name",   $e[3][3]);
        $this->assertEquals("x2_name",   $e[3][4]);
    }

    public function test_parse_6()
    {
        $p = new CFDBTransformParser;
        $p->parse('');
        $e = $p->getExpressionTree();
        $this->assertEquals(0, count($e));
    }
    
    // Evaluation Tests

//    public function test_eval_1_1() {
//
//        // TODO
//
//        $p = new CFDBTransformParser;
//        $p->parse('fname=AAA&&strtoupper(lname)=ZZZ');
//        $data = array('fname' => 'AAA');
//        $p->evaluate($data);
//
//        $this->fail();
//    }

}