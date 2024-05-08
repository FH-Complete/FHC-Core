<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
 
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/zeugnisnote.class.php');
require_once('../include/datum.class.php');
require_once('../include/note.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/projektarbeit.class.php');

$db = new basis_db(); 
$fussnotenzeichen=array('¹)','²)','³)');
$anzahl_fussnoten=0;
$xml_fussnote = '';
$test = false; 
$bez = '';

if(isset($_REQUEST['xmlformat']) && $_REQUEST['xmlformat']=="xml")
{
	$uid_arr = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);
	
	$uid_arr = explode(";",$uid_arr);
	
	echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>"; 
	echo "<sammelzeugnisse>";
	foreach($uid_arr as $uid)
	{
		$xml_fussnote=0;
		$anzahl_fussnoten=0;
		if($uid=='')
			continue;
			 
		echo "<sammelzeugnis>"; 
	
		
		$qry = "SELECT tbl_student.matrikelnr, tbl_student.studiengang_kz, tbl_studiengang.typ, tbl_studiengang.projektarbeit_note_anzeige, 
						tbl_studiengang.bezeichnung, tbl_studiengang.english, tbl_studentlehrverband.semester, 
						tbl_person.vorname, tbl_person.vornamen, tbl_person.nachname,tbl_person.gebdatum,tbl_person.titelpre, 
						tbl_person.titelpost, tbl_person.anrede, tbl_studiensemester.bezeichnung as sembezeichnung, 
						tbl_studiensemester.studiensemester_kurzbz as stsem, tbl_student.prestudent_id 
					FROM tbl_person, tbl_student, tbl_studiengang, tbl_benutzer, tbl_studentlehrverband, tbl_studiensemester 
					WHERE tbl_student.studiengang_kz = tbl_studiengang.studiengang_kz 
					AND tbl_student.student_uid = tbl_benutzer.uid AND tbl_benutzer.person_id = tbl_person.person_id 
					AND tbl_student.student_uid = '".addslashes($uid)."' 
					AND tbl_studentlehrverband.student_uid=tbl_student.student_uid 
					AND tbl_studiensemester.studiensemester_kurzbz = tbl_studentlehrverband.studiensemester_kurzbz 
					order by semester;"; 
	
		if($result = $db->db_query($qry))
		{
			if($row_person = $db->db_fetch_object($result))
			{
				$datum_aktuell = date('d.m.Y');
				$gebdatum = date('d.m.Y',strtotime($row_person->gebdatum));
				$studiengang = new studiengang();
				$stgleiter = $studiengang->getLeitung($row_person->studiengang_kz);
				$stgl='';
				foreach ($stgleiter as $stgleiter_uid)
				{
					$stgl_ma = new mitarbeiter($stgleiter_uid);
					$stgl .= trim($stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
				}
							
				echo ' <anrede>'.$row_person->anrede.'</anrede>';
				echo ' <vorname>'.$row_person->vorname.'</vorname>';
				echo ' <nachname>'.$row_person->nachname.'</nachname>';
				echo ' <name>'.trim($row_person->titelpre.' '.trim($row_person->vorname.' '.$row_person->vornamen).' '.mb_strtoupper($row_person->nachname).($row_person->titelpost!=''?', '.$row_person->titelpost:'')).'</name>';
				echo ' <gebdatum>'.$gebdatum.'</gebdatum>';
				echo ' <matrikelnr>'.$row_person->matrikelnr.'</matrikelnr>';
				echo ' <studiengang>'.$row_person->bezeichnung.'</studiengang>';
				echo ' <studiengang_englisch>'.$row_person->english.'</studiengang_englisch>';
				echo " <studiengang_kz>".sprintf('%04s', $row_person->studiengang_kz)."</studiengang_kz>";
				echo ' <studiengangsleiter>'.$stgl.'</studiengangsleiter>'; 
				echo ' <ort_datum>'.$datum_aktuell.'</ort_datum>';
				echo ' <projektarbeit_note_anzeige>'.($row_person->projektarbeit_note_anzeige=='t'?'true':'false').'</projektarbeit_note_anzeige>';
				switch ($row_person->typ) 
				{
					case 'b':
						$bezeichnung = 'Bachelor';
						break;
					case 'm':
						$bezeichnung = 'Master';
						break;
					case 'd':
						$bezeichnung = 'Diplom';
						break;
					default:
						$bezeichnung = '';
						break;
				}
				$bez = $row_person->typ; 
				echo ' <studiengang_art>'.$bezeichnung.'</studiengang_art>';
				$prestudent = new prestudent();
	
				$prestudent->getFirstStatus($row_person->prestudent_id, 'Student');
				echo '  <start_semester>'.substr($prestudent->studiensemester_kurzbz,2,6).'</start_semester>';
				echo '  <start_semester_number>'.$prestudent->ausbildungssemester.'</start_semester_number>';
				$prestudent->getLastStatus($row_person->prestudent_id, null, 'Student');
				echo '  <end_semester>'.substr($prestudent->studiensemester_kurzbz,2,6).'</end_semester>';
				echo '  <end_semester_number>'.$prestudent->ausbildungssemester.'</end_semester_number>';
			}
		}
		
		$qry_projektarbeit = "SELECT lehrveranstaltung_id, titel, themenbereich, note, titel_english 
		FROM lehre.tbl_projektarbeit 
		JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
		WHERE student_uid='$uid' 
		AND projekttyp_kurzbz in('Bachelor', 'Diplom') 
		ORDER BY beginn ASC, projektarbeit_id ASC;";
		
		$projektarbeit = array(); 
		
		if($result_projektarbeit = $db->db_query($qry_projektarbeit))
		{
			while($row_projektarbeit = $db->db_fetch_object($result_projektarbeit))
			{
					$projektarbeit[$row_projektarbeit->lehrveranstaltung_id]['titel']=$row_projektarbeit->titel;
					$projektarbeit[$row_projektarbeit->lehrveranstaltung_id]['titel_en']=$row_projektarbeit->titel_english;
					$projektarbeit[$row_projektarbeit->lehrveranstaltung_id]['themenbereich']=$row_projektarbeit->themenbereich;
					$projektarbeit[$row_projektarbeit->lehrveranstaltung_id]['note']=$row_projektarbeit->note;
			}
		}
		
		$qry ="Select distinct(student.lehrveranstaltung_id), student.uid, student.studiengang_kz, student.kurzbz, student.bezeichnung, student.bezeichnung_english, student.semester, student.semesterstunden, student.ects, student.studiensemester_kurzbz, zeugnis.note, note.bezeichnung note_bezeichnung, note.anmerkung
		from campus.vw_student_lehrveranstaltung student
		left join lehre.tbl_zeugnisnote zeugnis on(student.lehrveranstaltung_id = zeugnis.lehrveranstaltung_id AND student.uid = zeugnis.student_uid AND student.studiensemester_kurzbz = zeugnis.studiensemester_kurzbz)
		join lehre.tbl_note note using(note)
		
		where uid = '$uid' and zeugnis = true order by semester;"; 
		
	
		$i = 0; 
		$wochen = 15; 
		if($result_stud = $db->db_query($qry))
		{
			while($row_stud = $db->db_fetch_object($result_stud))
			{	
				if($i <= 31)
					echo "  <unterrichtsfach_1>"; 
				else
					echo "  <unterrichtsfach_2>";
					
				$qry_sws = "SELECT wochen from public.tbl_semesterwochen where studiengang_kz = '".addslashes($row_stud->studiengang_kz)."' 
				and semester = '".addslashes($row_stud->semester)."';"; 	
				
				if($result_sws = $db->db_query($qry_sws))
				{
					if($row_sws = $db->db_fetch_object($result_sws))
					{
						$wochen = $row_sws->wochen; 
					}
				}		
				$ssp = $row_stud->semesterstunden / $wochen; 
				
				if(array_key_exists($row_stud->lehrveranstaltung_id, $projektarbeit))
				{
						//$bezeichnung = $row_stud->lehrveranstaltung_bezeichnung.$firma.' '.$fussnotenzeichen[$anzahl_fussnoten];
						//$bezeichnung_englisch = $row_stud->lehrveranstaltung_bezeichnung_english.$firma_eng.' '.$fussnotenzeichen[$anzahl_fussnoten];
						$xml_fussnote .="\n <fussnote>";
						$xml_fussnote .=" 		<fussnotenzeichen>".$fussnotenzeichen[$anzahl_fussnoten]."</fussnotenzeichen>";
						
						//$projektarbeit[$row->lehrveranstaltung_id]['titel'] = breaktext($projektarbeit[$row->lehrveranstaltung_id]['titel'], 40);
						
						$anzahl_nl = substr_count($projektarbeit[$row_stud->lehrveranstaltung_id]['titel'],'\n');
						$nl2='';
						if($projektarbeit[$row_stud->lehrveranstaltung_id]['themenbereich']!='')
						{
							$xml_fussnote .="       <themenbereich_bezeichnung>Themenbereich: </themenbereich_bezeichnung>";
							$xml_fussnote .="       <themenbereich><![CDATA[".$projektarbeit[$row_stud->lehrveranstaltung_id]['themenbereich'].'\n]]></themenbereich>';
							$anzahl_nl++;
							$nl2='\n';
						}
	 
						if($bez=='b')
							$typ = "Bachelor's Thesis:";
						else 
							$typ = 'Master Thesis:';
							
						$nl='';
						$nl2='';
						$xml_fussnote .="      <titel_bezeichnung>$typ</titel_bezeichnung>";
						$xml_fussnote .="      <titel><![CDATA[".$projektarbeit[$row_stud->lehrveranstaltung_id]['titel'].$nl2."]]></titel>";
						$xml_fussnote .="      <titel_en><![CDATA[".$projektarbeit[$row_stud->lehrveranstaltung_id]['titel_en'].$nl2."]]></titel_en>";
						//$note = $note_arr[$projektarbeit[$row->lehrveranstaltung_id]['note']];
						$note = $projektarbeit[$row_stud->lehrveranstaltung_id]['note'];
						//$nl = str_repeat('\n',($anzahl_nl));
						$xml_fussnote .='      <note>'.(isset($note_arr[$note])?$note_arr[$note]:$note).$nl.'</note>';
						$xml_fussnote .='      <sws>'.$nl.'</sws>';
						$xml_fussnote .='      <ects>'.$nl.'</ects>';
						$xml_fussnote .='      <lv_lehrform_kurzbz>'.$nl.'</lv_lehrform_kurzbz>';
	
						$xml_fussnote .=" </fussnote>";
						$anzahl_fussnoten++;
						$test = true; 
				}
	
				echo "   <sws>".number_format($ssp,2)."</sws>"; 
				echo "   <semester>$row_stud->semester</semester>"; 
				echo "   <kurzbz>$row_stud->kurzbz</kurzbz>";
				echo "   <stsem>$row_stud->studiensemester_kurzbz</stsem>"; 
				echo "   <bezeichnung><![CDATA[$row_stud->bezeichnung]]></bezeichnung>"; 
				
				if($test == true)
					echo "   <bezeichnung_englisch><![CDATA[$row_stud->bezeichnung_english]]>".$fussnotenzeichen[$anzahl_fussnoten-1]."</bezeichnung_englisch>"; 
				else 
					echo "   <bezeichnung_englisch><![CDATA[$row_stud->bezeichnung_english]]></bezeichnung_englisch>"; 
					
				echo "   <ects>$row_stud->ects</ects>"; 
				echo "   <semesterstunden>$row_stud->semesterstunden</semesterstunden>"; 
				echo "   <note>$row_stud->note</note>"; 
				echo "   <note_bezeichnung>$row_stud->note_bezeichnung</note_bezeichnung>"; 
				echo "   <note_anmerkung>$row_stud->anmerkung</note_anmerkung>"; 
	
				if($i <= 31)
					echo "  </unterrichtsfach_1>"; 
				else
					echo "  </unterrichtsfach_2>";
				
				$i++; 
				$test = false; 
			}
	
		}
		echo $xml_fussnote; 
		echo "</sammelzeugnis>";
	}
	echo "</sammelzeugnisse>"; 

}


?>