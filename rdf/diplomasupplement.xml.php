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
// content type setzen
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/datum.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/student.class.php');

$datum = new datum();
$db = new basis_db();

if (isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{

	if(isset($_GET['uid']))
		$uid = $_GET['uid'];
	else 
		$uid = null;
	
	$uid_arr = explode(";",$uid);

	echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?> ";
	echo "<supplements>";

	for ($i = 0; $i < sizeof($uid_arr); $i++)
	{	
		if($uid_arr[$i]=='')
			continue;
		$query = "SELECT 
						*
		          FROM 
						campus.vw_student JOIN public.tbl_studiengang USING(studiengang_kz)
		          WHERE 
						uid = '".$uid_arr[$i]."'";

		if($db->db_query($query))
		{
				if(!$row = $db->db_fetch_object())
					die('Student not found'.$uid_arr[$i]);
		}
		else
			die('Student not found'.$uid_arr[$i]);

		//Bei DEW und DPW werden 60 ECTS angerechnet
		if($row->studiengang_kz==92 || $row->studiengang_kz==91)
			$angerechnete_sws=60;
		else 
			$angerechnete_sws=0;
			
		$studiengang_kz = sprintf("%04s",   $row->studiengang_kz); 
		echo '	<supplement>';
		echo '		<nachname><![CDATA['.$row->nachname.']]></nachname>';
		echo '		<vorname>'.$row->vorname.'</vorname>';
		echo '		<vornamen>'.$row->vornamen.'</vornamen>';
        echo '      <name><![CDATA['.$row->vorname.' '.$row->nachname.']]></name>';
		echo '		<geburtsdatum>'.$datum->convertISODate($row->gebdatum).'</geburtsdatum>';
		echo '		<matrikelnummer>'.$row->matrikelnr.'</matrikelnummer>';
		echo '		<studiengang_kz>'.$studiengang_kz.'</studiengang_kz>';
		echo '		<studiengang_bezeichnung_deutsch>'.$row->bezeichnung.'</studiengang_bezeichnung_deutsch>';
		echo '		<studiengang_bezeichnung_englisch>'.$row->english.'</studiengang_bezeichnung_englisch>';
		
        $prestudent = new prestudent(); 
        $prestudent->getFirstStatus($row->prestudent_id, 'Student');
        $semesterNumberStart = $prestudent->ausbildungssemester; 
        echo '      <start_semester>'.substr($prestudent->studiensemester_kurzbz,2,6).'</start_semester>';
        echo '      <start_semester_number>'.$prestudent->ausbildungssemester.'</start_semester_number>';
        $prestudent->getLastStatus($row->prestudent_id, null, 'Student');
        $semesterNumberEnd = $prestudent->ausbildungssemester; 
        echo '      <end_semester>'.substr($prestudent->studiensemester_kurzbz,2,6).'</end_semester>';
        echo '      <end_semester_number>'.$prestudent->ausbildungssemester.'</end_semester_number>';
        
        $studiengang = new studiengang(); 
        $studiengang->load($studiengang_kz);
        switch ($studiengang->typ) 
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
        echo '		<studiengang_typ>'.$bezeichnung.'</studiengang_typ>';
        
        $bez = $row->typ; 
		//Unterrichtssprache
		$sprache_deutsch='';
		$sprache_englisch='';
		if($row->mischform=='t')
		{
			//Bei Mischformen, die LVs auf Orgform filtern
			$prestudent = new prestudent();
			$prestudent->getLastStatus($row->prestudent_id);
			if($prestudent->orgform_kurzbz!='')
				$orgform_kurzbz=$prestudent->orgform_kurzbz;
			else
				$orgform_kurzbz=$row->orgform_kurzbz;
			$qry_sprache = "
			SELECT 
				sprache 
			FROM 
				lehre.tbl_lehrveranstaltung 
			WHERE 
				studiengang_kz=".$db->db_add_param($row->studiengang_kz)." 
				AND aktiv
				AND orgform_kurzbz=".$db->db_add_param($orgform_kurzbz)." 
			GROUP BY sprache 
			ORDER BY sprache DESC";
		}
		else
		{
			$qry_sprache = "
			SELECT 
				sprache 
			FROM 
				lehre.tbl_lehrveranstaltung 
			WHERE 
				studiengang_kz=".$db->db_add_param($row->studiengang_kz)." 
				AND aktiv 
			GROUP BY sprache 
			ORDER BY sprache DESC";
		}
		
		if($result_sprache = $db->db_query($qry_sprache))
		{
			while($row_sprache = $db->db_fetch_object($result_sprache))
			{
				if($sprache_englisch!='')
					$sprache_englisch.=', ';
				if($sprache_deutsch!='')
					$sprache_deutsch.=', ';
				
				$sprache_englisch .= $row_sprache->sprache;
				
				switch ($row_sprache->sprache)
				{
					case 'German': $sprache_deutsch .= 'Deutsch'; break;
					case 'English': $sprache_deutsch .= 'Englisch'; break;
				}
			}
		}
		echo '		<sprache_deutsch>'.$sprache_deutsch.'</sprache_deutsch>';
		echo '		<sprache_englisch>'.$sprache_englisch.'</sprache_englisch>';
		
		echo '		<semester>'.$row->max_semester.'</semester>';
		echo '		<jahre>'.($row->max_semester/2.0).'</jahre>';
		echo '		<ects>'.($row->max_semester*30+$angerechnete_sws).'</ects>';
		if($angerechnete_sws!=0)
			echo '		<ects_angerechnet>('.$angerechnete_sws.' ECTS angerechnet/credited)</ects_angerechnet>';
		else
			echo '		<ects_angerechnet></ects_angerechnet>';
			
		if($row->mischform=='t' || $row->orgform_kurzbz=='VBB')
		{
			//Bei Mischformen, die OrgForm aus dem Status nehmen
			$prestudent = new prestudent();
			$prestudent->getLastStatus($row->prestudent_id);
			if($prestudent->orgform_kurzbz!='')
				$row->orgform_kurzbz=$prestudent->orgform_kurzbz;
		}
		
		switch($row->orgform_kurzbz)
		{
			case 'BB':	echo '		<studienart>Berufbegleitendes Studium / Part-time degree programm</studienart>';
						break;
			case 'VZ':	echo '		<studienart>Vollzeitstudium / Full-time degree programm</studienart>';
						break;
			case 'DL':	echo '		<studienart>Fernstudium / Distance Learning</studienart>';
						break;
			default:	echo '		<studienart></studienart>';
						break;
		}
		
		if($row->typ=='d')
		{
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Allgemeine Universitätsreife (vgl. §4 Abs. 3 FHStG idgF), Berufsreifeprüfung bzw. Studienberechtigungsprüfung oder einschlägige berufliche Qualifikation (Lehrabschluss bzw. Abschluss einer berufsbildenden mittleren Schule mit Zusatzprüfungen). Die Aufnahme erfolgt auf Basis eines Auswahlverfahrens (Werdegang, Eignungstest, Bewerbungsgespräch).]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[Austrian or equivalent foreign school leaving certificate (Reifeprüfung), university entrance examination certificate (Studienberechtigungsprüfung), certificate or equivalent relevant professional qualification (Berufsreifeprüfung) plus entrance examination equal to the university entrance examination. Admission is on the basis of a selection process (including entrance exam and interview, professional background is considered).]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von Lehrveranstaltungen (Vorlesungen, Übungen, Seminaren, Projekten, integrierten Lehrveranstaltungen) im Ausmaß von jeweils 30 ECTS pro Semester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung integriert technische, wirtschaftliche, organisatorische und persönlichkeitsbildende Elemente. Das Studium beinhaltet ein facheinschlägiges Berufpraktikum. Im Rahmen des Studiums ist eine Diplomarbeit zu verfassen und eine abschließende Prüfung (Diplomprüfung) zu absolvieren. Curriculum des Studienganges gemäß dem vom FHR mit Kennzahl '.sprintf('%04s', $row->studiengang_kz).' genehmigten Antrag.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The program requires the positive completion of all courses (lectures, labs, seminars, project work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum. The program integrates technical, economical, management and personal study elements. Included in the program is a relevant work placement. The degree is awarded upon the successful completion of a diploma theses and the final examination. Curriculum of the program according to the application as approved by the Fachhochschul Council (Classification number: '.sprintf('%04s', $row->studiengang_kz).')]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Diplomstudiengangs berechtigt zu einem facheinschlägigen Doktoratsstudium, Magister- bzw. Master-Studium oder postgradualen Studium (mit eventuellen Zusatzprüfungen). Die Qualifikation entspricht einem Master of Science in Engineering, MSc.]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Diploma Degree Program qualifies the graduate to apply for admission to a relevant Doctoral Degree Program, Master Degree Program or postgraduate studies (additional qualifying exams may be required). The Diploma Degree Program is a graduate program, the qualification is equivalent to Master of Science in Engineering, MSc.]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Diplomstudium (UNESCO ISCED 5A)</niveau_deutsch>';
			echo '		<niveau_englisch>Diploma degree program (UNESCO ISCED 5A)</niveau_englisch>';
		}
		elseif($row->typ=='m')
		{
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Die fachliche Zugangsvoraussetzung (vgl. §4 Abs. 2 FHStG idgF) zu einem FH-Masterstudiengang ist ein abgeschlossener facheinschlägiger FH-Bachelorstudiengang oder der Abschluss eines gleichwertigen Studiums an einer anerkannten inländischen oder ausländischen postsekundären Bildungseinrichtung. Die Aufnahme in den Studiengang erfolgt auf Basis eines Auswahlverfahrens.]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[ Admission to the master\'s degree program is granted on the basis of the successful completion of a relevant bachelor\'s degree program or a  comparable Austrian or foreign post-secondary degree acknowledged to be its equivalent. Admission is on the basis of a selection process. ]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von Lehrveranstaltungen (Vorlesungen, Übungen, Seminaren, Projekten, integrierten Lehrveranstaltungen) im Ausmaß von jeweils 30 ECTS pro Semester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung integriert technische, wirtschaftliche, organisatorische und persönlichkeitsbildende Elemente. Im Rahmen des Studiums ist eine Master Thesis zu verfassen und eine abschließende Prüfung (Masterprüfung) zu absolvieren. Curriculum des Studienganges gemäß dem vom FHR mit Kennzahl '.sprintf('%04s', $row->studiengang_kz).' genehmigten Antrag. ]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The program requires the positive completion of all courses (lectures, labs, seminars, project work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum.  The program integrates technical, economical, management and personal study elements. The degree is awarded upon the successful completion of a Master´s Thesis and the final examination. Curriculum of the program according to the application as approved by the Fachhochschul Council (Classification number: '.sprintf('%04s', $row->studiengang_kz).') ]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Masterstudiengangs berechtigt zu einem facheinschlägigen Doktoratsstudium an einer Universität (mit eventuellen Zusatzprüfungen).]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Master Degree Program qualifies the graduate to apply for admission to a relevant Doctoral Degree Program at a University (additional qualifying exams may be required).    ]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Masterstudium (UNESCO ISCED 5A)</niveau_deutsch>';
			echo '		<niveau_englisch>Master degree program (UNESCO ISCED 5A)</niveau_englisch>';
		}
		elseif($row->typ=='b')
		{
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Allgemeine Universitätsreife (vgl. §4 Abs. 3 FHStG idgF), Berufsreifeprüfung bzw. Studienberechtigungsprüfung oder einschlägige berufliche Qualifikation (Lehrabschluss bzw. Abschluss einer berufsbildenden mittleren Schule mit Zusatzprüfungen). Die Aufnahme erfolgt auf Basis eines Auswahlverfahrens (Werdegang, Eignungstest, Bewerbungsgespräch).]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[Austrian or equivalent foreign school leaving certificate (Reifeprüfung), university entrance examination certificate (Studienberechtigungsprüfung), certificate or equivalent relevant professional qualification (Berufsreifeprüfung) plus entrance examination equal to the university entrance examination. Admission is  on the basis of a selection process. (including entrance exam and interview, professional background is considered).]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von Lehrveranstaltungen (Vorlesungen, Übungen, Seminaren, Projekten, integrierten Lehrveranstaltungen) im Ausmaß von jeweils 30 ECTS pro Semester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung integriert technische, wirtschaftliche, organisatorische und persönlichkeitsbildende Elemente. Das Studium beinhaltet ein facheinschlägiges Berufpraktikum. Im Rahmen des Studiums sind zwei Bachelorarbeiten zu verfassen und eine abschließende Prüfung (Bachelorprüfung) zu absolvieren. Curriculum des Studienganges gemäß dem vom FHR mit Kennzahl '.sprintf('%04s', $row->studiengang_kz).' genehmigten Antrag.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The program requires the positive completion of all courses (lectures, labs, seminars, project work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum. The program integrates technical, economical, management and personal study elements. Included in the program is a relevant work placement. The degree is awarded upon the successful completion of 2 bachelor theses and the final examination. Curriculum of the program according to the application as approved by the Fachhochschul Council (Classification number: '.sprintf('%04s', $row->studiengang_kz).')]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Bachelorstudiengangs berechtigt zu einem facheinschlägigen Magister- bzw. Master-Studium an einer fachhochschulischen Einrichtung oder Universität (mit eventuellen Zusatzprüfungen).]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Bachelor Degree Program qualifies the graduate to apply for admission to a relevant Master Degree Program at a University of Applied Sciences or a University (additional qualifying exams may be required).]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Bachelorstudium (UNESCO ISCED 5A)</niveau_deutsch>';
			echo '		<niveau_englisch>Bachelor degree program (UNESCO ISCED 5A)</niveau_englisch>';
			
		}
		
		$akadgrad_id='';
		$qry = "SELECT bezeichnung, akadgrad_id, bezeichnung_english FROM lehre.tbl_abschlusspruefung JOIN lehre.tbl_abschlussbeurteilung USING(abschlussbeurteilung_kurzbz) WHERE student_uid='".$uid_arr[$i]."' ORDER BY datum DESC LIMIT 1";
		if($db->db_query($qry))
		{
			if($row1 = $db->db_fetch_object())
			{
				echo "		<beurteilung>$row1->bezeichnung</beurteilung>";
				echo "		<beurteilung_english>$row1->bezeichnung_english</beurteilung_english>";
				$akadgrad_id = $row1->akadgrad_id;
			}
		}
				
		$qry = "SELECT * FROM lehre.tbl_akadgrad WHERE akadgrad_id='$akadgrad_id'";
		$titel_de = '';
        $titel_en = '';
		$titel_kurzbz = '';
		if($akadgrad_id!='')
		{
			if($db->db_query($qry))
			{
				if($row_titel = $db->db_fetch_object())
				{
					$titel_de = $row_titel->titel.', abgekürzt '.$row_titel->akadgrad_kurzbz;
					$titel_en = $row_titel->titel.', abbreviated '.$row_titel->akadgrad_kurzbz;
				}
			}
		}
		echo '		<titel_de>'.$titel_de.'</titel_de>';
		echo '		<titel_en>'.$titel_en.'</titel_en>';
	
		$qry = "SELECT projektarbeit_id FROM lehre.tbl_projektarbeit WHERE student_uid='".$uid_arr[$i]."' AND (projekttyp_kurzbz='Praxis' OR projekttyp_kurzbz='Praktikum')";
		if($db->db_query($qry))
		{
			if($row1 = $db->db_fetch_object())
			{
				echo "		<praktikum>Berufspraktikum/Internship: absolviert/completed</praktikum>";
			}
		}
		
		$qry = "SELECT von, bis FROM bis.tbl_bisio WHERE student_uid='".$uid_arr[$i]."'";
		if($db->db_query($qry))
		{
			if($db->db_num_rows()>0)
			{
				echo "		<auslandssemester>";
				while($row1 = $db->db_fetch_object())
				{
					echo "Auslandssemester/International semester ".$datum->convertISODate($row1->von)." - ".$datum->convertISODate($row1->bis);
				}
				echo "</auslandssemester>";
			}
		}
		$stg_oe_obj = new studiengang($row->studiengang_kz);
		$stgleiter = $stg_oe_obj->getLeitung($row->studiengang_kz);
		$stgl='';
		foreach ($stgleiter as $stgleiter_uid)
		{
			$stgl_ma = new mitarbeiter($stgleiter_uid);
			$stgl .= trim($stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
		}
		
		echo "		<stgl>$stgl</stgl>";
		
		$qry = "SELECT telefonklappe FROM public.tbl_mitarbeiter JOIN tbl_benutzerfunktion ON(uid=mitarbeiter_uid) WHERE funktion_kurzbz='ass' AND oe_kurzbz='$stg_oe_obj->oe_kurzbz'";
		if($db->db_query($qry))
		{
			if($row1 = $db->db_fetch_object())
			{
				echo "		<telefonklappe>$row1->telefonklappe</telefonklappe>";
			}
		}
		echo '		<tagesdatum>'.date('d.m.Y').'</tagesdatum>';
		
		
		/* 
		 * Hole Notendurchschnitt vom Jahr nach dem letzten Status und 2 Jahre davor,
		*/
		$student = new student(); 
		$student->load($uid_arr[$i]);
		$prestudent = new prestudent(); 
		$prestudent->getLastStatus($student->prestudent_id, null, 'Student');
		
		$lastStatusSemester = $prestudent->studiensemester_kurzbz; 
		$studiensemester = new studiensemester(); 
		$studiensemesterPrev = $studiensemester->getPreviousFrom($lastStatusSemester); 
		$noteArrayPrev = array(); 
		$noteArrayPrev[1] = 0;
		$noteArrayPrev[2] = 0;
		$noteArrayPrev[3] = 0;
		$noteArrayPrev[4] = 0;
		$noteArrayPrev[5] = 0;
		$noteArrayPrev[6] = 0;
		$noteArrayPrev[7] = 0;
		$noteArrayPrev[12] = 0;
		
		// letztes Jahr
		$qry_prevYear = "SELECT note, count(note) FROM lehre.tbl_zeugnisnote 
		WHERE lehrveranstaltung_id 
			IN(select distinct(lehrveranstaltung_id) FROM lehre.tbl_lehreinheit 
			JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
		 	WHERE studiensemester_kurzbz IN ('$lastStatusSemester','$studiensemesterPrev') and studiengang_kz = '$studiengang_kz') 
		 AND studiensemester_kurzbz IN ('$lastStatusSemester','$studiensemesterPrev') group by note order by note";
		if($result_prevYear = $db->db_query($qry_prevYear))
			{
				while($row_prevYear = $db->db_fetch_object($result_prevYear))
				{
					$noteArrayPrev[$row_prevYear->note] = $row_prevYear->count;
				}
			}
		
		$noten_anzahl =0; 
		$noten_anzahl += $noteArrayPrev[1];
		$noten_anzahl += $noteArrayPrev[2];
		$noten_anzahl += $noteArrayPrev[3];
		$noten_anzahl += $noteArrayPrev[4];
		$noten_anzahl += $noteArrayPrev[5];
		$noten_anzahl += $noteArrayPrev[6];
		$noten_anzahl += $noteArrayPrev[7];
		$noten_anzahl += $noteArrayPrev[12];

		// Noten: 1-5, angerechnet, nicht beurteilt, erfolgreich absolviert anzeigen
		echo "  <gradeLastYear1>".sprintf("%01.1f",($noteArrayPrev[1]/$noten_anzahl*100))."</gradeLastYear1>";
		echo "  <gradeLastYear2>".sprintf("%01.1f",($noteArrayPrev[2]/$noten_anzahl*100))."</gradeLastYear2>";
		echo "  <gradeLastYear3>".sprintf("%01.1f",($noteArrayPrev[3]/$noten_anzahl*100))."</gradeLastYear3>";
		echo "  <gradeLastYear4>".sprintf("%01.1f",($noteArrayPrev[4]/$noten_anzahl*100))."</gradeLastYear4>";
		echo "  <gradeLastYear5>".sprintf("%01.1f",($noteArrayPrev[5]/$noten_anzahl*100))."</gradeLastYear5>";
		echo "  <gradeLastYearAr>".sprintf("%01.1f",($noteArrayPrev[6]/$noten_anzahl*100))."</gradeLastYearAr>";
		echo "  <gradeLastYearNb>".sprintf("%01.1f",($noteArrayPrev[7]/$noten_anzahl*100))."</gradeLastYearNb>";
		echo "  <gradeLastYearEa>".sprintf("%01.1f",($noteArrayPrev[12]/$noten_anzahl*100))."</gradeLastYearEa>";
		
		// vorletztes Jahr
		$studiensemester = new studiensemester(); 
		$lastStatusSemester =  $studiensemester->getPreviousFrom($studiensemesterPrev); 
		$studiensemesterPrev = $studiensemester->getPreviousFrom($lastStatusSemester); 
		$qry_prevYear = "SELECT note, count(note) FROM lehre.tbl_zeugnisnote 
		WHERE lehrveranstaltung_id 
			IN(select distinct(lehrveranstaltung_id) FROM lehre.tbl_lehreinheit 
			JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
		 	WHERE studiensemester_kurzbz IN ('$lastStatusSemester','$studiensemesterPrev') and studiengang_kz = '$studiengang_kz') 
		 AND studiensemester_kurzbz IN ('$lastStatusSemester','$studiensemesterPrev') group by note order by note";
		if($result_prevYear = $db->db_query($qry_prevYear))
			{
				while($row_prevYear = $db->db_fetch_object($result_prevYear))
				{
					$noteArrayPrev[$row_prevYear->note] = $row_prevYear->count;
				}
			}
		
		$noten_anzahl =0; 
		$noten_anzahl += $noteArrayPrev[1];
		$noten_anzahl += $noteArrayPrev[2];
		$noten_anzahl += $noteArrayPrev[3];
		$noten_anzahl += $noteArrayPrev[4];
		$noten_anzahl += $noteArrayPrev[5];
		$noten_anzahl += $noteArrayPrev[6];
		$noten_anzahl += $noteArrayPrev[7];
		$noten_anzahl += $noteArrayPrev[12];

		// Noten: 1-5, angerechnet, nicht beurteilt, erfolgreich absolviert anzeigen
		echo "  <gradePrevLastYear1>".sprintf("%01.1f",($noteArrayPrev[1]/$noten_anzahl*100))."</gradePrevLastYear1>";
		echo "  <gradePrevLastYear2>".sprintf("%01.1f",($noteArrayPrev[2]/$noten_anzahl*100))."</gradePrevLastYear2>";
		echo "  <gradePrevLastYear3>".sprintf("%01.1f",($noteArrayPrev[3]/$noten_anzahl*100))."</gradePrevLastYear3>";
		echo "  <gradePrevLastYear4>".sprintf("%01.1f",($noteArrayPrev[4]/$noten_anzahl*100))."</gradePrevLastYear4>";
		echo "  <gradePrevLastYear5>".sprintf("%01.1f",($noteArrayPrev[5]/$noten_anzahl*100))."</gradePrevLastYear5>";
		echo "  <gradePrevLastYearAr>".sprintf("%01.1f",($noteArrayPrev[6]/$noten_anzahl*100))."</gradePrevLastYearAr>";
		echo "  <gradePrevLastYearNb>".sprintf("%01.1f",($noteArrayPrev[7]/$noten_anzahl*100))."</gradePrevLastYearNb>";
		echo "  <gradePrevLastYearEa>".sprintf("%01.1f",($noteArrayPrev[12]/$noten_anzahl*100))."</gradePrevLastYearEa>";
		
        $qry_projektarbeit = "SELECT lehrveranstaltung_id, titel, themenbereich, note, titel_english 
		FROM lehre.tbl_projektarbeit 
		JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
		WHERE student_uid='$uid_arr[$i]' 
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
        
        
        $ects_total = 0; 
/*        $anzahl_fussnoten=0;
        $fussnotenzeichen=array('¹)','²)','³)');
        $xml_fussnote = '';*/
        echo "<studiensemester>";
		for($start = $semesterNumberStart; $start <= $semesterNumberEnd; $start++)
		{
			//$thesis_beschreibung = '';
			echo "<semesters>"; 
			
			// hole Semesterbezeichnung für aktuelles Semester
			$qry_semester = "Select distinct(student.semester), student.studiensemester_kurzbz
			from campus.vw_student_lehrveranstaltung student
			left join lehre.tbl_zeugnisnote zeugnis on(student.lehrveranstaltung_id = zeugnis.lehrveranstaltung_id AND student.uid = zeugnis.student_uid AND student.studiensemester_kurzbz = zeugnis.studiensemester_kurzbz)
			join lehre.tbl_note note using(note) where uid = '$uid_arr[$i]' and zeugnis = true and
			semester = '$start';";			
			
			if($result_semester = $db->db_query($qry_semester))
			{
				if($row_semester = $db->db_fetch_object($result_semester))
				{
					$semester_kurzbz = $row_semester->studiensemester_kurzbz;
				}
			}
//			$aktuellesSemester = $semester_kurzbz;

			$semester = mb_substr($semester_kurzbz,0,2);
			$year = mb_substr($semester_kurzbz, 2,4); 
			
			if($semester == 'SS')
				$semester_kurzbz = 'Summer Semester '.$year;
			else if($semester == 'WS')
			{
				$helpyear = mb_substr($year, 2,2); 
				$helpyear +=1; 
				$helpyear = sprintf("%02d",$helpyear);
				$semester_kurzbz = 'Winter Semester '.$year.'/'.$helpyear;  
			}
			
			echo "   <semesterKurzbz>Semester $start | $semester_kurzbz</semesterKurzbz>";
			
			$qry ="Select distinct(student.lehrveranstaltung_id), student.uid, student.studiengang_kz, student.kurzbz, student.bezeichnung, student.bezeichnung_english, student.semester, student.semesterstunden, student.ects, student.studiensemester_kurzbz, zeugnis.note, note.bezeichnung note_bezeichnung, note.anmerkung
			from campus.vw_student_lehrveranstaltung student
			left join lehre.tbl_zeugnisnote zeugnis on(student.lehrveranstaltung_id = zeugnis.lehrveranstaltung_id AND student.uid = zeugnis.student_uid AND student.studiensemester_kurzbz = zeugnis.studiensemester_kurzbz)
			join lehre.tbl_note note using(note)
			
			where uid = '$uid_arr[$i]' and zeugnis = true and
			semester = '$start';"; 
			
			$j = 0; 
			$wochen = 15; 
			if($result_stud = $db->db_query($qry))
			{
				while($row_stud = $db->db_fetch_object($result_stud))
				{	
					$test = false; 
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
					
                    /* Fussnote nicht mehr notwendig -> Thesisbezeichnung wird gleich in LV geschrieben
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
							$thesis_beschreibung .=  "<thesis><bezeichnung>¹) ".$projektarbeit[$row_stud->lehrveranstaltung_id]['titel']."</bezeichnung>
														<note>".(isset($note_arr[$note])?$note_arr[$note]:$note).$nl."</note></thesis>";
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
					}*/

					echo " <lv>";
					
					// hole lehrform_kz von aktueller LV
					$qry_lehrform = "SELECT distinct(lehrform_kurzbz) FROM
					 lehre.tbl_lehreinheit WHERE studiensemester_kurzbz='$row_stud->studiensemester_kurzbz' and lehrveranstaltung_id = '$row_stud->lehrveranstaltung_id' 
					 ORDER BY lehrform_kurzbz";
					
					$lehrform_kurzbz = '';
					$y = 0; 
					if($result_lehrform = $db->db_query($qry_lehrform))
					{ 
						while($row_lehrform = $db->db_fetch_object($result_lehrform))
						{	if($y != 0)
								$lehrform_kurzbz = $lehrform_kurzbz.', '.$row_lehrform->lehrform_kurzbz;
							else
								$lehrform_kurzbz = $row_lehrform->lehrform_kurzbz;
							$y++;
						}
					}

					echo "   <lehrform_kurzbz>$lehrform_kurzbz</lehrform_kurzbz>"; 
					
					//  hole benotungsdatum von aktueller LV
					$qry_benotung = "SELECT benotungsdatum FROM 
					lehre.tbl_zeugnisnote WHERE lehrveranstaltung_id = '$row_stud->lehrveranstaltung_id'
					AND studiensemester_kurzbz = '$row_stud->studiensemester_kurzbz'
					AND student_uid = '$uid_arr[$i]';";
					if($result_benotung = $db->db_query($qry_benotung))
					{ 
						if($row_benotung = $db->db_fetch_object($result_benotung))
						{
							$benotungsdatum = $row_benotung->benotungsdatum; 
						}
					}
					
					$datum = new datum(); 
					$benotungsdatum = $datum->formatDatum($benotungsdatum,'d/m/Y'); 
					
                    $bezeichnung = $row_stud->bezeichnung; 
                    $bezeichnung_englisch = $row_stud->bezeichnung_english; 
                    
                    // Check ob an Lehrveranstaltung eine Thesis hängt
                    $qry = "SELECT lehrveranstaltung_id, titel, themenbereich, note, titel_english 
                    FROM lehre.tbl_projektarbeit 
                    JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
                    WHERE student_uid='".$uid_arr[$i]."' 
                    AND projekttyp_kurzbz in('Bachelor', 'Diplom') 
                    ORDER BY beginn ASC, projektarbeit_id ASC;";
                    
                    if($result_thesis = $db->db_query($qry))
                    {
                        while($row_thesis = $db->db_fetch_object($result_thesis))
                        {
                            if($row_thesis->lehrveranstaltung_id == $row_stud->lehrveranstaltung_id)
                            {
                                $bezeichnung.= ": \"".$row_thesis->titel."\"";
                                $bezeichnung_englisch.= ": \"".$row_thesis->titel."\""; 
                            }
                        }
                    }
                    
                    
					echo "   <benotungsdatum>$benotungsdatum</benotungsdatum>"; 
					echo "   <sws>".number_format($ssp,2)."</sws>"; 
					echo "   <semester>$row_stud->semester</semester>"; 					
					echo "   <kurzbz>$row_stud->kurzbz</kurzbz>";
					echo "   <stsem>$row_stud->studiensemester_kurzbz</stsem>"; 
					echo "   <bezeichnung><![CDATA[$bezeichnung]]></bezeichnung>"; 
                    echo "   <bezeichnung_englisch><![CDATA[$bezeichnung_englisch]]></bezeichnung_englisch>"; 
					/*
					if($test == true)
						echo "   <bezeichnung_englisch><![CDATA[$row_stud->bezeichnung_english]]>¹)</bezeichnung_englisch>"; 
					else 
						echo "   <bezeichnung_englisch><![CDATA[$row_stud->bezeichnung_english]]></bezeichnung_englisch>"; 
                    */
					echo "   <ects>$row_stud->ects</ects>"; 
					$ects_total += $row_stud->ects; 
					echo "   <semesterstunden>$row_stud->semesterstunden</semesterstunden>"; 
					$note = $row_stud->note; 
					switch ($note)
					{
						case 6: $note = 'ar';
						break;
						case 7: $note = 'nb'; 
						break;
						case 8: $note = 'tg';
						break; 
						case 12: $note = 'ea'; 
						break; 
						case 13: $note = 'nea';
						break; 
						case 14: $note = 'nbe';
						break; 
					}
					echo "   <note>$note</note>"; 
					echo "   <note_bezeichnung>$row_stud->note_bezeichnung</note_bezeichnung>"; 
					echo "   <note_anmerkung>$row_stud->anmerkung</note_anmerkung>"; 
					echo "   <lv_id>$row_stud->lehrveranstaltung_id</lv_id>"; 
					echo "</lv> ";
					
					$test = false; 
					
				}
					/*echo "<thesen>";
					echo $thesis_beschreibung;
					echo "</thesen>";*/
				echo "</semesters>";
			}
		}
		echo "</studiensemester>";
		//echo $xml_fussnote; 
		echo " <ects_total>$ects_total</ects_total>";
        
		echo '	</supplement>';
	}
	echo "</supplements>";
}
?>