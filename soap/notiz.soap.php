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
session_start();

require_once('../config/vilesci.config.inc.php');
require_once('../include/notiz.class.php');
require_once('../include/datum.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/dms.class.php');

$SOAPServer = new SoapServer(APP_ROOT."/soap/notiz.wsdl.php?".microtime(true));
$SOAPServer->addFunction("saveNotiz");
$SOAPServer->addFunction("deleteNotiz");
$SOAPServer->addFunction("deleteDokument");
$SOAPServer->addFunction("setErledigt");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 *
 * Speichert Notizen in die Datenbank
 *
 * @param string $username
 * @param string $passwort
 * @param complextype $notiz
 */
function saveNotiz($username, $passwort, $notiz)
{

	if(!$user = check_user($username, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('basis/notiz', null, 'sui'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Speichern von Notizen");

	$notiz_obj = new notiz();
	if($notiz->notiz_id != '')
	{
		if($notiz_obj->load($notiz->notiz_id))
		{
			$notiz_obj->new = false;
		}
		else
			return new SoapFault("Server", "Fehler beim Laden");
	}
	else
	{
		$notiz_obj->new=true;
		$notiz_obj->insertvon = $user;
		$notiz_obj->insertamum = date('Y-m-d H:i:s');
	}

	$notiz_obj->titel=$notiz->titel;
	$notiz_obj->text=$notiz->text;
	$notiz_obj->verfasser_uid = $notiz->verfasser_uid;
	$notiz_obj->bearbeiter_uid = $notiz->bearbeiter_uid;
	$notiz_obj->start = $notiz->start;
	$notiz_obj->ende = $notiz->ende;
	$notiz_obj->erledigt=($notiz->erledigt=='true'?true:false);
	$notiz_obj->updateamum = date('Y-m-d H:i:s');
	$notiz_obj->updatevon = $user;

	if($notiz_obj->save())
	{
		if($notiz_obj->new)
		{
			$notiz_obj->projekt_kurzbz = $notiz->projekt_kurzbz;
			$notiz_obj->projektphase_id = $notiz->projektphase_id;
			$notiz_obj->projekttask_id = $notiz->projekttask_id;
			$notiz_obj->uid = $notiz->uid;
			$notiz_obj->person_id = $notiz->person_id;
			$notiz_obj->prestudent_id = $notiz->prestudent_id;
			$notiz_obj->bestellung_id = $notiz->bestellung_id;
			$notiz_obj->lehreinheit_id = $notiz->lehreinheit_id;
			$notiz_obj->anrechnung_id = $notiz->anrechnung_id;

			if(!$notiz_obj->saveZuordnung())
				return new SoapFault("Server", $notiz_obj->errormsg);
		}
		return $notiz_obj->notiz_id;
	}
	else
		return new SoapFault("Server", $notiz_obj->errormsg);
}

/**
 *
 * Löscht die Notiz mit der vom Webservice übergebenen ID
 * @param $notiz_id
 */
function deleteNotiz($username, $passwort, $notiz_id)
{
	if(!$user = check_user($username, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('basis/notiz', null, 'suid'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Loeschen von Notizen");

	$notiz = new notiz();
	if($notiz->delete($notiz_id))
		return "OK";
	else
		return new SoapFault("Server", $projekttask->errormsg);
}

/**
 *
 * Löscht das Dokument mit der vom Webservice übergebenen DMS-ID
 * @param $dms_id
 */
function deleteDokument($username, $passwort, $dms_id)
{
	if(!$user = check_user($username, $passwort))
		return new SoapFault("Server", "Invalid Credentials");

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('basis/notiz', null, 'suid'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Loeschen von Dokumenten");

	$dms = new dms();
	if($dms->deleteDms($dms_id))
		return "OK";
	else
		return new SoapFault("Server", $dms->errormsg);
}

/**
 *
 * Setzt den erledigt Status
 * @param $notiz_id
 * @param $erledigt
 */
function setErledigt($notiz_id, $erledigt)
{
	$user = get_uid();

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('basis/notiz', null, 'su'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Speichern von Notizen");

	$notiz = new notiz();
	if($notiz->load($notiz_id))
	{
		$notiz->erledigt=$erledigt;
		$notiz->updateamum = date('Y-m-d H:i:s');
		$notiz->updatevon = $user;

		if($notiz->save())
		{
			return true;
		}
		else
			return new SoapFault("Server", $notiz->errormsg);
	}
	else
		return new SoapFault("Server", "Fehler beim Laden");
}
?>
