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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 */

require_once('../config/cis.config.inc.php'); 
require_once('../include/konto.class.php'); 
require_once('../include/betriebsmittelperson.class.php'); 
require_once('../include/studiensemester.class.php'); 
require_once('../include/benutzer.class.php'); 
require_once('../include/webservicelog.class.php'); 
require_once('../include/datum.class.php'); 

ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT.'soap/kartenverlaengerung.wsdl.php?'.microtime()); 
$SOAPServer->addFunction('getNumber'); 
$SOAPServer->handle();

function getNumber($cardNr)
{
    // Fehler wenn keine Kartennummer übergeben wurde
    if($cardNr == '')
    {
        $objArray = array('datum'=>'', 'errorMessage'=>'keine gültige  Nummer übergeben.');  
        return $objArray; 
    }
    
    // Karte ist noch nicht ausgegeben
    $cardUser = new betriebsmittelperson(); 
    if(!$cardUser->getKartenzuordnung($cardNr))
    {
        $objArray = array('datum'=>'', 'errorMessage'=>'Konnte Karte keiner Person zuweisen. Bitte wenden Sie sich an den Service Desk.');  
        return $objArray; 
    }
    
    // User zur Karte konnte nicht geladen werden
    $cardPerson = new benutzer(); 
    if(!$cardPerson->load($cardUser->uid))
    {
        $objArray = array('datum'=>'', 'errorMessage'=>'Die Person kann nicht geladen werden. Bitte wenden Sie sich an den Service Desk.');  
        return $objArray;   
    }
    /*
    // lädt das aktuelle semester und nach 75 Tagen nach Anfang des Semesters das nächste
    $studSemester = new studiensemester(); 
    if(!$aktSemester= $studSemester->getNextOrAktSemester('75'))
    {
        $objArray = array('datum'=>'', 'errorMessage'=>'Konnte Semester nicht laden. Bitte wenden Sie sich an den Service Desk.');  
        return $objArray;   
    }
    */
    // hole Semester des letzten eingezahlten Studienbeitrages
    $konto = new konto(); 
    if(!$aktSemester= $konto->getLastStudienbeitrag($cardPerson->uid))
    {
        $objArray = array('datum'=>'', 'errorMessage'=>'Fehler beim Auslesen des Studienganges. Bitte wenden Sie sich an den Service Desk.');  
        return $objArray;
    }  
    
    /*
    // überprüft ob Studienbeitrag bezahlt wurde
    if(!$konto->checkStudienbeitrag($cardPerson->uid, $aktSemester))
    {
        $objArray = array('datum'=>'', 'errorMessage'=>'Studienbeitrag noch nicht gezahlt.');  
        return $objArray;
    }
    */
    $studSemester = new studiensemester(); 
    $studSemester->load($aktSemester); 
    $datum = new datum(); 
    
    //$objArray = array('datum'=>'Gueltig bis/valid thru '.$datum->formatDatum($studSemester->ende, 'd.m.Y'), 'errorMessage'=>'');  
    $objArray = array('datum'=>'Gueltig fuer/valid for '.$studSemester->studiensemester_kurzbz, 'errorMessage'=>'');  
    return $objArray;  
    
}
?>