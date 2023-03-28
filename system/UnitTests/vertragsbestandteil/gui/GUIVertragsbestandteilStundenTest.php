<?php

require_once "application/libraries/vertragsbestandteil/gui/GUIVertragsbestandteilStunden.php";
require_once "application/libraries/vertragsbestandteil/gui/FormData.php";

use PHPUnit\Framework\TestCase;

class GUIVertragsbestandteilStundenTest extends TestCase
{
	
    public function setUp()
    {
		error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
	}
	
	public function testMapJSON_01()
	{
		$jsondata = file_get_contents('./system/UnitTests/vertragsbestandteil/gui/stunden01.json');
		$decoded = json_decode($jsondata, true);
		$formDataMapper = new FormData();
		$formDataMapper->mapJSON($decoded);
		// VBS
		$vbs = $formDataMapper->getVbs();
		$this->assertNotEmpty($vbs);
		$this->assertNotEmpty($vbs['73a60d69-cbd5-40f0-bcb1-ed0ccdd4a9fd']);
        $vbsMapper = new GUIVertragsbestandteilStunden();
		$vbsMapper->mapJSON($vbs['73a60d69-cbd5-40f0-bcb1-ed0ccdd4a9fd']);
		$vbsData=$vbsMapper->getData();
		var_dump($vbsData);
		$this->assertNotEmpty($vbsData['stunden']);
		$this->assertEquals(38.5, $vbsData['stunden']);
		// GBS
		$this->assertNotEmpty($vbsMapper->getGbs());

		foreach ($vbsMapper->getGbs() as $gbs)
		{			
			$this->assertNotEmpty($gbs->getData());
			$gbsData = $gbs->getData();
			$this->assertNotEmpty($gbsData['gehaltstyp']);
			$this->assertNotEmpty($gbsData['betrag']);
			$this->assertNotEmpty($gbsData['gueltigkeit']);
			$this->assertNotEmpty($gbsData['valorisierung']);
		}
	}

	public function testMapJSON_02()
	{
		$jsondata = file_get_contents('./system/UnitTests/vertragsbestandteil/gui/funktion01.json');
		$decoded = json_decode($jsondata, true);
		$formDataMapper = new FormData();
		$formDataMapper->mapJSON($decoded);
		// VBS
		$vbs = $formDataMapper->getVbs();
		$this->assertNotEmpty($vbs);
		$this->assertNotEmpty($vbs['98704748-0ef0-4a70-94b7-5d8e719c2b3e']);
        $vbsMapper = new GUIVertragsbestandteilStunden();
		$vbsMapper->mapJSON($vbs['98704748-0ef0-4a70-94b7-5d8e719c2b3e']);
		$vbsData=$vbsMapper->getData();
		$this->assertNotEmpty($vbsData['stunden']);
		$this->assertEquals(38.5, $vbsData['stunden']);
		$this->assertNotEmpty($vbsData['gueltigkeit']->getData());
		// GBS
		$this->assertNotEmpty($vbsMapper->getGbs());

		foreach ($vbsMapper->getGbs() as $gbs)
		{
			$this->assertNotEmpty($gbs->getData());
			$gbsData = $gbs->getData();
			$this->assertNotEmpty($gbsData['gehaltstyp']);
			$this->assertNotEmpty($gbsData['betrag']);
			$this->assertNotEmpty($gbsData['gueltigkeit']);
			$this->assertEmpty($gbsData['valorisierung']);
			$this->assertNotEmpty($gbsData['gueltigkeit']->getData());
		}
	}

}