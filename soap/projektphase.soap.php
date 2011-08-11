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
require_once('../include/projektphase.class.php');
require_once('../include/datum.class.php');

$SOAPServer = new SoapServer(APP_ROOT."/soap/projektphase.wsdl.php");
$SOAPServer->addFunction("saveProjektphase");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 * 
 * Speichert die vom Webservice übergebenen Parameter in die DB
 * @param string $projektphase_id
 * @param string $projekt_kurzbz
 * @param string $projektphase_fk
 * @param string $bezeichnung
 * @param string $beschreibung
 * @param date $start
 * @param date $ende
 * @param string $budget
 */
function saveProjektphase($projektphase_id, $projekt_kurzbz, $projektphase_fk, $bezeichnung, $beschreibung, $start, $ende, $budget)
{ 	
	$projektphase = new projektphase();
	$projektphase->projektphase_id=$projektphase_id;
	$projektphase->projekt_kurzbz=$projekt_kurzbz;
	$projektphase->projektphase_fk=$projektphase_fk;
	$projektphase->bezeichnung = $bezeichnung;
	$projektphase->beschreibung = $beschreibung;
	$projektphase->start = $start;
	$projektphase->ende = $ende;
	$projektphase->budget = $budget;
	$projektphase->new = true; 
	
	if($projektphase->save($new = true))
		return "OK"; 
	else
		return new SoapFault("Server", $projektphase->errormsg);
}
?>


