<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *		  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *		  Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *		  Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
/**
 * Auswertung fuer den Reihungstest
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/log.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/reihungstest.class.php');
require_once('../../include/organisationsform.class.php');
require_once('../../include/mail.class.php');

if (!$db = new basis_db())
{
	die('Fehler beim Oeffnen der Datenbankverbindung');
}

$user = get_uid();
$datum_obj = new datum();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$messageSuccess = '';
$messageError = '';

if (!$rechte->isBerechtigt('lehre/reihungstest') && !$rechte->isBerechtigt('lehre/reihungstestAufsicht'))
{
	die($rechte->errormsg);
}

// Post-Request für PreStudent Autocomplete
if (isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete'] == 'prestudent')
{
	$search = trim((isset($_REQUEST['term']) ? $_REQUEST['term'] : ''));
	if (is_null($search) || $search == '')
	{
		exit();
	}
	$qry = "SELECT
				nachname, vorname, prestudent_id, student_uid,
				UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) as stg,
				get_rolle_prestudent(prestudent_id, null) as status
			FROM
				public.tbl_person
				JOIN public.tbl_prestudent USING(person_id)
				JOIN public.tbl_studiengang USING(studiengang_kz)
				LEFT JOIN public.tbl_student USING (prestudent_id)
			WHERE
				lower(nachname) like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
				lower(vorname) like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
				lower(nachname || ' ' || vorname) like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
				lower(vorname || ' ' || nachname) like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
				prestudent_id::text like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
				student_uid::text like '%" . $db->db_escape(mb_strtolower($search)) . "%'
				ORDER BY nachname,vorname,stg
				LIMIT 10
			";
	if ($result = $db->db_query($qry))
	{
		$result_obj = array();
		while ($row = $db->db_fetch_object($result))
		{
			$item['vorname'] = html_entity_decode($row->vorname);
			$item['nachname'] = html_entity_decode($row->nachname);
			$item['stg'] = html_entity_decode($row->stg);
			$item['status'] = html_entity_decode($row->status);
			$item['prestudent_id'] = html_entity_decode($row->prestudent_id);
			$item['student_uid'] = html_entity_decode($row->student_uid);
			$result_obj[] = $item;
		}
		echo json_encode($result_obj);
	}
	exit;
}

// Post-Request für prestudentAdd Autocomplete
if (isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete'] == 'prestudentAdd')
{
	$search = trim((isset($_REQUEST['term']) ? $_REQUEST['term'] : ''));
	$studiensemester_kurzbz = (isset($_REQUEST['studiensemester_kurzbz']) ? $_REQUEST['studiensemester_kurzbz'] : '');
	if (is_null($search) || $search == '')
	{
		exit();
	}
	$qry = "SELECT
				nachname, vorname, prestudent_id,
				UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) as stg,
				'Interessent' as status
			FROM
				public.tbl_person
				JOIN public.tbl_prestudent USING(person_id)
				JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE
				(lower(nachname) like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
				lower(vorname) like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
				lower(nachname || ' ' || vorname) like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
				lower(vorname || ' ' || nachname) like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
				prestudent_id::text like '%" . $db->db_escape(mb_strtolower($search)) . "%')";
				$first = true;
				if (is_array($studiensemester_kurzbz))
					$qry .= " AND (";
				foreach ($studiensemester_kurzbz as $stsem) {
					$stsem = trim($stsem);
					if (!$first)
						$qry .= 'OR ';
					$qry .= "get_rolle_prestudent(prestudent_id, " . $db->db_add_param($stsem) . ") IN ('Interessent')";
				}
				if (is_array($studiensemester_kurzbz))
					$qry .= ")";
				$qry .=
				" ORDER BY nachname,vorname,stg
				LIMIT 10";

	if ($result = $db->db_query($qry))
	{
		$result_obj = array();
		if ($db->db_num_rows($result) > 0)
		{
			while ($row = $db->db_fetch_object($result))
			{
				$item['vorname'] = html_entity_decode($row->vorname);
				$item['nachname'] = html_entity_decode($row->nachname);
				$item['stg'] = html_entity_decode($row->stg);
				$item['status'] = html_entity_decode($row->status);
				$item['prestudent_id'] = html_entity_decode($row->prestudent_id);
				$item['student_uid'] = '';
				$result_obj[] = $item;
			}
		}
		else
		{
			$item['vorname'] = 'Keine Übereinstimmung gefunden';
			$item['nachname'] = '';
			$item['stg'] = '';
			$item['status'] = '';
			$item['prestudent_id'] = '';
			$item['student_uid'] = '';
			$result_obj[] = $item;
		}
			echo json_encode($result_obj);
	}
	exit;
}

// Ajax-Request um ein Gebiet eines PreStudenten zu löschen
$deleteSingleResult = filter_input(INPUT_POST, 'deleteSingleResult', FILTER_VALIDATE_BOOLEAN);
if ($deleteSingleResult)
{
	if (!$rechte->isBerechtigt('lehre/reihungstestAufsicht', null, 'suid'))
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => $rechte->errormsg
		));
		exit();
	}

	if (isset($_POST['prestudent_id']) && isset($_POST['gebiet_id']) &&
		is_numeric($_POST['prestudent_id']) && is_numeric($_POST['gebiet_id']))
	{
		$pruefling = new pruefling();
		$pruefling->getPruefling($_POST['prestudent_id']);
		if ($pruefling->pruefling_id == '')
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Prüfling konnte nicht gefunden/geladen werden'
			));
			exit();
		}

		//UNDO Befehl zusammenbauen und Log schreiben
		$undo = '';
		$db->db_query('BEGIN;');

		$qry = "SELECT * FROM testtool.tbl_pruefling_frage WHERE pruefling_id=" . $db->db_add_param($pruefling->pruefling_id, FHC_INTEGER) . " AND
				frage_id IN (SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id=" . $db->db_add_param($_POST['gebiet_id']) . ");
				";

		if ($db->db_query($qry))
		{
			while ($row = $db->db_fetch_object())
			{
				$undo .= " INSERT INTO testtool.tbl_pruefling_frage(prueflingfrage_id,pruefling_id,frage_id,nummer,begintime,endtime) VALUES (" .
					$db->db_add_param($row->prueflingfrage_id, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->pruefling_id, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->frage_id, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->nummer, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->begintime) . ', ' .
					$db->db_add_param($row->endtime) . ');';
			}
		}
		else
		{
			$db->db_query('ROLLBACK');
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Fehler beim Erstellen des UNDO Befehls fuer testtool.tbl_pruefling_frage'
			));
			exit();
		}

		$qry = "SELECT * FROM testtool.tbl_antwort
				WHERE pruefling_id=" . $db->db_add_param($pruefling->pruefling_id) . " AND
				vorschlag_id IN (SELECT vorschlag_id FROM testtool.tbl_vorschlag WHERE frage_id IN
				(SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id=" . $db->db_add_param($_POST['gebiet_id']) . "));
				";

		if ($db->db_query($qry))
		{
			while ($row = $db->db_fetch_object())
			{
				$undo .= " INSERT INTO testtool.tbl_antwort(antwort_id,pruefling_id,vorschlag_id) VALUES (" .
					$db->db_add_param($row->antwort_id, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->pruefling_id, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->vorschlag_id, FHC_INTEGER) . ');';
			}
		}
		else
		{
			$db->db_query('ROLLBACK');
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Fehler beim Erstellen des UNDO Befehls fuer testtool.tbl_antwort'
			));
			exit();
		}

		//Antworten loeschen
		$qry = "DELETE FROM testtool.tbl_pruefling_frage where pruefling_id=" . $db->db_add_param($pruefling->pruefling_id, FHC_INTEGER) . " AND
				frage_id IN (SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id=" . $db->db_add_param($_POST['gebiet_id']) . ");

				DELETE FROM testtool.tbl_antwort
				WHERE pruefling_id=" . $db->db_add_param($pruefling->pruefling_id) . " AND
				vorschlag_id IN (SELECT vorschlag_id FROM testtool.tbl_vorschlag WHERE frage_id IN
				(SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id=" . $db->db_add_param($_POST['gebiet_id']) . "));";

		if ($result = $db->db_query($qry))
		{
			//Log schreiben
			$log = new log();

			$log->new = true;
			$log->sql = $qry;
			$log->sqlundo = $undo;
			$log->executetime = date('Y-m-d H:i:s');
			$log->mitarbeiter_uid = $user;
			$log->beschreibung = "Testtool-Antworten-Gebiet " . $_POST['gebiet_id'] . " von Prestudent " . $_POST['prestudent_id'] . " geloescht";

			if (!$log->save())
			{
				$db->db_query('ROLLBACK');
				echo json_encode(array(
					'status' => 'fehler',
					'msg' => 'Fehler beim Schreiben des Log-Eintrags'
				));
				exit();
			}

			$db->db_query('COMMIT;');
			echo json_encode(array(
				'status' => 'ok',
				'msg' => $db->db_affected_rows($result) . ' Antworten wurden gelöscht'));
			exit();
		}
		else
		{
			$db->db_query('ROLLBACK');
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Fehler beim Loeschen der Daten'
			));
			exit();
		}
	}
	else
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => 'Fehler bei Parameterübergabe'
		));
		exit();
	}
}

// Ajax-Request um alle Antworten aller Gebiete eines PreStudenten zu löschen
$deleteAllResults = filter_input(INPUT_POST, 'deleteAllResults', FILTER_VALIDATE_BOOLEAN);
if ($deleteAllResults)
{
	if (!$rechte->isBerechtigt('lehre/reihungstestAufsicht', null, 'suid'))
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => $rechte->errormsg
		));
		exit();
	}

	if (isset($_POST['prestudent_id']) && is_numeric($_POST['prestudent_id']))
	{
		$pruefling = new pruefling();
		$pruefling->getPruefling($_POST['prestudent_id']);
		if ($pruefling->pruefling_id == '')
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Prüfling konnte nicht gefunden/geladen werden'
			));
			exit();
		}

		//UNDO Befehl zusammenbauen und Log schreiben
		$undo = '';
		$db->db_query('BEGIN;');

		$qry = "SELECT * FROM testtool.tbl_pruefling_frage WHERE pruefling_id=" . $db->db_add_param($pruefling->pruefling_id, FHC_INTEGER) . ";
				";

		if ($db->db_query($qry))
		{
			while ($row = $db->db_fetch_object())
			{
				$undo .= " INSERT INTO testtool.tbl_pruefling_frage(prueflingfrage_id,pruefling_id,frage_id,nummer,begintime,endtime) VALUES (" .
					$db->db_add_param($row->prueflingfrage_id, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->pruefling_id, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->frage_id, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->nummer, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->begintime) . ', ' .
					$db->db_add_param($row->endtime) . ');';
			}
		}
		else
		{
			$db->db_query('ROLLBACK');
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Fehler beim Erstellen des UNDO Befehls fuer testtool.tbl_pruefling_frage'
			));
			exit();
		}

		$qry = "SELECT * FROM testtool.tbl_antwort
				WHERE pruefling_id=" . $db->db_add_param($pruefling->pruefling_id) . ";
				";

		if ($db->db_query($qry))
		{
			while ($row = $db->db_fetch_object())
			{
				$undo .= " INSERT INTO testtool.tbl_antwort(antwort_id,pruefling_id,vorschlag_id) VALUES (" .
					$db->db_add_param($row->antwort_id, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->pruefling_id, FHC_INTEGER) . ', ' .
					$db->db_add_param($row->vorschlag_id, FHC_INTEGER) . ');';
			}
		}
		else
		{
			$db->db_query('ROLLBACK');
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Fehler beim Erstellen des UNDO Befehls fuer testtool.tbl_antwort'
			));
			exit();
		}

		//Antworten loeschen
		$qry = "	DELETE FROM testtool.tbl_pruefling_frage where pruefling_id=".$db->db_add_param($pruefling->pruefling_id).";
					DELETE FROM testtool.tbl_antwort WHERE pruefling_id=".$db->db_add_param($pruefling->pruefling_id).";";

		if ($result = $db->db_query($qry))
		{
			//Log schreiben
			$log = new log();

			$log->new = true;
			$log->sql = $qry;
			$log->sqlundo = $undo;
			$log->executetime = date('Y-m-d H:i:s');
			$log->mitarbeiter_uid = $user;
			$log->beschreibung = "Testtool-Antworten aller Gebiete von Prestudent " . $_POST['prestudent_id'] . " geloescht";

			if (!$log->save())
			{
				$db->db_query('ROLLBACK');
				echo json_encode(array(
					'status' => 'fehler',
					'msg' => 'Fehler beim Schreiben des Log-Eintrags'
				));
				exit();
			}

			$db->db_query('COMMIT;');
			echo json_encode(array(
				'status' => 'ok',
				'msg' => 'Alle ' . $db->db_affected_rows($result) . ' Antworten wurden gelöscht'));
			exit();
		}
		else
		{
			$db->db_query('ROLLBACK');
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Fehler beim Loeschen der Daten'
			));
			exit();
		}
	}
	else
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => 'Fehler bei Parameterübergabe'
		));
		exit();
	}
}

// Ajax-Request um die Liste zusammenzuräumen
$clearList = filter_input(INPUT_POST, 'clearList', FILTER_VALIDATE_BOOLEAN);
if ($clearList)
{
	if (!$rechte->isBerechtigt('infocenter', null, 'suid'))
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => $rechte->errormsg
		));
		exit();
	}

	$qry = "DELETE FROM testtool.tbl_pruefling pf
			WHERE 
			(
				NOT EXISTS (SELECT 1 FROM testtool.tbl_pruefling_frage WHERE pruefling_id=pf.pruefling_id) AND
				NOT EXISTS (SELECT 1 FROM testtool.tbl_antwort WHERE pruefling_id=pf.pruefling_id)
			)";

	if ($result = $db->db_query($qry))
	{
		echo json_encode(array(
			'status' => 'ok',
			'msg' => $db->db_affected_rows($result).' leere Prüflinge wurden gelöscht'));   
		exit();
	}
	else
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => 'Fehler beim Löschen der leeren Prüflinge'
		));
		exit();
	}
}

// Ajax-Request um einen Prüfling zu sperren
$rtprueflingEntSperren = filter_input(INPUT_POST, 'rtprueflingEntSperren', FILTER_VALIDATE_BOOLEAN);
if ($rtprueflingEntSperren)
{
	if (!$rechte->isBerechtigt('lehre/reihungstestAufsicht', null, 'su'))
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => $rechte->errormsg
		));
		exit();
	}

	if (isset($_POST['prestudent_id']) && is_numeric($_POST['prestudent_id'])
		&& isset($_POST['art']))
	{
		$qry = "UPDATE testtool.tbl_pruefling SET gesperrt =" . $db->db_add_param($_POST['art'], 'BOOLEAN') . "
				WHERE prestudent_id IN 
						(SELECT prestudent_id FROM public.tbl_prestudent ps
							JOIN public.tbl_person tp ON tp.person_id = ps.person_id 
							WHERE tp.person_id = (SELECT person_id FROM public.tbl_prestudent sps WHERE sps.prestudent_id = " . $db->db_add_param($_POST['prestudent_id']) . "));";

		if ($result = $db->db_query($qry))
		{
			$msg = $_POST['art'] === 'false' ? 'Pruefling wurde gesperrt' : 'Pruefling wurde freigeschaltet';
			echo json_encode(array(
				'status' => 'ok',
				'msg' => $msg));
			exit();
		}
		else
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Fehler beim speichern der Daten'
			));
			exit();
		}
	}
}

// Ajax-Request um einen Reihungstest freizuschalten
$rtFreischalten = filter_input(INPUT_POST, 'rtFreischalten', FILTER_VALIDATE_BOOLEAN);
if ($rtFreischalten)
{
	if (!$rechte->isBerechtigt('lehre/reihungstestAufsicht', null, 'su'))
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => $rechte->errormsg
		));
		exit();
	}

	if (isset($_POST['reihungstest_id']) &&	is_numeric($_POST['reihungstest_id'])
		&& isset($_POST['art']))
	{
		$qry = "UPDATE public.tbl_reihungstest SET freigeschaltet=" . $db->db_add_param($_POST['art'], 'BOOLEAN') . "
				WHERE reihungstest_id=" . $db->db_add_param($_POST['reihungstest_id']) . ";";

		if ($result = $db->db_query($qry))
		{
			$msg = $_POST['art'] === 'false' ? 'Reihungstest wurde gesperrt' : 'Reihungstest wurde freigeschaltet';
			echo json_encode(array(
				'status' => 'ok',
				'msg' => $msg));
			exit();
		}
		else
		{
			echo json_encode(array(
				'status' => 'fehler',
				'msg' => 'Fehler beim speichern der Daten'
			));
			exit();
		}
	}
}

// Informiert die Studiengangsassistenz über das Ende des Tests
$testende = filter_input(INPUT_POST, 'testende', FILTER_VALIDATE_BOOLEAN);
if ($testende)
{
	// Alle Bachelor-Studiengänge holen, bei denen der Bewerber Interessent ist, die Bewerbung abgeschickt hat und bestätigt wurde
	// Mail an alle diese Studiengänge senden
	if (isset($_POST['prestudents']))
	{
		// Array mit allen Prestudenten aufbauen
		$prestudentsrt = $_POST['prestudents'];
		$prestudentArray = array();
		foreach ($prestudentsrt AS $prestrt)
		{
			$prestudent_id = $prestrt['prestudent_id'];
			$reihungstest_id = $prestrt['reihungstest_id'];
			$prestudentrolle = new prestudent($prestudent_id);
			$reihungstest = new reihungstest($reihungstest_id);
			// Wenn der letzte Status Abgewiesener ist, wird der Bewerber ignoriert
			$prestudentrolle->getLastStatus($prestudent_id, $reihungstest->studiensemester_kurzbz);
			if ($prestudentrolle->status_kurzbz == 'Abgewiesener')
			{
				continue;
			}
			// Letzten Interessentenstatus laden
			$prestudentrolle->getLastStatus($prestudent_id, $reihungstest->studiensemester_kurzbz, 'Interessent');
			$stg = new studiengang($prestudentrolle->studiengang_kz);

			if ($prestudentrolle->bewerbung_abgeschicktamum != ''
				&& $prestudentrolle->bestaetigtam != ''
				&& $prestudentrolle->bestaetigtvon != ''
				&& $stg->typ == 'b')
			{
				$prestudentArray[$prestudentrolle->studiengang_kz][$prestudentrolle->orgform_kurzbz][] = $prestrt;
			}

			// Setzt "teilgenommen" (Zum Reihungstest angetreten) auf TRUE
			$teilgenommen = new reihungstest();
			$teilgenommen->getPersonReihungstest($prestudentrolle->person_id, $reihungstest_id, $prestudentrolle->studienplan_id);

			$teilgenommen->new = false;
			$teilgenommen->teilgenommen = true;
			$teilgenommen->updateamum = date('Y-m-d H:i:s');
			$teilgenommen->updatevon = $user;

			if (!$teilgenommen->savePersonReihungstest())
			{
				echo json_encode(array(
					'status' => 'fehler',
					'msg' => 'Fehler beim Speichern der Reihungstestteilnahme: '.$teilgenommen->errormsg
				));
				exit();
			}
		}
	}

	$sendError = false;
	$empfaengerArray = array();
	$rtidArray = array();
	$rtdatumstrArray = array();
	$rtdatumstr = '';

	foreach ($prestudentsrt as $psrt)
	{
		if (!in_array($psrt['reihungstest_id'], $rtidArray))
		{
			$rtidArray[] = $psrt['reihungstest_id'];
			$rt = new reihungstest($psrt['reihungstest_id']);
			$idx = 0;

			//sort by date and time for correct order in mailtext
			foreach ($rtdatumstrArray as $ds)
			{
				if ($ds->datum < $rt->datum)
					$idx++;
				elseif ($ds->datum == $rt->datum)
				{
					if ($ds->uhrzeit < $rt->uhrzeit)
						$idx++;
					else
						break;
				}
				else
					break;
			}
			$rtdatum = new stdClass();
			$rtdatum->datum = $rt->datum;
			$rtdatum->uhrzeit = $rt->uhrzeit;
			array_splice($rtdatumstrArray, $idx, 0, array($rtdatum));
		}
	}

	foreach ($rtdatumstrArray as $rtdatumobj) {
		$rtdatumstr .= 'Der Reihungstest vom '.$datum_obj->convertISODate($rtdatumobj->datum).' um '.$datum_obj->formatDatum($rtdatumobj->uhrzeit, 'H:i').' Uhr ist beendet.<br>';
	}

	$rtidparams = http_build_query(array('reihungstest' => $rtidArray));

	foreach ($prestudentArray AS $studiengang_kz => $OrgFormPrestudent)
	{
		foreach ($OrgFormPrestudent AS $orgForm => $prestudentrt)
		{
			$empfaenger = getMailEmpfaenger($studiengang_kz, null, $orgForm);
			//Pfuschloesung fur BIF Dual
			if (CAMPUS_NAME == 'FH Technikum Wien' && $studiengang_kz == 257 && $orgForm == 'DUA')
			{
				$empfaenger = 'info.bid@technikum-wien.at';
			}
			elseif (CAMPUS_NAME == 'FH Technikum Wien' && $studiengang_kz == 257 && $orgForm != 'DUA')
			{
				$empfaenger = 'info.bif@technikum-wien.at';
			}
			$empfaengerArray[] = $empfaenger;
			$anzahl = count($OrgFormPrestudent[$orgForm]);
			$stg = new studiengang($studiengang_kz);
			$mailtext = '<html>
							<head>	
								<title>Sancho Mail</title>
							</head>
							<body>
									<table cellpadding="0" cellspacing="0" style="border: 2px solid #000000; padding: 0px; max-width: 850px; 
										border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">  
										<tr>
											<td align="center">
												<table cellpadding="0" cellspacing="0" width="100%%" border="0">
													<tr>
														<td>
															<img src="cid:sancho_header" alt="sancho_header" style="width: 100%; display: block"/>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td style="padding-left: 8%; padding-right: 8%; padding-top: 5%; padding-bottom: 5%; font-family: verdana, sans-serif; font-size: 1em; border-bottom: 2px solid #000000;">';
			$mailtext .= $rtdatumstr;
			$mailtext .= 'Es haben <b>'.$anzahl.'</b> Person(en) aus dem Studiengang '.$stg->kuerzel.'-'.$orgForm.' teilgenommen.';
			$mailtext .= '<br><br><a href="'.APP_ROOT.'vilesci/stammdaten/auswertung_fhtw.php?'.$rtidparams.'&studiengang='.$studiengang_kz.'&orgform_kurzbz='.$orgForm.'">Link zur Auswertung</a>';
			$mailtext .= '<br><br><a href="'.APP_ROOT.'addons/reports/cis/vorschau.php?statistik_kurzbz=BewerberReihungstestPriorisierung&debug=true">Link zur Pivot-Tabelle für die Priorisierung</a>';
			$mailtext .= '<br><br>Reihung der BewerberInnen: Prio 1 innerhalb von 2 Werktagen, Prio 2 am 3. Werktag und Prio 3 am 4. Werktag';
			$mailtext .= '</td>
										</tr>
										<tr>
											<td align="center">
												<table cellpadding="0" cellspacing="0" width="100%">
													<tr>
														<td>
															<img src="cid:sancho_footer" alt="sancho_footer" style="width: 100%; display: block"/>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</body>
							</html>';

			$mailtext = wordwrap($mailtext, 70); // Bricht den Code um, da es sonst zu Anzeigefehlern im Mail kommen kann

			$mail = new mail($empfaenger, 'no-reply', 'Reihungstest beendet', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
			$mail->setHTMLContent($mailtext);
			$mail->addEmbeddedImage(APP_ROOT.'skin/images/sancho/sancho_header_min_bw.jpg', 'image/jpg', 'header_image', 'sancho_header');
			$mail->addEmbeddedImage(APP_ROOT.'skin/images/sancho/sancho_footer_min_bw.jpg', 'image/jpg', 'footer_image', 'sancho_footer');
			$mail->setBCCRecievers('kindlm@technikum-wien.at');

			if (!$mail->send())
			{
				$sendError = true;
			}
		}
	}
    if ($sendError)
    {
        echo json_encode(array(
            'status' => 'fehler',
            'msg' => '<p>Fehler beim Senden einer Nachricht</p>'
        ));
        exit();
    }
    else
    {
        $empfaengerArray = array_unique($empfaengerArray);
        echo json_encode(array(
            'status' => 'ok',
            'msg' => 'Nachricht erfolgreich verschickt an: '.implode(',', $empfaengerArray)
        ));
        exit();
    }
}

// Fügt einen Teilnehmer zum Reihungstest hinzu
if (isset($_POST['method']) && $_POST['method'] == 'addPerson')
{
	$error = false;
	if (!$rechte->isBerechtigt('lehre/reihungstestAufsicht', null, 'sui'))
	{
		$messageError = $rechte->errormsg;
		$error = true;
	}

	if (isset($_POST['prestudentToAdd']) && is_numeric($_POST['prestudentToAdd'])
		&& isset($_POST['reihungstest_id']) && is_numeric($_POST['reihungstest_id'])
		&& $error == false)
	{
		$studienplan = '';
		// Load Testdata
		$reihungstest = new reihungstest($_POST['reihungstest_id']);
		// Studienplan des letzten Interessentenstatus ermitteln
		$prestudent = new prestudent($_POST['prestudentToAdd']);
		$prestudent->getPrestudentRolle($_POST['prestudentToAdd'], 'Interessent', $reihungstest->studiensemester_kurzbz); //@todo: Sollen man Prestudenten ohne passendes Studiensemester hinzufügen können??
		$studiengang = new studiengang($prestudent->studiengang_kz);

		foreach ($prestudent->result AS $row)//@todo: Checken wir auf bewerbung abgeschickt und bestätigt??
		{
			if ($row->bewerbung_abgeschicktamum != '' && $row->bestaetigtam != '' && $row->studienplan_id != '')
			{
				$studienplan = $row->studienplan_id;
				break;
			}
		}

		// Prüfen, ob schon eine Zuteilung dieser Person zum Test vorhanden ist
		// Erst in Kombination mit dem Studienplan checken, danach ohne
		if ($reihungstest->checkPersonRtStudienplanExists($prestudent->person_id, $_POST['reihungstest_id'], $studienplan))
		{
			$messageError = 'Die Person mit diesem Studienplan ist bereits diesem Reihungstest zugeteilt';
			$error = true;
		}
		else
		{
			if ($studiengang->typ == 'b')
			{
				if ($reihungstest->getPersonReihungstest($prestudent->person_id, $_POST['reihungstest_id']))
				{
					$messageError = 'Die Person ist bereits diesem Reihungstest zugeteilt';
					$error = true;
				}
				else
				{
					$reihungstest->person_id = $prestudent->person_id;
					$reihungstest->studienplan_id = $studienplan;
					$reihungstest->anmeldedatum = date('Y-m-d');
					$reihungstest->teilgenommen = false;
					$reihungstest->ort_kurzbz = '';
					$reihungstest->punkte = '';
					$reihungstest->insertamum = date('Y-m-d H:i:s');
					$reihungstest->insertvon = $user;
					$reihungstest->new = true;

					if (!$reihungstest->savePersonReihungstest())
					{
						$messageError = 'Fehler beim speichern der Zuteilung: '.$reihungstest->errormsg;
					}
					else
					{
						$messageSuccess .= 'Person erfolgreich zugeteilt';
					}
				}
			}
			elseif ($studiengang->typ != 'b')
			{
				$reihungstest->person_id = $prestudent->person_id;
				$reihungstest->studienplan_id = $studienplan;
				$reihungstest->anmeldedatum = date('Y-m-d');
				$reihungstest->teilgenommen = false;
				$reihungstest->ort_kurzbz = '';
				$reihungstest->punkte = '';
				$reihungstest->insertamum = date('Y-m-d H:i:s');
				$reihungstest->insertvon = $user;
				$reihungstest->new = true;

				if (!$reihungstest->savePersonReihungstest())
				{
					$messageError = 'Fehler beim speichern der Zuteilung: '.$reihungstest->errormsg;
				}
				else
				{
					$messageSuccess .= 'Person erfolgreich zugeteilt';
				}
			}
		}
	}
	else
	{
		$messageError = 'Fehler bei Parameterübergabe';
	}
}

// Überträgt die Punkte ins FAS und setzt optional Reihungstest angetreten und Bewerberstatus
$punkteUebertragen = filter_input(INPUT_POST, 'punkteUebertragen', FILTER_VALIDATE_BOOLEAN);
if ($punkteUebertragen)
{
/*	if (isset($_POST['reihungstest_id']) &&	is_numeric($_POST['reihungstest_id']))
	{*/
		//$reihungstest = new reihungstest(/*$_POST['reihungstest_id']*/);
		$msg_warning = '';
		$msg_error = '';
		$count_success_punkte = 0;
		$count_success_gesamtpunkte = 0;
		$count_success_bewerber = 0;

		if (isset($_POST['prestudentPunkteArr']))
		{
			foreach ($_POST['prestudentPunkteArr'] AS $key => $array)
			{
				$reihungstest = new reihungstest($array['reihungstest_id']);
				$rtpunkte = number_format(floatval(str_replace(',', '.', $array['ergebnis'])), 4);
				$prestudentrolle = new prestudent($array['prestudent_id']);
				$prestudentrolle->getLastStatus($array['prestudent_id'], null, 'Interessent');

				if (!$rechte->isBerechtigt('lehre/reihungstest', $prestudentrolle->studiengang_kz, 'sui'))
				{
					$msg_error .= '<br>Sie haben keine Rechte, um für diesen Studiengang Ergebnisse ins FAS zu übertragen';
					continue;
				}

				// Checken, ob Person-Reihungstest-Studienplan zuteilung existiert
				if ($reihungstest->checkPersonRtStudienplanExists($prestudentrolle->person_id, $array['reihungstest_id'], $prestudentrolle->studienplan_id))
				{
					$setRTPunkte = new reihungstest();
					$setRTPunkte->getPersonReihungstest($prestudentrolle->person_id, $array['reihungstest_id'], $prestudentrolle->studienplan_id);

					// Check, ob Punkte schon befüllt sind
					if ($setRTPunkte->punkte == '')
					{
						$setRTPunkte->new = false;
						$setRTPunkte->punkte = $rtpunkte;
						$setRTPunkte->updateamum = date('Y-m-d H:i:s');
						$setRTPunkte->updatevon = $user;

						if (!$setRTPunkte->savePersonReihungstest())
						{
							$msg_error .= '<br>Fehler beim speichern der Reihungstestpunkte für Prestudent '.$array['prestudent_id'].': ' . $setRTPunkte->errormsg;
						}
						else
						{
							$count_success_punkte ++;
						}
					}
					else
					{
						$msg_warning .= '<br>Der Prestudent '.$array['prestudent_id'].' hat bereits Punkte für den Studienplan '.$prestudentrolle->studienplan_id.' eingetragen.';
					}
				}
				else
				{
					$setRTPunkte = new reihungstest();
					$ort_kurzbz = '';
					// Checken, ob schon irgendeine Raumzuteilung existiert (Check ohne Studienplan) und diese ggf. übernehmen
					$setRTPunkte->getPersonReihungstest($prestudentrolle->person_id, $array['reihungstest_id']);
					if ($setRTPunkte->ort_kurzbz != '')
					{
						$ort_kurzbz = $setRTPunkte->ort_kurzbz;
					}
					$setRTPunkte = new reihungstest();
					$setRTPunkte->getPersonReihungstest($prestudentrolle->person_id, $array['reihungstest_id'], $prestudentrolle->studienplan_id);

					// Check, ob Punkte schon befüllt sind
					if ($setRTPunkte->punkte == '')
					{
						$setRTPunkte->new = true;
						$setRTPunkte->person_id = $prestudentrolle->person_id;
						$setRTPunkte->reihungstest_id = $array['reihungstest_id'];
						$setRTPunkte->anmeldedatum = '';
						$setRTPunkte->teilgenommen = true;
						$setRTPunkte->ort_kurzbz = $ort_kurzbz;
						$setRTPunkte->studienplan_id = $prestudentrolle->studienplan_id;
						$setRTPunkte->punkte = $rtpunkte;
						$setRTPunkte->insertamum = date('Y-m-d H:i:s');
						$setRTPunkte->insertvon = $user;

						if (!$setRTPunkte->savePersonReihungstest())
						{
							$msg_error .= '<br>Fehler beim speichern der Reihungstestpunkte für Prestudent ' . $array['prestudent_id'] . ': ' . $setRTPunkte->errormsg;
						}
						else
						{
							$count_success_punkte ++;
						}
					}
					else
					{
						$msg_warning .= '<br>Der Prestudent '.$array['prestudent_id'].' hat bereits Punkte für den Studienplan '.$prestudentrolle->studienplan_id.' eingetragen.';
					}
				}

				$gesamtpunkteSetzen = filter_input(INPUT_POST, 'gesamtpunkteSetzen', FILTER_VALIDATE_BOOLEAN);
				// Wenn gesamtpunkteSetzen true ist, auch die Gesamtpunkte für den Prestudenten setzen
				if ($gesamtpunkteSetzen)
				{
					$prestudent = new prestudent($array['prestudent_id']);

					// Check, ob Punkte schon befüllt sind
					if ($prestudent->punkte == '')
					{
						$prestudent->new = false;
						$prestudent->punkte = $rtpunkte;
						$prestudent->reihungstestangetreten = true;
						$setRTPunkte->updateamum = date('Y-m-d H:i:s');
						$setRTPunkte->updatevon = $user;

						if (!$prestudent->save())
						{
							$msg_error .= '<br>Fehler beim setzen der Gesamtpunkte für Prestudent '.$array['prestudent_id'].': ' . $prestudent->errormsg;
						}
						else
						{
							$count_success_gesamtpunkte++;
						}
					}
					else
					{
						$msg_warning .= '<br>Der Prestudent '.$array['prestudent_id'].' hat bereits Gesamtpunkte eingetragen.';
					}
				}

				$zuBewerberMachen = filter_input(INPUT_POST, 'zuBewerberMachen', FILTER_VALIDATE_BOOLEAN);
				// Wenn zuBewerberMachen true ist, wird der Prestudent auch zum Bewerber gemacht
				if ($zuBewerberMachen)
				{
					$prestudent = new prestudent($array['prestudent_id']);

					// Checken, ob schon Bewerberstatus vorhanden ist
					if (!$prestudent->load_rolle($array['prestudent_id'], 'Bewerber', $prestudentrolle->studiensemester_kurzbz, $prestudentrolle->ausbildungssemester))
					{
						// Checken, ob Abgewiesener-Status vorhanden ist
						if (!$prestudent->load_rolle($array['prestudent_id'], 'Abgewiesener', $prestudentrolle->studiensemester_kurzbz, $prestudentrolle->ausbildungssemester))
						{
							// Um einen Bewerberstatus zu setzen, muss "reihungstestangetreten" true sein
							if ($prestudent->reihungstestangetreten == true)
							{
								// Um einen Bewerberstatus zu setzen, muss die ZGV ausgefüllt sein
								if ($prestudent->zgv_code != '')
								{
									$studiengang = new studiengang($prestudent->studiengang_kz);
									// Bei Mastern muss auch die ZGV-Master ausgefüllt sein
									if ($studiengang->typ == 'm' && $prestudent->zgvmas_code == '')
									{
										$msg_error .= '<br>Fehler beim speichern des Bewerberstatus für Prestudent '.$array['prestudent_id'].'. Es muss zuerst eine Master-ZGV eingetragen sein.';
									}
									else
									{
										$prestudent->new = true;
										$prestudent->prestudent_id = $array['prestudent_id'];
										$prestudent->status_kurzbz = 'Bewerber';
										$prestudent->studiensemester_kurzbz = $prestudentrolle->studiensemester_kurzbz;
										$prestudent->ausbildungssemester = $prestudentrolle->ausbildungssemester;
										$prestudent->datum = date('Y-m-d');
										$prestudent->insertamum = date('Y-m-d H:i:s');
										$prestudent->insertvon = $user;
										$prestudent->orgform_kurzbz = $prestudentrolle->orgform_kurzbz;
										$prestudent->bestaetigtam = '';
										$prestudent->bestaetigtvon = '';
										$prestudent->bewerbung_abgeschicktamum = '';
										$prestudent->studienplan_id = $prestudentrolle->studienplan_id;

										if (!$prestudent->save_rolle())
										{
											$msg_error .= '<br>Fehler beim speichern des Bewerberstatus für Prestudent '.$array['prestudent_id'].': '.$prestudent->errormsg;
										}
										else
										{
											$count_success_bewerber++;
										}
									}
								}
								else
								{
									$msg_error .= '<br>Fehler beim speichern des Bewerberstatus für Prestudent '.$array['prestudent_id'].'. Es muss zuerst eine ZGV eingetragen sein.';
								}
							}
							else
							{
								$msg_error .= '<br>Fehler beim speichern des Bewerberstatus für Prestudent '.$array['prestudent_id'].'. Zuerst muss "Reihungstestverfahren absolviert" gesetzt sein.';
							}
						}
						else
						{
							$msg_error .= '<br>Fehler beim speichern des Bewerberstatus für Prestudent '.$array['prestudent_id'].'. Es ist bereits ein Abgewiesener-Status vorhanden';
						}
					}
					else
					{
						$msg_warning .= '<br>Der Prestudent '.$array['prestudent_id'].' hat bereits einen Bewerberstatus';
					}
				}
			}
		}

		$msg_success = '';
		if ($count_success_punkte > 0)
		{
			$msg_success .= $count_success_punkte.' Punkte erfolgreich ins FAS übertragen';
		}
		if ($count_success_gesamtpunkte > 0)
		{
			$msg_success .= '<br>'.$count_success_gesamtpunkte.' Gesamtpunkte erfolgreich gesetzt';
		}
		if ($count_success_bewerber > 0)
		{
			$msg_success .= '<br>'.$count_success_bewerber.' Prestudenten zu Bewerber gemacht';
		}

		echo json_encode(array(
				'status' => 'ok',
				'msg_success' => $msg_success,
				'msg_warning' => $msg_warning,
				'msg_error' => $msg_error));
			exit();
}

