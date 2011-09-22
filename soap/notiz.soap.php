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
require_once('../include/notiz.class.php');
require_once('../include/datum.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$SOAPServer = new SoapServer(APP_ROOT."/soap/notiz.wsdl.php?".microtime());
$SOAPServer->addFunction("saveNotiz");
$SOAPServer->addFunction("deleteNotiz");
$SOAPServer->addFunction("setErledigt");
$SOAPServer->handle();

// WSDL Chache auf aus
ini_set("soap.wsdl_cache_enabled", "0");

/**
 * 
 * Speichert die vom Webservice übergebenen Parameter in die DB
 * @param string $notiz_id
 * @param string $titel
 * @param string $text
 * @param string $verfasser_uid
 * @param string $bearbeiter_uid
 * @param string $start
 * @param string $ende
 * @param boolean $erledigt
 * @param string $projekt_kurzbz
 * @param string $projektphase_id
 * @param string $projekttask_id
 * @param string $uid
 * @param string $person_id
 * @param string $prestudent_id
 * @param string $bestellung_id
 */
function saveNotiz($notiz_id, $titel, $text, $verfasser_uid, $bearbeiter_uid, $start, $ende, $erledigt, $projekt_kurzbz, $projektphase_id, $projekttask_id, $uid, $person_id, $prestudent_id, $bestellung_id)
{ 	
	$user = get_uid();
		
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
		
	if(!$rechte->isBerechtigt('basis/notiz', null, 'sui'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Speichern von Notizen");
	
	$notiz = new notiz();
	if($notiz_id != '')
	{
		if($notiz->load($notiz_id))
		{
			$notiz->new = false;
		}
		else
			return new SoapFault("Server", "Fehler beim Laden"); 
	}
	else
	{
		$notiz->new=true;
		$notiz->insertvon = $user;
		$notiz->insertamum = date('Y-m-d H:i:s');
	}

	$notiz->titel=$titel;
	$notiz->text=$text;
	$notiz->verfasser_uid = $verfasser_uid;
	$notiz->bearbeiter_uid = $bearbeiter_uid;
	$notiz->start = $start;
	$notiz->ende = $ende;
	$notiz->erledigt=$erledigt;
	
	if($notiz->save())
	{
		if($notiz->new)
		{
			$notiz->projekt_kurzbz = $projekt_kurzbz;
			$notiz->projektphase_id = $projektphase_id;
			$notiz->projekttask_id = $projekttask_id;
			$notiz->uid = $uid;
			$notiz->person_id = $person_id;
			$notiz->prestudent_id = $prestudent_id;
			$notiz->bestellung_id = $bestellung_id;
			
			if(!$notiz->saveZuordnung())
				return new SoapFault("Server", $notiz->errormsg);
		}		
		return $notiz->notiz_id;
	} 
	else
		return new SoapFault("Server", $notiz->errormsg);
}

/**
 * 
 * Löscht die Notiz mit der vom Webservice übergebenen ID 
 * @param $notiz_id
 */
function deleteNotiz($notiz_id)
{
	$user = get_uid();
		
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
		
	if(!$rechte->isBerechtigt('basis/notiz', null, 'suid'))
		return new SoapFault("Server", "Sie haben keine Berechtigung zum Speichern von Notizen");
	
	$notiz = new notiz();
	if($notiz->delete($notiz_id))
		return "OK";
	else
		return new SoapFault("Server", $projekttask->errormsg);
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


