<?php
/*
 * Copyright (C) 2006 Technikum-Wien
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger < christian.paminger@technikum-wien.at >
 *			Andreas Oesterreicher < andreas.oesterreicher@technikum-wien.at >
 * 			Rudolf Hangl < rudolf.hangl@technikum-wien.at >
 * 			Gerald Simane-Sequens < gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Script zum Zusammenlegen Doppelter Studenten
 * Es werden zwei Listen mit Studenten angezeigt
 * Links wird der Student markiert, der mit dem
 * rechts markierten zusammengelegt werden soll.
 * Der linke Student wird danach entfernt.
 */
require_once ('../../config/vilesci.config.inc.php');
require_once ('../../include/basis_db.class.php');
require_once ('../../include/person.class.php');
require_once ('../../include/functions.inc.php');
require_once ('../../include/benutzerberechtigung.class.php');
require_once ('../../include/akte.class.php');
require_once ('../../include/dms.class.php');
require_once ('../../include/adresse.class.php');
require_once ('../../include/personlog.class.php');
require_once ('../../include/prestudent.class.php');
require_once ('../../include/benutzer.class.php');
require_once ('../../include/mitarbeiter.class.php');
require_once ('../../include/fotostatus.class.php');
require_once ('../../include/kontakt.class.php');
require_once ('../../include/dokument.class.php');
require_once ('../../include/reihungstest.class.php');
require_once ('../../include/pruefling.class.php');
require_once ('../../include/udf.class.php');


