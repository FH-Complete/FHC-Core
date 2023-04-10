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
require_once( __DIR__.'/../../config/system.config.inc.php');
require_once( __DIR__.'/../../include/appdaten.class.php');
require_once( __DIR__.'/../../include/benutzer.class.php');

use PHPUnit\Framework\TestCase;

class AppdatenTest extends TestCase
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
	
	public function testCRUD()
	{		
		
		$app = 'TestApp';
		$bezeichnung = 'TestDaten';
		$daten = '<dummy>Hello World</dummy>';
		$version = '2';
		$appversion = '1.0';
		$insertvon = 'unittest';
		
		// Create
		$appdaten = new appdaten();
		$appdaten->uid = $this->benutzer->uid;
		$appdaten->app = $app;
		$appdaten->appversion = $appversion;  // string
		$appdaten->version = $version;  // integer
		$appdaten->bezeichnung = $bezeichnung;
		$appdaten->daten = $daten;
		$appdaten->freigabe = false;
		$appdaten->insertvon = $insertvon;
		$appdaten_id = $appdaten->save();
		$this->assertNotEquals($appdaten_id, false);
		
		// load
		$this->assertTrue($appdaten->load($appdaten_id));
		$this->assertEquals($this->benutzer->uid,$appdaten->uid);
		$this->assertEquals($appversion,$appdaten->appversion);
		$this->assertEquals($version,$appdaten->version);
		$this->assertEquals($app,$appdaten->app);
		$this->assertEquals($bezeichnung,$appdaten->bezeichnung);
		$this->assertEquals($daten,$appdaten->daten);
		$this->assertEquals($insertvon,$appdaten->insertvon);
		// test delete + cleanup test data
		$this->assertNotEquals($appdaten->delete($appdaten_id), false);

	}
	
	public function testSaveNeueVersion()
	{
		$app = 'TestApp';
		$bezeichnung = 'TestDaten';
		$daten = '<dummy>Hello World</dummy>';		
		$appversion = '1.0';		
		$insertvon = 'unittest';
		
		// neuen Datensatz anlegen und Version automatisch setzen
		$appdaten = new appdaten();
		$appdaten->uid = $this->benutzer->uid;
		$appdaten->app = $app;
		$appdaten->appversion = $appversion;  // string
		//$appdaten->version = $version;  // integer
		$appdaten->bezeichnung = $bezeichnung;
		$appdaten->daten = $daten;
		$appdaten->freigabe = false;
		$appdaten->insertvon = $insertvon;
		$appdaten_id1 = $appdaten->save(true);
		$this->assertNotEquals($appdaten_id1, false, 'Speichern der Appdaten fehlgeschlagen: '.$appdaten->errormsg);	
		$this->assertEquals($appdaten->version, 1, 'Version des ersten Datensatzes ist ungleich 1');
		// noch ein Datensatz
		$appdaten = new appdaten();
		$appdaten->uid = $this->benutzer->uid;
		$appdaten->app = $app;
		$appdaten->appversion = $appversion;  // string		
		$appdaten->bezeichnung = $bezeichnung;
		$appdaten->daten = $daten;
		$appdaten->freigabe = false;
		$appdaten->insertvon = $insertvon;
		$appdaten_id2 = $appdaten->save(true);
		$this->assertNotEquals($appdaten_id2, false);
		$this->assertEquals($appdaten->version,2, 'Version des ersten Datensatzes ist ungleich 2');
		// selben Datensatz in neue Version kopieren
		$appdaten_id3 = $appdaten->save(true);
		$this->assertNotEquals($appdaten_id3, false);
		$this->assertEquals($appdaten->version,3, 'Version des kopierten Datensatzes ist ungleich 3');
		// cleanup siehe teardown
		
	}
	
	public function testGetGetAllByApp() {		
		$this->assertNotEquals($this->createAppdaten('2013/14'), false);
		$this->assertNotEquals($this->createAppdaten('2013/14'), false);
		$this->assertNotEquals($this->createAppdaten('2013/14'), false);
		
		$this->assertNotEquals($this->createAppdaten('2014/15'), false);
		$this->assertNotEquals($this->createAppdaten('2014/15'), false);
		
		$appdaten = new appdaten();
		$apps = $appdaten->getAllByApp('TestApp1');
		$this->assertTrue(is_array($apps));
		$this->assertEquals(count($apps), 5);
		$this->assertEquals($apps[2]->version, 3);
		$this->assertEquals($apps[4]->bezeichnung, '2014/15');
		$this->assertEquals($apps[4]->version, 2);
		
	}
	

	
	private function createAppdaten($bezeichnung) {
		$app = 'TestApp1';		
		$daten = '<dummy>Hello World</dummy>';		
		$appversion = '1.0';		
		$insertvon = 'unittest';
		
		// neuen Datensatz anlegen
		$appdaten = new appdaten();
		$appdaten->uid = $this->benutzer->uid;
		$appdaten->app = $app;
		$appdaten->appversion = $appversion;  
		$appdaten->bezeichnung = $bezeichnung;
		$appdaten->daten = $daten;
		$appdaten->freigabe = false;
		$appdaten->insertvon = $insertvon;
		$appdaten->save(true);
		return $appdaten;
	}

	
}
?>

