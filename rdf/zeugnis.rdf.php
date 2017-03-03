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
//header("Cache-Control: no-cache");
//header("Cache-Control: post-check=0, pre-check=0",false);
//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/zeugnisnote.class.php');
require_once('../include/datum.class.php');
require_once('../include/note.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/anrechnung.class.php');

$datum = new datum();
$db = new basis_db();
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
	$note = new note();
	$note->getAll();
	foreach ($note->result as $n)
		$note_arr[$n->note] = $n->anmerkung;

	if(isset($_GET['ss']))
		$studiensemester_kurzbz = $_GET['ss'];
	else
		$studiensemester_kurzbz = $semester_aktuell;

	//Daten holen

	$xml = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
	$xml .= "<zeugnisse>";

	for ($i = 0; $i < sizeof($uid_arr); $i++)
	{
		$anzahl_fussnoten=0;
		$studiengang_typ='';
		$xml_fussnote='';
		$projektarbeit=array();

		$query = "SELECT tbl_student.matrikelnr, tbl_student.studiengang_kz, tbl_studiengang.typ, tbl_studiengang.projektarbeit_note_anzeige,
					tbl_studiengang.bezeichnung, tbl_studiengang.english, tbl_studentlehrverband.semester,
					tbl_person.vorname, tbl_person.vornamen, tbl_person.nachname,tbl_person.gebdatum,tbl_person.titelpre,
					tbl_person.titelpost, tbl_person.anrede, tbl_studiensemester.bezeichnung as sembezeichnung,
					tbl_studiensemester.studiensemester_kurzbz as stsem, tbl_student.prestudent_id, tbl_studiengang.max_semester
				FROM tbl_person, tbl_student, tbl_studiengang, tbl_benutzer, tbl_studentlehrverband, tbl_studiensemester
				WHERE tbl_student.studiengang_kz = tbl_studiengang.studiengang_kz
				AND tbl_student.student_uid = tbl_benutzer.uid AND tbl_benutzer.person_id = tbl_person.person_id
				AND tbl_student.student_uid = ".$db->db_add_param($uid_arr[$i])."
				AND tbl_studentlehrverband.student_uid=tbl_student.student_uid
				AND tbl_studiensemester.studiensemester_kurzbz = tbl_studentlehrverband.studiensemester_kurzbz
				AND tbl_studentlehrverband.studiensemester_kurzbz = ".$db->db_add_param($studiensemester_kurzbz);

		if($result = $db->db_query($query))
		{
			$xml .= "\n	<zeugnis>";
			if($row = $db->db_fetch_object($result))
			{
				$studiengang = new studiengang();
				$stgleiter = $studiengang->getLeitung($row->studiengang_kz);
				$stgl='';
				foreach ($stgleiter as $stgleiter_uid)
				{
					$stgl_ma = new mitarbeiter($stgleiter_uid);
					$stgl .= trim($stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
				}

				//Wenn das Semester 0 ist, dann wird das Semester aus der Rolle geholt. (Ausnahme: Incoming)
				//damit bei Outgoing Studenten die im 0. Semester angelegt sind das richtige Semester aufscheint
				$qry ="SELECT ausbildungssemester as semester FROM public.tbl_prestudentstatus
						WHERE
						prestudent_id=".$db->db_add_param($row->prestudent_id)." AND
						studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
						status_kurzbz not in('Incoming','Aufgenommener','Bewerber','Wartender', 'Interessent')
						ORDER BY DATUM DESC LIMIT 1";
				if($result_sem = $db->db_query($qry))
				{
					if($row_sem = $db->db_fetch_object($result_sem))
					{
						$row->semester = $row_sem->semester;
						$bezeichnung = $row_sem->semester.'. Semester';
					}
					else
						$bezeichnung = '';
				}
				else
					$bezeichnung = '';


				$xml .= "		<studiensemester>".$row->sembezeichnung."</studiensemester>";
				$xml .= "		<stsem>".$row->stsem."</stsem>";
				$xml .=	"		<semester>".$row->semester."</semester>";
				$xml .=	"		<semester_bezeichnung>".$bezeichnung."</semester_bezeichnung>";
				$xml .= "		<studiengang>".$row->bezeichnung."</studiengang>";
				$xml .= "		<studiengang_englisch>".$row->english."</studiengang_englisch>";
				if($row->typ=='b')
					$bezeichnung='Bachelor';
				elseif($row->typ=='m')
					$bezeichnung='Master';
				elseif($row->typ=='d')
					$bezeichnung='Diplom';
				else
					$bezeichnung='';
				$studiengang_typ=$row->typ;
				$semester = $row->semester;

				//Wenn Lehrgang, dann Erhalter-KZ vor die Studiengangs-Kz hängen
				if ($row->studiengang_kz<0)
				{
					$stg = new studiengang();
					$stg->load($row->studiengang_kz);

					$studiengang_kz = sprintf("%03s", $stg->erhalter_kz).sprintf("%04s", abs($row->studiengang_kz));
				}
				else
					$studiengang_kz = sprintf("%04s", abs($row->studiengang_kz));

				$xml .= "		<studiengang_art>".$bezeichnung."</studiengang_art>";
				$xml .= "		<studiengang_kz>".$studiengang_kz."</studiengang_kz>";
				$xml .= "\n		<anrede>".$row->anrede."</anrede>";
				$xml .= "\n		<vorname>".$row->vorname."</vorname>";
				$xml .= "		<nachname>".$row->nachname."</nachname>";
				$xml .= "		<name>".trim($row->titelpre.' '.trim($row->vorname.' '.$row->vornamen).' '.$row->nachname.($row->titelpost!=''?', '.$row->titelpost:''))."</name>";
				$gebdatum = date('d.m.Y',strtotime($row->gebdatum));
				$xml .= "		<gebdatum>".$gebdatum."</gebdatum>";
				$xml .= "		<matrikelnr>".trim($row->matrikelnr)."</matrikelnr>";
				$xml .= "		<studiengangsleiter>".$stgl."</studiengangsleiter>";
				$datum_aktuell = date('d.m.Y');
				$xml .= "		<ort_datum>".$datum_aktuell."</ort_datum>";
				$xml .= "		<projektarbeit_note_anzeige>".($row->projektarbeit_note_anzeige=='t'?'true':'false')."</projektarbeit_note_anzeige>";

				$qry_proj = "
					SELECT
						lehrveranstaltung_id, titel, themenbereich, note, titel_english,
						tbl_projekttyp.bezeichnung, projekttyp_kurzbz
					FROM
						lehre.tbl_projektarbeit
						JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
						JOIN lehre.tbl_projekttyp USING (projekttyp_kurzbz)
					WHERE
						student_uid=".$db->db_add_param($uid_arr[$i])."
						AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
						AND projekttyp_kurzbz in('Bachelor', 'Diplom')
					ORDER BY beginn ASC, projektarbeit_id ASC";

				if($result_proj = $db->db_query($qry_proj))
				{
					while($row_proj = $db->db_fetch_object($result_proj))
					{
						$projektarbeit[$row_proj->lehrveranstaltung_id]['titel']=$row_proj->titel;
						$projektarbeit[$row_proj->lehrveranstaltung_id]['titel_en']=$row_proj->titel_english;
						$projektarbeit[$row_proj->lehrveranstaltung_id]['themenbereich']=$row_proj->themenbereich;
						$projektarbeit[$row_proj->lehrveranstaltung_id]['note']=$row_proj->note;
						$projektarbeit[$row_proj->lehrveranstaltung_id]['projekttyp_bezeichnung']=$row_proj->bezeichnung;
						$projektarbeit[$row_proj->lehrveranstaltung_id]['projekttyp_kurzbz']=$row_proj->projekttyp_kurzbz;
					}
				}

				// Wenn es das letzte Semesterzeugnis ist, wird zusaetzlich die Abschlusspruefung geliefert
				if($row->semester==$row->max_semester)
				{

					$qry_abschlusspruefung = "SELECT
													tbl_abschlusspruefung.datum,
													tbl_abschlusspruefung.pruefungstyp_kurzbz,
													tbl_abschlussbeurteilung.bezeichnung,
													tbl_abschlussbeurteilung.bezeichnung_english
											FROM
												lehre.tbl_abschlusspruefung
												LEFT JOIN lehre.tbl_abschlussbeurteilung USING(abschlussbeurteilung_kurzbz)
											WHERE
												tbl_abschlusspruefung.student_uid=".$db->db_add_param($uid_arr[$i])."
											ORDER BY datum DESC LIMIT 1";
					if($result_abschlusspruefung = $db->db_query($qry_abschlusspruefung))
					{
						if($row_abschlusspruefung = $db->db_fetch_object($result_abschlusspruefung))
						{
							$xml .= "		<abschlusspruefung_typ>".$row_abschlusspruefung->pruefungstyp_kurzbz."</abschlusspruefung_typ>";
							$xml .= "		<abschlusspruefung_datum>".$datum->formatDatum($row_abschlusspruefung->datum,'d.m.Y')."</abschlusspruefung_datum>";
							$xml .= "		<abschlusspruefung_note>".$row_abschlusspruefung->bezeichnung."</abschlusspruefung_note>";
							$xml .= "		<abschlusspruefung_note_english>".$row_abschlusspruefung->bezeichnung_english."</abschlusspruefung_note_english>";
						}
					}
				}

				$obj = new zeugnisnote();

				$obj->getZeugnisnoten($lehrveranstaltung_id=null, $uid_arr[$i], $studiensemester_kurzbz);

				$ects_gesamt = 0;
				$prestudent_id = $row->prestudent_id;
				foreach ($obj->result as $row)
				{
					if($row->zeugnis)
					{
						if (trim($row->note)!=='')
							$note = $note_arr[$row->note];
						else
							$note = "";
						$note2=$note;

						//Firma fuer Berufspraktikum
						$qry = "SELECT tbl_firma.name
								FROM
									lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, public.tbl_firma
								WHERE
									tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
									tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
									tbl_projektarbeit.firma_id = tbl_firma.firma_id AND
									tbl_projektarbeit.student_uid=".$db->db_add_param($uid_arr[$i])." AND
									tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
									tbl_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($row->lehrveranstaltung_id);

						$firma = '';
						$firma_eng = '';
						if($result_firma = $db->db_query($qry))
						{
							if($row_firma = $db->db_fetch_object($result_firma))
							{
								if($row_firma->name!='')
								{
									$firma = " bei Firma: $row_firma->name";
									$firma_eng = " at: $row_firma->name";
								}
							}
						}

						//Bakk/Dipl Fussnoten
						if(array_key_exists($row->lehrveranstaltung_id, $projektarbeit))
						{
							$bezeichnung = $row->lehrveranstaltung_bezeichnung.$firma.' '.$fussnotenzeichen[$anzahl_fussnoten];
							$bezeichnung_englisch = $row->lehrveranstaltung_bezeichnung_english.$firma_eng.' '.$fussnotenzeichen[$anzahl_fussnoten];
							$xml_fussnote .="\n <fussnote>";
							$xml_fussnote .=" 		<fussnotenzeichen>".$fussnotenzeichen[$anzahl_fussnoten]."</fussnotenzeichen>";

							//$projektarbeit[$row->lehrveranstaltung_id]['titel'] = breaktext($projektarbeit[$row->lehrveranstaltung_id]['titel'], 40);

							$anzahl_nl = substr_count($projektarbeit[$row->lehrveranstaltung_id]['titel'],'\n');
							$nl2='';
							if($projektarbeit[$row->lehrveranstaltung_id]['themenbereich']!='')
							{
								//$xml_fussnote .="       <themenbereich_bezeichnung>Themenbereich: </themenbereich_bezeichnung>";
								$xml_fussnote .="       <themenbereich><![CDATA[".$projektarbeit[$row->lehrveranstaltung_id]['themenbereich'].']]></themenbereich>';
								$anzahl_nl++;
								$nl2='\n';
							}

							/*if($studiengang_typ=='b')
								$typ = 'Bachelorarbeit:';
							else
								$typ = 'Master Thesis:';*/

							$nl='';
							$nl2='';
							$xml_fussnote .="      <titel_bezeichnung><![CDATA[".$projektarbeit[$row->lehrveranstaltung_id]['projekttyp_bezeichnung']."]]></titel_bezeichnung>";
							$xml_fussnote .="      <titel_kurzbz><![CDATA[".$projektarbeit[$row->lehrveranstaltung_id]['projekttyp_kurzbz']."]]></titel_kurzbz>";
							$xml_fussnote .="      <titel><![CDATA[".$projektarbeit[$row->lehrveranstaltung_id]['titel'].$nl2."]]></titel>";
							$xml_fussnote .="      <titel_en><![CDATA[".$projektarbeit[$row->lehrveranstaltung_id]['titel_en'].$nl2."]]></titel_en>";
							//$note = $note_arr[$projektarbeit[$row->lehrveranstaltung_id]['note']];
							$note = $projektarbeit[$row->lehrveranstaltung_id]['note'];
							//$nl = str_repeat('\n',($anzahl_nl));
							$xml_fussnote .='      <note>'.(isset($note_arr[$note])?$note_arr[$note]:$note).$nl.'</note>';
							$xml_fussnote .='      <sws>'.$nl.'</sws>';
							$xml_fussnote .='      <ects>'.$nl.'</ects>';
							$xml_fussnote .='      <lv_lehrform_kurzbz>'.$nl.'</lv_lehrform_kurzbz>';



							$xml_fussnote .=" </fussnote>";

							$anzahl_fussnoten++;
						}
						else
						{
							$bezeichnung = $row->lehrveranstaltung_bezeichnung.$firma;
							$bezeichnung_englisch = $row->lehrveranstaltung_bezeichnung_english.$firma_eng;
						}


						$bisio_von = '';
						$bisio_bis = '';
						$bisio_ort = '';
						$bisio_universitaet = '';
						$auslandssemester=false;

						$qry = "SELECT tbl_bisio.* FROM bis.tbl_bisio JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
								WHERE tbl_lehreinheit.lehrveranstaltung_id='$row->lehrveranstaltung_id'
								AND student_uid=".$db->db_add_param($uid_arr[$i]);
						if($result_bisio = $db->db_query($qry))
						{
							if($row_bisio = $db->db_fetch_object($result_bisio))
							{
								$bisio_von = $row_bisio->von;
								$bisio_bis = $row_bisio->bis;
								$bisio_ort = $row_bisio->ort;
								$bisio_universitaet = $row_bisio->universitaet;
								$auslandssemester=true;
								$note2 = 'ar';
							}
						}

						$qry = "SELECT wochen FROM public.tbl_semesterwochen
								WHERE (studiengang_kz, semester) in (SELECT studiengang_kz, semester
								FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$db->db_add_param($row->lehrveranstaltung_id).")";
						$wochen = 15;
						if($result_wochen = $db->db_query($qry))
						{
							if($row_wochen = $db->db_fetch_object($result_wochen))
							{
								$wochen = $row_wochen->wochen;
							}
						}
						$xml .= "\n			<unterrichtsfach>";
						$xml .= "				<bezeichnung><![CDATA[".$bezeichnung."]]></bezeichnung>";
						$xml .= "				<bezeichnung_englisch><![CDATA[".$bezeichnung_englisch."]]></bezeichnung_englisch>";
						$xml .= "				<note>".$note2."</note>";
						$xml .= "				<sws>".($row->semesterstunden==0?'':number_format(sprintf('%.1F',$row->semesterstunden/$wochen),1))."</sws>";
						$ectspunkte='';

						$anrechnung = new anrechnung();
						$anrechnung->getAnrechnungPrestudent($prestudent_id, null, $row->lehrveranstaltung_id);

						if($anrechnung->result != null)
						{
							$lv = new lehrveranstaltung($anrechnung->result[0]->lehrveranstaltung_id);
							if(($lv->ects !== $row->ects) && ($lv->ects != "") && ($lv->ects != null))
							{
								$row->ects = $lv->ects;
							}
						}

						if($row->ects==0 || $row->ects=='')
							$ectspunkte='';
						else
						{
							//Bei 2 Nachkommastellen beide anzeigen, sonst nur 1
							if(number_format($row->ects,1)==number_format($row->ects,2))
								$ectspunkte=number_format($row->ects,1);
							else
								$ectspunkte=number_format($row->ects,2);
						}
						$ects_gesamt+=$ectspunkte;

						$xml .= "				<ects>".$ectspunkte."</ects>";
						$xml .= "				<lv_lehrform_kurzbz>".$row->lv_lehrform_kurzbz."</lv_lehrform_kurzbz>";
						if($auslandssemester)
						{
							$xml .= "			<bisio_von>".date('d.m.Y', $datum->mktime_fromdate($bisio_von))."</bisio_von>";
							$xml .= "			<bisio_bis>".date('d.m.Y', $datum->mktime_fromdate($bisio_bis))."</bisio_bis>";
							$xml .= "			<bisio_ort>$bisio_ort</bisio_ort>";
							$xml .= "			<bisio_universitaet>$bisio_universitaet</bisio_universitaet>";
						}
						$xml .= "			</unterrichtsfach>";
					}
				}
				$xml .= "<ects_gesamt>".$ects_gesamt."</ects_gesamt>";
				$xml .= $xml_fussnote;

			}
			else
			{
				$xml .="<name>PERSON NICHT GEFUNDEN / KEIN STATUS</name>";
			}
			$xml .= "	</zeugnis>";
		}
	}
	$xml .= "</zeugnisse>";
	echo $xml;
}
?>
