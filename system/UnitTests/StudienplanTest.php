<?php
/*
 * StudienplanTest.php
 * 
 * Copyright 2013 fhcomplete.org
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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 * 
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studienplan.class.php');

class StudienplanTest extends PHPUnit_Framework_TestCase
{
	protected $studienplan_id;
 
    public function setUp()
    {
        $studienplan=new studienplan();
		$studienplan->studienordnung_id = 1;
		$studienplan->orgform_kurzbz = 'VZ';
		$studienplan->version = 'VZ-08102013-01';
		$studienplan->bezeichnung = 'Vollzeit Entwurf 1';
		$studienplan->regelstudiendauer = 4;
		$studienplan->sprache = 'German';
		$studienplan->aktiv = false;
		$studienplan->semesterwochen = 15;
		$studienplan->testtool_sprachwahl = true;
		$studienplan->insertvon = 'unittest';
		$studienplan->save();

		$this->studienplan_id=$studienplan->studienplan_id;
    }
	
	public function testAttributes()
	{
		$studienplan = new studienplan();
		$this->assertTrue($studienplan->loadStudienplan($this->studienplan_id));
		$this->assertEquals(1,$studienplan->studienordnung_id);
		$this->assertEquals('VZ',$studienplan->orgform_kurzbz);
		$this->assertEquals('VZ-08102013-01',$studienplan->version);
		$this->assertEquals('Vollzeit Entwurf 1',$studienplan->bezeichnung);
		$this->assertEquals(4,$studienplan->regelstudiendauer);
		$this->assertEquals('German',$studienplan->sprache);
		$this->assertFalse($studienplan->aktiv);
		$this->assertEquals(15,$studienplan->semesterwochen);
		$this->assertTrue($studienplan->testtool_sprachwahl);
		$this->assertEquals('unittest',$studienplan->insertvon);
	}

	public function testUpdate()
	{
        $studienplan=new studienplan();
		$studienplan->studienordnung_id = 1;
		$studienplan->orgform_kurzbz = 'VZ';
		$studienplan->version = 'VZ-08102013-01';
		$studienplan->bezeichnung = 'Vollzeit Entwurf 1';
		$studienplan->regelstudiendauer = 4;
		$studienplan->sprache = 'German';
		$studienplan->aktiv = false;
		$studienplan->semesterwochen = 15;
		$studienplan->testtool_sprachwahl = true;
		$studienplan->insertvon = 'unittest';
		$studienplan->save();

		$this->studienplan_id=$studienplan->studienplan_id;

		//Aktualisieren
		$studienplan = new studienplan();
		$studienplan->loadStudienplan($this->studienplan_id);
		$studienplan->bezeichnung='UnitTeststudienplanäöü\'"éè$"!';
		$studienplan->updatevon='unittest';
		$studienplan->save();

		//Ergebnis vergleichen
		$studienplan = new studienplan();
		$studienplan->loadStudienplan($this->studienplan_id);
		$this->assertEquals('UnitTeststudienplanäöü\'"éè$"!',$studienplan->bezeichnung);
		$this->assertEquals('unittest',$studienplan->updatevon);
	}	
}
?>
