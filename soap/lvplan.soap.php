<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors:	Andreas Oesterreicher <oesi@technikum-wien.at>.
 */
/**
 * Webservice fuer LVPlan
 * 
 */
require_once('../config/vilesci.config.inc.php'); 
require_once('../include/basis_db.class.php');
require_once('../include/functions.inc.php');
require_once('../include/webservicerecht.class.php');
require_once('../include/lehrstunde.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/lehrveranstaltung.class.php'); 

ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT."/soap/lvplan.wsdl.php?".microtime(true));
$SOAPServer->addFunction("getLVPlanFromUser");
$SOAPServer->addFunction("getLVPlanFromLV");
$SOAPServer->addFunction("getLVPlanFromStg");
$SOAPServer->addFunction("getLVPlanFromOrt");
$SOAPServer->handle();

/**
 * 
 * Funktion getLVPlanFromUser Liefert den persoenlichen LVPlan eines Benutzers
 * @param uid - BenutzerUID
 * @param von - Von Datum
 * @param bis - Bis Datum
 * @param authentifizierung - Array mit Username und Passwort
 * 
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','anmerkung');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','titel');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','studiengang_kz');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','semester');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','stunde');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','datum');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','lehreinheit_id');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','institut');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','farbe');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','lektor');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','gruppe');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromUser','orte');
 */
function getLVPlanFromUser($uid, $von, $bis, $authentifizierung)
{
    if($uid == '')
        return new SOAPFault("Server", "uid must be set");
    if($von == '')
        return new SOAPFault("Server", "von must be set");
    if($bis == '')
        return new SOAPFault("Server", "bis must be set");
     
    $user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
 
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'getLVPlanFromUser'))
        return new SoapFault("Server", "No permission");

	if(check_lektor($uid))
		$type='lektor';
	else
		$type='student';
	$ls = new lehrstunde();
	if(!$ls->load_lehrstunden($type,$von,$bis,$uid))
		return new SoapFault("Server",$ls->errormsg);

    class foo{}; 
	
	$result = $ls->getLehrstundenGruppiert();
    $lv = new lehrveranstaltung(); 
    
	foreach($result as $row)
   	{
   		$Object = new foo();
		$le = new lehreinheit();
		if($row->lehreinheit_id!='' && $le->getLehreinheitDetails($row->lehreinheit_id))
		{
			$Object->studiengang_kz = $le->studiengang_kz;
			$Object->semester = $le->semester;
			$Object->institut = $le->fachbereich_kurzbz;
            $Object->lehrveranstaltung_id = $le->lehrveranstaltung_id; 
		}

        if(isset($Object->lehrveranstaltung_id))
        {
            $lv->load($Object->lehrveranstaltung_id);
            $Object->titel = $lv->bezeichnung;
            
            $le_help = new lehreinheit(); 
            if($le_help->load($le->lehreinheit_id))
                $Object->lehrform = $le_help->lehrform_kurzbz;                
        }
        else
        {
            $Object->titel = $row->titel;
            $Object->lehrform = $row->lehrform; 
            
        }
        //$Object->lehrform = $row->lehrform; 
        $Object->anmerkung = $row->anmerkung;
		$Object->stunde = $row->stunde;
		$Object->datum = $row->datum;
		$Object->lehreinheit_id = $row->lehreinheit_id;
		$Object->farbe = $row->farbe;
		$Object->lektor = $row->lektor_uid;
		$Object->gruppe = $row->gruppen;
		$Object->orte = $row->ort_kurzbz;

	    // lösche alle Attribute für die user keine Berechtigung hat    		
	   	$return[] = $recht->clearResponse($user, 'getLVPlanFromUser', $Object);
   	}
    
    return $return; 
}

/**
 * 
 * Funktion getLVPlanFromLV Liefert den LVPlan einer Lehrveranstaltung in einem Studiensemester
 * @param lehrveranstaltung_id
 * @param studiensemester_kurzbz
 * @param authentifizierung - Array mit Username und Passwort
 * 
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','anmerkung');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','titel');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','studiengang_kz');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','semester');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','stunde');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','datum');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','lehreinheit_id');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','institut');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','farbe');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','lektor');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','gruppe');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromLV','orte');
 */

function getLVPlanFromLV($lehrveranstaltung_id, $studiensemester_kurzbz, $authentifizierung)
{
	if($lehrveranstaltung_id == '')
		return new SOAPFault("Server", "LehrveranstaltungID must be set");
	if($studiensemester_kurzbz == '')
		return new SOAPFault("Server", "Studiensemester_kurzbz must be set");
     
	$user = $authentifizierung->username; 
	$passwort = $authentifizierung->passwort;

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
 
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'getLVPlanFromLV'))
        return new SoapFault("Server", "No permission");

	// Alle Lehreinheiten zur LV holen	
	$le = new lehreinheit();
	if(!$le->load_lehreinheiten($lehrveranstaltung_id, $studiensemester_kurzbz))
		return new SoapFault("Server",$le->errormsg);

	// Alle Stunden zu diesen Lehreinheiten holen
	$ls = new lehrstunde();
	$lehrstunden=array();
	foreach($le->lehreinheiten as $row)
	{
		if(!$ls->load_lehrstunden_le($row->lehreinheit_id, null, 'stundenplan'))
			return new SoapFault("Server", $ls->errormsg);
		foreach($ls->lehrstunden as $row)
			$lehrstunden[] = $row;
	}

    class foo{}; 
	$ls->lehrstunden = $lehrstunden;
	$result = $ls->getLehrstundenGruppiert();

	foreach($result as $row)
   	{
   		$Object = new foo();
		$le = new lehreinheit();
		if($row->lehreinheit_id!='' && $le->getLehreinheitDetails($row->lehreinheit_id))
		{
			$Object->studiengang_kz = $le->studiengang_kz;
			$Object->semester = $le->semester;
			$Object->institut = $le->fachbereich_kurzbz;
			$Object->farbe = $le->farbe;
		}

		$Object->anmerkung = $row->anmerkung;
		$Object->titel = $row->titel;
		$Object->stunde = $row->stunde;
		$Object->datum = $row->datum;
		$Object->lehreinheit_id = $row->lehreinheit_id;

		$Object->lektor = $row->lektor_uid;
		$Object->gruppe = $row->gruppen;
		$Object->orte = $row->ort_kurzbz;

	    // lösche alle Attribute für die user keine Berechtigung hat    		
	   	$return[] = $recht->clearResponse($user, 'getLVPlanFromLV', $Object);
   	}
    
    return $return; 
}

