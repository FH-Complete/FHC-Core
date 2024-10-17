<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors:		Stefan Puraner	<puraner@technikum-wien.at>
 */
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../../../../config/global.config.inc.php');
if (defined('CIS_PRUEFUNG_SET_ZEUGNISNOTE') && CIS_PRUEFUNG_SET_ZEUGNISNOTE)
	require_once('../../../../config/vilesci.config.inc.php');
else
	require_once('../../../../config/cis.config.inc.php');

require_once('../../../../include/functions.inc.php');
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/note.class.php');
require_once('../../../../include/zeugnisnote.class.php');
require_once('../../../../include/pruefung.class.php');
require_once('../../../../include/pruefungsanmeldung.class.php');
require_once('../../../../include/student.class.php');
require_once('../../../../include/pruefungstermin.class.php');
require_once('../../../../include/datum.class.php');
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

$studiensemester = new studiensemester();
$aktStudiensemester = $studiensemester->getaktorNext();

$method = filter_input(INPUT_POST, 'method');

switch($method)
{
	case 'getPruefungMitarbeiter':
		if ($rechte->isBerechtigt('lehre/pruefungsbeurteilungAdmin'))
		{
			$mitarbeiter_uid = filter_input(INPUT_POST, 'mitarbeiter_uid');
		}
		else if ($rechte->isBerechtigt('lehre/pruefungsbeurteilung'))
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
		$data = getPruefungMitarbeiter($mitarbeiter_uid);
		break;
	case 'getNoten':
		if (!($rechte->isBerechtigt('lehre/pruefungsbeurteilungAdmin')) && !($rechte->isBerechtigt('lehre/pruefungsbeurteilung')))
		{
			$data['result']='false';
			$data['error']='true';
			$data['errormsg']=$p->t('global/keineBerechtigung');
			break;
		}
		$data = getNoten();
		break;
	case 'saveBeurteilung':
		$lehrveranstaltung_id = filter_input(INPUT_POST, 'lehrveranstaltung_id');
		$student_uid = filter_input(INPUT_POST, 'student_uid');
		if ($rechte->isBerechtigt('lehre/pruefungsbeurteilungAdmin'))
		{
			$mitarbeiter_uid = filter_input(INPUT_POST, 'mitarbeiter_uid');
		}
		else if ($rechte->isBerechtigt('lehre/pruefungsbeurteilung'))
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
		$note = filter_input(INPUT_POST, 'note');
		$pruefung_id = filter_input(INPUT_POST, 'pruefung_id');
		$datum = filter_input(INPUT_POST, 'datum');
		$anmerkung = filter_input(INPUT_POST, 'anmerkung');
		$pruefungsanmeldung_id = filter_input(INPUT_POST, 'pruefungsanmeldung_id');
		$data = saveBeurteilung($lehrveranstaltung_id, $student_uid, $mitarbeiter_uid, $note, $pruefung_id, $datum, $anmerkung, $pruefungsanmeldung_id, $uid);
		break;
	case 'updateBeurteilung':
		if ($rechte->isBerechtigt('lehre/pruefungsbeurteilungAdmin'))
		{
			$mitarbeiter_uid = filter_input(INPUT_POST, 'mitarbeiter_uid');
		}
		else if ($rechte->isBerechtigt('lehre/pruefungsbeurteilung'))
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
		$pruefung_id = filter_input(INPUT_POST, 'pruefung_id');
		$note = filter_input(INPUT_POST, 'note');
		$anmerkung = filter_input(INPUT_POST, 'anmerkung');
		$data = updateBeurteilung($pruefung_id, $note, $mitarbeiter_uid, $anmerkung);
		break;
	case 'loadPruefung':
		if (!($rechte->isBerechtigt('lehre/pruefungsbeurteilungAdmin')) && ($rechte->isBerechtigt('lehre/pruefungsbeurteilung')))
		{
			$data['result']='false';
			$data['error']='true';
			$data['errormsg']=$p->t('global/keineBerechtigung');
			break;
		}
		$pruefung_id = filter_input(INPUT_POST, 'pruefung_id');
		$data = loadPruefung($pruefung_id);
		break;
	case 'getBeurteilung':
		if (!($rechte->isBerechtigt('lehre/pruefungsbeurteilungAdmin')) && !($rechte->isBerechtigt('lehre/pruefungsbeurteilung')))
		{
			$data['result']='false';
			$data['error']='true';
			$data['errormsg']=$p->t('global/keineBerechtigung');
			break;
		}
		$pruefungsanmeldung_id = filter_input(INPUT_POST, 'pruefungsanmeldung_id');
		$data = getBeurteilung($pruefungsanmeldung_id);
		break;
	case 'getAnmeldungenTermin':
		if (!($rechte->isBerechtigt('lehre/pruefungsbeurteilungAdmin')) && !($rechte->isBerechtigt('lehre/pruefungsbeurteilung')))
		{
			$data['result']='false';
			$data['error']='true';
			$data['errormsg']=$p->t('global/keineBerechtigung');
			break;
		}
		$lehrveranstaltung_id = filter_input(INPUT_POST, 'lehrveranstaltung_id');
		$pruefungstermin_id = filter_input(INPUT_POST, 'pruefungstermin_id');
		$data = getAnmeldungenTermin($lehrveranstaltung_id, $pruefungstermin_id);
		break;
	default:
		break;
}

