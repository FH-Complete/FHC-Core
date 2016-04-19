<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.

 */
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/datum.class.php');
require_once('../include/abschlusspruefung.class.php');
require_once('../include/student.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/studienplan.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/pruefung.class.php');
require_once('../include/projektarbeit.class.php');
require_once('../include/note.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/person.class.php');

if(isset($_SERVER['REMOTE_USER']))
{
	$uid = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);

	if(!$rechte->isBerechtigt('student/noten'))
		die('Sie haben keine Berechtigung für diese Seite');
}

$datum = new datum();

$abschlusspruefung_id = filter_input(INPUT_GET, "abschlusspruefung_id");
$abschlusspruefung = new abschlusspruefung($abschlusspruefung_id);

$studiensemester_kurzbz = filter_input(INPUT_GET, "ss");

$prestudent = new prestudent($abschlusspruefung->prestudent_id);

$studiengang = new studiengang($prestudent->studiengang_kz);

$prestudent = new prestudent();
$prestudent->getLastStatus($prestudent->prestudent_id, $studiensemester_kurzbz, "Student");

$studienplan = new studienplan();
$studienplan->loadStudienplan($prestudent->studienplan_id);

$lehrveranstaltung = new lehrveranstaltung();
$tree = $lehrveranstaltung->getLvTree($prestudent->studienplan_id);

$student = new student();
$student_uid = $student->getUid($prestudent->prestudent_id);
if(!$student_uid)
	die($student->errormsg);

$pruefung = new pruefung();
$pruefung->getPruefungen($student_uid, "fachpruefung");

$projektarbeit = new projektarbeit();
$projektarbeit->getProjektarbeit($student_uid);

if(!$person = new person($prestudent->person_id))
	die($person->errormsg);


echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n";
echo "<abschlusszeugnisse>";
echo "<abschlusszeugnis>";
$modul_temp = "";

echo "<akt_datum>".date('d.m.Y')."</akt_datum>";
echo "<uid>".$student_uid."</uid>";
echo "<vorname>".$person->vorname."</vorname>";
echo "<vornamen>".$person->vornamen."</vornamen>";
echo "<nachname>".$person->nachname."</nachname>";
echo "<geschlecht>".$person->geschlecht."</geschlecht>";
echo "<titelpost>".$person->titelpost."</titelpost>";
echo "<titelpre>".$person->titelpre."</titelpre>";
echo "<gebdatum>".$datum->formatDatum($person->gebdatum, "d.m.Y")."</gebdatum>";
echo "<studiengang_bezeichnung>".$studiengang->bezeichnung."</studiengang_bezeichnung>";
echo "<studienplan_bezeichnung>".$studienplan->bezeichnung."</studienplan_bezeichnung>";
echo "<gesamt_beurteilung>".strtoupper($abschlusspruefung->abschlussbeurteilung_kurzbz)."</gesamt_beurteilung>";
echo "<gesamt_datum>".$datum->formatDatum($abschlusspruefung->datum,"d.m.Y")."</gesamt_datum>";
$note = new note($abschlusspruefung->note);
echo "<gesamt_note>".$note->bezeichnung."</gesamt_note>";

if(!empty($projektarbeit->result))
{
	$lehreinheit = new lehreinheit($projektarbeit->result[0]->lehreinheit_id);
	$lehrveranstaltung = new lehrveranstaltung($lehreinheit->lehrveranstaltung_id);
	$note = new note($projektarbeit->result[0]->note);
	echo "<projektarbeit_titel>".$projektarbeit->result[0]->titel."</projektarbeit_titel>";
	echo "<projektarbeit_note>".$note->bezeichnung."</projektarbeit_note>";
	echo "<projektarbeit_lv>".$lehrveranstaltung->bezeichnung."</projektarbeit_lv>";
}
else
{
	echo "<projektarbeit_titel></projektarbeit_titel>";
}

if(sizeof($tree) > 1)
{
	foreach($tree as $modul)
	{
		if($modul_temp == "")
		{
			echo "<module>";
			echo "<modul>";
			$modul_temp = $modul->bezeichnung;
			echo "<modul_bezeichnung>".$modul->bezeichnung."</modul_bezeichnung>";
			echo "<lehrveranstaltungen>";
			foreach($modul->children as $child)
			{
				echo "<lv_bezeichnung>".$child->bezeichnung."</lv_bezeichnung>";
				echo "<lv_ects>".$child->ects."</lv_ects>";
			}
		}

		if($modul_temp != $modul->bezeichnung && $modul_temp != '')
		{
			echo '</lehrveranstaltungen></modul><modul>';
			$modul_temp = $modul->bezeichnung;
			echo "<modul_bezeichnung>".$modul->bezeichnung."</modul_bezeichnung>";
			echo "<lehrveranstaltungen>";
			foreach($modul->children as $child)
			{
				echo "<lv_bezeichnung>".$child->bezeichnung."</lv_bezeichnung>";
				echo "<lv_ects>".$child->ects."</lv_ects>";
			}
		}
		else
		{
			foreach($modul->children as $child)
			{
				echo "<lv_bezeichnung>".$child->bezeichnung."</lv_bezeichnung>";
				echo "<lv_ects>".$child->ects."</lv_ects>";
			}
		}
	}
	echo '</lehrveranstaltungen></modul></module>';
}
echo '<fachpruefungen>';
foreach($pruefung->result as $key => $prf)
{
	echo '<pruefung_'.$key.'>';
	echo '<prf_'.$key.'_bezeichnung>'.$prf->pruefungstyp_beschreibung.'</prf_'.$key.'_bezeichnung>';
	echo '<prf_'.$key.'_datum>'.$datum->formatDatum($prf->datum,"d.m.Y").'</prf_'.$key.'_datum>';
	echo '<prf_'.$key.'_note>'.$prf->note.'</prf_'.$key.'_note>';
	echo '<prf_'.$key.'_note_bezeichnung>'.$prf->note_bezeichnung.'</prf_'.$key.'_note_bezeichnung>';
	echo '<prf_'.$key.'_lv_bezeichnung>'.$prf->lehrveranstaltung_bezeichnung.'</prf_'.$key.'_lv_bezeichnung>';
	echo '</pruefung_'.$key.'>';
}
echo '</fachpruefungen>';
echo "</abschlusszeugnis>";
echo "</abschlusszeugnisse>";
