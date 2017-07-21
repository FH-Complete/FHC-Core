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
 * Authors:		Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/student.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/webservicerecht.class.php');
require_once('../include/studiensemester.class.php');
ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT."/soap/student.wsdl.php?".microtime(true));
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

    $studentObj = new student(); // Studentendaten
    $student = new foo();       // Rückgabeobjekt
    $preStudent = new prestudent(); // StudentenStatus

    if(!$studentObj->load($student_uid))
        return new SoapFault("Server", "Kein Student mit übergebener Uid gefunden");

    $preStudent->getLastStatus($studentObj->prestudent_id);

    $student->studiengang_kz = $studentObj->studiengang_kz;
    $student->person_id = $studentObj->person_id;
    $student->semester = $studentObj->semester;
    $student->verband = $studentObj->verband;
    $student->gruppe = $studentObj->gruppe;
    $student->vorname = $studentObj->vorname;
    $student->nachname = $studentObj->nachname;
    $student->uid = $studentObj->uid;
    $student->status = $preStudent->status_kurzbz;
    $student->personenkennzeichen = $studentObj->matrikelnr;
    $student->email = $student->uid.'@'.DOMAIN;

    $student = $recht->clearResponse($user, 'getStudentFromUid', $student);

    return $student;
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

    $studentObj = new student(); // Studentendaten
    $student = new foo();       // Rückgabeobjekt
    $preStudent = new prestudent(); // StudentenStatus

    $student_uid = $studentObj->getUidFromMatrikelnummer($matrikelnummer);
    if(!$studentObj->load($student_uid))
        return new SoapFault("Server", "Kein Student mit übergebener Matrikelnummer gefunden");

    $preStudent->getLastStatus($studentObj->prestudent_id);

    $student->studiengang_kz = $studentObj->studiengang_kz;
    $student->person_id = $studentObj->person_id;
    $student->semester = $studentObj->semester;
    $student->verband = $studentObj->verband;
    $student->gruppe = $studentObj->gruppe;
    $student->vorname = $studentObj->vorname;
    $student->nachname = $studentObj->nachname;
    $student->uid = $studentObj->uid;
    $student->status = $preStudent->status_kurzbz;
    $student->personenkennzeichen = $studentObj->matrikelnr;
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

    $studentObj = new student(); // Studentendaten
    $preStudent = new prestudent(); // StudentenStatus

    $studiensemester = new studiensemester(); // aktuelles Studiensemester
    $studSemester = $studiensemester->getakt();

    $studentObj->result = $studentObj->getStudents($studiengang, $semester, $verband, $gruppe, null, $studSemester);

    $studentArray = array();

    foreach($studentObj->result as $stud)
    {
        $student = new foo();       // Rückgabeobjekt
        $preStudent->getLastStatus($stud->prestudent_id);

        $student->studiengang_kz = $stud->studiengang_kz;
        $student->person_id = $stud->person_id;
        $student->semester = $stud->semester;
        $student->verband = $stud->verband;
        $student->gruppe = $stud->gruppe;
        $student->vorname = $stud->vorname;
        $student->nachname = $stud->nachname;
        $student->uid = $stud->uid;
        $student->status = $preStudent->status_kurzbz;
        $student->personenkennzeichen = $stud->matrikelnr;
        $student->email = $stud->uid.'@'.DOMAIN;

        $student = $recht->clearResponse($user, 'getStudentFromStudiengang', $student);
        $studentArray[] = $student;
    }
    return $studentArray;
}

class foo{}
