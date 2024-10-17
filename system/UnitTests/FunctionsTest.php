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



require_once(__DIR__.'/../../config/vilesci.config.inc.php');
require_once(__DIR__.'/../../include/functions.inc.php');

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
	
    public function setUp()
    {
	}
	
	public function testIncSemester()
	{
		$this->assertEquals('WS2014',incSemester('SS2014'));
		$this->assertEquals('SS2015',incSemester('WS2014'));
		$this->assertEquals('WS2015',incSemester('SS2015'));
		$this->assertEquals('SS2016',incSemester('WS2015'));		
	}
	
	public function testGenerateSemesterList()
	{
		$liste = generateSemesterList('WS2013', 3);
		$this->assertEquals(4,count($liste));		
		$this->assertEquals('WS2013',$liste[0]);		
		$this->assertEquals('SS2014',$liste[1]);		
		$this->assertEquals('WS2014',$liste[2]);		
		$this->assertEquals('SS2015',$liste[3]);		
	}
}

?>

