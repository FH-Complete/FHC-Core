<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/basis_db.class.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/studentnote.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/legesamtnote.class.php');
require_once('../../../../include/lvgesamtnote.class.php');
require_once('../../../../include/zeugnisnote.class.php');
require_once('../../../../include/pruefung.class.php');
require_once('../../../../include/mail.class.php');
require_once('../../../../include/benutzerfunktion.class.php');
require_once('../../../../include/benutzer.class.php');
require_once('../../../../include/student.class.php');
require_once('../../../../include/notenschluessel.class.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/note.class.php');


if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');

$sprache = getSprache();
$p = new phrasen($sprache);

$user = get_uid();

if(!check_lektor($user))
	die('Sie haben keine Berechtigung fuer diesen Bereich');


$lehreinheit_id='';
$lehreinheit_id_pr='';

if(isset($_GET['lvid']) && is_numeric($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id'])) //Lehreinheit_id
	$lehreinheit_id = $_GET['lehreinheit_id'];

if(isset($_GET['lehreinheit_id_pr']) && is_numeric($_GET['lehreinheit_id_pr'])) //Lehreinheit_id der pruefung
	$lehreinheit_id_pr = $_GET['lehreinheit_id_pr'];

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';
//Studiensemester laden
$stsem_obj = new studiensemester();
if($stsem=='')
	$stsem = $stsem_obj->getaktorNext();

//Notenanmerkung für Eintragung von nichtnumerischen Noten wie en (entschuldigt), ue(unentschuldigt)
$noten_anmerkung = array();
$noten_arr = array();
$note_obj = new note();
$note_obj->getAll();
foreach($note_obj->result as $row)
{
	$noten_anmerkung[$row->anmerkung] = $row->note;
	$noten_arr[$row->note] = $row;
}

if(!isset($_GET['typ']))
{
	$typ='Termin2';
}
else
{
	if(in_array($_GET['typ'], array('Termin2', 'Termin3')))
		$typ=$_GET['typ'];
	else
		die('Typ ist ungueltig');
}

if (isset($_REQUEST['sammel']) && $_REQUEST["sammel"] == 1)
{
	$errorMatrnr = '';
	$errorDatum = '';
	$errorNachp = '';

	foreach ($_POST as $row => $val)
	{
		if(mb_strstr(mb_strtolower($row), 'student_uid_'))
		{
			$id=mb_substr($row, mb_strlen('student_uid_'));

			$student_uid = $_POST['student_uid_'.$id];
			$note = null;
			$punkte = null;
			$datum = null;
			$response = '';
			if(isset($_POST['note_'.$id]))
				$note = $_POST['note_'.$id];
			elseif(isset($_POST['punkte_'.$id]))
				$punkte = $_POST['punkte_'.$id];
			else
			{
				$response.="\nNote oder Punkte fehlen";
				continue;
			}
			$punkte=str_replace(',', '.', $punkte);
			if($punkte!='')
			{
				// Bei Punkteeingabe wird die Note nochmals geprueft und ggf korrigiert
				$notenschluessel = new notenschluessel();
				$note_pruef = $notenschluessel->getNote($punkte, $lvid, $stsem);
				if($note_pruef!=$note)
				{
					$note = $note_pruef;
					$note_dirty=true;
				}
			}

			$datum = $_POST['datumNachp_'.$id];
			//check Datumsformat
			$checkedDatum = $datum;
			$datum_obj = new datum();
			if(!$datum = $datum_obj->checkformatDatum($datum, 'Y-m-d', true))
			{
				$errorDatum .="\n".$p->t('benotungstool/datumsformatUnzulaessig', array($checkedDatum));
				continue;
			}

			//check ob Matrikelnummer anstelle der student_uid übergeben wurde
			$student = new student();
			if (!$student->checkIfValidStudentUID($student_uid))
			{
				$checkedMatrnr = $student_uid;
				//UID ermitteln
				if(!$student_uid = $student->getUidFromMatrikelnummer($student_uid))
				{
					$errorMatrnr.="\n".$p->t('benotungstool/studentMitMatrikelnummerExistiertNicht', array($checkedMatrnr));
					continue;
				}
			}

			//check ob eine Note vorhanden ist oder (intern) angerechnet wurde
			$lvnote = new lvgesamtnote();
			if (!$lvnote->load($lvid, $student_uid, $stsem))
			{
				$errorNachp.="\n".$p->t('benotungstool/NachpruefungNichtZulaessig', array($student_uid));
				continue;
			}

			$lehreinheit_id = getLehreinheit($db, $lvid, $student_uid, $stsem);

			if(isset($_POST['student_uid_'.$id]) && (isset($_POST['note_'.$id]) || isset($_POST['punkte_'.$id])) && isset($_POST['datumNachp_'.$id]))
			{
				if(!is_numeric($note))
				{
					if(isset($noten_anmerkung[$note]))
						$note = $noten_anmerkung[$note];
				}
				$response = savePruefung($lvid, $student_uid, $stsem, $lehreinheit_id, $datum, $typ, $note, $punkte);
				if($response!='neu' && $response!='update' && $response!='update_f')
				{
					echo $response;
				}
			}
			else
			{
				echo "\n fehlende oder fehlerhafte Inputparameter";
			}
		}
	}
	echo $errorMatrnr . $errorDatum . $errorNachp;
}
else
{
	// Einzelupdate

	if(isset($_GET['datum']))
	{
		$datum = $_GET['datum'];
		$datum_obj = new datum();
		$datum = $datum_obj->checkformatDatum($datum, 'Y-m-d', true) or die($p->t('benotungstool/datumsformatUnzulaessig', array($checkedDatum)));
	}
	$student_uid = $_REQUEST["student_uid"];

	$note = $_REQUEST["note"];
	if(isset($_REQUEST['punkte']))
		$punkte = $_REQUEST['punkte'];
	else
		$punkte = '';

	$punkte = str_replace(',', '.', $punkte);

	if($punkte!='')
	{
		// Bei Punkteeingabe wird die Note nochmals geprueft und ggf korrigiert
		$notenschluessel = new notenschluessel();
		$note_pruef = $notenschluessel->getNote($punkte, $lvid, $stsem);
		if($note_pruef!=$note)
		{
			$note = $note_pruef;
			$note_dirty=true;
		}
	}

	if($note=='')
		$note = 9;

	$old_note = $note;

	//Laden der Lehrveranstaltung
	$lv_obj = new lehrveranstaltung();
	if(!$lv_obj->load($lvid))
		die($lv_obj->errormsg);

	//Studiengang laden
	$stg_obj = new studiengang($lv_obj->studiengang_kz);

	$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');
	$uid = (isset($_GET['uid'])?$_GET['uid']:'');

	// lvgesamtnote für studenten speichern
	if (isset($_REQUEST["submit"]) && ($_REQUEST["student_uid"] != ''))
	{
		// Die Pruefung muss einer Lehreinheit zugeordnet werden
		// deshalb wird hier versucht eine passende Lehreinheit zu ermitteln.
		$lehreinheit_id = getLehreinheit($db, $lvid, $student_uid, $stsem);

		$response = savePruefung($lvid, $student_uid, $stsem, $lehreinheit_id, $datum, $typ, $note, $punkte);
		echo $response;
	}
	else
		echo "Fehler beim Eintragen der Pr&uuml;fungen";
}

/**
 * Berechnet Lehreinheit auf Basis LV-Daten
 * @param string $db Datenbankvariable
 * @param string $student_uid student_uid
 * @param int $lvid Lehrveranstaltungsid
 * @param string $stsem Studiensemester im Format 'WSYYYY'
 * @return String LehreinheitId
 */
function getLehreinheit($db, $lvid, $student_uid, $stsem)
{
	$le_arr = array();
	$qry_stud = "SELECT lehreinheit_id, lehrform_kurzbz
		FROM
			campus.vw_student_lehrveranstaltung
			JOIN campus.vw_student using(uid)
		WHERE
			studiensemester_kurzbz = ".$db->db_add_param($stsem)."
			AND lehrveranstaltung_id = ".$db->db_add_param($lvid, FHC_INTEGER)."
			AND uid=".$db->db_add_param($student_uid)."
		ORDER BY CASE WHEN lehrform_kurzbz='PRF' THEN '0' ELSE lehrform_kurzbz END";

	if($result_stud = $db->db_query($qry_stud))
	{
		$i=1;
		while($row_stud = $db->db_fetch_object($result_stud))
		{
			$le_arr[] = $row_stud->lehreinheit_id;
		}
	}

	//if (!in_array($lehreinheit_id,$le_arr))
		$lehreinheit_id = $le_arr[0];

	return $lehreinheit_id;
}

/**
 * Prüfung speichern
 */
function savePruefung($lvid, $student_uid, $stsem, $lehreinheit_id, $datum, $typ, $note, $punkte = null)
{
	$jetzt = date("Y-m-d H:i:s");
	global $user; //, $note, $punkte, $datum;

	if ($typ == "Termin2")
	{
		$pr = new Pruefung();
		// Wenn eine Pruefung angelegt wird, wird  zuerst eine Pruefung mit 1. Termin angelegt
	// und dort die Zeugnisnote gespeichert
		if($pr->getPruefungen($student_uid, "Termin1", $lvid, $stsem))
		{
			if ($pr->result)
			{
				$termin1 = 1;
			}
			else
			{
				$lvnote = new lvgesamtnote();
				if ($lvnote->load($lvid, $student_uid, $stsem))
				{
					$pr_note = $lvnote->note;
					$pr_punkte = $lvnote->punkte;
					$benotungsdatum = $lvnote->benotungsdatum;
				}
				else
				{
					$pr_note = 9;
					$pr_punkte = '';
					$benotungsdatum = $jetzt;
				}

				$pr_1 = new Pruefung();
				$pr_1->lehreinheit_id = $lehreinheit_id;
				$pr_1->student_uid = $student_uid;
				$pr_1->mitarbeiter_uid = $user;
				$pr_1->note = $pr_note;
				$pr_1->punkte = $pr_punkte;
				$pr_1->pruefungstyp_kurzbz = "Termin1";
				$pr_1->datum = $benotungsdatum;
				$pr_1->anmerkung = "";
				$pr_1->insertamum = $jetzt;
				$pr_1->insertvon = $user;
				$pr_1->updateamum = null;
				$pr_1->updatevon = null;
				$pr_1->ext_id = null;
				$pr_1->new = true;
				$pr_1->save();
				$response = "neu T1";
			}

			$prTermin2 = new Pruefung();
			$pr_2 = new Pruefung();

			// Die Pruefung wird als Termin2 eingetragen
			if ($prTermin2->getPruefungen($student_uid, 'Termin2', $lvid, $stsem))
			{
				if	($prTermin2->result)
				{
					$pr_2->load($prTermin2->result[0]->pruefung_id);
					$pr_2->new = null;
					$pr_2->updateamum = $jetzt;
					$pr_2->updatevon = $user;
					$old_note = $pr_2->note;
					$pr_2->note = $note;
					$pr_2->punkte = $punkte;
					$pr_2->datum = $datum;
					$pr_2->anmerkung = "";
					$response = "update T2";
				}
				else
				{
					$pr_2->lehreinheit_id = $lehreinheit_id;
					$pr_2->student_uid = $student_uid;
					$pr_2->mitarbeiter_uid = $user;
					$pr_2->note = $note;
					$pr_2->punkte = $punkte;
					$pr_2->pruefungstyp_kurzbz = $typ;
					$pr_2->datum = $datum;
					$pr_2->anmerkung = "";
					$pr_2->insertamum = $jetzt;
					$pr_2->insertvon = $user;
					$pr_2->updateamum = null;
					$pr_2->updatevon = null;
					$pr_2->ext_id = null;
					$pr_2->new = true;
					$old_note = -1;
					$response = "new T2";
				}
				$pr_2->save();
			}
		}
	}
	elseif ($typ == "Termin3")
	{
		$prTermin3 = new Pruefung();
		$pr_3 = new Pruefung();

		if ($prTermin3->getPruefungen($student_uid, 'Termin3', $lvid, $stsem))
		{
			if	($prTermin3->result)
			{
				$pr_3->load($prTermin3->result[0]->pruefung_id);
				$pr_3->new = null;
				$pr_3->updateamum = $jetzt;
				$pr_3->updatevon = $user;
				$old_note = $pr_3->note;
				$pr_3->note = $note;
				$pr_3->punkte = $punkte;
				$pr_3->datum = $datum;
				$pr_3->anmerkung = "";
				$response = "update T3";
			}
			else
			{
				$pr_3->lehreinheit_id = $lehreinheit_id;
				$pr_3->student_uid = $student_uid;
				$pr_3->mitarbeiter_uid = $user;
				$pr_3->note = $note;
				$pr_3->punkte = $punkte;
				$pr_3->pruefungstyp_kurzbz = $typ;
				$pr_3->datum = $datum;
				$pr_3->anmerkung = "";
				$pr_3->insertamum = $jetzt;
				$pr_3->insertvon = $user;
				$pr_3->updateamum = null;
				$pr_3->updatevon = null;
				$pr_3->ext_id = null;
				$pr_3->new = true;
				$old_note = -1;
				$response = "new T3";
			}
			$pr_3->save();
		}
	}
	else
	{
		$response = "fehlende oder fehlerhafte Inputparameter";
	}

	//Gesamtnote updaten
	$lvgesamtnote = new lvgesamtnote();
	if (!$lvgesamtnote->load($lvid, $student_uid, $stsem))
	{
		$lvgesamtnote->student_uid = $student_uid;
		$lvgesamtnote->lehrveranstaltung_id = $lvid;
		$lvgesamtnote->studiensemester_kurzbz = $stsem;
		$lvgesamtnote->note = $note;
		$lvgesamtnote->punkte = $punkte;
		$lvgesamtnote->mitarbeiter_uid = $user;
		$lvgesamtnote->benotungsdatum = $jetzt;
		$lvgesamtnote->freigabedatum = null;
		$lvgesamtnote->freigabevon_uid = null;
		$lvgesamtnote->bemerkung = null;
		$lvgesamtnote->updateamum = null;
		$lvgesamtnote->updatevon = null;
		$lvgesamtnote->insertamum = $jetzt;
		$lvgesamtnote->insertvon = $user;
		$new = true;
		$response = "neu";
	}
	else
	{
		$lvgesamtnote->note = $note;
		$lvgesamtnote->punkte = $punkte;
		$lvgesamtnote->benotungsdatum = $jetzt;
		$lvgesamtnote->updateamum = $jetzt;
		$lvgesamtnote->updatevon = $user;
		$new = false;
		if ($lvgesamtnote->freigabedatum)
			$response = "update_f";
		else
			$response = "update";
	}

	if (!$lvgesamtnote->save($new))
		return $lvgesamtnote->errormsg;
	else
		return $response;
}

?>
