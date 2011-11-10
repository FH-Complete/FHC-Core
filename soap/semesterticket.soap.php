<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>.
 */
 
require_once('../config/vilesci.config.inc.php'); 
require_once('../include/student.class.php'); 
require_once('../include/benutzer.class.php');

ini_set("soap.wsdl_cache_enabled", "0");
	
$SOAPServer = new SoapServer(APP_ROOT."/soap/semesterticket.wsdl.php?".microtime());
$SOAPServer->addFunction("verifyData");
$SOAPServer->handle();

$fehler = ''; 

/**
 * 
 * Nimmt Anfrage entgegen und überprüft ob Student auch wirklich Student ist (anhand Matrikelnummer) 
 * @param unknown_type $parameters
 */
function verifyData($parameters)
{ 	
	global $fehler; 
	class foo{};
	
	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
	$obj = new foo(); 
	
	if(!validateRequest($parameters))
	{
		$obj->result = 'false';
		$obj->fehler = $fehler; 
	}
	else
	{
		$student = new student(); 
		$student_uid = $student->getUidFromMatrikelnummer($parameters->matrikelnummer); 
		// überprüfe ob Benutzer aktiv ist
		$benutzer = new benutzer(); 
		$benutzer->load($student_uid); 
		if(!$benutzer->bnaktiv)
		{
			$obj->result = 'false';
			$obj->fehler ='1';
			return $obj; 
		}	
		// hole prestudentID
		$student->load($student_uid); 
		if($student->prestudent_id == '')
		{
			// es wurde kein student gefunden
			$obj->result = 'false';
			$obj->fehler = '3'; 
			return $obj; 
		}
		
		// Übergabe von studiensemester -> z.b 11W, 12S auf WS2011, SS2012
		$year = mb_substr($parameters->semesterkuerzel, 0,2); 
		$semester = mb_substr($parameters->semesterkuerzel,2,1); 
		if($semester == 'S')
		{
			$semester = 'SS'; 
		}
		else if($semester == 'W')
		{
			$semester= 'WS'; 
		}
		else
		{
			// ungültiges Semester
			$obj->result = 'false';
			$obj->fehler = '8'; 
			return $obj; 
		}
		$studiensemester = $semester.'20'.$year; 
		
		// letzten Status holen
		$qry = "Select public.get_rolle_prestudent ('".$student->prestudent_id."', '".$studiensemester."')"; 
		
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				$status=$row->get_rolle_prestudent;
			}
		}
		// Status Student und Diplomand gültig
		if($status == 'Student' || $status == 'Diplomand')
		{
			$obj->result = 'true'; 
			$obj->fehler = '';
		}
		else
		{
			$obj->result = 'false';
			$obj->fehler ='1';
		}	
	}
	return $obj;
}

/**
 * 
 * Prüft die übergebenen Parameter auf Richtigkeit
 * @param $parameter
 */
function validateRequest($parameter)
{
	/**
	 * Fehlercodes
	 * 	1: Kein aufrechtes Studium
	 *	2: Fehlerhafter Request
	 *	3: Student wurde nicht gefunden
	 *	4: Fehler Geburtsdatum
	 *	5: Fehler Postleitzahl
	 *	6: Fehler Vorname
	 *	7: Fehler Nachname
	 *	8: Fehler Semester
	 *	9: Fehler Matrikelnummer
	 */
	
	global $fehler; 
	
	if(mb_strlen($parameter->postleitzahl) > 10)
	{
		$fehler = '5';
		return false; 
	}
	if(mb_strlen($parameter->vorname) > 255)
	{
		$fehler = '6';
		return false; 
	}
	if(mb_strlen($parameter->name) > 255)
	{
		$fehler = '7';
		return false; 
	}
	if($parameter->semesterkuerzel != '')
	{
		if(mb_strlen($parameter->semesterkuerzel) != 3 || ((mb_strpos($parameter->semesterkuerzel, 'W') != '2') && (mb_strpos($parameter->semesterkuerzel, 'S') != '2')))
		{
			$fehler = '8'; 
			return false; 
		}
	}
	if(mb_strlen($parameter->matrikelnummer) > 15 || $parameter->matrikelnummer == '' || !is_numeric($parameter->matrikelnummer))
	{
		$fehler = '9';
		return false; 
	}

	return true;
}

?>