/**
 * 
 * Funktion getLVPlanFromStg Liefert den LVPlan eines Studienganges/Semesters/Verbands/Gruppe
 * @param studiengang_kz - Studiengangskennzahl
 * @param semester - Semester
 * @param verband - Verband
 * @param gruppe - Gruppe
 * @param gruppe_kurzbz - Kurzbezeichnung der Spezialgruppe
 * @param von - Von Datum
 * @param bis - Bis Datum
 * @param authentifizierung - Array mit Username und Passwort
 * 
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','anmerkung');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','titel');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','studiengang_kz');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','semester');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','stunde');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','datum');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','lehreinheit_id');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','institut');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','farbe');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','lektor');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','gruppe');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromStg','orte');
 */
function getLVPlanFromStg($studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz, $von, $bis, $authentifizierung)
{
	if($studiengang_kz == '' && $gruppe_kurzbz=='')
        return new SOAPFault("Server", "Studiengang_kz or Gruppe_kurzbz must be set");
    if($von == '')
        return new SOAPFault("Server", "von must be set");
    if($bis == '')
        return new SOAPFault("Server", "bis must be set");
     
    $user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
 
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'getLVPlanFromStg'))
        return new SoapFault("Server", "No permission");

	if($gruppe_kurzbz!='')
		$type='gruppe';
	else
		$type='verband';
	$ls = new lehrstunde();
	if(!$ls->load_lehrstunden($type,$von,$bis,null, null, $studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz))
		return new SoapFault("Server",$ls->errormsg);

    class foo{}; 
	
	$result = $ls->getLehrstundenGruppiert();
	foreach($result as $row)
   	{
   		$Object = new foo();
		$le = new lehreinheit();
		if($row->lehreinheit_id!='' && $le->getLehreinheitDetails($row->lehreinheit_id))
		{
			$Object->studiengang_kz = $le->studiengang_kz;
			$Object->semester = $le->semester;
			$Object->institut = $le->fachbereich_kurzbz;
		}

		$Object->anmerkung = $row->anmerkung;
		$Object->titel = $row->titel;
		$Object->stunde = $row->stunde;
		$Object->datum = $row->datum;
		$Object->lehreinheit_id = $row->lehreinheit_id;
		$Object->farbe = $row->farbe;
		$Object->lektor = $row->lektor_uid;
		$Object->gruppe = $row->gruppen;
		$Object->orte = $row->ort_kurzbz;

	    // lösche alle Attribute für die user keine Berechtigung hat    		
	   	$return[] = $recht->clearResponse($user, 'getLVPlanFromStg', $Object);
   	}
    
    return $return; 
}

/**
 * 
 * Funktion getLVPlanFromOrt Liefert den LVPlan eines Ortes
 * @param ort_kurzbz - Kurzbezeichnung des Ortes
 * @param von - Von Datum
 * @param bis - Bis Datum
 * @param authentifizierung - Array mit Username und Passwort
 * 
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','anmerkung');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','titel');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','studiengang_kz');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','semester');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','stunde');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','datum');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','lehreinheit_id');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','institut');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','farbe');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','lektor');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','gruppe');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/lvplan','getLVPlanFromOrt','orte');
 */
function getLVPlanFromOrt($ort_kurzbz, $von, $bis, $authentifizierung)
{
	if($ort_kurzbz == '')
        return new SOAPFault("Server", "Ort must be set");
    if($von == '')
        return new SOAPFault("Server", "von must be set");
    if($bis == '')
        return new SOAPFault("Server", "bis must be set");
     
    $user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
 
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'getLVPlanFromOrt'))
        return new SoapFault("Server", "No permission");

	$ls = new lehrstunde();
	if(!$ls->load_lehrstunden('ort',$von,$bis,null, $ort_kurzbz))
		return new SoapFault("Server",$ls->errormsg);

    class foo{}; 
	
	$result = $ls->getLehrstundenGruppiert();
	foreach($result as $row)
   	{
   		$Object = new foo();
		$le = new lehreinheit();
		if($row->lehreinheit_id!='' && $le->getLehreinheitDetails($row->lehreinheit_id))
		{
			$Object->studiengang_kz = $le->studiengang_kz;
			$Object->semester = $le->semester;
			$Object->institut = $le->fachbereich_kurzbz;
		}

		$Object->anmerkung = $row->anmerkung;
		$Object->titel = $row->titel;
		$Object->stunde = $row->stunde;
		$Object->datum = $row->datum;
		$Object->lehreinheit_id = $row->lehreinheit_id;
		$Object->farbe = $row->farbe;
		$Object->lektor = $row->lektor_uid;
		$Object->gruppe = $row->gruppen;
		$Object->orte = $row->ort_kurzbz;

	    // lösche alle Attribute für die user keine Berechtigung hat    		
	   	$return[] = $recht->clearResponse($user, 'getLVPlanFromOrt', $Object);
   	}
    
    return $return; 
}
?>
