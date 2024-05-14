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
require_once('../../include/preincoming.class.php');
require_once('../../include/statusgrund.class.php');

$user = get_uid();

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$datum_obj = new datum();

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

echo '<html>
	<head>
		<title>PreInteressenten</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">';
include('../../include/meta/jquery.php');
include('../../include/meta/jquery-tablesorter.php');
echo '</head>'; ?>
<script language="JavaScript" type="text/javascript">
	// Add parser through the tablesorter addParser method for sorting Studiensemester
	$.tablesorter.addParser({
		// set a unique id
		id: "studiensemester",
		is: function(s) {
			// return false so this parser is not auto detected
			return false;
		},
		format: function(s) {
			// format data for normalization
			var result = s.substr(2) + s.substr(0, 2);
			return result;
		},
		// set type, either numeric or text
		type: "text"
	});

	$(document).ready(function()
	{
		$(".tablesorter").tablesorter(
			{
				widgets: ["zebra"]
			});
		$(".tablePreStudent").tablesorter(
			{
				headers: {
					1: {
						sorter:"insertamum"
					}},
				sortList: [[1,1],[2,0],[3,0]],
				widgets: ["zebra"]
			});
		$(".tableKontakt").tablesorter(
			{
				headers:
					{
						3:
							{
								sorter: "shortDate", dateFormat: "yyyy-mm-dd"
							}
					},
				sortList: [[0,0],[2,0],[3,1]],
				widgets: ["zebra"]
			});
	});
</script>
<style>
	.inactive
	{
		color: grey;
	}
</style>
<body class="Background_main">
<?php
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

// Erste Mailadresse (jüngstes Insertdatum) auslesen, da für Bewerbungstool-Login benötigt
$kontakt = new kontakt();
$kontakt->load_pers($person->person_id);
$insertdatum = '';
$email = '';
foreach ($kontakt->result as $row)
{
	if ($row->kontakttyp == "email")
	{
		if ($insertdatum == '' || $row->insertamum > $insertdatum)
		{
			$email = $row->kontakt;
			$insertdatum = $row->insertamum;
		}
	}
	else
		continue;
}

//PERSON
echo '<h2>Person</h2>';
echo '<table cellspacing="3px">';
echo "<tr><td align='right'>ID:</td><td> $person->person_id</td></tr>";
echo "<tr><td align='right'>Name:</td><td> $person->titelpre $person->nachname $person->vorname $person->titelpost</td></tr>";
echo "<tr><td align='right'>Geburtsdatum:</td><td> ".$datum_obj->formatDatum($person->gebdatum,'d.m.Y')."</td></tr>";
echo "<tr><td align='right'>Geschlecht:</td><td> ".$person->geschlecht."</td></tr>";
echo "<tr valign='top'><td align='right'>Anmerkung:</td><td width='800px'> ".$db->convert_html_chars($person->anmerkungen)."</td></tr>";
echo "<tr valign='top'><td align='right'>Zugangscode:</td><td width='800px'>".(in_array('bewerbung', (explode(';', ACTIVE_ADDONS)))?"<a href='".CIS_ROOT."addons/bewerbung/cis/registration.php?code=".$db->convert_html_chars($person->zugangscode)."&emailAdresse=".$email."' target='_blank'>".$db->convert_html_chars($person->zugangscode)."</a>":$db->convert_html_chars($person->zugangscode))."</td></tr>";
echo '</table>';

echo '<br><a href="../fhausweis/search.php?person_id='.$person->person_id.'">Statusinformation - FH Ausweis</a><br>';

echo '<h3>Kontaktdaten</h3>';
echo '<table class="tableKontakt">
		<thead>
			<tr>
				<th>Typ</th>
				<th>Kontakt</th>
				<th>Zustellung</th>
				<th>Eingefügt am</th>
				<th>Anmerkung</th>
			</tr>
		</thead>
		<tbody>';
