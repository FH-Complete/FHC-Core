<?php
/* Copyright (C) 2014 fhcomplete.org
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
//require_once('../include/functions.inc.php');
require_once('../include/zeugnisnote.class.php');
require_once('../include/datum.class.php');
require_once('../include/note.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/studienplan.class.php');
require_once('../include/student.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/organisationseinheit.class.php');
require_once('../include/anrechnung.class.php');
require_once('../include/lehrform.class.php');

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
	$note->getAll($offiziell = true);
	foreach ($note->result as $n){
		$note_arr[$n->note] = $n->anmerkung;
		$note_bezeichnung_arr[$n->note] = $n->bezeichnung;

	}
	if(isset($_GET['ss']))
		$studiensemester_kurzbz = $_GET['ss'];
	else
		die('Studiensemester muss uebergeben werden');

	if(isset($_GET['lvid']))
		$lehrveranstaltung_id = $_GET['lvid'];
	else
		$lehrveranstaltung_id = 0;

	//Daten holen

	$lqry = "SELECT
				tbl_person.titelpre, tbl_person.vorname, tbl_person.nachname, tbl_person.titelpost
			FROM
				public.tbl_benutzer JOIN public.tbl_person using (person_id)
			WHERE
				tbl_benutzer.uid = (SELECT
										tbl_lehreinheitmitarbeiter.mitarbeiter_uid
									FROM
										lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehrfunktion USING(lehrfunktion_kurzbz),
										lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
									WHERE
										tbl_lehreinheitmitarbeiter.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
										tbl_lehrveranstaltung.lehrveranstaltung_id = ".$db->db_add_param($lehrveranstaltung_id)." AND
										tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
									ORDER BY tbl_lehrfunktion.standardfaktor desc limit 1)";

	$leiter_titel = '';
	$leiter_vorname = '';
	$leiter_nachname = '';
	$leiter_titelpost = '';

	if($db->db_query($lqry))
	{
		if ($lrow = $db->db_fetch_object())
		{
			$leiter_titel = $lrow->titelpre;
			$leiter_vorname = $lrow->vorname;
			$leiter_nachname = $lrow->nachname;
			$leiter_titelpost = $lrow->titelpost;
		}
	}

	$qry = "SELECT wochen FROM public.tbl_semesterwochen
						WHERE (studiengang_kz, semester) in (SELECT studiengang_kz, semester
						FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER).")";
	$wochen = 15;
	if($result_wochen = $db->db_query($qry))
	{
		if($row_wochen = $db->db_fetch_object($result_wochen))
		{
			$wochen = $row_wochen->wochen;
		}
	}

	$lehrveranstaltung=new lehrveranstaltung();
	$lehrveranstaltung->load($lehrveranstaltung_id);
	$sws=$lehrveranstaltung->semesterstunden/$wochen;
	$ects = $lehrveranstaltung->ects;
	$lvbezeichnung = $lehrveranstaltung->bezeichnung;
	$lvstg = $lehrveranstaltung->studiengang_kz;
	$lehrform_kurzbz=$lehrveranstaltung->lehrform_kurzbz;
	$lehrform = new lehrform($lehrform_kurzbz);
	$lehrform_bezeichnung = $lehrform->bezeichnung;
	$organisationseinheit = new organisationseinheit($lehrveranstaltung->oe_kurzbz);

	$lehreinheit=new lehreinheit();
	$lehreinheit->load_lehreinheiten($lehrveranstaltung_id, $studiensemester_kurzbz);
	if(count($lehreinheit->lehreinheiten)>=1)
	{
		$lehrfach_id=$lehreinheit->lehreinheiten[0]->lehrfach_id;
	}
	else
	{
		$lehrfach_id='';
		die('keine Lehreinheiten gefunden!');
	}

	$lv_lehrfach=new lehrveranstaltung();
	$lv_lehrfach->load($lehrfach_id);
	$lehrfach_bezeichnung=$lv_lehrfach->bezeichnung;

/*	$lvqry = "SELECT * from lehre.tbl_lehrveranstaltung where lehrveranstaltung_id = ".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER);
	if($db->db_query($lvqry))
	{
		if ($lvrow = $db->db_fetch_object())
		{
			$sws = $lvrow->semesterstunden/$wochen;
			$ects = $lvrow->ects;
			$lvbezeichnung = $lvrow->bezeichnung;
			$lvstg = $lvrow->studiengang_kz;
		}
	} */

	$lehrinhalte = '';
	$infoqry = "SELECT * FROM campus.tbl_lvinfo WHERE sprache='German' AND lehrveranstaltung_id = ".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER);
	if($db->db_query($infoqry))
	{
		if ($inforow = $db->db_fetch_object())
		{
			$lehrinhalte_arr = explode("<br>",$inforow->lehrinhalte);
			for ($i = 0; $i < sizeof($lehrinhalte_arr); $i++)
			{
				$lehrinhalte .= $lehrinhalte_arr[$i].'\n';
			}
		}
	}

	$xml = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
	$xml .= "<zertifikate>";

	$studiensemester = new studiensemester();
	$studiensemester->load($studiensemester_kurzbz);

	for ($i = 0; $i < sizeof($uid_arr); $i++)
	{
		$anzahl_fussnoten=0;
		$studiengang_typ='';
		$xml_fussnote='';

		$query = "SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheit as le
			JOIN lehre.tbl_pruefung as p USING(lehreinheit_id)
			JOIN lehre.tbl_lehrveranstaltung as lv USING(lehrveranstaltung_id)
			WHERE p.student_uid = ".$db->db_add_param($uid_arr[$i])."
			AND le.studiensemester_kurzbz = ".$db->db_add_param($studiensemester_kurzbz)."
			AND lv.lehrveranstaltung_id = ".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER);

		$pruefer_uid='';
		$pruefer_name='';
		if($db->db_query($query))
		{
			if($row = $db->db_fetch_object())
			{
				$pruefer_uid=$row->mitarbeiter_uid;
			}
		}
		if($pruefer_uid!='')
		{
			$pruefer = new mitarbeiter($pruefer_uid);
			$pruefer_name = trim($pruefer->titelpre.' '.$pruefer->vorname.' '.$pruefer->nachname.' '.$pruefer->titelpost);
		}

		$query = "SELECT tbl_student.matrikelnr, tbl_student.studiengang_kz, tbl_studiengang.typ, tbl_studiengang.bezeichnung, tbl_person.vorname, tbl_person.nachname,tbl_person.gebdatum,tbl_person.titelpre, tbl_person.titelpost, tbl_person.geschlecht, tbl_person.matr_nr FROM tbl_person, tbl_student, tbl_studiengang, tbl_benutzer WHERE tbl_student.studiengang_kz = tbl_studiengang.studiengang_kz and tbl_student.student_uid = tbl_benutzer.uid and tbl_benutzer.person_id = tbl_person.person_id and tbl_student.student_uid = '".$uid_arr[$i]."'";

		if($db->db_query($query))
		{
				if(!$row = $db->db_fetch_object())
					die('Student not found');
		}
		else
			die('Student not found');
		$stg_oe_obj = new studiengang($row->studiengang_kz);
		$stgleiter = $stg_oe_obj->getLeitung($row->studiengang_kz);
		$stgl='';
		foreach ($stgleiter as $stgleiter_uid)
		{
			$stgl_ma = new mitarbeiter($stgleiter_uid);
			$stgl .= trim($stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
		}

		$student=new student();
		$student->load($uid_arr[$i]);
		$prestudent=new prestudent();
		$prestudent->getPrestudentRolle($student->prestudent_id);
		$studienplan_bezeichnung='';
		foreach($prestudent->result as $status)
		{
			if($status->studienplan_bezeichnung != '')
			    $studienplan_bezeichnung=$status->studienplan_bezeichnung;

			if($status->studienplan_id != NULL)
			    $studienplan_id = $status->studienplan_id;
		}

		$xml .= "\n	<zertifikat>";
		$xml .= "\n		<studiensemester>".$studiensemester_kurzbz."</studiensemester>";
		$xml .= "\n		<vorname>".$row->vorname."</vorname>";
		$xml .= "\n		<nachname>".$row->nachname."</nachname>";
		$xml .= "\n		<name>".trim($row->titelpre.' '.$row->vorname.' '.mb_strtoupper($row->nachname).($row->titelpost!=''?', '.$row->titelpost:''))."</name>";
		$gebdatum = date('d.m.Y',strtotime($row->gebdatum));
		$xml .= "\n		<gebdatum>".$gebdatum."</gebdatum>";
		$xml .= "\n		<geschlecht>".$row->geschlecht."</geschlecht>";
		$xml .= "\n		<matrikelnr>".$row->matrikelnr."</matrikelnr>";
        $xml .= "\n		<matr_nr>".$row->matr_nr."</matr_nr>";
		$xml .= "\n		<studiengangsleiter>".$stgl."</studiengangsleiter>";
		$datum_aktuell = date('d.m.Y');
		$xml .= "\n		<ort_datum>Wien, am ".$datum_aktuell."</ort_datum>";


		$obj = new zeugnisnote();
		$obj->load($lehrveranstaltung_id, $uid_arr[$i], $studiensemester_kurzbz);

		if ($obj->note && isset($note_arr[$obj->note]))
		{
			$note = $note_arr[$obj->note];
			$note_bezeichnung = $note_bezeichnung_arr[$obj->note];
			$uebernahmedatum = $obj->uebernahmedatum;
			$benotungsdatum = $obj->benotungsdatum;
		}
		else
		{
			$note = "";
			$note_bezeichnung = "";
			$uebernahmedatum = "";
			$benotungsdatum = "";
		}

		$stg = new studiengang();
		$stg->load($lvstg);

                $xml .= "				<stg_studiengang_bezeichnung>".$stg_oe_obj->bezeichnung."</stg_studiengang_bezeichnung>";
		$xml .= "				<lv_studiengang_bezeichnung>".$stg->bezeichnung."</lv_studiengang_bezeichnung>";
		$xml .= "				<lv_studiengang_typ>".$stg->typ."</lv_studiengang_typ>";
		$xml .= "				<lv_studiengang_kennzahl>".sprintf('%04s',$lvstg)."</lv_studiengang_kennzahl>";

		$xml .= "				<studienplan><![CDATA[".$studienplan_bezeichnung."]]></studienplan>";
		$xml .= "				<bezeichnung><![CDATA[".$lvbezeichnung."]]></bezeichnung>";
		$xml .= "				<lehrfach_bezeichnung><![CDATA[".$lehrfach_bezeichnung."]]></lehrfach_bezeichnung>";
		$xml .= "				<note>".$note."</note>";
		$xml .= "				<note_bezeichnung>".$note_bezeichnung."</note_bezeichnung>";
		$xml .= "				<pruefer>".$pruefer_name."</pruefer>";
		$xml .= "				<benotungsdatum>".$datum->formatDatum($benotungsdatum,'d.m.Y')."</benotungsdatum>";
		$xml .= "				<uebernahmedatum>".$datum->formatDatum($uebernahmedatum,'d.m.Y')."</uebernahmedatum>";
		$xml .= "				<lehrform_kurzbz>".$lehrform_kurzbz."</lehrform_kurzbz>";
		$xml .= "				<lehrform_bezeichnung>".$lehrform_bezeichnung."</lehrform_bezeichnung>";
		$xml .= "				<sws>".($sws==0?'':number_format(sprintf('%.1F',$sws),1))."</sws>";

		$xml .= "				<lvleiter>".$leiter_titel." ".$leiter_vorname." ".$leiter_nachname.($leiter_titelpost!=''?', '.$leiter_titelpost:'')."</lvleiter>";
		$xml .= "				<lehrinhalte><![CDATA[".clearHtmlTags($lehrinhalte)."]]></lehrinhalte>";
		$xml .= "				<kompatible_lvs>";

		$lehrveranstaltung->getLVkompatibel($lehrveranstaltung_id);
		foreach($lehrveranstaltung->lehrveranstaltungen as $lv_kompatibel)
		{
		    $xml .= "<lv><![CDATA[".$lv_kompatibel->bezeichnung."]]></lv>";
		}

		$xml .= "	</kompatible_lvs>";


		$anrechnung = new anrechnung();
		$anrechnung->getAnrechnungPrestudent($student->prestudent_id, null, $lehrveranstaltung_id);

		$xml .= "<studienverpflichtung>";
		$lehrveranstaltung_id_kompatibel = "";
		if(count($anrechnung->result) === 1)
		{
		    $lehrveranstaltung_id_kompatibel = $anrechnung->result[0]->lehrveranstaltung_id;
		    $xml .= $anrechnung->result[0]->lehrveranstaltung_bez;
		}
		$xml .= "</studienverpflichtung>";

                if($lehrveranstaltung_id_kompatibel != "")
                {
                    $lv = new lehrveranstaltung($lehrveranstaltung_id_kompatibel);
                    if(($lv->ects !== $ects) && ($lv->ects != "") && ($lv->ects != null))
                    {
                        $ects = $lv->ects;
                    }
                }

                $xml .= "				<ects>".number_format($ects,1)."</ects>";

		$lehrveranstaltung->loadLehrveranstaltungStudienplan($studienplan_id);

		$studienplan_lehrveranstaltung_id = "";
		foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
		{
		    if(($lv->lehrveranstaltung_id == $lehrveranstaltung_id) || ($lv->lehrveranstaltung_id == $lehrveranstaltung_id_kompatibel))
		    {
			$studienplan_lehrveranstaltung_id = $lv->studienplan_lehrveranstaltung_id;
			break;
		    }
		}

		$studienplan = new studienplan();
		if($studienplan_lehrveranstaltung_id != "")
		{
		    $studienplan->loadStudienplanLehrveranstaltung($studienplan_lehrveranstaltung_id);
		    $lv = new lehrveranstaltung();
		    while(($lv->lehrtyp_kurzbz != "modul") && ($studienplan->lehrveranstaltung_id != $studienplan_lehrveranstaltung_id))
		    {
				$lv->load($studienplan->lehrveranstaltung_id);
				$studienplan_lehrveranstaltung_id = $studienplan->lehrveranstaltung_id;
				$studienplan->loadStudienplanLehrveranstaltung($studienplan->studienplan_lehrveranstaltung_id_parent);
		    }
		    $lehrveranstaltung->lehrveranstaltungen = array(0 => $lv);
		}
		else
		{
		    $lehrveranstaltung->lehrveranstaltungen = array();
		}

//		$return = $lehrveranstaltung->getLVFromStudienplanByLehrtyp($studienplan_id, "modul");

		$xml .= "	<module>";

		//Variable wird zur korrekten Darstellung im Dokument benötigt
		$count=0;
		foreach($lehrveranstaltung->lehrveranstaltungen as $modul)
		{
		    $xml .= "<modul>";
			$xml.= "<modul_count>".$count."</modul_count>";
			$xml.= "<modul_id>".$modul->lehrveranstaltung_id."</modul_id>";
			$xml.= "<modul_bezeichnung>".$modul->bezeichnung."</modul_bezeichnung>";
		    $xml .= "</modul>";
		    $count++;
		}
		$xml .= "	</module>";
		$xml .= "<oe>";
		$xml .= "<oe_typ>".$organisationseinheit->organisationseinheittyp_kurzbz."</oe_typ>";
		$xml .= "<oe_bezeichnung>".$organisationseinheit->bezeichnung."</oe_bezeichnung>";
		$xml .= "</oe>";
		$xml .= "	</zertifikat>";
//		var_dump($lehrveranstaltung->errormsg);
	}
	$xml .= "</zertifikate>";
	echo $xml;
}
?>
