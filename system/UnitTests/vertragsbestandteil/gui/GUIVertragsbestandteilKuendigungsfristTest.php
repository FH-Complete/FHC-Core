<?php

require_once "application/libraries/vertragsbestandteil/gui/GUIVertragsbestandteilKuendigungsfrist.php";
require_once "application/libraries/vertragsbestandteil/gui/FormData.php";

use PHPUnit\Framework\TestCase;

class GUIVertragsbestandteilKuendigungsfristTest extends TestCase
{
	
    public function setUp()
    {
		error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
	}
	
	public function testMapJSON_01()
	{
		$jsondata = file_get_contents('./system/UnitTests/vertragsbestandteil/gui/kuendigungsfrist01.json');
		$decoded = json_decode($jsondata, true);
		$formDataMapper = new FormData();
		$formDataMapper->mapJSON($decoded);
		// VBS
		$vbs = $formDataMapper->getVbs();
		$this->assertNotEmpty($vbs);
		$this->assertNotEmpty($vbs['6ae61b45-99a8-406b-b583-9f0353dd834f']);
        $vbsMapper = new GUIVertragsbestandteilKuendigungsfrist();
		$vbsMapper->mapJSON($vbs['6ae61b45-99a8-406b-b583-9f0353dd834f']);
		$vbsData=$vbsMapper->getData();
		$this->assertNotEmpty($vbsData['arbeitgeber_frist']);
		$this->assertNotEmpty($vbsData['arbeitnehmer_frist']);
		
	}

	

}