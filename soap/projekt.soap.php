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
require_once('../include/projekt.class.php');
require_once('../include/datum.class.php');
require_once('../include/dms.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$SOAPServer = new SoapServer(APP_ROOT."/soap/projekt.wsdl.php?".microtime(true));
$SOAPServer->addFunction("saveProjekt");
$SOAPServer->addFunction("saveProjektdokumentZuordnung");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 *
 * Speichert das vom Webservice übergebene Projekt in die DB
 * @param $username
 * @param $passwort
 * @param $projekt
 */
function saveProjekt($username, $passwort, $projekt)
{
	if(!$user = check_user($username, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('planner', null, 'sui'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Speichern von Projekten.");

	$projektNew = new projekt();
	$projektNew->projekt_kurzbz=$projekt->projekt_kurzbz;
	$projektNew->nummer = $projekt->nummer;
	$projektNew->titel = $projekt->titel;
	$projektNew->beschreibung = $projekt->beschreibung;
	$projektNew->beginn = $projekt->beginn;
	$projektNew->ende = $projekt->ende;
	$projektNew->budget = $projekt->budget;
    $projektNew->farbe = $projekt->farbe;
	$projektNew->oe_kurzbz = $projekt->oe_kurzbz;
	$projektNew->aufwandstyp_kurzbz = $projekt->aufwandstyp_kurzbz;
	$projektNew->anzahl_ma = $projekt->anzahl_ma;
	$projektNew->aufwand_pt = $projekt->aufwand_pt;

	if($projekt->zeitaufzeichnung=='true')
	{
		$projektNew->zeitaufzeichnung = true;
	}
	else
	{
		$projektNew->zeitaufzeichnung = false;
	}

	if($projekt->neu=='true')
	{
		$projektNew->new = true;
	}
	else
	{
		$projektNew->new = false;
	}

	if($projektNew->save())
		return $projektNew->projekt_kurzbz;
	else
		return new SoapFault("Server", $projektNew->errormsg);
}

/**
 *
 * Speichert die Zuordnung eines Dokuments zu einem Projekt oder einer Phase
 * @param $username
 * @param $passwort
 * @param $projekt_kurzbz
 * @param $projektphase_id
 * @param $dms_id
 */
function saveProjektdokumentZuordnung($username, $passwort, $projekt_kurzbz, $projektphase_id, $dms_id)
{
	if(!$user = check_user($username, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('planner', null, 'sui'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Zuordnen von Dokumenten.");

	$dms = new dms();

	if($dms->saveProjektzuordnung($dms_id, $projekt_kurzbz, $projektphase_id))
		return true;
	else
		return new SoapFault("Server", $dms->errormsg);
}
?>
