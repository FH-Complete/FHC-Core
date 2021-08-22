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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/ressource.class.php');
require_once('../include/datum.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$SOAPServer = new SoapServer(APP_ROOT."/soap/ressource_projekt.wsdl.php?".microtime(true));
$SOAPServer->addFunction("saveProjektRessource");
$SOAPServer->addFunction("deleteProjektRessource");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 *
 * Speichert in der Zwischentabelle Ressource - Projekt
 * @param $username
 * @param $passwort
 * @param $projektRessource
 */
function saveProjektRessource($username, $passwort, $projektRessource)
{
	if(!$user = check_user($username, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('planner', null, 'sui'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Speichern von Projekten.");

	$ressource = new ressource();
	if($projektRessource->projekt_ressource_id!='')
	{
		$ressource->loadProjektRessource($projektRessource->projekt_ressource_id);
		$ressource->new = false;
	}
	else
	{
		$ressource->new = true;
	}
	$ressource->projekt_ressource_id=$projektRessource->projekt_ressource_id;
	$ressource->projektphase_id=$projektRessource->projektphase_id;
	$ressource->projekt_kurzbz=$projektRessource->projekt_kurzbz;
	$ressource->ressource_id = $projektRessource->ressource_id;
	$ressource->funktion_kurzbz = $projektRessource->funktion_kurzbz;
	$ressource->beschreibung = $projektRessource->beschreibung;
	$ressource->aufwand = $projektRessource->aufwand;


	if($ressource->saveProjektRessource())
		return $ressource->projekt_ressource_id;
	else
		return new SoapFault("Server", $ressource->errormsg);
}

/**
 * Löscht entweder eine Projekt zu Ressource oder Phase zu Ressource Zuordnung
 * @param type $username
 * @param type $passwort
 * @param type $projektRessource
 * @return \SoapFault
 */
function deleteProjektRessource($username, $passwort, $projektRessource)
{
    if(!$user = check_user($username, $passwort))
        return new SoapFault ("Server", "Invalid Credentials");

    $recht = new benutzerberechtigung();
    $recht->getBerechtigungen($user);

   // if(!$rechte->isBerechtigt('planner', null, 'sui'))
	//	return new SoapFault("Server", "Sie haben keine Berechtigung zum Speichern von Projekten.");

    $ressource = new ressource();

    if($projektRessource->projektphase_id != '')
    {
        // von Projektphase löschen
        if($ressource->deleteFromPhaseWithProjektRessourceId($projektRessource->ressource_id, $projektRessource->projektphase_id, $projektRessource->projekt_ressource_id))
            return "Erfolg";
        else
            return "Fehler beim Löschen";

    }
    else
    {

        // von Projekt löschen
        //if($ressource->deleteFromProjekt($projektRessource->ressource_id, $projektRessource->projekt_kurzbz))
	    if($ressource->deleteFromProjektWithProjektRessourceId($projektRessource->ressource_id, $projektRessource->projekt_kurzbz, $projektRessource->projekt_ressource_id))
            return "Erfolg";
        else
            return "Fehler beim Löschen";

    }
}

?>
