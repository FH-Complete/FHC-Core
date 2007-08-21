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
	foreach ($note->result as $n)
		$note_arr[$n->note] = $n->anmerkung;
	
	if(isset($_GET['ss']))
		$studiensemester_kurzbz = $_GET['ss'];
	else 
		$studiensemester_kurzbz = $semester_aktuell;
	
	//$rdf_url='http://www.technikum-wien.at/zeugnisnote';
	
	//Daten holen
	
	$xml = "<?xml version='1.0' encoding='ISO-8859-15' standalone='yes'?>";
	$xml .= "<zeugnisse>";
	
	for ($i = 0; $i < sizeof($uid_arr); $i++)
	{	
		$anzahl_fussnoten=0;
		$studiengang_typ='';
		$xml_fussnote='';
		
		$query = "SELECT tbl_student.matrikelnr, tbl_student.studiengang_kz, tbl_studiengang.typ, tbl_studiengang.bezeichnung, tbl_studentlehrverband.semester, tbl_person.vorname, tbl_person.nachname,tbl_person.gebdatum,tbl_person.titelpre, tbl_person.titelpost, tbl_studiensemester.bezeichnung as sembezeichnung FROM tbl_person, tbl_student, tbl_studiengang, tbl_benutzer, tbl_studentlehrverband, tbl_studiensemester WHERE tbl_student.studiengang_kz = tbl_studiengang.studiengang_kz and tbl_student.student_uid = tbl_benutzer.uid and tbl_benutzer.person_id = tbl_person.person_id and tbl_student.student_uid = '".$uid_arr[$i]."' and tbl_studentlehrverband.student_uid=tbl_student.student_uid and tbl_studiensemester.studiensemester_kurzbz = tbl_studentlehrverband.studiensemester_kurzbz and tbl_studentlehrverband.studiensemester_kurzbz = '".$studiensemester_kurzbz."'";
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
		$xml .= "\n	<zeugnis>";
		$xml .= "		<studiensemester>".$row->sembezeichnung."</studiensemester>";
		$xml .=	"		<semester>".$row->semester."</semester>";
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
		
		$qry_proj = "SELECT lehrveranstaltung_id, titel, themenbereich, note FROM lehre.tbl_projektarbeit JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) WHERE student_uid='".$uid_arr[$i]."' AND studiensemester_kurzbz='$studiensemester_kurzbz'";
		if($result_proj = pg_query($conn, $qry_proj))
		{
			while($row_proj = pg_fetch_object($result_proj))
			{
				$projektarbeit[$row_proj->lehrveranstaltung_id]['titel']=$row_proj->titel;
				$projektarbeit[$row_proj->lehrveranstaltung_id]['themenbereich']=$row_proj->themenbereich;
				$projektarbeit[$row_proj->lehrveranstaltung_id]['note']=$row_proj->note;
			}
		}
		
		$qry = "SELECT wochen FROM public.tbl_semesterwochen WHERE studiengang_kz='$row->studiengang_kz' AND semester='$row->semester'";
		$wochen = 15;
		if($result_wochen = pg_query($conn, $qry))
		{
			if($row_wochen = pg_fetch_object($result_wochen))
			{
				$wochen = $row_wochen->wochen;
			}
		}
		$obj = new zeugnisnote($conn, null, null, null, false);
		
		$obj->getZeugnisnoten($lehrveranstaltung_id=null, $uid_arr[$i], $studiensemester_kurzbz);

		foreach ($obj->result as $row)	
		{
			if($row->zeugnis)
			{
				if ($row->note)
					$note = $note_arr[$row->note];
				else
					$note = "";
					
				if(array_key_exists($row->lehrveranstaltung_id, $projektarbeit))
				{
					$bezeichnung = $row->lehrveranstaltung_bezeichnung.' '.$fussnotenzeichen[$anzahl_fussnoten];
					$xml_fussnote .="\n <fussnote>";
					$xml_fussnote .=" 		<fussnotenzeichen>".$fussnotenzeichen[$anzahl_fussnoten]."</fussnotenzeichen>";
					
					$projektarbeit[$row->lehrveranstaltung_id]['titel'] = breaktext($projektarbeit[$row->lehrveranstaltung_id]['titel'], 40);
					
					$anzahl_nl = substr_count($projektarbeit[$row->lehrveranstaltung_id]['titel'],'\n');
					$nl2='';
					if($projektarbeit[$row->lehrveranstaltung_id]['themenbereich']!='')
					{
						$xml_fussnote .="       <themenbereich_bezeichnung>Themenbereich: </themenbereich_bezeichnung>";
						$xml_fussnote .="       <themenbereich>".$projektarbeit[$row->lehrveranstaltung_id]['themenbereich'].'\n</themenbereich>';
						$anzahl_nl++;
						$nl2='\n';
					}
					
					if($studiengang_typ=='b')
						$typ = 'Bachelorarbeit:';
					else 
						$typ = 'Diplomarbeit:';
						
					$xml_fussnote .="      <titel_bezeichnung>$typ</titel_bezeichnung>";
					$xml_fussnote .="      <titel>".$projektarbeit[$row->lehrveranstaltung_id]['titel'].$nl2."</titel>";
					$note = $note_arr[$projektarbeit[$row->lehrveranstaltung_id]['note']];
					$nl = str_repeat('\n',($anzahl_nl));
					$xml_fussnote .='      <note>'.$note.$nl.'</note>';
					$xml_fussnote .='      <sws>'.$nl.'</sws>';
					$xml_fussnote .='      <ects>'.$nl.'</ects>';
				
						
					
					$xml_fussnote .=" </fussnote>";
					
					$anzahl_fussnoten++;
				}
				else 
					$bezeichnung = $row->lehrveranstaltung_bezeichnung;
				
				$xml .= "\n			<unterrichtsfach>";
				$xml .= "				<bezeichnung>".$bezeichnung."</bezeichnung>";
				$xml .= "				<note>".$note."</note>";
				$xml .= "				<sws>".sprintf('%.1f',$row->semesterstunden/$wochen)."</sws>";
				$xml .= "				<ects>".$row->ects."</ects>";
				$xml .= "			</unterrichtsfach>";
			}
		}
		$xml .= $xml_fussnote;
		$xml .= "	</zeugnis>";
	}
	$xml .= "</zeugnisse>";
	echo $xml;
}
?>