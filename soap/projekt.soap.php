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
require_once('../include/projekt.class.php');
require_once('../include/datum.class.php');
require_once('../include/dms.class.php');

$SOAPServer = new SoapServer(APP_ROOT."/soap/projekt.wsdl.php?".microtime());
$SOAPServer->addFunction("saveProjekt");
$SOAPServer->addFunction("saveProjektdokumentZuordnung");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 * 
 * Speichert die vom Webservice übergebenen Parameter in die DB
 * @param string $projekt_kurzbz
 * @param string $nummer
 * @param string $titel
 * @param string $beschreibung
 * @param date $beginn
 * @param date $ende
 * @param string $oe_kurzbz
 * @param boolean $neu
 */
function saveProjekt($projekt_kurzbz, $nummer, $titel, $beschreibung, $beginn, $ende, $oe_kurzbz, $neu)
{ 	
	
	$projekt = new projekt();
	$projekt->projekt_kurzbz=$projekt_kurzbz;
	$projekt->nummer = $nummer;
	$projekt->titel = $titel;
	$projekt->beschreibung = $beschreibung;
	$projekt->beginn = $beginn;
	$projekt->ende = $ende;
	$projekt->oe_kurzbz = $oe_kurzbz;
	if($neu=='true')
		$projekt->new = true; 
	else
		$projekt->new = false;
	
	if($projekt->save())
		return $projekt->projekt_kurzbz;
	else
		return new SoapFault("Server", $projekt->errormsg);
}

/**
 * Speichert die Zuordnung eines Dokuments zu einem Projekt oder einer Phase
 */
function saveProjektdokumentZuordnung($projekt_kurzbz, $projektphase_id, $dms_id)
{
	$dms = new dms();
	
	if($dms->saveProjektzuordnung($dms_id, $projekt_kurzbz, $projektphase_id))
		return true;
	else
		return new SoapFault("Server", $dms->errormsg);
}
?>


