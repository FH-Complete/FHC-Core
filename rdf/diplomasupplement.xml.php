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
				
		echo '	<supplement>';
		echo '		<nachname>'.$row->nachname.'</nachname>';
		echo '		<vorname>'.$row->vorname.'</vorname>';
		echo '		<vornamen>'.$row->vornamen.'</vornamen>';
		echo '		<geburtsdatum>'.$datum->convertISODate($row->gebdatum).'</geburtsdatum>';
		echo '		<matrikelnummer>'.$row->matrikelnr.'</matrikelnummer>';
		echo '		<studiengang_kz>'.sprintf("%04s",   $row->studiengang_kz).'</studiengang_kz>';
		echo '		<studiengang_bezeichnung_deutsch>'.$row->bezeichnung.'</studiengang_bezeichnung_deutsch>';
		echo '		<studiengang_bezeichnung_englisch>'.$row->english.'</studiengang_bezeichnung_englisch>';
		echo '		<semester>'.$row->max_semester.'</semester>';
		echo '		<jahre>'.($row->max_semester/2.0).'</jahre>';
		echo '		<ects>'.($row->max_semester*30).'</ects>';
		if($row->organisationsform=='b')
			echo '		<studienart>Berufbegleitendesstudium/Part-time degree programm</studienart>';
		else 
			echo '		<studienart>Vollzeitstudium/Full-time degree programm</studienart>';
		if($row->typ=='d')
		{
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Allgemeine Universitätsreife (vgl. $4 Abs. 3 FHStG idgF), Berufsreifeprüfung bzw. Studienberechtigungsprüfung oder einschlägige berufliche Qualifikation (Lehrabschluss bzw. Abschluss einer berufsbildenden mittleren Schule mit Zulassungsprüfung). Die Aufnahme erfolgt auf Basis eines Auswahlverfahrens (Werdegang, Eignungstest, Bewerbungsgespräch).]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[Austrian or quivalent foreign school leaving certificate (Reifeprüfung), university entrance examination certificate (Studienberechtigungsprüfung), certificate or quivalent relevant professional qualification (Berufsreifeprüfung) plus entrance examination equal to the university entrance examination. There is a selection procedure prior to admission (including entrance exam and interview, professional background is considered).]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von Lehrveranstaltungen(Vorlesungen, Übungen, Seminare, Projekte, integrierte Lehrveranstaltungen) im Ausmass von jeweils 30 ECTS pro Semester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung integriert technische, wirtschaftliche, organisatorische und persönlichkeitsbildende Elemente. Das Studium beinhaltet ein facheinschlägiges Berufpraktikum. Im Rahmen des Studiums ist eine Diplomarbeit zu verfassen und eine abschließende Prüfung (Diplomprüfung) zu absolvieren. Curriculum des Studienganges gemäß dem vom FHR mit Kennzahl '.sprintf('%04s', $row->studiengang_kz).' genehmigten Antrag.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The programm requires the positive completion of all courses (lectures, labs, seminars, projekct work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum. The programm integrates technical, economical, management and personal study elements. Included in the program is a relevant work placement. The degree is awarded upon the successful completion of a diploma these and the final examination. Curriculum of the program according to the application as approved by the Fachhochschul Council (Classification number: '.sprintf('%04s', $row->studiengang_kz).')]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Diplomstudiengangs berechtigt zu einem facheinschlägigen Doktoratsstudium, Magister- bzw. Master-Studium oder postgradualen Studium (in Abhängigkeit vom Studium mit eventuellen Zusatzprüfungen). Die Qualifikation entspricht einem Master of Science in Engineering, MSc.]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Diploma Degree Program qualifies the graduate to apply for admission to a relevant Doctoral Degree Program, Master Degree Program or postgraduate studies (depending on the program additional qualifying exams may be required). The Diplioma Degree Program is a graduate program, the qualification is equvalent to Master of Science in Engineering, MSc]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Diplomstudium (UNESCO ISCED 5A)</niveau_deutsch>';
			echo '		<niveau_englisch>Diploma degree program (UNESCO ISCED 5A)</niveau_englisch>';
		}
		elseif($row->typ=='m')
		{
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Die fachgliche Zugangsvoraussetzung (vgl. $4 Abs. 2 FHStG idgF) zu einem FH-Masterstudiengang ist ein abgeschlossener facheinschlägiger FH-Bachelorstudiengang ist ein abgeschlossener facheinschlägiger FH-Bachelorstudiengang order der Abschluss eines gleichwertigen Studiums an einer anerkannten inländischen oder ausländischen postsekundären Bildungseinrichtung. Die Aufnahme in den Studiengang erfolgt auf Basis eines Aufnahmeverfahrens.]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[Admission to the master\'s degree program is granted on the basis of the successful completion of a relevant post-secondary degree acknoledgement to be its equivalent. Admission is on the basis of a selection process.]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von Lehrveranstaltungen(Vorlesungen, Übungen, Seminare, Projekte, integrierte Lehrveranstaltungen) im Ausmass von jeweils 30 ECTS pro Semester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung integriert technische, wirtschaftliche, organisatorische und persönlichkeitsbildende Elemente. Im Rahmen des Studiums ist eine Diplomarbeit zu verfassen und eine abschließende Prüfung (Diplomprüfung) zu absolvieren. Curriculum des Studienganges gemäß dem vom FHR mit Kennzahl '.sprintf('%04s', $row->studiengang_kz).' genehmigten Antrag.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The programm requires the positive completion of all courses (lectures, labs, seminars, projekct work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum. The programm integrates technical, economical, management and personal study elements. The degree is awarded upon the successful completion of a diploma these and the final examination. Curriculum of the program according to the application as approved by the Fachhochschul Council (Classification number: '.sprintf('%04s', $row->studiengang_kz).')]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Masterstudiengangs berechtigt zu einem facheinschlägigen Doktoratsstudium an Universität (in Abhängigkeit vom Studium mit eventuellen Zusatzprüfungen)]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Master Degree Program qualifies the graduate to apply for admission to a relevant Doctoral Degree Program at an University (depending on the program additional qualifying exams may be required).]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Masterstudium (UNESCO ISCED 5A)</niveau_deutsch>';
			echo '		<niveau_englisch>Master degree program (UNESCO ISCED 5A)</niveau_englisch>';
		}
		elseif($row->typ=='b')
		{
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Allgemeine Universitätsreife (vgl. $4 Abs. 3 FHStG idgF), Berufsreifeprüfung bzw. Studienberechtigungsprüfung oder einschlägige berufliche Qualifikation (Lehrabschluss bzw. Abschluss einer berufsbildenden mittleren Schule mit Zulassungsprüfung). Die Aufnahme erfolgt auf Basis eines Auswahlverfahrens (Werdegang, Eignungstest, Bewerbungsgespräch).]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[Austrian or quivalent foreign school leaving certificate (Reifeprüfung), university entrance examination certificate (Studienberechtigungsprüfung), certificate or quivalent relevant professional qualification (Berufsreifeprüfung) plus entrance examination equal to the university entrance examination. There is a selection procedure prior to admission (including entrance exam and interview, professional background is considered).]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von Lehrveranstaltungen(Vorlesungen, Übungen, Seminare, Projekte, integrierte Lehrveranstaltungen) im Ausmass von jeweils 30 ECTS pro Semester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung integriert technische, wirtschaftliche, organisatorische und persönlichkeitsbildende Elemente. Das Studium beinhaltet ein facheinschlägiges Berufpraktikum. Im Rahmen des Studiums sind zwei Bachelorarbeiten zu verfassen und eine abschließende Prüfung (Bachelorprüfung) zu absolvieren. Curriculum des Studienganges gemäß dem vom FHR mit Kennzahl '.sprintf('%04s', $row->studiengang_kz).' genehmigten Antrag.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The programm requires the positive completion of all courses (lectures, labs, seminars, projekct work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum. The programm integrates technical, economical, management and personal study elements. Included in the program is a relevant work placement. The degree is awarded upon the successful completion of 2 bachelor these and the final examination. Curriculum of the program according to the application as approved by the Fachhochschul Council (Classification number: '.sprintf('%04s', $row->studiengang_kz).')]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Diplomstudiengangs berechtigt zu einem facheinschlägigen Magister- bzw. Master-Studium an einer fachhochschulischen Einrichtung oder Universität (mit eventuellen Zusatzprüfungen).]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Bachlor Degree Program qualifies the graduate to apply for admission to a relevant Master Degree Program at a University of Applied Sciences or a University (depending on the program additional qualifying exams may be required).]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Bachelorstudium (UNESCO ISCED 5A)</niveau_deutsch>';
			echo '		<niveau_englisch>Bachelor degree program (UNESCO ISCED 5A)</niveau_englisch>';
			
		}
		
		$qry = "SELECT bezeichnung FROM lehre.tbl_abschlusspruefung JOIN lehre.tbl_abschlussbeurteilung USING(abschlussbeurteilung_kurzbz) WHERE student_uid='".$uid_arr[$i]."' ORDER BY datum DESC LIMIT 1";
		if($result1 = pg_query($conn, $qry))
		{
			if($row1 = pg_fetch_object($result))
			{
				echo "		<beurteilung>$row1->bezeichnung</beurteilung>";
			}
		}
		
		$qry = "SELECT projektarbeit_id FROM lehre.tbl_projektarbeit WHERE student_uid='".$uid_arr[$i]."' AND projekttyp_kurzbz='Praxis'";
		if($result = pg_query($conn, $qry))
		{
			if($row1 = pg_fetch_object($result))
			{
				echo "		<praktikum>Berufspraktikum/Internship: absolviert/completet</praktikum>";
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