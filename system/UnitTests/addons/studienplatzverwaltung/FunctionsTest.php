<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Werner Masik <werner@gefi.at>,
 */

require_once('../../../../addons/studienplatzverwaltung/include/functions.inc.php');

class FunctionsTest extends PHPUnit_Framework_TestCase
{
	
    public function setUp()
    {
	}
	
	public function testFormatStudiengangKz()
	{
		$this->assertEquals('0227',formatStudiengangKz(227));
	}
	
	public function testSemester2BISDatum()
	{
		$this->assertEquals('15.11.2013',semester2BISDatum('WS2013'));
		$this->assertEquals('15.04.2014',semester2BISDatum('SS2014'));		
	}

	public function testBisDatum2Semester()
	{
		date_default_timezone_set('UTC');
		$datum = mktime(0,0,0,11,15,2013);
		$this->assertEquals('WS2013',bisDatum2Semester($datum));
		$datum = mktime(0,0,0,4,15,2013);
		$this->assertEquals('SS2013',bisDatum2Semester($datum));
	}
}

?>

