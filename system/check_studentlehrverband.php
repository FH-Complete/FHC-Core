<?php

/**
 * Copyright (C) 2006 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */

/**
 * Script zur Pruefung und Korrektur
 * moeglicher Inkonsistenzen
 * 
 * - Studenten ohne Prestudent_id werden korrigiert
 * - Inkonsistenzen der Tabellen tbl_studentlehrverband, tbl_student werden korrigiert
 */

require_once(dirname(__FILE__).'/../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../include/person.class.php');
require_once(dirname(__FILE__).'/../include/benutzer.class.php');
require_once(dirname(__FILE__).'/../include/student.class.php');
require_once(dirname(__FILE__).'/../include/prestudent.class.php');
require_once(dirname(__FILE__).'/../include/lehrverband.class.php');
require_once(dirname(__FILE__).'/../include/mail.class.php');

$db = new basis_db();

$anzahl_neue_prestudent_id = 0;
$anzahl_fehler_prestudent = 0;
$anzahl_gruppenaenderung = 0;
$anzahl_gruppenaenderung_fehler = 0;
$text = '';
$statistik = '';
$abunterbrecher_verschoben_error = 0;
$abunterbrecher_verschoben = 0;

// Bei Studenten mit fehlener Prestudent_id wird die passende id ermittelt und Eingetragen
$qry = "SELECT student_uid, studiengang_kz FROM public.tbl_student WHERE prestudent_id IS NULL";

if ($result = $db->db_query($qry))
{
	$text .= "Suche Studenten mit fehlender Prestudent_id ...\n\n";

	while ($row = $db->db_fetch_object($result))
	{
		$qry_id = "SELECT tbl_prestudent.prestudent_id
				FROM campus.vw_student
				JOIN public.tbl_prestudent USING(person_id)
			WHERE uid = ".$db->db_add_param($row->student_uid)."
			  AND tbl_prestudent.studiengang_kz = ".$db->db_add_param($row->studiengang_kz);

		if ($result_id = $db->db_query($qry_id))
		{
			if ($db->db_num_rows($result_id) == 1)
			{
				if ($row_id = $db->db_fetch_object($result_id))
				{
					$qry_upd = "UPDATE public.tbl_student
							SET prestudent_id = ".$db->db_add_param($row_id->prestudent_id)."
						WHERE student_uid = ".$db->db_add_param($row->student_uid);

					if ($db->db_query($qry_upd))
					{
						$text .= "Prestudent_id von $row->student_uid wurde auf $row_id->prestudent_id gesetzt\n";
						$anzahl_neue_prestudent_id++;
					}
				}
				else
				{
					$text .= "unbekannter Fehler\n";
					$anzahl_fehler_prestudent++;
				}
			}
			elseif ($db->db_num_rows($result_id) > 1)
			{
				$text .= "Student $row->student_uid hat keine Prestudent_id und MEHRERE passende Prestudenteintraege\n";
				$anzahl_fehler_prestudent++;
			}
			elseif( $db->db_num_rows($result_id) == 0)
			{
				$text .= "Student $row->student_uid hat keine Prestudent_id und KEINE passenden Prestudenteintraege\n";
				$anzahl_fehler_prestudent++;
			}
		}
		else
		{
			$text .= "Fehler bei Abfrage:".$db->db_last_error()."\n";
			$anzahl_fehler_prestudent++;
		}
	}
}

// Gruppenzuteilung von Abbrechern und Unterbrechern korrigieren.
// Abbrecher werden in die Gruppe 0A verschoben
// Unterbrecher in die Gruppe 0B
$text .= "\n\nKorrigiere Gruppenzuteilungen von Ab-/Unterbrechern\n";

