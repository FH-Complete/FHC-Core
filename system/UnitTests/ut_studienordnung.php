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
echo 'UnitTest - Studienordnung - ';
require_once(dirname(__FILE__).'/../../config/system.config.inc.php');
require_once(dirname(__FILE__).'/../../include/studienordnung.class.php');
$errormsg='';

class ut_studienordnung //extends PHPUnit_Framework_TestCase
{
	protected $studienordnung;
 
    public function setUp()
    {
        $this->studienordnung=new studienordnung();
    }
	
	public function testAttributes()
	{
		try 
		{
			//$studienordnung->test='test';
			$this->studienordnung->studiengang_kz='0';
			//$this->assertEquals(0, $this->studienordnung->studiengang_kz);
			$this->studienordnung->version='bla';
			$this->studienordnung->bezeichnung='bla';
			$this->studienordnung->ects='3.2';
			$this->studienordnung->gueltigvon='WS2012';
			$this->studienordnung->gueltigbis='SS2014';
			$this->studienordnung->studiengangbezeichnung='Unit Test';
			$this->studienordnung->studiengangbezeichnung_englisch='Unit Test English';
			$this->studienordnung->studiengangkurzbzlang='UnitTest';
			$this->studienordnung->akadgrad_id='0';
			$this->studienordnung->max_semester='6';
			//$this->studienordnung->validate();
			//
		}
		catch (Exception $exc)
		{
			$errormsg.=$exc->getMessage().$this->studienordnung->errormsg;
			return;
		}
	}
	
	public function testSaveStudienordnungInsert()
    {
        try 
        {
           $this->studienordnung->save();
        }
		catch (Exception $exc) 
		{
			$errormsg.=$exc->getMessage().$this->studienordnung->errormsg;
			return;
        }
 
        $this->fail();
    }
}
/*	
	
*/
$obj=new ut_studienordnung();
$obj->setUp();
$obj->testAttributes();
$obj->testSaveStudienordnungInsert();
if ($errormsg=='')
	echo 'OK<BR>';
else
	echo $errormsg
?>


