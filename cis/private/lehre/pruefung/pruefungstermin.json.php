<?php
/*
 * Copyright 2014 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 * Authors: Stefan Puraner	<puraner@technikum-wien.at>
 * 			Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/pruefungstermin.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/pruefungsfenster.class.php');
require_once('../../../../include/pruefungsanmeldung.class.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/globals.inc.php');
require_once('../../../../include/sprache.class.php');

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$method = isset($_REQUEST['method'])?$_REQUEST['method']:'';

switch($method)
{
	case 'loadPruefungstypen':
		$data = loadPruefungstypen("false");
		break;
	case 'loadStudiensemester':
		$studiensemester = new studiensemester();
		$aktStudiensemester = $studiensemester->getaktorNext();
		$prevSemester = empty($_POST["prevSemester"]) ? 0 : $_POST["prevSemester"];
		$data = loadStudiensemester($aktStudiensemester, $prevSemester);
		break;
	case 'getPruefungsfensterByStudiensemester':
		$studiensemester = new studiensemester();
		$aktStudiensemester = $studiensemester->getaktorNext();
		$studiensemester_kurzbz = isset($_REQUEST["studiensemester_kurzbz"]) ? $_REQUEST["studiensemester_kurzbz"] : $aktStudiensemester;
		$data = getPruefungsfensterByStudiensemester($studiensemester_kurzbz, $aktStudiensemester);
		break;
	case 'savePruefungstermin':
		$studiensemester_kurzbz = isset($_POST["studiensemester_kurzbz"])?$_POST["studiensemester_kurzbz"]:null;
		$pruefungsfenster_id = isset($_POST["pruefungsfenster_id"])?$_POST["pruefungsfenster_id"]:null;
		$pruefungstyp_kurzbz = isset($_POST["pruefungstyp_kurzbz"])?$_POST["pruefungstyp_kurzbz"]:null;
		$titel = isset($_POST["titel"])?$_POST["titel"]:null;
		$beschreibung = isset($_POST["beschreibung"])?$_POST["beschreibung"]:null;
		$methode = isset($_POST["methode"])?$_POST["methode"]:null;
		$einzeln = (isset($_POST["einzeln"]) && $_POST["einzeln"] ==="true")?true:false;
		$lehrveranstaltungen = isset($_POST["lehrveranstaltungen"]) ? $_POST["lehrveranstaltungen"] : null;
		$termine = isset($_POST["termine"])?$_POST["termine"]:null;
		$pruefungsintervall = NULL;
		if(isset($_REQUEST["pruefungsintervall"]) && ($_REQUEST["pruefungsintervall"] !== "false"))
		{
			$pruefungsintervall = $_REQUEST["pruefungsintervall"];
		}
		if($rechte->isBerechtigt('lehre/pruefungsterminAdmin'))
		{
			$mitarbeiter_uid = $_REQUEST["mitarbeiter_uid"];
		}
		else if($rechte->isBerechtigt('lehre/pruefungstermin'))
		{
			$mitarbeiter_uid = $uid;
		}
		else
		{
			$data['result']='false';
			$data['error']='true';
			$data['errormsg']=$p->t('global/keineBerechtigung');
			break;
		}
		$data = savePruefungstermin($mitarbeiter_uid, $studiensemester_kurzbz, $pruefungsfenster_id, $pruefungstyp_kurzbz, $titel, $beschreibung, $methode, $einzeln, $lehrveranstaltungen, $termine, $pruefungsintervall);
		break;
	case 'getLehrveranstaltungenByMitarbeiter':
		$mitarbeiter_uid = $_POST["mitarbeiter_uid"];
		$studiensemester_kurzbz = $_POST["studiensemester_kurzbz"];
		$data = getLehrveranstaltungenByMitarbeiter($mitarbeiter_uid, $studiensemester_kurzbz);
		break;
	case 'updatePruefungstermin':
		$pruefung_id = isset($_POST["pruefung_id"])?$_POST["pruefung_id"]:null;
		$studiensemester_kurzbz = isset($_POST["studiensemester_kurzbz"])?$_POST["studiensemester_kurzbz"]:null;
		$pruefungsfenster_id = isset($_POST["pruefungsfenster_id"])?$_POST["pruefungsfenster_id"]:null;
		$pruefungstyp_kurzbz = isset($_POST["pruefungstyp_kurzbz"])?$_POST["pruefungstyp_kurzbz"]:null;
		$titel = isset($_POST["titel"])?$_POST["titel"]:null;
		$beschreibung = isset($_POST["beschreibung"])?$_POST["beschreibung"]:null;
		$methode = isset($_POST["methode"])?$_POST["methode"]:null;
		$einzeln = (isset($_POST["einzeln"]) && $_POST["einzeln"] ==="true")?true:false;
		$lehrveranstaltungen = isset($_POST["lehrveranstaltungen"]) ? $_POST["lehrveranstaltungen"] : null;
		$termine = isset($_POST["termine"])?$_POST["termine"]:null;
		$termineNeu = isset($_POST["termineNeu"])?$_POST["termineNeu"]:null;
		$pruefungsintervall = NULL;
		if((isset($_REQUEST["pruefungsintervall"]) && $_REQUEST["pruefungsintervall"] !== false))
		{
			$pruefungsintervall = $_REQUEST["pruefungsintervall"];
		}
		if($rechte->isBerechtigt('lehre/pruefungsterminAdmin'))
		{
			$mitarbeiter_uid = $_REQUEST["mitarbeiter_uid"];
		}
		else if($rechte->isBerechtigt('lehre/pruefungstermin'))
		{
			$mitarbeiter_uid = $uid;
		}
		else
		{
			$data['result']='false';
			$data['error']='true';
			$data['errormsg']=$p->t('global/keineBerechtigung');
			break;
		}
		$data = updatePruefungstermin($mitarbeiter_uid, $pruefung_id, $studiensemester_kurzbz, $pruefungsfenster_id, $pruefungstyp_kurzbz, $titel, $beschreibung, $methode, $einzeln, $lehrveranstaltungen, $termine, $termineNeu, $pruefungsintervall);
		break;
	case 'deleteLehrveranstaltungFromPruefung':
		if(!($rechte->isBerechtigt('lehre/pruefungsterminAdmin')) && !($rechte->isBerechtigt('lehre/pruefungstermin')))
		{
			$data['result']='false';
			$data['error']='true';
			$data['errormsg']=$p->t('global/keineBerechtigung');
			break;
		}
		$lvId = $_POST["lehrveranstaltung_id"];
		$pruefung_id = $_POST["pruefung_id"];
		$data = deleteLehrveranstaltungFromPruefung($lvId, $pruefung_id);
		break;
	case 'stornoPruefung':
		if(!($rechte->isBerechtigt('lehre/pruefungsterminAdmin')) && !($rechte->isBerechtigt('lehre/pruefungstermin')))
		{
			$data['result']='false';
			$data['error']='true';
			$data['errormsg']=$p->t('global/keineBerechtigung');
			break;
		}
		$pruefung_id = $_REQUEST["pruefung_id"];
		$data = stornoPruefung($pruefung_id);
		break;
	case 'deleteTermin':
		if(!($rechte->isBerechtigt('lehre/pruefungsterminAdmin')) && !($rechte->isBerechtigt('lehre/pruefungstermin')))
		{
			$data['result']='false';
			$data['error']='true';
			$data['errormsg']=$p->t('global/keineBerechtigung');
			break;
		}
		$pruefung_id = $_REQUEST["pruefung_id"];
		$pruefungstermin_id = $_REQUEST["pruefungstermin_id"];
		$data = deleteTermin($pruefung_id, $pruefungstermin_id);
		break;
	case 'getAllPruefungen':
		if($rechte->isBerechtigt('lehre/pruefungsterminAdmin'))
		{
			$data = getAllPruefungen($_REQUEST["uid"]);
		}
		else if($rechte->isBerechtigt('lehre/pruefungstermin'))
		{
			$data = getAllPruefungen($uid);
		}
		else
		{
			$data['result']='false';
			$data['error']='true';
			$data['errormsg']=$p->t('global/keineBerechtigung');
			break;
		}
		break;
	default:
		break;
}
echo json_encode($data);

/**
 * Lädt alle Prüfungstypen aus der Datenbank
 * @param boolean $abschluss Gibt an ob Prüfungstypen einer Abschlussprüfunge geladen werden sollen oder nicht
 * @return Array
 */
