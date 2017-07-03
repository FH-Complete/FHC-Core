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
require_once('../include/addon.class.php');
require_once('../include/'.EXT_FKT_PATH.'/serviceterminal.inc.php');

ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT.'soap/kartenverlaengerung.wsdl.php?'.microtime(true)); 
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

    $addon_externeAusweise = false;
    $addon = new addon();
    $addon->loadAddons();
    foreach($addon->result as $ad)
    {
	if($ad->kurzbz == "externeAusweise")
	{
	    $addon_externeAusweise = true;
	}
    }

    if($addon_externeAusweise)
    {
	require_once (dirname(__FILE__).'/../addons/externeAusweise/include/idCard.class.php');
	$idCard = new idCard();
	if($idCard->loadByCardnumber($cardNr))
	{
	   return ServiceTerminalGetDrucktext($cardNr, $cardNr);
	}
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

	return ServiceTerminalGetDrucktext($cardUser->uid);

}
?>