function sortByField($multArray, $sortField, $desc = true)
{
	$tmpKey = '';
	$ResArray = array();

	if (!is_array($multArray))
	{
		return array();
	}

	$maIndex = array_keys($multArray);
	$maSize = count($multArray) - 1;

	for ($i = 0; $i < $maSize; $i++)
	{
		$minElement = $i;
		$tempMin = $multArray[$maIndex[$i]]->$sortField;
		$tmpKey = $maIndex[$i];
		for ($j = $i + 1; $j <= $maSize; $j++)
		{
			if ($multArray[$maIndex[$j]]->$sortField < $tempMin)
			{
				$minElement = $j;
				$tmpKey = $maIndex[$j];
				$tempMin = $multArray[$maIndex[$j]]->$sortField;
			}
		}
		$maIndex[$minElement] = $maIndex[$i];
		$maIndex[$i] = $tmpKey;
	}

	if ($desc)
	{
		for ($j = 0; $j <= $maSize; $j++)
		{
			$ResArray[$maIndex[$j]] = $multArray[$maIndex[$j]];
		}
	}
	else
	{
		for ($j = $maSize; $j >= 0; $j--)
		{
			$ResArray[$maIndex[$j]] = $multArray[$maIndex[$j]];
		}
	}

	return $ResArray;
}