function loadPruefungstypen($abschluss)
{
	$pruefungstermin = new pruefungstermin();
	$pruefungstypen = $pruefungstermin->getAllPruefungstypen($abschluss);
	if(!empty($pruefungstypen))
	{
		$data['result']=$pruefungstypen;
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$pruefungstermin->errormsg;
	}
	return $data;
}

/**
 * Lädt alle Studiensemester aus der Datenbank
 * @param String $aktStudiensemester das Aktuelle Studiensemester
 * @param int $prevSemester wie viele vergangene Semester sollen geladen werden
 * @return Array
 */
function loadStudiensemester($aktStudiensemester = null, $prevSemester = 0)
{
	$studiensemester = new studiensemester();
	$prevSemester == 0 ? $studiensemester->getAll() : $studiensemester->getPlusMinus(null, $prevSemester);

	if(!empty($studiensemester->studiensemester))
	{
		$data['result']=$studiensemester->studiensemester;
		if(!is_null($aktStudiensemester))
		{
			$data['aktSem']=$aktStudiensemester;
		}
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$studiensemester->errormsg;
	}
	return $data;
}

/**
 * Lädt alle Prüfungsfenster eines Studiensemesters
 * @param String $studiensemester_kurzbz Kurzbezeichnung des Studiensemesters
 * @return Array
 */
