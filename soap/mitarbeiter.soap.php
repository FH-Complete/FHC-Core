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
 * Webservice fuer Mitarbeiter
 * 
 */
require_once('../config/vilesci.config.inc.php'); 
require_once('../include/basis_db.class.php');
require_once('../include/functions.inc.php');
require_once('../include/webservicerecht.class.php');
require_once('../include/mitarbeiter.class.php');

ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT."/soap/mitarbeiter.wsdl.php?".microtime(true));
$SOAPServer->addFunction("getMitarbeiterFromUID");
$SOAPServer->addFunction("getMitarbeiter");
$SOAPServer->addFunction("SearchMitarbeiter");
$SOAPServer->handle();

/**
 * 
 * Funktion getMitarbeiterFromUID liefert einen Mitarbeiter zurück
 * @param uid - uid des Mitarbeiters
 * @param authentifizierung - Array mit Username und Passwort
 * 
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiterFromUID','vorname');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiterFromUID','nachname');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiterFromUID','titelpre');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiterFromUID','titelpost');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiterFromUID','uid');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiterFromUID','email');

 */
function getMitarbeiterFromUID($uid, $authentifizierung)
{
    $user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
 
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'getMitarbeiterFromUID'))
        return new SoapFault("Server", "No permission");
   
    // Daten für Lehrveranstaltung
    $mitarbeiter = new mitarbeiter();
    if(!$mitarbeiter->load($uid))
        return new SoapFault("Server", "Error loading Data");	
    
    class foo{}; 
       	
    $obj = new foo(); 
    $obj->vorname = $mitarbeiter->vorname; 
    $obj->nachname = $mitarbeiter->nachname; 
    $obj->titelpre = $mitarbeiter->titelpre;
    $obj->titelpost = $mitarbeiter->titelpost;
    $obj->uid = $mitarbeiter->uid;
    $obj->email = $mitarbeiter->uid.'@'.DOMAIN;

    // lösche alle Attribute für die user keine Berechtigung hat 
    $obj = $recht->clearResponse($user, 'getMitarbeiterFromUID', $obj);
    
    return $obj; 
}


/**
 * 
 * Funktion getMitarbeiter liefert alle aktiven Mitarbeiter zurück
 * @param authentifizierung - Array mit Username und Passwort
 * 
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiter','vorname');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiter','nachname');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiter','titelpre');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiter','titelpost');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiter','uid');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','getMitarbeiter','email'); 
 */
function getMitarbeiter($authentifizierung)
{
    $user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
 
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'getMitarbeiter'))
        return new SoapFault("Server", "No permission");
   
    // Daten für Lehrveranstaltung
    $mitarbeiter = new mitarbeiter();
    if(!$result = $mitarbeiter->getMitarbeiter())
        return new SoapFault("Server", "Error loading Data:".$mitarbeiter->errormsg);	

    $return = array();
    class foo{}; 
    foreach($result as $row)
    {
	    if($row->aktiv)
	    {
		    $obj = new foo(); 
		    $obj->vorname = $row->vorname;
		    $obj->nachname = $row->nachname;
		    $obj->titelpre = $row->titelpre;
		    $obj->titelpost = $row->titelpost;
		    $obj->uid = $row->uid;
		    $obj->email = $row->uid.'@'.DOMAIN; 
		             
		    // lösche alle Attribute für die user keine Berechtigung hat 
		    $return[] = $recht->clearResponse($user, 'getMitarbeiter', $obj);
	    }
    }
    
    return $return; 
}

/**
 * 
 * Funktion SearchMitarbeiter liefert alle aktiven Mitarbeiter zurück
 * @param filter - Suchfilter
 * @param authentifizierung - Array mit Username und Passwort
 * 
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','SearchMitarbeiter','vorname');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','SearchMitarbeiter','nachname');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','SearchMitarbeiter','titelpre');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','SearchMitarbeiter','titelpost');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','SearchMitarbeiter','uid');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/mitarbeiter','SearchMitarbeiter','email'); 
 */
function SearchMitarbeiter($filter, $authentifizierung)
{
    $user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
 
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'SearchMitarbeiter'))
        return new SoapFault("Server", "No permission");
   
    // Daten für Lehrveranstaltung
    $mitarbeiter = new mitarbeiter();
    if(!$mitarbeiter->search($filter))
        return new SoapFault("Server", "Error loading Data:".$mitarbeiter->errormsg);	

    $return = array();
    class foo{}; 
    foreach($mitarbeiter->result as $row)
    {
	    $obj = new foo(); 
	    $obj->vorname = $row->vorname;
	    $obj->nachname = $row->nachname;
	    $obj->titelpre = $row->titelpre;
	    $obj->titelpost = $row->titelpost;
	    $obj->uid = $row->uid;
	    $obj->email = $row->uid.'@'.DOMAIN; 
	             
	    // lösche alle Attribute für die user keine Berechtigung hat 
	    $return[] = $recht->clearResponse($user, 'SearchMitarbeiter', $obj);
    }
    
    return $return; 
}		
?>
