<?php
/*
 * AppdatenTest.php
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

class StudienplatzverwaltungAPITest extends PHPUnit_Framework_TestCase
{
	protected $benutzer;
	protected $uid = 'unittest';
 
	public function setUp()
    {
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
    }
	
	public function tearDown() 
	{
		if ($this->benutzer) {			
			// Benutzer löschen
			$this->assertTrue(
				$this->benutzer->deleteBenutzer($this->uid));
			// Person löschen
			$this->assertTrue(
				$this->benutzer->delete($this->benutzer->person_id));
			// benutzer hat On Delete Cascade zu den appdaten!!!
		}
	}
	
	
	public function testParseBasisDaten() {
		$api = StudienplatzverwaltungAPI::init();
		$result = $api->parseBasisdatenUV('testfile.xml');
		$this->assertTrue($result);
	}
	
	public function testStudiengangSPVGetAll()
	{
		$studiengangSPV = new studiengangSPV();
		$this->assertTrue($studiengangSPV->getAll('2013/14',2));
	}
		
	public function testNewUV()
	{
		$api = StudienplatzverwaltungAPI::init();
		$appdaten = $api->newUV('2013/14',2, $this->uid);
		$this->assertNotNull($appdaten);
	}
	
	public function testDownloadUV()
	{
		$api = StudienplatzverwaltungAPI::init();
		$appdaten = $api->newUV('2013/14',3, $this->uid);
		$this->assertNotNull($appdaten);
		$result = $api->exportXML('2013/14',1, $this->uid);
		printf($result);
		
	}

	public function testExportCSV()
	{
		$api = StudienplatzverwaltungAPI::init();
		$appdaten = $api->newUV('2013/14',3, $this->uid);
		$this->assertNotNull($appdaten);
		$result = $api->exportCSV('2013/14',1, $this->uid);
		printf($result);
		
	}
	
	public function testGetInfoData()
	{
		$api = StudienplatzverwaltungAPI::init();
		$appdaten = $api->getInfoDaten('2011/12',3);
		$this->assertNotNull($appdaten);
		var_dump($appdaten);		
	}
	
	
	
	public function testGetMetadata() {		
		$sj = '2013/14';
		$this->assertNotEquals($this->createAppdaten($sj, $this->createDaten($sj)), false);
		$this->assertNotEquals($this->createAppdaten($sj, $this->createDaten($sj)), false);
		$this->assertNotEquals($this->createAppdaten($sj, $this->createDaten($sj)), false);
		$sj = '2014/15';
		$this->assertNotEquals($this->createAppdaten($sj, $this->createDaten($sj)), false);
		$this->assertNotEquals($this->createAppdaten($sj, $this->createDaten($sj)), false);
		$api = StudienplatzverwaltungAPI::init();
		$metadataJSON = $api->getMetadata();
		$this->assertNotEquals($metadataJSON, false);
		$metadata = json_decode($metadataJSON, false);
		$this->assertTrue(is_array($metadata));
		$this->assertEquals($metadata[0]->studienjahr,'2013/14');
		$this->assertEquals($metadata[1]->studienjahr,'2014/15');
	}
	
	private function createDaten($studienjahr) {
		$daten = 
			array(
				'studienjahr' => $studienjahr,
				'zeitraum' => 2,
				'status' => 'Entwurf',
				'notizen' => '',
				'gesamtDaten' => array(
					array(
						'stgKz' => '0227',
						'stgArt' => 'Ba',
						'orgForm' => 'VZ',
						'studiengangDaten' => $this->createStgDaten(164, 168, $studienjahr)
					),
					array(
						'stgKz' => '0228',
						'stgArt' => 'Ba',
						'orgForm' => 'BB',
						'studiengangDaten' => $this->createStgDaten(60, 65, $studienjahr)
					)
				)
			);			
			
		
		return json_encode($daten);
	}
	
	private function createStgDaten($gpzBd, $gpzUv, $sj) {
		$jahr = substr($sj,0,4) + 0;
		return array(
			array('studiensemester' => 'WS'.$jahr++,
				  'gpzBd' => $gpzBd,
				  'gpzUV' => $gpzUv,
				  'npzBd' => $gpzBd,
				  'npzUv' => $gpzUv,
				  'aufnahme' => true),
			array('studiensemester' => 'SS'.$jahr,
				  'gpzBd' => $gpzBd,
				  'gpzUV' => $gpzUv,
				  'npzBd' => $gpzBd,
				  'npzUv' => $gpzUv,
				  'aufnahme' => true),
			array('studiensemester' => 'WS'.$jahr++,
				  'gpzBd' => $gpzBd,
				  'gpzUV' => $gpzUv,
				  'npzBd' => $gpzBd,
				  'npzUv' => $gpzUv,
				  'aufnahme' => true),
			array('studiensemester' => 'SS'.$jahr,
				  'gpzBd' => $gpzBd,
				  'gpzUV' => $gpzUv,
				  'npzBd' => $gpzBd,
				  'npzUv' => $gpzUv,
				  'aufnahme' => true),
		);
	}
	
	private function createAppdaten($bezeichnung, $gesamtdaten) {
		$app = 'Studienplatzverwaltung';		
		$appversion = '1.0';		
		$insertvon = 'unittest';
		
		// neuen Datensatz anlegen
		$appdaten = new appdaten();
		$appdaten->uid = $this->benutzer->uid;
		$appdaten->app = $app;
		$appdaten->appversion = $appversion;  
		$appdaten->bezeichnung = $bezeichnung;
		$appdaten->daten = $gesamtdaten;
		$appdaten->freigabe = false;
		$appdaten->insertvon = $insertvon;
		$appdaten->save(true);
		return $appdaten;
	}
	
}

?>
