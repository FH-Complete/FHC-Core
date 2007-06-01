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

//Parameter holen
if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else
	die('Fehlerhafte Parameteruebergabe');
if(isset($_GET['stg_kz']))
	$studiengang_kz = $_GET['stg_kz'];
else
	die('Fehlerhafte Parameteruebergabe');
if(isset($_GET['ss']))
	$ss = $_GET['ss'];
else
	die('Fehlerhafte Parameteruebergabe');


// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

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
$xml = '<?xml version="1.0" encoding="ISO-8859-15" ?>
<lehrauftrag>
	<studiengang>FH-';
//Studiengang
$studiengang = new studiengang($conn, $studiengang_kz);

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
$xml.="
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
		<plz>'.$row->plz.'</plz>
		<ort>'.$row->ort.'</ort>
		<svnr>'.$row->svnr.'</svnr>
		<personalnummer>'.$row->personalnummer.'</personalnummer>
	</mitarbeiter>';
	}
}

//Lehreinheiten
$fb_arr = array();
$fachbereich_obj = new fachbereich($conn);
$fachbereich_obj->getAll();
foreach ($fachbereich_obj->result as $fb)
	$fb_arr[$fb->fachbereich_kurzbz] = $fb->bezeichnung;

$lehreinheit = new lehreinheit($conn);
$qry = "SELECT * FROM campus.vw_lehreinheit WHERE lv_studiengang_kz='".addslashes($studiengang_kz)."' AND mitarbeiter_uid='".addslashes($uid)."' AND studiensemester_kurzbz='$ss' ORDER BY lehreinheit_id";

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
		<lehrveranstaltung>'.$lehrveranstaltung.'</lehrveranstaltung>
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
			$gruppen[] = $row->semester.$row->verband.$row->gruppe.' ';

		$stunden = $row->semesterstunden;
		$satz = $row->stundensatz;
		$faktor = $row->faktor;
		$brutto = $row->semesterstunden*$row->stundensatz*$row->faktor;
		$last_le=$row->lehreinheit_id;
	}
	array_unique($gruppen);
	foreach ($gruppen as $gruppe)
		$grp.=$gruppe.' ';
$xml.='
	<lehreinheit>
		<lehreinheit_id>'.(isset($lehreinheit_id)?$lehreinheit_id:'').'</lehreinheit_id>
		<lehrveranstaltung>'.(isset($lehrveranstaltung)?$lehrveranstaltung:'').'</lehrveranstaltung>
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

// Gesamtstunden und Gesamtkosten
$xml.="
	<gesamtstunden>$gesamtstunden</gesamtstunden>
	<gesamtbetrag>".number_format($gesamtkosten,2,',','.')."</gesamtbetrag>";

//Studiengangsleiter
$qry = "SELECT titelpre, vorname, nachname, titelpost FROM public.tbl_benutzerfunktion, public.tbl_person, public.tbl_benutzer WHERE
		funktion_kurzbz='stgl' AND studiengang_kz='".addslashes($studiengang_kz)."'
		AND tbl_benutzerfunktion.uid=tbl_benutzer.uid AND tbl_benutzer.person_id=tbl_person.person_id";
if($result = pg_query($conn, $qry))
{
	if($row = pg_fetch_object($result))
	{
		$stgl = trim($row->titelpost.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost);
$xml.="
	<studiengangsleiter>$stgl</studiengangsleiter>";
	}
}

$xml.= '
	<datum>'.date('d.m.Y').'</datum>
</lehrauftrag>
';

// END GENERATE XML
echo $xml;

?>