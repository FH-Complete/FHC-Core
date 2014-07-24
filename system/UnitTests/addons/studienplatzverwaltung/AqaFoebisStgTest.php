<?php
/*
 * AqaFoebisStgTest.php
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
require_once('../../../../addons/studienplatzverwaltung/include/aqa_foebis_stg.class.php');

class AqaFoebisStgTest extends PHPUnit_Framework_TestCase
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
		$stgartbez = "m";
		$regelstudiendauer = 5;
		$jahr = 2014;
		$monat = 11;
		$runde = 1;
		$npz = 200;
		$aq = 18;
		$foerdergruppe = 'technisch';
		$r1_plaetze_bezahlt = 201;
		$r2_plaetze_bezahlt = 202;
		$r3_plaetze_bezahlt = 203;		
		$r2_foebisaktive = 206;
		$r3_foebisaktive = 207;
		$r2r1_foebisaktive_korr = 4;
		$r3r2_foebisaktive_korr = 5;
		$foebisaktive = 163;
		$insertvon = 'unittest';
	
		// Create
		$foebis = new aqa_foebis_stg();
		$foebis->studiengang_kz = $studiengang_kz;
		$foebis->studiensemester_kurzbz = $studiensemester_kurzbz;
		$foebis->orgform_kurzbz = $orgform_kurzbz;
		$foebis->regelstudiendauer = $regelstudiendauer;
		$foebis->stgartbez = $stgartbez;
		$foebis->foebisaktive = $foebisaktive;
		$foebis->jahr = $jahr;
		$foebis->monat = $monat;
		$foebis->runde = $runde;
		$foebis->npz = $npz;
		$foebis->aq = $aq;
		$foebis->foerdergruppe = $foerdergruppe;
		$foebis->r1_plaetze_bezahlt = $r1_plaetze_bezahlt;
		$foebis->r2_plaetze_bezahlt = $r2_plaetze_bezahlt;
		$foebis->r3_plaetze_bezahlt = $r3_plaetze_bezahlt;
		$foebis->r2_foebisaktive = $r2_foebisaktive;
		$foebis->r3_foebisaktive = $r3_foebisaktive;
		$foebis->r2r1_foebisaktive_korr = $r2r1_foebisaktive_korr;
		$foebis->r3r2_foebisaktive_korr = $r3r2_foebisaktive_korr;
		
		$foebis->insertvon = $insertvon;
		$foebis_stg_id = $foebis->save();
		$this->assertNotEquals($foebis_stg_id, false);
		
		// load
		$this->assertTrue($foebis->load_foebis($foebis_stg_id));		
		$this->assertEquals($studiensemester_kurzbz,$foebis->studiensemester_kurzbz);
		$this->assertEquals($orgform_kurzbz,$foebis->orgform_kurzbz);
		$this->assertEquals($studiengang_kz,$foebis->studiengang_kz);
		$this->assertEquals($regelstudiendauer,$foebis->regelstudiendauer);
		$this->assertEquals($jahr,$foebis->jahr);
		$this->assertEquals($monat,$foebis->monat);
		$this->assertEquals($runde,$foebis->runde);
		$this->assertEquals($npz,$foebis->npz);
		$this->assertEquals($aq,$foebis->aq);
		$this->assertEquals($foerdergruppe,$foebis->foerdergruppe);
		$this->assertEquals($r1_plaetze_bezahlt, $foebis->r1_plaetze_bezahlt);
		$this->assertEquals($r2_plaetze_bezahlt,$foebis->r2_plaetze_bezahlt);
		$this->assertEquals($r3_plaetze_bezahlt,$foebis->r3_plaetze_bezahlt);
		$this->assertEquals($r2_foebisaktive,$foebis->r2_foebisaktive);
		$this->assertEquals($r3_foebisaktive,$foebis->r3_foebisaktive);
		$this->assertEquals($r2r1_foebisaktive_korr,$foebis->r2r1_foebisaktive_korr);
		$this->assertEquals($r3r2_foebisaktive_korr,$foebis->r3r2_foebisaktive_korr);
		
		$npzNeu = 205;
		
		$foebis->npz = $npzNeu;
		$foebis->save();
		$this->assertTrue($foebis->load_foebis($foebis_stg_id));		
		$this->assertEquals($npzNeu,$foebis->npz);
	
		// test delete + cleanup test data
		$this->assertNotEquals($foebis->delete($foebis_stg_id), false);

	}
	
	
	
}
?>

