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
require_once('../include/projektphase.class.php');
require_once('../include/datum.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$SOAPServer = new SoapServer(APP_ROOT."/soap/projektphase.wsdl.php?".microtime(true));
$SOAPServer->addFunction("saveProjektphase");
$SOAPServer->addFunction("deleteProjektphase");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 *
 * Speichert die vom Webservice übergebene Phase in die DB
 * @param $username
 * @param $passwort
 * @param $phase
 */
function saveProjektphase($username, $passwort, $phase)
{
	if(!$user = check_user($username, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('planner', null, 'sui'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Speichern von Phasen.");

	$projektphase = new projektphase();
	if($phase->projektphase_id!='')
	{
		$projektphase->load($phase->projektphase_id);
	}
	else
	{
		$projektphase->insertvon = $user;
		$projektphase->insertamum=date('Y-m-d H:i:s');
	}
	$projektphase->projektphase_id=$phase->projektphase_id;
	$projektphase->projekt_kurzbz=$phase->projekt_kurzbz;
	$projektphase->projektphase_fk=$phase->projektphase_fk;
	$projektphase->bezeichnung = $phase->bezeichnung;
	$projektphase->typ = $phase->typ;
	$projektphase->ressource_id = $phase->ressource_id;
	$projektphase->beschreibung = $phase->beschreibung;
	$projektphase->start = $phase->start;
	$projektphase->ende = $phase->ende;
	$projektphase->budget = $phase->budget;
	$projektphase->personentage = $phase->personentage;
    $projektphase->farbe = $phase->farbe;
	$projektphase->updatevon = $user;
	$projektphase->updateamum = date('Y-m-d H:i:s');

	if($phase->zeitaufzeichnung=='true')
	{
		$projektphase->zeitaufzeichnung = true;
	}
	else
	{
		$projektphase->zeitaufzeichnung = false;
	}

	if($phase->neu=='true')
	{
		$projektphase->new = true;
	}
	else
	{
		$projektphase->new = false;
	}

	if($projektphase->save())
		return $projektphase->projektphase_id;
	else
		return new SoapFault("Server", $projektphase->errormsg);
}

/**
 *
 * Loescht die übergebene Projektphase
 * @param $username
 * @param $passwort
 * @param $projektphase_kurzbz
 */
function deleteProjektphase($username, $passwort, $projektphase_id)
{
	if(!$user = check_user($username, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('planner', null, 'suid'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Loeschen von Phasen");

	$phase = new projektphase();
	if($phase->delete($projektphase_id))
		return "OK";
	else
		return new SoapFault("Server", $phase->errormsg);
}
?>
