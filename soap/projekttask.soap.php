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
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

require_once('../config/vilesci.config.inc.php'); 
require_once('../include/basis_db.class.php');
require_once('../include/projekttask.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/datum.class.php');
require_once('../include/functions.inc.php');

$SOAPServer = new SoapServer(APP_ROOT."/soap/projekttask.wsdl.php?".microtime());
$SOAPServer->addFunction("saveProjekttask");
$SOAPServer->addFunction("deleteProjekttask");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 * 
 * Speichert die vom Webservice übergebenen Parameter in die DB
 * @param string $projekttask_id
 * @param string $projektphase_id
 * @param string $bezeichnung
 * @param string $beschreibung
 * @param string $aufwand
 * @param string $mantis_id
 * @param string $user
 */
function saveProjekttask($projekttask_id, $projektphase_id, $bezeichnung, $beschreibung, $aufwand, $mantis_id, $user)
{ 	
	$user = get_uid(); 
	$projekttask = new projekttask();
	// wenn projekttaskt_id == leer -> neuer task anlegen ohne laden
	if($projekttask_id != '')
	{
		if($projekttask->load($projekttask_id))
		{
			$projekttask->new = false;
		}
		else
			return new SoapFault("Server", "Fehler beim Laden"); 
	}
	else
	{
		$projekttask->new=true;
		$projekttask->insertvon = $user;
	}

	$projekttask->projekttask_id=$projekttask_id;
	$projekttask->projektphase_id=$projektphase_id;
	$projekttask->bezeichnung=$bezeichnung;
	$projekttask->beschreibung = $beschreibung;
	$projekttask->aufwand = $aufwand;
	$projekttask->mantis_id = $mantis_id;
	$projekttask->updatevon = $user;
	
	if($projekttask->save())
	{
		return $projekttask->projekttask_id;
	} 
	else
		return new SoapFault("Server", $projekttask->errormsg);
}

/**
 * 
 * Löscht den Task mit der vom Webservice übergebenen ID 
 * @param $projekttask_id
 */
function deleteProjekttask($projekttask_id)
{
	$projekttask = new projekttask();
	if($projekttask->delete($projekttask_id))
		return "OK";
	else
		return new SoapFault("Server", $projekttask->errormsg);
}
?>


