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
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');

ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT."/soap/person.wsdl.php?".microtime(true));
$SOAPServer->addFunction("getPersonFromUID");
$SOAPServer->addFunction("searchPerson");
$SOAPServer->handle();

/**
 * 
 * Funktion getPersonFromUID liefert eine Person zurück
 * @param uid - uid der gesuchten Person
 * @param authentifizierung - Array mit Username und Passwort
 *
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','getPersonFromUID','vorname');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','getPersonFromUID','nachname');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','getPersonFromUID','titelpre');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','getPersonFromUID','titelpost');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','getPersonFromUID','uid');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','getPersonFromUID','email');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','getPersonFromUID','status');

 */
function getPersonFromUID($uid, $authentifizierung)
{
    $user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;
    
    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
    
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'getPersonFromUID'))
        return new SoapFault("Server", "No permission");
   
    //Personendaten laden
    $person = new benutzer();
    if(!$person->load($uid))
        return new SoapFault("Server", "Error loading Data");
    
    class foo{}; 
       	
    $obj = new foo(); 
    $obj->vorname = $person->vorname; 
    $obj->nachname = $person->nachname; 
    $obj->titelpre = $person->titelpre;
    $obj->titelpost = $person->titelpost;
    $obj->uid = $person->uid;
    $obj->email = $person->uid.'@'.DOMAIN;
    
    if(is_null($row->mitarbeiter_uid))
    {
        $obj->status = "Mitarbeiter";
    }
    else
    {
        $obj->status = "Student";
    }

    // lösche alle Attribute für die user keine Berechtigung hat 
    $obj = $recht->clearResponse($user, 'getPersonFromUID', $obj);
    
    return $obj; 
}

/**
 * 
 * Funktion searchPerson liefert eine Person zurück
 * @param searchItems - Array mit Suchbegriffen
 * @param authentifizierung - Array mit Username und Passwort
 *
 * Berechtigung:
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','searchPerson','vorname');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','searchPerson','nachname');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','searchPerson','titelpre');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','searchPerson','titelpost');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','searchPerson','uid');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','searchPerson','email');
 INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, attribut) VALUES('soap/person','searchPerson','status');

 */

function searchPerson($searchItems, $authentifizierung){
    $user = $authentifizierung->username; 
    $passwort = $authentifizierung->passwort;
    
    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");	
    
    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht(); 
    if(!$recht->isUserAuthorized($user, 'searchPerson'))
        return new SoapFault("Server", "No permission");
   
    //nach Personen suchen
    $person = new benutzer();
    $search = explode(' ',TRIM($searchItems));
    if(!$person->search($search))
        return new SoapFault("Server", "Error loading Data");
    
    class foo{}; 
       	
    $obj = new foo();
    $return = array();
    foreach($person->result as $row)
    {
        $obj = new foo(); 
        $obj->vorname = $row->vorname;
        $obj->nachname = $row->nachname;
        $obj->titelpre = $row->titelpre;
        $obj->titelpost = $row->titelpost;
        $obj->uid = $row->uid;
        $obj->email = $row->uid.'@'.DOMAIN;
        
        if(is_null($row->mitarbeiter_uid))
        {
            $obj->status = "Mitarbeiter";
        }
        else
        {
            $obj->status = "Student";
        }
        
        // lösche alle Attribute für die user keine Berechtigung hat 
        $return[] = $recht->clearResponse($user, 'searchPerson', $obj);
    }
    
    return $return;
}
?>