function getPruefungsfensterByStudiensemester($studiensemester_kurzbz)
{
	$pruefungsfenster = new pruefungsfenster();
	if($pruefungsfenster->getByStudiensemester($studiensemester_kurzbz))
	{
		$data['result']=$pruefungsfenster->result;
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$pruefungsfenster->errormsg;
	}
	return $data;
}

/**
 * Speichet einen Prüfungstermin
 * @param int $uid UID des Lektors
 * @param String $studiensemester_kurzbz Kurzbezeichnung des Studiensemesters
 * @param int $pruefungsfenster_id ID des Prüfungsfensters
 * @param String $pruefungstyp_kurzbz Kurzbezeichnung des Prüfungstyps
 * @param String $titel Titel der Prüfung
 * @param String $beschreibung Beschreibung zur Prüfung
 * @param String $methode Methode der Prüfung
 * @param boolen $einzeln TRUE, wenn Einzelprüfung
 * @param Array $lehrveranstaltungen Lehrveranstaltungen zur Prüfung
 * @param Array $termine Termine zur Prüfung
 * @return Array
 */
function savePruefungstermin($uid, $studiensemester_kurzbz, $pruefungsfenster_id, $pruefungstyp_kurzbz, $titel, $beschreibung, $methode, $einzeln, $lehrveranstaltungen, $termine, $pruefungsintervall)
{
	global $p;
	if($lehrveranstaltungen === null)
	{
		$data['error']='true';
		$data['errormsg']=$p->t('pruefung/keineLvAngegeben');
		return $data;
	}
	$termineArray = array();
	foreach ($termine as $key => $t)
	{
		$termin = new stdClass();
		$date = $t["datum"];
		$beginn = $t["beginn"];
		$ende = $t["ende"];
		$termin->min = $t["min"];
		$termin->max = $t["max"];
		$termin->beginn = date('Y-m-d H:i', strtotime($date." ".$beginn));
		$termin->ende = date('Y-m-d H:i', strtotime($date." ".$ende));
		$termin->sammelklausur = $t["sammelklausur"];

		if(!(checkCollision($uid, $termin->beginn, $termin->ende)))
		{
			array_push($termineArray, $termin);
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$p->t('pruefung/kollisionMitAnderemTermin');
			return $data;
		}
	}

	$pruefung = new pruefungCis();
	$pruefung->termine = $termineArray;
	$pruefung->mitarbeiter_uid = $uid;
	$pruefung->studiensemester_kurzbz = $studiensemester_kurzbz;
	$pruefung->pruefungsfenster_id = $pruefungsfenster_id;
	$pruefung->pruefungstyp_kurzbz = $pruefungstyp_kurzbz;
	$pruefung->titel = $titel;
	$pruefung->beschreibung = $beschreibung;
	$pruefung->methode = $methode;
	$pruefung->einzeln = $einzeln;
	$pruefung->insertvon = get_uid();
	$pruefung->pruefungsintervall = $pruefungsintervall;

	foreach ($lehrveranstaltungen as $lv)
	{
		if($lv != "null")
		{
			array_push($pruefung->lehrveranstaltungen, $lv);
		}
	}

	if($pruefung->save(true))
	{
		$data['result']="true";
		$data['error']='false';
		$data['errormsg']='';

	//TODO Mail-Inhalt fehlt
//						foreach ($pruefung->lehrveranstaltungen as $lvId)
//						{
//							$lv = new lehrveranstaltung($lvId);
//							$text = "Ein Prüfungstermin zu Ihrer Lehrveranstaltung ".$lv->bezeichnung." wurde angelegt.\n"
//									. "Die Prüfung kann am "
//									.date('d.m.Y', strtotime($pruefung->termine[0]->beginn))." "
//									. "in der Zeit von "
//									.date('H:i', strtotime($pruefung->termine[0]->beginn))." bis "
//									.date('H:i', strtotime($pruefung->termine[0]->ende))." abgelegt werden.";
//							//$text = "test";
//							$empfaenger = $lv->getStudentsOfLv($lvId, $pruefung->studiensemester_kurzbz);
//							$mailto = "";
//							foreach ($empfaenger as $e) {
//								$mailto .= $e.'@'.DOMAIN.', ';
//							}
//
//							$email = new mail($mailto, "fhcomplete", "Prüfungstermin für ".$lv->bezeichnung, $text);
//							$email->setReplyTo($uid."@".DOMAIN);
////							var_dump($email);
//							$email->send();
//						}
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$pruefung->errormsg;
	}
	return $data;
}

