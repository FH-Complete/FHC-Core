<?php

/* Copyright (C) 2012 Technikum-Wien
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
 * Authors:        Karl Burkhart <burkhart@technikum-wien.at> and
 *                 Andreas Moik <moik@technikum-wien.at>.
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/prestudent.class.php');
require_once('../include/webservicerecht.class.php');
require_once('../include/studiensemester.class.php');
ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT."/soap/student.wsdl.php?".microtime());
$SOAPServer->addFunction("getStudentFromUid");
$SOAPServer->addFunction("getStudentFromMatrikelnummer");
$SOAPServer->addFunction("getStudentFromStudiengang");
$SOAPServer->handle();

/**
 * Lädt einen Studenten anhand übergebener UID
 * @param $student_uid
 * @param $authentifizierung
 */
function getStudentFromUid($student_uid, $authentifizierung)
{
	$recht = new webservicerecht();
	$user = $authentifizierung->username;
	$passwort = $authentifizierung->passwort;

	// User authentifizieren
	if(!check_user($user, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	// darf User überhaupt Methode verwenden
	$recht = new webservicerecht();
	if(!$recht->isUserAuthorized($user, 'getStudentFromUid'))
		return new SoapFault("Server", "No permission");

	$obj = new stdClass();       // Rückgabeobjekt
	$prestudent = new prestudent();

	if(!$prestudent->getPrestudentsFromUid($student_uid) || count($prestudent->result) < 1)
		return new SoapFault("Server", "Kein Student mit übergebener Uid gefunden");

	$ps = $prestudent->result[count($prestudent->result)-1];		// TODO EINE hier wird nur der letzte prestudent zurückgegeben(muss noch abgeklärt werden, wer diese schnittstelle verwendet)

	$prestudent->getLastStatus($ps->prestudent_id);

	$obj->studiengang_kz = $ps->studiengang_kz;
	$obj->person_id = $ps->person_id;
	$obj->semester = $ps->semester;
	$obj->verband = $ps->verband;
	$obj->gruppe = $ps->gruppe;
	$obj->vorname = $ps->vorname;
	$obj->nachname = $ps->nachname;
	$obj->uid = $ps->uid;
	$obj->status = $prestudent->status_kurzbz;
	$obj->personenkennzeichen = $ps->perskz;
	$obj->email = $obj->uid.'@'.DOMAIN;

	$obj = $recht->clearResponse($user, 'getStudentFromUid', $obj);

	return $obj;
}

/**
 * Lädt einen Studenten anhand übergebener Matrikelnummer
 * @param $matrikelnummer
 * @param $authentifizierung
 */
function getStudentFromMatrikelnummer($matrikelnummer, $authentifizierung)
{
	$recht = new webservicerecht();
	$user = $authentifizierung->username;
	$passwort = $authentifizierung->passwort;

	// User authentifizieren
	if(!check_user($user, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	// darf User überhaupt Methode verwenden
	$recht = new webservicerecht();
	if(!$recht->isUserAuthorized($user, 'getStudentFromMatrikelnummer'))
		return new SoapFault("Server", "No permission");

	$student = new stdClass();       // Rückgabeobjekt
	$prestudent = new prestudent(); // Studentendaten

	if(!$prestudent->loadFromPerskz($matrikelnummer))
		return new SoapFault("Server", "Kein Student mit übergebener Matrikelnummer gefunden");

	$prestudent->getLastStatus($prestudent->prestudent_id);

	$student->studiengang_kz = $prestudent->studiengang_kz;
	$student->person_id = $prestudent->person_id;
	$student->semester = $prestudent->semester;
	$student->verband = $prestudent->verband;
	$student->gruppe = $prestudent->gruppe;
	$student->vorname = $prestudent->vorname;
	$student->nachname = $prestudent->nachname;
	$student->uid = $prestudent->uid;
	$student->status = $prestudent->status_kurzbz;
	$student->personenkennzeichen = $prestudent->perskz;
	$student->email = $student->uid.'@'.DOMAIN;

	$student = $recht->clearResponse($user, 'getStudentFromMatrikelnummer', $student);

	return $student;
}

/**
 * Lädt alle Studenten eines gewissen Kriteriums 
 * @param $studiengang
 * @param $semester
 * @param $verband
 * @param $gruppe
 * @param $authentifizierung
 */
function getStudentFromStudiengang($studiengang, $semester = null, $verband = null, $gruppe = null, $authentifizierung)
{
	$recht = new webservicerecht();
	$user = $authentifizierung->username;
	$passwort = $authentifizierung->passwort;

	// User authentifizieren
	if(!check_user($user, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	// darf User überhaupt Methode verwenden
	$recht = new webservicerecht();
	if(!$recht->isUserAuthorized($user, 'getStudentFromStudiengang'))
		return new SoapFault("Server", "No permission");

	$prestudent = new prestudent();

	$studiensemester = new studiensemester(); // aktuelles Studiensemester
	$studSemester = $studiensemester->getakt();

	$prestudent->result = $prestudent->getPrestudents($studiengang, $semester, $verband, $gruppe, null, $studSemester);

	$ret = array();

	foreach($prestudent->result as $ps)
	{
		$obj = new stdClass();       // Rückgabeobjekt
		$prestudent->getLastStatus($ps->prestudent_id);

		$obj->studiengang_kz = $ps->studiengang_kz;
		$obj->person_id = $ps->person_id;
		$obj->semester = $ps->semester;
		$obj->verband = $ps->verband;
		$obj->gruppe = $ps->gruppe;
		$obj->vorname = $ps->vorname;
		$obj->nachname = $ps->nachname;
		$obj->uid = $ps->uid;
		$obj->status = $prestudent->status_kurzbz;
		$obj->personenkennzeichen = $ps->matrikelnr;
		$obj->email = $ps->uid.'@'.DOMAIN;

		$obj = $recht->clearResponse($user, 'getStudentFromStudiengang', $obj);
		$ret[] = $obj;
	}
	return $ret;
}

