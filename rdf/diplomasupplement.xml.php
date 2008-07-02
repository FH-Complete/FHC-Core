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
require_once('../include/datum.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$datum = new datum();

if (isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{

	if(isset($_GET['uid']))
		$uid = $_GET['uid'];
	else 
		$uid = null;
	
	$uid_arr = explode(";",$uid);
	
	echo "<?xml version='1.0' encoding='ISO-8859-15' standalone='yes'?>\n";
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
		
		if($result = pg_query($conn, $query))
		{
				if(!$row = pg_fetch_object($result))
					die('Student not found'.$uid_arr[$i]);
		}
		else
			die('Student not found'.$uid_arr[$i]);
			
		//Bei DEW und DPW werden 60 ECTS angerechnet
		if($row->studiengang_kz==92 || $row->studiengang_kz==91)
			$angerechnete_sws=60;
		else 
			$angerechnete_sws=0;
		echo '	<supplement>';
		echo '		<nachname><![CDATA['.$row->nachname.']]></nachname>';
		echo '		<vorname>'.$row->vorname.'</vorname>';
		echo '		<vornamen>'.$row->vornamen.'</vornamen>';
		echo '		<geburtsdatum>'.$datum->convertISODate($row->gebdatum).'</geburtsdatum>';
		echo '		<matrikelnummer>'.$row->matrikelnr.'</matrikelnummer>';
		echo '		<studiengang_kz>'.sprintf("%04s",   $row->studiengang_kz).'</studiengang_kz>';
		echo '		<studiengang_bezeichnung_deutsch>'.$row->bezeichnung.'</studiengang_bezeichnung_deutsch>';
		echo '		<studiengang_bezeichnung_englisch>'.$row->english.'</studiengang_bezeichnung_englisch>';
		echo '		<semester>'.$row->max_semester.'</semester>';
		echo '		<jahre>'.($row->max_semester/2.0).'</jahre>';
		echo '		<ects>'.($row->max_semester*30+$angerechnete_sws).'</ects>';
		if($angerechnete_sws!=0)
			echo '		<ects_angerechnet>('.$angerechnete_sws.' ECTS angerechnet/credited)</ects_angerechnet>';
		else
			echo '		<ects_angerechnet></ects_angerechnet>';
			
		if($row->organisationsform=='b')
			echo '		<studienart>Berufbegleitendes Studium/Part-time degree programm</studienart>';
		else 
			echo '		<studienart>Vollzeitstudium/Full-time degree programm</studienart>';
		if($row->typ=='d')
		{
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Allgemeine Universitätsreife (vgl. §4 Abs. 3 FHStG idgF),\nBerufsreifeprüfung bzw. Studienberechtigungsprüfung oder\neinschlägige berufliche Qualifikation (Lehrabschluss bzw. Abschluss\neiner berufsbildenden mittleren Schule mit Zusatzprüfungen). Die\nAufnahme erfolgt auf Basis eines Auswahlverfahrens (Werdegang,\nEignungstest, Bewerbungsgespräch).]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[Austrian or equivalent foreign school leaving certificate\n(Reifeprüfung), university entrance examination certificate\n(Studienberechtigungsprüfung), certificate or equivalent relevant\nprofessional qualification (Berufsreifeprüfung) plus entrance\nexamination equal to the university entrance examination. Admission is\non the basis of a selection process (including entrance exam and\ninterview, professional background is considered).]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von\nLehrveranstaltungen (Vorlesungen, Übungen, Seminaren, Projekten,\nintegrierten Lehrveranstaltungen) im Ausmaß von jeweils 30 ECTS pro\nSemester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung\nintegriert technische, wirtschaftliche, organisatorische und\npersönlichkeitsbildende Elemente. Das Studium beinhaltet ein\nfacheinschlägiges Berufpraktikum. Im Rahmen des Studiums ist eine\nDiplomarbeit zu verfassen und eine abschließende Prüfung\n(Diplomprüfung) zu absolvieren. Curriculum des Studienganges\ngemäß dem vom FHR mit Kennzahl '.sprintf('%04s', $row->studiengang_kz).' genehmigten Antrag.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The program requires the positive completion of all courses (lectures, labs, seminars, project work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum. The program integrates technical, economical, management and personal study elements. Included in the program is a relevant work placement. The degree is awarded upon the successful completion of a diploma theses and the final examination. Curriculum of the program according to the application as approved by the Fachhochschul Council (Classification number: '.sprintf('%04s', $row->studiengang_kz).')]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Diplomstudiengangs berechtigt zu einem\nfacheinschlägigen Doktoratsstudium, Magister- bzw. Master-Studium\noder postgradualen Studium (mit eventuellen Zusatzprüfungen).\nDie Qualifikation entspricht einem Master of Science in Engineering, MSc.]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Diploma Degree Program qualifies the graduate to apply for admission to a relevant Doctoral Degree Program, Master Degree Program or postgraduate studies (additional qualifying exams may be required). The Diploma Degree Program is a graduate program, the qualification is equivalent to Master of Science in Engineering, MSc.]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Diplomstudium (UNESCO ISCED 5A)</niveau_deutsch>';
			echo '		<niveau_englisch>Diploma degree program (UNESCO ISCED 5A)</niveau_englisch>';
		}
		elseif($row->typ=='m')
		{
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Die fachliche Zugangsvoraussetzung (vgl. §4 Abs. 2 FHStG idgF) zu\neinem FH-Masterstudiengang ist ein abgeschlossener\nfacheinschlägiger FH-Bachelorstudiengang oder der Abschluss eines\ngleichwertigen Studiums an einer anerkannten inländischen oder\nausländischen postsekundären Bildungseinrichtung. Die Aufnahme in\nden Studiengang erfolgt auf Basis eines Auswahlverfahrens.]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[\nAdmission to the master\'s degree program is granted on the basis of\nthe successful completion of a relevant bachelor\'s degree program or a \ncomparable Austrian or foreign post-secondary degree\nacknowledged to be its equivalent. Admission is on the basis of a\nselection process.\n]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von\nLehrveranstaltungen (Vorlesungen, Übungen, Seminaren, Projekten,\nintegrierten Lehrveranstaltungen) im Ausmaß von jeweils 30 ECTS pro\nSemester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung\nintegriert technische, wirtschaftliche, organisatorische und\npersönlichkeitsbildende Elemente. Im Rahmen des Studiums ist eine\nDiplomarbeit zu verfassen und eine abschließende Prüfung\n(Diplomprüfung) zu absolvieren. Curriculum des Studienganges\ngemäß dem vom FHR mit Kennzahl '.sprintf('%04s', $row->studiengang_kz).' genehmigten Antrag.\n]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The program requires the positive completion of all courses (lectures, labs, seminars, project work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum.\n The program integrates technical, economical, management and personal study elements. The degree is awarded upon the successful completion of a diploma theses and the final examination. Curriculum of the program according to the application as approved by the Fachhochschul Council (Classification number: '.sprintf('%04s', $row->studiengang_kz).')\n]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Masterstudiengangs berechtigt zu einem facheinschlägigen Doktoratsstudium an einer Universität (mit eventuellen Zusatzprüfungen).]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Master Degree Program qualifies the graduate to apply for admission to a relevant Doctoral Degree Program at a University (additional qualifying exams may be required).\n\n\n\n]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Masterstudium (UNESCO ISCED 5A)</niveau_deutsch>';
			echo '		<niveau_englisch>Master degree program (UNESCO ISCED 5A)</niveau_englisch>';
		}
		elseif($row->typ=='b')
		{
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Allgemeine Universitätsreife (vgl. §4 Abs. 3 FHStG idgF),\nBerufsreifeprüfung bzw. Studienberechtigungsprüfung oder\neinschlägige berufliche Qualifikation (Lehrabschluss bzw. Abschluss\neiner berufsbildenden mittleren Schule mit Zusatzprüfungen). Die\nAufnahme erfolgt auf Basis eines Auswahlverfahrens (Werdegang,\nEignungstest, Bewerbungsgespräch).]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[Austrian or equivalent foreign school leaving certificate\n(Reifeprüfung), university entrance examination certificate\n(Studienberechtigungsprüfung), certificate or equivalent relevant\nprofessional qualification (Berufsreifeprüfung) plus entrance\nexamination equal to the university entrance examination. Admission is \non the basis of a selection process. (including entrance exam\nand interview, professional background is considered).]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von\nLehrveranstaltungen (Vorlesungen, Übungen, Seminaren, Projekten,\nintegrierten Lehrveranstaltungen) im Ausmaß von jeweils 30 ECTS pro\nSemester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung\nintegriert technische, wirtschaftliche, organisatorische und\npersönlichkeitsbildende Elemente. Das Studium beinhaltet ein\nfacheinschlägiges Berufpraktikum. Im Rahmen des Studiums sind\nzwei Bachelorarbeiten zu verfassen und eine abschließende Prüfung\n(Bachelorprüfung) zu absolvieren. Curriculum des Studienganges\ngemäß dem vom FHR mit Kennzahl '.sprintf('%04s', $row->studiengang_kz).' genehmigten Antrag.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The program requires the positive completion of all courses (lectures, labs, seminars, project work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum. The program integrates technical, economical, management and personal study elements. Included in the program is a relevant work placement. The degree is awarded upon the successful completion of 2 bachelor theses and the final examination. Curriculum of the program according to the application as approved by the Fachhochschul Council (Classification number: '.sprintf('%04s', $row->studiengang_kz).')]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Bachelorstudiengangs berechtigt zu einem facheinschlägigen Magister- bzw. Master-Studium an einer fachhochschulischen Einrichtung oder Universität (mit eventuellen Zusatzprüfungen).]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Bachelor Degree Program qualifies the graduate to apply for admission to a relevant Master Degree Program at a University of Applied Sciences or a University (additional qualifying exams may be required).\n\n\n]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Bachelorstudium (UNESCO ISCED 5A)</niveau_deutsch>';
			echo '		<niveau_englisch>Bachelor degree program (UNESCO ISCED 5A)</niveau_englisch>';
			
		}
		
		$qry = "SELECT bezeichnung, akadgrad_id FROM lehre.tbl_abschlusspruefung JOIN lehre.tbl_abschlussbeurteilung USING(abschlussbeurteilung_kurzbz) WHERE student_uid='".$uid_arr[$i]."' ORDER BY datum DESC LIMIT 1";
		if($result1 = pg_query($conn, $qry))
		{
			if($row1 = pg_fetch_object($result1))
			{
				echo "		<beurteilung>$row1->bezeichnung</beurteilung>";
				$akadgrad_id = $row1->akadgrad_id;
			}
		}
		
		$qry = "SELECT * FROM lehre.tbl_akadgrad WHERE akadgrad_id='$akadgrad_id'";
		$titel = '';
		$titel_kurzbz = '';
		if($result_titel = pg_query($conn, $qry))
		{
			if($row_titel = pg_fetch_object($result_titel))
			{
				$titel = $row_titel->titel;
				$titel_kurzbz = $row_titel->akadgrad_kurzbz;
			}
		}
		echo '		<titel>'.$titel.'</titel>';
		echo '		<titel_kurzbz>'.$titel_kurzbz.'</titel_kurzbz>';
	
		$qry = "SELECT projektarbeit_id FROM lehre.tbl_projektarbeit WHERE student_uid='".$uid_arr[$i]."' AND (projekttyp_kurzbz='Praxis' OR projekttyp_kurzbz='Praktikum')";
		if($result = pg_query($conn, $qry))
		{
			if($row1 = pg_fetch_object($result))
			{
				echo "		<praktikum>Berufspraktikum/Internship: absolviert/completed</praktikum>";
			}
		}
		
		$qry = "SELECT von, bis FROM bis.tbl_bisio WHERE student_uid='".$uid_arr[$i]."'";
		if($result = pg_query($conn, $qry))
		{
			if($row1 = pg_fetch_object($result))
			{
				echo "		<auslandssemester>Auslandssemester/International semester ".$datum->convertISODate($row1->von)." - ".$datum->convertISODate($row1->bis)."</auslandssemester>";
			}
		}
		
		$qry = "SELECT * FROM campus.vw_mitarbeiter JOIN public.tbl_benutzerfunktion USING(uid) WHERE studiengang_kz='$row->studiengang_kz' AND funktion_kurzbz='stgl'";
		if($result = pg_query($conn, $qry))
		{
			if($row1 = pg_fetch_object($result))
			{
				echo "		<stgl>$row1->titelpre $row1->vorname $row1->nachname $row1->titelpost</stgl>";
			}
		}
		
		$qry = "SELECT telefonklappe FROM public.tbl_mitarbeiter JOIN tbl_benutzerfunktion ON(uid=mitarbeiter_uid) WHERE funktion_kurzbz='ass' AND studiengang_kz='$row->studiengang_kz'";
		if($result = pg_query($conn, $qry))
		{
			if($row1 = pg_fetch_object($result))
			{
				echo "		<telefonklappe>$row1->telefonklappe</telefonklappe>";
			}
		}
		echo '		<tagesdatum>'.date('d.m.Y').'</tagesdatum>';
		
		echo '	</supplement>';
	}
	echo "</supplements>";
}
?>