/**
 * Lädt alle Lehrveranstaltungen eines Mitarbeiters
 * @param int $mitarbeiter_uid UID des Mitarbeiters
 * @param String $studiensemester_kurzbz Kurzbezeichnung des Studiensemesters
 * @return Array
 */
function getLehrveranstaltungenByMitarbeiter($mitarbeiter_uid, $studiensemester_kurzbz)
{
	$lehrveranstaltung = new lehrveranstaltung();
	if($lehrveranstaltung->getLVByMitarbeiter($mitarbeiter_uid, $studiensemester_kurzbz))
	{
		$stg = new studiengang();
		foreach($lehrveranstaltung->lehrveranstaltungen as $key=>$lv)
		{
			$stg->load($lv->studiengang_kz);
			$lv->studiengang_bezeichnung = $stg->kurzbzlang;
		}
		$data['result']=$lehrveranstaltung->lehrveranstaltungen;
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$lehrveranstaltung->errormsg;
	}
	return $data;
}

/**
 * Speichet einen Prüfungstermin
 * @param int $uid UID des Lektors
 * @param String $studiensemester_kurzbz Kurzbezeichnung des Studiensemesters
 * @param int $pruefungsfenster_id ID des Prüfungsfensters
 * @param String $pruefungstyp_kurzbz Kurzbezeichnung des Prüfungstyps
 * @param String $titel Titel der Prüfung
 * @param String $beschreibung Beschreibung zur Prüfung
 * @param String $methode Methode der Prüfung
 * @param boolen $einzeln TRUE, wenn Einzelprüfung
 * @param Array $lehrveranstaltungen Lehrveranstaltungen zur Prüfung
 * @param Array $termine Termine zur Prüfung (bestehende)
 * @param type $termineNeu Neu hinzugefügte Termine
 * @return Array
 */
