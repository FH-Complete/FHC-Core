<?php

require_once "application/libraries/vertragsbestandteil/gui/FormData.php";

use PHPUnit\Framework\TestCase;

class FormDataTest extends TestCase
{
	
    public function setUp()
    {
		error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
	}
	
	public function testMapJSON_01()
	{
		$jsondata = file_get_contents('./system/UnitTests/vertragsbestandteil/gui/stunden01.json');
        $formDataMapper = new FormData();
		$decoded = json_decode($jsondata, true);
		$formDataMapper->mapJSON($decoded);
		// Dienstverhaeltnis
		$dataDV = $formDataMapper->getData();
		$this->assertNotEmpty($dataDV);
		$this->assertNotEmpty($dataDV['unternehmen']);
		$this->assertEquals('gst', $dataDV['unternehmen']);
		$this->assertNull($dataDV['dienstverhaeltnisid']);
		$this->assertNotEmpty($dataDV['vertragsart_kurzbz']);
		$this->assertEquals('echterdv', $dataDV['vertragsart_kurzbz']);
        $this->assertNotEmpty($dataDV['gueltigkeit']);
		$this->assertNotEmpty($dataDV['gueltigkeit']->getGuioptions());
		$this->assertNotEmpty($dataDV['gueltigkeit']->getData());
		$this->assertNotEmpty($dataDV['gueltigkeit']->getData()['gueltig_ab']);
		// Vertragsbestandteile
		$vbs = $formDataMapper->getVbs();
		$this->assertNotEmpty($vbs);
	}
	


}