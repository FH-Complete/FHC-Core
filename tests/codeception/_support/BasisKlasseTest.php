<?php
require_once(dirname(__FILE__).'/../../../include/basis.class.php');

class ExampleTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testMe()
    {
	$bc = new basis();
	$bc->errormsg=true;
	$this->assertTrue($bc->getErrorMsg());
    }

}
