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
	$studiensemester_kurzbz = trim((isset($_REQUEST['studiensemester_kurzbz']) ? $_REQUEST['studiensemester_kurzbz'] : ''));
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
				prestudent_id::text like '%" . $db->db_escape(mb_strtolower($search)) . "%')
				AND get_rolle_prestudent(prestudent_id, " . $db->db_add_param($studiensemester_kurzbz) . ") IN ('Interessent')
				ORDER BY nachname,vorname,stg
				LIMIT 10
			";

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
			echo json_encode(array(
				'status' => 'ok',
				'msg' => 'Reihungstest wurde freigeschaltet'));
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
	if (isset($_POST['reihungstest_id']) &&	is_numeric($_POST['reihungstest_id']))
	{
		$reihungstest = new reihungstest($_POST['reihungstest_id']);
		// Alle Bachelor-Studiengänge holen, bei denen der Bewerber Interessent ist, die Bewerbung abgeschickt hat und bestätigt wurde
		// Mail an alle diese Studiengänge senden

		if (isset($_POST['prestudent_ids']))
		{
			// Array mit allen Prestudenten aufbauen
			$prestudentArray = array();
			foreach ($_POST['prestudent_ids'] AS $prest)
			{
				$prestudentrolle = new prestudent($prest);
				$prestudentrolle->getLastStatus($prest, $reihungstest->studiensemester_kurzbz, 'Interessent');
				$stg = new studiengang($prestudentrolle->studiengang_kz);

				if ($prestudentrolle->bewerbung_abgeschicktamum != ''
					&& $prestudentrolle->bestaetigtam != ''
					&& $prestudentrolle->bestaetigtvon != ''
					&& $stg->typ == 'b')
				{
					$prestudentArray[$prestudentrolle->studiengang_kz][$prestudentrolle->orgform_kurzbz][] = $prest;
				}

				// Setzt "teilgenommen" (Zum Reihungstest angetreten) auf TRUE
				$teilgenommen = new reihungstest();
				$teilgenommen->getPersonReihungstest($prestudentrolle->person_id, $_POST['reihungstest_id']);

				$teilgenommen->new = false;
				$teilgenommen->teilgenommen = true;
				$teilgenommen->updateamum = date('Y-m-d H:i:s');
				$teilgenommen->updatevon = $user;

				if (!$teilgenommen->savePersonReihungstest())
				{
					echo json_encode(array(
						'status' => 'fehler',
						'msg' => 'Fehler beim speichern der Reihungstestteilnahme: '.$teilgenommen->errormsg
					));
					exit();
				}
			}
		}

		$sendError = false;
		$empfaengerArray = array();
		foreach ($prestudentArray AS $studiengang_kz => $OrgFormPrestudent)
		{
			foreach ($OrgFormPrestudent AS $orgForm => $prestudent_id)
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
									$mailtext .= 'Der Reihungstest vom ' . $datum_obj->convertISODate($reihungstest->datum) . ' um ' . $datum_obj->formatDatum($reihungstest->uhrzeit, 'H:i') . ' Uhr ist beendet.';
									$mailtext .= '<br> Es haben <b>'.$anzahl.'</b> Person(en) aus dem Studiengang '.$stg->kuerzel.'-'.$orgForm.' teilgenommen';
									$mailtext .= '<br> Sie finden die Auswertung unter dem folgendem Link:';
									$mailtext .= '<br><br><a href="' . APP_ROOT . 'vilesci/stammdaten/auswertung_fhtw.php?reihungstest=' . $reihungstest->reihungstest_id . '&studiengang=' . $studiengang_kz . '&orgform_kurzbz=' . $orgForm . '">Link zur Auswertung</a>';

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

				$mail = new mail($empfaenger, 'no-reply', 'Reihungstest vom '.$datum_obj->convertISODate($reihungstest->datum).' um '.$datum_obj->formatDatum($reihungstest->uhrzeit, 'H:i').' beendet', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
				$mail->setHTMLContent($mailtext);
				$mail->addEmbeddedImage(APP_ROOT . 'skin/images/sancho/sancho_header_min_bw.jpg', 'image/jpg', 'header_image', 'sancho_header');
				$mail->addEmbeddedImage(APP_ROOT . 'skin/images/sancho/sancho_footer_min_bw.jpg', 'image/jpg', 'footer_image', 'sancho_footer');
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
				'msg' => 'Nachricht erfolgreich verschickt an: ' . implode(',',$empfaengerArray)));
			exit();
		}
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
				$break;
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
	if (isset($_POST['reihungstest_id']) &&	is_numeric($_POST['reihungstest_id']))
	{
		$reihungstest = new reihungstest($_POST['reihungstest_id']);
		$msg_warning = '';
		$msg_error = '';
		$count_success_punkte = 0;
		$count_success_gesamtpunkte = 0;
		$count_success_bewerber = 0;

		if (isset($_POST['prestudentPunkteArr']))
		{
			foreach ($_POST['prestudentPunkteArr'] AS $key => $array)
			{
				$prestudentrolle = new prestudent($array['prestudent_id']);
				$prestudentrolle->getLastStatus($array['prestudent_id'], null, 'Interessent');

				if (!$rechte->isBerechtigt('lehre/reihungstest', $prestudentrolle->studiengang_kz, 'sui'))
				{
					$msg_error .= '<br>Sie haben keine Rechte, um für diesen Studiengang Ergebnisse ins FAS zu übertragen';
					continue;
				}
				// Checken, ob Person-Reihungstest-Studienplan zuteilung existiert
				if ($reihungstest->checkPersonRtStudienplanExists($prestudentrolle->person_id, $_POST['reihungstest_id'], $prestudentrolle->studienplan_id))
				{
					$setRTPunkte = new reihungstest();
					$setRTPunkte->getPersonReihungstest($prestudentrolle->person_id, $_POST['reihungstest_id'], $prestudentrolle->studienplan_id);

					// Check, ob Punkte schon befüllt sind
					if ($setRTPunkte->punkte == '')
					{
						$setRTPunkte->new = false;
						$setRTPunkte->punkte = number_format($array['ergebnis'], 4);
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
						$msg_warning .= '<br>Der Prestudent '.$array['prestudent_id'].' hat bereits Punkte eingetragen.';
					}
				}
				else
				{
					$setRTPunkte = new reihungstest();
					$setRTPunkte->getPersonReihungstest($prestudentrolle->person_id, $_POST['reihungstest_id']);

					// Check, ob Punkte schon befüllt sind
					if ($setRTPunkte->punkte == '')
					{
						$setRTPunkte->new = true;
						$setRTPunkte->studienplan_id = $prestudentrolle->studienplan_id;
						$setRTPunkte->punkte = number_format($array['ergebnis'], 4);
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
						$msg_warning .= '<br>Der Prestudent '.$array['prestudent_id'].' hat bereits Punkte eingetragen.';
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
						$prestudent->punkte = number_format($array['ergebnis'], 4);
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

$ergebnis = '';
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

if ($reihungstest != '' && is_numeric($reihungstest))
{
	$reihungstestObj = new reihungstest($reihungstest);
	$rtStudiensemester = $reihungstestObj->studiensemester_kurzbz;
}
elseif ($reihungstest != '' && !is_numeric($reihungstest))
{
	die('ReihungstestID ist ungueltig');
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
if (($reihungstest == '' && isset($_REQUEST['reihungstest'])) && $studiengang == '' && $semester == '' && $prestudent_id == '' && $datum_von == '' && $datum_bis == '')
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
	$sql_query .= "UNION SELECT * FROM public.tbl_reihungstest WHERE reihungstest_id=" . $db->db_add_param($reihungstest, FHC_INTEGER);
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

if (isset($_REQUEST['reihungstest']))
{
	// Vorkommende Gebiete laden
	$query = "
		SELECT DISTINCT 
			tbl_gebiet.gebiet_id,
			tbl_gebiet.bezeichnung AS gebiet,
			tbl_ablauf.reihung,
			tbl_ablauf.studiengang_kz,
			tbl_ablauf.semester
		FROM PUBLIC.tbl_rt_person
		JOIN PUBLIC.tbl_person ON (tbl_rt_person.person_id = tbl_person.person_id)
		JOIN PUBLIC.tbl_prestudent ps ON (ps.person_id = tbl_rt_person.person_id)
		JOIN PUBLIC.tbl_reihungstest rt ON (tbl_rt_person.rt_id = rt.reihungstest_id)
		JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
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
		$query .= " AND rt_id = " . $db->db_add_param($reihungstest, FHC_INTEGER);
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
								)
							AND testtool.tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
						)
				END AS punkte,
			tbl_gebiet.gebiet_id,
			tbl_gebiet.bezeichnung AS gebiet,
			tbl_pruefling.idnachweis,
			tbl_pruefling.registriert,
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
			AND bewerbung_abgeschicktamum IS NOT NULL
			AND bestaetigtam IS NOT NULL
			AND tbl_gebiet.gebiet_id != 7
		";
	if ($reihungstest != '')
	{
		$query .= " AND rt_id = " . $db->db_add_param($reihungstest, FHC_INTEGER);
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
	if ($orgform_kurzbz != '')
	{
		$query .= " AND tbl_studienplan.orgform_kurzbz=" . $db->db_add_param($orgform_kurzbz);
	}
	//$query .= " AND nachname='Al-Mafrachi'";
	$query .= " ORDER BY nachname,
				vorname,
				person_id	
	";//var_dump($query);
	if (!($result = $db->db_query($query)))
	{
		die($db->db_last_error());
	}

	$gebiete_arr = array();
	while ($row = $db->db_fetch_object($result))
	{
		if (!isset($ergebnis[$row->prestudent_id]))
		{
			$ergebnis[$row->prestudent_id] = new stdClass();
			$gebiete_arr[$row->prestudent_id] = array();
		}

		$ergebnis[$row->prestudent_id]->prestudent_id = $row->prestudent_id;
		$ergebnis[$row->prestudent_id]->person_id = $row->person_id;
		//$ergebnis[$row->prestudent_id]->pruefling_id = $row->pruefling_id;
		$ergebnis[$row->prestudent_id]->nachname = $row->nachname;
		$ergebnis[$row->prestudent_id]->vorname = $row->vorname;
		$ergebnis[$row->prestudent_id]->gebdatum = $row->gebdatum;
		$ergebnis[$row->prestudent_id]->email = $row->email;
		$ergebnis[$row->prestudent_id]->geschlecht = $row->geschlecht;
		$ergebnis[$row->prestudent_id]->idnachweis = $row->idnachweis;
		$ergebnis[$row->prestudent_id]->registriert = $row->registriert;
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
		$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->punkte = (($row->punkte >= $row->maxpunkte) ? $row->maxpunkte : $row->punkte);
		if ($row->punkte == 0 && $row->punkte != '')
		{
			$prozent = '0';
		}
		elseif ($row->punkte >= $row->maxpunkte) //wenn maxpunkte ueberschritten wurde -> 100%
		{
			$prozent = 100;
		}
		else
		{
			$prozent = ($row->punkte / $row->maxpunkte) * 100;
		}

		if ($row->punkte >= $row->maxpunkte)
		{
			$punkte = $row->maxpunkte;
		}
		else
		{
			$punkte = $row->punkte;
		}

		$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->prozent = $prozent;
		$ergebnis[$row->prestudent_id]->gebiet[$row->gebiet_id]->punkte = $punkte;

		// Bei Auswertungen ohne rt_id kann es vorkommen, dass Datensätze Doppelt sind
		// Bei der Summe darf ein Gebiet jedenfalls nur einmal summiert werden

		if (!in_array($row->gebiet_id, $gebiete_arr[$row->prestudent_id]))
		{
			$gebiete_arr[$row->prestudent_id][] = $row->gebiet_id;

			// Gesamtpunkte mit Physik
			if (isset($ergebnis[$row->prestudent_id]->gesamt))
			{
				$ergebnis[$row->prestudent_id]->gesamt += $prozent * $row->gewicht;
			}
			else
			{
				$ergebnis[$row->prestudent_id]->gesamt = $prozent * $row->gewicht;
			}

			if (isset($ergebnis[$row->prestudent_id]->gesamtpunkte))
			{
				$ergebnis[$row->prestudent_id]->gesamtpunkte += $punkte;
			}
			else
			{
				$ergebnis[$row->prestudent_id]->gesamtpunkte = $punkte;
			}

			// Gesamtpunkte ohne Physik
			if ($row->gebiet_id != 10)
			{
				if (isset($ergebnis[$row->prestudent_id]->gesamt_ohne_physik))
				{
					$ergebnis[$row->prestudent_id]->gesamt_ohne_physik += $prozent * $row->gewicht;
				}
				else
				{
					$ergebnis[$row->prestudent_id]->gesamt_ohne_physik = $prozent * $row->gewicht;
				}

				if (isset($ergebnis[$row->prestudent_id]->gesamtpunkte_ohne_physik))
				{
					$ergebnis[$row->prestudent_id]->gesamtpunkte_ohne_physik += $punkte;
				}
				else
				{
					$ergebnis[$row->prestudent_id]->gesamtpunkte_ohne_physik = $punkte;
				}
			}
		}
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
	$workbook->send("Auswertung " . ((isset ($_REQUEST['reihungstest']) && $_REQUEST['reihungstest'] != '') ? $stg_arr[$rtest[$reihungstest]->studiengang_kz] . " " . $datum_obj->formatDatum($rtest[$reihungstest]->datum, 'd.m.Y') : 'aller Reihungstests') . ".xls");
	$workbook->setVersion(8);
	$workbook->setCustomColor(15, 192, 192, 192); //Setzen der HG-Farbe Hellgrau
	$workbook->setCustomColor(22, 193, 0, 0); //Setzen der HG-Farbe Dunkelrot
	// Creating a worksheet
	$titel_studiengang = (isset ($_REQUEST['studiengang']) && $_REQUEST['studiengang'] != '');
	$titel_semester = (isset ($_REQUEST['semester']) && $_REQUEST['semester'] != '');

	// Eigener TItel bei Bachelor-Studiengängen
	if (isset($studiengangObj) && $studiengangObj->typ == 'b')
	{
		$worksheet =& $workbook->addWorksheet("Auswertung MIT Physik " . ($titel_studiengang ? $stg_arr[$_REQUEST['studiengang']] : '') . ($titel_semester ? ' ' . $semester . '.Semester' : ''));
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

	$spalte = 11;
	$zeile = 0;

	foreach ($gebiet AS $gbt)
	{
		++$spalte;
		$worksheet->write($zeile, ++$spalte, strip_tags($gbt->name), $format_bold_border);
		$worksheet->mergeCells($zeile, $spalte, 0, $spalte + 1);
		$maxlength[$spalte] = 10;
	}
	$worksheet->write($zeile, ++$spalte + 1, 'Gesamt', $format_bold_border);
	$worksheet->mergeCells($zeile, ++$spalte, 0, $spalte + 1);
	$maxlength[$spalte] = 12;

	$spalte = 12;
	$zeile = 0;

	foreach ($gebiet AS $gbt)
	{
		$worksheet->write($zeile + 1, ++$spalte, 'Punkte', $format_bold_border);
		$worksheet->write($zeile + 1, ++$spalte, 'Prozent', $format_bold_border);
		$maxlength[$spalte] = 10;
	}
	$worksheet->write($zeile + 1, ++$spalte, 'Punkte', $format_bold_border);
	$worksheet->write($zeile + 1, ++$spalte, 'Prozent', $format_bold_border);
	$maxlength[$spalte] = 10;

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
				}
			}
			$worksheet->writeNumber($zeile, ++$spalte, $erg->gesamtpunkte, $format_punkte);
			$worksheet->writeNumber($zeile, ++$spalte, $erg->gesamt, $format_punkte);
		}
	}

	//Die Breite der Spalten setzen
	foreach ($maxlength as $i => $breite)
	{
		$worksheet->setColumn($i, $i, $breite);
	}

	// Worksheet ohne Physik nur für Bachelor-Studiengänge
	if (isset($studiengangObj) && $studiengangObj->typ == 'b')
	{
		$worksheetOhnePhsyik =& $workbook->addWorksheet("Auswertung OHNE Physik " . ($titel_studiengang ? $stg_arr[$_REQUEST['studiengang']] : '') . ($titel_semester ? ' ' . $semester . '.Semester' : ''));
		$worksheetOhnePhsyik->setInputEncoding('utf-8');
		$worksheetOhnePhsyik->setZoom(85);

		$spalte = 0;
		$zeile = 0;

		$worksheetOhnePhsyik->write(0, $spalte, 'PrestudentIn_ID', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 0, 1, 0);
		$maxlength[0] = 15;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'Nachname', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 1, 1, 1);
		$maxlength[1] = 15;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'Vorname', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 2, 1, 2);
		$maxlength[2] = 15;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'GebDatum', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 3, 1, 3);
		$maxlength[3] = 10;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'G', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 4, 1, 4);
		$maxlength[4] = 2;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'Registriert', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 5, 1, 5);
		$maxlength[5] = 18;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'STG', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 6, 1, 6);
		$maxlength[6] = 4;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'Studiengang', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 7, 1, 7);
		$maxlength[7] = 25;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'S', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 8, 1, 8);
		$maxlength[8] = 2;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'OrgForm', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 9, 1, 9);
		$maxlength[9] = 8;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'Prio', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 10, 1, 10);
		$maxlength[10] = 5;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'ZGV', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 11, 1, 11);
		$maxlength[11] = 20;
		$worksheetOhnePhsyik->write(0, ++$spalte, 'ZGV MA', $format_bold);
		$worksheetOhnePhsyik->mergeCells(0, 12, 1, 12);
		$maxlength[12] = 20;

		$spalte = 11;
		$zeile = 0;

		foreach ($gebiet AS $gbt)
		{
			if ($gbt->gebiet_id == 10)
			{
				continue;
			}
			++$spalte;
			$worksheetOhnePhsyik->write($zeile, ++$spalte, strip_tags($gbt->name), $format_bold_border);
			$worksheetOhnePhsyik->mergeCells($zeile, $spalte, 0, $spalte + 1);
			$maxlength[$spalte] = 10;
		}
		$worksheetOhnePhsyik->write($zeile, ++$spalte + 1, 'Gesamt', $format_bold_border);
		$worksheetOhnePhsyik->mergeCells($zeile, ++$spalte, 0, $spalte + 1);
		$maxlength[$spalte] = 12;

		$spalte = 12;
		$zeile = 0;

		foreach ($gebiet AS $gbt)
		{
			if ($gbt->gebiet_id == 10)
			{
				continue;
			}
			$worksheetOhnePhsyik->write($zeile + 1, ++$spalte, 'Punkte', $format_bold_border);
			$worksheetOhnePhsyik->write($zeile + 1, ++$spalte, 'Prozent', $format_bold_border);
			$maxlength[$spalte] = 10;
		}
		$worksheetOhnePhsyik->write($zeile + 1, ++$spalte, 'Punkte', $format_bold_border);
		$worksheetOhnePhsyik->write($zeile + 1, ++$spalte, 'Prozent', $format_bold_border);
		$maxlength[$spalte] = 10;

		$maxspalten = $spalte;

		$zeile = 1;
		$spalte = 0;

		if (isset($ergb))
		{
			foreach ($ergb AS $erg)
			{
				$zeile++;
				$spalte = 0;
				$worksheetOhnePhsyik->write($zeile, $spalte, $erg->prestudent_id);
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->nachname);
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->vorname);
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->gebdatum, $format_date);
				if ($erg->geschlecht == 'm')
				{
					$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->geschlecht, $format_male);
				}
				else
				{
					$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->geschlecht, $format_female);
				}
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->registriert, $format_registriert);
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->stg_kurzbz);
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->stg_bez);
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->ausbildungssemester);
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->orgform);
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $erg->prioritaet);
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $zgv_arr[$erg->zgv]);
				$worksheetOhnePhsyik->write($zeile, ++$spalte, $zgvma_arr[$erg->zgvma]);
				foreach ($gebiet AS $gbt)
				{
					if ($gbt->gebiet_id == 10)
					{
						continue;
					}
					if (isset($erg->gebiet[$gbt->gebiet_id]))
					{
						if ($erg->gebiet[$gbt->gebiet_id]->punkte != '' && $erg->gebiet[$gbt->gebiet_id]->punkte != '0')
						{
							$worksheetOhnePhsyik->writeNumber($zeile, ++$spalte, $erg->gebiet[$gbt->gebiet_id]->punkte, $format_punkte);
						}
						else
						{
							$worksheetOhnePhsyik->writeNumber($zeile, ++$spalte, $erg->gebiet[$gbt->gebiet_id]->punkte, $format_punkte_rot);
						}
						if ($erg->gebiet[$gbt->gebiet_id]->prozent != '0%')
						{
							$worksheetOhnePhsyik->writeNumber($zeile, ++$spalte, $erg->gebiet[$gbt->gebiet_id]->prozent / 100, $format_prozent);
						}
						else
						{
							$worksheetOhnePhsyik->writeNumber($zeile, ++$spalte, $erg->gebiet[$gbt->gebiet_id]->prozent / 100, $format_prozent_rot);
						}
					}
					else
					{
						$worksheetOhnePhsyik->write($zeile, ++$spalte, '');
						$worksheetOhnePhsyik->write($zeile, ++$spalte, '');
					}
				}
				$worksheetOhnePhsyik->writeNumber($zeile, ++$spalte, $erg->gesamtpunkte_ohne_physik, $format_punkte);
				$worksheetOhnePhsyik->writeNumber($zeile, ++$spalte, $erg->gesamt_ohne_physik, $format_punkte);
			}
		}

		//Die Breite der Spalten setzen
		foreach ($maxlength as $i => $breite)
		{
			$worksheetOhnePhsyik->setColumn($i, $i, $breite);
		}
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
	<link rel="stylesheet" type="text/css" href="../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
	<script type="text/javascript" src="../../vendor/components/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/ui/i18n/datepicker-de.js"></script>
	<script type="text/javascript" src="../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.widgets.min.js"></script>
	<script type="text/javascript" src="../../vendor/mottie/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js"></script>
	<script type="text/javascript" src="../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
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
		$( ".datepicker_datum" ).datepicker({
				 changeMonth: true,
				 changeYear: true,
				 dateFormat: "dd.mm.yy",
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
		
		$("#zuteilungAutocomplete").autocomplete({
			source: "auswertung_fhtw.php?autocomplete=prestudentAdd&studiensemester_kurzbz='.$rtStudiensemester.'",
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
			sortList: [[16,1],[3,0],[4,0]],
			headers: {0: { sorter: false, filter: false}, 2: { sorter: false, filter: false}, 4: { dateFormat: "ddmmyyyy" }}
			/*widgetOptions : {
				columnSelector_container : $("#columnSelector"),
				columnSelector_saveColumns: true}			*/
		});
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
						$("#row_"+prestudent_id).find("td.punkte, td.col_gesamtpunkte_ohne_physik").each (function() 
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
	
	function testende(reihungstest)
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
				$("input.prestudentCheckbox:checked").each(function() 
				{
					selected.push($(this).attr("name"));
				});
				
				$(".loaderIcon").show();
				
				data = {
					reihungstest_id: reihungstest,
					prestudent_ids: selected,
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
	function checkAllWithResult()
	{
		// Schleife ueber die einzelnen Elemente
		$(".col_gesamtpunkte_ohne_physik").each(function()
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
	function punkteUebertragen(reihungstest)
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
			//if (confirm("Setzt bei allen markierten Personen \'Zum Reihungstest angetreten\' und informiert die entsprechende Studiengangsassistenz. Wollen Sie fortfahren?"))
			{
				$("input.prestudentCheckbox:checked").each(function() 
				{
					if ($("#uebertragenOptionPhysik:checked").length === 1)
					{
						prestudentPunkteArr.push({
				 		    prestudent_id: $(this).attr("name"), 
							ergebnis:  $(this).parents("tr").find(".erg_gesamt_mit_physik").text()
				 		});
					}
					else
					{
						prestudentPunkteArr.push({
				 		    prestudent_id: $(this).attr("name"), 
							ergebnis:  $(this).parents("tr").find(".erg_gesamt_ohne_physik").text()
				 		});
					}
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
					reihungstest_id: reihungstest,
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
						if(data["msg_warning"] !== "")
						{
							$("#msgbox").attr("class","alert alert-warning");
							$(".loaderIcon").hide();
							$("#msgbox").show();
							$("#msgbox").append(data["msg_warning"]);
							//$("#msgbox").html(data["msg"]).delay(2000).fadeOut();
						}
						if(data["msg_error"] !== "")
						{
							$("#msgbox").attr("class","alert alert-danger");
							$(".loaderIcon").hide();
							$("#msgbox").show();
							$("#msgbox").append(data["msg_error"]);
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
	}
	  
	</script>
	<style>
	.info
	{
		color: #0c5460;
		background-color: #d1ecf1;
		padding: .75rem 1.25rem;
		border: 1px solid #bee5eb;;
	}
	.warning
	{
		color: #856404;
		background-color: #fff3cd;
		padding: .75rem 1.25rem;
		border: 1px solid #ffeeba;
	}
	.error
	{
		color: #721c24;
		background-color: #f8d7da;
		padding: .75rem 1.25rem;
		border: 1px solid #f5c6cb;
	}
	.loaderIcon 
	{
		border: 8px solid #f3f3f3; /* Light grey */
		border-top: 8px solid #3498db; /* Blue */
		border-radius: 50%;
		width: 30px;
		height: 30px;
		animation: spin 2s linear infinite;
		margin-right: auto;
		margin-left: auto;
	}
	@keyframes spin 
	{
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}
	.alert > .btn
	{
		padding: 0 6px;
	}
	/*** Bootstrap popover ***/
	#popover-target label 
	{
		margin: 0 5px;
		display: block;
	}
	#popover-target input 
	{
		margin-right: 5px;
	}
	#popover-target .disabled 
	{
		color: #ddd;
	}
	.glyphicon-remove
	{
		font-size: 150%;
		margin: -5px 0;
		top: 4px;
	}
	.ui-autocomplete-loading 
	{
		background: white url("../../skin/images/spinner.gif") right 5px center no-repeat;
	}
	</style>
	</head>

	<body>
	<div class="container-fluid">
	<h3>Auswertung Reihungstest</h3>
	<div class="row">
	<div class="col-md-6">
			<form method="POST">
			<table><tr><td>
			<table class="table table-bordered" style="margin-bottom: 0">
			<tr>
			<td style="white-space:nowrap; padding: 10px;">
				Reihungstest wählen:
					<SELECT id="reihungstest" name="reihungstest" style="width: 60%">
					<OPTION value="">-- keine Auswahl --</OPTION>';
	$selected = '';
	$select = false;
	foreach ($rtest as $rt)
	{
		if ($rt->reihungstest_id == $reihungstest && !$select)
		{
			$selected = 'selected';
			$select = true;
		}
		elseif ($prestudent_id == '' && $reihungstest == '' && $rt->datum == date('Y-m-d') && $datum_von == '' && $datum_bis == '' && $studiengang == '' && $semester == '' && !$select)
		{
			$selected = 'selected';
			$select = true;
		}
		else
		{
			$selected = '';
		}

		echo '<OPTION value="' . $rt->reihungstest_id . '" ' . $selected . '>' . $rt->datum . ' ' . $datum_obj->formatDatum($rt->uhrzeit,'H:i') . ' ' . (isset($stg_arr[$rt->studiengang_kz]) ? $stg_arr[$rt->studiengang_kz] : '') . ' ' . $rt->ort_kurzbz . ' ' . $rt->anmerkung . "</OPTION>\n";
	}

	echo '</SELECT></td></tr>
			<tr><td>
				Studiengang:
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
	echo '</SELECT>
				Semester:
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
	echo '</SELECT>
				OrgForm:
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
	echo '</SELECT>';

	echo 'von Datum: <INPUT class="datepicker_datum" type="text" name="datum_von" maxlength="10" size="10" value="' . $datum_obj->formatDatum($datum_von, 'd.m.Y') . '" />&nbsp;';
	echo 'bis Datum: <INPUT class="datepicker_datum" type="text" name="datum_bis" maxlength="10" size="10" value="' . $datum_obj->formatDatum($datum_bis, 'd.m.Y') . '" />';
	echo '</td></tr>';
	echo '<tr><td>';
	echo 'PrestudentIn: <INPUT id="prestudent" type="text" name="prestudent_id" size="50" value="' . $prestudent_id . '" placeholder="Name, UID oder Prestudent_id eingeben" onInput="document.getElementById(\'reihungstest\').value=\'\'" onkeyup="document.getElementById(\'prestudent_id\').value=this.value"/><input type="hidden" id="prestudent_id" name="prestudent_id" value="' . $prestudent_id . '" />';
	echo '</td></tr>
			</table></td><td style="vertical-align: middle; padding: 0 5px; border-right: 1px solid #ddd; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd">';
	echo '<INPUT type="submit" class="btn btn-primary" value="Anzeigen" id="auswertenButton"/><br><br>';
	echo '<a href="auswertung_fhtw.php?studiengang=' . $studiengang . '
										&semester=' . $semester . '
										&datum_von=' . $datum_von . '
										&datum_bis=' . $datum_bis . '
										&prestudent_id=' . $prestudent_id . '
										&reihungstest=' . $reihungstest . '
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
				reihungstest=' . $reihungstest . '">
		<div class="row" style="">';
	echo '<div class="col-xs-12">';
	if ($reihungstest != '')
	{
		$disabled = false;
	}
	else
	{
		$disabled = true;
	}
	// Button um Assistenz über Testende zu informieren
	// Nur aktiv, wenn Reihungstest ausgewählt
	if ($rechte->isBerechtigtMultipleOe('lehre/reihungstestAufsicht', $berechtigteOes, 'su'))
	{
		echo '<button '.($disabled ? 'disabled' : '').' type="button" class="btn btn-primary" onclick="testende('.$reihungstest.')" title="'.($disabled ? 'Nur aktiv bei ausgewähltem Reihungstesttermin' : 'Informiert die Assistenz über das Testende (alle markierten)').'">Testende...</button>';
	}

	// Input um Personen hinzuzufügen
	// Nur aktiv, wenn Reihungstest ausgewählt
	if ($rechte->isBerechtigt('lehre/reihungstestAufsicht', null, 'sui'))
	{
		echo '
				<div class="input-group" style="margin-left: 10px">
					<input '.($disabled ? 'disabled' : '').' type="text" maxlength="128" id="zuteilungAutocomplete" class="form-control" placeholder="Person zuteilen" title="'.($disabled ? 'Nur aktiv bei ausgewähltem Reihungstesttermin' : '').'">
					<input type="hidden" name="prestudentToAdd" id="zuteilungAutocompleteHidden" value="">
					<input type="hidden" name="method" value="addPerson">
					<input type="hidden" name="reihungstest_id" id="zuteilungAutocompleteHidden" value="' . $reihungstest . '">
					<span class="input-group-btn">
						<button '.($disabled ? 'disabled' : '').' type="submit" class="btn btn-primary" name="addPersonToTestButton" value="Zuteilen" title="'.($disabled ? 'Nur aktiv bei ausgewähltem Reihungstesttermin' : '').'">
							Zuteilen
						</button>
					</span>
				</div>				
		';
	}
	if ($rechte->isBerechtigt('lehre/reihungstestAufsicht', null, 'suid'))
	{
		echo '<button type="button" style="margin-left: 10px" class="btn btn-warning" id="toggleDelete">Löschoptionen anzeigen...</button>';
	}
	echo '<br><br>';
	echo '<button type="button" class="btn btn-primary btn-xs" onclick="sendMail()" id="mailSendButton">Mail an alle senden</button>';
	echo '<button type="button" class="btn btn-primary btn-xs" onclick="checkAllWithResult()" id="" style="margin-left: 10px">Alle mit Ergebnis markieren</button>';
	echo '<button type="button" class="btn btn-primary btn-xs" id="showUebertragenOptionsButton" style="margin-left: 10px">Punkte ins FAS übertragen...</button>';
	echo '</div></div></form>';
	echo '	<form class="form" role="form">
			<div class="panel panel-default" id="uebertragenOptions" style="display: none">
			 <div class="panel-body">
				 <div class="checkbox">
				  <label><input type="checkbox" id="uebertragenOptionPhysik" value="">Mit Physik</label>
				</div>
				<div class="checkbox">
				  <label><input type="checkbox" id="uebertragenOptionGesamtpunkte" value="">"Gesamtpunkte" und "Reihungsverfahren absolviert" setzen</label>
				</div>
				<div id="div_checkbox_bewerber" class="checkbox">
				  <label class="checkbox_bewerber"><input type="checkbox" id="uebertragenOptionBewerber" value="">Zu Bewerber machen</label>
				</div>
				<button type="button" class="btn btn-success" onclick="punkteUebertragen('.$reihungstest.')" id="punkteUebertragenButton" style="margin-left: 10px">Jetzt übertragen</button>
			</div>
			</div>
			</form>';

	echo '</div>
			<div class="col-md-6">';
	echo '	';
	$displayWarning = false;
	$displayInfo = false;
	if (isset($rtest[$reihungstest]) && $rtest[$reihungstest]->freigeschaltet === false)
	{
		$displayWarning = true;
		$displayInfo = false;
	}
	elseif (isset($rtest[$reihungstest]) && $rtest[$reihungstest]->freigeschaltet === true)
	{
		$displayWarning = false;
		$displayInfo = true;
	}
	echo '	<div id="freischaltenWarning" class="alert alert-warning" style="display: '.($displayWarning ? 'block' : 'none').'">Um den Reihungstest starten zu können, muss dieser freigeschaltet werden  
				<button class="btn btn-warning" onclick="freischalten('.$reihungstest.', true)">Jetzt freischalten</button>
			</div>';
	echo '	<div id="freischaltenInfo" class="alert alert-info" style="display: '.($displayInfo ? 'block' : 'none').'">Dieser Reihungstest ist freigeschaltet. Bitte sperren Sie ihn nach dem Test 
				<button class="btn btn-info" onclick="freischalten('.$reihungstest.', false)">Jetzt sperren</button>
			</div>';
	if ($messageSuccess != '' || $messageError != '')
	{
		$display = '';
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
		$display = 'style="display: none"';
		$class = 'class="alert alert-success"';
		$message = '';
	}
	echo '	<div id="msgbox" '.$class.' '.$display.'>'.$message.'</div>';
	echo ' <div class="loaderIcon center-block" style="display: none; margin-top: 10px"></div>';
	echo '	</div>';
	echo '</div>';

	//echo '<div class="col-xs-4"></div>';
	if (isset($_REQUEST['reihungstest']))
	{
		echo '
		<table class="tablesorter table-bordered tablesorter-default" id="auswertung_table">
		<thead>
			<tr>
				<th rowspan="2" style="width: 30px">
					<nobr>
						<a href="#" data-toggle="checkboxes" data-action="toggle" id="toggle_table"><img src="../../skin/images/checkbox_toggle.png" name="toggle"></a>
						<a href="#" data-toggle="checkboxes" data-action="uncheck" id="uncheck_table"><img src="../../skin/images/checkbox_uncheck.png" name="toggle"></a>
					</nobr>
				</th>
				<th rowspan="2">PreId</th>
				<th rowspan="2"><span class="glyphicon glyphicon-envelope"></span></th>
				<th rowspan="2">Nachname</th>
				<th rowspan="2">Vornamen</th>
				<th rowspan="2">GebDatum</th>
				<th rowspan="2" style="width: 20px">G</th>
				<th rowspan="2">ZGV</th>
				<th rowspan="2">ZGV MA</th>
				<!--<th rowspan="2">Registriert</th>-->
				<th rowspan="2">STG</th>
				<!--<th rowspan="2">Studiengang</th>-->
				<th title="Semester" rowspan="2" style="width: 20px">S</th>
				<th title="Organisationsform" rowspan="2">OF</th>
				<th title="Priorität" rowspan="2" style="width: 20px">Prio</th>
				<th rowspan="2">Raum</th>
				<th title="Teilgenommen" rowspan="2">TG</th>
				<th colspan="2">Gesamt mit Physik</th>
				<th colspan="2">Gesamt ohne Physik</th>';

		foreach ($gebiet AS $gbt)
		{
			echo '<th colspan="2">' . $gbt->name . '</th>';
		}

		echo '</tr>
			<tr>
				<th><small>Punkte</small></th>
				<th><small>Prozent</small></th>
				<th><small>Punkte</small></th>
				<th><small>Prozent</small></th>';

		foreach ($gebiet AS $gbt)
		{
			echo "<th><small>Punkte</small></th>";
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
				}
				echo "<tr id='row_".$erg->prestudent_id."'>
						<td style='text-align: center' class='".$inaktiv."'>
							<input type='checkbox' id='checkbox_$erg->prestudent_id' class='prestudentCheckbox' name='$erg->prestudent_id'>
						</td>
						<!--<td>$erg->prestudent_id <a href=".APP_ROOT."cis/testtool/admin/auswertung_detail_prestudent.php?prestudent_id=$erg->prestudent_id target='blank'>Details</a></td>-->
						<td class='".$inaktiv."'>$erg->prestudent_id</td>
						<td class='clm_email ".$inaktiv."'><a href='mailto:$erg->email'><span class='glyphicon glyphicon-envelope'></span></a></td>
						<td class='".$inaktiv."'>".$erg->nachname." ".($erg->qualifikationskurs == true ? "<span title='Qualifikationskurs' style='color: red'>(Q)</span>" : "")."</td>
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
						<td class='".$inaktiv."'>".($erg->teilgenommen == true ? "<span class='glyphicon glyphicon-ok'></span>" : "")."</td>";
				//<td>$erg->idnachweis</td>
				echo '	<td style="text-align: right; padding-right: 3px" class="punkte '.$inaktiv.'" nowrap>';
				// Punkte können nur gelöscht werden, solange "Zum Reihungstest angetreten" nicht gesetzt ist
				if ($erg->teilgenommen == false || $rechte->isBerechtigt('admin'))
				{
					echo '  <span class="punkteSpan"><b>' . ($erg->gesamtpunkte != '' ? number_format($erg->gesamtpunkte, 2, ',', ' ') : '') . '</b></span>
							<span class="deleteSpan"  style="display: none">
								<a href="#" onclick="deleteAllResults(' . $erg->prestudent_id . ', \'' . $erg->vorname . ' ' . $erg->nachname . '\');">
									<span class="glyphicon glyphicon-remove" style="color: #c82333;"></span>
								</a>
							</span>';
				}
				else
				{
					echo '  <span class=""><b>' . ($erg->gesamtpunkte != '' ? number_format($erg->gesamtpunkte, 2, ',', ' ') : '') . '</b></span>';
				}
				echo '	</td>';
				if (!isset($erg->gesamtpunkte_ohne_physik))
				{
					$erg->gesamtpunkte_ohne_physik = '';
				}
				if (!isset($erg->gesamt_ohne_physik))
				{
					$erg->gesamt_ohne_physik = '';
				}
				echo '	<td style="text-align: right; padding-right: 3px" class="punkte '.$inaktiv.'" nowrap>
							<b>' . ($erg->gesamt != '' ? number_format($erg->gesamt, 2, ',', ' ') : '') . '</b>
							<span class="erg_gesamt_mit_physik" style="display: none">'.$erg->gesamt.'</span>
						</td>';
				echo '	<td style="text-align: right; padding-right: 3px" class="col_gesamtpunkte_ohne_physik '.$inaktiv.'" nowrap>
							<b>' . ($erg->gesamtpunkte_ohne_physik != '' ? number_format($erg->gesamtpunkte_ohne_physik, 2, ',', ' ') : '') . '</b>
						</td>';
				echo '	<td style="text-align: right; padding-right: 3px" class="punkte '.$inaktiv.'" nowrap>
							<b>' . ($erg->gesamt_ohne_physik != '' ? number_format($erg->gesamt_ohne_physik, 2, ',', ' ') : '') . '</b>
							<span class="erg_gesamt_ohne_physik" style="display: none">'.$erg->gesamt_ohne_physik.'</span>
						</td>';
				foreach ($gebiet AS $gbt)
				{
					if (isset($erg->gebiet[$gbt->gebiet_id]))
					{
						// 0-Werte hervorheben
						if ($erg->gebiet[$gbt->gebiet_id]->punkte != '' && $erg->gebiet[$gbt->gebiet_id]->punkte == '0')
						{
							$style = 'style="color:#C10000; text-align: right; padding-right: 3px"';
						}
						else
						{
							$style = 'style="text-align: right; padding-right: 3px"';
						}

						echo '<td ' . $style . ' class="pst_' . $erg->prestudent_id . '_gbt_' . $gbt->gebiet_id . ' punkte '.$inaktiv.'" nowrap>
								';
						// Punkte können nur gelöscht werden, solange "Zum Reihungstest angetreten" nicht gesetzt ist
						if ($erg->teilgenommen == false || $rechte->isBerechtigt('admin'))
						{
							echo '  <span class="punkteSpan">' . ($erg->gebiet[$gbt->gebiet_id]->punkte != '' ? number_format($erg->gebiet[$gbt->gebiet_id]->punkte, 2, ',', ' ') : '') . '</span>
									<span class="deleteSpan"  style="display: none">
										<a href="#" onclick="deleteResult(' . $erg->prestudent_id . ',' . $gbt->gebiet_id . ', \'' . $erg->vorname . ' ' . $erg->nachname . '\', \'' . $gbt->name . '\');">
											<span class="glyphicon glyphicon-remove" style="color: #e0a800;"></span>
										</a>
									</span>';
						}
						else
						{
							echo '  <span class="">' . ($erg->gebiet[$gbt->gebiet_id]->punkte != '' ? number_format($erg->gebiet[$gbt->gebiet_id]->punkte, 2, ',', ' ') : '') . '</span>';
						}
						echo '</td>';
						echo '<td ' . $style . ' class="pst_' . $erg->prestudent_id . '_gbt_' . $gbt->gebiet_id . ' punkte '.$inaktiv.'" nowrap>' . ($erg->gebiet[$gbt->gebiet_id]->prozent != '' ? number_format($erg->gebiet[$gbt->gebiet_id]->prozent, 2, ',', ' ') . ' %' : '') . '</td>';
					}
					else
					{
						echo '<td></td><td></td>';
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
