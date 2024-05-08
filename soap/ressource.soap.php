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
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/functions.inc.php');

$SOAPServer = new SoapServer(APP_ROOT."/soap/ressource.wsdl.php?".microtime(true));
$SOAPServer->addFunction("saveRessource");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 *
 * Speichert die Ressource
 * @param $username
 * @param $passwort
 * @param $ressource
 */
function saveRessource($username, $passwort, $ressource)
{
	if(!$user = check_user($username, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('planner', null, 'sui'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Speichern von Ressourcen.");

	$ressourceNew = new ressource();
	if($ressource_id!='')
	{
		$ressourceNew->load($ressource->ressource_id);
		$ressourceNew->new = false;
	}
	else
	{
		$ressourceNew->new = true;
		$ressourceNew->insertvon = $user;
	}
	$ressourceNew->ressource_id=$ressource->ressource_id;
	$ressourceNew->bezeichnung=$ressource->bezeichnung;
	$ressourceNew->beschreibung=$ressource->beschreibung;
	$ressourceNew->mitarbeiter_uid = $ressource->mitarbeiter_uid;
	$ressourceNew->student_uid = $ressource->student_uid;
	$ressourceNew->betriebsmittel_id = $ressource->betriebsmittel_id;
	$ressourceNew->firma_id = $ressource->firma_id;
	$ressourceNew->updatevon = $user;

	if($ressourceNew->save())
		return $ressourceNew->ressource_id;
	else
		return new SoapFault("Server", $ressourceNew->errormsg);
}
?>
