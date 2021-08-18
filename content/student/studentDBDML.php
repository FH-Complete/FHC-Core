<?php
/* Copyright (C) 2006 fhcomplete.org
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
 *			Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *			Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

// ****************************************
// * Insert/Update/Delete
// * der Studenten
// *
// * Script sorgt fuer den Datenbanzugriff
// * fuer das XUL - Studenten-Modul
// *
// ****************************************

require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/'.EXT_FKT_PATH.'/generateuid.inc.php');
require_once('../../include/'.EXT_FKT_PATH.'/generatematrikelnr.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/benutzergruppe.class.php');
require_once('../../include/log.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/akte.class.php');
require_once('../../include/konto.class.php');
require_once('../../include/dokument.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/bisio.class.php');
require_once('../../include/zeugnisnote.class.php');
require_once('../../include/lvgesamtnote.class.php');
require_once('../../include/pruefung.class.php');
require_once('../../include/abschlusspruefung.class.php');
require_once('../../include/projektarbeit.class.php');
require_once('../../include/projektbetreuer.class.php');
require_once('../../include/lehrverband.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/mail.class.php');
require_once('../../include/kontakt.class.php');
require_once('../../include/dms.class.php');
require_once('../../include/notenschluessel.class.php');
require_once('../../include/anrechnung.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/anwesenheit.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/note.class.php');
require_once('../../include/standort.class.php');
require_once('../../include/adresse.class.php');
require_once('../../include/reihungstest.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/mobilitaet.class.php');
require_once('../../include/studienordnung.class.php');

$user = get_uid();
$db = new basis_db();

$return = false;
$errormsg = 'unknown';
$warning = '';
$data = '';
$error = false;

loadVariables($user);
//Berechtigungen laden
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('mitarbeiter'))
{
	$return = false;
	$errormsg = 'Keine Berechtigung';
	$data = '';
	$error = true;
}

// *** FUNKTIONEN ***

// ****
// * Generiert die Matrikelnummer
// * FORMAT: 0710254001
// * 07 = Jahr
// * 1/2/0  = WS/SS/Incomming
// * 0254 = Studiengangskennzahl vierstellig
// * 001 = Laufende Nummer
// ****
function generateMatrikelnummer($studiengang_kz, $studiensemester_kurzbz)
{
	$db = new basis_db();

	$jahr = substr($studiensemester_kurzbz, 4);
	$art = substr($studiensemester_kurzbz, 0, 2);

	if($studiengang_kz<0)
	{
		$studiengang_kz=abs($studiengang_kz);
		//Lehrgang
		switch($art)
		{
			case 'WS': $art = '3'; break;
			case 'SS': $art = '4'; break;
			default: $art = '0'; break;
		}
	}
	else
	{
		//Studiengang
		switch($art)
		{
			case 'WS': $art = '1'; break;
			case 'SS': $art = '2'; break;
			default: $art = '0'; break;
		}
	}
	if($art=='2' || $art=='4')
		$jahr = $jahr-1;
	$matrikelnummer = sprintf("%02d",$jahr).$art.sprintf("%04d",$studiengang_kz);

	$qry = "SELECT matrikelnr FROM public.tbl_student WHERE matrikelnr LIKE '$matrikelnummer%' ORDER BY matrikelnr DESC LIMIT 1";

	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			$max = substr($row->matrikelnr, (strlen(trim($row->matrikelnr))-3));
		}
		else
			$max = 0;

		$max += 1;
		return $matrikelnummer.sprintf("%03d",$max);
	}
	else
	{
		return false;
	}
}

/**
 * Wenn die Anwesenheit und einen bestimmten Prozentsatz faellt, wird ein Pruefungstermin abgezogen
 * @param $studiensemester_kurzbz
 * @param $student_uid
 * @param $lehrveranstaltung_id
 * @param $note
 * @return null, error wird direkt in globale Variable geschrieben
 */
function NotePruefungAnlegen($studiensemester_kurzbz, $student_uid, $lehrveranstaltung_id, $note, $punkte)
{
	global $return, $error, $errormsg;

	$db = new basis_db();
	$anwesenheit = new anwesenheit();
	$anwesenheit->loadAnwesenheitStudiensemester($studiensemester_kurzbz, $student_uid, $lehrveranstaltung_id);

	// Lehreinheit ermitteln
	$error = false;
	$qry = "
		SELECT
			lehreinheit_id
		FROM
			campus.vw_student_lehrveranstaltung
		WHERE
			uid=".$db->db_add_param($student_uid)."
		 	AND lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id)."
			AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
		ORDER BY lehreinheit_id ASC
		LIMIT 1";

	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			$lehreinheit_id = $row->lehreinheit_id;
		}
		else
		{
			$return = false;
			$error = true;
			$errormsg = 'Fehler beim Ermitteln der Lehreinheit ID';
		}
	}
	else
	{
		$return = false;
		$error = true;
		$errormsg = 'Fehler beim Ermitteln der Lehreinheit ID';
	}

	if(!$error)
	{
		$pruefung = new pruefung;
		$pruefung->new = true;
		$pruefung->student_uid = $student_uid;
		$pruefung->lehreinheit_id = $lehreinheit_id;
		$pruefung->datum = date("Y-m-d");

		$stsem_obj = new studiensemester();
		$stsem_obj->load($studiensemester_kurzbz);

		// In Benutzerfunktion nachsehen ob eine Anwesenheitsbefreiung eingetragen ist
		$benutzerfunktion = new benutzerfunktion();
		$benutzerfunktion->getBenutzerFunktionByUid($student_uid, 'awbefreit', $stsem_obj->start, $stsem_obj->ende);

		$anwesenheitsbefreit=false;
		if(count($benutzerfunktion->result)>0)
			$anwesenheitsbefreit=true;

		$gesamtnote_punkte = defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE;

		// Wenn nicht Anwesenheitsbefreit und Anwesenheit unter einem bestimmten Prozentsatz faellt dann wird ein
		// Pruefungsantritt abgezogen
		if(isset($anwesenheit->result[0]) && $anwesenheit->result[0]->prozent < FAS_ANWESENHEIT_ROT && !$anwesenheitsbefreit)
		{
			// 1. Termin mit "nicht beurteilt" erstellen
			$pruefung->pruefungstyp_kurzbz = "Termin1";
			$pruefung->note = 7;
			if($pruefung->save())
			{
				// 2. Termin mit Note erstellen
				$pruefung->pruefungstyp_kurzbz = "Termin2";
				$pruefung->note = $note;
				if($gesamtnote_punkte)
					$pruefung->punkte = $punkte;
				if($pruefung->save())
				{
					$return = true;
				}
				else
				{
					$errormsg = $pruefung->errormsg;
					$return = false;
				}
			}
			else
			{
				$errormsg = $pruefung->errormsg;
				$return = false;
			}
		}
		else
		{
			// 1. Termin mit Note erstellen
			$pruefung->pruefungstyp_kurzbz = "Termin1";
			$pruefung->note = $note;
			if($gesamtnote_punkte)
				$pruefung->punkte = $punkte;

			if($pruefung->save())
			{
				$return = true;
			}
			else
			{
				$errormsg = $pruefung->errormsg;
				$return = false;
			}
		}
	}
}

function deleteFromSAP($buchung)
{
	global $db, $user;

	if(defined('BUCHUNGEN_CHECK_SAP') && BUCHUNGEN_CHECK_SAP == true)
	{
		$qry = "SELECT * FROM sync.tbl_sap_salesorder WHERE buchungsnr=".$db->db_add_param($buchung->buchungsnr);
		if($result = $db->db_query($qry))
		{
			if($row = $db->db_fetch_object($result))
			{
				$qry = "DELETE FROM sync.tbl_sap_salesorder WHERE buchungsnr=".$db->db_add_param($buchung->buchungsnr);
				if($db->db_query($qry))
				{
					$log = new log();
					$log->mitarbeiter_uid = $user;
					$log->beschreibung = 'Buchungszuordnung SAP geloescht: SalesOrder:'.$row->sap_sales_order_id;
					$log->sql = $qry;
					if(!$log->save(true))
					{
						echo "Fehler beim Schreiben des Log".$log->errormsg;
						return false;
					}
					return true;
				}
				else
				{
					echo "Fehler beim Löschen der Zahlungszuordnung";
					return false;
				}
			}
			else
			{
				//Gegenbuchung
			}
		}
		else
		{
			echo "Zahlung nicht gefunden";
			return false;
		}
	}
	return true;
}

/**
 * Prueft ob die Kontobuchung bereits ins SAP Ubertragen wurde oder noch geändert werden darf
 */
function isBuchungAllowedToChange($buchung_obj)
{
	global $db;

	if(defined('BUCHUNGEN_CHECK_SAP') && BUCHUNGEN_CHECK_SAP == true)
	{
		$qry = "SELECT * FROM sync.tbl_sap_salesorder WHERE buchungsnr=".$db->db_add_param($buchung_obj->buchungsnr);
		if ($buchung_obj->buchungsnr_verweis != '')
			$qry .= " OR buchungsnr=".$db->db_add_param($buchung_obj->buchungsnr_verweis);

		if ($result = $db->db_query($qry))
		{
			if($db->db_num_rows($result) > 0)
			{
				return false;
			}
			else
				return true;
		}
		else
			return false;
	}
	else
		return true;
}

