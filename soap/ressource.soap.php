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

$SOAPServer = new SoapServer(APP_ROOT."/soap/ressource.wsdl.php?".microtime());
$SOAPServer->addFunction("saveRessource");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 * 
 * Speichert die Ressource
 * @param unknown_type $ressource_id
 * @param unknown_type $bezeichnung
 * @param unknown_type $beschreibung
 * @param unknown_type $mitarbeiter_uid
 * @param unknown_type $student_uid
 * @param unknown_type $betriebsmittel_id
 * @param unknown_type $firma_id
 * @param unknown_type $user
 */
function saveRessource($ressource_id, $bezeichnung, $beschreibung, $mitarbeiter_uid, $student_uid, $betriebsmittel_id, $firma_id, $user)
{ 	
	$ressource = new ressource();
	if($ressource_id!='')
	{
		$ressource->load($ressource_id);
		$ressource->new = false;
	}
	else
	{
		$ressource->new = true; 
		$ressource->insertvon = $user;
	}
	$ressource->ressource_id=$ressource_id;
	$ressource->bezeichnung=$bezeichnung;
	$ressource->beschreibung=$beschreibung;
	$ressource->mitarbeiter_uid = $mitarbeiter_uid;
	$ressource->student_uid = $student_uid;
	$ressource->betriebsmittel_id = $betriebsmittel_id;
	$ressource->firma_id = $firma_id;
	$ressource->updatevon = $user;
	
	if($ressource->save())
		return $ressource->ressource_id; 
	else
		return new SoapFault("Server", $ressource->errormsg);
}
?>


