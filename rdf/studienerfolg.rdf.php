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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Raab <gerald.raab@technikum-wien.at>.
 */

// header f¸r no cache
//header("Cache-Control: no-cache");
//header("Cache-Control: post-check=0, pre-check=0",false);
//header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
//header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/zeugnisnote.class.php');
require_once('../include/datum.class.php');
require_once('../include/note.class.php');
require_once('../include/studiensemester.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

//$user = get_uid();
//loadVariables($conn, $user);
$datum = new datum();

function draw_studienerfolg($uid, $studiensemester_kurzbz)
{
	global $conn, $xml, $note_arr, $datum;

	$query = "SELECT tbl_student.matrikelnr, tbl_student.studiengang_kz, tbl_studiengang.bezeichnung, tbl_studentlehrverband.semester, tbl_person.titelpre, tbl_person.titelpost, tbl_person.vorname, tbl_person.nachname,tbl_person.gebdatum, tbl_studiensemester.bezeichnung as sembezeichnung FROM public.tbl_person, public.tbl_student, public.tbl_studiengang, public.tbl_benutzer, public.tbl_studentlehrverband, public.tbl_studiensemester WHERE tbl_student.studiengang_kz = tbl_studiengang.studiengang_kz and tbl_student.student_uid = tbl_benutzer.uid and tbl_benutzer.person_id = tbl_person.person_id and tbl_student.student_uid = '".$uid."' and tbl_studentlehrverband.student_uid=tbl_student.student_uid and tbl_studiensemester.studiensemester_kurzbz = tbl_studentlehrverband.studiensemester_kurzbz and tbl_studentlehrverband.studiensemester_kurzbz = '".$studiensemester_kurzbz."'";

	if($result = pg_query($conn, $query))
	{
			if(!$row = pg_fetch_object($result))
				return false; //die('Student not found');
	}
	else
		return false; //die('Student not found');

	$studiensemester = new studiensemester($conn);
	$studiensemester_aktuell = $studiensemester->getNearest();

	$semester_aktuell='';
	$qry_semester = "SELECT tbl_student.semester FROM public.tbl_student, public.tbl_prestudentrolle WHERE tbl_student.prestudent_id=tbl_prestudentrolle.prestudent_id AND tbl_prestudentrolle.rolle_kurzbz in('Student','Incoming','Outgoing','Praktikant','Diplomand') AND studiensemester_kurzbz='$studiensemester_aktuell' AND tbl_student.student_uid = '".$uid."'";
	if($result_semester = pg_query($conn, $qry_semester))
		if($row_semester = pg_fetch_object($result_semester))
			$semester_aktuell=$row_semester->semester;

	if($semester_aktuell=='')
		$studiensemester_aktuell='';
	$stgl_query = "SELECT titelpre, titelpost, vorname, nachname FROM public.tbl_person, public.tbl_benutzer, public.tbl_benutzerfunktion WHERE tbl_person.person_id = tbl_benutzer.person_id and tbl_benutzer.uid = tbl_benutzerfunktion.uid and tbl_benutzerfunktion.funktion_kurzbz = 'stgl' and tbl_benutzerfunktion.studiengang_kz = '".$row->studiengang_kz."'";
	if($stgl_result = pg_query($conn, $stgl_query))
			$stgl_row = pg_fetch_object($stgl_result);
	else
		die('Studiengangsleiter wurde nicht gefunden');
	$xml .= "	<studienerfolg>";
	$xml .= "		<logopath>".DOC_ROOT."skin/images/</logopath>";
	$xml .= "		<studiensemester>".$row->sembezeichnung."</studiensemester>";
	$xml .= "		<studiensemester_aktuell>".$studiensemester_aktuell."</studiensemester_aktuell>";
	$xml .=	"		<semester>".$row->semester."</semester>";
	$xml .=	"		<semester_aktuell>".$semester_aktuell.($semester_aktuell!=''?'. Semester':'')."</semester_aktuell>";
	$xml .= "		<studiengang>".$row->bezeichnung."</studiengang>";
	$xml .= "		<studiengang_kz>".sprintf('%04s',$row->studiengang_kz)."</studiengang_kz>";
	$xml .= "		<titelpre>".$row->titelpre."</titelpre>";
	$xml .= "		<titelpost>".$row->titelpost."</titelpost>";
	$xml .= "		<vorname>".$row->vorname."</vorname>";
	$xml .= "		<nachname>".$row->nachname."</nachname>";
	$gebdatum = date('d.m.Y',strtotime($row->gebdatum));
	$xml .= "		<gebdatum>".$gebdatum."</gebdatum>";
	$xml .= "		<matrikelnr>".$row->matrikelnr."</matrikelnr>";
	$xml .= "		<studiensemester_kurzbz>".$studiensemester_kurzbz."</studiensemester_kurzbz>";
	$datum_aktuell = date('d.m.Y');
	$xml .= "		<datum>".$datum_aktuell."</datum>";

	if(isset($_REQUEST['typ']) && $_REQUEST['typ']=='finanzamt')
		$xml .= "		<finanzamt>(gem‰ﬂ ß2 Abs. 1 lit.b des Familienlastenausgleichsgesetzes 1967 zur Vorlage beim Wohnsitzfinanzamt)</finanzamt>";
	else
		$xml .= "		<finanzamt></finanzamt>";

	$obj = new zeugnisnote($conn, null, null, null, false);

	$obj->getZeugnisnoten($lehrveranstaltung_id=null, $uid, $studiensemester_kurzbz);

	$qry = "SELECT wochen FROM public.tbl_semesterwochen WHERE studiengang_kz='$row->studiengang_kz' AND semester='$row->semester'";
	$wochen = 15;
	if($result_wochen = pg_query($conn, $qry))
	{
		if($row_wochen = pg_fetch_object($result_wochen))
		{
			$wochen = $row_wochen->wochen;
		}
	}

	$gesamtstunden=0;
	$gesamtects=0;
	$notensumme=0;
	$anzahl=0;
	foreach ($obj->result as $row)
	{
		//Note darf nicht teilnote(0), negativ(5), noch nicht eingetragen(7), nicht beurteilt (9), nicht erfolgreich absolviert (13), angerechnet(6) sein
		if($row->zeugnis && $row->note!=0 && $row->note!=5 && $row->note!=7 && $row->note!=9 && $row->note!=13 && $row->note!=6)
		{
			if ($row->note)
				$note = $note_arr[$row->note];
			else
				$note = "";
			if($note!='')
			{
				$xml .= "			<unterrichtsfach>";
				$xml .= "				<bezeichnung><![CDATA[".$row->lehrveranstaltung_bezeichnung."]]></bezeichnung>";
				$xml .= "				<note>".$note."</note>";
				$xml .= "				<sws>".sprintf('%.1f',$row->semesterstunden)."</sws>"; ///$wochen
				$xml .= "				<ects>".$row->ects."</ects>";
				if($row->benotungsdatum!='')
					$xml .= "				<benotungsdatum>".date('d.m.Y',$datum->mktime_fromtimestamp($row->benotungsdatum))."</benotungsdatum>";
				$xml .= "			</unterrichtsfach>";

				$gesamtstunden +=$row->semesterstunden;
				$gesamtects += $row->ects;
				if(is_numeric($note))
				{
					$notensumme += $note;
					$anzahl++;
				}
			}
		}
	}


	if($anzahl!=0)
	{
		$schnitt = ($notensumme/$anzahl);
	}
	else
		$schnitt = 0;
	$xml .= "		<gesamtstunden>$gesamtstunden</gesamtstunden>";
	$xml .= "		<gesamtects>$gesamtects</gesamtects>";
	$xml .= "		<schnitt>".sprintf('%.2f',$schnitt)."</schnitt>";
	$xml .= "	</studienerfolg>";
}

if (isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{

	if(isset($_GET['uid']))
		$uid = $_GET['uid'];
	else
		$uid = null;

	$uid_arr = explode(";",$uid);

	if ($uid_arr[0] == "")
	{
		unset($uid_arr[0]);
		$uid_arr = array_values($uid_arr);
	}

	$note_arr = array();
	$note = new note($conn);
	$note->getAll();
	foreach ($note->result as $n)
		$note_arr[$n->note] = $n->anmerkung;

	if(isset($_GET['ss']))
		$studiensemester_kurzbz = $_GET['ss'];
	else
		$studiensemester_kurzbz = $semester_aktuell;

	//Daten holen

	$xml = "<?xml version='1.0' encoding='ISO-8859-15' standalone='yes'?>\n";
	$xml .= "<studienerfolge>";

	if(isset($_GET['all']))
	{
		for ($i = 0; $i < sizeof($uid_arr); $i++)
		{
			//Studienbestaetigung fuer alle Semester dieses Studenten
			$qry = "SELECT * FROM public.tbl_studiensemester WHERE studiensemester_kurzbz in(SELECT studiensemester_kurzbz FROM public.tbl_prestudentrolle JOIN public.tbl_student USING(prestudent_id) WHERE student_uid='".addslashes($uid_arr[$i])."') ORDER BY start";
			if($result = pg_query($conn, $qry))
				while($row = pg_fetch_object($result))
					draw_studienerfolg($uid_arr[$i], $row->studiensemester_kurzbz);
		}
	}
	else
	{
		//Studienbestaetigung fuer ein bestimmtes Semester
		for ($i = 0; $i < sizeof($uid_arr); $i++)
		{
			draw_studienerfolg($uid_arr[$i], $studiensemester_kurzbz);
		}
	}

	$xml .= "</studienerfolge>";
	echo $xml;
}
?>