if (! $db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if (! $rechte->isBerechtigt('basis/person', null, 'suid'))
	die($rechte->errormsg);

$udf = new UDF();
$msg_info = array();
$msg_error = array();
$msg_warning = array();
$outp = '';

$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '';
// Filterstring saeubern
if ($filter != '')
{
	// String aufsplitten und Sonderzeichen entfernen
	$searchItems = explode(' ', TRIM(str_replace(',', '', $filter), ' 	!.?'));
	// Leerzeichen und Whitespaces entfernen
	$searchItems = preg_replace("/\s/", '', $searchItems);
	// Leere Strings aus Array entfernen
	while ($array_key = array_search("", $searchItems))
		unset($searchItems[$array_key]);

	// Wenn Zeichen uebrig bleiben
	if (implode(',', $searchItems) != '')
	{
		$filter = implode(' ', $searchItems);
	}
	else
		$filter = '';
}

// Wenn 2 Personen IDs uebergeben werden, diese Personen laden
// Wenn nur eine Person ID uebergeben wird, alle Personen mit diesem Namen laden
if (isset($_GET['person_id_1']) && $_GET['person_id_1'] != '' && isset($_GET['person_id_2']) && $_GET['person_id_2'] != '')
{
	$person_id_1 = $_GET['person_id_1'];
	$person_id_2 = $_GET['person_id_2'];
}
elseif ((isset($_GET['person_id_1']) && $_GET['person_id_1'] != '') || (isset($_GET['person_id_2']) && $_GET['person_id_2'] != ''))
{
	if (isset($_GET['person_id_1']) && $_GET['person_id_1'] != '')
	{
		$person = new person($_GET['person_id_1']);
		$filter = $person->nachname.' '.$person->vorname;
		$person_id_1 = '';
		$person_id_2 = '';
	}
	elseif (isset($_GET['person_id_2']) && $_GET['person_id_2'] != '')
	{
		$person = new person($_GET['person_id_2']);
		$filter = $person->nachname.' '.$person->vorname;
		$person_id_1 = '';
		$person_id_2 = '';
	}
}
else
{
	$person_id_1 = '';
	$person_id_2 = '';
}

if (isset($_GET['radio_1']) || isset($_POST['radio_1']))
{
	$personToDelete = (isset($_GET['radio_1']) ? $_GET['radio_1'] : $_POST['radio_1']);
}
else
{
	$personToDelete = - 1;
}
if (isset($_GET['radio_2']) || isset($_POST['radio_2']))
{
	$personToKeep = (isset($_GET['radio_2']) ? $_GET['radio_2'] : $_POST['radio_2']);
}
else
{
	$personToKeep = - 1;
}

if (isset($personToDelete) && isset($personToKeep) && $personToDelete >= 0 && $personToKeep >= 0)
{
	if ($personToDelete == $personToKeep)
	{
		$msg_error = "Die Datensaetze duerfen nicht die gleiche ID haben";
	}
	else
	{
		// Prüfen, ob tbl_alma existiert (also ob ALMA extension installiert ist)
		if($result = @$db->db_query("SELECT 1 FROM sync.tbl_alma LIMIT 1"))
		{
			// Wenn Person in ALMA Bibliothek vorkommt, ggf. die Person dort übernehmen
			$alma_has_personToKeep = false;
			$alma_has_personToDelete = false;
			$alma_update_obj = new StdClass();
			$alma_query_upd = '';

			$alma_query = "
						SELECT *
						FROM sync.tbl_alma
						WHERE (
							person_id = " . $db->db_add_param($personToKeep, FHC_INTEGER) . " OR
							person_id = " . $db->db_add_param($personToDelete, FHC_INTEGER) . "
						)";

			if ($result = $db->db_query($alma_query))
			{
				while ($row = $db->db_fetch_object($result))
				{
					if ($row->person_id == $personToKeep)
					{
						$alma_has_personToKeep = true;
					}
					if ($row->person_id == $personToDelete)
					{
						$alma_has_personToDelete = true;
						$alma_update_obj = $row;
					}
				}
			}

			// Falls nur die zu löschende Person in ALMA vorhanden ist, mit der zu behaltenden Person ersetzen
			if ($alma_has_personToDelete && !$alma_has_personToKeep)
			{
				$alma_query_upd = "
					UPDATE sync.tbl_alma
					SET person_id = " . $db->db_add_param($personToKeep, FHC_INTEGER) . "
					WHERE alma_match_id = " . $alma_update_obj->alma_match_id . "
					AND person_id = " . $alma_update_obj->person_id . ";";
			}
			// Falls bereits doppelte Einträge in ALMA vorhanden sind (zu löschende und zu behaltende), manuell lösen
			elseif ($alma_has_personToDelete && $alma_has_personToKeep)
			{
				die('Es sind bereits beide Personen in ALMA vorhanden. Bitte zuerst direkt im ALMA Bibliotheksystem und in der tbl_alma lösen.');
			}
	   }

		// Prüfen, ob tbl_sap_students exisitiert
		if($result = @$db->db_query("SELECT 1 FROM sync.tbl_sap_students LIMIT 1"))
		{
			// Wenn Person in SAP students vorkommt, ggf. die Person dort übernehmen
			$sap_students_has_personToKeep = false;
			$sap_students_has_personToDelete = false;
			$sap_students_update_obj = new StdClass();
			$sap_students_query_upd = '';

			$sap_students_query = "
					SELECT *
					FROM sync.tbl_sap_students
					WHERE (
						person_id = " . $db->db_add_param($personToKeep, FHC_INTEGER) . " OR
						person_id = " . $db->db_add_param($personToDelete, FHC_INTEGER) . "
					)";

			if ($result = $db->db_query($sap_students_query)) {
				while ($row = $db->db_fetch_object($result)) {
					if ($row->person_id == $personToKeep) {
						$sap_students_has_personToKeep = true;
					}
					if ($row->person_id == $personToDelete) {
						$sap_students_has_personToDelete = true;
						$sap_students_update_obj = $row;
					}
				}
			}

			// Wenn die zu löschende Person in SAP students eingetragen ist, dann mit der zu behaltenden Person überschreiben
			if ($sap_students_has_personToDelete && !$sap_students_has_personToKeep) {
				$sap_students_query_upd = "
				UPDATE sync.tbl_sap_students
				SET person_id = " . $db->db_add_param($personToKeep, FHC_INTEGER) . "
				WHERE sap_user_id = " . $db->db_add_param($sap_students_update_obj->sap_user_id, FHC_STRING) . "
				AND person_id = " . $sap_students_update_obj->person_id . ";";
			}
			// Wenn doppelte Personeneinträge in SAP students vorhanden sind (zu löschende UND zu behaltende Person),
			// dann manuell lösen
            elseif ($sap_students_has_personToDelete && $sap_students_has_personToKeep) {
				die('Es sind bereits beide Personen in SAP vorhanden. Bitte zuerst direkt in der tbl_sap_students lösen.');
			}
		}
		
		$personToDelete_obj = new person();
		if ($personToDelete_obj->load($personToDelete))
		{
			$error = false;
			$sql_query_upd1 = "BEGIN;";
			$personToKeep_obj = new person();
			$personToKeep_obj->load($personToKeep);

			// Wenn beide Personen eine SVNR oder ein Ersatzkennzeichen haben, abbrechen
			if (($personToDelete_obj->ersatzkennzeichen != '' && $personToKeep_obj->ersatzkennzeichen != '') ||
				($personToDelete_obj->svnr != '' && $personToKeep_obj->svnr != ''))
			{
				$msg_error[] = 'Beide Personen haben eine Sozialversicherungsnummer oder ein Ersatzkennzeichen und können nicht zusammengelegt werden.<br>
						Bitte wenden Sie sich an einen Administrator.';
				$error = true;
			}

			// Wenn beide Personen eine Matr_nr haben, abbrechen
			if ($personToDelete_obj->matr_nr != '' && $personToKeep_obj->matr_nr != ''
				&& $personToDelete_obj->matr_nr != $personToKeep_obj->matr_nr)
			{
				$msg_error[] = 'Beide Personen haben eine Matrikelnummer und können nicht zusammengelegt werden.<br>
						Bitte wenden Sie sich an einen Administrator.';
				$error = true;
			}

			// Wenn beide Personen ein BPK haben, abbrechen
			if ($personToDelete_obj->bpk != '' && $personToKeep_obj->bpk != '' && $personToDelete_obj->bpk != $personToKeep_obj->bpk)
			{
				$msg_error[] = 'Beide Personen haben unterschiedliche BPK und können nicht zusammengelegt werden.<br>
						Bitte wenden Sie sich an einen Administrator.';
				$error = true;
			}

			// Wenn zwei gleiche rt_person Einträge vorhanden sind, wird ein Fehler ausgegeben und abgebrochen
			$reihungstest_personToKeep = new reihungstest();
			$reihungstest_personToKeep->getReihungstestPerson($personToKeep);
			$doppelteReihungstestzuordnung = false;

			foreach ($reihungstest_personToKeep->result as $row)
			{
				$rt_doppelt = new reihungstest();
				if ($rt_doppelt->checkPersonRtStudienplanExists($personToDelete, $row->reihungstest_id, $row->studienplan_id))
				{
					$doppelteReihungstestzuordnung = true;
					$msg_error[] = "Die Person ".$personToDelete." hat schon eine Reihungstestzuordnung
									zu Reihungstest ID ".$row->reihungstest_id." im Studienplan ".$row->studienplan_id.".<br>
									Sie müssen die Datensätze manuell bereinigen, bevor Sie die Personen zusammenlegen können.";
					$error = true;
					break;
				}
			}
			if ($doppelteReihungstestzuordnung === false)
				$sql_query_upd1 .= "UPDATE public.tbl_rt_person SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";


			$personUDF = $udf->personHasUDF();

			if ($personUDF)
			{
				$udfToKeep = json_decode($personToKeep_obj->udf_values, true);
				$udfToDelete = json_decode($personToDelete_obj->udf_values, true);

				$udfToKeep = is_null($udfToKeep) ? array() : $udfToKeep;
				$udfToDelete = is_null($udfToDelete) ? array() : $udfToDelete;

				if ($udfToKeep != $udfToDelete)
				{
					foreach ($udfToDelete as $key => $udfValue)
					{
						if (!array_key_exists($key, $udfToKeep))
						{
							$udfToKeep[$key] = $udfValue;
						}
						elseif ($udfToKeep[$key] !== $udfValue && !is_null($udfValue))
						{
							if (is_null($udfToKeep[$key]))
								$udfToKeep[$key] = $udfValue;
							else
							{
								$msg_error[] = 'Beide Personen haben unterschiedliche Werte in den UDFs und können nicht zusammengelegt werden.<br>
												Sie müssen die Datensätze manuell bereinigen, bevor Sie die Personen zusammenlegen können.';
								$error = true;
								break;
							}
						}
					}
				}
			}
			if ($error == false)
			{
				// Wenn bei einer der Personen das Foto gesperrt ist, dann die Sperre uebernehmen
				if ($personToDelete_obj->foto_sperre)
					$sql_query_upd1 .= "UPDATE public.tbl_person SET foto_sperre=true WHERE person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . ";";

				// Wenn die zu loeschende Person ein Foto hat, und die andere nicht,
				// dann wird das Foto, die Fotosperre und die Historie des Fotostatus übernommen
				if ($personToDelete_obj->foto != '' && $personToKeep_obj->foto == '')
				{
					$sql_query_upd1 .= "UPDATE public.tbl_person SET foto=" . $db->db_add_param($personToDelete_obj->foto) . " WHERE person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . ";";
					$sql_query_upd1 .= "UPDATE public.tbl_person_fotostatus SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
					$msg_warning[] = "Das Foto der zu löschenden Person wurde übernommen";
				}
				// Wenn 2 Fotos vorhanden sind, wird das Aktuellere (aus der Akte) übernommen, skaliert und dessen Fotostatus übernommen
				elseif ($personToDelete_obj->foto != '' && $personToKeep_obj->foto != '')
				{
					$akte1 = new akte();
					$akte1->getAkten($personToDelete, 'Lichtbil', null, null, true);
					if (isset($akte1->result[0]->insertamum))
						$insertamum1 = $akte1->result[0]->insertamum;
					else
						$insertamum1 = 0;

					$akte2 = new akte();
					$akte2->getAkten($personToKeep, 'Lichtbil', null, null, true);
					if (isset($akte2->result[0]->insertamum))
						$insertamum2 = $akte2->result[0]->insertamum;
					else
						$insertamum2 = 0;

					// Die zu löschende Person hat ein aktuelleres Foto -> dieses nehmen
					if ($insertamum1 > $insertamum2)
					{
						$akteInhalt = $akte1->result[0]->inhalt;
						$akteDMS = $akte1->result[0]->dms_id;
						// Bestehende Fotohistorie löschen und jene vom neuen Foto übernehmen
						$sql_query_upd1 .= "DELETE FROM public.tbl_person_fotostatus WHERE person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . ";";
						$sql_query_upd1 .= "UPDATE public.tbl_person_fotostatus SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
						$msg_warning[] = "Das Foto von Person ".$personToDelete." war aktueller und wurde übernommen";
					}
					elseif ($insertamum1 < $insertamum2)
					{
						$akteInhalt = $akte2->result[0]->inhalt;
						$akteDMS = $akte2->result[0]->dms_id;
						// Bestehende Fotohistorie löschen und jene vom neuen Foto übernehmen
						$sql_query_upd1 .= "DELETE FROM public.tbl_person_fotostatus WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
						$msg_warning[] = "Das Foto von Person ".$personToKeep." war aktueller und wurde übernommen";
					}
					else
					{
						$akteInhalt = '';
						$akteDMS = '';
					}
					// Wenn Inhalt vorhanden, diesen laden, sonst aus DMS
					$base64foto = '';
					if (isset($akteInhalt) && $akteInhalt != '')
					{
						$base64foto = $akteInhalt;
					}
					elseif (isset($akteDMS) && $akteDMS != '')
					{
						$dms = new dms();
						if ($dms->load($akteDMS))
						{
							$filename = DMS_PATH . $dms->filename;
							$base64foto = base64_encode(file_get_contents($filename));
						}
					}

					// Bild in tbl_person auf 240x320 skalieren
					$base64_src = resize($base64foto, 240, 320);

					$sql_query_upd1 .= "UPDATE public.tbl_person SET foto=" . $db->db_add_param($base64_src) . " WHERE person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . ";";
				}

				// Wenn Ersatzkennzeichen und Sozialversicherungsnummer vorhanden ist, beide erhalten
				// Setzen erst möglich, wenn $personToDelete_obj gelöscht

				$ersatzkennzeichen = '';
				if ($personToDelete_obj->ersatzkennzeichen == '' && $personToKeep_obj->ersatzkennzeichen != '')
					$ersatzkennzeichen = $personToKeep_obj->ersatzkennzeichen;
				if ($personToKeep_obj->ersatzkennzeichen == '' && $personToDelete_obj->ersatzkennzeichen != '')
					$ersatzkennzeichen = $personToDelete_obj->ersatzkennzeichen;

				$sozialversicherungsnummer = '';
				if ($personToDelete_obj->svnr == '' && $personToKeep_obj->svnr != '')
					$sozialversicherungsnummer = $personToKeep_obj->svnr;
				if ($personToKeep_obj->svnr == '' && $personToDelete_obj->svnr != '')
					$sozialversicherungsnummer = $personToDelete_obj->svnr;

				$matr_nr = '';
				if ($personToDelete_obj->matr_nr == '' && $personToKeep_obj->matr_nr != '')
					$matr_nr = $personToKeep_obj->matr_nr;
				if ($personToKeep_obj->matr_nr == '' && $personToDelete_obj->matr_nr != '')
					$matr_nr = $personToDelete_obj->matr_nr;
				else
					$matr_nr = $personToKeep_obj->matr_nr;

				$bpk = '';
				if ($personToDelete_obj->bpk == '' && $personToKeep_obj->bpk != '')
					$bpk = $personToKeep_obj->bpk;
				if ($personToKeep_obj->bpk == '' && $personToDelete_obj->bpk != '')
					$bpk = $personToDelete_obj->bpk;
				else
					$bpk = $personToKeep_obj->bpk;

				// Beide Anmerkungen behalten, wenn vorhanden und Person_id der gelöschten Person in die Anmerkung schreiben
				$anmerkung = '';
				if ($personToDelete_obj->anmerkungen == '' && $personToKeep_obj->anmerkungen != '')
					$anmerkung = $personToKeep_obj->anmerkungen;
				if ($personToKeep_obj->anmerkungen == '' && $personToDelete_obj->anmerkungen != '')
					$anmerkung = $personToDelete_obj->anmerkungen;
				if ($personToKeep_obj->anmerkungen != '' && $personToDelete_obj->anmerkungen != '')
					$anmerkung = $personToKeep_obj->anmerkungen."
Alte Anmerkungen: ".$personToDelete_obj->anmerkungen;

				$anmerkung .= "
				
Zusammengelegt mit Person-ID ".$personToDelete_obj->person_id." am ".date('d.m.Y H:i:s')." von ".$uid;

				// Letztbenutzten Zugangscode abfragen und übernehmen
				$zugangscode = '';
				$log = new personlog();
				$log->getLog($personToDelete, null, null, array('name' => 'Login with code'));
				if (isset($log->logs[0]))
					$logZugriff1 = strtotime($log->logs[0]->zeitpunkt);
				else
					$logZugriff1 = 0;

				$log->getLog($personToKeep, null, null, array('name' => 'Login with code'));
				if (isset($log->logs[0]))
					$logZugriff2 = strtotime($log->logs[0]->zeitpunkt);
				else
					$logZugriff2 = 0;

				if ($logZugriff1 > $logZugriff2)
					$zugangscode = $personToDelete_obj->zugangscode;
				elseif ($logZugriff2 > $logZugriff1)
					$zugangscode = $personToKeep_obj->zugangscode;
				else
					$zugangscode = $personToKeep_obj->zugangscode;

				$sql_query_upd1 .= "UPDATE lehre.tbl_abschlusspruefung SET pruefer1=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE pruefer1=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE lehre.tbl_abschlusspruefung SET pruefer2=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE pruefer2=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE lehre.tbl_abschlusspruefung SET pruefer3=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE pruefer3=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE lehre.tbl_projektbetreuer SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE lehre.tbl_vertrag SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_adresse SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_akte SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_bankverbindung SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_benutzer SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_kontakt SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_konto SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_msg_message SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_msg_recipient SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_msg_status SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_notizzuordnung SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_personfunktionstandort SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_preincoming SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_preincoming SET person_id_coordinator_dep=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id_coordinator_dep=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_preincoming SET person_id_coordinator_int=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id_coordinator_int=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_preincoming SET person_id_emergency=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id_emergency=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_preinteressent SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE public.tbl_prestudent SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE system.tbl_filters SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE system.tbl_log SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE system.tbl_person_lock SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE system.tbl_issue SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE wawi.tbl_betriebsmittelperson SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= "UPDATE wawi.tbl_konto SET person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . " WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";
				$sql_query_upd1 .= $alma_query_upd;
				$sql_query_upd1 .= $sap_students_query_upd;

				$sql_query_upd1 .= "DELETE FROM public.tbl_person WHERE person_id=" . $db->db_add_param($personToDelete, FHC_INTEGER) . ";";

				// Ersatzkennzeichen und Sozialversicherungsnummer erst setzen, wenn nur mehr eine Person vorhanden ist
				$sql_query_upd1 .= "UPDATE public.tbl_person SET ersatzkennzeichen=" . $db->db_add_param($ersatzkennzeichen, FHC_STRING) . ", svnr=" . $db->db_add_param($sozialversicherungsnummer, FHC_STRING) . " WHERE person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . ";";

				// Zugangscode erst setzen, wenn nur mehr eine Person vorhanden ist
				$sql_query_upd1 .= "UPDATE public.tbl_person SET zugangscode=" . $db->db_add_param($zugangscode, FHC_STRING) . " WHERE person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . ";";

				// Matr_nr erst setzen, wenn nur mehr eine Person vorhanden ist
				$sql_query_upd1 .= "UPDATE public.tbl_person SET matr_nr=" . $db->db_add_param($matr_nr, FHC_STRING) . " WHERE person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . ";";

				// BPK erst setzen, wenn nur mehr eine Person vorhanden ist
				$sql_query_upd1 .= "UPDATE public.tbl_person SET bpk=" . $db->db_add_param($bpk, FHC_STRING) . " WHERE person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . ";";

				// Anmerkung erst setzen, wenn nur mehr eine Person vorhanden ist
				$sql_query_upd1 .= "UPDATE public.tbl_person SET anmerkung=" . $db->db_add_param($anmerkung, FHC_STRING) . " WHERE person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . ";";

				if ($personUDF)
					$sql_query_upd1 .= "UPDATE public.tbl_person SET udf_values=" . $db->db_add_param(json_encode($udfToKeep)) . " WHERE person_id=" . $db->db_add_param($personToKeep, FHC_INTEGER) . ";";

				if ($db->db_query($sql_query_upd1))
				{
					$msg_info[] = "Update Query:";
					$msg_info = array_merge($msg_info, explode(';', $sql_query_upd1));
					$db->db_query("COMMIT;");
					// Logeintrag schreiben
					PersonLog($personToKeep, 'Action', array(
						'name' => 'Persons merged',
						'message' => 'Person with id ' . $personToDelete . ' merged into person with id ' . $personToKeep,
						'success' => 'true'
					), 'datenwartung', 'core', null, $uid);

					/*
					* ----------------------------------------------------------------------
					* Adressen der verbliebenen Person laden und zusammenräumen
					* -----------------------------------------------------------------------
					*/
					$adresse = new adresse();
					$adresse->load_pers($personToKeep);
					$adressArray = array();
					$adressLoeschArray = array();

					// Alle Adressen in ein Array schreiben
					foreach ($adresse->result AS $row)
					{
						$clean_strasse = str_replace(array(' ','	',',','/','.',':',';','-'), '', strtolower($row->strasse));
						$clean_strasse = str_replace('straße', 'strasse', $clean_strasse);
						$adressArray[] = array('adresse_id' => $row->adresse_id,
							'cleanstrasse' => $clean_strasse,
							'strasse' => $row->strasse,
							'plz' => $row->plz,
							'ort' => $row->ort,
							'gemeinde' => $row->gemeinde,
							'nation' => $row->nation,
							'heimatadresse' => $row->heimatadresse,
							'zustelladresse' => $row->zustelladresse
						);
					}
					// Sortiert die Adressen
					function sortAdressArray($a, $b)
					{
						$c = strcmp($b['cleanstrasse'],$a['cleanstrasse']);
						$c .= strcmp($b['plz'], $a['plz']);
						$c .= strcmp($b['ort'], $a['ort']);
						$c .= $b['heimatadresse'] - $a['heimatadresse'];
						$c .= $b['zustelladresse'] - $a['zustelladresse'];
						return $c;
					}
					usort($adressArray, "sortAdressArray");

					$cleanstrasse = '';
					$plz =  '';
					$ort = '';
					foreach ($adressArray AS $key => $value)
					{
						// Leere/Halbleere Datensätze löschen
						if (($value['cleanstrasse'] == '' && $value['plz'] == '' && $value['ort'] == '') ||
							($value['cleanstrasse'] == '' && $value['plz'] != '' && $value['ort'] == '') ||
							($value['cleanstrasse'] == '' && $value['plz'] == '' && $value['ort'] != ''))
						{
							unset($adressArray[$key]);
							$adressLoeschArray[] = $value['adresse_id'];
							continue;
						}
						if ($cleanstrasse != '')
						{
							// Wenn die Strasse gleich der vorherigen ist, PLZ und Ort vergleichen
							if ($cleanstrasse == $value['cleanstrasse'])
							{
								if ($plz == $value['plz'] || $value['plz'] == '')
								{
									if ($ort == $value['ort'] || $value['ort'] == '')
									{
										unset($adressArray[$key]);
										$adressLoeschArray[] = $value['adresse_id'];
										continue;
									}
									else
									{
										$ort = $value['ort'];
										continue;
									}
								}
								else
								{
									$plz = $value['plz'];
									continue;
								}
							}
						}
						$cleanstrasse = $value['cleanstrasse'];
						$plz = $value['plz'];
						$ort = $value['ort'];
					}
					// Adressen im $adressLoeschArray löschen
					if (count($adressLoeschArray) > 0)
					{
						foreach ($adressLoeschArray AS $key => $value)
						{
							$adresse->delete($value);
							$msg_warning[] = "Adresse mit ID" . $value . " gelöscht";
						}
					}
					// Wenn mehr als eine Adresse mit Heimatadresse übrig bleibt, Warnung ausgeben
					$anzahlHeimatadressen = 0;
					foreach ($adressArray AS $key => $value)
					{
						if ($value['heimatadresse'] === true)
							$anzahlHeimatadressen++;
					}
					if ($anzahlHeimatadressen > 1)
						$msg_error[] = "Es ist mehr als eine Adresse als Heimatadresse gekennzeichnet";

					/*
					 * -------------------------------------------------------------------
					 * Kontakte der verbliebenen Person laden und zusammenräumen
					 * -------------------------------------------------------------------
					 */
					$kontakt = new kontakt();
					$kontakt->load_pers($personToKeep);
					$kontaktArray = array();
					$kontaktLoeschArray = array();
					foreach ($kontakt->result AS $row)
					{
						// Telefonnummer validieren
						if ($row->kontakttyp != 'email' && $row->kontakttyp != 'homepage')
							$cleanKontakt = preg_replace("/[^0-9+]/", '', $row->kontakt);
						else
							$cleanKontakt = $row->kontakt;

						$kontaktArray[] = array('kontakt_id' => $row->kontakt_id,
							'cleanKontakt' => $cleanKontakt,
							'kontakttyp' => $row->kontakttyp,
							'kontakt' => $row->kontakt,
							'anmerkung' => $row->anmerkung,
							'zustellung' => $row->zustellung
						);
					}

					// Sortiert die Kontakte
					function sortKontaktArray($a, $b)
					{
						//$c = strcmp($a['kontakttyp'],$b['kontakttyp']);
						$c = strcmp($b['cleanKontakt'], $a['cleanKontakt']);
						$c .= strcmp($a['kontakttyp'],$b['kontakttyp']);
						$c .= strcmp($b['anmerkung'], $a['anmerkung']);
						$c .= $b['zustellung'] - $a['zustellung'];
						return $c;
					}
					usort($kontaktArray, "sortKontaktArray");

					$cleanKontakt = '';
					$anmerkung = '';
					foreach ($kontaktArray AS $key => $value)
					{
						// Leere/Halbleere Datensätze löschen
						if ($value['cleanKontakt'] == '' && $value['anmerkung'] == '' )
						{
							unset($kontaktArray[$key]);
							$kontaktLoeschArray[] = $value['kontakt_id'];
							continue;
						}
						if ($cleanKontakt != '')
						{
							// Wenn der Kontakt gleich dem vorherigen ist, Anmerkung vergleichen
							if ($cleanKontakt == $value['cleanKontakt'])
							{
								if ($anmerkung == $value['anmerkung'] || $value['anmerkung'] == '')
								{
									unset($kontaktArray[$key]);
									$kontaktLoeschArray[] = $value['kontakt_id'];
									continue;
								}
								else
								{
									$anmerkung = $value['anmerkung'];
									continue;
								}
							}
						}
						$cleanKontakt = $value['cleanKontakt'];
						$anmerkung = $value['anmerkung'];
					}
					// Kontakte im $kontaktLoeschArray löschen
					if (count($kontaktLoeschArray) > 0)
					{
						foreach ($kontaktLoeschArray AS $key => $value)
						{
							$kontakt->delete($value);
							$msg_warning[] = "Kontakt mit ID" . $value . " gelöscht";
						}
					}

					/*
					 * --------------------------------------------------------
					 * Doppelte PreStudenten löschen
					 * --------------------------------------------------------
					 */
					$prestudenten = new prestudent();
					$prestudenten->getPrestudenten($personToKeep);
					$statusArrayWichtige = array(); // Array mit allen PreStudentStatus die NICHT Interessent oder Abgewiesener sind

					foreach ($prestudenten->result AS $key => $value)
					{
						$laststatus = new prestudent();
						$laststatus->getLastStatus($value->prestudent_id);
						$prestudentStatus = new prestudent();
						$prestudentStatus->getPrestudentRolle($value->prestudent_id);

						foreach ($prestudentStatus->result AS $row)
						{
							if ($row->status_kurzbz != 'Interessent' && $row->status_kurzbz != 'Abgewiesener')
								$statusArrayWichtige[$value->prestudent_id][] = $row->status_kurzbz;
						}
						if (isset($statusArrayWichtige[$value->prestudent_id]))
							$statusArrayWichtige[$value->prestudent_id] = array_unique($statusArrayWichtige[$value->prestudent_id]);

						$studiengang = new studiengang($value->studiengang_kz);
						$prestudenten->result[$key]->studiensemester_kurzbz = $laststatus->studiensemester_kurzbz;
						$prestudenten->result[$key]->orgform_kurzbz = $laststatus->orgform_kurzbz;
						$prestudenten->result[$key]->status_kurzbz = $laststatus->status_kurzbz;
						$prestudenten->result[$key]->bewerbung_abgeschicktamum = $laststatus->bewerbung_abgeschicktamum;
						$prestudenten->result[$key]->bestaetigtam = $laststatus->bestaetigtam;
						$prestudenten->result[$key]->ausbildungssemester = $laststatus->ausbildungssemester;
						$prestudenten->result[$key]->studiengang_typ = $studiengang->typ;
						$prestudenten->result[$key]->anzahlStatus = $prestudentStatus->num_rows;
					}

					$statusreihenfolge = array('Aufgenommener','Wartender','Bewerber','Interessent','Abgewiesener');

					function sortPrestudents($a, $b)
					{
						global $statusreihenfolge;
						$c = $a->studiengang_kz - $b->studiengang_kz;
						$c .= strcmp(strtolower($b->studiensemester_kurzbz), strtolower($a->studiensemester_kurzbz));
						$c .= strcmp(strtolower($b->orgform_kurzbz), strtolower($a->orgform_kurzbz));

						// Sortiert den Status nach der vorgegebenen Liste $statusreihenfolge
						$x = array_search($a->status_kurzbz, $statusreihenfolge);
						$y = array_search($b->status_kurzbz, $statusreihenfolge);
						if($x === false && $y === false)
						{
							$c .= 0;
						}
						elseif ($x === false)
						{
							$c .= 1;
						}
						elseif ($y === false)
						{
							$c .= -1;
						}
						else
						{
							$c .= $x - $y;
						}

						$c .= $b->anzahlStatus - $a->anzahlStatus;
						$c .= $b->bestaetigtam - $a->bestaetigtam;
						$c .= $b->bewerbung_abgeschicktamum - $a->bewerbung_abgeschicktamum;
						$c .= $b->ausbildungssemester - $a->ausbildungssemester;
						$c .= $b->zgvmas_code - $a->zgvmas_code;
						$c .= strcmp(strtolower($b->zgvmaort), strtolower($a->zgvmaort));
						$c .= $b->zgvmadatum - $a->zgvmadatum;
						$c .= strcmp(strtolower($b->zgvmanation), strtolower($a->zgvmanation));
						$c .= $b->zgv_code - $a->zgv_code;
						$c .= strcmp(strtolower($b->zgvort), strtolower($a->zgvort));
						$c .= $b->zgvdatum - $a->zgvdatum;
						$c .= strcmp(strtolower($b->zgvnation), strtolower($a->zgvnation));
						return $c;
					}

					usort($prestudenten->result, "sortPrestudents");

					$prestudentUDF = $udf->prestudentHasUDF();
					$prestudentenArray = array();
					$kontaktLoeschArray = array();
					foreach ($prestudenten->result AS $key => $row)
					{
						$prestudentenArray[] = array(
							'prestudent_id' => $row->prestudent_id,
							'studiengang_kz' => $row->studiengang_kz,
							'studiensemester_kurzbz' => $row->studiensemester_kurzbz,
							'orgform_kurzbz' => $row->orgform_kurzbz,
							'status_kurzbz' => $row->status_kurzbz,
							'bewerbung_abgeschicktamum' => $row->bewerbung_abgeschicktamum,
							'bestaetigtam' => $row->bestaetigtam,
							'ausbildungssemester' => $row->ausbildungssemester,
							'zgv_code' => $row->zgv_code,
							'zgvort' => $row->zgvort,
							'zgvdatum' => $row->zgvdatum,
							'zgvnation' => $row->zgvnation,
							'zgvmas_code' => $row->zgvmas_code,
							'zgvmaort' => $row->zgvmaort,
							'zgvmadatum' => $row->zgvmadatum,
							'zgvmanation' => $row->zgvmanation,
							'studiengang_typ' => $row->studiengang_typ
						);
						if ($prestudentUDF)
							$prestudentenArray[$key] = array_merge($prestudentenArray[$key], array('udf_values' => $row->udf_values));
					}

					/*
					 * Entscheidung, ob und welche doppelten PreStudenten gelöscht werden
					 * Wenn Studiengang, Studiensemester und OrgForm gleich sind, wird eventuell einer gelöscht
					 *
					 * Der "höchste" Status laut Liste $statusreihenfolge wird behalten.
					 * Wenn es Widersprüche bei der ZGV gibt, wird dies in $warningList ausgegeben
					 *
					 * Wenn ein PreStudent in seiner Historie ausschließlich Interessent und Abgewiesener hat,
					 * wird einer gelöscht. Bei allen anderen wird eine Warnung ausgegeben
					 *
					 */
					$studiengang_kz = '';
					$anmerkung = '';
					$prestudentLoeschArray = array();
					$prueflingTransferArray = array();
					$warningList = array();
					$i = 0;
					foreach ($prestudentenArray AS $key => $value)
					{
						if ($studiengang_kz != '')
						{
							// Wenn der Studiengang gleich dem vorherigen ist, Studiensemester vergleichen
							if ($studiengang_kz == $value['studiengang_kz'])
							{
								// Wenn das Studiensemester gleich dem vorherigen ist, prüfen ob der Status gleich ist
								if ($studiensemester_kurzbz == $value['studiensemester_kurzbz'])
								{
									// Wenn die OrgForm leer oder gleich der vorherigen ist, prüfen ob der Status gleich ist
									if ($orgform_kurzbz == '' || $orgform_kurzbz == $value['orgform_kurzbz'])
									{
										// Wenn der Status nicht Interessent oder Abgewiesener ist, wird mit dem nächsten weitergemacht und eine Warnung ausgegeben
										if ($value['status_kurzbz'] != 'Interessent' && $value['status_kurzbz'] != 'Abgewiesener')
										{
											$warningList['statusUnklar'][$i][1] = $prestudentId;
											$warningList['statusUnklar'][$i][2] = $value['prestudent_id'];
											$i++;
											continue;
										}

										//Wenn die UDF values nicht zusammengeführt werden können, wird mit dem nächsten weitergemacht und eine Warnung ausgegeben
										if ($prestudentUDF)
										{
											$udfError = false;

											$udfToKeep =  json_decode($prestudentenArray[$previousKey]['udf_values'], true);;
											$udfToKeep =  is_null($udfToKeep) ? array() : $udfToKeep;

											$udfToDelete = json_decode($value['udf_values'], true);
											$udfToDelete = is_null($udfToDelete) ? array() : $udfToDelete;

											if ($udfToKeep != $udfToDelete)
											{
												foreach ($udfToDelete as $udfKey => $udfValue)
												{
													if (!array_key_exists($udfKey, $udfToKeep))
													{
														$udfToKeep[$udfKey] = $udfValue;
													}
													elseif ($udfToKeep[$udfKey] !== $udfValue && !is_null($udfValue))
													{
														if (is_null($udfToKeep[$udfKey]))
															$udfToKeep[$udfKey] = $udfValue;
														else
														{

															$warningList['udfUnklar'][$i][1] = $prestudentId;
															$warningList['udfUnklar'][$i][2] = $value['prestudent_id'];
															$i++;
															$udfError = true;
															break;
														}
													}
												}
											}

											$prestudentenArray[$previousKey]['udf_values'] = json_encode($udfToKeep);
											if ($udfError)
												continue;

										}
										// Wenn der Status gleich ist, wird auf bestätigt-datum geprüft
										if ($status_kurzbz == $value['status_kurzbz'])
										{
											// Wenn bestätigt gleich oder leer ist und die ZGV leer ist, wird die ZGV vom nächsten Datensatz übernommen, falls eine vorhanden ist
											//if ($bestaetigtam == '' || $bestaetigtam == $value['bestaetigtam'])
											{
												// Bei Master wird die Master-ZGV berücksichtigt, bei allen anderen die Bachelor-ZGV
												if ($value['studiengang_typ'] == 'm')
												{
													if ($zgvmas_code == '' && $zgvmaort == '' && $zgvmadatum == '' && $zgvmanation == '' &&
														($value['zgvmas_code'] != '' || $value['zgvmaort'] != '' || $value['zgvmadatum'] != '' || $value['zgvmanation'] != ''))
													{
														$prestudentenArray[$previousKey]['zgvmas_code'] = $zgvmas_code = $value['zgvmas_code'];
														$prestudentenArray[$previousKey]['zgvmaort'] = $zgvmaort = $value['zgvmaort'];
														$prestudentenArray[$previousKey]['zgvmadatum'] = $zgvmadatum = $value['zgvmadatum'];
														$prestudentenArray[$previousKey]['zgvmanation'] = $zgvmanation = $value['zgvmanation'];
														// Wenn kein Status außer Interessent und Abgewiesener mehr vorhanden ist, löschen
														if (!isset($statusArrayWichtige[$value['prestudent_id']]))
														{
															setPrueflingTransfer($value['prestudent_id'], $prestudentId, $prueflingTransferArray);
															unset($prestudentenArray[$key]);
															$prestudentLoeschArray[] = $value['prestudent_id'];
														}
														continue;
													}
													elseif (($value['zgvmas_code'] != '' && $zgvmas_code != $value['zgvmas_code']) ||
														($value['zgvmaort'] != '' && $zgvmaort != $value['zgvmaort']) ||
														($value['zgvmadatum'] != '' && $zgvmadatum != $value['zgvmadatum']) ||
														($value['zgvmanation'] != '' && $zgvmanation != $value['zgvmanation']))
													{
														$warningList['zgvUnklar'][$prestudentId][$i]['zgvcode'] = $value['zgvmas_code'];
														$warningList['zgvUnklar'][$prestudentId][$i]['zgvmaort'] = $value['zgvmaort'];
														$warningList['zgvUnklar'][$prestudentId][$i]['zgvmadatum'] = $value['zgvmadatum'];
														$warningList['zgvUnklar'][$prestudentId][$i]['zgvmanation'] = $value['zgvmanation'];
														// Wenn kein Status außer Interessent und Abgewiesener mehr vorhanden ist, löschen
														if (!isset($statusArrayWichtige[$value['prestudent_id']]))
														{
															setPrueflingTransfer($value['prestudent_id'], $prestudentId, $prueflingTransferArray);
															unset($prestudentenArray[$key]);
															$prestudentLoeschArray[] = $value['prestudent_id'];
														}
														$i++;
														continue;
													}
												}
												else
												{
													if ($zgv_code == '' && $zgvort == '' && $zgvdatum == '' && $zgvnation == '' &&
														($value['zgv_code'] != '' || $value['zgvort'] != '' || $value['zgvdatum'] != '' || $value['zgvnation'] != ''))
													{
														$prestudentenArray[$previousKey]['zgv_code'] = $zgv_code = $value['zgv_code'];
														$prestudentenArray[$previousKey]['zgvort'] = $zgvort = $value['zgvort'];
														$prestudentenArray[$previousKey]['zgvdatum'] = $zgvdatum = $value['zgvdatum'];
														$prestudentenArray[$previousKey]['zgvnation'] = $zgvnation = $value['zgvnation'];
														setPrueflingTransfer($value['prestudent_id'], $prestudentId, $prueflingTransferArray);
														unset($prestudentenArray[$key]);
														$prestudentLoeschArray[] = $value['prestudent_id'];
														continue;
													}
													elseif (($value['zgv_code'] != '' && $zgv_code != $value['zgv_code']) ||
														($value['zgvort'] != '' && $zgvort != $value['zgvort']) ||
														($value['zgvdatum'] != '' && $zgvdatum != $value['zgvdatum']) ||
														($value['zgvnation'] != '' && $zgvnation != $value['zgvnation']))
													{
														$warningList['zgvUnklar'][$prestudentId][$i]['zgvcode'] = $value['zgv_code'];
														$warningList['zgvUnklar'][$prestudentId][$i]['zgvort'] = $value['zgvort'];
														$warningList['zgvUnklar'][$prestudentId][$i]['zgvdatum'] = $value['zgvdatum'];
														$warningList['zgvUnklar'][$prestudentId][$i]['zgvnation'] = $value['zgvnation'];
														setPrueflingTransfer($value['prestudent_id'], $prestudentId, $prueflingTransferArray);
														unset($prestudentenArray[$key]);
														$prestudentLoeschArray[] = $value['prestudent_id'];
														$i++;
														continue;
													}
												}
											}
										}
										setPrueflingTransfer($value['prestudent_id'], $prestudentId, $prueflingTransferArray);
										unset($prestudentenArray[$key]);
										$prestudentLoeschArray[] = $value['prestudent_id'];
										continue;
									}
								}
							}
						}
						$prestudentId = $value['prestudent_id'];
						$studiengang_kz = $value['studiengang_kz'];
						$studiensemester_kurzbz = $value['studiensemester_kurzbz'];
						$status_kurzbz = $value['status_kurzbz'];
						$orgform_kurzbz = $value['orgform_kurzbz'];
						$bestaetigtam = $value['bestaetigtam'];
						$bewerbung_abgeschicktamum = $value['bewerbung_abgeschicktamum'];
						$zgv_code = $value['zgv_code'];
						$zgvort = $value['zgvort'];
						$zgvdatum = $value['zgvdatum'];
						$zgvnation = $value['zgvnation'];
						$zgvmas_code = $value['zgvmas_code'];
						$zgvmaort = $value['zgvmaort'];
						$zgvmadatum = $value['zgvmadatum'];
						$zgvmanation = $value['zgvmanation'];
						$previousKey = $key;
						if ($prestudentUDF)
						{
							$udfToKeep =  json_decode($value['udf_values'], true);
							$udfToKeep =  is_null($udfToKeep) ? array() : $udfToKeep;
						}
					}

					// Messages in $msg_warning schreiben
					$messageOutput = '';
					if (isset($warningList['zgvUnklar']))
					{
						foreach ($warningList['zgvUnklar'] as $verbleibenderPrestudent => $gefundeneZgv)
						{
							$messageOutput .= '<div>Bei Prestudent ID '.$verbleibenderPrestudent.' sind widersprüchliche ZGV-Angaben vorhanden.
							<br>Folgende ZGV-Daten sind bei anderen PreStudenten gespeichert:<br>';

							foreach ($gefundeneZgv as $key => $zgvArray)
							{
								foreach ($zgvArray as $zgv => $wert)
								{
									$messageOutput .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$zgv.': '.$wert.'<br>';
								}
								$messageOutput .= '<br>';
							}
							$messageOutput .= '</div>';
						}
					}

					$msg_warning[] = $messageOutput;
					$messageOutput = '';

					if (isset($warningList['statusUnklar']))
					{
						foreach ($warningList['statusUnklar'] as $key => $value)
						{
							$messageOutput .= '<div>Bei folgenden PreStudenten ist der Status widersprüchlich oder unklar:<br>';

							foreach ($value as $key => $presstudentid)
							{
								$messageOutput .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$presstudentid.'<br>';
							}
							$messageOutput .= '</div>';
						}
					}

					$msg_warning[] = $messageOutput;
					$messageOutput = '';
					if(isset($warningList['udfUnklar']))
					{
						foreach ($warningList['udfUnklar'] as $key => $value) {

							$messageOutput .= '<div>Bei folgenden PreStudenten sind die UDFs widersprüchlich oder unklar:<br>';

							foreach ($value as $presstudentid)
							{
								$messageOutput .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$presstudentid.'<br>';
							}
							$messageOutput .= '</div>';
						}
					}

					$msg_warning[] = $messageOutput;

					//Wenn Prüfling auf zu löschenden Prestudenten zeigt und ggf auf bleibenden umhängen.
					foreach ($prueflingTransferArray as $pruefling_id => $prestudent_id)
					{
						$transferqry = "UPDATE testtool.tbl_pruefling SET prestudent_id=" . $db->db_add_param($prestudent_id, FHC_INTEGER) . " WHERE pruefling_id=" . $db->db_add_param($pruefling_id, FHC_INTEGER) . ";";

						if (!$db->db_query($transferqry))
						{
							$msg_error[] = 'Fehler beim Aktualisieren des Prüflings '.$pruefling_id;
						}
						else
							$msg_warning[] = 'Prüfling '.$pruefling_id.' auf prestudent '.$prestudent_id.' aktualisiert';
					}

					// Prestudenten in $prestudentLoeschArray löschen
					foreach ($prestudentLoeschArray AS $key => $value)
					{
						//Schauen ob akzeptierte Dokumente vorhanden sind und ggf entfernen
						$akzeptierteDokumente = new dokument();
						$akzeptierteDokumente->getPrestudentDokumente($value);
						if (count($akzeptierteDokumente->result) > 0)
						{
							foreach ($akzeptierteDokumente->result as $row)
							{
								$akzeptierteDokumente->delete($row->dokument_kurzbz, $value);
							}
						}
						//Rollen laden und einzeln löschen
						$prestudentenRollen = new prestudent();
						$prestudentenRollen->getPrestudentRolle($value);

						foreach ($prestudentenRollen->result as $row)
						{
							$prestudentenRollen->delete_rolle($row->prestudent_id, $row->status_kurzbz, $row->studiensemester_kurzbz, $row->ausbildungssemester);
						}

						if (!$prestudentenRollen->deletePrestudent($value))
							$msg_error[] = 'Fehler beim Löschen des Prestudenten '.$value;
						else
							$msg_warning[] = 'Prestudent '.$value.' gelöscht';
					}
				}
				else
				{
					$msg_error[] = "Die Änderung konnte nicht durchgeführt werden!";
					$db->db_query("ROLLBACK;");

					$msg_error = array_merge($msg_error, explode(';', $sql_query_upd1));
					$msg_error[] = "ROLLBACK";
				}
				$personToDelete = 0;
				$personToKeep = 0;
			}
		}
		else
		{
			$msg_info[] = "Fehler beim Laden der zu löschenden Person";
		}
	}
}
if ((isset($personToDelete) && ! isset($personToKeep)) || (! isset($personToDelete) && isset($personToKeep)) || ($personToDelete < 0 || $personToKeep < 0))
{
	$msg_info[] = "Es muß je ein Radio-Button pro Tabelle angeklickt werden";
}

/**
 * Holt sich Prüflinge zu einem zu löschenden Prestudenten,
 * speichert auf welchen Prestudenten die Prüflinge "umgehängt" werden sollen.
 * @param $prestudentIdToDelete prestudent_id des zu löschenden Prestudenten
 * @param $prestudentIdToKeep prestudent_id des behaltenen Prestundenten
 * @param $prueflingTransferArray zum Speichern, form [pruefling_id] => neue_prestudent_id
 */
function setPrueflingTransfer($prestudentIdToDelete, $prestudentIdToKeep, &$prueflingTransferArray)
{
	$pruefling = new pruefling();

	if (is_numeric($prestudentIdToDelete) && is_numeric($prestudentIdToKeep) &&
		$pruefling->getPrueflinge($prestudentIdToDelete))
	{
		foreach ($pruefling->result as $pr)
		{
			$prueflingTransferArray[$pr->pruefling_id] = (int)$prestudentIdToKeep;
		}
	}
}

function resize($base64, $width, $height) // 828 x 1104 -> 240 x 320
{
	ob_start();
	$image = imagecreatefromstring(base64_decode($base64));

	// Hoehe und Breite neu berechnen
	list ($width_orig, $height_orig) = getimagesizefromstring(base64_decode($base64));

	if ($width && ($width_orig < $height_orig))
	{
		$width = intval(($height / $height_orig) * $width_orig);
	}
	else
	{
		$height = intval(($width / $width_orig) * $height_orig);
	}

	$image_p = imagecreatetruecolor($width, $height);
	// $image = imagecreatefromjpeg($filename);

	// Bild nur verkleinern aber nicht vergroessern
	if ($width_orig > $width || $height_orig > $height)
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	else
		$image_p = $image;

	imagejpeg($image_p);
	$retval = ob_get_contents();
	ob_end_clean();
	$retval = base64_encode($retval);

	@imagedestroy($image_p);
	@imagedestroy($image);
	return $retval;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/fhcomplete.css" rel="stylesheet" type="text/css">
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
<link href="../../skin/jquery.css" rel="stylesheet" type="text/css" />
<script type="text/javascript"
	src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript"
	src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript"
	src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript"
	src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">

	$(document).ready(function()
	{
		$('#t1').tablesorter(
		{
			sortList: [[2,0],[3,0]],
			widgets: ["zebra"],
			headers: {	8: {sorter: false}}
		});
		$('#t2').tablesorter(
		{
			sortList: [[3,0],[4,0]],
			widgets: ["zebra"],
			headers: {	0: {sorter: false}}
		});
// 		$("input[name='radio_1']").click(function() {
// 			  alert( "Handler for .click() called." );
// 		});

	});
	function enable(id)
	{
		if (id == 'radio_1')
			var radios = document.getElementsByName('radio_2');
		else
			var radios = document.getElementsByName('radio_1');
		for (var i=0, iLen=radios.length; i<iLen; i++) {
			radios[i].disabled = false;
			}
	}
	function disable(id)
	{
		document.getElementById(id).disabled = true;
	}
	function checkPersonen()
	{
		// Check, ob jeweils ein Radiobutton ausgewählt wurde
		if(!$('input[type=radio][name=radio_1]').is(':checked') || !$('input[type=radio][name=radio_2]').is(':checked'))
		{
			alert('Bitte wählen Sie auf beiden Seiten einen Radio-Button aus');
			return false;
		}

		// Wenn die Personen zu unterschiedlich sind, Warnung ausgeben
		// Prüfen auf Vorname, Nachname, Geburtsdatum
		var nachnameLinks = $('input[type=radio][name=radio_1]:checked').closest('tr').find('.nachname').text().toLowerCase();
		var nachnameRechts = $('input[type=radio][name=radio_2]:checked').closest('tr').find('.nachname').text().toLowerCase();
		var vornameLinks = $('input[type=radio][name=radio_1]:checked').closest('tr').find('.vorname').text().toLowerCase();
		var vornameRechts = $('input[type=radio][name=radio_2]:checked').closest('tr').find('.vorname').text().toLowerCase();
		var gebdatumLinks = $('input[type=radio][name=radio_1]:checked').closest('tr').find('.gebdatum').text().toLowerCase();
		var gebdatumRechts = $('input[type=radio][name=radio_2]:checked').closest('tr').find('.gebdatum').text().toLowerCase();

		if (	nachnameLinks != nachnameRechts
				|| vornameLinks != vornameRechts
				|| (gebdatumLinks != '' && gebdatumRechts != '' && gebdatumLinks != gebdatumRechts))
			return confirm('Die Datensätze sind auffallend unterschiedlich. Wollen Sie fortfahren?');
		else
			return true;
	}
	function changeImageSize(id)
	{
		if (document.getElementById(id).style.height == '50px')
		{
			document.getElementById(id).style.height = '130px';
			document.getElementById(id).style.maxWidth = '87px';
		}
		else
		{
			document.getElementById(id).style.height = '50px';
			document.getElementById(id).style.maxWidth = '38px';
		}
	}
	</script>
<style>
.button {
	font-size: 16px;
	font-family: Helvetica, Arial, sans-serif;
	color: #ffffff;
	text-decoration: none;
	border-radius: 3px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	background-color: #5cb85c;
	border-top: 6px solid #5cb85c;
	border-bottom: 6px solid #5cb85c;
	border-right: 12px solid #5cb85c;
	border-left: 12px solid #5cb85c;
	display: inline-block;
	"
}
</style>

<title>Personen-Zusammenlegung</title>
</head>
<body>
	<H1>Zusammenlegen von Personendatensätzen</H1>

<?php
echo $outp;
echo "<form name='suche' action='personen_wartung.php' method='POST'>";
echo '<input name="filter" type="text" value="' . $db->convert_html_chars($filter) . '" size="64" maxlength="64">';
echo '<input type="submit" value=" suchen ">';
echo "</form>";

if ($filter != '' || ($person_id_1 != '' && $person_id_2 != ''))
{
	$studiengang = new studiengang();
	$studiengang->getAll('typ, kurzbz', false);

	/*
	 * echo '<br>
	 * <center>
	 * <h2><span style="font-size:0.7em">'.$msg_info.'</span></h2></center>
	 * <br>';
	 */
	$messageOutput = '';
	if (count($msg_error) > 0)
	{
		foreach ($msg_error as $value)
		{
			$messageOutput .= '<p class="error">'.$value.'</p>';
		}
	}
	if (count($msg_warning) > 0)
	{
		foreach ($msg_warning as $value)
		{
			$messageOutput .= '<p class="warning">'.$value.'</p>';
		}
	}
	if (count($msg_info) > 0)
	{
		foreach ($msg_info as $value)
		{
			$messageOutput .= '<br/>'.$value;
		}
	}
	echo '<br><br><div contenteditable="true" style="width: 100%; height : 150px; border : 1px dotted grey; overflow-y:auto; text-align: left; font-size: 9pt">' . $messageOutput . '</div><br>';

	// Tabellen anzeigen
	echo '<form name="form_table" action="personen_wartung.php?filter='.$db->convert_html_chars($filter).'&person_id_1='.$person_id_1.'&person_id_2='.$person_id_2.'" method="POST">';
	echo '<div style="text-align: center"><input type="submit" value="Zusammenlegen" class="button" onclick="return checkPersonen()"></div>';
	echo "<table width='100%' border='0' cellspacing='0' cellpadding='0'>";
	echo "<tr>";

	echo '<td valign="top" style="text-align: center;"><span style="font-size: 1.5em; color: red;">Person wird gelöscht:</span>';

	// Tabelle 1
	echo '<table id="t1" class="tablesorter" style="padding-right: 5px"><thead><tr>';
	echo "<th>ID</th>";
	echo "<th>Foto</th>";
	echo "<th>Nachname</th>";
	echo "<th>Vorname</th>";
	echo "<th>Geb.datum</th>";
	echo "<th>SVNr</th>";
	echo "<th>Ersatzkennz.</th>";
	echo "<th>Rollen</th>";
	echo "<th>&nbsp;</th></tr></thead><tbody>";

	// Wenn Person IDs uebergeben werden, werden diese geladen
	if ($person_id_1 != '' && $person_id_2 != '')
	{
		$person = new person();
		$person->personen[] = new person($person_id_1);
		$person->personen[] = new person($person_id_2);
	}
	else
	{
		$person = new person();
		$person->getTab($filter);
	}

	$i = 0;
	foreach ($person->personen as $l)
	{
		$rollen = '<ul style="margin: 0; padding-left: 10px;">';
		// Prestudent Rollen laden
		$prestudent = new prestudent();
		$prestudent->getPrestudenten($l->person_id);
		if(count($prestudent->result) > 0)
		{
			foreach ($prestudent->result as $row)
			{
				$laststatus = new prestudent();
				$laststatus->getLastStatus($row->prestudent_id);
				$style = '';
				if ($laststatus->status_kurzbz == 'Abgewiesener' || $laststatus->status_kurzbz == 'Abbrecher')
					$style = 'color: darkred;';
				elseif ($laststatus->status_kurzbz == 'Absolvent')
					$style = 'color: grey;';
				elseif ($laststatus->status_kurzbz == 'Student')
					$style = 'color: green;';

				if (isset($laststatus->status_mehrsprachig[DEFAULT_LANGUAGE]))
					$status = $laststatus->status_mehrsprachig[DEFAULT_LANGUAGE];
				else
					$status = $laststatus->status_kurzbz;

				$rollen .= '<li><span style="'.$style.'">'.$status.'</span> ('.$studiengang->kuerzel_arr[$row->studiengang_kz].' '.$laststatus->ausbildungssemester.'. Semester '.$laststatus->studiensemester_kurzbz.')</li>';
			}
		}
		// Benutzer laden
		$benutzer = new benutzer();
		$benutzer->getBenutzerFromPerson($l->person_id);
		if(count($benutzer->result) > 0)
		{
			foreach ($benutzer->result as $row)
			{
				$mitarbeiter = new mitarbeiter();
				if ($mitarbeiter->load($row->uid))
					$rollen .= '<li><span style="color: green">MitarbeiterIn UID</span> '.$mitarbeiter->uid.'</li>';
			}
		}
		$rollen .= '</ul>';

		echo "<tr>";
		echo "<td>$l->person_id</td>";
		echo '<td>'.($l->foto != '' ? '<img id="imgLeft_'.$l->person_id.'" src="../../content/bild.php?src=person&person_id='.$l->person_id.'" style="height: 50px; max-width: 38px" onclick="changeImageSize(\'imgLeft_'.$l->person_id.'\')">':'').'</td>';
		echo "<td class='nachname'>$l->nachname</td>";
		echo "<td class='vorname'>$l->vorname</td>";
		echo "<td class='gebdatum'>$l->gebdatum</td>";
		echo "<td>$l->svnr</td>";
		echo "<td>$l->ersatzkennzeichen</td>";
		echo "<td>$rollen</td>";
		echo "<td style='text-align: right'><input type='radio' name='radio_1' id='radio_1_$l->person_id' value='$l->person_id' " . ((isset($personToDelete) && $personToDelete == $l->person_id) ? 'checked' : '') . " onclick='enable(\"radio_1\"); disable(\"radio_2_$l->person_id\")'></td>";
		echo "</tr>";
		$i ++;
	}
	echo "</tbody></table>";
	echo "</td>";
	// echo '<td valign="top"></td>';
	echo '<td valign="top" style="text-align: center;"><span style="font-size: 1.5em; font-style: bold; color: green;">Person bleibt:</span>';

	// Tabelle 2
	echo '<table id="t2" class="tablesorter" style="padding-left: 5px"><thead><tr>';
	echo "<th>&nbsp;</th>";
	echo "<th>ID</th>";
	echo "<th>Foto</th>";
	echo "<th>Nachname</th>";
	echo "<th>Vorname</th>";
	echo "<th>Geb.datum</th>";
	echo "<th>SVNr</th>";
	echo "<th>Ersatzkennz.</th>";
	echo "<th>Rollen</th>";
	echo "</tr></thead><tbody>";

	// Wenn Person IDs uebergeben werden, werden diese geladen
	if ($person_id_1 != '' && $person_id_2 != '')
	{
		$person = new person();
		$person->personen[] = new person($person_id_1);
		$person->personen[] = new person($person_id_2);
	}
	else
	{
		$person = new person();
		$person->getTab($filter);
	}

	$i = 0;
	foreach ($person->personen as $l)
	{
		$rollen = '<ul style="margin: 0; padding-left: 10px;">';
		// Prestudent Rollen laden
		$prestudent = new prestudent();
		$prestudent->getPrestudenten($l->person_id);
		if(count($prestudent->result) > 0)
		{
			foreach ($prestudent->result as $row)
			{
				$laststatus = new prestudent();
				$laststatus->getLastStatus($row->prestudent_id);
				$style = '';
				if ($laststatus->status_kurzbz == 'Abgewiesener' || $laststatus->status_kurzbz == 'Abbrecher')
					$style = 'color: darkred;';
				elseif ($laststatus->status_kurzbz == 'Absolvent')
					$style = 'color: grey;';
				elseif ($laststatus->status_kurzbz == 'Student')
					$style = 'color: green;';

				if (isset($laststatus->status_mehrsprachig[DEFAULT_LANGUAGE]))
					$status = $laststatus->status_mehrsprachig[DEFAULT_LANGUAGE];
				else
					$status = $laststatus->status_kurzbz;

				$rollen .= '<li><span style="'.$style.'">'.$status.'</span> ('.$studiengang->kuerzel_arr[$row->studiengang_kz].' '.$laststatus->ausbildungssemester.'. Semester '.$laststatus->studiensemester_kurzbz.')</li>';
			}
		}
		// Benutzer laden
		$benutzer = new benutzer();
		$benutzer->getBenutzerFromPerson($l->person_id);
		if(count($benutzer->result) > 0)
		{
			foreach ($benutzer->result as $row)
			{
				$mitarbeiter = new mitarbeiter();
				if ($mitarbeiter->load($row->uid))
					$rollen .= '<li><span style="color: green">MitarbeiterIn UID</span> '.$mitarbeiter->uid.'</li>';
			}
		}
		$rollen .= '</ul>';

		echo "<tr>";
		echo "<td><input type='radio' name='radio_2' id='radio_2_$l->person_id' value='$l->person_id' " . ((isset($personToKeep) && $personToKeep == $l->person_id) ? 'checked' : '') . " onclick='enable(\"radio_2\"); disable(\"radio_1_$l->person_id\")'></td>";
		echo "<td>$l->person_id</td>";
		echo '<td>'.($l->foto != '' ? '<img id="imgRight_'.$l->person_id.'" src="../../content/bild.php?src=person&person_id='.$l->person_id.'" style="height: 50px; max-width: 38px" onclick="changeImageSize(\'imgRight_'.$l->person_id.'\')">':'').'</td>';
		echo "<td class='nachname'>$l->nachname</td>";
		echo "<td class='vorname'>$l->vorname</td>";
		echo "<td class='gebdatum'>$l->gebdatum</td>";
		echo "<td>$l->svnr</td>";
		echo "<td>$l->ersatzkennzeichen</td>";
		echo "<td>$rollen</td>";
		echo "</tr>";
		$i ++;
	}
	echo "</tbody></table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

?>
</tr>
	</table>
</body>
</html>
