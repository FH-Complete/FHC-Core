<?php

require_once "application/libraries/vertragsbestandteil/gui/GUIVertragsbestandteilZeitaufzeichnung.php";
require_once "application/libraries/vertragsbestandteil/gui/FormData.php";

use PHPUnit\Framework\TestCase;

class GUIVertragsbestandteilZeitaufzeichnungTest extends TestCase
{
	
    public function setUp()
    {
		error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
	}
	
	public function testMapJSON_01()
	{
		$jsondata = file_get_contents('./system/UnitTests/vertragsbestandteil/gui/zeitaufzeichnung01.json');
		$decoded = json_decode($jsondata, true);
		$formDataMapper = new FormData();
		$formDataMapper->mapJSON($decoded);
		// VBS
		$vbs = $formDataMapper->getVbs();
		$this->assertNotEmpty($vbs);
		$this->assertNotEmpty($vbs['7625d25d-8fd9-476b-94a6-4fbb72c147d4']);
        $vbsMapper = new GUIVertragsbestandteilZeitaufzeichnung();
		$vbsMapper->mapJSON($vbs['7625d25d-8fd9-476b-94a6-4fbb72c147d4']);
		$vbsData=$vbsMapper->getData();
		$this->assertNotEmpty($vbsData['zeitaufzeichnung']);
		$this->assertTrue($vbsData['zeitaufzeichnung']);
		$this->assertEmpty($vbsData['azgrelevant']);
		$this->assertNotEmpty($vbsData['homeoffice']);
		$this->assertTrue($vbsData['homeoffice']);
		
	}

	

}