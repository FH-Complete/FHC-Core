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

$studienordnung=new studienordnung();
try 
{
	$studienordnung->studiengang_kz=0;
	$studienordnung->version='bla';
}
catch (Exception $exc)
{
	$errormsg=$exc->getMessage()'.$studienordnung->errormsg;
	die($errormsg);
}
/*	private $version; 				// varchar (256)
	private $bezeichnung;			// varchar (512)
	private $ects;					// numeric (5,2)
	private $gueltigvon;            // varchar (FK Studiensemester)
	private $gueltigbis;            // varchar (FK Studiensemester)
	private $studiengangbezeichnung;	// varchar (256)
	private $studiengangbezeichnung_english;	// varchar (256)
	private $studiengangkurzbzlang;// varchar (256)
	private $akadgrad_id;			// integer (FK akadgrad)
	private $max_semester;			// smallint
*/
echo 'OK<BR>';
?>


