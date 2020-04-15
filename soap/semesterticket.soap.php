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
require_once('../include/adresse.class.php');
require_once('../include/person.class.php');
require_once('../include/webservicelog.class.php');

ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT."/soap/semesterticket.wsdl.php?".microtime(true));
$SOAPServer->addFunction("verifyData");
$SOAPServer->handle();

$fehler = '';


/**
 *
 * Nimmt Anfrage entgegen und überprüft ob Student auch wirklich Student ist (anhand Matrikelnummer)
 * @param $parameters
 */
function verifyData($parameters)
{
	global $fehler;

	$obj = new stdClass();

	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	// Eintrag in der LogTabelle anlegen
	$log = new webservicelog();
	$log->request_data = file_get_contents('php://input');
	$log->webservicetyp_kurzbz = 'wienerlinien';
	$log->request_id = $parameters->token;
	$log->beschreibung = "Semesterticketanfrage";
	$log->save(true);

	if(!validateRequest($parameters))
	{
		$obj->result = 'false';
		$obj->fehler = $fehler;
	}
	else
	{
		// nach Personenkennzeichen suchen
		$student = new student();
		$student_uid = $student->getUidFromMatrikelnummer($parameters->Matrikelnummer);
		$studenten_arr = array();

		if($student_uid !== false)
		{
			// überprüfe ob Benutzer aktiv ist
			$benutzer = new benutzer();
			$benutzer->load($student_uid);
			if(!$benutzer->bnaktiv)
			{
				$obj->result = 'false';
				$obj->fehler ='1';
				return $obj;
			}
			$person = new person();
			if(!$person->load($benutzer->person_id))
			{
				$obj->result = 'false';
				$obj->fehler ='3';
				return $obj;
			}
			$studenten_arr[] = $student_uid;
		}
		else
		{
			// nach richtiger Matrikelnummer suchen
			$person = new person();
			if(!$person->getPersonByMatrNr($parameters->Matrikelnummer))
			{
				$obj->result = 'false';
				$obj->fehler = '9';
				return $obj;
			}

			// Aktive Accounts der Person laden
			$benutzer = new benutzer();
			$benutzer->getBenutzerFromPerson($person->person_id);
			foreach($benutzer->result as $row)
			{
				$studenten_arr[] = $row->uid;
			}
		}

		// überprüfe vorname
		if(mb_strtolower($person->vorname) != mb_strtolower($parameters->Vorname))
		{
			// es wurde keine übereinstimmung gefunden
			$obj->result = 'false';
			$obj->fehler = '6';
			return $obj;
		}

		if(mb_strtolower($person->nachname) != mb_strtolower($parameters->Name))
		{
			// es wurde keine übereinstimmung gefunden
			$obj->result = 'false';
			$obj->fehler = '7';
			return $obj;
		}

		// Überprüfe PLZ
		$adresse = new adresse();
		$adresse->load_pers($person->person_id);

		$foundAdr = false;
		foreach($adresse->result as $adr)
		{
			if($adr->plz == $parameters->Postleitzahl && $adr->typ == 'h')
				$foundAdr = true;
		}
		if($foundAdr == false)
		{
			// es wurde keine übereinstimmung gefunden
			$obj->result = 'false';
			$obj->fehler = '5';
			return $obj;
		}

		// Überprüfe Geburtsdatum
		if($person->gebdatum != $parameters->Geburtsdatum)
		{
			$obj->result = 'false';
			$obj->fehler = '4';
			return $obj;
		}

		foreach($studenten_arr as $student_uid)
		{
			// hole prestudentID
			if(!$student->load($student_uid))
				continue;

			// Übergabe von studiensemester -> z.b 11W, 12S auf WS2011, SS2012
			$year = mb_substr($parameters->Semesterkuerzel, 0,2);
			$semester = mb_substr($parameters->Semesterkuerzel,2,1);
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
				return $obj;
			}
			else
			{
				continue;
			}
		}

		// es wurde kein passender student gefunden
		$obj->result = 'false';
		$obj->fehler = '1';
		return $obj;
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

	if(mb_strlen($parameter->Postleitzahl) > 10)
	{
		$fehler = '5';
		return false;
	}
	if(mb_strlen($parameter->Vorname) > 255)
	{
		$fehler = '6';
		return false;
	}
	if(mb_strlen($parameter->Name) > 255)
	{
		$fehler = '7';
		return false;
	}

	if(mb_strlen($parameter->Matrikelnummer) >15  || $parameter->Matrikelnummer == '')
	{
		$fehler = '9';
		return false;
	}

	return true;
}

?>