/**
 * Liefert die interne Empfangsadresse des Studiengangs fuer den Mailversand.
 * Wenn BEWERBERTOOL_MAILEMPFANG gesetzt ist, wird diese genommen,
 * sonst diejenige aus BEWERBERTOOL_BEWERBUNG_EMPFAENGER,
 * sonst die Mailadresse des Studiengangs
 *
 * @param integer $studiengang_kz
 * @param integer $studienplan_id
 * @param string $orgform_kurzbz
 * @return string mit den Mailadressen sonst false
 */
function getMailEmpfaenger($studiengang_kz, $studienplan_id = null, $orgform_kurzbz = null)
{
	$studiengang = new studiengang($studiengang_kz);

	if ($studienplan_id != '')
	{
		$studienplan = new studienplan();
		$studienplan->loadStudienplan($studienplan_id);
	}

	$empf_array = array();
	$empfaenger = '';
	if(defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
		$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);

	// Umgehung für FHTW. Ausprogrammiert im Code
	if(defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG != '')
	{
		$empfaenger = BEWERBERTOOL_MAILEMPFANG;
	}
	elseif(isset($empf_array[$studiengang_kz]))
	{
		// Pfuschloesung, damit bei BIF Dual die Mail an info.bid geht
		if ($studiengang_kz == 257)
		{
			if ((isset($studienplan) && $studienplan->orgform_kurzbz == 'DUA') ||
				($orgform_kurzbz != '' && $orgform_kurzbz == 'DUA'))
				$empfaenger = 'info.bid@technikum-wien.at';
		}
		else
			$empfaenger = $empf_array[$studiengang_kz];
	}
	else
		$empfaenger = $studiengang->email;

	if ($empfaenger != '')
		return $empfaenger;
	else
		return false;
}

$ergebnis = array();
$gebiet = array();
$kategorie = array();
$erg_kat = array();
$zgv_arr = array();
$zgvma_arr = array();

$datum_von = isset($_REQUEST['datum_von']) ? $_REQUEST['datum_von'] : '';
$datum_bis = isset($_REQUEST['datum_bis']) ? $_REQUEST['datum_bis'] : '';
$reihungstest = isset($_REQUEST['reihungstest']) ? $_REQUEST['reihungstest'] : '';
$studiengang = isset($_REQUEST['studiengang']) ? $_REQUEST['studiengang'] : '';
$semester = isset($_REQUEST['semester']) ? $_REQUEST['semester'] : '';
$prestudent_id = isset($_REQUEST['prestudent_id']) ? $_REQUEST['prestudent_id'] : '';
$orgform_kurzbz = isset($_REQUEST['orgform_kurzbz']) ? $_REQUEST['orgform_kurzbz'] : '';
$format = (isset($_REQUEST['format']) ? $_REQUEST['format'] : '');
$rtStudiensemester = '';

if ($reihungstest != '' && (is_array($reihungstest) || is_numeric($reihungstest)))
{
	$rtStudiensemester = array();

	if (is_numeric($reihungstest))
		$reihungstest = array($reihungstest);

	foreach ($reihungstest as $rt_id)
	{
		$reihungstestObj = new reihungstest($rt_id);
		if (!in_array($reihungstestObj->studiensemester_kurzbz, $rtStudiensemester))
			$rtStudiensemester[] = $reihungstestObj->studiensemester_kurzbz;
	}
}
elseif ($reihungstest != '' && !is_array($reihungstest) && !is_numeric($reihungstest))
{
	die('ReihungstestIDs sind ungueltig');
}
if ($studiengang != '' && is_numeric($studiengang))
{
	$studiengangObj = new studiengang($studiengang);
}
elseif ($studiengang != '' && !is_numeric($studiengang))
{
	die('Studiengang ist ungueltig');
}
if ($semester != '' && !is_numeric($semester))
{
	die('Semester ist ungueltig');
}
if ($prestudent_id != '' && !is_numeric($prestudent_id))
{
	die('PrestudentID ist ungueltig');
}
if (isset($_POST['rtauswsubmit']) && $reihungstest == '' && $studiengang == '' && $semester == '' && $prestudent_id == '' && $datum_von == '' && $datum_bis == '')
{
	die('Waehlen Sie bitte mindestens eine der Optionen aus');
}

if ($datum_von != '')
{
	$datum_von = $datum_obj->formatDatum($datum_von, 'Y-m-d');
}
if ($datum_bis != '')
{
	$datum_bis = $datum_obj->formatDatum($datum_bis, 'Y-m-d');
}

$zgv_arr[''] = '';
$qry = "SELECT * FROM bis.tbl_zgv";
if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
		$zgv_arr[$row->zgv_code] = $row->zgv_kurzbz;
}

$zgvma_arr[''] = '';
$qry = "SELECT * FROM bis.tbl_zgvmaster";
if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
		$zgvma_arr[$row->zgvmas_code] = $row->zgvmas_kurzbz;
}

// Reihungstests laden
$sql_query = "SELECT * FROM public.tbl_reihungstest WHERE date_part('year',datum)=date_part('year',now()) ";

// Wenn Reihungstest ID gesetzt ist, diesen Test zusaetzlich laden, um auch jene außerhalbs des Datumszeitraums zu erwischen
if ($reihungstest != '')
{
	$sql_query .= "UNION SELECT * FROM public.tbl_reihungstest WHERE reihungstest_id IN (" . $db->implode4SQL($reihungstest) . ")";
}

$sql_query .= " ORDER BY datum,uhrzeit";

if (!($result = $db->db_query($sql_query)))
{
	die($db->db_last_error());
}

while ($row = $db->db_fetch_object($result))
{
	if (!isset($rtest[$row->reihungstest_id]))
	{
		$rtest[$row->reihungstest_id] = new stdClass();
	}
	$rtest[$row->reihungstest_id]->reihungstest_id = $row->reihungstest_id;
	$rtest[$row->reihungstest_id]->studiengang_kz = $row->studiengang_kz;
	$rtest[$row->reihungstest_id]->ort_kurzbz = $row->ort_kurzbz;
	$rtest[$row->reihungstest_id]->anmerkung = $row->anmerkung;
	$rtest[$row->reihungstest_id]->datum = $row->datum;
	$rtest[$row->reihungstest_id]->uhrzeit = $row->uhrzeit;
	$rtest[$row->reihungstest_id]->freigeschaltet = $db->db_parse_bool($row->freigeschaltet);
}

