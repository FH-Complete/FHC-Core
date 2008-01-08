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

// header für no cache
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

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

//$user = get_uid();
//loadVariables($conn, $user);
$datum = new datum();
$projektarbeit=array();
$fussnotenzeichen=array('¹)','²)','³)');
$anzahl_fussnoten=0;
$studiengang_typ='';
$xml_fussnote='';

function breaktext($text, $zeichen)
{
	$arr = explode(' ',$text);
	$ret = '';
	$teilstring='';
	
	foreach($arr as $elem)
	{
		if(strlen($teilstring.$elem)>$zeichen)
		{
			$ret.=' '.$teilstring.'\n';
			$teilstring=$elem;
		}
		else 
			$teilstring .=' '.$elem;
	}
	$ret.=$teilstring;
	return $ret;
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
	foreach ($note->result as $n){
		$note_arr[$n->note] = $n->anmerkung;
		$note_bezeichnung_arr[$n->note] = $n->bezeichnung;	
	
	}
	if(isset($_GET['ss']))
		$studiensemester_kurzbz = $_GET['ss'];
	else 
		$studiensemester_kurzbz = $semester_aktuell;

	if(isset($_GET['lvid']))
		$lehrveranstaltung_id = $_GET['lvid'];
	else 
		$lehrveranstaltung_id = 0;
	
	//$rdf_url='http://www.technikum-wien.at/zeugnisnote';
	
	//Daten holen

	$lqry = "select tbl_person.* from public.tbl_benutzer join public.tbl_person using (person_id) where tbl_benutzer.uid = (select tbl_lehreinheitmitarbeiter.mitarbeiter_uid from lehre.tbl_lehreinheitmitarbeiter join lehre.tbl_lehrfunktion using(lehrfunktion_kurzbz), lehre.tbl_lehreinheit join lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id) where tbl_lehreinheitmitarbeiter.lehreinheit_id = tbl_lehreinheit.lehreinheit_id and tbl_lehrveranstaltung.lehrveranstaltung_id = '".$lehrveranstaltung_id."' order by tbl_lehrfunktion.standardfaktor desc limit 1)";
	if($lres = pg_query($conn, $lqry)){
		if ($lrow = pg_fetch_object($lres)){	
			$leiter_titel = $lrow->titelpre;			
			$leiter_vorname = $lrow->vorname;
			$leiter_nachname = $lrow->nachname;			
		}		
	}	
	
	$lvqry = "SELECT * from lehre.tbl_lehrveranstaltung where lehrveranstaltung_id = '".$lehrveranstaltung_id."'";
	if($lvres = pg_query($conn, $lvqry)){
		if ($lvrow = pg_fetch_object($lvres)){
			$sws = $lvrow->semesterstunden;
			$ects = $lvrow->ects;
			$lvbezeichnung = $lvrow->bezeichnung;			
		}		
	}
	
	$lehrinhalte = '';
	$infoqry = "SELECT * from campus.tbl_lvinfo where sprache='German' and lehrveranstaltung_id = '".$lehrveranstaltung_id."'";
	if($infores = pg_query($conn, $infoqry)){
		if ($inforow = pg_fetch_object($infores)){
			//$lehrinhalte = ereg_replace("<br>","<line lineafter='1' />",$inforow->lehrinhalte);	
			$lehrinhalte_arr = explode("<br>",$inforow->lehrinhalte);			
			for ($i = 0; $i < sizeof($lehrinhalte_arr); $i++)
			{
				$lehrinhalte .= $lehrinhalte_arr[$i].'\n';			
			}
		}		
	}	
	
	$xml = "<?xml version='1.0' encoding='ISO-8859-15' standalone='yes'?>";
	$xml .= "<zertifikate>";
	
	for ($i = 0; $i < sizeof($uid_arr); $i++)
	{	
		$anzahl_fussnoten=0;
		$studiengang_typ='';
		$xml_fussnote='';
		
		$query = "SELECT tbl_student.matrikelnr, tbl_student.studiengang_kz, tbl_studiengang.typ, tbl_studiengang.bezeichnung, tbl_studentlehrverband.semester, tbl_person.vorname, tbl_person.nachname,tbl_person.gebdatum,tbl_person.titelpre, tbl_person.titelpost, tbl_studiensemester.bezeichnung as sembezeichnung FROM tbl_person, tbl_student, tbl_studiengang, tbl_benutzer, tbl_studentlehrverband, tbl_studiensemester WHERE tbl_student.studiengang_kz = tbl_studiengang.studiengang_kz and tbl_student.student_uid = tbl_benutzer.uid and tbl_benutzer.person_id = tbl_person.person_id and tbl_student.student_uid = '".$uid_arr[$i]."' and tbl_studentlehrverband.student_uid=tbl_student.student_uid and tbl_studiensemester.studiensemester_kurzbz = tbl_studentlehrverband.studiensemester_kurzbz and tbl_studentlehrverband.studiensemester_kurzbz = '".$studiensemester_kurzbz."'";
		//echo $query;
		if($result = pg_query($conn, $query))
		{
				if(!$row = pg_fetch_object($result))
					die('Student not found');
		}
		else
			die('Student not found');
		
		$stgl_query = "SELECT titelpre, titelpost, vorname, nachname FROM tbl_person, tbl_benutzer, tbl_benutzerfunktion WHERE tbl_person.person_id = tbl_benutzer.person_id and tbl_benutzer.uid = tbl_benutzerfunktion.uid and tbl_benutzerfunktion.funktion_kurzbz = 'stgl' and tbl_benutzerfunktion.studiengang_kz = '".$row->studiengang_kz."'";
		if($stgl_result = pg_query($conn, $stgl_query))
				$stgl_row = pg_fetch_object($stgl_result);
		else
			die('Stgl not found');
			
		$sem_qry = "SELECT bezeichnung FROM public.tbl_lehrverband WHERE studiengang_kz='".$row->studiengang_kz."' AND semester = '".$row->semester."'";
		if($result_sem = pg_query($conn, $sem_qry))
		{
			if($row_sem = pg_fetch_object($result_sem))
			{
				$bezeichnung = $row_sem->bezeichnung;
			}
		}
		
		if($bezeichnung=='')
			$bezeichnung = $row->semester.'. Semester';
			
		$xml .= "\n	<zertifikat>";
		$xml .= "		<studiensemester>".$row->sembezeichnung."</studiensemester>";
		$xml .=	"		<semester>".$row->semester."</semester>";
		$xml .=	"		<semester_bezeichnung>".$bezeichnung."</semester_bezeichnung>";
		$xml .= "		<studiengang>".$row->bezeichnung."</studiengang>";
		if($row->typ=='b')
			$bezeichnung='Bachelor-Studiengang';
		elseif($row->typ=='m')
			$bezeichnung='Master-Studiengang';
		elseif($row->typ=='d')
			$bezeichnung='Diplom-Studiengang';
		else 
			$bezeichnung='Studiengang';
		$studiengang_typ=$row->typ;
		
		$xml .= "		<studiengang_art>".$bezeichnung."</studiengang_art>";
		$xml .= "		<studiengang_kz>".sprintf('%04s', $row->studiengang_kz)."</studiengang_kz>";
		$xml .= "\n		<vorname>".$row->vorname."</vorname>";
		$xml .= "		<nachname>".$row->nachname."</nachname>";
		$xml .= "		<name>".trim($row->titelpre.' '.$row->vorname.' '.strtoupper($row->nachname).' '.$row->titelpost)."</name>";
		$gebdatum = date('d.m.Y',strtotime($row->gebdatum));
		$xml .= "		<gebdatum>".$gebdatum."</gebdatum>";
		$xml .= "		<matrikelnr>".$row->matrikelnr."</matrikelnr>";
		$xml .= "		<studiengangsleiter>".$stgl_row->titelpre." ".$stgl_row->vorname." ".$stgl_row->nachname."</studiengangsleiter>";
		$datum_aktuell = date('d.m.Y');
		$xml .= "		<ort_datum>Wien, am ".$datum_aktuell."</ort_datum>";
		
		
		$obj = new zeugnisnote($conn, null, null, null, false);
		$obj->load($lehrveranstaltung_id, $uid_arr[$i], $studiensemester_kurzbz);

		if ($obj->note)
		{
			$note = $note_arr[$obj->note];
			$note_bezeichnung = $note_bezeichnung_arr[$obj->note];
		}		
		else
		{
			$note = "";
			$note_bezeichnung = "";
		}		
		$note2=$note;

		$xml .= "				<bezeichnung><![CDATA[".$lvbezeichnung."]]></bezeichnung>";
		$xml .= "				<note>".$note."</note>";
		$xml .= "				<note_bezeichnung>".$note_bezeichnung."</note_bezeichnung>";
		$xml .= "				<sws>".$sws."</sws>";
		$xml .= "				<ects>".number_format($ects,1)."</ects>";
		$xml .= "				<lvleiter>".$leiter_titel." ".$leiter_vorname." ".$leiter_nachname."</lvleiter>";
		$xml .= "				<lehrinhalte><![CDATA[".$lehrinhalte."]]></lehrinhalte>";

		
		$xml .= "	</zertifikat>";
	}
	$xml .= "</zertifikate>";
	echo $xml;
}
?>