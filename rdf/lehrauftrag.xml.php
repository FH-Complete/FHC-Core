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
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/fachbereich.class.php');

// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

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

//String der laenger als limit ist wird
//abgeschnitten und '...' angehaengt
function CutString($strVal, $limit)
{
	if(strlen($strVal) > $limit+3)
		return substr($strVal, 0, $limit) . "...";
	else
		return $strVal;
}

// GENERATE XML
$xml = '<?xml version="1.0" encoding="ISO-8859-15" ?><lehrauftraege>';
$stg_arr = array();
$studiengang = new studiengang($conn);
$studiengang->getAll();

foreach ($studiengang->result as $row)
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;

//Studiengang laden
$studiengang = new studiengang($conn, $studiengang_kz);

//Fachbereiche laden
$fb_arr = array();
	$fachbereich_obj = new fachbereich($conn);
	$fachbereich_obj->getAll();
	foreach ($fachbereich_obj->result as $fb)
		$fb_arr[$fb->fachbereich_kurzbz] = $fb->bezeichnung;
		
//Studiengangsleiter holen
$stgl='';
$qry = "SELECT titelpre, vorname, nachname, titelpost FROM public.tbl_benutzerfunktion, public.tbl_person, public.tbl_benutzer WHERE
		funktion_kurzbz='stgl' AND studiengang_kz='".addslashes($studiengang_kz)."'
		AND tbl_benutzerfunktion.uid=tbl_benutzer.uid AND tbl_benutzer.person_id=tbl_person.person_id";
if($result = pg_query($conn, $qry))
{
	if($row = pg_fetch_object($result))
	{
		$stgl = trim($row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost);
	}
}

if($uid==null)
{
	$qry = "SELECT distinct tbl_lehreinheitmitarbeiter.mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung WHERE
			tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
			tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
			tbl_lehrveranstaltung.studiengang_kz='".addslashes($studiengang_kz)."' AND
			tbl_lehreinheit.studiensemester_kurzbz='".addslashes($ss)."'";
	
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
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
	global $conn;
	global $stgl;
	
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
	$qry = "SELECT * FROM campus.vw_mitarbeiter LEFT JOIN public.tbl_adresse USING(person_id) WHERE uid='".addslashes($uid)."' ORDER BY zustelladresse LIMIT 1";
	
	if($result = pg_query($conn, $qry))
	{
		if($row = pg_fetch_object($result))
		{
			$xml.='
		<mitarbeiter>
			<titelpre>'.$row->titelpre.'</titelpre>
			<vorname>'.$row->vorname.'</vorname>
			<familienname>'.$row->nachname.'</familienname>
			<titelpost>'.$row->titelpost.'</titelpost>
			<anschrift>'.$row->strasse.'</anschrift>
			<name_gesamt>'.trim($row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost).'</name_gesamt>
			<plz>'.$row->plz.'</plz>
			<ort>'.$row->ort.'</ort>
			<svnr>'.$row->svnr.'</svnr>
			<personalnummer>'.$row->personalnummer.'</personalnummer>
		</mitarbeiter>';
		}
	}
	
	//Lehreinheiten
	$qry = "SELECT * FROM campus.vw_lehreinheit WHERE mitarbeiter_uid='".addslashes($uid)."' AND studiensemester_kurzbz='$ss'";

	if($studiengang_kz!='0' && $studiengang_kz!='')
		$qry .= "AND lv_studiengang_kz='".addslashes($studiengang_kz)."'";
	$qry.=" ORDER BY lehreinheit_id";
	
	if($result = pg_query($conn, $qry))
	{
		$last_le='';
		$gesamtkosten = 0;
		$gesamtstunden = 0;
		$gruppen = array();
		$grp='';
		while($row = pg_fetch_object($result))
		{
			if($last_le!=$row->lehreinheit_id && $last_le!='')
			{
				array_unique($gruppen);
				foreach ($gruppen as $gruppe)
					$grp.=$gruppe.' ';
	$xml.='
		<lehreinheit>
			<lehreinheit_id>'.$lehreinheit_id.'</lehreinheit_id>
			<lehrveranstaltung><![CDATA['.$lehrveranstaltung.']]></lehrveranstaltung>
			<fachbereich>'.$fb_arr[$fachbereich].'</fachbereich>
			<gruppe>'.trim($grp).'</gruppe>
			<stunden>'.$stunden.'</stunden>
			<satz>'.$satz.'</satz>
			<faktor>'.$faktor.'</faktor>
			<brutto>'.number_format($brutto,2,',','.').'</brutto>
		</lehreinheit>';
	
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
			$lehrveranstaltung = CutString($row->lv_bezeichnung,30).' '.$row->lehrform_kurzbz.' '.$row->semester.'. Semester';
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
		
			if(isset($brutto))
				$gesamtkosten = $gesamtkosten + $brutto;
			if(isset($stunden))
				$gesamtstunden = $gesamtstunden + $stunden;
		}
	}
	$qry = "SELECT tbl_projektarbeit.projektarbeit_id, tbl_projektbetreuer.faktor, tbl_projektbetreuer.stunden, tbl_projektbetreuer.stundensatz, vw_student.semester, 
	               vorname, nachname, vw_student.studiengang_kz, projekttyp_kurzbz, tbl_lehrfach.fachbereich_kurzbz
	        FROM lehre.tbl_projektbetreuer, lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehrveranstaltung, 
	               public.tbl_benutzer, lehre.tbl_projektarbeit, campus.vw_student 
	        WHERE tbl_projektbetreuer.person_id=tbl_benutzer.person_id AND tbl_benutzer.uid='$uid' AND 
	              tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND student_uid=vw_student.uid
	              AND tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND
	              tbl_lehreinheit.studiensemester_kurzbz='$ss' AND tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id ";
	if($studiengang_kz!='0' && $studiengang_kz!='')
		$qry.=" AND tbl_lehrveranstaltung.studiengang_kz='$studiengang_kz'";
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$brutto = $row->stunden*$row->stundensatz*$row->faktor;
			if($brutto!=0)
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
				$gesamtkosten = $gesamtkosten + $brutto;
				$gesamtstunden = $gesamtstunden + $row->stunden;
			}
		}
	}
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