if (isset($_REQUEST['reihungstest']) || isset($_POST['rtauswsubmit']))
{
	// Vorkommende Gebiete laden
	$query = "
		SELECT DISTINCT 
			tbl_gebiet.gebiet_id,
			tbl_gebiet.bezeichnung AS gebiet,
			tbl_ablauf.reihung,
			tbl_ablauf.studiengang_kz,
			tbl_ablauf.semester,
		    tbl_ablauf.gewicht
		FROM PUBLIC.tbl_rt_person
		JOIN PUBLIC.tbl_person ON (tbl_rt_person.person_id = tbl_person.person_id)
		JOIN PUBLIC.tbl_prestudent ps ON (ps.person_id = tbl_rt_person.person_id)
		JOIN PUBLIC.tbl_reihungstest rt ON (tbl_rt_person.rt_id = rt.reihungstest_id)
		JOIN PUBLIC.tbl_prestudentstatus pss USING (prestudent_id)
		JOIN public.tbl_studiengang ON (ps.studiengang_kz = tbl_studiengang.studiengang_kz)
		LEFT JOIN bis.tbl_zgv ON (ps.zgv_code = tbl_zgv.zgv_code)
		LEFT JOIN PUBLIC.tbl_ort ON (tbl_rt_person.ort_kurzbz = tbl_ort.ort_kurzbz)
		LEFT JOIN testtool.tbl_pruefling USING (prestudent_id)
		LEFT JOIN testtool.tbl_ablauf ON (testtool.tbl_ablauf.studiengang_kz = ps.studiengang_kz)
		LEFT JOIN testtool.tbl_gebiet USING (gebiet_id)
		WHERE 1=1
			--AND get_rolle_prestudent(prestudent_id, rt.studiensemester_kurzbz) = 'Interessent'
			--AND tbl_prestudentstatus.studiensemester_kurzbz = rt.studiensemester_kurzbz
			--AND bewerbung_abgeschicktamum IS NOT NULL
			--AND bestaetigtam IS NOT NULL
			AND NOT (testtool.tbl_ablauf.gebiet_id IN ( SELECT testtool.tbl_kategorie.gebiet_id FROM testtool.tbl_kategorie))";
	if ($reihungstest != '')
	{
		$query .= " AND rt_id IN (" . $db->implode4SQL($reihungstest) . ")";
	}
	if ($studiengang != '')
	{
		$query .= " AND ps.studiengang_kz = " . $db->db_add_param($studiengang, FHC_INTEGER);
	}
	if ($datum_von != '')
	{
		$query .= " AND rt.datum >= " . $db->db_add_param($datum_von);
	}
	if ($datum_bis != '')
	{
		$query .= " AND rt.datum <= " . $db->db_add_param($datum_bis);
	}
	if ($studiengang != '')
	{
		$query .= " AND ps.studiengang_kz = " . $db->db_add_param($studiengang, FHC_INTEGER);
	}
	if ($semester != '')
	{
		$query .= " AND tbl_ablauf.semester=" . $db->db_add_param($semester, FHC_INTEGER);
		//$query .= " AND tbl_prestudentstatus.ausbildungssemester = " . $db->db_add_param($semester, FHC_INTEGER);
	}
	if ($prestudent_id != '')
	{
		$query .= " AND ps.prestudent_id=" . $db->db_add_param($prestudent_id, FHC_INTEGER);
	}
	if ($orgform_kurzbz != '' && $studiengang != '')
	{
		$query .= " AND (tbl_ablauf.studienplan_id=(
							SELECT studienplan_id FROM lehre.tbl_studienplan 
							JOIN lehre.tbl_studienordnung USING (studienordnung_id) 
							WHERE studiengang_kz=".$db->db_add_param($studiengang, FHC_INTEGER)."
							AND tbl_studienplan.orgform_kurzbz = ".$db->db_add_param($orgform_kurzbz)."
							AND tbl_studienplan.aktiv
							AND tbl_studienordnung.status_kurzbz='approved'
							AND ((SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=tbl_studienordnung.gueltigvon) <= now() 
									OR tbl_studienordnung.gueltigvon IS NULL)
							AND ((SELECT ende FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=tbl_studienordnung.gueltigbis) >= now() OR tbl_studienordnung.gueltigbis IS NULL)
							ORDER BY studienplan_id DESC LIMIT 1)
							OR tbl_ablauf.studienplan_id IS NULL)";
	}
	//$query .= " AND nachname='Al-Mafrachi'";
	$query .= " ORDER BY tbl_ablauf.studiengang_kz, tbl_ablauf.semester, reihung";

	if (!($result = $db->db_query($query)))
	{
		die($db->db_last_error());
	}
	while ($row = $db->db_fetch_object($result))
	{
		if (!isset($gebiet[$row->gebiet_id]))
		{
			$gebiet[$row->gebiet_id] = new stdClass();
		}
		$gebiet[$row->gebiet_id]->name = $row->gebiet;
		$gebiet[$row->gebiet_id]->gebiet_id = $row->gebiet_id;
		//gewicht ist meist für alle Studiengänge gleich (Bachelor, Master und Distance haben jeweilsandere Gebiete)
		if (!isset($gebiet[$row->gebiet_id]->gewicht))
		{
			$gebiet[$row->gebiet_id]->gewicht = $row->gewicht;
		}
	}

	// Alle Ergebnisse laden
	$query = "
		SELECT DISTINCT tbl_rt_person.person_id,
			gebdatum,
			tbl_person.geschlecht,
			nachname,
			vorname,
			UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) AS stg_kurzbz,
			tbl_studiengang.bezeichnung AS stg_bez,
			tbl_studienplan.orgform_kurzbz,
			tbl_gebiet.maxpunkte,
		    tbl_gebiet.offsetpunkte,            
			tbl_prestudentstatus.ausbildungssemester,
			tbl_ablauf.gewicht,
			tbl_ort.planbezeichnung AS raum,
			ps.prestudent_id,
			tbl_zgv.zgv_kurzbz,
			ps.zgv_code,
			ps.zgvmas_code,
			tbl_rt_person.teilgenommen,
			CASE 
				WHEN tbl_prestudentstatus.statusgrund_id = 9
					AND tbl_prestudentstatus.status_kurzbz = 'Interessent'
					THEN true
				ELSE false
				END AS qualifikationskurs,
			(
				SELECT count(*) AS prio_relativ
				FROM (
					SELECT *,
						(
							SELECT status_kurzbz
							FROM PUBLIC.tbl_prestudentstatus
							WHERE prestudent_id = pst.prestudent_id
							ORDER BY datum DESC,
								tbl_prestudentstatus.insertamum DESC LIMIT 1
							) AS laststatus
					FROM PUBLIC.tbl_prestudent pst
					JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
					WHERE person_id = (
							SELECT person_id
							FROM PUBLIC.tbl_prestudent
							WHERE prestudent_id = ps.prestudent_id
							)
						AND studiensemester_kurzbz = (
							SELECT studiensemester_kurzbz
							FROM PUBLIC.tbl_prestudentstatus
							WHERE prestudent_id = ps.prestudent_id
								AND status_kurzbz = 'Interessent' LIMIT 1
							)
						AND status_kurzbz = 'Interessent'
					) prest
				WHERE laststatus NOT IN ('Abbrecher', 'Abgewiesener', 'Absolvent')
					AND priorisierung <= (
						SELECT priorisierung
						FROM PUBLIC.tbl_prestudent
						WHERE prestudent_id = ps.prestudent_id
						)
				) AS prioritaet,
			(
				SELECT kontakt
				FROM PUBLIC.tbl_kontakt
				WHERE kontakttyp = 'email'
					AND zustellung = true
					AND person_id = tbl_rt_person.person_id
				ORDER BY insertamum DESC,
					updateamum DESC LIMIT 1
				) AS email,
			CASE 
				WHEN /*tbl_studiengang.typ = 'b'
					AND*/ EXISTS (
						SELECT 1
						FROM testtool.tbl_pruefling
						WHERE prestudent_id = ps.prestudent_id
						)
					THEN (
							SELECT sum(testtool.tbl_vorschlag.punkte) AS sum
							FROM testtool.tbl_vorschlag
							JOIN testtool.tbl_antwort USING (vorschlag_id)
							JOIN testtool.tbl_frage USING (frage_id)
							WHERE testtool.tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id
								AND testtool.tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
							)
				ELSE (
						SELECT sum(testtool.tbl_vorschlag.punkte) AS sum
						FROM testtool.tbl_vorschlag
						JOIN testtool.tbl_antwort USING (vorschlag_id)
						JOIN testtool.tbl_frage USING (frage_id)
						WHERE testtool.tbl_antwort.pruefling_id = (
								SELECT pruefling_id
								FROM testtool.tbl_pruefling
								WHERE prestudent_id = (
										SELECT prestudent_id
										FROM PUBLIC.tbl_prestudent
										JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
										JOIN PUBLIC.tbl_studiengang USING (studiengang_kz)
										JOIN testtool.tbl_pruefling USING (prestudent_id)
										WHERE person_id = tbl_rt_person.person_id
											AND tbl_studiengang.typ = 'b'
											--AND status_kurzbz = 'Interessent'
											AND studiensemester_kurzbz = rt.studiensemester_kurzbz
										ORDER BY registriert DESC LIMIT 1
										)
						    	ORDER BY registriert DESC LIMIT 1
								)
							AND testtool.tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
						)
				END AS punkte,
			rt.reihungstest_id,
		    tbl_gebiet.gebiet_id,
			tbl_gebiet.bezeichnung AS gebiet,
			tbl_pruefling.idnachweis,
			tbl_pruefling.registriert,
			tbl_pruefling.gesperrt,
			get_rolle_prestudent(prestudent_id, rt.studiensemester_kurzbz) AS letzter_status
		FROM PUBLIC.tbl_rt_person
		JOIN PUBLIC.tbl_person ON (tbl_rt_person.person_id = tbl_person.person_id)
		JOIN PUBLIC.tbl_prestudent ps ON (ps.person_id = tbl_rt_person.person_id)
		JOIN PUBLIC.tbl_reihungstest rt ON (tbl_rt_person.rt_id = rt.reihungstest_id)
		JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
		JOIN PUBLIC.tbl_studiengang ON (ps.studiengang_kz = tbl_studiengang.studiengang_kz)
		LEFT JOIN lehre.tbl_studienplan ON (tbl_prestudentstatus.studienplan_id = tbl_studienplan.studienplan_id)
		LEFT JOIN bis.tbl_zgv ON (ps.zgv_code = tbl_zgv.zgv_code)
		LEFT JOIN PUBLIC.tbl_ort ON (tbl_rt_person.ort_kurzbz = tbl_ort.ort_kurzbz)
		LEFT JOIN testtool.tbl_pruefling USING (prestudent_id)
		LEFT JOIN testtool.tbl_ablauf ON (testtool.tbl_ablauf.studiengang_kz = ps.studiengang_kz)
		LEFT JOIN testtool.tbl_gebiet USING (gebiet_id)
		WHERE 1 = 1
			--AND get_rolle_prestudent(prestudent_id, rt.studiensemester_kurzbz) NOT IN ('Abgewiesener') /*Wenn einkommentiert, kommen zB bei alten Bewerbungen keine Ergebnisse*/ 
			AND tbl_prestudentstatus.studiensemester_kurzbz IN (
				SELECT studiensemester_kurzbz
				FROM PUBLIC.tbl_studiensemester
				WHERE studiensemester_kurzbz = rt.studiensemester_kurzbz
				
				UNION
				
				(
					SELECT studiensemester_kurzbz
					FROM PUBLIC.tbl_studiensemester
					WHERE ende <= (
							SELECT start
							FROM PUBLIC.tbl_studiensemester
							WHERE studiensemester_kurzbz = rt.studiensemester_kurzbz
							)
					ORDER BY ende DESC LIMIT 1
					)
				
				UNION
				
				(
					SELECT studiensemester_kurzbz
					FROM PUBLIC.tbl_studiensemester
					WHERE start >= (
							SELECT ende
							FROM PUBLIC.tbl_studiensemester
							WHERE studiensemester_kurzbz = rt.studiensemester_kurzbz
							)
					ORDER BY start ASC LIMIT 1
					)
				)
			/*AND bewerbung_abgeschicktamum IS NOT NULL*/ /* Leider gibt es bestaetigte Bewerbungen, die nie abgeschickt wurden */ 
			AND bestaetigtam IS NOT NULL
			AND tbl_gebiet.gebiet_id != 7
		";
	if ($reihungstest != '')
	{
		$query .= " AND rt_id IN (" . $db->implode4SQL($reihungstest) . ")";
	}
	if ($studiengang != '')
	{
		$query .= " AND ps.studiengang_kz = " . $db->db_add_param($studiengang, FHC_INTEGER);
	}
	if ($datum_von != '')
	{
		$query .= " AND rt.datum >= " . $db->db_add_param($datum_von);
	}
	if ($datum_bis != '')
	{
		$query .= " AND rt.datum <= " . $db->db_add_param($datum_bis);
	}
	if ($semester != '')
	{
		$query .= " AND tbl_ablauf.semester=" . $db->db_add_param($semester, FHC_INTEGER);
		//$query .= " AND tbl_prestudentstatus.ausbildungssemester = " . $db->db_add_param($semester, FHC_INTEGER);
	}
	if ($prestudent_id != '')
	{
		$query .= " AND ps.prestudent_id=" . $db->db_add_param($prestudent_id, FHC_INTEGER);
	}
	if ($orgform_kurzbz != '')
	{
		//$query .= " AND tbl_prestudentstatus.orgform_kurzbz=" . $db->db_add_param($orgform_kurzbz);
		//$query .= " AND tbl_ablauf.studienplan_id = tbl_prestudentstatus.studienplan_id";
		//$query .= " AND tbl_ablauf.studienplan_id = 5";
		$query .= " AND tbl_studienplan.orgform_kurzbz=" . $db->db_add_param($orgform_kurzbz);
	}
	//$query .= " AND nachname='Al-Mafrachi'";
	$query .= " ORDER BY nachname,
				vorname,
				person_id	
	";/*print_r($query);*/
	//echo '<pre>', var_dump($query), '</pre>';
	if (!($result = $db->db_query($query)))
	{
		die($db->db_last_error());
	}

	$gebiete_arr = array();
	while ($row = $db->db_fetch_object($result))
	{
		// Hack für BEW-BB, wenn auch BEW-DL-Ergebnisse vorliegen
		if ($row->stg_kurzbz == 'BEW' && $row->orgform_kurzbz == 'BB')
		{
			if ($row->gebiet_id == 2 || $row->gebiet_id == 44 || $row->gebiet_id == 95 || $row->gebiet_id == 10)
			{
				continue;
			}
		}

		if (!isset($ergebnis[$row->prestudent_id]))
		{
			$ergebnis[$row->prestudent_id] = new stdClass();
			$gebiete_arr[$row->prestudent_id] = array();
		}

		$ergebnis[$row->prestudent_id]->prestudent_id = $row->prestudent_id;
		$ergebnis[$row->prestudent_id]->person_id = $row->person_id;
		$ergebnis[$row->prestudent_id]->reihungstest_id = $row->reihungstest_id;
		//$ergebnis[$row->prestudent_id]->pruefling_id = $row->pruefling_id;
		$ergebnis[$row->prestudent_id]->nachname = $row->nachname;
		$ergebnis[$row->prestudent_id]->vorname = $row->vorname;
		$ergebnis[$row->prestudent_id]->gebdatum = $row->gebdatum;
		$ergebnis[$row->prestudent_id]->email = $row->email;
		$ergebnis[$row->prestudent_id]->geschlecht = $row->geschlecht;
		$ergebnis[$row->prestudent_id]->idnachweis = $row->idnachweis;
		$ergebnis[$row->prestudent_id]->registriert = $row->registriert;
		$ergebnis[$row->prestudent_id]->gesperrt = $row->gesperrt;
		$ergebnis[$row->prestudent_id]->stg_kurzbz = $row->stg_kurzbz;
		$ergebnis[$row->prestudent_id]->stg_bez = $row->stg_bez;
		$ergebnis[$row->prestudent_id]->ausbildungssemester = $row->ausbildungssemester;
		$ergebnis[$row->prestudent_id]->zgv = $row->zgv_code;
		$ergebnis[$row->prestudent_id]->zgvma = $row->zgvmas_code;
		$ergebnis[$row->prestudent_id]->raum = $row->raum;
		$ergebnis[$row->prestudent_id]->prioritaet = ($row->prioritaet != '' ? $row->prioritaet : '');
		$ergebnis[$row->prestudent_id]->orgform = $row->orgform_kurzbz;
		$ergebnis[$row->prestudent_id]->teilgenommen = $db->db_parse_bool($row->teilgenommen);
		$ergebnis[$row->prestudent_id]->qualifikationskurs = $db->db_parse_bool($row->qualifikationskurs);
		$ergebnis[$row->prestudent_id]->letzter_status = $row->letzter_status;

		if (!isset($ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]))
		{
			$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id] = new stdClass();
		}

		$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->name = $row->gebiet;

		if ($row->punkte >= $row->maxpunkte)
		{
			$punkte = $row->maxpunkte;
		}
		else
		{
			$punkte = $row->punkte;
		}

		$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->prozent = null;
		$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->punkte = $punkte;
		$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->punktemitoffset = null;

		// Punkte berechnen
		if (isset($punkte))
		{
			//offset zur Vermeidung negativer Prozentzahlen
			$punkte_positiv = $punkte + $row->offsetpunkte;
			$maxpunkte_positiv = $row->maxpunkte + $row->offsetpunkte;
			$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->punktemitoffset = $punkte_positiv;

			if ($row->punkte >= $row->maxpunkte)
			{
				$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->prozent = 100;
			}
			else
			{
				$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->prozent = $maxpunkte_positiv > 0 ? $punkte_positiv / $maxpunkte_positiv * 100 : null;
			}
		}

		// Bei Auswertungen ohne rt_id kann es vorkommen, dass Datensätze Doppelt sind
		// Bei der Summe darf ein Gebiet jedenfalls nur einmal summiert werden

		if (!in_array($row->gebiet_id, $gebiete_arr[$row->prestudent_id]))
		{
			$gebiete_arr[$row->prestudent_id][] = $row->gebiet_id;

			// Gewichtung bei BEW BB bei Schlussfolgerungen manuell korrigieren, da es zur falschen Berechnung kommt, wenn es auch BEW-DL Ergebnisse gibt
			if ($row->stg_kurzbz == 'BEW' && $row->orgform_kurzbz == 'BB' && $row->gebiet_id == 4)
			{
				$row->gewicht = 2;
			}
			// Gesamtpunkte
			if (isset($ergebnis[$row->prestudent_id]->gesamt))
			{
				$ergebnis[$row->prestudent_id]->gesamt += $ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->prozent * $row->gewicht;
			}
			else
			{
				$ergebnis[$row->prestudent_id]->gesamt = $ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->prozent * $row->gewicht;
			}

			if (isset($ergebnis[$row->prestudent_id]->gesamtpunkte))
			{
				$ergebnis[$row->prestudent_id]->gesamtpunkte += $punkte;
			}
			else
			{
				$ergebnis[$row->prestudent_id]->gesamtpunkte = $punkte;
			}

			if (isset($ergebnis[$row->prestudent_id]->gesamtoffsetpunkte))
			{
				$ergebnis[$row->prestudent_id]->gesamtoffsetpunkte += $ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->punktemitoffset;
			}
			else
			{
				$ergebnis[$row->prestudent_id]->gesamtoffsetpunkte = $ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->punktemitoffset;
			}

			if (isset($row->punkte))
			{
				if (isset($ergebnis[$row->prestudent_id]->gesamtgewicht))
				{
					$ergebnis[$row->prestudent_id]->gesamtgewicht += $row->gewicht;
				}
				else
				{
					$ergebnis[$row->prestudent_id]->gesamtgewicht = $row->gewicht;
				}
			}
		}
	}

	foreach ($ergebnis as $prestudentid => $erg)
	{
		//Berechnen Gesamtpunkte nach Formel: Summe(Punkte/Maxpunkte * Gewicht)/Summe(Gewichte) * 100
		if (isset($erg->gesamtgewicht) && $erg->gesamtgewicht > 0)
			$erg->gesamt /= $erg->gesamtgewicht;
	}

	$ergb = $ergebnis;
}

