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
 * Webservice fuer Ort/Raum
 * 
 */
require_once('../config/vilesci.config.inc.php'); 
require_once('../include/basis_db.class.php');
require_once('../include/functions.inc.php');
require_once('../include/webservicerecht.class.php');
require_once('../include/ort.class.php');
require_once('../include/ortraumtyp.class.php');

ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT."/soap/ort.wsdl.php?".microtime(true));
$SOAPServer->addFunction("getOrtFromKurzbz");
$SOAPServer->addFunction("getRaeume");
$SOAPServer->addFunction("searchRaum");
$SOAPServer->handle();

/**
 * 
 * Funktion getOrtFromKurzbz liefert einen Ort zurück
 * @param ort_kurzbz - ort_kurzbz
 * @param authentifizierung - Array mit Username und Passwort
 * 
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getOrtFromKurzbz','bezeichnung');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getOrtFromKurzbz','stockwerk');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getOrtFromKurzbz','sitzplaetze');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getOrtFromKurzbz','raumtyp');

 */
function getOrtFromKurzbz($ort_kurzbz, $authentifizierung)
{
    if($ort_kurzbz == '')
        return new SOAPFault("Server", "ort_kurzbz must be set");
     
    $user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
 
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'getOrtFromKurzbz'))
        return new SoapFault("Server", "No permission");
   
    // Daten für Lehrveranstaltung
    $ort = new ort();
    if(!$ort->load($ort_kurzbz))
        return new SoapFault("Server", "Error loading Data");	
    
    class foo{}; 
        
   	$raumtyp = new ortraumtyp();
   	$raumtyp->getRaumtypen($ort_kurzbz);
   	$raumtyp_arr = array();

   	foreach($raumtyp->result as $row)
   	{
   		$rt = new foo();
   		$rt->beschreibung = $row->beschreibung;
   		$rt->raumtyp_kurzbz = $row->raumtyp_kurzbz;
   		$rt->hierarchie = $row->hierarchie;
   		
   		$raumtyp_arr[] = $rt;
   	}
   	
    $OrtObject = new foo(); 
    $OrtObject->bezeichnung = $ort->bezeichnung; 
    $OrtObject->stockwerk = $ort->stockwerk; 
    $OrtObject->sitzplaetze = $ort->max_person; 
    $OrtObject->raumtyp= $raumtyp_arr; 
             
    // lösche alle Attribute für die user keine Berechtigung hat 
    $OrtObject = $recht->clearResponse($user, 'getOrtFromKurzbz', $OrtObject);
    
    return $OrtObject; 
}


/**
 * 
 * Funktion getRaeume liefert alle aktiven reservierbaren Orte zurück
 * @param authentifizierung - Array mit Username und Passwort
 * 
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getRaeume','ort_kurzbz');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getRaeume','bezeichnung');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getRaeume','planbezeichnung');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getRaeume','sitzplaetze');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getRaeume','aktiv');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getRaeume','lehre');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getRaeume','reservieren');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','getRaeume','stockwerk');
 */
function getRaeume($raumtyp_kurzbz=null, $authentifizierung)
{
    $user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
 
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'getRaeume'))
        return new SoapFault("Server", "No permission");
   
    // Daten für Lehrveranstaltung
    $ort = new ort();
    if(!$ort->getAll($raumtyp_kurzbz))
        return new SoapFault("Server", "Error loading Data:".$ort->errormsg);	

    $return = array();
    class foo{}; 
    foreach($ort->result as $row)
    {
	    if($row->aktiv && $row->lehre)
	    {
		    $OrtObject = new foo(); 
		    $OrtObject->ort_kurzbz = $row->ort_kurzbz;
		    $OrtObject->bezeichnung = $row->bezeichnung;
		    $OrtObject->planbezeichnung = $row->planbezeichnung; 
		    $OrtObject->sitzplaetze = $row->max_person;
		    $OrtObject->aktiv = $row->aktiv;
		    $OrtObject->lehre = $row->lehre;
		    $OrtObject->reservieren = $row->reservieren;
		    $OrtObject->stockwerk = $row->stockwerk; 
		             
		    // lösche alle Attribute für die user keine Berechtigung hat 
		    $return[] = $recht->clearResponse($user, 'getRaeume', $OrtObject);
	    }
    }
    
    return $return; 
}

/**
 * 
 * Funktion searchRaum - Sucht einen freien Raum
 * 
 * @param date $datum
 * @param time $zeit_von
 * @param time $zeit_bis
 * @param string $raumtyp
 * @param integer $anzpersonen
 * @param boolean $reservierung
 * @param authentifizierung - Array mit Username und Passwort
 * 
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','searchRaum','ort_kurzbz');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','searchRaum','bezeichnung');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','searchRaum','planbezeichnung');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','searchRaum','sitzplaetze');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','searchRaum','aktiv');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','searchRaum','lehre');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','searchRaum','reservieren');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/ort','searchRaum','stockwerk');
 */
function searchRaum($datum, $zeit_von, $zeit_bis, $raumtyp=null, $anzpersonen=null, $reservierung=true, $authentifizierung)
{
	$user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;

    $anzpersonen = 0;
    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
 
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'searchRaum'))
        return new SoapFault("Server", "No permission");
   
    // Daten für Lehrveranstaltung
    $ort = new ort();
    if(!$ort->search($datum, $zeit_von, $zeit_bis, $raumtyp, $anzpersonen, $reservierung))
        return new SoapFault("Server", "Error loading Data");	

    $return = array();
    class foo{}; 
    foreach($ort->result as $row)
    {
	    if($row->aktiv && $row->lehre)
	    {
		    $OrtObject = new foo(); 
		    $OrtObject->ort_kurzbz = $row->ort_kurzbz;
		    $OrtObject->bezeichnung = $row->bezeichnung;
		    $OrtObject->planbezeichnung = $row->planbezeichnung; 
		    $OrtObject->sitzplaetze = $row->max_person;
		    $OrtObject->aktiv = $row->aktiv;
		    $OrtObject->lehre = $row->lehre;
		    $OrtObject->reservieren = $row->reservieren;
		    $OrtObject->stockwerk = $row->stockwerk; 
		             
		    // lösche alle Attribute für die user keine Berechtigung hat 
		    $return[] = $recht->clearResponse($user, 'searchRaum', $OrtObject);
	    }
    }
    
    return $return; 
}
	
?>