echo json_encode($data);

/**
 * Lädt alle Prüfungen eines Lektors/Mitarbeiters
 * @param type $uid UID des Lektors/Mitarbeiters
 * @return Array
 */
function getPruefungMitarbeiter($uid = null)
{
	global $p;
	$lehrveranstaltung = new lehrveranstaltung();
	if ($uid !== null)
	{
		$lehrveranstaltung->getLVByMitarbeiter($uid);
		$result = array();
		foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
		{
			$pruefung = new pruefungCis();
			$pruefung->getPruefungByLv($lv->lehrveranstaltung_id);
			if (!empty($pruefung->lehrveranstaltungen))
			{
				foreach($pruefung->lehrveranstaltungen as $tempLv)
				{
					$prf = new pruefungCis($tempLv->pruefung_id);
					$prf->getTermineByPruefung();
					$tempLv->pruefung = $prf;
				}
				$lv->pruefung = $pruefung;
				array_push($result, $lv);
			}
		}
	}

	if (!empty($result))
	{
		$data['result']=$result;
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$p->t('pruefung/keinePruefungenVorhanden');
	}
	return $data;
}

/**
 * Lädt alle Noten per AJAX aus der Datenbank
 * @return Array
 */
function getNoten()
{
	$note = new note();
	if ($note->getAll())
	{
		$data['result']=$note->result;
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$note->errormsg;
	}
	return $data;
}

/**
 * Speichert eine Beurteilung
 * @param int $lehrveranstaltung_id ID der Lehrveranstaltung
 * @param String $student_uid UID des Studenten
 * @param String $mitarbeiter_uid UID des Lektors
 * @param int $note Prüfungsnote
 * @param int $pruefung_id ID der Prüfung
 * @param String $datum Datum (YYYY-MM-DD)
 * @param String $anmerkung Anmerkung zur Beurteilung
 * @param int $pruefungsanmeldung_id ID der Anmeldung
 * @param String $uid UID des aktuellen Benutzers
 * @return Arrray
 */