//Studiengaenge laden
$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz', true);
$stg_arr = array();

foreach ($stg_obj->result as $row)
{
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;
}

//Orgformen laden
$orgformen = new organisationsform();
$orgformen->getOrgformLV();
$orgformen_arr = array();

foreach ($orgformen->result as $row)
{
	$orgformen_arr[$row->orgform_kurzbz] = $row->bezeichnung;
}


if (isset($_REQUEST['format']) && $_REQUEST['format'] == 'xls')
{
	// Nach Gesamtergebnis sortieren
	$ergb = sortByField($ergb, 'gesamt');

	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$stgstr = '';
	if ((isset ($_REQUEST['reihungstest']) && $_REQUEST['reihungstest'] != ''))
	{
		$rtdates = array();
		foreach ($reihungstest as $index => $rt_id)
		{
			$rtdate = $datum_obj->formatDatum($rtest[$rt_id]->datum, 'd.m.Y');
			if (!isset($rtdates[$rtdate]))
				$rtdates[$rtdate] = array();

			$rtdates[$rtdate][] = $stg_arr[$rtest[$rt_id]->studiengang_kz];
			/*$stgstr .= " " . $stg_arr[$rtest[$rt_id]->studiengang_kz];
			if (isset($reihungstest[$index + 1]) && $reihungstest[$index + 1] !== )
				$stgstr .= ' ' . $rtdate;
			*//*if (!in_array($rtdate, $rtdates))
				$rtdates[] = $rtdate;*//*. " " . $datum_obj->formatDatum($rtest[$rt_id]->datum, 'd.m.Y');*/
		}
		foreach ($rtdates as $rtdate => $stgs)
		{
			$stgstr .= " " . implode("_", $stgs) . "_" . $rtdate;
		}
	}
	else
		$stgstr =  "aller Reihungstests";

	$workbook->send("Auswertung" . $stgstr . ".xls");
	$workbook->setVersion(8);
	$workbook->setCustomColor(15, 192, 192, 192); //Setzen der HG-Farbe Hellgrau
	$workbook->setCustomColor(22, 193, 0, 0); //Setzen der HG-Farbe Dunkelrot
	// Creating a worksheet
	$titel_studiengang = (isset ($_REQUEST['studiengang']) && $_REQUEST['studiengang'] != '');
	$titel_semester = (isset ($_REQUEST['semester']) && $_REQUEST['semester'] != '');

	// Eigener TItel bei Bachelor-Studiengängen
	if (isset($studiengangObj) && $studiengangObj->typ == 'b')
	{
		$worksheet =& $workbook->addWorksheet("Auswertung " . ($titel_studiengang ? $stg_arr[$_REQUEST['studiengang']] : '') . ($titel_semester ? ' ' . $semester . '.Semester' : ''));
	}
	else
	{
		$worksheet =& $workbook->addWorksheet("Technischer Teil " . ($titel_studiengang ? $stg_arr[$_REQUEST['studiengang']] : '') . ($titel_semester ? ' ' . $semester . '.Semester' : ''));
	}

	$worksheet->setInputEncoding('utf-8');
	$worksheet->setZoom(85);
	//Formate Definieren
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	$format_bold->setAlign("center");
	$format_bold->setFgColor(15);
	$format_bold->setVAlign('vcenter');

	$format_bold_border =& $workbook->addFormat();
	$format_bold_border->setBold();
	$format_bold_border->setAlign("center");
	$format_bold_border->setFgColor(15);
	$format_bold_border->setBorder(1);
	$format_bold_border->setBorderColor('white');

	$format_date =& $workbook->addFormat();
	$format_date->setNumFormat('YYYY-MM-DD');

	$format_registriert =& $workbook->addFormat();
	$format_registriert->setNumFormat('YYYY-MM-DD hh:mm:ss');

	$format_punkte =& $workbook->addFormat();
	$format_punkte->setNumFormat('0.00');

	$format_punkte_rot =& $workbook->addFormat();
	$format_punkte_rot->setNumFormat('0.00');
	$format_punkte_rot->setColor('22');

	$format_prozent =& $workbook->addFormat();
	$format_prozent->setNumFormat('0.00%');

	$format_prozent_rot =& $workbook->addFormat();
	$format_prozent_rot->setNumFormat('0.00%');
	$format_prozent_rot->setColor('22');

	$format_male =& $workbook->addFormat();
	$format_male->setColor('blue');

	$format_female =& $workbook->addFormat();
	$format_female->setColor('magenta');

	$spalte = 0;
	$zeile = 0;

	$worksheet->write(0, $spalte, 'PrestudentIn_ID', $format_bold);
	$worksheet->mergeCells(0, 0, 1, 0);
	$maxlength[0] = 15;
	$worksheet->write(0, ++$spalte, 'Nachname', $format_bold);
	$worksheet->mergeCells(0, 1, 1, 1);
	$maxlength[1] = 15;
	$worksheet->write(0, ++$spalte, 'Vorname', $format_bold);
	$worksheet->mergeCells(0, 2, 1, 2);
	$maxlength[2] = 15;
	$worksheet->write(0, ++$spalte, 'GebDatum', $format_bold);
	$worksheet->mergeCells(0, 3, 1, 3);
	$maxlength[3] = 10;
	$worksheet->write(0, ++$spalte, 'G', $format_bold);
	$worksheet->mergeCells(0, 4, 1, 4);
	$maxlength[4] = 2;
	$worksheet->write(0, ++$spalte, 'Registriert', $format_bold);
	$worksheet->mergeCells(0, 5, 1, 5);
	$maxlength[5] = 18;
	$worksheet->write(0, ++$spalte, 'STG', $format_bold);
	$worksheet->mergeCells(0, 6, 1, 6);
	$maxlength[6] = 4;
	$worksheet->write(0, ++$spalte, 'Studiengang', $format_bold);
	$worksheet->mergeCells(0, 7, 1, 7);
	$maxlength[7] = 25;
	$worksheet->write(0, ++$spalte, 'S', $format_bold);
	$worksheet->mergeCells(0, 8, 1, 8);
	$maxlength[8] = 2;
	$worksheet->write(0, ++$spalte, 'OrgForm', $format_bold);
	$worksheet->mergeCells(0, 9, 1, 9);
	$maxlength[9] = 8;
	$worksheet->write(0, ++$spalte, 'Prio', $format_bold);
	$worksheet->mergeCells(0, 10, 1, 10);
	$maxlength[10] = 5;
	$worksheet->write(0, ++$spalte, 'ZGV', $format_bold);
	$worksheet->mergeCells(0, 11, 1, 11);
	$maxlength[11] = 20;
	$worksheet->write(0, ++$spalte, 'ZGV MA', $format_bold);
	$worksheet->mergeCells(0, 12, 1, 12);
	$maxlength[12] = 20;

	$spalte = 12;
	$zeile = 0;

	foreach ($gebiet AS $gbt)
	{
		$worksheet->write($zeile, ++$spalte, strip_tags($gbt->name) . (	isset($gbt->gewicht) ? " (Gew: $gbt->gewicht)" : ""), $format_bold_border);
		$worksheet->mergeCells($zeile, $spalte, 0, $spalte + 2);
		$spalte += 2;
	}
	$worksheet->write($zeile, ++$spalte, 'Gesamt', $format_bold_border);
	$worksheet->mergeCells($zeile, $spalte, 0, $spalte + 2);

	$spalte = 12;
	$zeile = 0;

	foreach ($gebiet AS $gbt)
	{
		$maxlength[$spalte +1] = $maxlength[$spalte + 2] = $maxlength[$spalte + 3] = 14;
		$worksheet->write($zeile + 1, ++$spalte, 'Punkte', $format_bold_border);
		$worksheet->write($zeile + 1, ++$spalte, 'Punkte + Offset', $format_bold_border);
		$worksheet->write($zeile + 1, ++$spalte, 'Prozent', $format_bold_border);
	}
	$maxlength[$spalte +1] = $maxlength[$spalte + 2] = 14;
	$maxlength[$spalte + 3] = 17;
	$worksheet->write($zeile + 1, ++$spalte, 'Punkte', $format_bold_border);
	$worksheet->write($zeile + 1, ++$spalte, 'Punkte + Offset', $format_bold_border);
	$worksheet->write($zeile + 1, ++$spalte, 'Prozent (gewichtet)', $format_bold_border);

	$maxspalten = $spalte;

	$zeile = 1;
	$spalte = 0;

	if (isset($ergb))
	{
		foreach ($ergb AS $erg)
		{
			$zeile++;
			$spalte = 0;
			$worksheet->write($zeile, $spalte, $erg->prestudent_id);
			$worksheet->write($zeile, ++$spalte, $erg->nachname);
			$worksheet->write($zeile, ++$spalte, $erg->vorname);
			$worksheet->write($zeile, ++$spalte, $erg->gebdatum, $format_date);
			if ($erg->geschlecht == 'm')
			{
				$worksheet->write($zeile, ++$spalte, $erg->geschlecht, $format_male);
			}
			else
			{
				$worksheet->write($zeile, ++$spalte, $erg->geschlecht, $format_female);
			}
			$worksheet->write($zeile, ++$spalte, $erg->registriert, $format_registriert);
			$worksheet->write($zeile, ++$spalte, $erg->stg_kurzbz);
			$worksheet->write($zeile, ++$spalte, $erg->stg_bez);
			$worksheet->write($zeile, ++$spalte, $erg->ausbildungssemester);
			$worksheet->write($zeile, ++$spalte, $erg->orgform);
			$worksheet->write($zeile, ++$spalte, $erg->prioritaet);
			$worksheet->write($zeile, ++$spalte, $zgv_arr[$erg->zgv]);
			$worksheet->write($zeile, ++$spalte, $zgvma_arr[$erg->zgvma]);
			foreach ($gebiet AS $gbt)
			{
				if (isset($erg->gebiet[$gbt->gebiet_id]))
				{
					if ($erg->gebiet[$gbt->gebiet_id]->punkte != '' && $erg->gebiet[$gbt->gebiet_id]->punkte != '0')
					{
						$worksheet->writeNumber($zeile, ++$spalte, $erg->gebiet[$gbt->gebiet_id]->punkte, $format_punkte);
					}
					else
					{
						$worksheet->writeNumber($zeile, ++$spalte, $erg->gebiet[$gbt->gebiet_id]->punkte, $format_punkte_rot);
					}
					if ($erg->gebiet[$gbt->gebiet_id]->punktemitoffset != '' && $erg->gebiet[$gbt->gebiet_id]->punktemitoffset != '0')
					{
						$worksheet->writeNumber($zeile, ++$spalte, $erg->gebiet[$gbt->gebiet_id]->punktemitoffset, $format_punkte);
					}
					else
					{
						$worksheet->writeNumber($zeile, ++$spalte, $erg->gebiet[$gbt->gebiet_id]->punktemitoffset, $format_punkte_rot);
					}
					if ($erg->gebiet[$gbt->gebiet_id]->prozent != '0%')
					{
						$worksheet->writeNumber($zeile, ++$spalte, $erg->gebiet[$gbt->gebiet_id]->prozent / 100, $format_prozent);
					}
					else
					{
						$worksheet->writeNumber($zeile, ++$spalte, $erg->gebiet[$gbt->gebiet_id]->prozent / 100, $format_prozent_rot);
					}
				}
				else
				{
					$worksheet->write($zeile, ++$spalte, '');
					$worksheet->write($zeile, ++$spalte, '');
					$worksheet->write($zeile, ++$spalte, '');
				}
			}
			$worksheet->writeNumber($zeile, ++$spalte, $erg->gesamtpunkte, $format_punkte);
			$worksheet->writeNumber($zeile, ++$spalte, $erg->gesamtoffsetpunkte, $format_punkte);
			$worksheet->writeNumber($zeile, ++$spalte, $erg->gesamt / 100, $format_prozent);
		}
	}

	//Die Breite der Spalten setzen
	foreach ($maxlength as $i => $breite)
	{
		$worksheet->setColumn($i, $i, $breite);
	}

	if (isset($erg_kat) && count($erg_kat) > 0)
	{
		// Creating second worksheet
		$worksheet2 =& $workbook->addWorksheet("Persoenlichkeit");
		$worksheet2->setInputEncoding('utf-8');
		$worksheet2->setZoom(85);

		$spalte = 0;
		$zeile = 0;

		$worksheet2->write(0, $spalte, 'PrestudentIn_ID', $format_bold);
		$worksheet2->mergeCells(0, 0, 1, 0);
		$maxlength[0] = 15;
		$worksheet2->write(0, ++$spalte, 'Nachname', $format_bold);
		$worksheet2->mergeCells(0, 1, 1, 1);
		$maxlength[1] = 15;
		$worksheet2->write(0, ++$spalte, 'Vorname', $format_bold);
		$worksheet2->mergeCells(0, 2, 1, 2);
		$maxlength[2] = 15;
		$worksheet2->write(0, ++$spalte, 'GebDatum', $format_bold);
		$worksheet2->mergeCells(0, 3, 1, 3);
		$maxlength[3] = 10;
		$worksheet2->write(0, ++$spalte, 'G', $format_bold);
		$worksheet2->mergeCells(0, 4, 1, 4);
		$maxlength[4] = 2;
		$worksheet2->write(0, ++$spalte, 'Registriert', $format_bold);
		$worksheet2->mergeCells(0, 5, 1, 5);
		$maxlength[5] = 18;
		$worksheet2->write(0, ++$spalte, 'STG', $format_bold);
		$worksheet2->mergeCells(0, 6, 1, 6);
		$maxlength[6] = 4;
		$worksheet2->write(0, ++$spalte, 'Studiengang', $format_bold);
		$worksheet2->mergeCells(0, 7, 1, 7);
		$maxlength[7] = 25;
		$worksheet2->write(0, ++$spalte, 'S', $format_bold);
		$worksheet2->mergeCells(0, 8, 1, 8);
		$maxlength[8] = 2;
		$worksheet2->write(0, ++$spalte, 'ZGV', $format_bold);
		$worksheet2->mergeCells(0, 9, 1, 9);
		$maxlength[9] = 20;
		$worksheet2->write(0, ++$spalte, 'ZGV MA', $format_bold);
		$worksheet2->mergeCells(0, 10, 1, 10);
		$maxlength[10] = 20;

		$spalte = 9;
		$zeile = 0;

		foreach ($kategorie AS $gbt)
		{
			++$spalte;
			$worksheet2->write($zeile, ++$spalte, $gbt->name, $format_bold_border);
			$worksheet2->mergeCells($zeile, $spalte, 0, $spalte + 1);
			$maxlength[$spalte] = 10;
		}

		$spalte = 10;
		$zeile = 0;

		foreach ($kategorie AS $gbt)
		{
			$worksheet2->write($zeile + 1, ++$spalte, 'Punkte', $format_bold_border);
			$worksheet2->write($zeile + 1, ++$spalte, 'Typ', $format_bold_border);
			$maxlength[$spalte] = 10;
		}

		$maxspalten = $spalte;

		$zeile = 1;
		$spalte = 0;

		foreach ($erg_kat AS $erg)
		{
			$zeile++;
			$spalte = 0;
			$worksheet2->write($zeile, $spalte, $erg->prestudent_id);
			$worksheet2->write($zeile, ++$spalte, $erg->nachname);
			$worksheet2->write($zeile, ++$spalte, $erg->vorname);
			$worksheet2->write($zeile, ++$spalte, $erg->gebdatum, $format_date);
			if ($erg->geschlecht == 'm')
			{
				$worksheet2->write($zeile, ++$spalte, $erg->geschlecht, $format_male);
			}
			else
			{
				$worksheet2->write($zeile, ++$spalte, $erg->geschlecht, $format_female);
			}
			$worksheet2->write($zeile, ++$spalte, $erg->registriert, $format_registriert);
			$worksheet2->write($zeile, ++$spalte, $erg->stg_kurzbz);
			$worksheet2->write($zeile, ++$spalte, $erg->stg_bez);
			$worksheet2->write($zeile, ++$spalte, $erg->ausbildungssemester);
			$worksheet2->write($zeile, ++$spalte, $zgv_arr[$erg->zgv]);
			$worksheet2->write($zeile, ++$spalte, $zgvma_arr[$erg->zgvma]);
			foreach ($kategorie AS $gbt)
			{
				$worksheet2->write($zeile, ++$spalte, $erg->kategorie[$gbt->name]->punkte);
				$worksheet2->write($zeile, ++$spalte, $erg->kategorie[$gbt->name]->typ);
			}
		}

		//Die Breite der Spalten setzen
		foreach ($maxlength as $i => $breite)
		{
			$worksheet2->setColumn($i, $i, $breite);
		}
	}
	$workbook->close();
}
else
{
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>

	<head>
	<title>Testtool - Auswertung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link type="text/css" rel="stylesheet" href="../../skin/style.css.php">
	
	<link rel="stylesheet" type="text/css" href="../../vendor/components/jqueryui/themes/base/jquery-ui.min.css" >
	<link rel="stylesheet" type="text/css" href="../../vendor/mottie/tablesorter/dist/css/theme.default.min.css">
	<link rel="stylesheet" type="text/css" href="../../vendor/mottie/tablesorter/dist/css/jquery.tablesorter.pager.min.css">
	<link rel="stylesheet" type="text/css" href="../../vendor/twbs/bootstrap3/dist/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="../../public/css/tools/auswertung_fhtw.css">
	<script type="text/javascript" src="../../vendor/components/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/ui/i18n/datepicker-de.js"></script>
	<script type="text/javascript" src="../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.widgets.min.js"></script>
	<script type="text/javascript" src="../../vendor/mottie/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js"></script>
	<script type="text/javascript" src="../../vendor/twbs/bootstrap3/dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="../../vendor/rmariuzzo/jquery-checkboxes/dist/jquery.checkboxes-1.0.7.min.js"></script>
	';

	//include('../../include/meta/jquery.php');
	//include('../../include/meta/jquery-tablesorter.php');

	/*echo '
	<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript" src="../../vendor/rmariuzzo/jquery-checkboxes/dist/jquery.checkboxes-1.0.7.min.js"></script>';*/
	echo '
	<script type="text/javascript">
	$(document).ready(function()
	{
		$("#rtcheckboxesbtn").click(toggleRtDropdown);
		$("#rtcheckboxes .rtlabel").click(
			function(e)
			{
				//only toggle if div itself and not child label or input
				if (e.target.type !== \'checkbox\' && e.target === this)
				{
					var rtel = $(this).find("input[type=\'checkbox\']");
					rtel.prop("checked",!rtel.prop("checked"));
				}
				showSelectedRts();				
			}
		);
		$(".rtlabel input[type=\'checkbox\']").change(
			showSelectedRts	
		);
		showSelectedRts();
		
		//RT dropdown verstecken wenn Klick ausserhalb
		$(document).mousedown(function(e)
		{
			var dropdown = $("#rtcheckboxes");
			
			if (!dropdown.is(e.target) && dropdown.has(e.target).length === 0 && !$("#rtcheckboxesbtn").is(e.target))
				dropdown.hide();
		});
		
		$( ".datepicker_datum" ).datepicker({
				 changeMonth: true,
				 changeYear: true,
				 dateFormat: "dd.mm.yy"
		});

		$("#prestudent").autocomplete({
			source: "auswertung_fhtw.php?autocomplete=prestudent",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].nachname+" "+ui.content[i].vorname+" "+ui.content[i].stg+" "+ui.content[i].status+" "+ui.content[i].prestudent_id+" ("+ui.content[i].student_uid+")";
					ui.content[i].label=ui.content[i].nachname+" "+ui.content[i].vorname+" "+ui.content[i].stg+" "+ui.content[i].status+" "+ui.content[i].prestudent_id+" ("+ui.content[i].student_uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#prestudent_id").val(ui.item.prestudent_id);
			}
		});
		
		$("#prestudent").on("input", function(){
		    var numchecked = $("#rtcheckboxes input[type=checkbox]:checked").length;
		    if (numchecked > 0)
		    {
				$("#rtcheckboxes input[type=checkbox]").prop("checked", false);
				showSelectedRts();
			}
		});
		
		$("#prestudent").keyup( function(){
			$("#prestudent_id").val(this.value);
		});
		
		$("#zuteilungAutocomplete").autocomplete({
			source: "auswertung_fhtw.php?autocomplete=prestudentAdd&'.http_build_query(array('studiensemester_kurzbz' => $rtStudiensemester)).'",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].nachname+" "+ui.content[i].vorname+" "+ui.content[i].stg+" "+ui.content[i].status+" "+ui.content[i].prestudent_id;
					ui.content[i].label=ui.content[i].nachname+" "+ui.content[i].vorname+" "+ui.content[i].stg+" "+ui.content[i].status+" "+ui.content[i].prestudent_id;
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#zuteilungAutocompleteHidden").val(ui.item.prestudent_id);
			}
		});
		
		$("#auswertung_table").tablesorter(
		{			
			widgets: ["zebra", "filter", "columnSelector"],
			sortList: [[16,1],[19,1],[3,0],[4,0]],//17th (index 16) fake hidden column for correct sort with colspan
			headers: {0: { sorter: false, filter: false}, 2: { sorter: false, filter: false}, 4: { dateFormat: "ddmmyyyy" }, 16: { sorter: false, filter: false}}
			/*widgetOptions : {
				columnSelector_container : $("#columnSelector"),
				columnSelector_saveColumns: true}			*/
		});
		
		//hide tablesorter filter field if column is hidden
		var tbldatalength = $("#auswertung_table tbody tr:nth-child(1) td").length;
		if (tbldatalength < 1)
			$(".tablesorter-filter-row").hide();
		else
		{	
			for (var i = 0; i < $("#auswertung_table tbody tr:nth-child(1) td").length; i++)
			{
				var colnr = i + 1;
				var cell = $("#auswertung_table tbody tr:nth-child(1) td:nth-child(" + colnr + ")");
				if (cell.css("display") === "none")
				{
					$("#auswertung_table tr.tablesorter-filter-row td:nth-child(" + colnr + ")").css("display", "none");
				}
			}
		}
		/*$.tablesorter.columnSelector.attachTo( $("#auswertung_table"), "#popover-target");*/
		
		/*$("#columnSelector").popover(
		{
			placement: "right",
			html: true, // required if content has HTML
			content: $("#popover-target")
		});*/
		
		$("#toggle_table").on("click", function(e) 
		{
			$("#auswertung_table").checkboxes("toggle");
			e.preventDefault();
			if ($("input.prestudentCheckbox:checked").length > 0)
				$("#mailSendButton").html("Mail an markierte senden");
			else
				$("#mailSendButton").html("Mail an alle senden");
		});

		$("#uncheck_table").on("click", function(e) 
		{
			$("#auswertung_table").checkboxes("uncheck");
			e.preventDefault();
			if ($("input.prestudentCheckbox:checked").length > 0)
				$("#mailSendButton").html("Mail an markierte senden");
			else
				$("#mailSendButton").html("Mail an alle senden");
		});
		
		$(".prestudentCheckbox").change(function()
		{
			if ($("input.prestudentCheckbox:checked").length > 0)
				$("#mailSendButton").html("Mail an markierte senden");
			else
				$("#mailSendButton").html("Mail an alle senden");
		});
						
		$("#auswertung_table").checkboxes("range", true);
		
		$(".deleteColumn").hide();
		$("#toggleDelete").on("click", function(e) 
		{
			$(".deleteSpan").toggle();
			$(".punkteSpan").toggle();
			if ($(".deleteSpan").is(":visible"))
			{
				$(".deleteSpan").closest("td").css("text-align","center");
			}
			else
			{
				$(".deleteSpan").closest("td").css("text-align","right");
			}
		});
		
		$("#auswertenButton").on("click", function(e) 
		{
			$(".loaderIcon").show();
		});
		
		$("#showUebertragenOptionsButton").on("click", function(e) 
		{
			$("#uebertragenOptions").toggle(300);
		});
		
		if($("#uebertragenOptionGesamtpunkte").not(":checked"))
		{
			$("#div_checkbox_bewerber").addClass("disabled");
			$("#div_checkbox_bewerber").find("label").addClass("text-muted");
			$("#div_checkbox_bewerber").find("label").prop("title", "Erst \"Gesamtpunkte\" und \"Reihungsverfahren absolviert\" setzen");
			$("#uebertragenOptionBewerber").prop("disabled", true);
		}
		$("#uebertragenOptionGesamtpunkte").on("click", function(e) 
		{
			if($(this).is(":checked"))
			{
				$("#div_checkbox_bewerber").removeClass("disabled");
				$("#div_checkbox_bewerber").find("label").removeClass("text-muted");
				$("#uebertragenOptionBewerber").prop("disabled", false);
			}
			else
			{
				$("#div_checkbox_bewerber").addClass("disabled");
				$("#div_checkbox_bewerber").find("label").addClass("text-muted");
				$("#div_checkbox_bewerber").find("label").prop("title", "Erst \"Gesamtpunkte\" und \"Reihungsverfahren absolviert\" setzen");
				$("#uebertragenOptionBewerber").prop("disabled", true);
			}
		});
	});
	
	function deleteResult(prestudent_id, gebiet_id, name, gebiet_bezeichnung)
	{
		if (confirm("Wollen Sie das Ergebnis des Gebiets "+gebiet_bezeichnung+" der Person "+name+" wirklich löschen?"))
		{
			data = {
				prestudent_id: prestudent_id,
				gebiet_id: gebiet_id,
				deleteSingleResult: true
			};

			$.ajax({
				url: "auswertung_fhtw.php",
				data: data,
				type: "POST",
				dataType: "json",
				success: function(data)
				{
					if(data.status !== "ok")
					{
						$("#msgbox").attr("class","alert alert-danger");
						$("#msgbox").show();
						$("#msgbox").html(data["msg"]);
					}
					else
					{
						$(".pst_"+prestudent_id+"_gbt_"+gebiet_id).html("");
						$("#msgbox").show();
						$("#msgbox").html(data["msg"]).delay(2000).fadeOut();
					}
				},
				error: function(data)
				{
					$("#msgbox").attr("class","alert alert-danger");
					$("#msgbox").show();
					$("#msgbox").html(data["msg"]);
				}
			});
		}
	}
	function prueflingEntSperren(prestudent_id, name, art)
	{
		if (art === true)
			var text = "sperren";
		else if (art === false)
			var text = "entsperren";

		if (confirm("Wollen Sie den Studenten "+ name + " wirklich " + text + "?"))
		{
			data = {
				prestudent_id: prestudent_id,
				art: art,
				rtprueflingEntSperren: true
			};

			$.ajax({
				url: "auswertung_fhtw.php",
				data: data,
				type: "POST",
				dataType: "json",
				success: function(data)
				{
					if(data.status !== "ok")
					{
						$("#msgbox").attr("class","alert alert-danger");
						$("#msgbox").show();
						$("#msgbox").html(data["msg"]);
					}
					else
					{
						if (art === true)
						{
							$("#prueflingentsperren_" + prestudent_id).removeClass("hidden");
							$("#prueflingsperren_" + prestudent_id).addClass("hidden");
						}
						else if (art === false)
						{
							$("#prueflingsperren_" + prestudent_id).removeClass("hidden");
							$("#prueflingentsperren_" + prestudent_id).addClass("hidden");
						}
					}
				},
				error: function(data)
				{
					$("#msgbox").attr("class","alert alert-danger");
					$("#msgbox").show();
					$("#msgbox").html(data["msg"]);
				}
			});
		}
	}
	function deleteAllResults(prestudent_id, name)
	{
		if (confirm("Wollen Sie ALLE Ergebnisse der Person "+name+" wirklich löschen"))
		{
			data = {
				prestudent_id: prestudent_id,
				deleteAllResults: true
			};

			$.ajax({
				url: "auswertung_fhtw.php",
				data: data,
				type: "POST",
				dataType: "json",
				success: function(data)
				{
					if(data.status !== "ok")
					{
						$("#msgbox").attr("class","alert alert-danger");
						$("#msgbox").show();
						$("#msgbox").html(data["msg"]);
					}
					else
					{
						$("#row_"+prestudent_id).find("td.punkte, td.col_gesamtpunkte").each (function() 
						{
						  $(this).html("");
						});
						$("#msgbox").show();
						$("#msgbox").html(data["msg"]).delay(2000).fadeOut();
					}
				},
				error: function(data)
				{
					$("#msgbox").attr("class","alert alert-danger");
					$("#msgbox").show();
					$("#msgbox").html(data["msg"]);
				}
			});
		}
	}
	
	function freischalten(reihungstest, art)
	{
		data = {
			reihungstest_id: reihungstest,
			art: art,
			rtFreischalten: true
		};

		$.ajax({
			url: "auswertung_fhtw.php",
			data: data,
			type: "POST",
			dataType: "json",
			success: function(data)
			{
				if(data.status !== "ok")
				{
					$("#msgbox").attr("class","alert alert-danger");
					$("#msgbox").show();
					$("#msgbox").html(data["msg"]);
				}
				else
				{
					if (art === true) 
					{
						$("#freischaltenWarning").hide();
						$("#freischaltenInfo").show();
						$("#msgbox").show();
						$("#msgbox").html(data["msg"]).delay(2000).fadeOut();
					}
					else if (art === false)
					{
						$("#freischaltenWarning").show();
						$("#freischaltenInfo").hide();
						$("#msgbox").show();
						$("#msgbox").html(data["msg"]).delay(2000).fadeOut();
					}
				}
			},
			error: function(data)
			{
				$("#msgbox").attr("class","alert alert-danger");
				$("#msgbox").show();
				$("#msgbox").html(data["msg"]);
			}
		});
	}
	
	function testende()
	{
		var selected = [];
		if ($("input.prestudentCheckbox:checked").length === 0)
		{
			alert("Bitte wählen Sie mindestens einen Eintrag aus der Liste");
			return false;
		}
		else
		{
			if (confirm("Setzt bei allen markierten Personen \'Zum Reihungstest angetreten\' und informiert die entsprechende Studiengangsassistenz. Wollen Sie fortfahren?"))
			{
				$("#auswertung_table tr").each(function() 
				{
					var prestudent_id = $(this).find("input.prestudentCheckbox:checked").prop("name");
				
					if (prestudent_id)
					{
						var rt_id = $(this).find("td.rt_id").text();
						selected.push({prestudent_id: prestudent_id, reihungstest_id: rt_id});
					}
				});
				
				$(".loaderIcon").show();
				
				data = {
					prestudents: selected,
					testende: true
				};
	
				$.ajax({
					url: "auswertung_fhtw.php",
					data: data,
					type: "POST",
					dataType: "json",
					success: function(data)
					{
						if(data.status !== "ok")
						{
							$("#msgbox").attr("class","alert alert-danger");
							$("#msgbox").show();
							$("#msgbox").html(data["msg"]);
						}
						else
						{
							$("#msgbox").attr("class","alert alert-success");
							$(".loaderIcon").hide();
							$("#msgbox").show();
							$("#msgbox").html(data["msg"]);
							//$("#msgbox").html(data["msg"]).delay(2000).fadeOut();
						}
					},
					error: function(data)
					{
						$("#msgbox").attr("class","alert alert-danger");
						$(".loaderIcon").hide();
						$("#msgbox").show();
						$("#msgbox").html(data["msg"]);
					}
				});
			}
		}
	}
	function sendMail()
	{
		// Wenn Checkboxen markiert sind, an diese senden, sonst an alle
		if ($("input.prestudentCheckbox:checked").length > 0)
		{
			var elements = $("input.prestudentCheckbox:checked");
		}
		else
		{
			var elements = $("input.prestudentCheckbox:visible");
		}

		var mailadressen = "";
		var adresse = "";
		var counter = 0;
		var adresseArray = [];

		// Schleife ueber die einzelnen Elemente
		// Aus Spamgründen dürfen je Nachricht maximal 100 Empfänger enthalten sein
		// Deshalb wird nach 100 Einträgen ein neues window.location.href erzeugt
		// Außerdem darf die URL nicht länger als 1988 Zeichen (2000 - 12 Zeichen URL-Prefix) sein.
		$.each(elements, function(index, item)
		{
			adresse = $(this).closest("tr").find("td.clm_email a:first").attr("href");
			adresse = adresse.replace(/^mailto?:/, "");
			
			if($.inArray(adresse, adresseArray) === -1)
			{
				if (counter > 0 && (counter % 100 === 0) || (adresseArray.join(";").length + adresse.length > 1988))
				{
					window.location.href = "mailto:?bcc="+adresseArray.join(";");
					adresseArray = [];
					adresseArray.push(adresse);
					counter = 0;
				}
				else
				{
					adresseArray.push(adresse);
				}
				counter ++;
			}			
		});
		window.location.href = "mailto:?bcc="+adresseArray.join(";");
	}
	
	function clearList()
	{
		$.ajax({
			url: "auswertung_fhtw.php",
			data: {clearList: true},
			type: "POST",
			dataType: "json",
			success: function(data)
			{
				if(data.status !== "ok")
				{
					$("#msgbox").attr("class","alert alert-danger");
					$("#msgbox").show();
					$("#msgbox").html(data["msg"]);
				}
				else
				{
					$("#freischaltenWarning").show();
					$("#freischaltenInfo").hide();
					$("#msgbox").show();
					$("#msgbox").html(data["msg"]).delay(2000).fadeOut();
				}
			},
			error: function(data)
			{
				$("#msgbox").attr("class","alert alert-danger");
				$("#msgbox").show();
				$("#msgbox").html(data["msg"]);
			}
		});
	}
	
	function checkAllWithResult()
	{
		// Schleife ueber die einzelnen Elemente
		$(".col_gesamtpunkte .erg_gesamt_visible").each(function()
		{
			if ($(this).text().trim() !== "")
			{
				$(this).parents("tr").find("input[type=checkbox]").prop("checked", true);
			}
			else
			{
				$(this).parents("tr").find("input[type=checkbox]").prop("checked", false);
			}
		});
		if ($("input.prestudentCheckbox:checked").length > 0)
			$("#mailSendButton").html("Mail an markierte senden");
		else
			$("#mailSendButton").html("Mail an alle senden");
	}
	function punkteUebertragen()
	{
		var prestudentPunkteArr = [];
		var gesamtpunkteSetzen = false;
		var zuBewerberMachen = false;
		if ($("input.prestudentCheckbox:checked").length === 0)
		{
			alert("Bitte wählen Sie mindestens einen Eintrag aus der Liste");
			return false;
		}
		else if ($("#uebertragenOptionBewerber:checked").length === 1 && $("#uebertragenOptionGesamtpunkte:checked").length !== 1)
		{
			alert("Um den Bewerberstatus setzen zu können, muss \"Gesamtpunkte\" und \"Reihungsverfahren absolviert\" gesetzt sein");
			return false;
		}
		else
		{
			$("input.prestudentCheckbox:checked").each(function() 
			{
				prestudentPunkteArr.push({
					prestudent_id: $(this).attr("name"), 
					ergebnis:  $(this).parents("tr").find(".erg_gesamt").text(),
					reihungstest_id: $(this).parents("tr").find(".rt_id").text(),
				});
		    });
		    
			$(".loaderIcon").show();
			if ($("#uebertragenOptionGesamtpunkte:checked").length === 1)
			{
				gesamtpunkteSetzen = true;
			}
			if ($("#uebertragenOptionBewerber:checked").length === 1)
			{
				zuBewerberMachen = true;
			}
			
			data = {
				prestudentPunkteArr: prestudentPunkteArr,
				gesamtpunkteSetzen: gesamtpunkteSetzen,
				zuBewerberMachen: zuBewerberMachen,
				punkteUebertragen: true
			};
	
			$.ajax({
				url: "auswertung_fhtw.php",
				data: data,
				type: "POST",
				dataType: "json",
				success: function(data)
				{
					$("#msgbox").html("");
					if(data["msg_success"] !== "")
					{
						$("#msgbox").attr("class","alert alert-success");
						$(".loaderIcon").hide();
						$("#msgbox").show();
						$("#msgbox").append(data["msg_success"]);
					}
					else
					{
						$(".loaderIcon").hide();
					}
					if(data["msg_warning"] !== "")
					{
						$("#msgbox").attr("class","alert alert-warning");
						$(".loaderIcon").hide();
						$("#msgbox").show();
						$("#msgbox").append(data["msg_warning"]);
						//$("#msgbox").html(data["msg"]).delay(2000).fadeOut();
					}
					else
					{
						$(".loaderIcon").hide();
					}
					if(data["msg_error"] !== "")
					{
						$("#msgbox").attr("class","alert alert-danger");
						$(".loaderIcon").hide();
						$("#msgbox").show();
						$("#msgbox").append(data["msg_error"]);
					}
					else
					{
						$(".loaderIcon").hide();
					}
				},
				error: function(data)
				{
					$("#msgbox").attr("class","alert alert-danger");
					$("#msgbox").show();
					$("#msgbox").html(data["msg"]);
				}
			});
		}
	}
	function toggleRtDropdown()
	{
		if ($("#rtcheckboxes").is(":visible"))
		{
			$("#rtcheckboxes").hide();
		}
		else if($("#rtcheckboxes").is(":hidden"))
			$("#rtcheckboxes").show();
	}
	function showSelectedRts()
	{
		var rtsstr = "";
		var first = true;
		$("#rtcheckboxes input:checked").each(
			function() {
				var rt_id = this.id;
				if (!first)
					rtsstr += "<br />";
						
				rtsstr += $("label[for=" + rt_id + "]").text();
				first = false;
			}
		);
		if (rtsstr === "")
			rtsstr = "-- keine Auswahl --";
		$("#rtcheckboxesbtn").html(rtsstr);
			
	}
	</script>
	</head>

	<body>
	<div class="container-fluid">
	<h3>Auswertung Reihungstest</h3>
	<div class="row">
	<div class="col-lg-6 col-md-12">
			<form method="POST">
			<table><tr><td>
			<table class="table table-bordered" id="paramstbl">
			<tr>
			<td id="rtwaehlen">
				Reihungstest wählen: ';
	$selectedrtstr = '';
	$checkbxstr = '';
	$first = true;
	$noparamsselected = $prestudent_id == '' && $reihungstest == '' && $datum_von == '' && $datum_bis == '' && $studiengang == '' && $semester == '';
	//$maxeachline = 1;
	foreach ($rtest as $rt)
	{
		$rtstr = $rt->datum . ' ' . $datum_obj->formatDatum($rt->uhrzeit,'H:i') . ' ' . (isset($stg_arr[$rt->studiengang_kz]) ? $stg_arr[$rt->studiengang_kz] : '') . ' ' . $rt->ort_kurzbz . ' ' . $rt->anmerkung;

		$checked = '';
		if (isset($reihungstest) && is_array($reihungstest))
		{
			foreach ($reihungstest as $rttest)
			{
				if ($rttest === $rt->reihungstest_id)
				{
					$checked = ' checked';

					if (!$first)
						$selectedrtstr .= '<br />';

					$selectedrtstr .= $rtstr;
					$first = false;
					break;
				}
			}
		}
		elseif($noparamsselected && $rt->datum == date('Y-m-d'))
		{
			//wenn nichts ausgewählt, heute Reihungstests vorausgewählt
			$checked = ' checked';

			if (!$first)
				$selectedrtstr .= '<br />';

			$selectedrtstr .= $rtstr;
			$first = false;
		}

		$checkbxstr .=  '<div class="input-group rtlabel"><input type="checkbox" name="reihungstest[]" id="rt_' . $rt->reihungstest_id . '" value="' . $rt->reihungstest_id . '"' . $checked . ' />';
		$checkbxstr .= '<label for="rt_' . $rt->reihungstest_id . '">&nbsp;' . $rtstr . '</label></div>';
	}

	$btntxt = $selectedrtstr === '' ? '-- keine Auswahl --' : $selectedrtstr;
	echo '<button type="button" id="rtcheckboxesbtn" style="text-align: left">' . $btntxt . '</button>
			<div id="rtcheckboxes">';
	echo $checkbxstr;

	echo '</div></td></tr>
			<tr><td>
				<label>Studiengang:
					<SELECT name="studiengang">
						<OPTION value="">Alle</OPTION>';
	foreach ($stg_arr as $kz => $kurzbz)
	{
		if (isset($_REQUEST['studiengang']) && $_REQUEST['studiengang'] == $kz && $_REQUEST['studiengang'] != '')
		{
			$selected = 'selected';
		}
		else
		{
			$selected = '';
		}

		echo '<OPTION value="' . $kz . '" ' . $selected . '>' . $kurzbz . '</OPTION>';
	}
	echo '</SELECT></label>
				<label>Semester:
					<SELECT name="semester">
						<OPTION value="">Alle</OPTION>';
	for ($i = 1; $i < 9; $i++)
	{
		if (isset($semester) && $semester == $i)
		{
			echo "<option value=\"$i\" selected>$i</option>";
		}
		else
		{
			echo "<option value=\"$i\">$i</option>";
		}
	}
	echo '</SELECT></label>
				<label>OrgForm:
					<SELECT name="orgform_kurzbz">
						<OPTION value="">Alle</OPTION>';
	foreach ($orgformen_arr as $kurzbz => $bezeichnung)
	{
		if ($orgform_kurzbz == $kurzbz)
		{
			$selected = 'selected';
		}
		else
		{
			$selected = '';
		}

		echo '<OPTION value="' . $kurzbz . '" ' . $selected . '>' . $kurzbz . '</OPTION>';
	}
	echo '</SELECT></label>';

	echo '&nbsp;<label>von Datum: <INPUT class="datepicker_datum" type="text" name="datum_von" maxlength="10" size="10" value="' . $datum_obj->formatDatum($datum_von, 'd.m.Y') . '" /></label>&nbsp;';
	echo '<label>bis Datum: <INPUT class="datepicker_datum" type="text" name="datum_bis" maxlength="10" size="10" value="' . $datum_obj->formatDatum($datum_bis, 'd.m.Y') . '" /></label>';
	echo '</td></tr>';
	echo '<tr><td>';
	echo 'PrestudentIn: <INPUT id="prestudent" type="text" name="prestudent_id" size="50" value="' . $prestudent_id . '" placeholder="Name, UID oder Prestudent_id eingeben"/><input type="hidden" id="prestudent_id" name="prestudent_id" value="' . $prestudent_id . '" />';
	echo '</td></tr>
			</table></td><td id="auswertencell">';
	echo '<INPUT type="submit" class="btn btn-primary" value="Anzeigen" name="rtauswsubmit" id="auswertenButton"/><br><br>';
	echo '<a href="auswertung_fhtw.php?studiengang=' . $studiengang . '
										&semester=' . $semester . '
										&datum_von=' . $datum_von . '
										&datum_bis=' . $datum_bis . '
										&prestudent_id=' . $prestudent_id . '
										&' . http_build_query(array('reihungstest' => $reihungstest)) . '
										&orgform_kurzbz=' . $orgform_kurzbz . '
										&format=xls"
									class="btn btn-primary"
									role="button">
										<span class="glyphicon glyphicon-export"></span> Excel</a>';
	echo '</td></tr></table></form>';
	echo '<div class="row"><div class="col-xs-12">';
	echo 'Auswahl: <strong>';
	if (isset ($_REQUEST['studiengang']) && $_REQUEST['studiengang'] != '')
	{
		echo $stg_arr[$_REQUEST['studiengang']] . ' ';
	}
	else
	{
		echo 'Alle ';
	}
	if (isset ($_REQUEST['semester']) && $_REQUEST['semester'] != '')
	{
		echo $semester . '. Semester ';
	}
	if ($datum_von != '')
	{
		echo 'von ' . $datum_obj->formatDatum($datum_von, 'd.m.Y');
	}
	if ($datum_bis != '')
	{
		echo ' bis ' . $datum_obj->formatDatum($datum_bis, 'd.m.Y');
	}
	if ($prestudent_id != '')
	{
		echo ' PrestudentID: ' . $prestudent_id;
	}

	echo '</strong>';

	echo ' </div></div><br />';

	$berechtigteOes = array();
	foreach ($stg_obj->result AS $stg)
	{
		if ($stg->typ != 'b')
		{
			$berechtigteOes[] = $stg->oe_kurzbz;
		}
	}
	echo '<form class="form-inline" role="form" method="post" action="' . basename(__FILE__) . '?
				studiengang=' . $studiengang . '&
				semester=' . $semester . '&
				datum_von=' . $datum_von . '&
				datum_bis=' . $datum_bis . '&
				prestudent_id=' . $prestudent_id . '&
				&' . http_build_query(array('reihungstest' => $reihungstest)) . '">
		<div class="row">';
	echo '<div class="col-xs-12" id="miscfunctionscol">';
	$disabledZuteilen = true;
	$disabledTestende = true;
	$rt_id_val = '';
	if ($reihungstest != '')
	{
		$disabledTestende = false;
		if (count($reihungstest) == 1)
		{
			$rt_id_val = $reihungstest[0];
			$disabledZuteilen = false;
		}
	}
	// Button um Assistenz über Testende zu informieren
	// Nur aktiv, wenn Reihungstest ausgewählt
	if ($rechte->isBerechtigtMultipleOe('lehre/reihungstestAufsicht', $berechtigteOes, 'su'))
	{
		echo '<button '.($disabledTestende ? 'disabled' : '').' type="button" class="btn btn-primary" onclick="testende()" title="'.($disabledTestende ? 'Nur aktiv bei ausgewähltem Reihungstesttermin' : 'Informiert die Assistenz über das Testende (alle markierten)').'">Testende...</button>';
	}

	// Input um Personen hinzuzufügen
	// Nur aktiv, wenn Reihungstest ausgewählt
	if ($rechte->isBerechtigt('lehre/reihungstestAufsicht', null, 'sui'))
	{
		echo '
				<div class="input-group" id="addpers">
					<input '.($disabledZuteilen ? 'disabled' : '').' type="text" maxlength="128" id="zuteilungAutocomplete" class="form-control" placeholder="Person zuteilen" title="'.($disabledZuteilen ? 'Nur aktiv bei genau einem ausgewählten Reihungstesttermin' : '').'">
					<input type="hidden" name="prestudentToAdd" id="zuteilungAutocompleteHidden" value="">
					<input type="hidden" name="method" value="addPerson">
					<input type="hidden" name="reihungstest_id" id="zuteilungAutocompleteHidden" value="' . $rt_id_val . '">
					<span class="input-group-btn">
						<button '.($disabledZuteilen ? 'disabled' : '').' type="submit" class="btn btn-primary" name="addPersonToTestButton" value="Zuteilen" title="'.($disabledZuteilen ? 'Nur aktiv bei genau einem ausgewählten Reihungstesttermin' : '').'">
							Zuteilen
						</button>
					</span>
				</div>				
		';
	}
	if ($rechte->isBerechtigt('lehre/reihungstestAufsicht', null, 'suid'))
	{
		echo '<button type="button" class="btn btn-warning" id="toggleDelete">Löschoptionen anzeigen...</button>';
	}
	echo '<br><br>';
	echo '<button type="button" class="btn btn-primary btn-xs" onclick="sendMail()" id="mailSendButton">Mail an alle senden</button>';
	echo '<button type="button" class="btn btn-primary btn-xs" onclick="checkAllWithResult()" id="checkAllResButton">Alle mit Ergebnis markieren</button>';
	echo '<button type="button" class="btn btn-primary btn-xs" id="showUebertragenOptionsButton">Punkte ins FAS übertragen...</button>';
	if ((isset($_POST['reihungstest']) && count($_POST['reihungstest']) == 1) || (isset($_GET['reihungstest']) && $_GET['reihungstest'] != ''))
	{
		if (isset($_POST['reihungstest']))
		{
			echo '&nbsp;&nbsp;<a href="'.APP_ROOT.'/addons/reports/cis/vorschau.php?statistik_kurzbz=TesttoolFortschritt&debug=true&ReihungstestID='.$_POST['reihungstest'][0].'" class="btn btn-default btn-xs" role="button" target="_blank">Testfortschritt ansehen</a>';
		}
		else
		{
			echo '&nbsp;&nbsp;<a href="'.APP_ROOT.'/addons/reports/cis/vorschau.php?statistik_kurzbz=TesttoolFortschritt&debug=true&ReihungstestID='.$_GET['reihungstest'].'" class="btn btn-default btn-xs" role="button" target="_blank">Testfortschritt ansehen</a>';
		}
	}
	else
	{
		echo '&nbsp;&nbsp;<a href="#" class="btn btn-default btn-xs" disabled="" role="button" title="Aktiv nur bei Einzelterminauswahl">Testfortschritt ansehen</a>';
	}
	if ($rechte->isBerechtigt('infocenter', null, 'suid'))
	{
		echo '&nbsp;&nbsp;<button type="button" class="btn btn-default btn-xs" onclick="clearList()" id="clearListButton" title="Löscht alle Prüflinge, bei denen keine Daten vorhanden sind">Liste aufräumen</button>';
	}
	echo '</div></div></form>';
	echo '	<form class="form" role="form">
			<div class="panel panel-default hiddenEl" id="uebertragenOptions">
			 <div class="panel-body">
				<div class="checkbox">
				  <label><input type="checkbox" id="uebertragenOptionGesamtpunkte" value="">"Gesamtpunkte" und "Reihungsverfahren absolviert" setzen</label>
				</div>
				<div id="div_checkbox_bewerber" class="checkbox">
				  <label class="checkbox_bewerber"><input type="checkbox" id="uebertragenOptionBewerber" value="">Zu Bewerber machen</label>
				</div>
				<button type="button" class="btn btn-success" onclick="punkteUebertragen()" id="punkteUebertragenButton">Jetzt übertragen</button>
			</div>
			</div>
			</form>';

	echo '</div>
			<div class="col-lg-6 col-md-12">';
	echo '	';
	$displayWarning = false;
	$displayInfo = false;
	$frsch_rt_id = '';
	if (isset($reihungstest) && is_array($reihungstest) && count($reihungstest) == 1)
	{
		$frsch_rt_id = $reihungstest[0];
		if (isset($rtest[$frsch_rt_id]))
		{
			if ($rtest[$frsch_rt_id]->freigeschaltet === false)
			{
				$displayWarning = true;
				$displayInfo = false;
			}
			elseif ($rtest[$frsch_rt_id]->freigeschaltet === true)
			{
				$displayWarning = false;
				$displayInfo = true;
			}
		}
	}
	echo '	<div id="freischaltenWarning" class="alert alert-warning'.($displayWarning ? '' : ' hiddenEl').'">Um den Reihungstest starten zu können, muss dieser freigeschaltet werden  
				<button class="btn btn-warning" onclick="freischalten('.$frsch_rt_id.', true)">Jetzt freischalten</button>
			</div>';
	echo '	<div id="freischaltenInfo" class="alert alert-info'.($displayInfo ? '' : ' hiddenEl').'">Dieser Reihungstest ist freigeschaltet. Bitte sperren Sie ihn nach dem Test 
				<button class="btn btn-info" onclick="freischalten('.$frsch_rt_id.', false)">Jetzt sperren</button>
			</div>';
	if ($messageSuccess != '' || $messageError != '')
	{
		if ($messageSuccess != '')
		{
			$class = 'class="alert alert-success"';
			$message = $messageSuccess;
		}
		elseif ($messageError != '')
		{
			$class = 'class="alert alert-danger"';
			$message = $messageError;
		}
	}
	else
	{
		$class = 'class="alert alert-success hiddenEl"';
		$message = '';
	}
	echo '	<div id="msgbox" '.$class.'>'.$message.'</div>';
	echo ' <div class="loaderIcon center-block"></div>';
	echo '	</div>';
	echo '</div>';

	//echo '<div class="col-xs-4"></div>';
	if (isset($_REQUEST['reihungstest']) || isset($_POST['rtauswsubmit']))
	{
		echo '
		<table class="tablesorter table-bordered tablesorter-default" id="auswertung_table">
		<thead>
			<tr>
				<th rowspan="2" class="toggletblchkboxcol">
					<nobr>
						<a href="#" data-toggle="checkboxes" data-action="toggle" id="toggle_table"><img src="../../skin/images/checkbox_toggle.png" name="toggle"></a>
						<a href="#" data-toggle="checkboxes" data-action="uncheck" id="uncheck_table"><img src="../../skin/images/checkbox_uncheck.png" name="toggle"></a>
					</nobr>
				</th>
				<th rowspan="2">PreId</th>
				<th rowspan="2"><span class="glyphicon glyphicon-envelope"></span></th>
				<th rowspan="2">Ent-/Sperren</th>
				<th rowspan="2">Nachname</th>
				<th rowspan="2">Vornamen</th>
				<th rowspan="2">GebDatum</th>
				<th rowspan="2" class="smallcol">G</th>
				<th rowspan="2">ZGV</th>
				<th rowspan="2">ZGV MA</th>
				<!--<th rowspan="2">Registriert</th>-->
				<th rowspan="2">STG</th>
				<!--<th rowspan="2">Studiengang</th>-->
				<th title="Semester" rowspan="2" class="smallcol">S</th>
				<th title="Organisationsform" rowspan="2">OF</th>
				<th title="Priorität" rowspan="2" class="smallcol">Prio</th>
				<th rowspan="2">Raum</th>
				<th title="Teilgenommen" rowspan="2">TG</th>
				<th title="Reihungstest" rowspan="2" class="hiddenEl">Reihungstest</th>
				<th class="hiddenEl"></th>
				<th colspan="3">Gesamt</th>';

		foreach ($gebiet AS $gbt)
		{
			echo '<th colspan="3">' . $gbt->name . '</th>';
		}

		echo '</tr>
			<tr>
				<th class="hiddenEl"></th>
				<th><small>Punkte</small></th>
				<th><small>Punkte mit Offset</small></th>
				<th><small>Prozent (gewichtet)</small></th>';

		foreach ($gebiet AS $gbt)
		{
			echo "<th><small>Punkte</small></th>";
			echo "<th><small>Punkte mit Offset</small></th>";
			echo "<th><small>Prozent</small></th>";
		}

		echo '</tr></thead><tbody>';

		if (isset($ergb) && $ergb != '')
		{
			foreach ($ergb AS $erg)
			{
				$inaktiv = '';
				if ($erg->letzter_status == 'Abgewiesener')
				{
					$inaktiv = 'text-muted';
					$erg->prioritaet = 0;
				}
				echo "<tr id='row_".$erg->prestudent_id."'>
						<td class='textcentered ".$inaktiv."'>
							<input type='checkbox' id='checkbox_$erg->prestudent_id' class='prestudentCheckbox' name='$erg->prestudent_id'>
						</td>
						<!--<td>$erg->prestudent_id <a href=".APP_ROOT."cis/testtool/admin/auswertung_detail_prestudent.php?prestudent_id=$erg->prestudent_id target='blank'>Details</a></td>-->
						<td class='".$inaktiv."'>$erg->prestudent_id</td>
						<td class='clm_email ".$inaktiv."'><a href='mailto:$erg->email'><span class='glyphicon glyphicon-envelope'></span></a></td>
						";


				echo "<td class='textcentered ".$inaktiv ."'>
						<a href='#' id='prueflingsperren_".$erg->prestudent_id ."' class='" . ($erg->gesperrt === 't' ? "hidden" : "") ."' onclick='prueflingEntSperren(" . $erg->prestudent_id . ", \"" . $erg->vorname . " " . $erg->nachname ."\"" .", true)'>
							<span class='glyphicon glyphicon-remove'></span>
						</a>
						<a href='#' id='prueflingentsperren_".$erg->prestudent_id ."' class='" . ($erg->gesperrt !== 't' ? "hidden" : "") ."' onclick='prueflingEntSperren(" . $erg->prestudent_id . ", \"" . $erg->vorname . " " . $erg->nachname ."\"" .", false);'>
							<span class='glyphicon glyphicon-ok'></span>
						</a>
					</td>";
				echo "
						<td class='".$inaktiv."'>".$erg->nachname." ".($erg->qualifikationskurs == true ? "<span title='Qualifikationskurs' class='redcolor'>(Q)</span>" : "")."</td>
						<td class='".$inaktiv."'>$erg->vorname</td>
						<td class='".$inaktiv."'>" . $datum_obj->formatDatum($erg->gebdatum, 'd.m.Y') . "</td>
						<td class='".$inaktiv."'>$erg->geschlecht</td>
						<td class='".$inaktiv."' nowrap>" . $zgv_arr[$erg->zgv] . "</td>
						<td class='".$inaktiv."' nowrap>" . $zgvma_arr[$erg->zgvma] . "</td>
						<!--<td>$erg->registriert</td>-->
						<td class='".$inaktiv."'>$erg->stg_kurzbz</td>
						<!--<td>$erg->stg_bez</td>-->
						<td class='".$inaktiv."'>$erg->ausbildungssemester</td>
						<td class='".$inaktiv."'>$erg->orgform</td>
						<td class='".$inaktiv."'>$erg->prioritaet</td>
						<td class='".$inaktiv."'>$erg->raum</td>
						<td class='".$inaktiv."'>".($erg->teilgenommen == true ? "<span class='glyphicon glyphicon-ok'></span>" : "")."</td>
						<td class='rt_id hiddenEl'>$erg->reihungstest_id</td>";
				//<td>$erg->idnachweis</td>
				$gesamtprozent = ($erg->gesamt != '' ? number_format($erg->gesamt, 2, ',', ' ') . ' %': '');
				echo '<td class="hiddenEl">'. $gesamtprozent .'</td>';
				echo '	<td class="punkte rightaligned '.$inaktiv.'" nowrap>';
				// Punkte können nur gelöscht werden, solange "Zum Reihungstest angetreten" nicht gesetzt ist
				if ($erg->teilgenommen == false || $rechte->isBerechtigt('admin'))
				{
					echo '  <span class="punkteSpan"><b>' . ($erg->gesamtpunkte != '' ? number_format($erg->gesamtpunkte, 2, ',', ' ') : '') . '</b></span>
							<span class="deleteSpan hiddenEl">
								<a href="#" onclick="deleteAllResults(' . $erg->prestudent_id . ', \'' . $erg->vorname . ' ' . $erg->nachname . '\');">
									<span class="glyphicon glyphicon-remove darkredcolor"></span>
								</a>
							</span>';
				}
				else
				{
					echo '  <span class=""><b>' . ($erg->gesamtpunkte != '' ? number_format($erg->gesamtpunkte, 2, ',', ' ') : '') . '</b></span>';
				}
				echo '	</td>';

				echo '	<td class="col_gesamtpunkte_mit_offset rightaligned '.$inaktiv.'" nowrap>
							<b>' . ($erg->gesamtoffsetpunkte != '' ? number_format($erg->gesamtoffsetpunkte, 2, ',', ' ') : '') . '</b>
						</td>';

				echo '	<td class="col_gesamtpunkte punkte rightaligned '.$inaktiv.'" nowrap>
							<span class="erg_gesamt_visible"><b>' . $gesamtprozent . '</b></span>
							<span class="erg_gesamt hiddenEl">'.$erg->gesamt.'</span>
						</td>';

				foreach ($gebiet AS $gbt)
				{
					if (isset($erg->gebiet[$gbt->gebiet_id]))
					{
						// 0-Werte hervorheben
						$zerovalclass = '';
						if ($erg->gebiet[$gbt->gebiet_id]->punkte != '' && $erg->gebiet[$gbt->gebiet_id]->punkte == '0')
						{
							$zerovalclass = ' zerovalcolor';
						}

						echo '<td class="rightaligned ' . $zerovalclass . 'pst_' . $erg->prestudent_id . '_gbt_' . $gbt->gebiet_id . ' punkte '.$inaktiv.'" nowrap>
								';
						// Punkte können nur gelöscht werden, solange "Zum Reihungstest angetreten" nicht gesetzt ist
						if ($erg->teilgenommen == false || $rechte->isBerechtigt('admin'))
						{
							echo '  <span class="punkteSpan">' . ($erg->gebiet[$gbt->gebiet_id]->punkte != '' ? number_format($erg->gebiet[$gbt->gebiet_id]->punkte, 2, ',', ' ') : '') . '</span>
									<span class="deleteSpan hiddenEl">
										<a href="#" onclick="deleteResult(' . $erg->prestudent_id . ',' . $gbt->gebiet_id . ', \'' . $erg->vorname . ' ' . $erg->nachname . '\', \'' . $gbt->name . '\');">
											<span class="glyphicon glyphicon-remove darkyellowcolor"></span>
										</a>
									</span>';
						}
						else
						{
							echo '  <span class="">' . ($erg->gebiet[$gbt->gebiet_id]->punkte != '' ? number_format($erg->gebiet[$gbt->gebiet_id]->punkte, 2, ',', ' ') : '') . '</span>';
						}
						echo '</td>';
						echo '<td class="rightaligned ' . $zerovalclass . 'pst_' . $erg->prestudent_id . '_gbt_' . $gbt->gebiet_id . ' punkte '.$inaktiv.'" nowrap>' . ($erg->gebiet[$gbt->gebiet_id]->punktemitoffset != '' ? number_format($erg->gebiet[$gbt->gebiet_id]->punktemitoffset, 2, ',', ' ') : '') . '</td>';
						echo '<td class="rightaligned ' . $zerovalclass . 'pst_' . $erg->prestudent_id . '_gbt_' . $gbt->gebiet_id . ' punkte '.$inaktiv.'" nowrap>' . ($erg->gebiet[$gbt->gebiet_id]->prozent != '' ? number_format($erg->gebiet[$gbt->gebiet_id]->prozent, 2, ',', ' ') . ' %' : '') . '</td>';
					}
					else
					{
						echo '<td></td><td></td><td></td>';
					}
				}

				echo '</tr>';
			}
		}

		echo '</tbody></table>';
	}
}
echo '</div></body></html>';
?>
