<?php


require_once __DIR__ . "/../../../../application/libraries/vertragsbestandteil/gui/GUIHandler.php";

use PHPUnit\Framework\TestCase;

class GuiHandlerTest extends TestCase
{
	protected $employeeUID = 'masik';
    protected $userUID = 'user4712';
    private static $CI;

    
    public static function setUpBeforeClass(): void
    {
		error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
        self::$CI =& get_instance();
        self::$CI->load->helper('hlp_common');
        self::$CI->load->helper('hlp_return_object');
	}

    
    public function testHandleInsert(): void
	{
        $jsondata = file_get_contents('./system/UnitTests/vertragsbestandteil/gui/stunden01.json');
        $this->assertNotEmpty($jsondata);
        $GH = new GUIHandler($this->employeeUID, $this->userUID);
        $res = $GH->handle($jsondata);
    }

    

    public function test_true()
    {
        $this->assertTrue(true);
    }



}