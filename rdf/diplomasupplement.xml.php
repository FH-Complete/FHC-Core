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
require_once('../include/studienordnung.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/student.class.php');
require_once('../include/firma.class.php');
require_once('../include/note.class.php');

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
						vw_student.vorname, vw_student.nachname, vw_student.vornamen, vw_student.gebdatum,
						vw_student.matrikelnr, vw_student.prestudent_id,
						tbl_studiengang.bezeichnung, tbl_studiengang.english, tbl_studiengang.studiengang_kz,
						tbl_studiengang.typ, tbl_studiengang.mischform, tbl_studiengang.max_semester,
						tbl_studiengang.orgform_kurzbz
				  FROM
						campus.vw_student JOIN public.tbl_studiengang USING(studiengang_kz)
				  WHERE
						uid = ".$db->db_add_param($uid_arr[$i]);

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

		//Wenn Lehrgang, dann Erhalter-KZ vor die Studiengangs-Kz hängen
		if ($row->studiengang_kz<0)
		{
			$stg = new studiengang();
			$stg->load($row->studiengang_kz);

			$studiengang_kz = sprintf("%03s", $stg->erhalter_kz).sprintf("%04s", abs($row->studiengang_kz));
		}
		else
			$studiengang_kz = sprintf("%04s", abs($row->studiengang_kz));
		echo '	<supplement>';
		echo '		<nachname><![CDATA['.$row->nachname.']]></nachname>';
		echo '		<vorname><![CDATA['.$row->vorname.']]></vorname>';
		echo '		<vornamen><![CDATA['.$row->vornamen.']]></vornamen>';
		echo '		<name><![CDATA['.$row->vorname.' '.$row->nachname.']]></name>';
		echo '		<geburtsdatum><![CDATA['.$datum->convertISODate($row->gebdatum).']]></geburtsdatum>';
		echo '		<matrikelnummer>'.TRIM($row->matrikelnr).'</matrikelnummer>';
		echo '		<studiengang_kz>'.$studiengang_kz.'</studiengang_kz>';

		$prestudent = new prestudent($row->prestudent_id);
		if ($prestudent->getLastStatus($row->prestudent_id, null, 'Student'))
		{
			$studienplan_id = $prestudent->studienplan_id;
			$studienordnung = new studienordnung();
			if ($studienordnung->getStudienordnungFromStudienplan($studienplan_id))
			{
				$studiengangbezeichnung = $studienordnung->__get('studiengangbezeichnung');
				$studiengangbezeichnung_englisch = $studienordnung->__get('studiengangbezeichnung_englisch');
			}
		}
		$studiengang_bezeichnung = empty($studiengangbezeichnung) ? $row->bezeichnung : $studiengangbezeichnung;
		$studiengang_bezeichnung_englisch = empty($studiengangbezeichnung_englisch) ? $row->english : $studiengangbezeichnung_englisch;

		echo '		<studiengang_bezeichnung_deutsch><![CDATA['.$studiengang_bezeichnung.']]></studiengang_bezeichnung_deutsch>';
		echo '		<studiengang_bezeichnung_englisch><![CDATA['.$studiengang_bezeichnung_englisch.']]></studiengang_bezeichnung_englisch>';

		$prestudent = new prestudent();
		$prestudent->getFirstStatus($row->prestudent_id, 'Student');
		$semesterNumberStart = $prestudent->ausbildungssemester;


		//ECTS-Punkte die bei Quereinsteigern angerechnet werden
		if($semesterNumberStart>1)
		{
			$angerechneteECTS=($semesterNumberStart-1)*30; // 30 ECTS pro Semester
			echo '		<angerechnete_ects_quereinstieg>'.$angerechneteECTS.'</angerechnete_ects_quereinstieg>';
		}
		echo '      <start_semester>'.substr($prestudent->studiensemester_kurzbz,2,6).'</start_semester>';
		echo '      <start_semester_number>'.$prestudent->ausbildungssemester.'</start_semester_number>';
		$prestudent->getLastStatus($row->prestudent_id, null);
		$semesterNumberEnd = $prestudent->ausbildungssemester;
		echo '      <end_semester>'.substr($prestudent->studiensemester_kurzbz,2,6).'</end_semester>';
		echo '      <end_semester_number>'.$prestudent->ausbildungssemester.'</end_semester_number>';

		switch ($row->typ)
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
		//Wenn Lehrgang, kommen die max-semester nicht aus tbl_studiengang,
		// sondern aus dem höchsten prestudent-status-semester
		if($row->studiengang_kz<0)
		{
			$lastPrestudentSemester=new prestudent();
			$lastPrestudentSemester->getLastStatus($row->prestudent_id,null,'Student');
			$maxsemester=$lastPrestudentSemester->ausbildungssemester;
		}
		else
			$maxsemester=$row->max_semester;
		echo '		<semester>'.$maxsemester.'</semester>';
		echo '		<jahre>'.($maxsemester/2.0).'</jahre>';
		echo '		<ects>'.($maxsemester*30+$angerechnete_sws).'</ects>';
		if($angerechnete_sws!=0)
			echo '		<ects_angerechnet>('.$angerechnete_sws.' ECTS angerechnet/credited)</ects_angerechnet>';
		else
			echo '		<ects_angerechnet></ects_angerechnet>';

		//OrgForm aus dem Status nehmen
		$prestudent = new prestudent();
		$prestudent->getLastStatus($row->prestudent_id);
		if($prestudent->orgform_kurzbz!='')
			$row->orgform_kurzbz=$prestudent->orgform_kurzbz;

		$anforderungen_praxis = 'Das Studium beinhaltet ein facheinschlägiges Berufspraktikum.';
		$anforderungen_praxiseng = 'Included in the program is a relevant work placement.';

		switch($row->orgform_kurzbz)
		{
			case 'BB':	echo '		<studienart>Berufsbegleitendes Studium / Part-time degree programm</studienart>';
				break;
			case 'VZ':	echo '		<studienart>Vollzeitstudium / Full-time degree programm</studienart>';
				break;
			case 'DL':	echo '		<studienart>Fernstudium / Distance Learning</studienart>';
				break;
			case 'DUA': // andere praxisanforderungen bei dualem Studium
				echo '		<studienart>Duales Studium / Integrated work/study degree program</studienart>';
				$anforderungen_praxis = 'Das Studium ist als duales Studium konzipiert und weist einen hohen Anteil an Praxisphasen in Partnerunternehmen auf, die inhaltlich und organisatorisch im Studienplan verankert sind und die systematische Verschränkung von Wissen und Anwendung fördern.';
				$anforderungen_praxiseng = 'The program is designed as integrated work study program and has a high proportion of practical phases in partner companies, which are embedded in the curriculum in terms of content and organisation and promote the systematic interconnection of knowledge and utilisation.';
				break;
			default:	echo '		<studienart></studienart>';
				break;
		}

		if($row->typ=='d')
		{
			echo '		<niveau_code>UNESCO ISCED 7</niveau_code>';
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Allgemeine Universitätsreife (vgl. §4 Abs. 3 FHStG idgF), Berufsreifeprüfung bzw. Studienberechtigungsprüfung oder einschlägige berufliche Qualifikation (Lehrabschluss bzw. Abschluss einer berufsbildenden mittleren Schule mit Zusatzprüfungen). Die Aufnahme erfolgt auf Basis eines Auswahlverfahrens (Werdegang, Eignungstest, Bewerbungsgespräch).]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[Austrian or equivalent foreign school leaving certificate (Reifeprüfung), university entrance examination certificate (Studienberechtigungsprüfung), certificate or equivalent relevant professional qualification (Berufsreifeprüfung) plus entrance examination equal to the university entrance examination. Admission is on the basis of a selection process (including entrance exam and interview, professional background is considered).]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von Lehrveranstaltungen (Vorlesungen, Übungen, Seminaren, Projekten, integrierten Lehrveranstaltungen) im Ausmaß von jeweils 30 ECTS pro Semester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung integriert technische, wirtschaftliche, organisatorische und persönlichkeitsbildende Elemente. '.$anforderungen_praxis.' Im Rahmen des Studiums ist eine Diplomarbeit zu verfassen und eine abschließende Prüfung (Diplomprüfung) zu absolvieren. Der Studiengang (Kennzahl '.$studiengang_kz.') ist von der AQ Austria akkreditiert.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The program requires the positive completion of all courses (lectures, labs, seminars, project work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum. The program integrates technical, economical, management and personal study elements. '.$anforderungen_praxiseng.' The degree is awarded upon the successful completion of a diploma theses and the final examination. The program (classification number '.$studiengang_kz.') is accredited by AQ Austria.]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Diplomstudiengangs berechtigt zu einem facheinschlägigen Doktoratsstudium, Magister- bzw. Master-Studium oder postgradualen Studium (mit eventuellen Zusatzprüfungen). Die Qualifikation entspricht einem Master of Science in Engineering, MSc.]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Diploma Degree Program qualifies the graduate to apply for admission to a relevant Doctoral Degree Program, Master Degree Program or postgraduate studies (additional qualifying exams may be required). The Diploma Degree Program is a graduate program, the qualification is equivalent to Master of Science in Engineering, MSc.]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Diplomstudium (UNESCO ISCED 7)</niveau_deutsch>';
			echo '		<niveau_englisch>Diploma degree program (UNESCO ISCED 7)</niveau_englisch>';
		}
		elseif($row->typ=='m')
		{
			echo '		<niveau_code>UNESCO ISCED 7</niveau_code>';
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Die fachliche Zugangsvoraussetzung (vgl. §4 Abs. 2 FHStG idgF) zu einem FH-Masterstudiengang ist ein abgeschlossener facheinschlägiger FH-Bachelorstudiengang oder der Abschluss eines gleichwertigen Studiums an einer anerkannten inländischen oder ausländischen postsekundären Bildungseinrichtung. Die Aufnahme in den Studiengang erfolgt auf Basis eines Auswahlverfahrens.]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[ Admission to the master\'s degree program is granted on the basis of the successful completion of a relevant bachelor\'s degree program or a  comparable Austrian or foreign post-secondary degree acknowledged to be its equivalent. Admission is on the basis of a selection process. ]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von Lehrveranstaltungen (Vorlesungen, Übungen, Seminaren, Projekten, integrierten Lehrveranstaltungen) im Ausmaß von jeweils 30 ECTS pro Semester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung integriert technische, wirtschaftliche, organisatorische und persönlichkeitsbildende Elemente. Im Rahmen des Studiums ist eine Masterarbeit zu verfassen und eine abschließende Prüfung (Masterprüfung) zu absolvieren. Der Studiengang (Kennzahl '.$studiengang_kz.') ist von der AQ Austria akkreditiert.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The program requires the positive completion of all courses (lectures, labs, seminars, project work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum.  The program integrates technical, economical, management and personal study elements. The degree is awarded upon the successful completion of a Master´s Thesis and the final examination. The program (classification number '.$studiengang_kz.') is accredited by AQ Austria.]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Masterstudiengangs berechtigt zu einem facheinschlägigen Doktoratsstudium an einer Universität (mit eventuellen Zusatzprüfungen).]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Master Degree Program qualifies the graduate to apply for admission to a relevant Doctoral Degree Program at a University (additional qualifying exams may be required).    ]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Masterstudium (UNESCO ISCED 7)</niveau_deutsch>';
			echo '		<niveau_englisch>Master degree program (UNESCO ISCED 7)</niveau_englisch>';
		}
		elseif($row->typ=='b')
		{
			echo '		<niveau_code>UNESCO ISCED 6</niveau_code>';
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Allgemeine Universitätsreife (vgl. §4 Abs. 3 FHStG idgF), Berufsreifeprüfung bzw. Studienberechtigungsprüfung oder einschlägige berufliche Qualifikation (Lehrabschluss bzw. Abschluss einer berufsbildenden mittleren Schule mit Zusatzprüfungen). Die Aufnahme erfolgt auf Basis eines Auswahlverfahrens (Werdegang, Eignungstest, Bewerbungsgespräch).]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[Austrian or equivalent foreign school leaving certificate (Reifeprüfung), university entrance examination certificate (Studienberechtigungsprüfung), certificate or equivalent relevant professional qualification (Berufsreifeprüfung) plus entrance examination equal to the university entrance examination. Admission is  on the basis of a selection process. (including entrance exam and interview, professional background is considered).]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von Lehrveranstaltungen (Vorlesungen, Übungen, Seminaren, Projekten, integrierten Lehrveranstaltungen) im Ausmaß von jeweils 30 ECTS pro Semester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung integriert technische, wirtschaftliche, organisatorische und persönlichkeitsbildende Elemente. '.$anforderungen_praxis.' Im Rahmen des Studiums ist eine Bachelorarbeit zu verfassen und eine abschließende Prüfung (Bachelorprüfung) zu absolvieren. Der Studiengang (Kennzahl '.$studiengang_kz.') ist von der AQ Austria akkreditiert.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The program requires the positive completion of all courses (lectures, labs, seminars, project work, and integrated courses) to the extend of 30 ECTS per semester according to the curriculum. The program integrates technical, economical, management and personal study elements. '.$anforderungen_praxiseng.' The degree is awarded upon the successful completion of 1 bachelor theses and the final examination. The program (classification number '.$studiengang_kz.') is accredited by AQ Austria.]]></anforderungen_englisch>';
			echo '		<zugangsberechtigung_deutsch><![CDATA[Der Abschluss des Bachelorstudiengangs berechtigt zu einem facheinschlägigen Magister- bzw. Master-Studium an einer fachhochschulischen Einrichtung oder Universität (mit eventuellen Zusatzprüfungen).]]></zugangsberechtigung_deutsch>';
			echo '		<zugangsberechtigung_englisch><![CDATA[The successful completion of the Bachelor Degree Program qualifies the graduate to apply for admission to a relevant Master Degree Program at a University of Applied Sciences or a University (additional qualifying exams may be required).]]></zugangsberechtigung_englisch>';
			echo '		<niveau_deutsch>Bachelorstudium (UNESCO ISCED 6)</niveau_deutsch>';
			echo '		<niveau_englisch>Bachelor degree program (UNESCO ISCED 6)</niveau_englisch>';
		}
		elseif($row->typ=='r')
		{
			echo '		<niveau_code>UNESCO ISCED 8</niveau_code>';
			echo '		<niveau_deutsch>Doktoratsstudium (UNESCO ISCED 8)</niveau_deutsch>';
			echo '		<niveau_englisch>University doctoral studies (UNESCO ISCED 8)</niveau_englisch>';
		}
		elseif($row->typ=='l' || $row->typ=='k' || $row->typ=='e')
		{
			echo '		<niveau_code>UNESCO ISCED 7</niveau_code>';
			echo '		<niveau_deutsch>Lehrgang zur Weiterbildung nach §9 FHStG idgF.</niveau_deutsch>';
			echo '		<niveau_englisch>Certificate Program for Further Education subjected to § 9 FHStG</niveau_englisch>';
			echo '		<zulassungsvoraussetzungen_deutsch><![CDATA[Facheinschlägiger Studienabschluss oder einschlägige Berufserfahrung]]></zulassungsvoraussetzungen_deutsch>';
			echo '		<zulassungsvoraussetzungen_englisch><![CDATA[Appropriate university degree or appropriate work experience]]></zulassungsvoraussetzungen_englisch>';
			echo '		<anforderungen_deutsch><![CDATA[Das Studium erfordert die positive Absolvierung von Lehrveranstaltungen (Vorlesungen, Übungen, Seminaren, Projekten, integrierten Lehrveranstaltungen) im Ausmaß der laut Studienplan vorgeschriebenen ECTS. Die Ausbildung integriert technische, wirtschaftliche, organisatorische und persönlichkeitsbildende Elemente. Im Rahmen des Master-Lehrgangs ist eine Master Thesis zu verfassen und eine abschließende Prüfung  (Masterprüfung) zu absolvieren. Der Lehrgang ist vom Kollegium der FH Technikum  Wien genehmigt und der AQ Austria (Kennzahl '.$studiengang_kz.') gemeldet.]]></anforderungen_deutsch>';
			echo '		<anforderungen_englisch><![CDATA[The program requires the positive completion of all courses (lectures, labs, seminars, project work, and integrated courses) to the ECTS according to the curriculum. The program integrates technical, economical, management and personal study elements. The degree for the Master Course is awarded upon the successful completion of a Master´s Thesis and the final examination. The program is accredited by the Council of the University of Applied Sciences Technikum Wien and reported to AQ Austria (classification number '.$studiengang_kz.')]]></anforderungen_englisch>';
		}

		$akadgrad_id='';

		$qry = "SELECT
					bezeichnung, akadgrad_id, bezeichnung_english
				FROM
					lehre.tbl_abschlusspruefung
					LEFT JOIN lehre.tbl_abschlussbeurteilung USING(abschlussbeurteilung_kurzbz)
				WHERE
					student_uid=".$db->db_add_param($uid_arr[$i])."
				ORDER BY datum DESC LIMIT 1";

		if($db->db_query($qry))
		{
			if($row1 = $db->db_fetch_object())
			{
				$akadgrad_id = $row1->akadgrad_id;
			}

			echo "		<beurteilung>In diesem Curriculum nicht zutreffend.</beurteilung>";
			echo "		<beurteilung_english>Not applicable within this curriculum.</beurteilung_english>";
		}

		$qry = "SELECT * FROM lehre.tbl_akadgrad WHERE akadgrad_id=".$db->db_add_param($akadgrad_id);
		$titel_de = '';
		$titel_en = '';
		$titel_kurzbz = '';
		if($akadgrad_id!='')
		{
			if($db->db_query($qry))
			{
				if($row_titel = $db->db_fetch_object())
				{
					$titel_de = $row_titel->titel.($row_titel->akadgrad_kurzbz!=''?', ('.$row_titel->akadgrad_kurzbz.')':'');
					$titel_en = $row_titel->titel.($row_titel->akadgrad_kurzbz!=''?', ('.$row_titel->akadgrad_kurzbz.')':'');
				}
			}
		}
		echo '		<titel_de>'.$titel_de.'</titel_de>';
		echo '		<titel_en>'.$titel_en.'</titel_en>';
		$praktikum = false;
		$auslandssemester = false;
		$qry = "SELECT
					projektarbeit_id
				FROM
					lehre.tbl_projektarbeit
				WHERE
					student_uid=".$db->db_add_param($uid_arr[$i])."
					AND (projekttyp_kurzbz='Praxis' OR projekttyp_kurzbz='Praktikum')";

		if($db->db_query($qry))
		{
			if($row1 = $db->db_fetch_object())
			{
				echo "		<praktikum>Berufspraktikum/Internship: absolviert/completed</praktikum>";
				$praktikum = true;
			}
		}

		$qry = "SELECT tbl_bisio.bisio_id, von, bis, lehreinheit_id,
       			(SELECT STRING_AGG (
						tbl_zweck.bezeichnung,
						', '
					   	ORDER BY tbl_zweck.zweck_code
					) FROM bis.tbl_bisio_zweck
					JOIN bis.tbl_zweck USING(zweck_code)
					WHERE tbl_bisio_zweck.bisio_id = tbl_bisio.bisio_id
				) zweck, ort, universitaet
				FROM bis.tbl_bisio
				WHERE student_uid=".$db->db_add_param($uid_arr[$i]);

		if($db->db_query($qry))
		{
			if($db->db_num_rows()>0)
			{
				echo "<auslandssemester>";
				while($row1 = $db->db_fetch_object())
				{
					echo "<auslandssemesters>";
					echo "<von>".$datum->convertISODate($row1->von)."</von>";
					echo "<bis>".$datum->convertISODate($row1->bis)."</bis>";
					echo "<zweck>$row1->zweck</zweck>";
					echo "<ort>$row1->ort</ort>";
					echo "<universitaet>$row1->universitaet</universitaet>";
					echo "</auslandssemesters>";
				}
				echo "</auslandssemester>";
				$auslandssemester=true;
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

		$abschlussbeurteilung='';
		// Hole Datum der Sponsion -> wenn keine vorhanden nimm aktuelles datum
		$qry = "SELECT
					sponsion, tbl_abschlussbeurteilung.bezeichnung_english, datum, pruefungstyp_kurzbz
				FROM
					lehre.tbl_abschlusspruefung
					JOIN lehre.tbl_abschlussbeurteilung USING(abschlussbeurteilung_kurzbz)
				WHERE
					student_uid=".$db->db_add_param($uid_arr[$i])."
				ORDER BY datum DESC LIMIT 1";

		$sponsion_datum = date('d.m.Y');
		$abschlusspruefungsdatum = '';
		$abschlussbeurteilung='';
		$pruefungstyp_kurzbz='';

		if($db->db_query($qry))
		{
			if($row1= $db->db_fetch_object())
			{
				$sponsion_datum = $datum->formatDatum($row1->sponsion, 'd.m.Y');
				$abschlusspruefungsdatum = $datum->formatDatum($row1->datum, 'd.m.Y');
				$abschlussbeurteilung = $row1->bezeichnung_english;
				$pruefungstyp_kurzbz = $row1->pruefungstyp_kurzbz;
			}
		}
		echo "		<pruefungstyp_kurzbz>$pruefungstyp_kurzbz</pruefungstyp_kurzbz>";
		echo "		<abschlussbeurteilung>$abschlussbeurteilung</abschlussbeurteilung>";
		echo "		<abschlusspruefungsdatum>$abschlusspruefungsdatum</abschlusspruefungsdatum>";
		echo "      <sponsion_datum>$sponsion_datum</sponsion_datum>";

		$qry = "SELECT telefonklappe FROM public.tbl_mitarbeiter JOIN tbl_benutzerfunktion ON(uid=mitarbeiter_uid)
				WHERE funktion_kurzbz='ass' AND oe_kurzbz=".$db->db_add_param($stg_oe_obj->oe_kurzbz);
		if($db->db_query($qry))
		{
			if($row1 = $db->db_fetch_object())
			{
				echo "		<telefonklappe>$row1->telefonklappe</telefonklappe>";
			}
		}
		echo '		<datum>'.date('d.m.Y').'</datum>';

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
		$qry_prevYear = "
		SELECT note, count(note)
		FROM
			lehre.tbl_zeugnisnote
		WHERE
			lehrveranstaltung_id IN(
				SELECT distinct(lehrveranstaltung_id)
				FROM
					lehre.tbl_lehreinheit
					JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
				WHERE
					studiensemester_kurzbz IN (".$db->db_add_param($lastStatusSemester).",".$db->db_add_param($studiensemesterPrev).")
					AND studiengang_kz = ".$db->db_add_param($row->studiengang_kz, FHC_INTEGER)."
			)
			AND studiensemester_kurzbz IN (".$db->db_add_param($lastStatusSemester).",".$db->db_add_param($studiensemesterPrev).")
		GROUP BY note
		ORDER BY note";
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

		// Division durch 0 verhindern
		if($noten_anzahl==0)
			$noten_anzahl=1;

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
		$qry_prevYear = "
		SELECT
			note, count(note)
		FROM
			lehre.tbl_zeugnisnote
		WHERE
			lehrveranstaltung_id IN(
				SELECT
					distinct(lehrveranstaltung_id)
				FROM
					lehre.tbl_lehreinheit
					JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
				WHERE
					studiensemester_kurzbz IN (".$db->db_add_param($lastStatusSemester).",".$db->db_add_param($studiensemesterPrev).")
					AND studiengang_kz = ".$db->db_add_param($row->studiengang_kz, FHC_INTEGER).")
			AND studiensemester_kurzbz IN (".$db->db_add_param($lastStatusSemester).",".$db->db_add_param($studiensemesterPrev).")
		GROUP BY note
		ORDER BY note";
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

		// Division durch 0 verhindern
		if($noten_anzahl==0)
			$noten_anzahl=1;

		// Noten: 1-5, angerechnet, nicht beurteilt, erfolgreich absolviert anzeigen
		echo "  <gradePrevLastYear1>".sprintf("%01.1f",($noteArrayPrev[1]/$noten_anzahl*100))."</gradePrevLastYear1>";
		echo "  <gradePrevLastYear2>".sprintf("%01.1f",($noteArrayPrev[2]/$noten_anzahl*100))."</gradePrevLastYear2>";
		echo "  <gradePrevLastYear3>".sprintf("%01.1f",($noteArrayPrev[3]/$noten_anzahl*100))."</gradePrevLastYear3>";
		echo "  <gradePrevLastYear4>".sprintf("%01.1f",($noteArrayPrev[4]/$noten_anzahl*100))."</gradePrevLastYear4>";
		echo "  <gradePrevLastYear5>".sprintf("%01.1f",($noteArrayPrev[5]/$noten_anzahl*100))."</gradePrevLastYear5>";
		echo "  <gradePrevLastYearAr>".sprintf("%01.1f",($noteArrayPrev[6]/$noten_anzahl*100))."</gradePrevLastYearAr>";
		echo "  <gradePrevLastYearNb>".sprintf("%01.1f",($noteArrayPrev[7]/$noten_anzahl*100))."</gradePrevLastYearNb>";
		echo "  <gradePrevLastYearEa>".sprintf("%01.1f",($noteArrayPrev[12]/$noten_anzahl*100))."</gradePrevLastYearEa>";

		$ects_total = 0;
		$ects_total_positiv = 0;

		echo "<studiensemester>";
		for($start = $semesterNumberStart; $start <= $semesterNumberEnd; $start++)
		{
			$semester_ects = 0;
			$semester_ects_positiv = 0;
			echo "<semesters>";

			// alle semester für das ausbildungssemester holen
			// Semester wo Unterbrecher nicht holen
			$qry_semester="
			SELECT
				distinct(status.studiensemester_kurzbz), datum
			FROM
				lehre.tbl_zeugnisnote zeugnis
				JOIN lehre.tbl_note note USING(note)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				JOIN public.tbl_student student USING(student_uid)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
			WHERE
				student_uid = ".$db->db_add_param($uid_arr[$i])."
				AND zeugnis = true
				AND status.ausbildungssemester = ".$db->db_add_param($start)."
				AND status.status_kurzbz NOT IN('Unterbrecher', 'Interessent','Bewerber','Aufgenommener','Abgewiesener','Wartender')
			ORDER BY datum ASC";

			$semester_kurzbz = array();
			if($result_semester = $db->db_query($qry_semester))
			{
				while($row_semester = $db->db_fetch_object($result_semester))
				{
					$semester_kurzbz[] = $row_semester->studiensemester_kurzbz;
				}
			}

			// Array der Semester
			$aktuellesSemester = $semester_kurzbz;

			$semester = mb_substr($semester_kurzbz[0],0,2);
			$year = mb_substr($semester_kurzbz[0], 2,4);

			if($semester == 'SS')
				$semester_kurzbz = 'Summer Semester '.$year;
			else if($semester == 'WS')
			{
				$helpyear = mb_substr($year, 2,2);
				$helpyear +=1;
				$helpyear = sprintf("%02d",$helpyear);
				$semester_kurzbz = 'Winter Semester '.$year.'/'.$helpyear;
			}

			$sqlStudent = new student();

			echo "   <semesterKurzbz>Semester $start</semesterKurzbz>";

			// alle lvs im semester holen
			// Ohne LVs an denen ein Auslandssemester haengt. Diese werden spaeter separat geholt
			$qry ="
			SELECT
				distinct(tbl_lehrveranstaltung.lehrveranstaltung_id),
				tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.kurzbz,
				tbl_lehrveranstaltung.bezeichnung, COALESCE(tbl_lehrveranstaltung.bezeichnung_english,
				tbl_lehrveranstaltung.bezeichnung) as bezeichnung_english, tbl_lehrveranstaltung.semester,
				tbl_lehrveranstaltung.semesterstunden, tbl_lehrveranstaltung.ects, zeugnis.studiensemester_kurzbz,
				zeugnis.note, note.bezeichnung note_bezeichnung, note.anmerkung, note.offiziell, note.positiv, sort, tbl_lehrveranstaltung.sws
			FROM
				lehre.tbl_zeugnisnote zeugnis
				JOIN lehre.tbl_note note USING(note)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				student_uid = ".$db->db_add_param($uid_arr[$i])."
				AND zeugnis = true
				AND studiensemester_kurzbz in (".$sqlStudent->implode4SQL($aktuellesSemester).")
				AND NOT EXISTS(SELECT 1 FROM bis.tbl_bisio JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
					AND student_uid=".$db->db_add_param($uid_arr[$i])."
					AND tbl_lehreinheit.studiensemester_kurzbz in(".$sqlStudent->implode4SQL($aktuellesSemester)."))
			ORDER BY sort, tbl_lehrveranstaltung.bezeichnung;";

			$arrayLvAusbildungssemester= array();

			$j = 0;
			$wochen = 15;
			if($result_stud = $db->db_query($qry))
			{
				while($row_stud = $db->db_fetch_object($result_stud))
				{
					// wenn es lv noch nicht gibt dann hinzufügen
					if(!isset($arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]))
					{
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['lehrveranstaltung_id'] = $row_stud->lehrveranstaltung_id;
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['studiengang_kz'] = $row_stud->studiengang_kz;
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['kurzbz'] = $row_stud->kurzbz;
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['bezeichnung'] = $row_stud->bezeichnung;
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['bezeichnung_english'] = $row_stud->bezeichnung_english;
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['semester'] = $row_stud->semester;
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['semesterstunden'] = $row_stud->semesterstunden;
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['ects'] = $row_stud->ects;
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['sws_lv'] = $row_stud->sws;
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['studiensemester_kurzbz'] = $row_stud->studiensemester_kurzbz;
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['note'] = $db->db_parse_bool($row_stud->offiziell) ?  $row_stud->anmerkung : "";
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['note_positiv'] = $db->db_parse_bool($row_stud->positiv);
						$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['sort'] = $row_stud->sort;
						$ects_total += $row_stud->ects;
						$semester_ects +=$row_stud->ects;
						if ($arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['note_positiv'] === true)
						{
							$ects_total_positiv += $row_stud->ects;
							$semester_ects_positiv += $row_stud->ects;
						}
					}
					else
					{
						$note_alt = $arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['note'];
						$note_alt_positiv = $arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['note_positiv'];
						$note_neu = $row_stud->anmerkung;

						// alte oder neue note besser
						if(checkNote($note_alt, $note_neu))
						{
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['lehrveranstaltung_id'] = $row_stud->lehrveranstaltung_id;
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['studiengang_kz'] = $row_stud->studiengang_kz;
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['kurzbz'] = $row_stud->kurzbz;
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['bezeichnung'] = $row_stud->bezeichnung;
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['bezeichnung_english'] = $row_stud->bezeichnung_english;
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['semester'] = $row_stud->semester;
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['semesterstunden'] = $row_stud->semesterstunden;
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['ects'] = $row_stud->ects;
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['sws_lv'] = $row_stud->sws;
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['studiensemester_kurzbz'] = $row_stud->studiensemester_kurzbz;
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['note'] = $db->db_parse_bool($row_stud->offiziell) ?  $row_stud->anmerkung : "";
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['note_positiv'] = $db->db_parse_bool($row_stud->positiv);
							$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['sort'] = $row_stud->sort;

							// ects dazuzählen wenn alte Note negativ, neue positiv
							if ($arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['note_positiv'] === true && $note_alt_positiv !== true)
							{
								$ects_total_positiv += $row_stud->ects;
								$semester_ects_positiv += $row_stud->ects;
							}
						}
					}

					$test = false;
					$qry_sws = "
					SELECT
						wochen
					FROM
						public.tbl_semesterwochen
					WHERE
						studiengang_kz = ".$db->db_add_param($row_stud->studiengang_kz)."
						AND semester = ".$db->db_add_param($row_stud->semester).";";

					if($result_sws = $db->db_query($qry_sws))
					{
						if($row_sws = $db->db_fetch_object($result_sws))
						{
							$wochen = $row_sws->wochen;
						}
					}
					$ssp = $row_stud->semesterstunden / $wochen;
					$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['sws']= $ssp;

					$qry_lehrform = "
						SELECT
							distinct(lehrform_kurzbz)
						FROM
					 		lehre.tbl_lehrveranstaltung
						WHERE
							lehrveranstaltung_id = ".$db->db_add_param($row_stud->lehrveranstaltung_id)."
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
					$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['lehrform_kurzbz']= $lehrform_kurzbz;

					//  hole benotungsdatum von aktueller LV
					$qry_benotung = "
						SELECT
							benotungsdatum
						FROM
							lehre.tbl_zeugnisnote
						WHERE
							lehrveranstaltung_id = ".$db->db_add_param($row_stud->lehrveranstaltung_id)."
							AND studiensemester_kurzbz = ".$db->db_add_param($row_stud->studiensemester_kurzbz)."
							AND student_uid = ".$db->db_add_param($uid_arr[$i]).";";
					if($result_benotung = $db->db_query($qry_benotung))
					{
						if($row_benotung = $db->db_fetch_object($result_benotung))
						{
							$benotungsdatum = $row_benotung->benotungsdatum;
						}
					}

					$datum = new datum();
					$benotungsdatum = $datum->formatDatum($benotungsdatum,'d/m/Y');
					$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['benotungsdatum']= $benotungsdatum;

					$bezeichnung_englisch = $row_stud->bezeichnung_english;
					$bezeichnung = $row_stud->bezeichnung;

					// Check ob Lehrveranstaltung ein Praktikum mit eingetragener Firma besitzt
					$qry = "
					SELECT
						tbl_firma.name, lehrveranstaltung_id, firma_id
					FROM
						lehre.tbl_projektarbeit
						JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
						JOIN public.tbl_firma USING(firma_id)
					WHERE
						student_uid=".$db->db_add_param($uid_arr[$i])."
						AND projekttyp_kurzbz in('Praktikum', 'Praxis')
						AND tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($row_stud->lehrveranstaltung_id)."
					ORDER BY beginn ASC, projektarbeit_id ASC;";

					if($result_praktikum = $db->db_query($qry))
					{
						if($row_praktikum = $db->db_fetch_object($result_praktikum))
						{
							$bezeichnung.= ' absolviert in: '.$row_praktikum->name;
							$bezeichnung_englisch .= ' at: '.$row_praktikum->name;
						}
					}

					// Check ob an Lehrveranstaltung eine Thesis hängt
					// Aber kein Auslandssemester war, sonst wirds spaeter hinzugefügt
					$qry = "
						SELECT
							lehrveranstaltung_id, titel, themenbereich, note, titel_english
						FROM
							lehre.tbl_projektarbeit
							JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
						WHERE
							student_uid=".$db->db_add_param($uid_arr[$i])."
							AND projekttyp_kurzbz in('Bachelor', 'Diplom')
							AND lehrveranstaltung_id=".$db->db_add_param($row_stud->lehrveranstaltung_id)."
							AND NOT EXISTS(SELECT 1
								FROM bis.tbl_bisio
								JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
								WHERE lehrveranstaltung_id=".$db->db_add_param($row_stud->lehrveranstaltung_id)."
								AND student_uid=".$db->db_add_param($uid_arr[$i]).")
						ORDER BY beginn DESC, projektarbeit_id DESC LIMIT 1;";

					if($result_thesis = $db->db_query($qry))
					{
						while($row_thesis = $db->db_fetch_object($result_thesis))
						{
							$bezeichnung.= ": \"".$row_thesis->titel."\"";
							$bezeichnung_englisch.= ": \"".$row_thesis->titel."\"";
						}
					}

					$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['bezeichnung']= $bezeichnung;
					$arrayLvAusbildungssemester[$row_stud->lehrveranstaltung_id]['bezeichnung_englisch']=$bezeichnung_englisch;

					$note_eintragen = new note();
					$note_eintragen->load($row_stud->note);

					$test = false;
				}
			}

			foreach($arrayLvAusbildungssemester as $lv_test)
			{
				$sws = number_format(sprintf('%.1F',$lv_test['sws']),2);
				$sws_lv = number_format(sprintf('%.1F',$lv_test['sws_lv']),2);

				if($sws == '0.0')
					$sws = '';
				if($sws_lv == '0.0')
					$sws_lv = '';

				echo '<lv>
						<lehrform_kurzbz>'.$lv_test['lehrform_kurzbz'].'</lehrform_kurzbz>
						<benotungsdatum>'.$lv_test['benotungsdatum'].'</benotungsdatum>
						<sws>'.$sws.'</sws>
						<sws_lv>'.$sws_lv.'</sws_lv>
						<semester>'.$lv_test['semester'].'</semester>
						<kurzbz>'.$lv_test['kurzbz'].'</kurzbz>
						<stsem>'.$lv_test['studiensemester_kurzbz'].'</stsem>
						<bezeichnung><![CDATA['.$lv_test['bezeichnung'].']]></bezeichnung>
						<bezeichnung_englisch><![CDATA['.$lv_test['bezeichnung_englisch'].']]></bezeichnung_englisch>
						<ects>'.$lv_test['ects'].'</ects>
						<semesterstunden>'.$lv_test['semesterstunden'].'</semesterstunden>
						<note>'.$lv_test['note'].'</note>
						<note_positiv>'.$lv_test['note_positiv'].'</note_positiv>
						<lv_id>'.$lv_test['lehrveranstaltung_id'].'</lv_id>
					</lv>';
			}

			// Ist er Outgoing in diesem semester
			$qry_outgoing = "
					SELECT
						studiensemester_kurzbz, ort, ects, semesterstunden, von, bis,
						universitaet, lehrveranstaltung_id, tbl_lehrveranstaltung.sws,
						(SELECT titel_english FROM lehre.tbl_projektarbeit
						WHERE lehreinheit_id=tbl_bisio.lehreinheit_id
						AND student_uid = ".$db->db_add_param($uid_arr[$i])." limit 1) as projektarbeitstitel
					FROM
						bis.tbl_bisio
						JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
						JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
					WHERE
						student_uid = ".$db->db_add_param($uid_arr[$i]);

			if($result_outgoing = $db->db_query($qry_outgoing))
			{
				if($row_outgoing = $db->db_fetch_object($result_outgoing))
				{
					// Outgoing eintrag ist vorhanden
					if(in_array($row_outgoing->studiensemester_kurzbz, $aktuellesSemester))
					{
						$note_outgoing = 'ar';
						$benotungsdatum_outgoing = '';
						$lehrform_kurzbz_outgoing = '';

						$qry_outgoing_note = "
								SELECT
									tbl_note.anmerkung, tbl_note.offiziell,
									tbl_note.positiv, tbl_zeugnisnote.benotungsdatum,
									tbl_lehrveranstaltung.lehrform_kurzbz
								FROM
									lehre.tbl_zeugnisnote
									JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id)
									JOIN lehre.tbl_note using(note)
								WHERE
									lehrveranstaltung_id = ".$db->db_add_param($row_outgoing->lehrveranstaltung_id)."
									AND student_uid = ".$db->db_add_param($uid_arr[$i]);

						if($result_outgoing_note = $db->db_query($qry_outgoing_note))
						{
							if($row_outgoing_note = $db->db_fetch_object($result_outgoing_note))
							{
								$note_outgoing = $db->db_parse_bool($row_outgoing_note->offiziell) ? $row_outgoing_note->anmerkung : "";
								$note_positiv_outgoing = $db->db_parse_bool($row_outgoing_note->positiv);
								$benotungsdatum_outgoing = $datum->formatDatum($row_outgoing_note->benotungsdatum,'d/m/Y');
								$lehrform_kurzbz_outgoing = $row_outgoing_note->lehrform_kurzbz;
							}
						}

						$datum = new datum();
						$datum_von = $datum->formatDatum($row_outgoing->von, 'Y.m.d');
						$datum_bis = $datum->formatDatum($row_outgoing->bis, 'Y.m.d');
						$auslandssemester_start = 'th'; //Zur englischen Nummerierung der Semester (1st, 2nd, 3rd, 4th, ...)

						$sws = number_format(sprintf('%.1F',($row_outgoing->semesterstunden/$wochen)),2);
						if($sws == '0.0')
							$sws = '';

						$sws_lv = number_format(sprintf('%.1F',($row_outgoing->sws)),2);
						if($sws_lv == '0.0')
							$sws_lv = '';

						switch ($start)
						{
							case '1':
								$auslandssemester_start = 'st';
								break;
							case '2':
								$auslandssemester_start = 'nd';
								break;
							case '3':
								$auslandssemester_start = 'rd';
								break;
							default:
								$auslandssemester_start = 'th';
								break;
						}

						if($row_outgoing->projektarbeitstitel != '')
						{
							$projektarbeitszusatz = 'Thesis: "'.$row_outgoing->projektarbeitstitel.'"';
						}
						else
							$projektarbeitszusatz = '';

						echo '<lv>
								<lehrform_kurzbz></lehrform_kurzbz>
								<benotungsdatum>'.$benotungsdatum_outgoing.'</benotungsdatum>
								<sws>'.$sws.'</sws>
								<sws_lv>'.$sws_lv.'</sws_lv>
								<semester></semester>
								<kurzbz>'.$lehrform_kurzbz_outgoing.'</kurzbz>
								<stsem></stsem>
								<bezeichnung><![CDATA[]]></bezeichnung>
								<bezeichnung_englisch><![CDATA[International Semester Abroad: '.$datum_von.'-'.$datum_bis.', at '.$row_outgoing->ort.', '.$row_outgoing->universitaet.'. All credits earned during the International Semester Abroad (ISA) are fully credited for the '.$start.$auslandssemester_start.' semester at the UAS Technikum Wien. '.$projektarbeitszusatz.']]></bezeichnung_englisch>
								<ects>'.$row_outgoing->ects.'</ects>
								<semesterstunden>'.$row_outgoing->semesterstunden.'</semesterstunden>
								<note>'.$note_outgoing.'</note>
								<note_positiv>'.$note_positiv_outgoing.'</note_positiv>
								<lv_id></lv_id>
							</lv>';

						$ects_total +=$row_outgoing->ects;
						$semester_ects+=$row_outgoing->ects;
						if ($note_positiv_outgoing === true)
						{
							$ects_total_positiv += $row_outgoing->ects;
							$semester_ects_positiv += $row_outgoing->ects;
						}
					}
				}
			}
			echo '<ects_gesamt>'.$semester_ects.'</ects_gesamt>';
			echo '<ects_gesamt_positiv>'.$semester_ects_positiv.'</ects_gesamt_positiv>';
			echo "</semesters>";
		}
		echo "</studiensemester>";
		echo " <ects_total>$ects_total</ects_total>";
		echo " <ects_total_positiv>$ects_total_positiv</ects_total_positiv>";
		echo '	</supplement>';
	}
}
echo "</supplements>";

// die beiden noten werden verglichen und die mit höherer priorität(niedrigerer index) wird genommen
// return true wenn neue note genommen werden soll
function checkNote($note_alt, $note_neu)
{
	$priority_neu = 9999;
	$priority_alt = 9999;
	$arrayNotenPriority = array(
		'0' => '1',
		'1' => '2',
		'2' => '3',
		'3' => '4',
		'4' => 'ea',
		'5' => 'tg',
		'6' => 'met',
		'7' => 'ar',
		'8' => 'nb',
		'9' => '5',
		'10' => 'nea');

	for($i = 0; $i<=9; $i++)
	{
		if($note_alt == $arrayNotenPriority[$i])
			$priority_alt = $i;

		if($note_neu == $arrayNotenPriority[$i])
			$priority_neu = $i;
	}

	if($priority_neu <= $priority_alt)
		return true;
	else
		return false;
}

?>