function saveBeurteilung($lehrveranstaltung_id, $student_uid, $mitarbeiter_uid, $note, $pruefung_id, $datum, $anmerkung, $pruefungsanmeldung_id, $uid)
{
	global $p;
	$pruefungCis = new pruefungCis($pruefung_id);
	$lehrveranstaltung = new lehrveranstaltung();
	$lehreinheiten = $lehrveranstaltung->getLehreinheitenOfLv($lehrveranstaltung_id, $student_uid);
	$pruefung = new pruefung();
	$pruefung->new = true;
	if (!empty($lehreinheiten))
	{
		$pruefungsanmeldung = new pruefungsanmeldung($pruefungsanmeldung_id);
		$pruefungstermin = new pruefungstermin($pruefungsanmeldung->pruefungstermin_id);

		$pruefung->lehreinheit_id = $lehreinheiten[0];
		$pruefung->student_uid = $student_uid;
		$pruefung->mitarbeiter_uid = $mitarbeiter_uid;
		$pruefung->note = $note;
		$pruefung->pruefungstyp_kurzbz = $pruefungsanmeldung->pruefungstyp_kurzbz;
		$pruefung->datum = $datum;
		$pruefung->anmerkung = $anmerkung;
		$pruefung->pruefungsanmeldung_id = $pruefungsanmeldung_id;
		$pruefung->insertvon = $uid;
		$pruefung->insertamum = date('Y-m-d H:i:s');

		$datum = new datum();

		if ($datum->between("", date("Y-m-d", time()), $pruefungstermin->von))
		{
			if ($pruefung->save())
			{
				$data['result']=$pruefung->pruefung_id;
				$data['error']='false';
				$data['errormsg']='';
			}
			else
			{
				$data['error']='true';
				$data['errormsg']=$pruefung->errormsg;
			}
			if (defined('CIS_PRUEFUNG_SET_ZEUGNISNOTE') && CIS_PRUEFUNG_SET_ZEUGNISNOTE)
			{
				$zeugnisnote = new zeugnisnote();
				$zeugnisnote->new = true;
				$zeugnisnote->lehrveranstaltung_id = $lehrveranstaltung_id;
				$zeugnisnote->student_uid = $student_uid;
				$zeugnisnote->studiensemester_kurzbz = $pruefungCis->studiensemester_kurzbz;
				$zeugnisnote->note = $note;
				$zeugnisnote->benotungsdatum = $pruefung->datum;
				$zeugnisnote->insertamum = date('Y-m-d H:i:s');
				$zeugnisnote->insertvon = $uid;
				$zeugnisnote_check = new zeugnisnote();
				if (!$zeugnisnote_check->load($zeugnisnote->lehrveranstaltung_id, $zeugnisnote->student_uid, $zeugnisnote->studiensemester_kurzbz))
				{
					$zeugnisnote->save(true);
				}
				else
				{
					$data['error'] = 'true';
					$data['errormsg'] = 'Existing Grade';
				}
			}
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$p->t('pruefung/terminNichtInDerVergangenheit');
		}
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$p->t('pruefung/keineLehreinheitenVorhanden');
	}

	return $data;
}

/**
 * Aktualisiert den Datensatz einer Beurteilung
 * @param int $pruefung_id ID der Prüfung
 * @param int $note Prüfungsnote
 * @param String $uid UID des aktuellen Benutzers
 * @return Array
 */
function updateBeurteilung($pruefung_id, $note, $uid, $anmerkung)
{
	global $p;
	$pruefung = new pruefung($pruefung_id);
	$pruefung->new = FALSE;
	$pruefung->note = $note;
	$pruefung->anmerkung = $anmerkung;
	$pruefung->updatevon = $uid;
	$pruefung->updateamum = date('Y-m-d H:i:s');
	if ($pruefung->save())
	{
		$data['result']=$pruefung->pruefung_id;
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
 * Lädt die Beurteilung zu einer Anmeldung
 * @param int $pruefungsanmeldung_id ID einer Anmeldung
 * @return Array
 */
function getBeurteilung($pruefungsanmeldung_id)
{
	$pruefung = new pruefung();
	if ($pruefung->getPruefungByAnmeldung($pruefungsanmeldung_id))
	{
		$data['result']=$pruefung->pruefung_id;
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
 * Lädt alle Anmeldungen zu einem Prüfungstermin
 * @return Array
 */
function getAnmeldungenTermin($lehrveranstaltung_id, $pruefungstermin_id)
{
	global $p;
	$pruefungsanmeldung = new pruefungsanmeldung();
	$anmeldungen = $pruefungsanmeldung->getAnmeldungenByTermin($pruefungstermin_id, $lehrveranstaltung_id);
	foreach($anmeldungen as $a)
	{
		$student = new student($a->uid);
		$temp = new stdClass();
		$temp->vorname = $student->vorname;
		$temp->nachname = $student->nachname;
		$temp->uid = $student->uid;
		$a->student = $temp;
		$pruefung = new pruefung();
		$pruefung->getPruefungByAnmeldung($a->pruefungsanmeldung_id);
		$a->pruefung = $pruefung;
	}
	if (!empty($anmeldungen))
	{
		$data['result']=$anmeldungen;
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['error']='true';
		if ($pruefungsanmeldung->errormsg !== null)
		{
			$data['errormsg']=$pruefungsanmeldung->errormsg;
		}
		else
		{
			$data['errormsg']= $p->t('pruefung/keineAnmeldungenVorhanden');
		}
	}
	return $data;
}
