<?php
/*
 * AqaClientTest.php
 * 
 * Copyright 2014 fhcomplete.org
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * Authors: Werner Masik <werner.masik@gefi.at>
 * 
 */
require_once('../../../../config/system.config.inc.php');
require_once('../../../../include/appdaten.class.php');
require_once('../../../../include/benutzer.class.php');
require_once('../../../../addons/studienplatzverwaltung/vilesci/StudienplatzverwaltungAPI.class.php');
require_once('../../../../addons/studienplatzverwaltung/studienplatzverwaltung.config.inc.php');
require_once('../../../../addons/studienplatzverwaltung/soap/AqaFoebisClient.php');


class AqaClientTest extends PHPUnit_Framework_TestCase
{
	protected $benutzer;
	protected $uid = 'unittest';
 
	public function setUp()
    {
        /*
        $this->benutzer = new benutzer();
		$this->benutzer->new = true;
		$this->benutzer->uid = $this->uid;
		$this->benutzer->alias = 'Unit Test Benutzer';
		$this->benutzer->aktiv = true;
		$this->benutzer->nachname = 'Unit';
		$this->benutzer->geschlecht = 'm';
		$result = $this->benutzer->save();
		if (!$result) {
			echo 'Fehler: '.$this->benutzer->errormsg;
		}
		$this->assertTrue($result);		
        */
    }
	
	public function tearDown() 
	{
/*		if ($this->benutzer) {			
			// Benutzer löschen
			$this->assertTrue(
				$this->benutzer->deleteBenutzer($this->uid));
			// Person löschen
			$this->assertTrue(
				$this->benutzer->delete($this->benutzer->person_id));
			// benutzer hat On Delete Cascade zu den appdaten!!!
		}*/
	}
	
	
	public function testPing() {
        $params->userName = AQA_USERNAME;
        $params->passWord = AQA_PASSWORD;
        $params->stgKz = '0227';
        $params->studJahrCode = '19';
        $params->runde = '2';

        $client = new SoapClient(AQA_ERHALTERSERVICE,array( 
                    'trace'          => 1,
                    'exceptions'     => 0
        ));
        $result = $client->ListFoebisAbrechnungStudiengang($params);
	    $this->soapDebug($client);
    	$this->assertNotNull($result);
		$xml = simplexml_load_string($this->removeNamespace($result->ListFoebisAbrechnungStudiengangResult->any));
		$details = $xml->Tablix1->Group1_Collection->Group1->Details_Collection->Details;		
		foreach ($details as $detail)
		{
			var_dump($detail);
		}		
	}
	
	public function testClient() {
		$stgKz = '0228';
		$studjahrCode = 2012;
		$runde = 3;
		$client = new AqaFoebisClient();
		$result = $client->listFoebisAbrechnungStudiengang($stgKz,$studjahrCode,$runde);
	}

	public function testPersonClient() {
		$personKz = '1110256020';		
		$client = new AqaFoebisClient();
		$result = $client->listFoebisAbrechnungPerson($personKz);
	}

	public function testSemesterClient() {
		$stgKz = 256;
		$semester = 'WS2013';		
		$client = new AqaFoebisClient();
		$result = $client->listFoebisAbrechnungSemester($stgKz, $semester);
		$this->assertTrue($result);
	}

	public function testSyncAll() {

		$client = StudienplatzverwaltungAPI::init();
		$result = $client->syncFoebisStg();
		$this->assertTrue($result);
	}

	
	function removeNamespace($xml_string) {
		// Gets rid of all namespace definitions 
		$xml_string = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml_string);

		// Gets rid of all namespace references
		$xml_string = preg_replace('/[a-zA-Z]+:([a-zA-Z]+[=>])/', '$1', $xml_string);
		return $xml_string;
	}
	
    function soapDebug($client){

        $requestHeaders = $client->__getLastRequestHeaders();
        $request = $client->__getLastRequest();
        $responseHeaders = $client->__getLastResponseHeaders();
        $response = $client->__getLastResponse();

        echo '<code>' . nl2br(htmlspecialchars($requestHeaders, true)) . '</code>';
        echo highlight_string($request, true) . "<br/>\n";

        echo '<code>' . nl2br(htmlspecialchars($responseHeaders, true)) . '</code>' . "<br/>\n";
        echo highlight_string($response, true) . "<br/>\n";
    }

}

?>
