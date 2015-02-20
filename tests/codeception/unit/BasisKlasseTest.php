<?php
require_once('../include/basis.class.php');

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
