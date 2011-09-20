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
require_once('../include/ressource.class.php');
require_once('../include/datum.class.php');

$SOAPServer = new SoapServer(APP_ROOT."/soap/ressource_projekt.wsdl.php?".microtime());
$SOAPServer->addFunction("saveProjektRessource");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 * 
 * Speichert in der Zwischentabelle Ressource - Projekt
 * @param unknown_type $projekt_ressource_id
 * @param unknown_type $projektphase_id
 * @param unknown_type $projekt_kurzbz
 * @param unknown_type $ressource_id
 * @param unknown_type $funktion_kurzbz
 * @param unknown_type $beschreibung
 * @param unknown_type $user
 */

function saveProjektRessource($projekt_ressource_id, $projektphase_id, $projekt_kurzbz, $ressource_id, $funktion_kurzbz, $beschreibung)
{ 	
	$ressource = new ressource();
	if($projekt_ressource_id!='')
	{
		$ressource->loadProjektRessource($projekt_ressource_id);
		$ressource->new = false;
	}
	else
	{
		$ressource->new = true; 
	}
	$ressource->projekt_ressource_id=$projekt_ressource_id;
	$ressource->projektphase_id=$projektphase_id;
	$ressource->projekt_kurzbz=$projekt_kurzbz;
	$ressource->ressource_id = $ressource_id;
	$ressource->funktion_kurzbz = $funktion_kurzbz;
	$ressource->beschreibung = $beschreibung;

	
	if($ressource->saveProjektRessource())
		return $ressource->errormsg; 
	else
		return new SoapFault("Server", $ressource->errormsg);
}
?>