foreach ($kontakt->result as $row)
{
	echo '<tr>';
	echo "<td>$row->kontakttyp</td>";
	echo '<td>'.($row->kontakttyp == "email" ? '<a href="mailto:'.$row->kontakt.'">'.$row->kontakt.'</a>' : $row->kontakt).'</td>';
	echo "<td>".($row->zustellung?'Ja':'Nein')."</td>";
	echo "<td>".$datum_obj->formatDatum($row->insertamum, 'Y-m-d H:i:s')."</td>";
	echo "<td>$row->anmerkung</td>";
	echo '</tr>';
}
echo '</tbody></table>';

//Nationen laden
$nation_arr = array();
$nation = new nation();
$nation->getAll();

$nation_arr['']='';
foreach($nation->nation as $row)
	$nation_arr[$row->code]=$row->kurztext;

// *** ADRESSEN ***
echo '<h3>Adressen:</h3>';
echo '<table class="tablesorter" data-sortlist="[[7,0],[0,0]]">
		<thead>
		<tr>
			<th>Strasse</th>
			<th>Plz</th>
			<th>Ort</th>
			<th>Gemeinde</th>
			<th>Nation</th>
			<th>Typ</th>
			<th>Heimat</th>
			<th>Zustellung</th>
			<th>Firma</th>
		</tr>
		</thead>
		<tbody>';
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
	echo "<td>".$row->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE]."</td>";
	echo "<td>".($row->heimatadresse?'Ja':'Nein')."</td>";
	echo "<td>".($row->zustelladresse?'Ja':'Nein')."</td>";
	$firma=new firma();
	if($row->firma_id!='')
		$firma->load($row->firma_id);
	echo "<td>".$firma->name."</td>";
}
echo '</tbody></table>';


/* PreInteressenten deprecated*/
/*
$preinteressent = new preinteressent();
$preinteressent->getPreinteressenten($person->person_id);
if(count($preinteressent->result)>0)
{
	echo '<br><h2>Preinteressent</h2>';
	echo '<table class="tablesorter" data-sortlist="[[0,0]]">
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
		if($row->firma_id)
		{
			$firma = new firma();
			$firma->load($row->firma_id);
			$adresse = new adresse();
			$adresse->load_firma($row->firma_id);
			if(isset($adresse->result[0]))
			{
				$plz = $adresse->result[0]->plz;
				$ort = $adresse->result[0]->ort;
			}
			else
			{
				$plz='';
				$ort='';
			}

			echo $plz.' '.$ort.' '.$firma->name." ($firma->firmentyp_kurzbz)";
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}
*/

//PreIncoming deprecated
/*
$preincoming = new preincoming();
$preincoming->loadFromPerson($person->person_id);

if(count($preincoming->result)>0)
{
	echo '<br><h2>Preincoming</h2>';
	echo '<table class="tablesorter" data-sortlist="[[0,0]]">
			<thead>
				<tr>
					<th>ID</th>
					<th>von</th>
					<th>bis</th>
				</tr>
			</thead>
			<tbody>';
	foreach ($preincoming->result as $row)
	{
		echo '<tr>';
		echo "<td>$row->preincoming_id</td>";
		echo "<td>".$datum_obj->formatDatum($row->von, 'd.m.Y')."</td>";
		echo "<td>".$datum_obj->formatDatum($row->bis, 'd.m.Y')."</td>";
		echo '</tr>';
	}
	echo '</tbody></table>';
}*/