function updatePruefungstermin($uid, $pruefung_id, $studiensemester_kurzbz, $pruefungsfenster_id, $pruefungstyp_kurzbz, $titel, $beschreibung, $methode, $einzeln, $lehrveranstaltungen, $termine, $termineNeu, $pruefungsintervall)
{
	global $p;
	$pruefungsfenster = new pruefungsfenster();
	$pruefungsfenster->load($pruefungsfenster_id);
	$datum = new datum();
	$pruefung = new pruefungCis();
	$pruefung->load($pruefung_id);

	if($termineNeu !== null)
	{
		$termineNeuArray = array();
		foreach ($termineNeu as $key => $t)
		{
			$termin = new stdClass();
			$datum = new datum();
			$date = $t["datum"];
			$beginn = $t["beginn"];
			$ende = $t["ende"];
			$termin->min = $t["min"];
			$termin->max = $t["max"];
			$termin->beginn = date('Y-m-d H:i', strtotime($date." ".$beginn));
			$termin->ende = date('Y-m-d H:i', strtotime($date." ".$ende));
			$termin->sammelklausur = $t["sammelklausur"];

			if(!(checkCollision($uid, $termin->beginn, $termin->ende)))
			{
				array_push($termineNeuArray, $termin);
			}
			else
			{
				$data['error']='true';
				$data['errormsg']=$p->t('pruefung/kollisionMitAnderemTermin');
				return $data;
			}
		}
		foreach ($termineNeuArray as $t)
		{
			$pruefung->saveTerminPruefung($pruefung_id, $t->beginn, $t->ende, $t->max, $t->min, $t->sammelklausur);
		}
	}

	if($termine !== null)
	{
		$termineArray = array();
		foreach ($termine as $key => $t)
		{
			$termin = new stdClass();
			$datum = new datum();
			$date = $t["datum"];
			$beginn = $t["beginn"];
			$ende = $t["ende"];
			$termin->pruefungstermin_id = $t["pruefungstermin_id"];
			$termin->min = $t["min"];
			$termin->max = ($t["max"] === "null") ? NULL : $t["max"];
			$termin->beginn = date('Y-m-d H:i', strtotime($date." ".$beginn));
			$termin->ende = date('Y-m-d H:i', strtotime($date." ".$ende));
		}
		foreach($termineArray as $key=>$t)
		{
			$termineArray[$key] = (object) $t;
		}
		$pruefung->termine = $termineArray;
	}

	$pruefung->mitarbeiter_uid = $uid;
	$pruefung->studiensemester_kurzbz = $studiensemester_kurzbz;
	$pruefung->pruefungsfenster_id = $pruefungsfenster_id;
	$pruefung->pruefungstyp_kurzbz = $pruefungstyp_kurzbz;
	$pruefung->titel = $titel;
	$pruefung->beschreibung = $beschreibung;
	$pruefung->methode = $methode;
	$pruefung->einzeln = $einzeln;
	$pruefung->updatevon = get_uid();
	$pruefung->pruefungsintervall = $pruefungsintervall;
	if($lehrveranstaltungen !== null)
	{
		foreach ($lehrveranstaltungen as $lv)
		{
			if($lv != "null")
			{
			array_push($pruefung->lehrveranstaltungen, $lv);
			}
		}
	}
	if($pruefung->save(false))
	{
		$data['result']="true";
		$data['error']='false';
		$data['errormsg']='';

		//TODO Mail-Inhalt fehlt
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$pruefung->errormsg;
	}
	return $data;
}

