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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/* Erstellt einen Lehrauftrag im PDF Format
 *
 * Erstellt ein XML File fuer den Lehrauftrag
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/fachbereich.class.php');
require_once('../include/mitarbeiter.class.php');


// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");

//Parameter holen
if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else
	$uid=null;
	
if(isset($_GET['stg_kz']))
	$studiengang_kz = $_GET['stg_kz'];
else
	die('Fehlerhafte Parameteruebergabe');
if(isset($_GET['ss']))
	$ss = $_GET['ss'];
else
	die('Fehlerhafte Parameteruebergabe');

$ANZAHL_ZEILEN_PRO_SEITE=25;

//String der laenger als limit ist wird
//abgeschnitten und '...' angehaengt
function CutString($strVal, $limit)
{
	if(mb_strlen($strVal) > $limit+3)
		return mb_substr($strVal, 0, $limit) . "...";
	else
		return $strVal;
}

// GENERATE XML
$xml = '<?xml version="1.0" encoding="UTF-8" ?><lehrauftraege>';
$stg_arr = array();
$studiengang = new studiengang();
$studiengang->getAll();

foreach ($studiengang->result as $row)
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;

//Studiengang laden
$studiengang = new studiengang($studiengang_kz);

//Fachbereiche laden
$fb_arr = array();
	$fachbereich_obj = new fachbereich();
	$fachbereich_obj->getAll();
	foreach ($fachbereich_obj->result as $fb)
		$fb_arr[$fb->fachbereich_kurzbz] = $fb->bezeichnung;
		
//Studiengangsleiter holen
$stgl='';
$db = new basis_db();
if($studiengang_kz!='')
{
	$studiengang_obj = new studiengang();
	$stgleiter = $studiengang_obj->getLeitung($studiengang_kz);
	
	foreach ($stgleiter as $stgleiter_uid)
	{
		$row = new mitarbeiter($stgleiter_uid);
		$stgl .= trim($row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost);
	}
}

if($uid==null)
{
	$qry = "
			SELECT 
				distinct mitarbeiter_uid 
			FROM (
					SELECT 
						tbl_lehreinheitmitarbeiter.mitarbeiter_uid 
					FROM 
						lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung 
					WHERE
						tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
						tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
						tbl_lehrveranstaltung.studiengang_kz='".addslashes($studiengang_kz)."' AND
						tbl_lehreinheit.studiensemester_kurzbz='".addslashes($ss)."'
					UNION
					SELECT
						tbl_benutzer.uid as mitarbeiter_uid
			        FROM 
			        	lehre.tbl_projektbetreuer, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung,
						public.tbl_benutzer, lehre.tbl_projektarbeit, campus.vw_student, public.tbl_mitarbeiter
			        WHERE 
			        	tbl_projektbetreuer.person_id=tbl_benutzer.person_id AND
						tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
						student_uid=vw_student.uid AND
						tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid AND
						tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND
						tbl_lehreinheit.studiensemester_kurzbz='".addslashes($ss)."' AND
						tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
						tbl_lehrveranstaltung.studiengang_kz=".addslashes($studiengang_kz)." AND
						tbl_projektbetreuer.stunden!='0'
					) as mitarbeiter ORDER BY mitarbeiter_uid";
	
	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			drawLehrauftrag($row->mitarbeiter_uid);
		}
	}
}
else 
	drawLehrauftrag($uid);
function drawLehrauftrag($uid)
{
	global $studiengang;
	global $studiengang_kz;
	global $fb_arr;
	global $stg_arr;
	global $ss;
	global $xml;
	global $stgl;
	global $ANZAHL_ZEILEN_PRO_SEITE;
	
	$xml.='<lehrauftrag>
		<studiengang>FH-';
	//Studiengang
		
	if($studiengang->typ=='d')
		$xml.= 'Diplom-';
	elseif($studiengang->typ=='m')
		$xml.= 'Master-';
	elseif($studiengang->typ=='b')
		$xml.= 'Bachelor-';
	
	$xml.= 'Studiengang '.$studiengang->bezeichnung.'</studiengang>';
	
	//Studiensemester
	if(substr($ss,0,2)=='WS')
		$studiensemester = 'Wintersemester '.substr($ss,2);
	else
		$studiensemester = 'Sommersemester '.substr($ss,2);
	$xml.="<studiensemester_kurzbz>$ss</studiensemester_kurzbz>
		<studiensemester>$studiensemester</studiensemester>";
	
	//Lektor
	$qry = "SELECT * FROM campus.vw_mitarbeiter LEFT JOIN public.tbl_adresse USING(person_id) WHERE uid='".addslashes($uid)."'
			ORDER BY zustelladresse DESC, firma_id LIMIT 1";
	$db = new basis_db();
	
	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			$firmenanschrift=false;
			if($row->firma_id!='')
			{
				$qry ="SELECT tbl_firma.name, tbl_adresse.strasse, tbl_adresse.plz, tbl_adresse.ort FROM public.tbl_firma JOIN public.tbl_adresse USING(firma_id) 
						WHERE tbl_firma.firma_id='$row->firma_id' AND person_id='$row->person_id' LIMIT 1";
				if($result_firma = $db->db_query($qry))
				{
					if($row_firma = $db->db_fetch_object($result_firma))
					{
						$name_gesamt = $row_firma->name;
						$strasse = $row_firma->strasse;
						$plz = $row_firma->plz;
						$ort = $row_firma->ort;
						$zuhanden = "zu Handen ".trim($row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost);
						$firmenanschrift=true;
					}
				}
			}
			
			if(!$firmenanschrift)
			{
				$strasse = $row->strasse;
				$plz = $row->plz;
				$ort = $row->ort;
				$name_gesamt = trim($row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost);
				$zuhanden='';
			}
			$xml.='
		<mitarbeiter>
			<titelpre>'.$row->titelpre.'</titelpre>
			<vorname>'.$row->vorname.'</vorname>
			<familienname>'.$row->nachname.'</familienname>
			<titelpost>'.$row->titelpost.'</titelpost>
			<anschrift>'.$strasse.'</anschrift>
			<name_gesamt><![CDATA['.$name_gesamt.']]></name_gesamt>
			<zuhanden>'.$zuhanden.'</zuhanden>
			<plz>'.$plz.'</plz>
			<ort>'.$ort.'</ort>
			<svnr>'.$row->svnr.'</svnr>
			<personalnummer>'.$row->personalnummer.'</personalnummer>
		</mitarbeiter>';
		}
	}
	
	//Lehreinheiten
	$qry = "SELECT * FROM campus.vw_lehreinheit WHERE mitarbeiter_uid='".addslashes($uid)."' AND studiensemester_kurzbz='$ss'";

	if($studiengang_kz!='') //$studiengang_kz!='0' && 
		$qry .= "AND lv_studiengang_kz='".addslashes($studiengang_kz)."'";
	//$qry.=" ORDER BY lehreinheit_id";
	$qry.=" ORDER BY lv_orgform_kurzbz, lv_bezeichnung, lehreinheit_id";
	$lv = array();
	$anzahl_lvs=0;
	if($result = $db->db_query($qry))
	{
		$last_le='';
		$gesamtkosten = 0;
		$gesamtstunden = 0;
		$gruppen = array();
		$grp='';
		while($row = $db->db_fetch_object($result))
		{
			if($last_le!=$row->lehreinheit_id && $last_le!='')
			{
				array_unique($gruppen);
				foreach ($gruppen as $gruppe)
					$grp.=$gruppe.' ';
					
				$lv[$anzahl_lvs]['lehreinheit_id'] = $lehreinheit_id;
				$lv[$anzahl_lvs]['lehrveranstaltung'] = $lehrveranstaltung;
				$lv[$anzahl_lvs]['fachbereich'] = (isset($fb_arr[$fachbereich])?$fb_arr[$fachbereich]:'');
				$lv[$anzahl_lvs]['gruppe'] = ($grp!=''?trim($grp):' ');
				$lv[$anzahl_lvs]['stunden'] = ($stunden!=''?$stunden:' ');
				$lv[$anzahl_lvs]['satz'] = ($satz!=''?$satz:' ');
				$lv[$anzahl_lvs]['faktor'] = ($faktor!=''?$faktor:' ');
				$lv[$anzahl_lvs]['brutto'] = number_format($brutto,2,',','.');
				$anzahl_lvs++;
				/*$xml.='
					<lehreinheit>
						<lehreinheit_id>'.$lehreinheit_id.'</lehreinheit_id>
						<lehrveranstaltung><![CDATA['.$lehrveranstaltung.']]></lehrveranstaltung>
						<fachbereich>'.$fb_arr[$fachbereich].'</fachbereich>
						<gruppe>'.trim($grp).'</gruppe>
						<stunden>'.$stunden.'</stunden>
						<satz>'.$satz.'</satz>
						<faktor>'.$faktor.'</faktor>
						<brutto>'.number_format($brutto,2,',','.').'</brutto>
					</lehreinheit>';*/
	
				$gesamtkosten = $gesamtkosten + $brutto;
				$gesamtstunden = $gesamtstunden + $stunden;
	
				$lehreinheit_id='';
				$lehrveranstaltung = '';
				$fachbereich = '';
				$gruppen= array();
				$stunden = '';
				$satz = '';
				$faktor = '';
				$brutto = '';
				$grp='';
			}
	
			$lehreinheit_id=$row->lehreinheit_id;
			$lehrveranstaltung = CutString($row->lv_bezeichnung,30).' '.$row->lehrform_kurzbz.' '.$row->lv_semester.'. Semester';
			$fachbereich = $row->fachbereich_kurzbz;
	
			if($row->gruppe_kurzbz!='')
				$gruppen[] = $row->gruppe_kurzbz;
			else
				$gruppen[] = trim($stg_arr[$row->studiengang_kz].'-'.$row->semester.$row->verband.$row->gruppe).' ';
	
			$stunden = $row->semesterstunden;
			$satz = $row->stundensatz;
			$faktor = $row->faktor;
			$brutto = $row->semesterstunden*$row->stundensatz*$row->faktor;
			$last_le=$row->lehreinheit_id;
		}
		array_unique($gruppen);
		foreach ($gruppen as $gruppe)
			$grp.=$gruppe.' ';
		if(isset($lehreinheit_id))
		{
			$lv[$anzahl_lvs]['lehreinheit_id'] = (isset($lehreinheit_id)?$lehreinheit_id:' ');
			$lv[$anzahl_lvs]['lehrveranstaltung'] = (isset($lehrveranstaltung)?$lehrveranstaltung:' ');
			$lv[$anzahl_lvs]['fachbereich'] = (isset($fachbereich)?$fb_arr[$fachbereich]:' ');
			$lv[$anzahl_lvs]['gruppe'] = ($grp!=''?trim($grp):' ');
			$lv[$anzahl_lvs]['stunden'] = (isset($stunden)?$stunden:' ');
			$lv[$anzahl_lvs]['satz'] = (isset($satz)?$satz:' ');
			$lv[$anzahl_lvs]['faktor'] = (isset($faktor)?$faktor:' ');
			$lv[$anzahl_lvs]['brutto'] = (isset($brutto)?number_format($brutto,2,',','.'):' ');
			$anzahl_lvs++;
			/*
		$xml.='
			<lehreinheit>
				<lehreinheit_id>'.(isset($lehreinheit_id)?$lehreinheit_id:'').'</lehreinheit_id>
				<lehrveranstaltung><![CDATA['.(isset($lehrveranstaltung)?$lehrveranstaltung:'').']]></lehrveranstaltung>
				<fachbereich>'.(isset($fachbereich)?$fb_arr[$fachbereich]:'').'</fachbereich>
				<gruppe>'.trim($grp).'</gruppe>
				<stunden>'.(isset($stunden)?$stunden:'').'</stunden>
				<satz>'.(isset($satz)?$satz:'').'</satz>
				<faktor>'.(isset($faktor)?$faktor:'').'</faktor>
				<brutto>'.(isset($brutto)?number_format($brutto,2,',','.'):'').'</brutto>
			</lehreinheit>';
			*/
			
			if(isset($brutto))
				$gesamtkosten = $gesamtkosten + $brutto;
			if(isset($stunden))
				$gesamtstunden = $gesamtstunden + $stunden;
		}
	}
	$qry = "SELECT tbl_projektarbeit.projektarbeit_id, tbl_projektbetreuer.faktor, tbl_projektbetreuer.stunden, tbl_projektbetreuer.stundensatz, tbl_lehrveranstaltung.semester, 
	               vorname, nachname, vw_student.studiengang_kz, projekttyp_kurzbz, tbl_fachbereich.fachbereich_kurzbz
	        FROM lehre.tbl_projektbetreuer, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach, lehre.tbl_lehrveranstaltung, public.tbl_fachbereich,
	               public.tbl_benutzer, lehre.tbl_projektarbeit, campus.vw_student 
	        WHERE tbl_projektbetreuer.person_id=tbl_benutzer.person_id AND tbl_benutzer.uid=".$db->db_add_param($uid)." AND 
	              tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND student_uid=vw_student.uid AND tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz
	              AND tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND
	              tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($ss)." AND tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id ";
	if($studiengang_kz!='')
		$qry.=" AND tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$brutto = $row->stunden*$row->stundensatz*$row->faktor;
			if($row->stunden!=0)
			{
				switch($row->projekttyp_kurzbz)
				{
					case 'Bachelor':  $kuerzel='BA'; break;
					case 'Diplom':    $kuerzel='DA'; break;
					case 'Projekt':   $kuerzel='PJ'; break;
					case 'Praktikum': $kuerzel='PX'; break;
					case 'Praxis':    $kuerzel='PX'; break;
					default:          $kuerzel='PA'; break;
				}
				
				$lv[$anzahl_lvs]['lehreinheit_id'] = (isset($row->projektarbeit_id)?$kuerzel.$row->projektarbeit_id:' ');
				$lv[$anzahl_lvs]['lehrveranstaltung'] = 'Betreuung '.$row->vorname.' '.$row->nachname.' '.$row->semester.'. Semester';
				$lv[$anzahl_lvs]['fachbereich'] = (isset($row->fachbereich_kurzbz)?$fb_arr[$row->fachbereich_kurzbz]:' ');
				$lv[$anzahl_lvs]['gruppe'] = ' ';
				$lv[$anzahl_lvs]['stunden'] = (isset($row->stunden)?number_format($row->stunden,2):' ');
				$lv[$anzahl_lvs]['satz'] = (isset($row->stundensatz)?$row->stundensatz:' ');
				$lv[$anzahl_lvs]['faktor'] = (isset($row->faktor)?$row->faktor:'');
				$lv[$anzahl_lvs]['brutto'] = (isset($brutto)?number_format($brutto,2,',','.'):' ');
				$anzahl_lvs++;
				/*
				$xml.='
					<lehreinheit>
						<lehreinheit_id>'.(isset($row->projektarbeit_id)?$kuerzel.$row->projektarbeit_id:'').'</lehreinheit_id>
						<lehrveranstaltung><![CDATA[Betreuung '.$row->vorname.' '.$row->nachname.' '.$row->semester.'. Semester]]></lehrveranstaltung>
						<fachbereich>'.(isset($row->fachbereich_kurzbz)?$fb_arr[$row->fachbereich_kurzbz]:'').'</fachbereich>
						<gruppe></gruppe>
						<stunden>'.(isset($row->stunden)?number_format($row->stunden,2):'').'</stunden>
						<satz>'.(isset($row->stundensatz)?$row->stundensatz:'').'</satz>
						<faktor>'.(isset($row->faktor)?$row->faktor:'').'</faktor>
						<brutto>'.(isset($brutto)?number_format($brutto,2,',','.'):'').'</brutto>
					</lehreinheit>';
				*/
				$gesamtkosten = $gesamtkosten + $brutto;
				$gesamtstunden = $gesamtstunden + $row->stunden;
			}
		}
	}
	
	$anz=0;
	$newsite=false;
	foreach ($lv as $lv_row) 
	{
		if($anz>$ANZAHL_ZEILEN_PRO_SEITE)
		{
			if($newsite)
				$xml.='</newsite>';
			$xml.='<newsite>';
			$newsite=true;
			$anz=0;
		}
			$xml.='
				<lehreinheit>
					<lehreinheit_id>'.$lv_row['lehreinheit_id'].'</lehreinheit_id>
					<lehrveranstaltung><![CDATA['.$lv_row['lehrveranstaltung'].']]></lehrveranstaltung>
					<fachbereich>'.$lv_row['fachbereich'].'</fachbereich>
					<gruppe>'.$lv_row['gruppe'].'</gruppe>
					<stunden>'.$lv_row['stunden'].'</stunden>
					<satz>'.$lv_row['satz'].'</satz>
					<faktor>'.$lv_row['faktor'].'</faktor>
					<brutto>'.$lv_row['brutto'].'</brutto>
				</lehreinheit>';
		$anz++;
	}
	if($newsite)
		$xml.='</newsite>';
		
	// Gesamtstunden und Gesamtkosten
	$xml.="
		<gesamtstunden>".number_format($gesamtstunden,2)."</gesamtstunden>
		<gesamtbetrag>".number_format($gesamtkosten,2,',','.')."</gesamtbetrag>";
	
	
	$xml.="
		<studiengangsleiter>$stgl</studiengangsleiter>";
	
	$xml.= '
		<datum>'.date('d.m.Y').'</datum>
	</lehrauftrag>
	';
}

// END GENERATE XML
echo $xml.'</lehrauftraege>';

?>