if(!$error)
{

	if(isset($_POST['type']) && $_POST['type']=='savestudent')
	{
		if(!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid'))
		{
			$error = true;
			$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
		}
		//Studentendaten speichern
		if(!$error)
		{
			$student = new student();

			if(!$student->load($_POST['uid']))
			{
				$return = false;
				$errormsg = 'Fehler beim Laden:'.$student->errormsg;
				$error = true;
			}
			$datum = new datum();

			$gebdatum = $_POST['geburtsdatum'];
			if($gebdatum!='' && !$datum->checkDatum($gebdatum))
			{
				$return = false;
				$errormsg = 'Geburtsdatum ist nicht korrekt.';
				$error = true;
			}
			if(!$error)
			{
				$student->uid = $_POST['uid'];
				$student->anrede = $_POST['anrede'];
				$student->titelpre = $_POST['titelpre'];
				$student->titelpost = $_POST['titelpost'];
				$student->vorname = $_POST['vorname'];
				$student->vornamen = $_POST['vornamen'];
				$student->nachname = $_POST['nachname'];
				$student->gebdatum = $_POST['geburtsdatum'];
				$student->gebort = $_POST['geburtsort'];
				$student->gebzeit = $_POST['geburtszeit'];
				$student->anmerkungen = $_POST['anmerkung'];
				$student->homepage = $_POST['homepage'];
				$student->matr_nr = $_POST['matr_nr'];
				$student->svnr = $_POST['svnr'];
				$student->ersatzkennzeichen = $_POST['ersatzkennzeichen'];
				$student->familienstand = $_POST['familienstand'];
				$student->geschlecht = $_POST['geschlecht'];
				$student->bnaktiv = ($_POST['aktiv']=='true'?true:false);
				$student->anzahlkinder = $_POST['anzahlderkinder'];
				$student->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
				$student->geburtsnation = $_POST['geburtsnation'];
				$student->sprache = $_POST['sprache'];
				$student->matrikelnr = $_POST['matrikelnummer'];
				$student->updateamum = date('Y-m-d H:i:s');
				$student->updatevon = $user;

				if($_POST['alias']!='')
				{
					if(checkalias($_POST['alias']))
					{
						$student->alias = $_POST['alias'];
					}
					else
					{
						$error = true;
						$return = false;
						$errormsg = 'Alias ist ungueltig';
					}
				}
				else
					$student->alias = '';

				if(!$error)
				{
					$stsem = new studiensemester();
					$stsem_kurzbz = $stsem->getaktorNext();
					//Wenn das ausgewaehlte Semester das aktuelle ist, dann wird auch in der
					//Tabelle Student der Stg/Semester/Verband/Gruppe geaendert.
					//Sonst nur in der Tabelle Studentlehrverband
					if($semester_aktuell == $stsem_kurzbz)
					{
						$student->studiengang_kz = $_POST['studiengang_kz'];
						$student->semester = $_POST['semester'];
						$student->verband = ($_POST['verband']==''?' ':$_POST['verband']);
						$student->gruppe = ($_POST['gruppe']==''?' ':$_POST['gruppe']);
					}

					$student->new=false;

					$lehrverband = new lehrverband();
					if(!$lehrverband->exists($_POST['studiengang_kz'],$_POST['semester'],$_POST['verband'], $_POST['gruppe']))
					{
						$errormsg = 'Die angegebene Lehrverbandsgruppe existiert nicht!';
						$return = false;
						$error = true;
					}

					if(!$error)
					{
						if($student->save())
						{
							$student_lvb = new student();

							// Studentlehrverband Eintrag nur Speichern, wenn der Student in diesem Studiensemester
							// einen Status besitzt da es sonst beim Bearbeiten von alten Studenten immer in das
							// entsprechende Studiensemester gewechselt werden muss.
							$prestudentobj = new prestudent();
							$prestudentobj->getPrestudentRolle($student->prestudent_id, null, $semester_aktuell);

							if(count($prestudentobj->result)>0)
							{
								if($student_lvb->studentlehrverband_exists($_POST['uid'], $semester_aktuell))
									$student_lvb->new = false;
								else
									$student_lvb->new = true;

								$student_lvb->uid = $_POST['uid'];
								$student_lvb->studiensemester_kurzbz = $semester_aktuell;
								$student_lvb->studiengang_kz = $_POST['studiengang_kz'];
								$student_lvb->semester = $_POST['semester'];
								$student_lvb->verband = ($_POST['verband']==''?' ':$_POST['verband']);
								$student_lvb->gruppe = ($_POST['gruppe']==''?' ':$_POST['gruppe']);
								$student_lvb->updateamum = date('Y-m-d H:i:s');
								$student_lvb->updatevon = $user;

								if($student_lvb->save_studentlehrverband())
								{
									$return = true;
									$error=false;
									$data = $student->prestudent_id;
								}
								else
								{
									$error = true;
									$errormsg = $student_lvb->errormsg;
									$return = false;
								}
							}
							else
							{
								$error = false;
								$return = true;
								$data = $student->prestudent_id;
							}
						}
						else
						{
							$return = false;
							$errormsg  = $student->errormsg;
							$error = true;
						}
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='saveperson')
	{

		if(!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid'))
		{
			$error = true;
			$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
		}
		//Personendaten Speichern

		if(!$error)
		{
			$person = new person();

			if(!$person->load($_POST['person_id']))
			{
				$return = false;
				$errormsg = 'Fehler beim Laden:'.$person->errormsg;
				$error = true;
			}

			if(!$error)
			{
				$person->person_id = $_POST['person_id'];
				$person->anrede = $_POST['anrede'];
				$person->titelpre = $_POST['titelpre'];
				$person->titelpost = $_POST['titelpost'];
				$person->vorname = $_POST['vorname'];
				$person->vornamen = $_POST['vornamen'];
				$person->nachname = $_POST['nachname'];
				$person->gebdatum = $_POST['geburtsdatum'];
				$person->gebort = $_POST['geburtsort'];
				$person->gebzeit = $_POST['geburtszeit'];
				$person->anmerkungen = $_POST['anmerkung'];
				$person->homepage = $_POST['homepage'];
				$person->svnr = $_POST['svnr'];
				$person->ersatzkennzeichen = $_POST['ersatzkennzeichen'];
				$person->familienstand = $_POST['familienstand'];
				$person->geschlecht = $_POST['geschlecht'];
				//$person->aktiv = ($_POST['aktiv']=='true'?true:false);
				$person->anzahlkinder = $_POST['anzahlderkinder'];
				$person->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
				$person->geburtsnation = $_POST['geburtsnation'];
				$person->sprache = $_POST['sprache'];
				$person->matr_nr = $_POST['matr_nr'];
				$person->updateamum = date('Y-m-d H:i:s');
				$person->updatevon = $user;

				$person->new=false;

				if(!$error)
				{
					if($person->save())
					{
						$return = true;
						$error=false;
						$data = $person->person_id;
					}
					else
					{
						$return = false;
						$errormsg  = $person->errormsg;
						$error = true;
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='saveprestudent')
	{
		//PreStudentdaten Speichern
		if(!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid'))
		{
			$error = true;
			$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
		}

		if(!$error)
		{
			$prestudent = new prestudent();

			if(!$prestudent->load($_POST['prestudent_id']))
			{
				$return = false;
				$errormsg = 'Fehler beim Laden:'.$prestudent->errormsg;
				$error = true;
			}

			if(!$error)
			{
				$prestudent->prestudent_id = $_POST['prestudent_id'];
				$prestudent->aufmerksamdurch_kurzbz = $_POST['aufmerksamdurch_kurzbz'];
				$prestudent->person_id = $_POST['person_id'];
				$prestudent->studiengang_kz = $_POST['studiengang_kz'];
				$prestudent->berufstaetigkeit_code = $_POST['berufstaetigkeit_code'];
				$prestudent->ausbildungcode = $_POST['ausbildungcode'];
				// Die Bachelor-ZGV darf nur mit einem eigenen Recht geändert werden
				if($rechte->isBerechtigt('student/editBakkZgv',$_POST['studiengang_kz'],'suid'))
				{
					$prestudent->zgv_code = $_POST['zgv_code'];
				}
				elseif ($prestudent->zgv_code != $_POST['zgv_code'])
				{
					$errormsg = 'Keine Berechtigung zum Ändern der ZGV';
					$error = true;
				}
				$prestudent->zgvort = $_POST['zgvort'];
				$prestudent->zgvdatum = $_POST['zgvdatum'];
				$prestudent->zgvnation = $_POST['zgvnation'];
				$prestudent->zgvmas_code = $_POST['zgvmas_code'];
				$prestudent->zgvmaort = $_POST['zgvmaort'];
				$prestudent->zgvmadatum = $_POST['zgvmadatum'];
				$prestudent->zgvmanation = $_POST['zgvmanation'];
				$prestudent->aufnahmeschluessel = $_POST['aufnahmeschluessel'];
				$prestudent->facheinschlberuf = ($_POST['facheinschlberuf']=='true'?true:false);
				$prestudent->bismelden = ($_POST['bismelden']=='true'?true:false);
				$prestudent->dual = ($_POST['dual']=='true'?true:false);
				$prestudent->anmerkung = $_POST['anmerkung'];
				$prestudent->mentor = $_POST['mentor'];
				$prestudent->gsstudientyp_kurzbz = $_POST['gsstudientyp_kurzbz'];
				$prestudent->priorisierung = $_POST['priorisierung'];
				//$prestudent->insertamum = date('Y-m-d H:i:s');
				//$prestudent->insertvon = $user;
				$prestudent->updateamum = date('Y-m-d H:i:s');
				$prestudent->updatevon = $user;
				$prestudent->new=false;

				if(!$error)
				{
					if($prestudent->save())
					{
						$return = true;
						$error=false;
						$data = $prestudent->prestudent_id;
					}
					else
					{
						$return = false;
						$errormsg  = $prestudent->errormsg;
						$error = true;
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='addrolle')
	{
		//Prestudentrolle hinzufuegen

		if(!$error)
		{
			$prestudent_ids = explode(';', $_POST['prestudent_id']);
			$errormsg='';
			$anzahl_fehler=0;
			foreach($prestudent_ids as $prestudent_id)
			{
				$error = false;
				if($prestudent_id!='')
				{
					$prestd = new prestudent();
					if(!$prestd->load($prestudent_id))
					{
						$error = true;
						$errormsg .= "\n$prestd->vorname $prestd->nachname: Fehler beim Laden des Prestudenten";
						$anzahl_fehler++;
					}
					else
					{
						if(!$rechte->isBerechtigt('assistenz',$prestd->studiengang_kz,'suid') &&
						   !$rechte->isBerechtigt('admin',$prestd->studiengang_kz, 'suid'))
						{
							$error = true;
							$errormsg .= "\n$prestd->vorname $prestd->nachname: Sie haben keine Schreibrechte fuer diesen Studiengang";
							$anzahl_fehler++;
						}
					}

					// Check ob Reihungstest berücksichtigt werden soll
					// kann in Config abgestellt werden
					if(REIHUNGSTEST_CHECK)
					{
						$reihungstestangetreten = $prestd->reihungstestangetreten;

						if($_POST['status_kurzbz']=='Bewerber' && !$reihungstestangetreten)
						{
							$error = true;
							$errormsg .= "\n $prestd->vorname $prestd->nachname: Um einen Interessenten zum Bewerber zu machen, muss die Person das Reihungstestverfahren abgeschlossen haben";
							$anzahl_fehler++;
						}
					}

					if(!defined("ZGV_CHECK") || ZGV_CHECK)
					{
						if($_POST['status_kurzbz']=='Bewerber' && $prestd->zgv_code=='')
						{
							$error = true;
							$errormsg .= "\n $prestd->vorname $prestd->nachname: Um einen Interessenten zum Bewerber zu machen, muss die Zugangsvoraussetzung eingetragen sein.";
							$anzahl_fehler++;
						}
					}

					$stg_obj = new studiengang();
					$stg_obj->load($prestd->studiengang_kz);
					if(!defined("ZGV_CHECK") || ZGV_CHECK)
					{
						if($_POST['status_kurzbz']=='Bewerber' && $prestd->zgvmas_code=='' && $stg_obj->typ=='m')
						{
							$error = true;
							$errormsg .= "\n $prestd->vorname $prestd->nachname: Um einen Interessenten zum Bewerber zu machen, muss die Zugangsvoraussetzung Master eingetragen sein.";
							$anzahl_fehler++;
						}
					}

					if(!$error)
					{

						if($prestd->getLastStatus($prestudent_id))
						{
							if($_POST['status_kurzbz']=='Absolvent' || $_POST['status_kurzbz']=='Diplomand')
								$studiensemester = $semester_aktuell;
							else
								$studiensemester = $prestd->studiensemester_kurzbz;
							$hlp = new prestudent();

							if($_POST['status_kurzbz']=='Student')
								$sem=$_POST['semester'];
							else
								$sem=$prestd->ausbildungssemester;

							$hlp->getPrestudentRolle($prestudent_id, $_POST['status_kurzbz'], $studiensemester, "datum, insertamum", $sem);
							if(count($hlp->result)>0)
							{
								$errormsg .= "\n$prestd->vorname $prestd->nachname: Diese Rolle ist bereits vorhanden";
								$anzahl_fehler++;
								$return = false;
							}
							else
							{
								//Wenn der Status auf Aufgenommener oder Wartender geaendert wird,
								//muss ein Bewerberstatus vorhanden sein
								if($_POST['status_kurzbz']=='Aufgenommener' || $_POST['status_kurzbz']=='Wartender')
								{
									$qry = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id=".$db->db_add_param($prestudent_id, FHC_INTEGER)." AND status_kurzbz='Bewerber'";
									if($result_bw = $db->db_query($qry))
									{
										if($db->db_num_rows($result_bw)==0)
										{
											$return = false;
											$error = true;
											$errormsg .= "\n$prestd->vorname $prestd->nachname: Person muss zuerst zum Bewerber gemacht werden!";
											$anzahl_fehler++;
										}
									}
								}

								if(!$error)
								{
									$prestd_neu = new prestudent();
									$prestd_neu->prestudent_id = $prestudent_id;
									$prestd_neu->status_kurzbz = $_POST['status_kurzbz'];
									$prestd_neu->studiensemester_kurzbz = $studiensemester;
									$prestd_neu->datum = date('Y-m-d');
									$prestd_neu->ausbildungssemester = $sem;
									$prestd_neu->orgform_kurzbz = $prestd->orgform_kurzbz;
									$prestd_neu->studienplan_id = $prestd->studienplan_id;
									$prestd_neu->insertamum = date('Y-m-d H:i:s');
									$prestd_neu->insertvon = $user;
									$prestd_neu->bestaetigtam = date('Y-m-d');
									$prestd_neu->bestaetigtvon = $user;
									if(isset($_POST['statusgrund_id']))
										$prestd_neu->statusgrund_id = $_POST['statusgrund_id'];
									$prestd_neu->new = true;

									if($prestd_neu->save_rolle())
									{
										// wenn kein fehler und status bewerber und "email senden an bewerber" eingestellt ist
										if($_POST['status_kurzbz']=='Bewerber' && SEND_BEWERBER_INFOMAIL)
										{
											// hole Email Adresse
											$kontakt = new kontakt();
											$kontakt->load_persKontakttyp($prestd->person_id, 'email');

											// Wenn zumindest eine Email Adresse gefunden wurde
											if(count($kontakt->result)>0)
											{
												$email = "Sehr geehrter Frau/Herr ".$prestd->vorname." ".$prestd->nachname.",<br><br> ";

												$email.= INFOMAIL_BEWERBER;
												foreach($kontakt->result as $k)
												{
													$mail = new mail($k->kontakt, 'no-reply', 'Infomail', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');

													$mail->setHTMLContent($email);
													if(!$mail->send())
													{
														$errormsg= 'Fehler beim Senden des Mails!';
														$anzahl_fehler++;
													}
												}
											}
										}

										//Unterbrecher und Abbrecher werden ins 0. Semester verschoben
										if($_POST['status_kurzbz']=='Unterbrecher' || $_POST['status_kurzbz']=='Abbrecher')
										{
											$student = new student();
											$uid = $student->getUid($prestudent_id);
											$student->load($uid);
											$student->studiensemester_kurzbz=$studiensemester;
											$student->semester = '0';
											if($_POST['status_kurzbz']=='Abbrecher')
											{
												$student->verband='A';
												$student->gruppe='';
											}
											if($_POST['status_kurzbz']=='Unterbrecher')
											{
												$student->verband='B';
												$student->gruppe='';
											}

											// noch nicht eingetragene Noten ergänzen
											$noten = new zeugnisnote();
											$noten->getZeugnisnoten(null, $uid, $semester_aktuell);
											foreach($noten->result as $obj)
											{
												if($obj->note == '')
												{
													$obj->note = 9;
													$obj->save(true);
												}
											}

											//Nachschauen ob dieser Lehrverband schon existiert, falls nicht dann anlegen
											$lehrverband = new lehrverband();
											if(!$lehrverband->exists($student->studiengang_kz, $student->semester, $student->verband, ''))
											{
												//Pruefen ob der uebergeordnete Lehrverband existiert, falls nicht dann anlegen
												if(!$lehrverband->exists($student->studiengang_kz, $student->semester, '', ''))
												{
													$lehrverband->studiengang_kz = $student->studiengang_kz;
													$lehrverband->semester = $student->semester;
													$lehrverband->verband = '';
													$lehrverband->gruppe = '';
													$lehrverband->aktiv = true;
													$lehrverband->bezeichnung = 'Ab-Unterbrecher';

													$lehrverband->save(true);
												}

												$lehrverband->studiengang_kz = $student->studiengang_kz;
												$lehrverband->semester = $student->semester;
												$lehrverband->verband = $student->verband;
												$lehrverband->gruppe = '';
												$lehrverband->aktiv = true;
												if($student->verband=='A')
													$lehrverband->bezeichnung = 'Abbrecher';
												else
													$lehrverband->bezeichnung = 'Unterbrecher';

												$lehrverband->save(true);
											}
											$student->updatevon = $user;
											$student->updateamum = date('Y-m-d H:i:s');
											//Student Speichern
											$student->save(false, false);
											//Studentlehrverband Eintrag Speichern
											$student->save_studentlehrverband(false);
										}

										//Wenn Unterbrecher zu Studenten werden, dann wird das Semester mituebergeben
										//Verband und Gruppe wird entfernt.
										if($_POST['status_kurzbz']=='Student')
										{
											$student = new student();
											$uid = $student->getUid($prestudent_id);
											$student->load($uid);
											$student->studiensemester_kurzbz=$semester_aktuell;
											$student->semester = $_POST['semester'];
											$student->verband = '';
											$student->gruppe = '';
											$student->save(false, false);
											$student->save_studentlehrverband(false);
											//Aktiv Status setzen
											$benutzer = new benutzer();
											if($benutzer->load($uid))
											{
												$benutzer->updateamum = date('Y-m-d H:i:s');
												$benutzer->updatevon = $user;
												$benutzer->bnaktiv=true;
												$benutzer->save(false, false);
											}
										}

										//bei Abbrechern und Absolventen wird der Aktiv Status auf false gesetzt
										if($_POST['status_kurzbz']=='Abbrecher' || $_POST['status_kurzbz']=='Absolvent')
										{
											$student = new student();
											$uid = $student->getUid($prestudent_id);

											$benutzer = new benutzer();
											if($benutzer->load($uid))
											{
												$benutzer->updateamum = date('Y-m-d H:i:s');
												$benutzer->updatevon = $user;
												$benutzer->bnaktiv=false;
												$benutzer->save(false, false);
											}
										}
										$return = true;
									}
									else
									{
										$return = false;
										$errormsg = "\n$prestd->vorname $prestd->nachname: ".$prestd_neu->errormsg;
										$anzahl_fehler++;
									}
								}
							}
						}
						else
						{
							$return = false;
							$errormsg .= "\n$prestd->vorname $prestd->nachname: Es ist keine Rolle fuer diesen Prestudent vorhanden";
							$anzahl_fehler++;
						}
					}
				}
			}

			if($anzahl_fehler>0)
			{
				$return = false;
				$errormsg = "Es sind $anzahl_fehler Fehler aufgetreten: ".$errormsg;
			}
			else
			{
				$return = true;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deleterolle')
	{
		//Loescht eine Prestudentrolle
		$deletePrestudent = false;

		if(isset($_POST['studiensemester_kurzbz']) && isset($_POST['status_kurzbz']) &&
		   isset($_POST['prestudent_id']) && is_numeric($_POST['prestudent_id']) &&
		   isset($_POST['ausbildungssemester']) && is_numeric($_POST['ausbildungssemester']))
		{
			if($_POST['status_kurzbz']=='Student' && !$rechte->isBerechtigt('admin', null, 'suid'))
			{
				$return = false;
				$errormsg = 'Studentenrolle kann nur durch den Administrator geloescht werden';
			}
			else
			{
				$qry = "SELECT count(*) as anzahl FROM public.tbl_prestudentstatus WHERE prestudent_id=".$db->db_add_param($_POST['prestudent_id'], FHC_INTEGER);
				if($result = $db->db_query($qry))
				{
					if($row = $db->db_fetch_object($result))
					{
						// Die letzte Rolle darf nur durch einen Administrator geloescht werden. Danach wird auch der PreStudent-Eintrag geloescht
						if($row->anzahl<=1)
						{
							$deletePrestudent = true;
							if (!$rechte->isBerechtigt('admin', null, 'suid'))
							{
								$return = false;
								$errormsg = 'Die letzte Rolle kann nur durch den Administrator geloescht werden';
								$error = true;
							}
						}
					}
					else
					{
						$return = false;
						$errormsg = 'Fehler beim Ermitteln der Rollen';
						$error = true;
					}
				}
				else
				{
					$return = false;
					$error = true;
					$errormsg = 'Fehler beim Ermitteln der Rollen';
				}

				if(!$error)
				{
					$rolle = new prestudent();
					if($rolle->load_rolle($_POST['prestudent_id'],$_POST['status_kurzbz'],$_POST['studiensemester_kurzbz'], $_POST['ausbildungssemester']))
					{
						if($rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') || $rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
						{
							if($rolle->delete_rolle($_POST['prestudent_id'],$_POST['status_kurzbz'],$_POST['studiensemester_kurzbz'], $_POST['ausbildungssemester']))
							{
								$log_id = $rolle->log_id;
								$return = true;
								// Wenn in diesem Semester kein Status mehr vorhanden ist,
								// dann wird auch der Studentlehrverband eintrag entfernt
								if($rolle->getLastStatus($_POST['prestudent_id'],$_POST['studiensemester_kurzbz'])===false)
								{
									if($return)
									{
										$student = new student();
										$temp_uid = $student->getUid($rolle->prestudent_id);
										if(!$student->delete_studentLehrverband($temp_uid, $_POST['studiengang_kz'], $rolle->studiensemester_kurzbz, $rolle->ausbildungssemester))
										{
											$return = false;
											$errormsg = "Fehler beim Löschen der Lehrverbandszuordnung.";
										}
									}
								}
								// Wenn kein Prestudent-Eintrag mehr vorhanden ist, wird der ganze Prestudent-Datensatz geloescht
								if ($deletePrestudent == true)
								{
									$prestudent = new prestudent();
									if(!$prestudent->deletePrestudent($_POST['prestudent_id']))
									{
										$return = false;
										$errormsg = "Fehler beim Löschen des Prestudenten.";
										$log = new log();
										$log->undo($log_id);
									}
								}
							}
							else
							{
								$return = false;
								$errormsg = $rolle->errormsg;
							}
						}
						else
						{
							$return = false;
							$errormsg = 'Sie haben keine Berechtigung zum Loeschen dieser Rolle';
						}
					}
					else
					{
						$return = false;
						$errormsg = $rolle->errormsg;
					}
				}
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='returnDeletePrestudent')
	{
		//Prueft, wie viele Prestudentrolle-Eintraege noch vorhanden sind und gibt diese Anzahl als Wert zurueck

		if(isset($_POST['prestudent_id']) && is_numeric($_POST['prestudent_id']))
		{
			$qry = "SELECT count(*) as anzahl FROM public.tbl_prestudentstatus WHERE prestudent_id=".$db->db_add_param($_POST['prestudent_id'], FHC_INTEGER);
			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))
				{
					$return = true;
					$data = $row->anzahl;
				}
				else
				{
					$return = false;
					$errormsg = 'Fehler beim Zaehlen der Rollen';
					$error = true;
				}
			}
			else
			{
				$return = false;
				$error = true;
				$errormsg = 'Fehler beim Zaehlen der Rollen';
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='bestaetigerolle')
	{
		//Bestaetigt eine Prestudentrolle

		if(isset($_POST['studiensemester_kurzbz']) && isset($_POST['status_kurzbz']) &&
		   isset($_POST['prestudent_id']) && is_numeric($_POST['prestudent_id']) &&
		   isset($_POST['ausbildungssemester']) && is_numeric($_POST['ausbildungssemester']))
		{
			if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') && !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
			{
				$return = false;
				$errormsg = 'keine Berechtigung';
			}
			else
			{
				$rolle = new prestudent();
				if($rolle->load_rolle($_POST['prestudent_id'],$_POST['status_kurzbz'],$_POST['studiensemester_kurzbz'], $_POST['ausbildungssemester']))
				{
					if($rolle->bestaetige_rolle($_POST['prestudent_id'],$_POST['status_kurzbz'],$_POST['studiensemester_kurzbz'], $_POST['ausbildungssemester'],$user))
					{
						// Message verschicken
						$prestudent = new prestudent();
						$prestudent->load($_POST['prestudent_id']);

						$url =APP_ROOT.'index.ci.php/api/v1/system/Message/MessageVorlage';
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL,$url);
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

						$header=array();
						$auth = base64_encode(FHC_REST_USER.':'.FHC_REST_PASSWORD);
						$header[]="Authorization: Basic $auth";
						$header[]='FHC-API-KEY:'.FHC_REST_API_KEY;

						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
						$studiengang = new studiengang();
						$studiengang->load($prestudent->studiengang_kz);
						$studiengang->getAllTypes();

						$orgform='';
						if($rolle->studienplan_id!='')
						{
							$studienplan = new studienplan();
							$studienplan->loadStudienplan($rolle->studienplan_id);
							$orgform = $studienplan->orgform_kurzbz;
						}
						else
							$orgform = $rolle->orgform_kurzbz;
						$curl_post_data = array(
						"sender_id" => 1, // TODO
						"receiver_id" => $prestudent->person_id,
						"oe_kurzbz" => $studiengang->oe_kurzbz,
						"vorlage_kurzbz" => 'MailStatConfirm'.$_POST['status_kurzbz'],
						"data" => array(
							'anrede'=>$prestudent->anrede,
							'vorname'=>$prestudent->vorname,
							'nachname'=>$prestudent->nachname,
							'typ'=>$studiengang->studiengang_typ_arr[$studiengang->typ],
							'studiengang'=>$studiengang->bezeichnung,
							'orgform'=>$orgform,
							'stgMail'=>$studiengang->email
						 )
						);

						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curl_post_data));

						$result = curl_exec($ch);
						$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
						curl_close($ch);

						$jsonresult = json_decode($result);
						if(isset($jsonresult->error) && $jsonresult->error==1)
						{
							$return = false;
							$errormsg = $jsonresult->retval;
						}
						else
						{
							$return = true;
						}
					}
					else
					{
						$return = false;
						$errormsg = $rolle->errormsg;
					}
				}
				else
				{
					$return = false;
					$errormsg = $rolle->errormsg;
				}
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='saverolle')
	{
		//Prestudentrolle speichern
		if(!$error)
		{
			if(isset($_POST['prestudent_id']))
			{
				$rolle = new prestudent();
				if(!$rolle->load($_POST['prestudent_id']))
				{
					$error = true;
					$errormsg = 'Prestudent wurde nicht gefunden';
				}
				else
				{
					//Berechtigung pruefen
					if(!$rechte->isBerechtigt('assistenz',$rolle->studiengang_kz,'suid') &&
					   !$rechte->isBerechtigt('admin',$rolle->studiengang_kz, 'suid'))
					{
						$error = true;
						$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
					}
				}

				$rolle = new prestudent();
				$rolle->prestudent_id = $_POST['prestudent_id'];

				if(!$error)
				{
					if(($_POST['studiensemester_old']=='') || (!$rolle->load_rolle($_POST['prestudent_id'], $_POST['status_kurzbz'], $_POST['studiensemester_old'], $_POST['ausbildungssemester_old'])))
					{
						$rolle->new = true;
						$rolle->insertamum = date('Y-m-d H:i:s');
						$rolle->insertvon = $user;
						$rolle->status_kurzbz = $_POST['status_kurzbz'];
						$rolle->bestaetigtam = date('Y-m-d');
						$rolle->bestaetigtvon = $user;

						if($_POST['status_kurzbz']=='Student')
						{
							//Die Rolle Student darf nur eingefuegt werden, wenn schon eine Studentenrolle vorhanden ist
							$qry = "SELECT count(*) as anzahl FROM public.tbl_student WHERE prestudent_id=".$db->db_add_param($_POST['prestudent_id'], FHC_INTEGER);
							if($result = $db->db_query($qry))
							{
								if($row = $db->db_fetch_object($result))
								{
									if($row->anzahl==0)
									{
										$error = true;
										$errormsg = 'Ein Studentenstatus kann hier nur hinzugefuegt werden wenn die Person bereits Student ist. Um einen Bewerber zum Studenten zu machen waehlen Sie bitte unter "Status aendern" den Punkt "Student".';
										$return = false;
									}
								}
							}
						}
					}
					else
					{
						$rolle->ausbildungssemester_old = $_POST['ausbildungssemester_old'];
						$rolle->studiensemester_old = $_POST['studiensemester_old'];
						$rolle->updateamum = date('Y-m-d H:i:s');
						$rolle->updatevon = $user;
						$rolle->new = false;
					}

					if(!$error)
					{
						// Bei Studenten wird der Studentlehrverband Eintrag angelegt/korrigiert
						$student = new student();
						if($temp_uid = $student->getUid($rolle->prestudent_id))
						{
							if(in_array($rolle->status_kurzbz,array('Student','Diplomand','Absovlent','Incoming','Abbrecher','Unterbrecher')))
							{
								if($student->load($temp_uid))
								{
									$stdsem_new = filter_input(INPUT_POST, "studiensemester_kurzbz");
									$semester = filter_input(INPUT_POST, "ausbildungssemester");

									if($student->load_studentlehrverband($temp_uid, $stdsem_new))
										$student->new=false;
									else
										$student->new=true;

									$lehrverband = new lehrverband();
									if(!$lehrverband->exists($student->studiengang_kz, $semester, $student->verband, $student->gruppe))
									{
										$student->studiensemester_kurzbz = $stdsem_new;
										$return = false;
										$errormsg = $student->errormsg;
									}
									else
									{
										$student->studiensemester_kurzbz = $stdsem_new;
										$student->semester = $semester;
										$student->updatevon = $user;
									}

									$student->save_studentlehrverband();
								}
							}
						}

						$rolle->ausbildungssemester = $_POST['ausbildungssemester'];
						$rolle->studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
						$rolle->datum = $_POST['datum'];
						$rolle->bestaetigtam = $_POST['bestaetigtam'];
						if($_POST['bestaetigtam']=='')
							$rolle->bestaetigtvon = null;

						$rolle->bewerbung_abgeschicktamum = $_POST['bewerbung_abgeschicktamum'];
						$rolle->orgform_kurzbz = $_POST['orgform_kurzbz'];
						$rolle->studienplan_id = $_POST['studienplan_id'];
						$rolle->anmerkung_status = $_POST['anmerkung'];
						$rolle->rt_stufe = $_POST['rt_stufe'];
						$rolle->statusgrund_id = $_POST['statusgrund_id'];

						if($rolle->save_rolle())
							$return = true;
						else
						{
							$return = false;
							$errormsg = $rolle->errormsg;
						}
					}
				}
			}
			else
			{
				$return = false;
				$errormsg = 'Prestudent_id muss angegeben werden';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='rolleVorruecken')
	{
		$errormsg='';
		//Prestudentrolle speichern
		if(!$error)
		{
			if(isset($_POST['prestudent_id']))
			{
				$rolle = new prestudent();
				if(!$rolle->load($_POST['prestudent_id']))
				{
					$error = true;
					$errormsg = 'Prestudent wurde nicht gefunden';
				}
				else
				{
					//Berechtigung pruefen
					if(!$rechte->isBerechtigt('assistenz',$rolle->studiengang_kz,'suid') &&
					   !$rechte->isBerechtigt('admin',$rolle->studiengang_kz, 'suid'))
					{
						$error = true;
						$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
					}
				}

				$rolle = new prestudent();
				$rolle->prestudent_id = $_POST['prestudent_id'];

				if(!$error)
				{
					$studiensem = new studiensemester();
					$stdsem = $studiensem->getNextFrom($_POST["studiensemester_kurzbz"]);
//					$stdsem = ($stdesm==false ? $_POST["studiensemester_kurzbz"]:$stdsem);
					$semester = $_POST["ausbildungssemester"];
					$semester++;
					if((!$rolle->load_rolle($_POST['prestudent_id'], $_POST['status_kurzbz'], $stdsem, $semester)))
					{
						$rolle->new = true;
						$rolle->insertamum = date('Y-m-d H:i:s');
						$rolle->insertvon = $user;
						$rolle->status_kurzbz = $_POST['status_kurzbz'];
						$rolle->bestaetigtam = date('Y-m-d');
						$rolle->bestaetigtvon = $user;

						if($_POST['status_kurzbz']=='Student')
						{
							//Die Rolle Student darf nur eingefuegt werden, wenn schon eine Studentenrolle vorhanden ist
							$qry = "SELECT count(*) as anzahl FROM public.tbl_student WHERE prestudent_id=".$db->db_add_param($_POST['prestudent_id'], FHC_INTEGER);
							if($result = $db->db_query($qry))
							{
								if($row = $db->db_fetch_object($result))
								{
									if($row->anzahl==0)
									{
										$error = true;
										$errormsg = 'Ein Studentenstatus kann hier nur hinzugefuegt werden wenn die Person bereits Student ist. Um einen Bewerber zum Studenten zu machen waehlen Sie bitte unter "Status aendern" den Punkt "Student".';
										$return = false;
									}
								}
							}
						}
					}
					else
					{
						//Rolle existiert bereits
						$error = true;
						$errormsg = "Rolle existiert bereits.";
						$return = false;
					}

					$student = new student();
					$temp_uid = $student->getUid($rolle->prestudent_id);

					if(!$error)
					{
						$student->load_studentlehrverband($temp_uid, $_POST["studiensemester_kurzbz"]);
						$lehrverband = new lehrverband();
						if(!$lehrverband->exists($student->studiengang_kz, $semester, $student->verband, $student->gruppe))
						{
							$student->studiensemester_kurzbz = $stdsem;
							$return = false;
							$errormsg = $student->errormsg;
						}
						else
						{
							$student->studiensemester_kurzbz = $stdsem;
							$student->semester = $semester;
						}

						$student->save_studentlehrverband(true);
						$rolle->ausbildungssemester = $semester;
						$rolle->studiensemester_kurzbz = $stdsem;
						$rolle->datum = date("Y-m-d");
						$rolle->orgform_kurzbz = $_POST['orgform_kurzbz'];
						$rolle->studienplan_id = $_POST['studienplan_id'];

						if($rolle->save_rolle())
						{
							$return = true;
						}
						else
						{
							$return = false;
							$errormsg = $rolle->errormsg;
						}
					}
				}
			}
			else
			{
				$return = false;
				$errormsg = 'Prestudent_id muss angegeben werden';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='BewerberZuStudent')
	{
		// macht aus einem Bewerber einen Studenten
		// Voraussetzungen:
		// - ZGV muss ausgefuellt sein (bei Master beide)
		// - Kaution muss bezahlt sein
		// - Rolle Bewerber muss existieren
		// Wenn die Voraussetzungen erfuellt sind, dann wird die Matrikelnr
		// und UID generiert und der Studentendatensatz angelegt.
		$errormsg='';
		if(!$error)
		{
			if(isset($_POST['prestudent_id']))
			{
				$prestudent_ids = explode(';', $_POST['prestudent_id']);
				$anzahl_fehler=0;
				foreach ($prestudent_ids as $prestudent_id)
				{
					$error = false;
					if($prestudent_id!='')
					{

						$prestd = new prestudent();
						if($prestd->load($prestudent_id))
						{
							//Berechtigung pruefen
							if(!$rechte->isBerechtigt('assistenz',$prestd->studiengang_kz,'suid') &&
							   !$rechte->isBerechtigt('admin',$prestd->studiengang_kz, 'suid'))
							{
								$error = true;
								$errormsg .= "\n$prestd->vorname $prestd->nachname: Sie haben keine Schreibrechte fuer diesen Studiengang";
								$anzahl_fehler++;
							}

							if(!$error)
							{
								if((!defined("ZGV_CHECK") && $prestd->zgv_code!='') || (defined("ZGV_CHECK") && ZGV_CHECK == false) || (defined("ZGV_CHECK") && ZGV_CHECK == true && $prestd->zgv_code!=''))
								{
									$stg = new studiengang();
									$stg->load($prestd->studiengang_kz);

									if((defined("ZGV_CHECK") && ZGV_CHECK == true && $stg->typ=='m' && $prestd->zgvmas_code=='') || (!defined("ZGV_CHECK") && $stg->typ=='m' && $prestd->zgvmas_code==''))
									{
										$return = false;
										$errormsg .= "\n$prestd->vorname $prestd->nachname: ZGV Master muss eingegeben werden";
										$anzahl_fehler++;
									}
									else
									{
										//Pruefen ob die Rolle Bewerber existiert
										$hlp = new prestudent();
										$hlp->getPrestudentRolle($prestudent_id, 'Bewerber',null,'datum DESC, insertamum DESC');

										if(count($hlp->result)>0)
										{
											$aufgenommener = new prestudent();
											$aufgenommener ->getPrestudentRolle($prestudent_id, 'Aufgenommener', null,'datum DESC, insertamum DESC');

											if(count($aufgenommener->result)>0)
											{
												if($hlp->result[0]->studiensemester_kurzbz==$aufgenommener->result[0]->studiensemester_kurzbz
												   && $hlp->result[0]->ausbildungssemester==$aufgenommener->result[0]->ausbildungssemester)
												{
													//pruefen ob schon eine Studentenrolle Existiert
													$hlp1 = new prestudent();
													$hlp1->getPrestudentRolle($prestudent_id, 'Student', $aufgenommener->result[0]->studiensemester_kurzbz);
													if(count($hlp1->result)>0)
													{
														$return = false;
														$errormsg .= "\n$prestd->vorname $prestd->nachname: Diese Person ist bereits Student";
														$anzahl_fehler++;
													}
													else
													{
														$db->db_query('BEGIN;');

														//Matrikelnummer und UID generieren
														$matrikelnr = generateMatrikelnummer($prestd->studiengang_kz, $aufgenommener->result[0]->studiensemester_kurzbz);
														$jahr = substr($matrikelnr,0, 2);
														$stg = $prestd->studiengang_kz;
														$stg_obj = new studiengang();
														$stg_obj->load(ltrim($stg,'0'));
														$uid = generateUID($stg_obj->kurzbz,$jahr,$stg_obj->typ,$matrikelnr);
														$matrikelnummer = generateMatrikelnr($stg_obj->oe_kurzbz);

														if($matrikelnummer != null)
														{
															$qry = "UPDATE public.tbl_person SET matr_nr=".$db->db_add_param($matrikelnummer)." WHERE person_id=".$db->db_add_param($prestd->person_id, FHC_INTEGER).' AND matr_nr is null';
															$db->db_query($qry);
														}

														if(defined('SET_UID_AS_PERSONENKENNZEICHEN') && SET_UID_AS_PERSONENKENNZEICHEN)
														{
															$matrikelnr = $uid;
														}

														if(defined('SET_UID_AS_MATRIKELNUMMER') && SET_UID_AS_MATRIKELNUMMER)
														{
															$qry = "UPDATE public.tbl_person SET matr_nr=".$db->db_add_param($uid)." WHERE person_id=".$db->db_add_param($prestd->person_id, FHC_INTEGER).' AND matr_nr is null';
															$db->db_query($qry);
														}
														//Benutzerdatensatz anlegen
														$benutzer = new benutzer();
														$benutzer->uid = $uid;
														$benutzer->person_id = $prestd->person_id;
														$benutzer->aktiv = true;
														$benutzer->aktivierungscode = generateActivationKey();

														$nachname_clean = mb_strtolower(convertProblemChars($prestd->nachname));
														$vorname_clean = mb_strtolower(convertProblemChars($prestd->vorname));
														$nachname_clean = str_replace(' ','_', $nachname_clean);
														$vorname_clean = str_replace(' ','_', $vorname_clean);

														if(!defined('GENERATE_ALIAS_STUDENT') || GENERATE_ALIAS_STUDENT===true)
														{
															$qry_alias = "SELECT * FROM public.tbl_benutzer WHERE alias=LOWER(".$db->db_add_param($vorname_clean.".".$nachname_clean).")";
															$result_alias = $db->db_query($qry_alias);
															if($db->db_num_rows($result_alias)==0)
																$benutzer->alias = $vorname_clean.'.'.$nachname_clean;
															else
																$benutzer->alias = '';
														}
														else
															$benutzer->alias='';

														$benutzer->insertamum = date('Y-m-d H:i:s');
														$benutzer->insertvon = $user;

														if($benutzer->save(true, false))
														{
															//Studentendatensatz anlegen
															$student = new student();
															$student->uid = $uid;
															$student->matrikelnr = $matrikelnr;
															$student->prestudent_id = $prestd->prestudent_id;
															$student->studiengang_kz = $prestd->studiengang_kz;
															$student->semester = $aufgenommener->result[0]->ausbildungssemester;
															$student->verband = ' ';
															$student->gruppe = ' ';
															$student->insertamum = date('Y-m-d H:i:s');
															$student->insertvon = $user;

															//Pruefen ob der Lehrverband exisitert, falls nicht dann anlegen
															$lehrverband = new lehrverband();
															if(!$lehrverband->load($student->studiengang_kz, $student->semester, $student->verband, $student->gruppe))
															{
																$lehrverband->studiengang_kz = $student->studiengang_kz;
																$lehrverband->semester = $student->semester;
																$lehrverband->verband = $student->verband;
																$lehrverband->gruppe = $student->gruppe;
																$lehrverband->aktiv = true;
																if(!$lehrverband->save(true))
																{
																	$error = true;
																	$errormsg = 'Fehler beim Speichern des Lehrverbandes';
																	$return = false;
																}
															}

															if(!$error)
															{
																if($student->save(true, false))
																{
																	//Prestudentrolle hinzugfuegen
																	$rolle = new prestudent();
																	$rolle->prestudent_id = $prestd->prestudent_id;
																	$rolle->status_kurzbz = 'Student';
																	$rolle->studiensemester_kurzbz = $aufgenommener->result[0]->studiensemester_kurzbz;
																	$rolle->ausbildungssemester = $aufgenommener->result[0]->ausbildungssemester;
																	$rolle->orgform_kurzbz = $aufgenommener->result[0]->orgform_kurzbz;
																	$rolle->studienplan_id = $aufgenommener->result[0]->studienplan_id;
																	$rolle->datum = date('Y-m-d');
																	$rolle->insertamum = date('Y-m-d H:i:s');
																	$rolle->insertvon = $user;
																	$rolle->bestaetigtam = date('Y-m-d');
																	$rolle->bestaetigtvon = $user;
																	if(isset($_POST['statusgrund_id']))
																		$rolle->statusgrund_id = $_POST['statusgrund_id'];
																	$rolle->new = true;

																	if($rolle->save_rolle())
																	{
																		//StudentLehrverband anlegen
																		$studentlehrverband = new student();
																		$studentlehrverband->uid = $uid;
																		$studentlehrverband->studiensemester_kurzbz = $aufgenommener->result[0]->studiensemester_kurzbz;
																		$studentlehrverband->studiengang_kz = $prestd->studiengang_kz;
																		$studentlehrverband->semester = $aufgenommener->result[0]->ausbildungssemester;
																		$studentlehrverband->verband = ' ';
																		$studentlehrverband->gruppe = ' ';
																		$studentlehrverband->insertamum = date('Y-m-d H:i:s');
																		$studentlehrverband->insertvon = $user;

																		if($studentlehrverband->save_studentlehrverband(true))
																		{
																			$return = true;
																			$db->db_query('COMMIT;');
																		}
																		else
																		{
																			$errormsg .= "\n$prestd->vorname $prestd->nachname: Fehler beim Speichern des Studentlehrverbandes: ".$studentlehrverband->errormsg;
																			$return = false;
																			$anzahl_fehler++;
																			$db->db_query('ROLLBACK;');
																		}
																	}
																	else
																	{
																		$errormsg .= "\n$prestd->vorname $prestd->nachname: Fehler beim Speichern des Rolle: ".$rolle->errormsg;
																		$return = false;
																		$anzahl_fehler++;
																		$db->db_query('ROLLBACK;');
																	}
																}
																else
																{
																	$errormsg .= "\n$prestd->vorname $prestd->nachname: Fehler beim Speichern des Studenten: ".$student->errormsg;
																	$return = false;
																	$anzahl_fehler++;
																	$db->db_query('ROLLBACK;');
																}
															}
														}
														else
														{
															$errormsg .= "\n$prestd->vorname $prestd->nachname $matrikelnr: Fehler beim Speichern des Benutzers: ".$benutzer->errormsg;
															$return = false;
															$anzahl_fehler++;
															$db->db_query('ROLLBACK;');
														}
													}
												}
												else
												{
													$return = false;
													$errormsg .= "\n$prestd->vorname $prestd->nachname: Das Studiensemester oder Ausbildungsemester des Berwerberstatus und des Aufgenommenenstatus passen nicht überein";
													$anzahl_fehler++;
												}
											}
											else
											{
												$return = false;
												$errormsg .= "\n$prestd->vorname $prestd->nachname: Die Person muss zuerst Aufgenommener sein bevor Sie zum Studenten gemacht werden kann";
												$anzahl_fehler++;
											}
										}
										else
										{
											$return = false;
											$errormsg .= "\n$prestd->vorname $prestd->nachname: Die Person muss zuerst Bewerber sein bevor Sie zum Studenten gemacht werden kann";
											$anzahl_fehler++;
										}
									}
								}
								else
								{
									$return = false;
									$errormsg .= "\n$prestd->vorname $prestd->nachname: ZGV muss eingegeben werden";
									$anzahl_fehler++;
								}
							}
						}
						else
						{
							$return = false;
							$errormsg .= "\nPrestudent wurde nicht gefunden zu $prestudent_id";
						}
					}
				}
			}
			else
			{
				$return = false;
				$errormsg = 'Prestudent_id muss angegeben werden';
			}

			if($anzahl_fehler>0)
			{
				$return = false;
				$errormsg = "Es sind $anzahl_fehler Fehler aufgetreten: ".$errormsg;
			}
			else
			{
				$return = true;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='gruppenzuteilung')
	{
		if(isset($_POST['uid']) && isset($_POST['gruppe_kurzbz']))
		{
			$gruppe = new gruppe();
			if(!$gruppe->load($_POST['gruppe_kurzbz']))
			{
				$error = true;
				$errormsg='Gruppe wurde nicht gefunden';
			}
			else
			{
				//Berechtigung pruefen
				if(!$rechte->isBerechtigt('assistenz',$gruppe->studiengang_kz,'suid') &&
				   !$rechte->isBerechtigt('admin',$gruppe->studiengang_kz, 'suid'))
				{
					$error = true;
					$errormsg = 'Sie haben keine Schreibrechte fuer diese Gruppe';
				}
			}
			$benutzergruppe = new benutzergruppe();

			$uids = explode(';',$_POST['uid']);
			$errormsg = '';
			foreach ($uids as $uid)
			{
				if($uid!='')
				{
					if($_POST['gruppe_kurzbz']!='')
					{
						//Zuteilung zu einer Spezialgruppe

						if(!$benutzergruppe->load($uid, $_POST['gruppe_kurzbz']))
						{
							$benutzergruppe->uid = $uid;
							$benutzergruppe->gruppe_kurzbz = $_POST['gruppe_kurzbz'];
							$benutzergruppe->studiensemester_kurzbz = $semester_aktuell;
							$benutzergruppe->insertamum = date('Y-m-d H:i:s');
							$benutzergruppe->insertvon = $user;
							$benutzergruppe->new = true;

							if(!$benutzergruppe->save())
							{
								$errormsg .= "$uid konnte nicht hinzugefuegt werden\n";
							}
						}
						else
							$errormsg .= "Der Student $uid ist bereits im $benutzergruppe->studiensemester_kurzbz dieser Gruppe zugeteilt. Entfernen Sie vorher diese Zuteilung.\n";
					}
					else
					{
						//Zuteilung zu einer Lehrverbandsgruppe
						$error = false;
						$stsem = new studiensemester();
						$stsem_kurzbz = $stsem->getaktorNext();

						//Schauen ob die Lehrverbandsgruppe existiert
						$lehrverband = new lehrverband();
						if(!$lehrverband->exists($_POST['stg_kz'],$_POST['semester'],$_POST['verband'], $_POST['gruppe']))
						{
							$errormsg .= 'Die angegebene Lehrverbandsgruppe existiert nicht!';
							$return = false;
							$error = true;
						}

						//Wenn das ausgewaehlte Semester das aktuelle ist, dann wird auch in der
						//Tabelle Student der Stg/Semester/Verband/Gruppe geaendert.
						//Sonst nur in der Tabelle Studentlehrverband
						if($semester_aktuell == $stsem_kurzbz)
						{
							//Eintrag in der Tabelle Student aendern
							$student = new student();

							if(!$student->load($uid))
							{
								$errormsg .= 'Fehler beim Laden des Studenten';
								$error = true;
								$return = false;
							}

							if($student->studiengang_kz!=$_POST['stg_kz'])
							{
								$errormsg.='Ein Student kann nicht in eine Lehrverbandsgruppe eines anderen Studienganges verschoben werden';
								$return = false;
								$error = true;
							}

							if(!$error)
							{
								$student->studiengang_kz = $_POST['stg_kz'];
								$student->semester = $_POST['semester'];
								$student->verband = ($_POST['verband']==''?' ':$_POST['verband']);
								$student->gruppe = ($_POST['gruppe']==''?' ':$_POST['gruppe']);
								$student->new=false;

								if(!$student->save())
								{
									$errormsg .= 'Fehler beim Speichern des Studenteneintrages';
									$return = false;
									$error = true;
								}
							}
						}

						if(!$error)
						{
							//Eintrag in der Tabelle Student aendern
							$student = new student();

							if(!$student->load($uid))
							{
								$errormsg .= 'Fehler beim Laden des Studenten';
								$error = true;
								$return = false;
							}

							if($student->studiengang_kz!=$_POST['stg_kz'])
							{
								$errormsg.='Ein Student kann nicht in eine Lehrverbandsgruppe eines anderen Studienganges verschoben werden';
								$return = false;
								$error = true;
							}

							if(!$error)
							{
								//Eintrag in der Tabelle Studentlehrverband aendern
								$student_lvb = new student();

								if($student_lvb->studentlehrverband_exists($uid, $semester_aktuell))
									$student_lvb->new = false;
								else
									$student_lvb->new = true;

								$student_lvb->uid = $uid;
								$student_lvb->studiensemester_kurzbz = $semester_aktuell;
								$student_lvb->studiengang_kz = $_POST['stg_kz'];
								$student_lvb->semester = $_POST['semester'];
								$student_lvb->verband = ($_POST['verband']==''?' ':$_POST['verband']);
								$student_lvb->gruppe = ($_POST['gruppe']==''?' ':$_POST['gruppe']);
								$student_lvb->updateamum = date('Y-m-d H:i:s');
								$student_lvb->updatevon = $user;

								if($student_lvb->save_studentlehrverband())
								{
									$return = true;
									$error=false;
								}
								else
								{
									$error = true;
									$errormsg .= $student_lvb->errormsg;
									$return = false;
								}
							}
						}
					}
				}
			}
			if($errormsg=='')
				$return = true;
			else
				$return = false;
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deleteGruppenzuteilung')
	{
		if(isset($_POST['uid']) && isset($_POST['gruppe_kurzbz']))
		{
			$gruppe = new gruppe();
			if($gruppe->load($_POST['gruppe_kurzbz']))
			{
				$uids = explode(';',$_POST['uid']);
				$errormsg = '';
				foreach ($uids as $uid)
				{
					if($uid!='')
					{
						$qry = "SELECT studiengang_kz FROM public.tbl_student WHERE student_uid=".$db->db_add_param($uid);
						if($result = $db->db_query($qry))
						{
							if($row = $db->db_fetch_object($result))
							{
								//Berechtigung pruefen
								if(!$rechte->isBerechtigt('assistenz',$gruppe->studiengang_kz,'suid') &&
								   !$rechte->isBerechtigt('admin',$gruppe->studiengang_kz, 'suid') &&
								   !$rechte->isBerechtigt('admin',$row->studiengang_kz, 'suid') &&
								   !$rechte->isBerechtigt('assistenz',$row->studiengang_kz, 'suid'))
								{
									$error = true;
									$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
								}

								if(!$error)
								{
									$benutzergruppe = new benutzergruppe();

									if(!$benutzergruppe->delete($uid, $_POST['gruppe_kurzbz']))
									{
										$errormsg .= "$uid konnte nicht aus der Gruppe geloescht werden\n";
									}
								}
							}
							else
								$errormsg .= "Studiengang von $uid konnte nicht ermittelt werden\n";
						}
						else
							$errormsg .= "Studiengang von $uid konnte nicht ermittelt werden\n";
					}
				}
				if($errormsg=='')
					$return = true;
				else
					$return = false;
			}
			else
			{
				$return = false;
				$errormsg = "Gruppe wurde nicht gefunden";
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deleteAkte')
	{
		if(isset($_POST['akte_id']) && is_numeric($_POST['akte_id']))
		{
			//Berechtigung pruefen
			if(!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
			   !$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid'))
			{
				$error = true;
				$return = false;
				$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
			}
			if(!$error)
			{
				$akte = new akte($_POST['akte_id']);
				//Akzeptierte Ausbildungsverträge dürfen nur von Admins gelöscht werden
				if ($akte->dokument_kurzbz == 'Ausbvert' &&
					$akte->akzeptiertamum != '' &&
					!$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid'))
				{
					$error = true;
					$return = false;
					$errormsg = 'Akzeptierte Ausbildungsverträge dürfen nur von Administratoren gelöscht werden';
				}

				if(!$error)
				{
					if ($akte->delete($_POST['akte_id']))
					{
						$return = true;
					}
					else
					{
						$return = false;
						$errormsg = $akte->errormsg;
					}
				}
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe'.$_POST['akte_id'];
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savebuchung') // ***** KONTO *****
	{
		//Speichert eine Buchung
		if(isset($_POST['buchungsnr']) && is_numeric($_POST['buchungsnr']))
		{
			$buchung = new konto();

			if($buchung->load($_POST['buchungsnr']))
			{
				if(!$rechte->isBerechtigt('assistenz',$buchung->studiengang_kz,'suid') &&
				   !$rechte->isBerechtigt('admin',$buchung->studiengang_kz, 'suid'))
				{
					$error = true;
					$return = false;
					$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
				}
				else
				{
					if(isBuchungAllowedToChange($buchung)
						|| $rechte->isBerechtigt('student/zahlungAdmin',$buchung->studiengang_kz,'suid'))
					{
						$buchung->betrag = $_POST['betrag'];
						$buchung->buchungsdatum = $_POST['buchungsdatum'];
						$buchung->buchungstext = $_POST['buchungstext'];
						$buchung->mahnspanne = $_POST['mahnspanne'];
						$buchung->buchungstyp_kurzbz = $_POST['buchungstyp_kurzbz'];
						$buchung->studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
						$buchung->studiengang_kz = $_POST['studiengang_kz'];
						$buchung->credit_points = $_POST['credit_points'];
						$buchung->new = false;
						$buchung->updateamum = date('Y-m-d H:i:s');
						$buchung->updatevon = $user;
						$buchung->anmerkung = $_POST['anmerkung'];

						if($buchung->save())
						{
							$return = true;
						}
						else
						{
							$return = false;
							$errormsg = 'Fehler beim Speichern:'.$buchung->errormsg;
						}
					}
					else
					{
						$return = false;
						$errormsg = 'Buchung wurde bereits übertragen und darf nicht geändert werden';
					}
				}
			}
			else
			{
				$errormsg = 'Buchung wurde nicht gefunden:'.$_POST['buchungsnr'];
				$return = false;
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe'.$_POST['buchungsnr'];
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savegegenbuchung')
	{
		//Speichert eine Buchung
		if(isset($_POST['buchungsnr']))
		{
			$bnr_arr = explode(';',$_POST['buchungsnr']);
			$gegenbuchungsdatum = filter_input(INPUT_POST, "gegenbuchungsdatum");
			if(strlen($gegenbuchungsdatum) != 0)
			{
				$gegenbuchungsdatum = date("Y-m-d", strtotime($gegenbuchungsdatum));
			}
			else
			{
				$gegenbuchungsdatum = date('Y-m-d');
			}

			$errormsg='';
			foreach ($bnr_arr as $buchungsnr)
			{
				if(is_numeric($buchungsnr))
				{
					$buchung = new konto();

					if($buchung->load($buchungsnr))
					{
						//Berechtigung pruefen
						if(!$rechte->isBerechtigt('assistenz',$buchung->studiengang_kz,'suid') &&
						   !$rechte->isBerechtigt('admin',$buchung->studiengang_kz, 'suid'))
						{
							$error = true;
							$return = false;
							$errormsg = "\nSie haben keine Schreibrechte fuer diese Buchung";
						}
						else
						{
							if($buchung->buchungsnr_verweis=='')
							{
								if(isBuchungAllowedToChange($buchung)
								|| $rechte->isBerechtigt('student/zahlungAdmin',$buchung->studiengang_kz,'suid'))
								{
									$kto = new konto();
									//$buchung->betrag*(-1);
									$buchung->betrag = $kto->getDifferenz($buchungsnr);
									$buchung->buchungsdatum = $gegenbuchungsdatum;
									$buchung->mahnspanne = '0';
									$buchung->buchungsnr_verweis = $buchung->buchungsnr;
									$buchung->new = true;
									$buchung->insertamum = date('Y-m-d H:i:s');
									$buchung->insertvon = $user;
									$buchung->anmerkung = '';

									if($buchung->save())
									{
										//$data = $buchung->buchungsnr;
										$return = true;
									}
									else
									{
										$return = false;
										$errormsg .= "\n".'Fehler beim Speichern:'.$buchung->errormsg;
									}
								}
								else
								{
									$return = false;
									$errormsg .= "\n".'Buchung wurde bereits Übertragen und darf nicht geändert werden';
								}
							}
							else
							{
								$return = false;
								$errormsg .= "\n".'Gegenbuchungen koennen nur auf die obersten Buchungen getaetigt werden';
							}
						}
					}
					else
					{
						$errormsg .= "\n".'Buchung wurde nicht gefunden';
						$return = false;
					}
				}
				if($errormsg!='')
				{
					$return = false;
				}
				else
				{
					$return = true;
				}
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletebuchung')
	{
		//Loescht eine Buchung
		if(isset($_POST['buchungsnr']) && is_numeric($_POST['buchungsnr']))
		{
			$buchung = new konto();

			if($buchung->load($_POST['buchungsnr']))
			{
				if(!$rechte->isBerechtigt('assistenz',$buchung->studiengang_kz,'suid') &&
				   !$rechte->isBerechtigt('admin',$buchung->studiengang_kz, 'suid'))
				{
					$error = true;
					$return = false;
					$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
				}
				else
				{
					$isAllowedToChange = isBuchungAllowedToChange($buchung);
					if($isAllowedToChange
					|| $rechte->isBerechtigt('student/zahlungAdmin',$buchung->studiengang_kz,'suid')
					)
					{
						if(!$isAllowedToChange)
						{
							// Buchung aus SAP Zwischentabelle loeschen
							deleteFromSAP($buchung);
						}

						if($buchung->delete($_POST['buchungsnr']))
						{
							$return = true;
						}
						else
						{
							$errormsg = $buchung->errormsg;
							$return = false;
						}
					}
					else
					{
						$error = true;
						$return = false;
						$errormsg = 'Diese Buchung darf nicht gelöscht werden da diese bereits übertragen wurde';
					}
				}
			}
			else
			{
				$errormsg = 'Buchung wurde nicht gefunden';
				$return = false;
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='neuebuchung')
	{
		//Speichert eine neue Buchung
		//Gleichzeitiges speichern mehrerer Personen ist moeglich
		//Personen werden durch ';' getrennt
		$person_ids = explode(';',$_POST['person_ids']);
		$errormsg = '';
		if(!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid'))
		{
			$error = true;
			$return = false;
			$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
		}
		else
		{
			foreach ($person_ids as $person_id)
			{
				if($person_id!='')
				{
					$buchung = new konto();
					$buchung->person_id = $person_id;
					$buchung->studiengang_kz = $_POST['studiengang_kz'];
					$buchung->studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
					$buchung->buchungsnr_verweis='';
					$buchung->betrag = $_POST['betrag'];
					$buchung->buchungsdatum = $_POST['buchungsdatum'];
					$buchung->buchungstext = $_POST['buchungstext'];
					$buchung->mahnspanne = $_POST['mahnspanne'];
					$buchung->buchungstyp_kurzbz = $_POST['buchungstyp_kurzbz'];
					$buchung->credit_points = $_POST["credit_points"];
					$buchung->insertamum = date('Y-m-d H:i:s');
					$buchung->insertvon = $user;
					$buchung->anmerkung = $_POST['anmerkung'];
					$buchung->new = true;

					if($buchung->save())
					{
						$data = $buchung->buchungsnr;
					}
					else
					{
						$errormsg .= "Fehler beim Speichern: $buchung->errormsg\n";
					}
				}
			}
		}
		if($errormsg=='')
			$return = true;
		else
			$return = false;
	}
	elseif(isset($_POST['type']) && $_POST['type']=='dokumentprestudentadd')
	{
		//Speichert die Zuordnung von Dokumenten zu einem Prestudent
		//Gleichzeitiges zuteilen mehrerer Dokumente auf einmal ist moeglich
		//Dokumente werden durch ';' getrennt uebergeben

		if(!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid'))
		{
			$error = true;
			$return = false;
			$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
		}
		else
		{
			$dokumente = explode(';',$_POST['dokumente']);
			$errormsg = '';
			$sonst = 0;
			foreach ($dokumente as $dokument_kurzbz)
			{
				if ($dokument_kurzbz === 'Sonst' && $sonst !== 0)
					continue;

				if($dokument_kurzbz!='')
				{
					$dok = new dokument();
					$dok->dokument_kurzbz = $dokument_kurzbz;
					$dok->prestudent_id = $_POST['prestudent_id'];
					$dok->mitarbeiter_uid = $user;
					$dok->datum = date('Y-m-d');
					$dok->insertamum = date('Y-m-d H:i:s');
					$dok->insertvon = $user;
					$dok->new = true;
					if ($dokument_kurzbz === 'Sonst')
						$sonst++;

					if(!$dok->save())
					{
						$errormsg .= "Fehler bei $dokument_kurzbz: $dok->errormsg\n";
					}
				}
			}
			if($errormsg=='')
				$return = true;
			else
				$return = false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='dokumentprestudentDetailSave')
	{
		//Speichert die Zuordnung von Dokumenten zu einem Prestudent
		//Gleichzeitiges zuteilen mehrerer Dokumente auf einmal ist moeglich
		//Dokumente werden durch ';' getrennt uebergeben

		if(!$rechte->isBerechtigt('assistenz',null,'suid') &&
		   !$rechte->isBerechtigt('admin',null, 'suid'))
		{
			$error = true;
			$return = false;
			$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
		}
		else
		{
			$akte_id = $_POST['akte_id'];
			$errormsg = '';
			$akte = new akte();
			if($akte->load($akte_id))
			{
				$akte->new=false;
				$akte->anmerkung_intern = $_POST['anmerkung_intern'];
				$akte->titel_intern = $_POST['titel_intern'];
				$akte->dokument_kurzbz = $_POST['dokument_kurzbz'];
				$akte->nachgereicht_am = $_POST['nachgereicht_am'];
				$akte->updateamum = date('Y-m-d H:i:s');
				$akte->updatevon = $user;

				if(!$akte->save())
				{
					$errormsg='Speichern fehlgeschlagen:'.$akte->errormsg;
				}
			}

			if($errormsg=='')
				$return = true;
			else
				$return = false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='dokumentprestudentDeleteDokument')
	{
		$prestudent_id = $_POST['prestudent_id'];
		$prestudent = new prestudent();
		if($prestudent->load($prestudent_id))
		{
			$studiengang = new studiengang();
			$studiengang->load($prestudent->studiengang_kz);

			if(!$rechte->isBerechtigt('assistenz',$studiengang->oe_kurzbz,'suid') &&
			   !$rechte->isBerechtigt('admin',$studiengang->oe_kurzbz, 'suid'))
			{
				$error = true;
				$return = false;
				$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
			}
			else
			{

				$akte_id = $_POST['akte_id'];
				$errormsg = '';
				$akte = new akte();
				if($akte->load($akte_id))
				{
					if($akte->person_id==$prestudent->person_id)
					{
						if($akte->dms_id!='')
						{
							$dms = new dms();
							if($dms->load($akte->dms_id))
							{
								if($akte->delete($akte_id))
								{
									// Eintrag im DMS loeschen
									if(!$dms->deleteDms($akte->dms_id))
									{
										$error=true;
										$errormsg='Fehler beim Loeschen des Dokuments';
									}
									else
									{
										// Log schreiben
										$logdata_dms = (array)$dms;
										$logdata = var_export($logdata_dms, true);
										$log = new log();
										$log->executetime = date('Y-m-d H:i:s');
										$log->mitarbeiter_uid = $user;
										$log->beschreibung = "Löschen der DMS_ID ".$akte->dms_id;
										$log->sql = 'LogData:'.$logdata;
										$log->sqlundo = '';
										$log->save(true);
									}
									// Log schreiben
									$logdata_akte = (array)$akte;
									$logdata = var_export($logdata_akte, true);
									$log = new log();
									$log->executetime = date('Y-m-d H:i:s');
									$log->mitarbeiter_uid = $user;
									$log->beschreibung = "Löschen der Akte '".$akte->dokument_kurzbz."' ID '".$akte_id."'";
									$log->sql = 'DELETE FROM public.tbl_akte WHERE akte_id='.$db->db_add_param($akte_id, FHC_INTEGER).'; LogData:'.$logdata;
									$log->sqlundo = '';
									$log->save(true);
								}
								else
								{
									$error=true;
									$errormsg='Fehler beim Loeschen der Akte';
								}
							}
							else
							{
								$error=true;
								$errormsg='Fehler beim Laden des Dokuments';
							}
						}
						// Wenn kein Dokument im DMS vorhanden ist, aber die akte auf "wird nachgereicht" gesetzt ist
						// darf das Dokument auch gelöscht werden
						elseif($akte->nachgereicht === true && $akte->inhalt == '')
						{
							if($akte->delete($akte_id))
							{
								// Log schreiben
								$logdata_akte = (array)$akte;
								$logdata = var_export($logdata_akte, true);
								$log = new log();
								$log->executetime = date('Y-m-d H:i:s');
								$log->mitarbeiter_uid = $user;
								$log->beschreibung = "Löschen der Nachreichung der Akte '".$akte->dokument_kurzbz."' ID '".$akte_id."'";
								$log->sql = 'DELETE FROM public.tbl_akte WHERE akte_id='.$db->db_add_param($akte_id, FHC_INTEGER).'; LogData:'.$logdata;
								$log->sqlundo = '';
								$log->save(true);
							}
							else
							{
								$error=true;
								$errormsg='Fehler beim Loeschen der Akte';
							}
						}
						else
						{
							$error=true;
							$errormsg='Dieses Dokument darf hier nicht geloescht werden';
						}
					}
					else
					{
						$error=true;
						$errormsg='Akte und Prestudent passen nicht zusammen';
					}
				}

				if($errormsg=='')
					$return = true;
				else
					$return = false;
			}
		}
		else
		{
			$errormsg='Prestudent konnte nicht geladen werden';
			$error=true;
			$return=false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='dokumentprestudentdel')
	{
		//Loescht die Zuordnung von Dokumenten zu einem Prestudent
		//Gleichzeitiges loeschen mehrerer Dokumente auf einmal ist moeglich
		//Dokumente werden durch ';' getrennt uebergeben
		if(!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid'))
		{
			$error = true;
			$return = false;
			$errormsg = 'Sie haben keine Schreibrechte fuer diesen Studiengang';
		}
		else
		{
			$dokumente = explode(';',$_POST['dokumente']);
			$errormsg = '';
			$sonst = 0;

			foreach ($dokumente as $dokument_kurzbz)
			{
				if ($dokument_kurzbz === 'Sonst' && $sonst !== 0)
					continue;

				if($dokument_kurzbz!='')
				{
					$dok = new dokument();
					if($dok->load($dokument_kurzbz, $_POST['prestudent_id']))
					{
						if($dok->mitarbeiter_uid==$user || $rechte->isBerechtigt('student/dokumente',$_POST['studiengang_kz'], 'suid'))
						{
							if(!$dok->delete($dokument_kurzbz, $_POST['prestudent_id']))
							{
								$errormsg .= "Fehler bei $dokument_kurzbz: $dok->errormsg\n";
							}
							else
							{
								if ($dokument_kurzbz === 'Sonst')
									$sonst++;
							}
						}
						else
						{
							$errormsg.="Fehler bei $dokument_kurzbz: Loeschen nur durch $dok->mitarbeiter_uid moeglich\n";
						}
					}
					else
					{
						$errormsg.="Dokumentenzuteilung existiert nicht: $dokument_kurzbz\n";
					}
				}
			}
			if($errormsg=='')
				$return = true;
			else
				$return = false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletebetriebsmittel')
	{
		//Loescht ein Betriebsmittel
		//Wenn studiengang_kz uebergeben wird, dann handelt es sich um die Betriebsmittel eines Studenten
		//Wenn studiengang_kz='' dann werden Mitarbeiterrechte benoetigt
		//ToDo: hier sollte nicht der Studiengang uebergeben werden sonder aus der DB ermittelt werden ob es
		//ein Student oder Mitarbeiter ist
		if(($_POST['studiengang_kz']!='' &&
			!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
			!$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid') &&
			!$rechte->isBerechtigt('basis/betriebsmittel',$_POST['studiengang_kz'], 'suid')
		   ) ||
		   ($_POST['studiengang_kz']=='' &&
			!$rechte->isBerechtigt('admin', null, 'suid') &&
			!$rechte->isBerechtigt('mitarbeiter', null, 'suid') &&
			!$rechte->isBerechtigt('basis/betriebsmittel', null, 'suid')
		   ))
		{
			$error = true;
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			if(isset($_POST['betriebsmittelperson_id']) && is_numeric($_POST['betriebsmittelperson_id']))
			{
				$btm = new betriebsmittelperson();

				if($btm->delete($_POST['betriebsmittelperson_id']))
				{
					$return = true;
				}
				else
				{
					$errormsg = $btm->errormsg;
					$return = false;
				}
			}
			else
			{
				$return = false;
				$errormsg  = 'Fehlerhafte Parameteruebergabe';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savebetriebsmittel')
	{
		//Speichert eine Betriebsmittelzuordnung
		//Wenn studiengang_kz uebergeben wird, dann handelt es sich um die Betriebsmittel eines Studenten
		//Wenn studiengang_kz='' dann werden Mitarbeiterrechte benoetigt
		//ToDo: hier sollte nicht der Studiengang uebergeben werden sonder aus der DB ermittelt werden ob es
		//ein Student oder Mitarbeiter ist
		if(($_POST['studiengang_kz']!='' &&
			!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
			!$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid') &&
			!$rechte->isBerechtigt('basis/betriebsmittel',$_POST['studiengang_kz'], 'suid')
		   ) ||
		   ($_POST['studiengang_kz']=='' &&
			!$rechte->isBerechtigt('admin', null, 'suid') &&
			!$rechte->isBerechtigt('mitarbeiter', null, 'suid') &&
			!$rechte->isBerechtigt('basis/betriebsmittel', null, 'suid')
		   ))
		{
			$error = true;
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			$betriebsmittel_id = $_POST['betriebsmittel_id'];
			$bm = new betriebsmittel();

			// Wenn es kein vorhandenes Inventar ist, dann neu anlegen
			if($_POST['betriebsmitteltyp']!='Inventar')
			{
				if($_POST['betriebsmitteltyp']=='Zutrittskarte')
					$_POST['nummer']=$bm->transform_kartennummer($_POST['nummer']);

				//Das speichern von Zutrittskarten ohne Nummern verhindern
				if($_POST['betriebsmitteltyp']=='Zutrittskarte' && ($_POST['nummer']=='' && $_POST['nummer2']==''))
				{
					$error = true;
					$return = false;
					$errormsg = 'Eine Zutrittskarte muss eine Nummer haben. Um die Zuordnung zu dieser Karte zu loeschen entfernen Sie bitte den ganzen Datensatz';
				}
				else
				{
					if ($_POST['betriebsmitteltyp']=='Zutrittskarte' && $_POST['nummer'] == '')
					{
						$resultBM = $bm->getBetriebsmittel($_POST['betriebsmitteltyp'],null, $_POST['nummer2']);
					}
					else
					{
						$resultBM = $bm->getBetriebsmittel($_POST['betriebsmitteltyp'],$_POST['nummer']);
					}

					//Nachschauen ob dieses Betriebsmittel schon existiert
					if($resultBM)
					{
						if(count($bm->result)>0)
						{
							//Wenn die Nummer gleich bleibt dann die alte ID verwenden da es
							//unterschiedliche Schluessel gibt die die gleiche nummer haben ?!?
							if($_POST['nummer'] != '' && $_POST['nummer'] == $_POST['nummerold'])
							{
								$betriebsmittel_id = $_POST['betriebsmittel_id'];
							}
							else
								$betriebsmittel_id = $bm->result[0]->betriebsmittel_id;
							//Wenn ein Eintrag gefunden wurde, dann wird die Beschreibung aktualisiert
							if($bm->load($betriebsmittel_id))
							{
								$bm->beschreibung = $_POST['beschreibung'];
								$bm->nummer = $_POST['nummer'];
								$bm->nummer2 = $_POST['nummer2'];
								if(!$bm->save(false))
								{
									$return = false;
									$error = true;
									$errormsg = 'Fehler beim Speichern des Betriebsmittels';
								}
							}
							else
							{
								$return = false;
								$error = true;
								$errormsg = 'Gefundener Eintrag konnte nicht geladen werden!?!?';
							}
						}
						else
						{
							//Wenn kein Eintrag gefunden wurde, dann wird ein neuer Eintrag angelegt
							$bm->betriebsmitteltyp = $_POST['betriebsmitteltyp'];
							$bm->nummer = $_POST['nummer'];
							$bm->nummer2 = $_POST['nummer2'];
							$bm->beschreibung = $_POST['beschreibung'];
							$bm->reservieren = false;
							$bm->ort_kurzbz = null;
							$bm->insertamum = date('Y-m-d H:i:s');
							$bm->insertvon = $user;

							if($bm->save(true))
							{
								$betriebsmittel_id = $bm->betriebsmittel_id;
							}
							else
							{
								$error = true;
								$return = false;
								$errormsg = 'Fehler beim Anlegen des Betriebsmittels';
							}
						}

						if($_POST['betriebsmitteltyp']=='Zutrittskarte')
						{
							//Bei Zutrittskarten schauen ob diese schon vergeben sind
							$qry = "SELECT vorname, nachname, uid
									FROM public.vw_betriebsmittelperson
									WHERE betriebsmitteltyp='Zutrittskarte' AND
										nummer='".$db->db_escape($_POST['nummer'])."'::varchar AND
										person_id<>".$db->db_add_param($_POST['person_id'], FHC_INTEGER)." AND
										retouram is null";
							if($result_bmp = $db->db_query($qry))
							{
								if($db->db_num_rows($result_bmp)>0)
								{
									$row_bmp = $db->db_fetch_object($result_bmp);
									$error = true;
									$return = false;
									$errormsg = "Diese Zutrittskarte ist bereits ausgegeben an: $row_bmp->vorname $row_bmp->nachname ($row_bmp->uid)";
								}
							}
							else
							{
								$error = true;
								$return = false;
								$errormsg = 'Fehler beim Laden der Zutrittskarte';
							}
						}
					}
				}
			}

			if(!$error)
			{
				//Zuordnung Betriebsmittel-Person anlegen
				$bmp = new betriebsmittelperson();
				if($_POST['neu']!='true')
				{
					if($bmp->load($_POST['betriebsmittelperson_id']))
					{
						$bmp->updateamum = date('Y-m-d H:i:s');
						$bmp->updatevon = $user;
						$bmp->betriebsmittelperson_id = $_POST['betriebsmittelperson_id'];
						$bmp->new = false;
					}
					else
					{
						$error = true;
						$errormsg = "Zuordnung unbekannt:".$_POST['betriebsmittelperson_id'];
						$return = false;
					}
				}
				else
				{
					$bmp->insertamum = date('Y-m-d H:i:s');
					$bmp->insertvon = $user;
					$bmp->new = true;
				}

				if(!$error)
				{
					$bmp->person_id = $_POST['person_id'];
					$bmp->betriebsmittel_id=$betriebsmittel_id;
					$bmp->anmerkung = $_POST['anmerkung'];
					$bmp->kaution = trim(str_replace(',','.',$_POST['kaution']));
					$bmp->ausgegebenam = $_POST['ausgegebenam'];
					$bmp->retouram = $_POST['retouram'];
					if($bmp->new)
						$bmp->uid = $_POST['uid'];

					if($bmp->save())
					{
						$return = true;
						$data = $bmp->betriebsmittelperson_id;
					}
					else
					{
						$return = false;
						$errormsg = $bmp->errormsg;
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletebisio')
	{
		if(!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid'))
		{
			$error = true;
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			//Loescht einen BisIO Eintrag
			if(isset($_POST['bisio_id']) && is_numeric($_POST['bisio_id']))
			{
				$bisio = new bisio();

				if($bisio->delete($_POST['bisio_id']))
				{
					$return = true;
				}
				else
				{
					$errormsg = $bisio->errormsg;
					$return = false;
				}
			}
			else
			{
				$return = false;
				$errormsg  = 'Fehlerhafte Parameteruebergabe';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savebisio')
	{
		//Speichert einen BisIO Eintrag
		if(!$rechte->isBerechtigt('assistenz',$_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('admin',$_POST['studiengang_kz'], 'suid'))
		{
			$error = true;
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{

			$bisio = new bisio();

			if($_POST['neu']=='true')
			{
				$bisio->insertamum = date('Y-m-d H:i:s');
				$bisio->insertvon = $user;
				$bisio->new = true;
			}
			else
			{
				if($bisio->load($_POST['bisio_id']))
					$bisio->new = false;
				else
				{
					$error = true;
					$errormsg = $bisio->errormsg;
					$return = false;
				}
			}

			$bisio->bisio_id = (isset($_POST['bisio_id'])?$_POST['bisio_id']:'');
			$bisio->mobilitaetsprogramm_code = $_POST['mobilitaetsprogramm_code'];
			$bisio->nation_code = $_POST['nation_code'];
			$bisio->von = $_POST['von'];
			$bisio->bis = $_POST['bis'];
			$bisio->student_uid = $_POST['student_uid'];
			$bisio->lehreinheit_id = $_POST['lehreinheit_id'];
			$bisio->ort = $_POST['ort'];
			$bisio->universitaet = $_POST['universitaet'];
			$bisio->ects_erworben = $_POST['ects_erworben'];
			$bisio->ects_angerechnet = $_POST['ects_angerechnet'];
			$bisio->updateamum = date('Y-m-d H:i:s');
			$bisio->updatevon = $user;

			if(!$error)
			{
				if($bisio->save())
				{
					$return = true;
					$data = $bisio->bisio_id;
				}
				else
				{
					$errormsg = $bisio->errormsg;
					$return = false;
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savebisiozweck')
	{
		$bisio = new bisio();
		if($bisio->load($_POST['bisio_id']))
		{
			$student = new student();
			if($student->load($bisio->student_uid))
			{

				//Speichert einen BisIO Eintrag
				if(!$rechte->isBerechtigt('assistenz',$student->studiengang_kz,'suid') &&
				   !$rechte->isBerechtigt('admin',$student->studiengang_kz, 'suid'))
				{
					$error = true;
					$return = false;
					$errormsg = 'Sie haben keine Berechtigung';
				}
				else
				{

					$bisio = new bisio();

					$bisio->bisio_id = (isset($_POST['bisio_id'])?$_POST['bisio_id']:'');
					$bisio->zweck_code = $_POST['zweck_code'];

					if(!$error)
					{
						if($bisio->saveZweck())
						{
							$return = true;
							$data = $bisio->bisio_id;
						}
						else
						{
							$errormsg = $bisio->errormsg;
							$return = false;
						}
					}
				}
			}
			else
			{
				$errormsg = $student->errormsg;
				$return = false;
			}
		}
		else
		{
			$errormsg = $bisio->errormsg;
			$return = false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletebisiozweck')
	{
		$bisio = new bisio();
		if($bisio->load($_POST['bisio_id']))
		{
			$student = new student();
			if($student->load($bisio->student_uid))
			{
				//Speichert einen BisIO Eintrag
				if(!$rechte->isBerechtigt('assistenz',$student->studiengang_kz,'suid') &&
				   !$rechte->isBerechtigt('admin',$student->studiengang_kz, 'suid'))
				{
					$error = true;
					$return = false;
					$errormsg = 'Sie haben keine Berechtigung';
				}
				else
				{

					$bisio = new bisio();

					$bisio->bisio_id = (isset($_POST['bisio_id'])?$_POST['bisio_id']:'');
					$bisio->zweck_code = $_POST['zweck_code'];

					if(!$error)
					{
						if($bisio->deleteZweck())
						{
							$return = true;
						}
						else
						{
							$errormsg = $bisio->errormsg;
							$return = false;
						}
					}
				}
			}
			else
			{
				$errormsg = $student->errormsg;
				$return = false;
			}
		}
		else
		{
			$errormsg = $bisio->errormsg;
			$return = false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savebisioaufenthaltfoerderung')
	{
		$bisio = new bisio();
		if($bisio->load($_POST['bisio_id']))
		{
			$student = new student();
			if($student->load($bisio->student_uid))
			{

				//Speichert einen BisIO Eintrag
				if(!$rechte->isBerechtigt('assistenz',$student->studiengang_kz,'suid') &&
				   !$rechte->isBerechtigt('admin',$student->studiengang_kz, 'suid'))
				{
					$error = true;
					$return = false;
					$errormsg = 'Sie haben keine Berechtigung';
				}
				else
				{

					$bisio = new bisio();

					$bisio->bisio_id = (isset($_POST['bisio_id'])?$_POST['bisio_id']:'');
					$bisio->aufenthaltfoerderung_code = $_POST['aufenthaltfoerderung_code'];

					if(!$error)
					{
						if($bisio->saveAufenthaltFoerderung())
						{
							$return = true;
							$data = $bisio->bisio_id;
						}
						else
						{
							$errormsg = $bisio->errormsg;
							$return = false;
						}
					}
				}
			}
			else
			{
				$errormsg = $student->errormsg;
				$return = false;
			}
		}
		else
		{
			$errormsg = $bisio->errormsg;
			$return = false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletebisioaufenthaltfoerderung')
	{
		$bisio = new bisio();
		if($bisio->load($_POST['bisio_id']))
		{
			$student = new student();
			if($student->load($bisio->student_uid))
			{
				//Speichert einen BisIO Eintrag
				if(!$rechte->isBerechtigt('assistenz',$student->studiengang_kz,'suid') &&
				   !$rechte->isBerechtigt('admin',$student->studiengang_kz, 'suid'))
				{
					$error = true;
					$return = false;
					$errormsg = 'Sie haben keine Berechtigung';
				}
				else
				{

					$bisio = new bisio();

					$bisio->bisio_id = (isset($_POST['bisio_id'])?$_POST['bisio_id']:'');
					$bisio->aufenthaltfoerderung_code = $_POST['aufenthaltfoerderung_code'];

					if(!$error)
					{
						if($bisio->deleteAufenthaltFoerderung())
						{
							$return = true;
						}
						else
						{
							$errormsg = $bisio->errormsg;
							$return = false;
						}
					}
				}
			}
			else
			{
				$errormsg = $student->errormsg;
				$return = false;
			}
		}
		else
		{
			$errormsg = $bisio->errormsg;
			$return = false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='getnotenotenschluessel')
	{
		if(!$rechte->isBerechtigt('admin', null, 's') && !$rechte->isBerechtigt('assistenz', null, 's') &&
		   !$rechte->isBerechtigt('student/noten', null, 's'))
		{
			$return = false;
			$error = true;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			$punkte=$_POST['punkte'];
			if(is_numeric($punkte))
			{
				$lehrveranstaltung_id=$_POST['lehrveranstaltung_id'];
				$studiensemester_kurzbz=$semester_aktuell;

				$notenschluessel = new notenschluessel();
				if(($note = $notenschluessel->getNote($punkte, $lehrveranstaltung_id, $studiensemester_kurzbz))!==false)
				{
					$return = true;
					$error = false;
					$data = $note;
				}
				else
				{
					$return = false;
					$error = true;
					$errormsg=$notenschluessel->errormsg;
				}
			}
			else
			{
				$return = true;
				$error = false;
				$data = '';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savenote')
	{
		//Speichert einen Noteneintrag

		$noten = new zeugnisnote();
		$lehrveranstaltung_id = filter_input(INPUT_POST, 'lehrveranstaltung_id');
		$student_uid = filter_input(INPUT_POST, 'student_uid');
		$studiensemester_kurzbz = filter_input(INPUT_POST, 'studiensemester_kurzbz');

		if(!is_null($lehrveranstaltung_id) && !is_null($student_uid) && !is_null($studiensemester_kurzbz))
		{
			//Berechtigung pruefen
			$qry = "SELECT studiengang_kz FROM public.tbl_student WHERE student_uid=".$db->db_add_param($student_uid);
			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))
				{
					$stg_std = $row->studiengang_kz;
				}
				else
				{
					$return = false;
					$error = true;
					$errormsg = 'Fehler beim Ermitteln des Studenten';
				}
			}
			else
			{
				$return = false;
				$error = true;
				$errormsg = 'Fehler beim Ermitteln des Studenten';
			}

			if(!$error)
			{
				$lva = new lehrveranstaltung($lehrveranstaltung_id);

				if(!$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid') && !$rechte->isBerechtigt('admin', $stg_std, 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid') && !$rechte->isBerechtigt('assistenz', $stg_std, 'suid'))
				{
					$return = false;
					$error = true;
					$errormsg = 'Sie haben keine Berechtigung';
				}
				else
				{

					if($noten->load($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz))
					{
						$noten->new = false;
						$noten->updateamum = date('Y-m-d H:i:s');
						$noten->updatevon = $user;
						$log = new log();
						$log->executetime = date('Y-m-d H:i:s');
						$log->mitarbeiter_uid = $user;
						$log->beschreibung = "Ändern der Note ".$noten->note." bei ".$noten->student_uid;
						$log->sql = 'UPDATE lehre.tbl_zeugnisnote SET '.
								'note='.$db->db_add_param($noten->note).', '.
								'punkte='.$db->db_add_param($noten->punkte).','.
								'uebernahmedatum='.$db->db_add_param($noten->uebernahmedatum).', '.
								'benotungsdatum='.$db->db_add_param($noten->benotungsdatum).', '.
								'bemerkung='.$db->db_add_param($noten->bemerkung).', '.
								'updateamum= '.$db->db_add_param($noten->updateamum).', '.
								'updatevon='.$db->db_add_param($noten->updatevon).' '.
								'WHERE lehrveranstaltung_id='.$db->db_add_param($noten->lehrveranstaltung_id, FHC_INTEGER).' '.
								'AND student_uid='.$db->db_add_param($noten->student_uid).' '.
								'AND studiensemester_kurzbz='.$db->db_add_param($noten->studiensemester_kurzbz).';';
						$log->sqlundo = $noten->getUndo('update');
						$log->save(true);
					}
					else
					{
						$noten->new = true;
						$noten->insertamum = date('Y-m-d H:i:s');
						$noten->insertvon = $user;
					}

					$noten->lehrveranstaltung_id = $_POST['lehrveranstaltung_id'];
					$noten->student_uid = $_POST['student_uid'];
					$noten->studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
					$noten->benotungsdatum = date('Y-m-d H:i:s');
					$noten->note = $_POST['note'];
					if(isset($_POST['punkte']))
						$noten->punkte=str_replace(',','.',$_POST['punkte']);

					if($noten->save())
					{
						$return = true;
					}
					else
					{
						$errormsg = $noten->errormsg;
						$return = false;
					}

					if(defined('FAS_PRUEFUNG_BEI_NOTENEINGABE_ANLEGEN') && FAS_PRUEFUNG_BEI_NOTENEINGABE_ANLEGEN && $return == true && $noten->new == true)
					{
						NotePruefungAnlegen($studiensemester_kurzbz, $student_uid, $lehrveranstaltung_id, $noten->note, $noten->punkte);
					}
				}
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='movenote')
	{
		//Speichert einen LVGesamtNoten Eintrag in die Tbl Zeugnisnote
		//Die Daten werden per POST uebermittelt. Es wird ein Feld Anzahl mituebergeben
		//mit der Anzahl der Felder. Die Felder sind durchnummeriert zB lehreinheit_id_0, lehreinheit_id_1, ...
		$errormsg = '';
		$ueberschreibbar=true;
		$nueberschreibbarbez=array();

		for($i=0;$i<$_POST['anzahl'];$i++)
		{
			$lvgesamtnote = new lvgesamtnote();
			$zeugnisnote = new zeugnisnote();
			$noten_ueberschreibbar = array();
			$noten_bezeichnung = array();
			$note_obj = new note();
			$note_obj->getAll();
			foreach($note_obj->result as $row)
			{
				$noten_ueberschreibbar[$row->note] = $row->lkt_ueberschreibbar;
				$noten_bezeichnung[$row->note] = $row->bezeichnung;
			}

			//Berechtigung pruefen
			$qry = "SELECT studiengang_kz FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$db->db_add_param($_POST['lehrveranstaltung_id_'.$i], FHC_INTEGER);
			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))
				{
					$stg_lva = $row->studiengang_kz;
				}
				else
				{
					$return = false;
					$error = true;
					$errormsg = 'Fehler beim Ermitteln der LVA';
				}
			}
			else
			{
				$return = false;
				$error = true;
				$errormsg = 'Fehler beim Ermitteln der LVA';
			}

			$qry = "SELECT studiengang_kz FROM public.tbl_student WHERE student_uid=".$db->db_add_param($_POST['student_uid_'.$i]);
			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))
				{
					$stg_std = $row->studiengang_kz;
				}
				else
				{
					$return = false;
					$error = true;
					$errormsg = 'Fehler beim Ermitteln des Studenten';
				}
			}
			else
			{
				$return = false;
				$error = true;
				$errormsg = 'Fehler beim Ermitteln des Studenten';
			}

			if(!$error)
			{
				if(!$rechte->isBerechtigt('student/noten', $stg_lva, 'suid') && !$rechte->isBerechtigt('student/noten', $stg_std, 'suid'))
				{
					$return = false;
					$error = true;
					$errormsg .= 'Sie haben keine Berechtigung';
				}
				else
				{
					if($lvgesamtnote->load($_POST['lehrveranstaltung_id_'.$i], $_POST['student_uid_'.$i], $_POST['studiensemester_kurzbz_'.$i]))
					{
						if($zeugnisnote->load($_POST['lehrveranstaltung_id_'.$i], $_POST['student_uid_'.$i], $_POST['studiensemester_kurzbz_'.$i]))
						{
							$zeugnisnote->new = false;
							$zeugnisnote->updateamum = date('Y-m-d H:i:s');
							$zeugnisnote->updatevon = $user;
							// Noten wie "angerechnet", "nicht erforderlich" werden nicht ueberschrieben
							if(isset($zeugnisnote->note) && array_key_exists($zeugnisnote->note, $noten_ueberschreibbar) && $noten_ueberschreibbar[$zeugnisnote->note] === false)
							{
								$notenbez = '"'.$noten_bezeichnung[$zeugnisnote->note].'"';
								if (!in_array($notenbez, $nueberschreibbarbez))
									$nueberschreibbarbez[] = $notenbez;
								$ueberschreibbar = false;
								continue;
							}
						}
						else
						{
							$zeugnisnote->new = true;
							$zeugnisnote->insertamum = date('Y-m-d H:i:s');
							$zeugnisnote->insertvon = $user;
							$zeugnisnote->lehrveranstaltung_id = $_POST['lehrveranstaltung_id_'.$i];
							$zeugnisnote->student_uid = $_POST['student_uid_'.$i];
							$zeugnisnote->studiensemester_kurzbz = $_POST['studiensemester_kurzbz_'.$i];
						}

						$zeugnisnote->note = $lvgesamtnote->note;
						$zeugnisnote->punkte = $lvgesamtnote->punkte;
						$zeugnisnote->uebernahmedatum = date('Y-m-d H:i:s');
						$zeugnisnote->benotungsdatum = $lvgesamtnote->benotungsdatum;
						$zeugnisnote->bemerkung = $lvgesamtnote->bemerkung;

						if(!$zeugnisnote->save())
						{
							$errormsg .= "\n".$zeugnisnote->errormsg;
						}
						else
						{
							if(defined('FAS_PRUEFUNG_BEI_NOTENEINGABE_ANLEGEN') && FAS_PRUEFUNG_BEI_NOTENEINGABE_ANLEGEN && $zeugnisnote->new == true)
							{
								NotePruefungAnlegen($zeugnisnote->studiensemester_kurzbz, $zeugnisnote->student_uid, $zeugnisnote->lehrveranstaltung_id, $zeugnisnote->note, $zeugnisnote->punkte);
							}
						}
					}
					else
					{
						$errormsg .= "\nLvGesamtNote wurde nicht gefunden";
					}
				}
			}
		}
		if(!$ueberschreibbar)
		{
			$errormsg.="\nNote(n) ".implode(', ', $nueberschreibbarbez)." waren nicht ueberschreibbar";
		}

		if($errormsg=='')
			$return = true;
		else
			$return = false;
	}
	elseif(isset($_POST['type']) && $_POST['type']=='importnoten')
	{
		//Importiert die Noten einer Lehrveranstaltung
		//als Parameter wird die Matrikelnummer und die Note uebergeben
		//Die Felder sind durchnummeriert zB matrikelnummer_0, matrikelnummer_1, ...
		//Die Anzahl der Gesamten Daten wird auch als Parameter uebergeben
		$errormsg = '';
		$ueberschreibbar=true;
		$nueberschreibbarbez=array();

		$noten_anmerkung_arr=array();
		$noten_ueberschreibbar = array();
		$noten_bezeichnung = array();
		$note_obj = new note();
		$note_obj->getAll();
		foreach($note_obj->result as $row)
		{
			$noten_ueberschreibbar[$row->note] = $row->lkt_ueberschreibbar;
			$noten_bezeichnung[$row->note] = $row->bezeichnung;
			$noten_anmerkung_arr[$row->anmerkung] = $row->note;
		}

		for($i=0;$i<$_POST['anzahl'];$i++)
		{
			if($_POST['matrikelnummer_'.$i]!='')
			{
				$zeugnisnote = new zeugnisnote();
				$error = false;
				if(!is_numeric(trim($_POST['matrikelnummer_'.$i])))
				{
					$error = true;
					$errormsg = "\nMatrikelnummer ist ungueltig: ".$_POST['matrikelnummer_'.$i];
				}
				if((isset($_POST['note_'.$i]) && !is_numeric($_POST['note_'.$i]) && !isset($noten_anmerkung_arr[$_POST['note_'.$i]])))
				{
					$error = true;
					$errormsg = "\nNote ist ungueltig: ".$_POST['note_'.$i];
				}
				if(!$error)
				{
					$qry = "SELECT student_uid, studiengang_kz FROM public.tbl_student WHERE trim(matrikelnr)=".$db->db_add_param(trim($_POST['matrikelnummer_'.$i]));
					if($result = $db->db_query($qry))
					{
						if($row = $db->db_fetch_object($result))
						{
							$uid = $row->student_uid;
							$stg_std = $row->studiengang_kz;
						}
						else
						{
							$error = true;
							$errormsg.="\nMatrikelnummer ".$_POST['matrikelnummer_'.$i]." wurde nicht gefunden";
						}
					}
					else
					{
						$error = true;
						$errormsg.="\nFehler beim Ermitteln der UID";
					}

					//Berechtigung pruefen
					$qry = "SELECT studiengang_kz FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$db->db_add_param($_POST['lehrveranstaltung_id'], FHC_INTEGER);
					if($result = $db->db_query($qry))
					{
						if($row = $db->db_fetch_object($result))
						{
							$stg_lva = $row->studiengang_kz;
						}
						else
						{
							$return = false;
							$error = true;
							$errormsg = 'Fehler beim Ermitteln der LVA';
						}
					}
					else
					{
						$return = false;
						$error = true;
						$errormsg = 'Fehler beim Ermitteln der LVA';
					}

					if(!$error)
					{
						if(!$rechte->isBerechtigt('admin', $stg_lva, 'suid') && !$rechte->isBerechtigt('admin', $stg_std, 'suid') &&
						   !$rechte->isBerechtigt('assistenz', $stg_lva, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_std, 'suid'))
						{
							$return = false;
							$error = true;
							$errormsg .= 'Sie haben keine Berechtigung';
						}
						else
						{
							if($zeugnisnote->load($_POST['lehrveranstaltung_id'], $uid, $semester_aktuell))
							{
								$zeugnisnote->new = false;
								$zeugnisnote->updateamum = date('Y-m-d H:i:s');
								$zeugnisnote->updatevon = $user;
								// Noten wie "angerechnet", "nicht erforderlich" werden nicht ueberschrieben
								if(isset($zeugnisnote->note) && array_key_exists($zeugnisnote->note, $noten_ueberschreibbar) && $noten_ueberschreibbar[$zeugnisnote->note] === false)
								{
									$notenbez = '"'.$noten_bezeichnung[$zeugnisnote->note].'"';
									if (!in_array($notenbez, $nueberschreibbarbez))
										$nueberschreibbarbez[] = $notenbez;
									$ueberschreibbar = false;
									continue;
								}
							}
							else
							{
								$zeugnisnote->new = true;
								$zeugnisnote->insertamum = date('Y-m-d H:i:s');
								$zeugnisnote->insertvon = $user;
								$zeugnisnote->lehrveranstaltung_id = $_POST['lehrveranstaltung_id'];
								$zeugnisnote->student_uid = $uid;
								$zeugnisnote->studiensemester_kurzbz = $semester_aktuell;
							}

							if(isset($_POST['note_'.$i]))
							{
								$zeugnisnote->note = $_POST['note_'.$i];
								$zeugnisnote->punkte = null;
								// Wenn es nicht numerisch ist, dann nachsehen ob es eine anmerkung gibt die so heisst
								// zB fuer met, nb, ar, etc
								if(!is_numeric($zeugnisnote->note) && isset($noten_anmerkung_arr[$zeugnisnote->note]))
								{
									$zeugnisnote->note = $noten_anmerkung_arr[$zeugnisnote->note];
								}
							}
							elseif(isset($_POST['punkte_'.$i]))
							{
								$zeugnisnote->punkte=$_POST['punkte_'.$i];
								$zeugnisnote->punkte = str_replace(',','.', $zeugnisnote->punkte);
								$notenschluessel = new notenschluessel();
								$note = $notenschluessel->getNote($zeugnisnote->punkte, $_POST['lehrveranstaltung_id'], $semester_aktuell);
								$zeugnisnote->note = $note;
							}
							$zeugnisnote->uebernahmedatum = date('Y-m-d H:i:s');
							$zeugnisnote->benotungsdatum = date('Y-m-d H:i:s');

							if(!$zeugnisnote->save())
							{
								$errormsg .= "\n".$zeugnisnote->errormsg;
							}
							else
							{
								if(defined('FAS_PRUEFUNG_BEI_NOTENEINGABE_ANLEGEN') && FAS_PRUEFUNG_BEI_NOTENEINGABE_ANLEGEN && $zeugnisnote->new == true)
								{
									NotePruefungAnlegen($semester_aktuell, $uid, $_POST['lehrveranstaltung_id'], $zeugnisnote->note, $zeugnisnote->punkte);
								}
							}
						}
					}
				}
			}
		}
		if(!$ueberschreibbar)
		{
			$errormsg.="\nNote(n) ".implode(', ', $nueberschreibbarbez)." waren nicht ueberschreibbar";
		}

		if($errormsg=='')
			$return = true;
		else
			$return = false;
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletenote')
	{
		//Loescht einen Noteneintrag

		$noten = new zeugnisnote();
		$lehrveranstaltung_id = filter_input(INPUT_POST, 'lehrveranstaltung_id');
		$student_uid = filter_input(INPUT_POST, 'student_uid');
		$studiensemester_kurzbz = filter_input(INPUT_POST, 'studiensemester_kurzbz');

		if(!is_null($lehrveranstaltung_id) && !is_null($student_uid) && !is_null($studiensemester_kurzbz))
		{
			//Berechtigung pruefen
			$qry = "SELECT studiengang_kz FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER);
			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))
				{
					$stg_lva = $row->studiengang_kz;
				}
				else
				{
					$return = false;
					$error = true;
					$errormsg = 'Fehler beim Ermitteln der LVA';
				}
			}
			else
			{
				$return = false;
				$error = true;
				$errormsg = 'Fehler beim Ermitteln der LVA';
			}

			$qry = "SELECT studiengang_kz FROM public.tbl_student WHERE student_uid=".$db->db_add_param($student_uid);
			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))
				{
					$stg_std = $row->studiengang_kz;
				}
				else
				{
					$return = false;
					$error = true;
					$errormsg = 'Fehler beim Ermitteln des Studenten';
				}
			}
			else
			{
				$return = false;
				$error = true;
				$errormsg = 'Fehler beim Ermitteln des Studenten';
			}

			if(!$error)
			{
				if(!$rechte->isBerechtigt('admin', $stg_lva, 'suid') && !$rechte->isBerechtigt('admin', $stg_std, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $stg_lva, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_std, 'suid'))
				{
					$return = false;
					$error = true;
					$errormsg = 'Sie haben keine Berechtigung';
				}
				else
				{
					$noten->load($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz);
					if($noten->delete($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz))
					{
						$log = new log();
						$log->executetime = date('Y-m-d H:i:s');
						$log->mitarbeiter_uid = $user;
						$log->beschreibung = "Löschen der Note ".$noten->note." bei ".$noten->student_uid;
						$log->sql = "DELETE FROM lehre.tbl_zeugnisnote WHERE
								lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER, false)." AND
								student_uid=".$db->db_add_param($student_uid)." AND
								studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).";";
						$log->sqlundo = $noten->getUndo('insert');
						$log->save(true);
						$return = true;
					}
					else
					{
						$return = false;
						$errormsg = $noten->errormsg;
					}
				}
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletepruefung')  // **** PRUEFUNGEN **** //
	{
		//Loescht einen Pruefungs Eintrag
		if(isset($_POST['pruefung_id']) && is_numeric($_POST['pruefung_id']))
		{
			if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') && !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
			{
				$return = false;
				$error = true;
				$errormsg = 'Sie haben keine Berechtigung';
			}
			else
			{
				$pruefung = new pruefung();

				if($pruefung->delete($_POST['pruefung_id']))
				{
					$return = true;
				}
				else
				{
					$errormsg = $pruefung->errormsg;
					$return = false;
				}
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savepruefung')  // **** PRUEFUNGEN **** //
	{
		$datum_obj = new datum();
		if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') && !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			$pruefung = new pruefung();

			if($_POST['neu']=='false')
			{
				if($pruefung->load($_POST['pruefung_id']))
				{
					$pruefung->new = false;
				}
				else
				{
					$error = true;
					$return = false;
					$errormsg = $pruefung->errormsg;
				}
			}
			else
			{
				$pruefung->new = true;
				$pruefung->insertamum = date('Y-m-d H:i:s');
				$pruefung->insertvon = $user;
			}

			$db->db_query('BEGIN');

			if($_POST['pruefungstyp_kurzbz']=='Termin2')
			{
				//Wenn ein 2. Termin angelegt wird, und kein 1. Termin vorhanden ist,
				//dann wird auch ein 1. Termin angelegt mit der derzeitigen Zeugnisnote
				$qry = "SELECT * FROM lehre.tbl_pruefung WHERE
						student_uid=".$db->db_add_param($_POST['student_uid'])." AND
						lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER)." AND
						pruefungstyp_kurzbz='Termin1'";
				if($result = $db->db_query($qry))
				{
					if($db->db_num_rows($result)==0)
					{
						$qry = "SELECT note,punkte, benotungsdatum FROM lehre.tbl_zeugnisnote JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id) WHERE
								student_uid=".$db->db_add_param($_POST['student_uid'])." AND
								tbl_lehreinheit.lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER)." AND
								tbl_lehreinheit.studiensemester_kurzbz = tbl_zeugnisnote.studiensemester_kurzbz";
						if($result = $db->db_query($qry))
						{
							if($row = $db->db_fetch_object($result))
							{
								//Wenn kein Ersttermin existiert, dann wird einer angelegt
								$ersttermin = new pruefung();
								$ersttermin->new=true;
								$ersttermin->insertamum = date('Y-m-d H:i:s');
								$ersttermin->insertvon = $user;
								$ersttermin->lehreinheit_id = $_POST['lehreinheit_id'];
								$ersttermin->student_uid = $_POST['student_uid'];
								$ersttermin->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
								$ersttermin->note = $row->note;
								$ersttermin->punkte = $row->punkte;
								$ersttermin->pruefungstyp_kurzbz = 'Termin1';
								$ersttermin->datum = $row->benotungsdatum;
								$ersttermin->anmerkung = '';

								if(!$ersttermin->save())
								{
									$error = true;
									$return = false;
									$errormsg = 'Fehler beim Anlegen des 1.Termin:'.$ersttermin->errormsg;
								}
							}
						}
						//Wenn keine Zeugnisnote vorhanden ist, dann wird kein
						//1.Termin angelegt
					}
				}
				else
				{
					$error = true;
					$return = false;
					$errormsg = 'Fehler beim Ermitteln des Ersttermines';
				}

			}

			if(!$error)
			{
				$pruefung->lehreinheit_id = $_POST['lehreinheit_id'];
				$pruefung->student_uid = $_POST['student_uid'];
				$pruefung->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
				$pruefung->note = $_POST['note'];
				if(isset($_POST['punkte']))
					$pruefung->punkte = str_replace(',','.',$_POST['punkte']);
				$pruefung->pruefungstyp_kurzbz = $_POST['pruefungstyp_kurzbz'];
				$pruefung->datum = $_POST['datum'];
				$pruefung->anmerkung = $_POST['anmerkung'];
				$pruefung->updateamum = date('Y-m-d H:i:s');
				$pruefung->updatevon = $user;


				if($pruefung->save())
				{
					$return = true;
					$data = $pruefung->pruefung_id;
					//Zeugnisnote aktualisieren
					$qry = "SELECT lehrveranstaltung_id, studiensemester_kurzbz FROM lehre.tbl_lehreinheit
							WHERE lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER);
					if($result_le = $db->db_query($qry))
					{
						if($row_le = $db->db_fetch_object($result_le))
						{
							$lehrveranstaltung_id = $row_le->lehrveranstaltung_id;
							$studiensemester_kurzbz = $row_le->studiensemester_kurzbz;
						}
						else
						{
							$error = true;
							$return = false;
							$errormsg = 'Fehler beim Ermitteln der Lehrveranstaltung';
						}
					}
					else
					{
						$error = true;
						$return = false;
						$errormsg = 'Fehler beim Ermitteln der Lehrveranstaltung';
					}


					if(!$error)
					{
						$zeugnisnote = new zeugnisnote();
						if($zeugnisnote->load($lehrveranstaltung_id, $_POST['student_uid'], $studiensemester_kurzbz))
						{
							if($zeugnisnote->uebernahmedatum=='' ||
								($datum_obj->mktime_fromtimestamp($zeugnisnote->benotungsdatum) >
								 $datum_obj->mktime_fromtimestamp($zeugnisnote->uebernahmedatum)))
								$checkdatum = $zeugnisnote->benotungsdatum;
							else
								$checkdatum = $zeugnisnote->uebernahmedatum;

							if($datum_obj->mktime_fromtimestamp($checkdatum)>$datum_obj->mktime_fromdate($_POST['datum']))
							{
								if($zeugnisnote->note!=$_POST['note'])
								{
									$error = true;
									$return = false;
									$errormsg = 'ACHTUNG! Diese Pruefungsnote wurde nicht ins Zeugnis uebernommen da die Zeugnisnote nach dem Pruefungsdatum veraendert wurde';
								}
							}
							else
							{
								$zeugnisnote->new = false;
							}
						}
						else
						{
							$zeugnisnote->new = true;
							$zeugnisnote->insertamum = date('Y-m-d H:i:s');
							$zeugnisnote->insertvon = $user;
						}

						if(!$error)
						{
							$zeugnisnote->student_uid = $_POST['student_uid'];
							$zeugnisnote->lehrveranstaltung_id = $lehrveranstaltung_id;
							$zeugnisnote->studiensemester_kurzbz = $studiensemester_kurzbz;
							$zeugnisnote->note = $_POST['note'];
							if(isset($_POST['punkte']))
								$zeugnisnote->punkte = str_replace(',','.',$_POST['punkte']);
							else
								$zeugnisnote->punkte='';
							$zeugnisnote->uebernahmedatum = date('Y-m-d H:i:s');
							$zeugnisnote->benotungsdatum = date('Y-m-d',$datum_obj->mktime_fromdate($_POST['datum']));
							$zeugnisnote->updateamum = date('Y-m-d H:i:s');
							$zeugnisnote->updatevon = $user;

							if(!$zeugnisnote->save())
							{
								$return = false;
								$error = true;
								$errormsg = 'Fehler beim Speichern der Zeungisnote:'.$zeugnisnote->errormsg;
								$db->db_query('ROLLBACK');
							}
							else
							{
								$db->db_query('COMMIT');
							}
						}
						else
						{
							//Kein Rollback damit die Pruefung gespeichert wird
							//returnwert ist aber false damit die Meldung angezeigt wird,
							//dass die Note nicht ins Zeugnis uebernommen wird
							$db->db_query('COMMIT');
						}
					}
					else
					{
						$db->db_query('ROLLBACK');
					}
				}
				else
				{
					$return = false;
					$errormsg = $pruefung->errormsg;
					$db->db_query('ROLLBACK');
				}
			}
			else
			{
				$db->db_query('ROLLBACK');
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='saveabschlusspruefung')  // **** ABSCHLUSSPRUEFUNGEN **** //
	{
		if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') && !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			$pruefung = new abschlusspruefung();

			if($_POST['neu']=='false')
			{
				if($pruefung->load($_POST['abschlusspruefung_id']))
				{
					$pruefung->new = false;
				}
				else
				{
					$error = true;
					$return = false;
					$errormsg = $pruefung->errormsg;
				}
			}
			else
			{
				$pruefung->new = true;
				$pruefung->insertamum = date('Y-m-d H:i:s');
				$pruefung->insertvon = $user;
			}

			$pruefung->student_uid = $_POST['student_uid'];
			$pruefung->vorsitz = $_POST['vorsitz'];
			$pruefung->pruefer1 = $_POST['pruefer1'];
			$pruefung->pruefer2 = $_POST['pruefer2'];
			$pruefung->pruefer3 = $_POST['pruefer3'];
			$pruefung->abschlussbeurteilung_kurzbz = $_POST['abschlussbeurteilung_kurzbz'];
			if(isset($_POST['pruefungsantritt_kurzbz']))
				$pruefung->pruefungsantritt_kurzbz = $_POST['pruefungsantritt_kurzbz'];

			$pruefung->note = $_POST['notekommpruef'];
			$pruefung->akadgrad_id = $_POST['akadgrad_id'];
			$pruefung->pruefungstyp_kurzbz = $_POST['pruefungstyp_kurzbz'];
			$pruefung->datum = $_POST['datum'];
			$pruefung->uhrzeit = $_POST['uhrzeit'];
			$pruefung->sponsion = $_POST['sponsion'];
			$pruefung->anmerkung = $_POST['anmerkung'];
			$pruefung->updateamum = date('Y-m-d H:i:s');
			$pruefung->updatevon = $user;

			if(!$error)
			{
				if($pruefung->save())
				{
					$return = true;
					$data = $pruefung->abschlusspruefung_id;
				}
				else
				{
					$return = false;
					$errormsg = $pruefung->errormsg;
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deleteabschlusspruefung')
	{
		//Loescht einen Pruefungs Eintrag
		if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') && !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			if(isset($_POST['abschlusspruefung_id']) && is_numeric($_POST['abschlusspruefung_id']))
			{
				$pruefung = new abschlusspruefung();
				if($pruefung->load($_POST['abschlusspruefung_id']))
				{
					if ($pruefung->freigabedatum == '')
					{
						if($pruefung->delete($_POST['abschlusspruefung_id']))
						{
							$return = true;
						}
						else
						{
							$errormsg = $pruefung->errormsg;
							$return = false;
						}
					}
					else
					{
						$errormsg = 'Löschen ist nicht möglich da bereits ein freigegebenes Protokoll vorhanden ist';
						$return = false;
					}
				}
				else
				{
					$errormsg = $pruefung->errormsg;
					$return = false;
				}
			}
			else
			{
				$return = false;
				$errormsg  = 'Fehlerhafte Parameteruebergabe';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='saveprojektarbeit')  // **** Projektarbeit **** //
	{
		if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') && !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			$projektarbeit = new projektarbeit();

			if($_POST['neu']=='false')
			{
				if($projektarbeit->load($_POST['projektarbeit_id']))
				{
					$projektarbeit->new = false;
				}
				else
				{
					$error = true;
					$return = false;
					$errormsg = $projektarbeit->errormsg;
				}
			}
			else
			{
				$projektarbeit->new = true;
				$projektarbeit->insertamum = date('Y-m-d H:i:s');
				$projektarbeit->insertvon = $user;
			}

			$projektarbeit->projekttyp_kurzbz = $_POST['projekttyp_kurzbz'];
			$projektarbeit->titel = $_POST['titel'];
			$projektarbeit->titel_english = $_POST['titel_english'];
			$projektarbeit->lehreinheit_id = $_POST['lehreinheit_id'];
			$projektarbeit->student_uid = $_POST['student_uid'];
			$projektarbeit->firma_id = $_POST['firma_id'];
			$projektarbeit->note = $_POST['note'];
			$projektarbeit->punkte = str_replace(',','.',$_POST['punkte']);
			$projektarbeit->beginn = $_POST['beginn'];
			$projektarbeit->ende = $_POST['ende'];
			$projektarbeit->faktor = str_replace(',','.',$_POST['faktor']);
			$projektarbeit->freigegeben = ($_POST['freigegeben']=='true'?true:false);
			$projektarbeit->gesperrtbis = $_POST['gesperrtbis'];
			$projektarbeit->stundensatz = str_replace(',','.',$_POST['stundensatz']);
			$projektarbeit->gesamtstunden = $_POST['gesamtstunden'];
			$projektarbeit->themenbereich = $_POST['themenbereich'];
			$projektarbeit->anmerkung = $_POST['anmerkung'];
			$projektarbeit->updateamum = date('Y-m-d H:i:s');
			$projektarbeit->updatevon = $user;
			$projektarbeit->final = ($_POST['final']=='true'?true:false);

			if(!$error)
			{
				if($projektarbeit->save())
				{
					$return = true;
					$data = $projektarbeit->projektarbeit_id;
				}
				else
				{
					$return = false;
					$errormsg = $projektarbeit->errormsg;
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deleteprojektarbeit')
	{
		if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') && !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			//Loescht einen Projektarbeit Eintrag
			if(isset($_POST['projektarbeit_id']) && is_numeric($_POST['projektarbeit_id']))
			{
				$projektarbeit = new projektarbeit();

				$qry = "SELECT count(*) as anzahl FROM lehre.tbl_projektbetreuer
					WHERE projektarbeit_id=".$db->db_add_param($_POST['projektarbeit_id'], FHC_INTEGER);

				if($result = $db->db_query($qry))
				{
					if($row = $db->db_fetch_object($result))
					{
						if($row->anzahl>0)
						{
							$errormsg = 'Bitte zuerst alle BetreuerInnen loeschen';
							$return = false;
						}
						else
						{
							$qry = "SELECT count(*) as anzahl FROM campus.tbl_paabgabe
									WHERE projektarbeit_id=".$db->db_add_param($_POST['projektarbeit_id'], FHC_INTEGER).";";

							if($result = $db->db_query($qry))
							{
								if($row = $db->db_fetch_object($result))
								{
									if($row->anzahl>0)
									{
										$errormsg = 'Diese Projektarbeit kann nicht gelöscht werden, da bereits AbgabeTermine bzw Abgaben vorhanden sind';
										$return = false;
									}
									else
									{
										if($projektarbeit->delete($_POST['projektarbeit_id']))
										{
											$return = true;
										}
										else
										{
											$errormsg = $projektarbeit->errormsg;
											$return = false;
										}
									}
								}
							}
						}
					}
					else
					{
						$errormsg = 'Fehler beim Loeschen';
						$return = false;
					}
				}
				else
				{
					$errormsg = 'Fehler beim Loeschen';
					$return = false;
				}
			}
			else
			{
				$return = false;
				$errormsg  = 'Fehlerhafte Parameteruebergabe';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='saveprojektbetreuer')  // **** Projektbetreuer **** //
	{
		if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') && !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			$projektbetreuer = new projektbetreuer();

			if($_POST['neu']=='false')
			{
				if($projektbetreuer->load($_POST['person_id_old'], $_POST['projektarbeit_id'], $_POST['betreuerart_kurzbz_old']))
				{
					$projektbetreuer->new = false;
				}
				else
				{
					$error = true;
					$return = false;
					$errormsg = $projektbetreuer->errormsg;
				}
			}
			else
			{
				if($projektbetreuer->load($_POST['person_id'], $_POST['projektarbeit_id'], $_POST['betreuerart_kurzbz']))
				{
					$error = true;
					$errormsg = 'Diese/r BetreuerIn ist bereits zugeteilt';
				}
				$projektbetreuer->new = true;
				$projektbetreuer->insertamum = date('Y-m-d H:i:s');
				$projektbetreuer->insertvon = $user;
			}

			$projektbetreuer->person_id = $_POST['person_id'];
			$projektbetreuer->person_id_old = $_POST['person_id_old'];
			$projektbetreuer->projektarbeit_id = $_POST['projektarbeit_id'];
			$projektbetreuer->note = $_POST['note'];
			$projektbetreuer->faktor = str_replace(',','.', $_POST['faktor']);
			$projektbetreuer->name = $_POST['name'];
			$projektbetreuer->punkte = str_replace(',','.', $_POST['punkte']);
			$projektbetreuer->stunden = str_replace(',','.', $_POST['stunden']);
			$projektbetreuer->stundensatz = str_replace(',','.', $_POST['stundensatz']);
			$projektbetreuer->betreuerart_kurzbz = $_POST['betreuerart_kurzbz'];
			$projektbetreuer->betreuerart_kurzbz_old = $_POST['betreuerart_kurzbz_old'];
			$projektbetreuer->updateamum = date('Y-m-d H:i:s');
			$projektbetreuer->updatevon = $user;

			if(!$error)
			{
				if($projektbetreuer->save())
				{
					$return = true;
				}
				else
				{
					$return = false;
					$errormsg = $projektbetreuer->errormsg;
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deleteprojektbetreuer')
	{
		if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') && !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			// Wenn der Projektbetreuer schon einen Vertrag hat, wird das Loeschen verhindert
			if (isset($_POST['vertrag_id']) && is_numeric($_POST['vertrag_id']) &&
				(defined('FAS_STUDIERENDE_PROJEKTARBEIT_VERTRAGSDETAILS_ANZEIGEN') && FAS_STUDIERENDE_PROJEKTARBEIT_VERTRAGSDETAILS_ANZEIGEN))
			{
				$return = false;
				$errormsg = 'Löschen nur nach Stornierung des Vertrags möglich.';
			}
			else
			{
				//Loescht einen Projektbetreuer Eintrag
				if (isset($_POST['person_id']) && is_numeric($_POST['person_id'])) {
					$projektbetreuer = new projektbetreuer();
					if ($projektbetreuer->delete($_POST['person_id'], $_POST['projektarbeit_id'], $_POST['betreuerart_kurzbz'])) {
						$return = true;
					} else {
						$errormsg = $projektbetreuer->errormsg;
						$return = false;
					}
				} else {
					$return = false;
					$errormsg = 'Fehlerhafte Parameteruebergabe';
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='getprivatemailadress')
	{
		if(isset($_POST['person_ids']))
		{
			$pers_arr = explode(';',$_POST['person_ids']);
			$data='';
			$anz_error=0;

			foreach ($pers_arr as $person_id)
			{
				if(is_numeric($person_id))
				{
					$qry = "SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp='email'
						AND person_id=".$db->db_add_param($person_id, FHC_INTEGER)." AND zustellung=true LIMIT 1";
					if($result = $db->db_query($qry))
					{
						if($row = $db->db_fetch_object($result))
						{
							if($data!='')
								$data.=$emailadressentrennzeichen.$row->kontakt;
							else
								$data = $row->kontakt;
						}
						else
						{
							$anz_error++;
						}
					}
				}
			}
			if($data!='')
			{
				if($anz_error==0)
					$return = true;
				else
				{
					$return = false;
					$errormsg = "Bei $anz_error Personen wurde keine Emailadresse gefunden!";
				}
			}
			else
			{
				$return = false;
				$errormsg = 'Es wurde keine Privatadresse gefunden';
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='getReihungstestPunkte')
	{
		//Liefert die Reihungstestpunkte eines Prestudenten
		if(isset($_POST['person_id']) && isset($_POST['reihungstest_id']) && isset($_POST['studienplan_studiengang_kz']))
		{
			$pruefling = new pruefling();
			if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
				$data = $pruefling->getReihungstestErgebnisPerson($_POST['person_id'], true, $_POST['reihungstest_id'], false, null, $_POST['studienplan_studiengang_kz']);
			else
				$data = $pruefling->getReihungstestErgebnisPerson($_POST['person_id'], false, $_POST['reihungstest_id'], false, null, $_POST['studienplan_studiengang_kz']);

			// Runden auf 4 Nachkommastellen
			$data = number_format($data, 4, '.','');
			$return = true;
		}
		else
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='getstundensatz')
	{
		if(isset($_POST['person_id']))
		{
			$qry = "SELECT stundensatz FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid)
					WHERE person_id=".$db->db_add_param($_POST['person_id'], FHC_INTEGER);
			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))
				{
					$data = $row->stundensatz;
					$return = true;
				}
				else
				{
					$data = '80.00';
					$return = true;
				}
			}
			else
			{
				$return = false;
				$errormsg = 'Unbekannter Fehler';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='saveanrechnung')
	{
		$anrechnung = new anrechnung();

		$anrechnung->new = $_POST['neu'];
		$anrechnung->anrechnung_id = $_POST['anrechnung_id'];
		$anrechnung->prestudent_id = $_POST['prestudent_id'];
		$anrechnung->lehrveranstaltung_id = $_POST['lehrveranstaltung_id'];
		$anrechnung->begruendung_id = $_POST['begruendung_id'];
		isset($_POST['lehrveranstaltung_id_kompatibel']) ? $anrechnung->lehrveranstaltung_id_kompatibel = $_POST['lehrveranstaltung_id_kompatibel'] : $anrechnung->lehrveranstaltung_id_kompatibel = null;
		isset($_POST['notiz_id']) ? $anrechnung->notiz_id = $_POST['notiz_id'] : $anrechnung->notiz_id = null;
		$anrechnung->genehmigt_von = $_POST['genehmigt_von'];
		$anrechnung->insertvon = $user;
		$anrechnung->updatevon = $user;

		if($anrechnung->save())
		{
			$return = true;
		}
		else
		{
			$return = false;
			$errormsg = $anrechnung->errormsg;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deleteanrechnung')
	{
		//Loescht eine Anrechunng
		if(isset($_POST['anrechnung_id']) && is_numeric($_POST['anrechnung_id']))
		{
			if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'], 'suid') && !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid'))
			{
				$return = false;
				$error = true;
				$errormsg = 'Sie haben keine Berechtigung';
			}
			else
			{
				$anrechnung = new anrechnung();

				if($anrechnung->delete($_POST['anrechnung_id']))
				{
					$return = true;
				}
				else
				{
					$errormsg = $anrechnung->errormsg;
					$return = false;
				}
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}

    //******** AUFNAHMETERMINE *****
	elseif(isset($_POST['type']) && $_POST['type']=='AufnahmeTermineSave')  // **** AufnahmeTermine **** //
	{
		//Speichert einen Aufnahmetermin einer Person
		if(isset($_POST['prestudent_id']) && is_numeric($_POST['prestudent_id']))
		{
			$prestudent = new prestudent();
			if(!$prestudent->load($_POST['prestudent_id']))
			{
				$return = false;
				$error = true;
				$errormsg = $prestudent->errormsg;
			}
			else
			{
				if(!$rechte->isBerechtigt('admin', $prestudent->studiengang_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $prestudent->studiengang_kz, 'suid'))
				{
					$return = false;
					$error = true;
					$errormsg = 'Sie haben keine Berechtigung';
				}
				else
				{
					$rt_person_id = $_POST['rt_person_id'];
					$rt_id = $_POST['rt_id'];
					$person_id = $_POST['person_id'];
					if($person_id=='')
						$person_id=$prestudent->person_id;
					$punkte = str_replace(',','.',$_POST['punkte']);
					$teilgenommen = ($_POST['teilgenommen']=='true'?true:false);
					$anmeldedatum = $_POST['anmeldedatum'];
					$studienplan_id = $_POST['studienplan_id'];

					$reihungstest = new reihungstest();

					if($rt_person_id!='')
					{
						$reihungstest->loadReihungstestPerson($rt_person_id);
					}
					else
					{
						$reihungstest->new=true;
					}

					$warnung_zu_viele_teilnehmer = false;
					$warnung_raumzuordnungentfernt = false;
					if($reihungstest->reihungstest_id != $rt_id)
					{
						// Wenn ein neuer Reihungstesttermin ausgewählt wird, dann wird geprueft ob
						// noch genuegend Platz vorhanden ist.
						$rt_help = new reihungstest();
						$aktuelle_anzahl = $rt_help->getTeilnehmerAnzahl($rt_id);
						$rt_help->load($rt_id);
						$max_teilnehmer = $rt_help->max_teilnehmer;
						if($max_teilnehmer == '')
						{
							// Wenn keine Teilnehmer eingetragen sind, dann schauen welche Raeume zugeteilt
							// sind und wie viel Platz dort ist
							$max_teilnehmer = $rt_help->getPlatzAnzahlRaum($rt_id);
						}
						if($max_teilnehmer!='' && $aktuelle_anzahl >= $max_teilnehmer)
							$warnung_zu_viele_teilnehmer = true;

						if ($reihungstest->ort_kurzbz != '')
						{
							$reihungstest->ort_kurzbz = '';
							$warnung_raumzuordnungentfernt = true;
						}
					}
					$reihungstest->reihungstest_id = $rt_id;
					$reihungstest->person_id = $person_id;
					$reihungstest->punkte = $punkte;
					$reihungstest->teilgenommen = $teilgenommen;
					$reihungstest->anmeldedatum = $anmeldedatum;
					$reihungstest->studienplan_id = $studienplan_id;
                    ($reihungstest->new) ? $reihungstest->insertamum = date('Y-m-d H:i:s'): $reihungstest->updateamum = date('Y-m-d H:i:s');
                    ($reihungstest->new) ? $reihungstest->insertvon = $user : $reihungstest->updatevon = $user;

					if($reihungstest->savePersonReihungstest())
					{
						$return = true;
						$error = false;
						$errormsg = 'Erfolgreich gespeichert';
						$data = $reihungstest->rt_person_id;
						if($warnung_zu_viele_teilnehmer)
						{
							$return = false;
							$error = true;
							$errormsg .= ' - Achtung - die Maximalanzahl der Teilnehmer wurde überschritten;'.
							' Prüfen Sie ob genug Platz zur Verfügung steht';
						}
						if ($warnung_raumzuordnungentfernt)
						{
							$return = false;
							$error = true;
							$errormsg .= ' - Achtung - Die Person war bereits einem Raum für den Reihungstest zugeordnet.'.
							'Die Raumzuordnung wurde entfernt.';
						}
					}
					else
					{
						$return = false;
						$error = true;
						$errormsg = $reihungstest->errormsg;
					}
				}
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='AufnahmeTermineDelete')
	{
		//Loescht einen Aufnahmetermin einer Person
		if(isset($_POST['rt_person_id']) && is_numeric($_POST['rt_person_id']))
		{
			$rt_person_id = $_POST['rt_person_id'];
			$reihungstest = new reihungstest();
			if(!$reihungstest->loadReihungstestPerson($rt_person_id))
			{
				$return = false;
				$error = true;
				$errormsg = $reihungstest->errormsg;
			}
			else
			{
				$prestudent = new prestudent();
				if(!$prestudent->getPrestudenten($reihungstest->person_id))
				{
					$return = false;
					$error = true;
					$errormsg = $prestudent->errormsg;
				}
				else
				{
					$berechtigt = false;
					foreach($prestudent->result as $row_prest)
					{
						if($rechte->isBerechtigt('admin', $row_prest->studiengang_kz, 'suid') || $rechte->isBerechtigt('assistenz', $row_prest->studiengang_kz, 'suid'))
						{
							$berechtigt = true;
							break;
						}
					}

					if(!$berechtigt)
					{
						$return = false;
						$error = true;
						$errormsg = 'Sie haben keine Berechtigung';
					}
					else
					{
						$reihungstest = new reihungstest();

						if($reihungstest->deletePersonReihungstest($rt_person_id))
						{
							$return = true;
							$error = false;
							$errormsg = 'Erfolgreich geloescht';
						}
						else
						{
							$return = false;
							$error = true;
							$errormsg = $reihungstest->errormsg;
						}
					}
				}
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='AufnahmeTermineSaveGesamtpunkte')
	{
		//Speichert einen Aufnahmetermin einer Person
		if(isset($_POST['prestudent_id']) && is_numeric($_POST['prestudent_id']))
		{
			$prestudent = new prestudent();
			if(!$prestudent->load($_POST['prestudent_id']))
			{
				$return = false;
				$error = true;
				$errormsg = $prestudent->errormsg;
			}
			else
			{
				if(!$rechte->isBerechtigt('admin', $prestudent->studiengang_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $prestudent->studiengang_kz, 'suid'))
				{
					$return = false;
					$error = true;
					$errormsg = 'Sie haben keine Berechtigung';
				}
				else
				{
					$punkte = str_replace(',','.',$_POST['punkte']);
					$reihungstestangetreten = ($_POST['reihungstestangetreten']=='true'?true:false);
					$prestudent->punkte = $punkte;
					$prestudent->reihungstestangetreten = $reihungstestangetreten;
					$prestudent->aufnahmegruppe_kurzbz = $_POST['aufnahmegruppe_kurzbz'];
					$prestudent->new=false;

					if($prestudent->save())
					{
						$return = true;
						$error = false;
						$errormsg = 'Erfolgreich gespeichert';
					}
					else
					{
						$return = false;
						$error = true;
						$errormsg = $reihungstest->errormsg;
					}
				}
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='AufnahmeTermineBerechneGesamtpunkte')
	{
		//Speichert einen Aufnahmetermin einer Person
		if(isset($_POST['prestudent_id']) && is_numeric($_POST['prestudent_id']))
		{
			$prestudent = new prestudent();
			if(!$prestudent->load($_POST['prestudent_id']))
			{
				$return = false;
				$error = true;
				$errormsg = $prestudent->errormsg;
			}
			else
			{
				if(!$rechte->isBerechtigt('admin', $prestudent->studiengang_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $prestudent->studiengang_kz, 'suid'))
				{
					$return = false;
					$error = true;
				}
				else
				{
					$gesamtpunkte_arr = array();
					$reihungstest = new reihungstest();
					if($reihungstest->getReihungstestPerson($prestudent->person_id))
					{
						foreach($reihungstest->result as $row)
						{
							$studienplan = new studienplan();
							if($studienplan->loadStudienplan($row->studienplan_id))
							{
								$studienordnung = new studienordnung();
								if($studienordnung->loadStudienordnung($studienplan->studienordnung_id))
								{
									if($studienordnung->studiengang_kz == $prestudent->studiengang_kz)
									{
										$rt_obj = new reihungstest();
										$rt_obj->load($row->reihungstest_id);
										if(!isset($gesamtpunkte_arr[$rt_obj->studiensemester_kurzbz]))
											$gesamtpunkte_arr[$rt_obj->studiensemester_kurzbz]=0;
										$gesamtpunkte_arr[$rt_obj->studiensemester_kurzbz] += $row->punkte;
									}
								}
								else
								{
									$return = false;
									$errormsg = $studienordnung->errormsg;
									$error = true;
								}
							}
							else
							{
								$return = false;
								$errormsg = $studienplan->errormsg;
								$error = true;
							}
						}

						if(!$error)
						{
							if(count(array_keys($gesamtpunkte_arr))>0)
							{
								$studiensemester = new studiensemester();
								$stsem = $studiensemester->getYoungestFromArray(array_keys($gesamtpunkte_arr));
								$gesamtpunkte = $gesamtpunkte_arr[$stsem];
							}
							else
								$gesamtpunkte = '0';
							$return = true;
							$error = false;
							$data = $gesamtpunkte;
							$errormsg = 'Erfolgreich gespeichert';
						}
					}
					else
					{
						$return = false;
						$error = true;
						$errormsg = $reihungstest->errormsg;
					}
				}
			}
		}
		else
		{
			$return = false;
			$error = true;
			$errormsg = 'Falsche Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savemobilitaet')
	{
		// Speichert Mobilitaet/Gemeinsames Studium
		$prestudent_id = $_POST['prestudent_id'];
		$prestudent_obj = new prestudent();
		$prestudent_obj->load($prestudent_id);

		if(!$rechte->isBerechtigt('student/stammdaten', $prestudent_obj->studiengang_kz, 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'Sie haben keine Berechtigung';
		}
		else
		{
			$mob = new mobilitaet();

			$error = false;
			if(isset($_POST['mobilitaet_id']))
			{
				$mobilitaet_id = $_POST['mobilitaet_id'];
				if(!$mob->load($mobilitaet_id))
				{
					$error=true;
					$errormsg = $mob->errormsg;
				}
			}
			else
			{
				$mob->new = true;
				$mob->inservon = $user;
			}

			if(isset($_POST['studiensemester_kurzbz'])
			&& isset($_POST['mobilitaetsprogramm_code'])
			&& isset($_POST['gsprogramm_id'])
			&& isset($_POST['mobilitaetstyp_kurzbz'])
			&& isset($_POST['firma_id'])
			&& isset($_POST['status_kurzbz'])
			&& isset($_POST['ausbildungssemester']))
			{
				$studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
				$mobilitaetsprogramm_code = $_POST['mobilitaetsprogramm_code'];
				$gsprogramm_id = $_POST['gsprogramm_id'];
				$mobilitaetstyp_kurzbz = $_POST['mobilitaetstyp_kurzbz'];
				$firma_id = $_POST['firma_id'];
				$status_kurzbz = $_POST['status_kurzbz'];
				$ausbildungssemester = $_POST['ausbildungssemester'];
			}
			else
			{
				$error = true;
				$errormsg = 'Fehlerhafte Parameteruebergabe';
				$return = false;
			}

			if(!$error)
			{
				$mob->prestudent_id = $prestudent_id;
				$mob->studiensemester_kurzbz = $studiensemester_kurzbz;
				$mob->mobilitaetsprogramm_code = $mobilitaetsprogramm_code;
				$mob->gsprogramm_id = $gsprogramm_id;
				$mob->mobilitaetstyp_kurzbz = $mobilitaetstyp_kurzbz;
				$mob->firma_id = $firma_id;
				$mob->status_kurzbz = $status_kurzbz;
				$mob->ausbildungssemester = $ausbildungssemester;
				$mob->updatevon = $user;

				if($mob->save())
				{
					$return = true;
					$data = $mob->mobilitaet_id;
				}
				else
				{
					$return = false;
					$error = true;
					$errormsg = $mob->errormsg;
				}
			}
			else
			{
				$return = false;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletemobilitaet')
	{
		$mobilitaet_id = $_POST['mobilitaet_id'];
		$mob = new mobilitaet();
		if($mob->load($mobilitaet_id))
		{
			$prestudent = new prestudent();
			if($prestudent->load($mob->prestudent_id))
			{
				if(!$rechte->isBerechtigt('student/stammdaten',$prestudent->studiengang_kz,'suid'))
				{
					$error = true;
					$return = false;
					$errormsg = 'Sie haben keine Berechtigung';
				}
				else
				{
					if($mob->delete($mobilitaet_id))
					{
						$return = true;
					}
					else
					{
						$errormsg = $mob->errormsg;
						$return = false;
					}
				}
			}
			else
			{
				$error = true;
				$return = false;
				$errormsg = $prestudent->errormsg;
			}
		}
		else
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
			$errormsg  = $mob->errormsg;
		}
	}
	else
	{
		$return = false;
		$errormsg = 'Unkown type: "'.$db->convert_html_chars($_POST['type']).'"';
		$data = '';
	}
}
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:DBDML="http://www.technikum-wien.at/dbdml/rdf#"
>
  <RDF:Seq RDF:about="http://www.technikum-wien.at/dbdml/msg">
	<RDF:li>
		<RDF:Description RDF:about="http://www.technikum-wien.at/dbdml/0" >
			<DBDML:return>'.($return?'true':'false').'</DBDML:return>
			<DBDML:errormsg><![CDATA['.$errormsg.']]></DBDML:errormsg>
			<DBDML:warning><![CDATA['.$warning.']]></DBDML:warning>
			<DBDML:data><![CDATA['.$data.']]></DBDML:data>
		</RDF:Description>
	</RDF:li>
  </RDF:Seq>
</RDF:RDF>';
?>