//Prestudent
$prestudent = new prestudent();
$prestudent->getPrestudenten($person->person_id);
foreach ($prestudent->result as $row)
{
	$prestudentLastStatus = new prestudent();
	$prestudentLastStatus->getLastStatus($row->prestudent_id);

	$row->status_kurzbz = $prestudentLastStatus->status_kurzbz;
	$row->studiensemester_kurzbz = $prestudentLastStatus->studiensemester_kurzbz;
	$row->ausbildungssemester = $prestudentLastStatus->ausbildungssemester;
	$row->datum = $prestudentLastStatus->datum;
	$row->orgform_kurzbz = $prestudentLastStatus->orgform_kurzbz;
	$row->studienplan_bezeichnung = $prestudentLastStatus->studienplan_bezeichnung;
	if ($prestudentLastStatus->statusgrund_id != '')
	{
		$statusgrund = new statusgrund($prestudentLastStatus->statusgrund_id);
		$row->statusgrund = $statusgrund->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE];
	}
	else
	{
		$row->statusgrund = '';
	}
}

// Sortiert PreStudenten nach Studiensemester
function sortPrestudents($a, $b)
{
	$c = substr($b->studiensemester_kurzbz, 2) - substr($a->studiensemester_kurzbz, 2);
	$c .= strcmp(substr($b->studiensemester_kurzbz, 0, 2), substr($a->studiensemester_kurzbz, 0, 2));
	return $c;
}

usort($prestudent->result, "sortPrestudents");

//var_dump($prestudent->result);
$studiensemester_kurzbz = '';
$stdsem = '';
if(count($prestudent->result)>0)
{
	echo '<br><h2>Pre-/Studenten</h2>';
	foreach ($prestudent->result as $row)
	{
		if ($studiensemester_kurzbz == '' || $studiensemester_kurzbz != $row->studiensemester_kurzbz)
		{
			if ($studiensemester_kurzbz != $row->studiensemester_kurzbz)
			{
				echo '</tbody></table>';
			}
			echo '<h3>'.$row->studiensemester_kurzbz.'</h3>';
			echo '<table class="tablePreStudent" id="tablePreStudent_'.$row->studiensemester_kurzbz.'">
			<thead>
				<tr>
					<th>ID</th>
					<th>Studiensemester</th>
					<th>Priorität</th>
					<th>Studiengang</th>
					<th>Organisationsform</th>
					<th>Studienplan</th>
					<th>Reihung absolviert</th>
					<th>UID</th>
					<th>Gruppe</th>
					<th>Status</th>
				</tr>
			</thead><tbody>';
		}
		$class = '';
		if ($row->status_kurzbz == 'Abgewiesener' || $row->status_kurzbz == 'Abbrecher' || $row->status_kurzbz == 'Absolvent' )
		{
			$class = 'class="inactive"';
		}
		$status = $row->status_kurzbz;
		if ($row->ausbildungssemester != '')
		{
			$status .= ' ('.$row->ausbildungssemester.'. Semester)';
		}
		if ($row->statusgrund != '')
		{
			$status .= ' - '.$row->statusgrund;
		}
		echo '<tr>';
		echo "<td ".$class.">$row->prestudent_id</td>";
		echo "<td ".$class.">$row->studiensemester_kurzbz</td>";
		echo "<td ".$class.">$row->priorisierung</td>";
		echo "<td ".$class.">".$studiengang->kuerzel_arr[$row->studiengang_kz]."</td>";
		echo "<td ".$class.">$row->orgform_kurzbz</td>";
		echo "<td ".$class.">$row->studienplan_bezeichnung</td>";
		echo "<td ".$class.">".($row->reihungstestangetreten?'Ja':'Nein')."</td>";
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
		echo "<td ".$class.">$uid</td>";
		echo "<td ".$class.">$gruppe</td>";
		echo "<td ".$class.">".$status."</td>";
		echo '</tr>';

		$studiensemester_kurzbz = $row->studiensemester_kurzbz;
	}
	echo '</tbody></table>';
}

$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE person_id='$person->person_id'";
if($result = $db->db_query($qry))
{
	if($db->db_num_rows($result)>0)
	{
		echo '<br><h2>Mitarbeiter</h2>';
		echo '<table class="tablesorter" data-sortlist="[[0,0]]">
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
