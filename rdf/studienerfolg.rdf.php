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
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/zeugnisnote.class.php');
require_once('../include/datum.class.php');
require_once('../include/note.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/student.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/studienplan.class.php');

$datum = new datum();
$db = new basis_db();

function draw_studienerfolg($uid, $studiensemester_kurzbz)
{
	global $xml, $note_arr, $datum, $note_wert;

	$db = new basis_db();
	$query = "SELECT
				tbl_student.matrikelnr, tbl_student.studiengang_kz, tbl_studiengang.bezeichnung,
				tbl_studentlehrverband.semester, tbl_person.titelpre, tbl_person.titelpost,
				tbl_person.vorname, tbl_person.nachname,tbl_person.gebdatum,
				tbl_studiensemester.bezeichnung as sembezeichnung,
				tbl_studiengang.english as bezeichnung_englisch,
				tbl_studiengang.typ, tbl_studiengang.orgform_kurzbz,
       			tbl_studiengangstyp.bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE sprache='German')] as studiengangstypbezeichnung, 
       			tbl_studiengangstyp.bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE sprache='English')] as studiengangstypbezeichnung_englisch, 
       			tbl_person.matr_nr
			FROM
				public.tbl_person, public.tbl_student, public.tbl_studiengang, public.tbl_studiengangstyp, public.tbl_benutzer,
				public.tbl_studentlehrverband, public.tbl_studiensemester
			WHERE
				tbl_student.studiengang_kz = tbl_studiengang.studiengang_kz
				and tbl_studiengang.typ = tbl_studiengangstyp.typ
				and tbl_student.student_uid = tbl_benutzer.uid
				and tbl_benutzer.person_id = tbl_person.person_id
				and tbl_student.student_uid = ".$db->db_add_param($uid)."
				and tbl_studentlehrverband.student_uid=tbl_student.student_uid
				and tbl_studiensemester.studiensemester_kurzbz = tbl_studentlehrverband.studiensemester_kurzbz
				and tbl_studentlehrverband.studiensemester_kurzbz = ".$db->db_add_param($studiensemester_kurzbz);

	if($db->db_query($query))
	{
		if(!$row = $db->db_fetch_object())
			return false;
	}
	else
		return false;

	$studiensemester = new studiensemester();
	$studiensemester_aktuell = $studiensemester->getNearest();
	$student = new student();
	$student->load($uid);
	$prestudentstatus = new prestudent();
	$prestudentstatus->getLastStatus($student->prestudent_id,'','Student');

	if($studiensemester_aktuell!=$prestudentstatus->studiensemester_kurzbz)
		$studiensemester_aktuell = $prestudentstatus->studiensemester_kurzbz;

	$studiensemester->load($studiensemester_aktuell);

	$semester_aktuell='';
	$qry_semester = "SELECT tbl_prestudentstatus.ausbildungssemester as semester FROM public.tbl_student, public.tbl_prestudentstatus
					WHERE tbl_student.prestudent_id=tbl_prestudentstatus.prestudent_id
						AND tbl_prestudentstatus.status_kurzbz in('Student','Incoming','Outgoing','Praktikant','Diplomand')
						AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_aktuell)."
						AND tbl_student.student_uid = ".$db->db_add_param($uid);

	if($db->db_query($qry_semester))
		if($row_semester = $db->db_fetch_object())
			$semester_aktuell=$row_semester->semester;

	if($semester_aktuell=='')
		$studiensemester_aktuell='';

	$qry_semester = "SELECT tbl_prestudentstatus.ausbildungssemester as semester, tbl_prestudentstatus.orgform_kurzbz, tbl_prestudentstatus.studienplan_id FROM public.tbl_student, public.tbl_prestudentstatus
					WHERE tbl_student.prestudent_id=tbl_prestudentstatus.prestudent_id
						AND tbl_prestudentstatus.status_kurzbz in('Student','Incoming','Outgoing','Praktikant','Diplomand')
						AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
						AND tbl_student.student_uid = ".$db->db_add_param($uid);

	$orgform='';
	$studiengang_bezeichnung_sto='';
	$studiengang_bezeichnung_sto_englisch='';

	if($db->db_query($qry_semester))
	{
		if($row_semester = $db->db_fetch_object())
		{
			$row->semester=$row_semester->semester;
			$orgform = $row_semester->orgform_kurzbz;

			$stpl = new studienplan();
			$stpl->loadStudienplan($row_semester->studienplan_id);
			$sto = new studienordnung();
			$sto->loadStudienordnung($stpl->studienordnung_id);

			$studiengang_bezeichnung_sto = $sto->studiengangbezeichnung;
			$studiengang_bezeichnung_sto_englisch = $sto->studiengangbezeichnung_englisch;
		}
	}

	// Wenn der Studierende keine Orgform eingetragen hat, wird die Orgform des Studiengangs genommen
	if($orgform=='')
		$orgform = $row->orgform_kurzbz;

	$studiengang = new studiengang();
	$stgleiter = $studiengang->getLeitung($row->studiengang_kz);
	$stgl='';
	foreach ($stgleiter as $stgleiter_uid)
	{
		$stgl_ma = new mitarbeiter($stgleiter_uid);
		$stgl .= trim($stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
	}
	//Wenn Lehrgang, dann Erhalter-KZ vor die Studiengangs-Kz hängen
	if ($row->studiengang_kz<0)
	{
		$stg = new studiengang();
		$stg->load($row->studiengang_kz);

		$studiengang_kz = sprintf("%03s", $stg->erhalter_kz).sprintf("%04s", abs($row->studiengang_kz));
	}
	else
		$studiengang_kz = sprintf("%04s", abs($row->studiengang_kz));

	$stdsem = new studiensemester($studiensemester_kurzbz);

	$xml .= "	<studienerfolg>";
	$xml .= "		<logopath>".DOC_ROOT."skin/images/</logopath>";
	$xml .= "		<studiensemester>".$row->sembezeichnung."</studiensemester>";
	$xml .= "		<studiensemester_aktuell>".$studiensemester_aktuell."</studiensemester_aktuell>";
	$xml .= "		<studiensemester_aktuell_beschreibung>".(($studiensemester->beschreibung != NULL) ? $studiensemester->beschreibung : $studiensemester_aktuell)."</studiensemester_aktuell_beschreibung>";
	$xml .=	"		<semester>".$row->semester."</semester>";
	$xml .=	"		<semester_aktuell>".$semester_aktuell.($semester_aktuell!=''?'. Semester':'')."</semester_aktuell>";
	$xml .=	"		<semester_aktuell_semester>".$semester_aktuell."</semester_aktuell_semester>";
	$xml .= "		<studiengang><![CDATA[".$row->bezeichnung."]]></studiengang>";
	$xml .= "		<studiengang_englisch><![CDATA[".$row->bezeichnung_englisch."]]></studiengang_englisch>";
	$xml .= "		<studiengang_bezeichnung_sto><![CDATA[".$studiengang_bezeichnung_sto."]]></studiengang_bezeichnung_sto>";
	$xml .= "		<studiengang_bezeichnung_sto_englisch><![CDATA[".$studiengang_bezeichnung_sto_englisch."]]></studiengang_bezeichnung_sto_englisch>";
	$xml .= "		<studiengang_typ>".$row->studiengangstypbezeichnung."</studiengang_typ>";
	$xml .= "		<studiengang_typ_englisch>".$row->studiengangstypbezeichnung_englisch."</studiengang_typ_englisch>";
	$xml .= "		<studiengang_kz>".$studiengang_kz."</studiengang_kz>";
	$xml .= "		<titelpre><![CDATA[".$row->titelpre."]]></titelpre>";
	$xml .= "		<titelpost><![CDATA[".$row->titelpost."]]></titelpost>";
	$xml .= "		<vorname><![CDATA[".$row->vorname."]]></vorname>";
	$xml .= "		<nachname><![CDATA[".$row->nachname."]]></nachname>";
	$gebdatum = date('d.m.Y',strtotime($row->gebdatum));
	$xml .= "		<gebdatum>".$gebdatum."</gebdatum>";
	$xml .= "		<matrikelnr>".$row->matrikelnr."</matrikelnr>";
	$xml .= "		<matr_nr>".$row->matr_nr."</matr_nr>";
	$xml .= "		<studiensemester_kurzbz>".(($stdsem->beschreibung != NULL) ? $stdsem->beschreibung : $studiensemester_kurzbz)."</studiensemester_kurzbz>";
	$datum_aktuell = date('d.m.Y');
	$xml .= "		<datum>".$datum_aktuell."</datum>";
	$xml .= "		<orgform>".$orgform."</orgform>";
	$xml .= "		<studiengangsleitung>".$stgl."</studiengangsleitung>";

	if(isset($_REQUEST['typ']) && $_REQUEST['typ']=='finanzamt')
		$xml .= "		<finanzamt>(gemäß §2 Abs. 1 lit.b des Familienlastenausgleichsgesetzes 1967 zur Vorlage beim Wohnsitzfinanzamt)</finanzamt>";
	else
		$xml .= "		<finanzamt></finanzamt>";

	$obj = new zeugnisnote();

	if(!$obj->getZeugnisnoten($lehrveranstaltung_id=null, $uid, $studiensemester_kurzbz))
		die('Fehler beim Laden der Noten:'.$obj->errormsg);



	$gesamtstunden=0;
	$gesamtstunden_positiv=0;
	$gesamtstunden_lv=0;
	$gesamtstunden_lv_positiv=0;
	$gesamtects=0;
	$gesamtects_positiv=0;
	$notensumme=0;
	$notensumme_positiv=0;
	$anzahl=0;
	$anzahl_positiv=0;

	foreach ($obj->result as $row)
	{
		if($row->zeugnis)
		{
			if (trim($row->note)!=='' && isset($note_arr[$row->note]))
				$note = $note_arr[$row->note];
			else
				$note = "";
			if($note!='')
			{
				$qry = "SELECT
							wochen
						FROM
							public.tbl_semesterwochen
							JOIN lehre.tbl_lehrveranstaltung USING(studiengang_kz, semester)
						WHERE
							tbl_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($row->lehrveranstaltung_id);

				$wochen = 15;
				if($db->db_query($qry))
					if($row_wochen = $db->db_fetch_object())
						$wochen = $row_wochen->wochen;

				$xml .= "			<unterrichtsfach>";
				$xml .= "				<bezeichnung><![CDATA[".$row->lehrveranstaltung_bezeichnung."]]></bezeichnung>";
				$xml .= "				<bezeichnung_englisch><![CDATA[".$row->lehrveranstaltung_bezeichnung_english."]]></bezeichnung_englisch>";
				$xml .= "				<note>".$note."</note>";
				$xml .= "				<note_idx>".$row->note."</note_idx>";
				$xml .= "				<note_positiv>".$row->note_positiv."</note_positiv>";
				$sws = sprintf('%.1F',$row->semesterstunden/$wochen);
				$xml .= "				<sws>".number_format($sws,2)."</sws>";
				$sws_lv = sprintf('%.1F',$row->sws);
				$xml .= "				<sws_lv>".number_format($sws_lv,2)."</sws_lv>";
				$xml .= "				<ects>".number_format($row->ects,2)."</ects>";
				$xml .= "				<lehrform><![CDATA[".$row->lv_lehrform_kurzbz."]]></lehrform>";
				if($row->benotungsdatum!='')
					$xml .= "				<benotungsdatum>".date('d.m.Y',$datum->mktime_fromtimestamp($row->benotungsdatum))."</benotungsdatum>";
				$xml .= "			</unterrichtsfach>";

				$gesamtstunden +=$sws;
				$gesamtstunden_lv +=$sws_lv;
				$gesamtects += $row->ects;
				if ($row->note_positiv === true)
				{
					$gesamtstunden_positiv += $sws;
					$gesamtstunden_lv_positiv += $sws_lv;
					$gesamtects_positiv += $row->ects;
				}
				if($note_wert[$row->note]!='')
				{
					$notensumme += $note_wert[$row->note];
					$anzahl++;
					if ($row->note_positiv)
					{
						$notensumme_positiv += $note_wert[$row->note];
						$anzahl_positiv++;
					}
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

	if($anzahl_positiv!=0)
	{
		$schnitt_positiv = ($notensumme_positiv/$anzahl_positiv);
	}
	else
		$schnitt_positiv = 0;

	$xml .= "		<gesamtstunden>".$gesamtstunden."</gesamtstunden>";
	$xml .= "		<gesamtstunden_positiv>".$gesamtstunden_positiv."</gesamtstunden_positiv>";
	$xml .= "		<gesamtstunden_lv>".number_format($gesamtstunden_lv,2)."</gesamtstunden_lv>";
	$xml .= "		<gesamtstunden_lv_positiv>".number_format($gesamtstunden_lv_positiv,2)."</gesamtstunden_lv_positiv>";
	$xml .= "		<gesamtects>".number_format($gesamtects,2)."</gesamtects>";
	$xml .= "		<gesamtects_positiv>".number_format($gesamtects_positiv,2)."</gesamtects_positiv>";
	$xml .= "		<schnitt>".sprintf('%.2f',$schnitt)."</schnitt>";
	$xml .= "		<schnitt_positiv>".sprintf('%.2f',$schnitt_positiv)."</schnitt_positiv>";
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
	$note_wert = array();
	$note = new note();
	$note->getAll($offiziell = true);
	foreach ($note->result as $n)
	{
		$note_arr[$n->note] = $n->anmerkung;
		$note_wert[$n->note] = $n->notenwert;
	}

	if(isset($_GET['ss']))
		$studiensemester_kurzbz = $_GET['ss'];
	else
		die('Studiensemester nicht uebergeben');

	//Daten holen

	$xml = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n";
	$xml .= "<studienerfolge>";

	if(isset($_GET['all']))
	{
		for ($i = 0; $i < sizeof($uid_arr); $i++)
		{
			//Studienbestaetigung fuer alle Semester dieses Studenten
			$qry = "SELECT * FROM public.tbl_studiensemester
					WHERE studiensemester_kurzbz in(
						SELECT studiensemester_kurzbz
						FROM public.tbl_prestudentstatus JOIN public.tbl_student USING(prestudent_id)
						WHERE student_uid='".addslashes($uid_arr[$i])."')
					ORDER BY start";
			if($db->db_query($qry))
				while($row = $db->db_fetch_object())
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
