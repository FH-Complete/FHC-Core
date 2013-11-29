<?php
/*
 * ut_studienordnung.php
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 * 
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studienordnung.class.php');

class StudienordnungTest extends PHPUnit_Framework_TestCase
{
	protected $studienordnung_id;
 
    public function setUp()
    {
        $studienordnung=new studienordnung();
		$studienordnung->studiengang_kz=0;
		$studienordnung->version='V1.9';
		$studienordnung->bezeichnung='UnitTestStudienordnung';
		$studienordnung->ects='3.2';
		$studienordnung->gueltigvon='WS2012';
		$studienordnung->gueltigbis='SS2014';
		$studienordnung->studiengangbezeichnung='Unit Test';
		$studienordnung->studiengangbezeichnung_englisch='Unit Test English';
		$studienordnung->studiengangkurzbzlang='UTLang';
		$studienordnung->akadgrad_id=0;
		$studienordnung->max_semester=6;
		$studienordnung->insertvon='unittest';
		$studienordnung->save();

		$this->studienordnung_id=$studienordnung->studienordnung_id;
    }
	
	public function testAttributes()
	{
		$studienordnung = new studienordnung();
		$this->assertTrue($studienordnung->loadStudienordnung($this->studienordnung_id));
		$this->assertEquals(0,$studienordnung->studiengang_kz);
		$this->assertEquals('V1.9',$studienordnung->version);
		$this->assertEquals('UnitTestStudienordnung',$studienordnung->bezeichnung);
		$this->assertEquals('3.2',$studienordnung->ects);
		$this->assertEquals('WS2012',$studienordnung->gueltigvon);
		$this->assertEquals('SS2014',$studienordnung->gueltigbis);
		$this->assertEquals('Unit Test',$studienordnung->studiengangbezeichnung);
		$this->assertEquals('Unit Test English',$studienordnung->studiengangbezeichnung_englisch);
		$this->assertEquals('UTLang',$studienordnung->studiengangkurzbzlang);
		$this->assertEquals(0,$studienordnung->akadgrad_id);
		$this->assertEquals(6,$studienordnung->max_semester);
		$this->assertEquals('unittest',$studienordnung->insertvon);
	}

	public function testUpdate()
	{
		//Datensatz anlegen
        $studienordnung=new studienordnung();
		$studienordnung->studiengang_kz=0;
		$studienordnung->version='V1.9';
		$studienordnung->bezeichnung='UnitTestStudienordnungUPD';
		$studienordnung->ects='3.2';
		$studienordnung->gueltigvon='WS2012';
		$studienordnung->gueltigbis='SS2014';
		$studienordnung->studiengangbezeichnung='Unit Test';
		$studienordnung->studiengangbezeichnung_englisch='Unit Test English';
		$studienordnung->studiengangkurzbzlang='UTLang';
		$studienordnung->akadgrad_id=0;
		$studienordnung->max_semester=6;
		$studienordnung->insertvon='unittest';
		$studienordnung->save();

		$studienordnung_id=$studienordnung->studienordnung_id;

		//Aktualisieren
		$studienordnung = new studienordnung();
		$studienordnung->loadStudienordnung($studienordnung_id);
		$studienordnung->bezeichnung='UnitTestStudienordnungäöü\'"éè$"!';
		$studienordnung->updatevon='unittest';
		$studienordnung->save();

		//Ergebnis vergleichen
		$studienordnung = new studienordnung();
		$studienordnung->loadStudienordnung($studienordnung_id);
		$this->assertEquals('UnitTestStudienordnungäöü\'"éè$"!',$studienordnung->bezeichnung);
		$this->assertEquals('unittest',$studienordnung->updatevon);
	}

	public function testLoadStudienordnungStudiengang()
	{
		//Datensatz anlegen
        $studienordnung=new studienordnung();
		$this->assertTrue($studienordnung->loadStudienordnungSTG(0));		
	}

	
}
?>