// Alle Ab-/Unterbrecher holen die nicht im 0. Semester sind
$qry = "SELECT
			student_uid,
			tbl_student.studiengang_kz,
			tbl_prestudent.prestudent_id,
			status_kurzbz,
			studiensemester_kurzbz
		FROM
			public.tbl_student,
			public.tbl_prestudent,
			public.tbl_prestudentstatus
		WHERE
			tbl_student.prestudent_id=tbl_prestudent.prestudent_id AND
			tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id AND
			(
				tbl_prestudentstatus.status_kurzbz='Unterbrecher' OR
				tbl_prestudentstatus.status_kurzbz='Abbrecher'
			)
			AND
			EXISTS (SELECT
						*
					FROM
						public.tbl_studentlehrverband
					WHERE
			        	student_uid=tbl_student.student_uid AND
			        	studiensemester_kurzbz=tbl_prestudentstatus.studiensemester_kurzbz AND
			        	semester<>0
			        )
		";

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		// Eintrag nur korrigieren wenn der Abbrecher/Unterbrecher Status der letzte in diesem Studiensemester ist
		$prestd = new prestudent();
		$prestd->getLastStatus($row->prestudent_id, $row->studiensemester_kurzbz);

		if ($prestd->status_kurzbz == 'Unterbrecher' || $prestd->status_kurzbz == 'Abbrecher')
		{
			// Studentlehrverbandeintrag aktualisieren
			$student = new student();
			if ($student->studentlehrverband_exists($row->student_uid, $row->studiensemester_kurzbz))
				$student->new = false;
			else
			{
				$student->new = true;
				$student->insertamum = date('Y-m-d H:i:s');
				$student->insertvon = 'chkstudentlvb';
			}

			$student->uid = $row->student_uid;
			$student->studiensemester_kurzbz=$row->studiensemester_kurzbz;
			$student->studiengang_kz = $row->studiengang_kz;
			$student->semester = '0';
			$student->verband = ($prestd->status_kurzbz == 'Unterbrecher' ? 'B' : 'A');
			$student->gruppe = ' ';
			$student->updateamum = date('Y-m-d H:i:s');
			$student->updatevon = 'chkstudentlvb';

			// Pruefen ob der Lehrverband exisitert, wenn nicht dann wird er angelegt
			$lehrverband = new lehrverband();
			if (!$lehrverband->exists($student->studiengang_kz, $student->semester, $student->verband, $student->gruppe))
			{
				$lehrverband->studiengang_kz = $student->studiengang_kz;
				$lehrverband->semester = $student->semester;
				$lehrverband->verband = $student->verband;
				$lehrverband->gruppe = $student->gruppe;
				$lehrverband->bezeichnung = ($student->verband == 'A' ? 'Abbrecher' : 'Unterbrecher');

				$lehrverband->save(true);
			}

			if ($student->save_studentlehrverband())
			{
				$text .= "Student $student->uid wurde im $row->studiensemester_kurzbz in die Gruppe $student->semester$student->verband verschoben\n";
				$abunterbrecher_verschoben++;
			}
			else
			{
				$text .= "Fehler biem Speichern des Lehrverbandeintrages bei $student->student_uid:".$student->errormsg."\n";
				$abunterbrecher_verschoben_error++;
			}
		}
	}
}

// Unterschiedliche Gruppenzuteilungen in tbl_studentlehrverband - tbl_student korrigieren

$stsem = new studiensemester();

$stsem = $stsem->getNearest();

$text .= "\n\nKorrigiere Inkonsitenzen in den Tabellen tbl_studentlehrverband, tbl_student (Verwendetes Studiensemester: $stsem)\n\n";

$qry = "SELECT
			tbl_student.studiengang_kz as studiengang_kz_old,
			tbl_student.semester as semester_old,
			tbl_student.verband as verband_old,
			tbl_student.gruppe as gruppe_old,
			tbl_studentlehrverband.student_uid,
			tbl_studentlehrverband.studiengang_kz,
			tbl_studentlehrverband.semester,
			tbl_studentlehrverband.verband,
			tbl_studentlehrverband.gruppe
		FROM
			public.tbl_student JOIN public.tbl_studentlehrverband USING(student_uid)
		WHERE
			tbl_studentlehrverband.studiensemester_kurzbz=".$db->db_add_param($stsem)." AND
			(
				tbl_student.studiengang_kz<>tbl_studentlehrverband.studiengang_kz OR
				tbl_student.semester<>tbl_studentlehrverband.semester OR
				tbl_student.verband<>tbl_studentlehrverband.verband OR
				tbl_student.gruppe<>tbl_studentlehrverband.gruppe
			)";

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE public.tbl_student
			SET studiengang_kz = ".$db->db_add_param($row->studiengang_kz).",
				semester = ".$db->db_add_param($row->semester).",
				verband = ".$db->db_add_param($row->verband).",
				gruppe = ".$db->db_add_param($row->gruppe)."
			WHERE student_uid = ".$db->db_add_param($row->student_uid);

		if ($db->db_query($qry))
		{
			$text .= "Bei Student $row->student_uid wurde die Gruppenzuordnung
				von $row->studiengang_kz_old/$row->semester_old/$row->verband_old/$row->gruppe_old
				auf $row->studiengang_kz/$row->semester/$row->verband/$row->gruppe geaendert\n";
			$anzahl_gruppenaenderung++;
		}
		else
		{
			$text .= "Fehler beim Aendern der Gruppe: ".$db->db_last_error()."\n";
			$anzahl_gruppenaenderung_fehler++;
		}
	}
}
else
	$text .= "Fehler bei Abfrage: ".$db->db_last_error();

$statistik .= "Prestudent_id wurde bei $anzahl_neue_prestudent_id Studenten korrigiert\n";
$statistik .= "$anzahl_fehler_prestudent Fehler sind bei der Korrektur der Prestudent_id aufgetreten\n";
$statistik .= "$abunterbrecher_verschoben Studenten wurden ins 0. Semester verschoben\n ";
$statistik .= "$abunterbrecher_verschoben_error Fehler sind beim Verschieben aufgetreten\n ";
$statistik .= "Bei $anzahl_gruppenaenderung Studenten wurde die Gruppenzuordnung korrigiert\n";
$statistik .= "$anzahl_gruppenaenderung_fehler Fehler sind bei der Korrektur der Gruppenzuordnung aufgetreten\n";
$statistik .= "\n\n";

$mail = new mail(MAIL_ADMIN, 'vilesci@'.DOMAIN, 'CHECK Studentlehrverband', $statistik.$text);
if ($mail->send())
	echo 'Mail an '.MAIL_ADMIN.' wurde versandt';
else
	echo 'Fehler beim Versenden des Mails an '.MAIL_ADMIN;

echo "\n\n".$statistik.$text;

?>

