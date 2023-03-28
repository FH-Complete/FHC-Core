<?php

require_once "application/libraries/vertragsbestandteil/gui/GUIVertragsbestandteilZusatzvereinbarung.php";
require_once "application/libraries/vertragsbestandteil/gui/FormData.php";

use PHPUnit\Framework\TestCase;

class GUIVertragsbestandteilZusatzvereinbarungTest extends TestCase
{
	
    public function setUp()
    {
		error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
	}
	
	public function testMapJSON_01()
	{
		$jsondata = file_get_contents('./system/UnitTests/vertragsbestandteil/gui/zusatz01.json');
		$decoded = json_decode($jsondata, true);
		$formDataMapper = new FormData();
		$formDataMapper->mapJSON($decoded);
		// VBS
		$vbs = $formDataMapper->getVbs();
		$this->assertNotEmpty($vbs);
		$this->assertNotEmpty($vbs['b168a3bb-d0e2-407f-8192-525a5ab59b22']);
        $vbsMapper = new GUIVertragsbestandteilZusatzvereinbarung();
		$vbsMapper->mapJSON($vbs['b168a3bb-d0e2-407f-8192-525a5ab59b22']);
		$vbsData=$vbsMapper->getData();
		$this->assertNotEmpty($vbsData['freitexttyp']);
		$this->assertEquals('allin', $vbsData['freitexttyp']);
		$this->assertNotEmpty($vbsData['titel']);
		$this->assertEquals('Lorem ipsum', $vbsData['titel']);
		// GBS
		$this->assertEmpty($vbsMapper->getGbs());

		
	}

	

}