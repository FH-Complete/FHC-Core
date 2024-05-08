<?php
/*
 * AqaFoebisPersonTest.php
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
require_once('../../../../include/studienplatz.class.php');
require_once('../../../../include/benutzer.class.php');
require_once('../../../../addons/studienplatzverwaltung/include/aqa_foebis_person.class.php');

class AqaFoebisPersonTest extends PHPUnit_Framework_TestCase
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
			// benutzer hat On Delete Restrict zu den appdaten!!!
		}
	}
	
	public function testCRUD()
	{		
		$matrikelnr = '012342343245';
		$studiengang_kz = '120';  // 120 = dummy
		$studiensemester_kurzbz = 'WS2014';
		$orgform_kurzbz = 'BB';
		$regelstudiendauer = 6;
		$ausbildungssemester = 2;
		$guthaben = 34;
		$gefoerdert = 1;
		$maxguthaben = 46;
		$stud_status = 1;
		$meldedatum = mktime(0, 0, 0, 10, 14, 2014);
		$insertvon = 'unittest';
	
		// Create
		$foebis = new aqa_foebis_person();
		$foebis->matrikelnr = $matrikelnr;
		$foebis->studiengang_kz = $studiengang_kz;
		$foebis->studiensemester_kurzbz = $studiensemester_kurzbz;
		$foebis->orgform_kurzbz = $orgform_kurzbz;
		$foebis->regelstudiendauer = $regelstudiendauer;
		$foebis->ausbildungssemester = $ausbildungssemester;
		$foebis->guthaben = $guthaben;
		$foebis->gefoerdert = $gefoerdert;
		$foebis->maxguthaben = $maxguthaben;
		$foebis->stud_status = $stud_status;
		$foebis->meldedatum = $meldedatum;
		
		$foebis->insertvon = $insertvon;
		$foebis_person_id = $foebis->save();
		$this->assertNotEquals($foebis_person_id, false);
		
		// load
		$this->assertTrue($foebis->load_foebis($foebis_person_id));	
		$this->assertEquals($studiengang_kz,$foebis->studiengang_kz);	
		$this->assertEquals($studiensemester_kurzbz,$foebis->studiensemester_kurzbz);
		$this->assertEquals($orgform_kurzbz,$foebis->orgform_kurzbz);
		$this->assertEquals($regelstudiendauer,$foebis->regelstudiendauer);
		
		$this->assertEquals($ausbildungssemester,$foebis->ausbildungssemester);
		$this->assertEquals($guthaben,$foebis->guthaben);
		$this->assertEquals($gefoerdert,$foebis->gefoerdert);
		$this->assertEquals($maxguthaben,$foebis->maxguthaben);
		$this->assertEquals($stud_status,$foebis->stud_status);
		$this->assertEquals($meldedatum, $foebis->meldedatum);
		$guthabenNeu = 28;
		
		$foebis->guthaben = $guthabenNeu;
		$foebis->save();
		$this->assertTrue($foebis->load_foebis($foebis_person_id));		
		$this->assertEquals($guthabenNeu,$foebis->guthaben);
	
		// test delete + cleanup test data
		$this->assertNotEquals($foebis->delete($foebis_person_id), false);

	}
	
	
	
}
?>

