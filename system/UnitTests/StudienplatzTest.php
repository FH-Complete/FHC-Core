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
require_once('../../config/system.config.inc.php');
require_once('../../include/studienplatz.class.php');
require_once('../../include/benutzer.class.php');


class StudienplatzTest extends PHPUnit_Framework_TestCase
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
		
		
		$studiengang_kz = '120';  // 120 = dummy
		$studiensemester_kurzbz = 'WS2014';
		$orgform_kurzbz = 'BB';
		$ausbildungssemester = 2;
		$npz = 123;
		$gpz = 234;
		$insertvon = 'unittest';
	
		// Create
		$studienplatz = new studienplatz();
		$studienplatz->studiengang_kz = $studiengang_kz;
		$studienplatz->studiensemester_kurzbz = $studiensemester_kurzbz;
		$studienplatz->orgform_kurzbz = $orgform_kurzbz;
		$studienplatz->ausbildungssemester = $ausbildungssemester;
		$studienplatz->npz = $npz;
		$studienplatz->gpz = $gpz;
		$studienplatz->insertvon = $insertvon;
		$studienplatz_id = $studienplatz->save();
		$this->assertNotEquals($studienplatz_id, false);
		
		// load
		$this->assertTrue($studienplatz->loadStudienplatz($studienplatz_id));		
		$this->assertEquals($studiensemester_kurzbz,$studienplatz->studiensemester_kurzbz);
		$this->assertEquals($orgform_kurzbz,$studienplatz->orgform_kurzbz);
		$this->assertEquals($studiengang_kz,$studienplatz->studiengang_kz);
		$this->assertEquals($ausbildungssemester,$studienplatz->ausbildungssemester);
		$this->assertEquals($npz,$studienplatz->npz);
		$this->assertEquals($gpz,$studienplatz->gpz);
	
		// test delete + cleanup test data
		$this->assertNotEquals($studienplatz->delete($studienplatz_id), false);

	}
	
	
	
}
?>

