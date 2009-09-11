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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/preinteressent.class.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/kontakt.class.php');
require_once('../../include/adresse.class.php');
require_once('../../include/nation.class.php');
require_once('../../include/firma.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$datum_obj = new datum();

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

echo '<html>
	<head>
		<title>PreInteressenten</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body class="Background_main">
	';

if(!$rechte->isBerechtigt('admin') && 
   !$rechte->isBerechtigt('preinteressent') &&
   !$rechte->isBerechtigt('assistenz'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if(isset($_GET['id']) && is_numeric($_GET['id']))
	$id = $_GET['id'];
else 
	die('der Parameter id mit der Person_id muss uebergeben werden');

$person = new person();

if(!$person->load($id))
	die('Person wurde nicht gefunden');
	
//PERSON
echo '<h2>Person</h2>';
echo "ID: $person->person_id<br>";
echo "Name: $person->titelpre $person->nachname $person->vorname $person->titelpost<br>";
echo "Geburtsdatum: ".$datum_obj->formatDatum($person->gebdatum,'d.m.Y')."<br>";
echo "Geschlecht: ".$person->geschlecht."<br>";

$kontakt = new kontakt();
$kontakt->load_pers($person->person_id);
echo '<h3>Kontaktdaten</h3>';
echo '<table class="liste table-autosort:0 table-stripeclass:alternate table-autostripe">
		<thead>
			<tr>
				<th>Typ</th>
				<th>Kontakt</th>
				<th>Zustellung</th>
				<th>Anmerkung</th>
			</tr>
		</thead>
		<tbody>';
foreach ($kontakt->result as $row)
{
	echo '<tr>';
	echo "<td>$row->kontakttyp</td>";
	echo "<td>$row->kontakt</td>";
	echo "<td>".($row->zustellung?'Ja':'Nein')."</td>";
	echo "<td>$row->anmerkung</td>";
	echo '<tr>';
}
echo '</tbody></table>';

//Nationen laden
$nation_arr = array();
$nation = new nation();
$nation->getAll();

$nation_arr['']='';
foreach($nation->nation as $row)
	$nation_arr[$row->code]=$row->kurztext;
	
$adresstyp_arr = array('h'=>'Hauptwohnsitz','n'=>'Nebenwohnsitz','f'=>'Firma');

// *** ADRESSEN ***
echo "<h3>Adressen:</h3>";
echo "<table class='liste'><tr><th>Strasse</th><th>Plz</th><th>Ort</th><th>Gemeinde</th><th>Nation</th><th>Typ</th><th>Heimat</th><th>Zustellung</th><th>Firma</th></tr>";
$adresse_obj = new adresse();
$adresse_obj->load_pers($person->person_id);


foreach ($adresse_obj->result as $row)
{
	echo '<tr class="liste1">';
	echo "<td>$row->strasse</td>";
	echo "<td>$row->plz</td>";
	echo "<td>$row->ort</td>";
	echo "<td>$row->gemeinde</td>";
	echo "<td>".$nation_arr[$row->nation]."</td>";
	echo "<td>".$adresstyp_arr[$row->typ]."</td>";
	echo "<td>".($row->heimatadresse?'Ja':'Nein')."</td>";
	echo "<td>".($row->zustelladresse?'Ja':'Nein')."</td>";
	$firma=new firma();
	if($row->firma_id!='')
		$firma->load($row->firma_id);
	echo "<td>".$firma->name."</td>";
}
echo '</table>';
//PREINTERESSENT
function CutString($strVal, $limit)
{
	if(strlen($strVal) > $limit+3)
	{
		return substr($strVal, 0, $limit) . "...";
	}
	else
	{
		return $strVal;
	}
}

$preinteressent = new preinteressent();
$preinteressent->getPreinteressenten($person->person_id);
if(count($preinteressent->result)>0)
{
	echo '<br><h2>Preinteressent</h2>';
	echo '<table class="liste table-autosort:0 table-stripeclass:alternate table-autostripe">
			<thead>
				<tr>
					<th>ID</th>
					<th>StSemester</th>
					<th>Erfassungsdatum</th>
					<th>Anmerkung</th>
					<th>AufmerksamDurch</th>
					<th>Kontaktmedium (Woher)</th>
					<th>Studiengänge</th>
					<th>Schule</th>
				</tr>
			</thead>
			<tbody>';
	foreach ($preinteressent->result as $row)
	{
		echo '<tr>';
		echo "<td>$row->preinteressent_id</td>";
		echo "<td>$row->studiensemester_kurzbz</td>";
		echo "<td>".$datum_obj->formatDatum($row->erfassungsdatum, 'd.m.Y')."</td>";
		echo "<td title='".$row->anmerkung."'>".CutString($row->anmerkung, 50)."</td>";
		echo "<td>".$row->aufmerksamdurch_kurzbz."</td>";
		echo "<td>".$row->kontaktmedium_kurzbz."</td>";
		echo '<td>';
		$preinteressent1 = new preinteressent();
		$preinteressent1->loadZuordnungen($row->preinteressent_id);
		
		$stgs='';
		foreach ($preinteressent1->result as $row_zuordnung)
		{
			if($stgs!='')
				$stgs.=', ';
			$stgs.= $studiengang->kuerzel_arr[$row_zuordnung->studiengang_kz]."(".$preinteressent1->prioritaet_arr[$row_zuordnung->prioritaet].")";
		}
		echo $stgs;
		echo '</td>';
		echo '<td>';
		$firma = new firma();
		$firma->load($row->firma_id);
		$adresse = new adresse();
		$adresse->load_firma($row->firma_id);
		if(isset($adresse->result[0]))
		{
			$plz = $adresse->result[0]->plz;
			$ort = $adresse->result[0]->ort;
		}
			
		echo '<a href="../stammdaten/firma_details.php?firma_id='.$firma->firma_id.'" class="Item">'.$plz.' '.$ort.' '.$firma->name." ($firma->firmentyp_kurzbz)</a>";
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}

$prestudent = new prestudent();
$prestudent->getPrestudenten($person->person_id);
if(count($prestudent->result)>0)
{
	echo '<br><h2>Pre-/Studenten</h2>';
	echo '<table class="liste table-autosort:0 table-stripeclass:alternate table-autostripe">
			<thead>
				<tr>
					<th>ID</th>
					<th>Studiengang</th>
					<th>Reihungstest</th>
					<th>UID</th>
					<th>Gruppe</th>
					<th>Status</th>
				</tr>
			</thead><tbody>';
	foreach ($prestudent->result as $row)
	{
		echo '<tr>';
		echo "<td>$row->prestudent_id</td>";
		echo "<td>".$studiengang->kuerzel_arr[$row->studiengang_kz]."</td>";
		echo "<td>".($row->reihungstestangetreten?'Ja':'Nein')."</td>";
		$uid='';
		$gruppe='';
		$qry ="SELECT * FROM public.tbl_student WHERE prestudent_id='$row->prestudent_id'";
		if($result = $db->db_query($qry))
		{
			if($db->db_num_rows($result)>1)
			{
				$uid='ACHTUNG: Es gibt mehrere Studenteneinträge die auf diesen Prestudenten zeigen!';
			}
			else 
			{
				if($row_std = $db->db_fetch_object($result))
				{
					$uid = $row_std->student_uid;
					$gruppe = $row_std->semester.$row_std->verband.$row_std->gruppe;
				}
			}
		}
		echo "<td>$uid</td>";
		echo "<td>$gruppe</td>";
		$prestudent1 = new prestudent();
		$prestudent1->getLastStatus($row->prestudent_id);	
		echo "<td>$prestudent1->status_kurzbz ".($prestudent1->ausbildungssemester!=''?"($prestudent1->ausbildungssemester. Semester)":'')."</td>";
		echo '</tr>';
	}
	echo '</tbody></table>';
}

$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE person_id='$person->person_id'";
if($result = $db->db_query($qry))
{
	if($db->db_num_rows($result)>0)
	{
		echo '<br><h2>Mitarbeiter</h2>';		
		echo '<table class="liste table-autosort:0 table-stripeclass:alternate table-autostripe">
			<thead>
				<tr>
					<th>UID</th>
					<th>Kurzbz</th>
					<th>Lektor</th>
					<th>Fixangestellt</th>
					<th>Telefonklappe</th>
				</tr>
			</thead><tbody>';
		while($row = $db->db_fetch_object($result))
		{
			echo "<tr>";
			echo "<td>$row->uid</td>";
			echo "<td>$row->kurzbz</td>";
			echo "<td>".($row->lektor=='t'?'Ja':'Nein')."</td>";
			echo "<td>".($row->fixangestellt=='t'?'Ja':'Nein')."</td>";
			echo "<td>$row->telefonklappe</td>";
			echo "</tr>";
		}
		echo '</tbody></table>';
	}
}
echo '</body>';
echo '</html>';
?>