/**
 * Löscht Lehrveranstaltungen von einer Prüfung
 * @param int $lvId ID der Lehrveranstaltung
 * @param int $pruefung_id ID der Prüfung
 * @return Array
 */
function deleteLehrveranstaltungFromPruefung($lvId, $pruefung_id)
{
	$pruefung = new pruefungCis();
	$pruefung->load($pruefung_id);
	if($pruefung->deleteLehrveranstaltungPruefung($lvId, $pruefung_id))
	{
		$data['result']='true';
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$pruefung->errormsg;
	}
	return $data;
}

/**
 * Storniert eine Prüfung
 * @param int $pruefung_id ID der Prüfung
 * @return Array
 */
function stornoPruefung($pruefung_id)
{
	$pruefung = new pruefungCis();
	if($pruefung->pruefungStornieren($pruefung_id))
	{
		$data['result']="true";
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$pruefung->errormsg;
	}
	return $data;
}

/**
 * Löscht einen Termin einer Prüfung
 * @param int $pruefung_id ID der Prüfung
 * @param int $pruefungstermin_id ID des Termins
 * @return Array
 */
function deleteTermin($pruefung_id, $pruefungstermin_id)
{
	$pruefung = new pruefungCis();
	$pruefung->load($pruefung_id);
	if($pruefung->deleteTerminPruefung($pruefungstermin_id))
	{
		$data['result']='true';
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$pruefung->errormsg;
	}
	return $data;
}

/**
 * Lädt alle Prüfungen eines Mitarbeiters
 * @param String $mitarbeiter_uid UID des Mitarbeiters
 * @return Array
 */
function getAllPruefungen($mitarbeiter_uid)
{
	$pruefung = new pruefungCis();
	if($pruefung->getAllPruefungen($mitarbeiter_uid))
	{
		foreach ($pruefung->result as $prf)
		{
			$prf->getLehrveranstaltungenByPruefung();
			foreach($prf->lehrveranstaltungen as $key=>$lv)
			{
				$temp = new lehrveranstaltung($lv->lehrveranstaltung_id);
				$array = $temp->cleanResult();
				$prf->lehrveranstaltungen[$key] = $array[0];
			}
			$prf->getTermineByPruefung();
		}
		$data['result']=$pruefung->result;
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$pruefung->errormsg;
	}
	return $data;
}

/**
 * Überprüft ob das angegebene Datum innerhalb eines Prüfungsfensters ist
 * @param int $pruefungsfenster_id ID des Prüfungsfensters
 * @param String $datum
 * @return boolean
 */
function checkTerminPruefungsfenster($pruefungsfenster_id, $datum)
{
	$pruefungsfenster = new pruefungsfenster($pruefungsfenster_id);
	$date = new datum();
	if($date->between($pruefungsfenster->start, $pruefungsfenster->ende, $datum))
	{
		return true;
	}
	return false;
}

/**
 * Überprüft ob es eine Kollision zu anderen Prüfungen des Mitarbeiters gibt
 * @param String $uid UID des Mitarbeiters
 * @param String $beginn Beginn des Termins
 * @param String $ende Ende des Termins
 * @return boolean
 */
function checkCollision($uid, $beginn, $ende)
{
	$collision = false;
	$pruefung = new pruefungCis();
	$pruefung->getAllPruefungen($uid);
	$datum = new datum();
	foreach($pruefung->result as $prf)
	{
		$prf->getTermineByPruefung();
		foreach($prf->termine as $termin)
		{
			if(($datum->between($termin->von, $termin->bis, $beginn)) || ($datum->between($termin->von, $termin->bis, $ende)))
			{
				$collision = true;
			}
		}
	}
	if($collision)
	{
		return true;
	}
	return false;
}
?>
